<?php
/*
 * Form VIII - Service Certificate
 * Word Export — generates a REAL .docx (OOXML) file.
 *
 * Why this is the definitive fix:
 *   - Word 2016/2019/365 reject HTML delivered as .doc in many setups
 *     ("Word experienced an error trying to open the file").
 *   - A real .docx is a ZIP of XML parts. Word opens it natively,
 *     with no MIME / extension ambiguity.
 *   - .docx has NO page-border concept unless explicitly added, so
 *     the border disappears automatically.
 *   - Native <w:pageBreakBefore/> guarantees 1 employee = 1 page.
 */

ob_start();
include('../config.php');
include('IsLogin.php');

/* =========================================================
   DATA
   ========================================================= */
function formVIIIWordGetEmployees($dbconn, $companyId, $salaryMonth, $employeeId = 0)
{
    $companyId  = (int)$companyId;
    $employeeId = (int)$employeeId;

    if ($companyId <= 0) {
        throw new InvalidArgumentException('A valid company is required.');
    }
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }

    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    $employeeWhere = $employeeId > 0 ? ' AND employee.employeeId=' . $employeeId : '';

    $sql = "
        SELECT DISTINCT
            employee.employeeId, employee.employeecode, employee.emp_name,
            employee.uan, employee.adharcard, employee.mno,
            employee.designation, employee.dateofjoining, employee.strExitDate
        FROM salarydetails
        INNER JOIN employee ON employee.employeeId = salarydetails.emp_id
            AND employee.isDelete = '0'
        WHERE salarydetails.companyId = " . $companyId . "
        AND salarydetails.salaryId IN (
            SELECT salarymasterId FROM salarymaster
            WHERE month = '" . $month . "' AND isDelete = '0' AND istatus = '1'
        )
        AND salarydetails.isDelete = '0'
        AND salarydetails.istatus = '1'
        AND salarydetails.workingdays > 0
        " . $employeeWhere . "
        ORDER BY employee.emp_name ASC
    ";

    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Form VIII employees.');
    }
    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function formVIIIWordDate($value)
{
    $value = trim((string)$value);
    if ($value === '' || $value === '0000-00-00') return '';
    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date('d-m-Y', $timestamp);
}

function formVIIIWordPeriod(array $employee)
{
    $from = formVIIIWordDate(isset($employee['dateofjoining']) ? $employee['dateofjoining'] : '');
    $to   = formVIIIWordDate(isset($employee['strExitDate'])    ? $employee['strExitDate']    : '');
    if ($from === '') return $to;
    return $from . ' to ' . ($to === '' ? 'Present' : $to);
}

/* =========================================================
   DOCX HELPERS
   ========================================================= */

function docxEscape($s)
{
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/**
 * A single run.
 * $size is in half-points (26 = 13pt, 44 = 22pt, 38 = 19pt, 24 = 12pt).
 */
function docxRun($text, $bold = false, $underline = false, $italic = false, $size = 26)
{
    $rPr  = '<w:rPr>';
    $rPr .= '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
    $rPr .= '<w:sz w:val="' . $size . '"/>';
    $rPr .= '<w:szCs w:val="' . $size . '"/>';
    if ($bold)      $rPr .= '<w:b/>';
    if ($underline) $rPr .= '<w:u w:val="single"/>';
    if ($italic)    $rPr .= '<w:i/>';
    $rPr .= '</w:rPr>';
    return '<w:r>' . $rPr . '<w:t xml:space="preserve">' . docxEscape($text) . '</w:t></w:r>';
}

/**
 * A paragraph. $runsXml is the concatenated runs.
 * $opts: pageBreakBefore, keepNext, spaceBefore, spaceAfter, line, indentLeft
 */
function docxPara($runsXml, $align = 'left', array $opts = array())
{
    $pPr = '<w:pPr>';
    if (!empty($opts['keepNext']))       $pPr .= '<w:keepNext/>';
    if (!empty($opts['pageBreakBefore'])) $pPr .= '<w:pageBreakBefore/>';
    if (isset($opts['spaceBefore']))     $pPr .= '<w:spacing w:before="' . (int)$opts['spaceBefore'] . '"/>';
    if (isset($opts['spaceAfter']))      $pPr .= '<w:spacing w:after="' . (int)$opts['spaceAfter'] . '"/>';
    if (isset($opts['line']))            $pPr .= '<w:spacing w:line="' . (int)$opts['line'] . '" w:lineRule="auto"/>';
    if (isset($opts['indentLeft']))      $pPr .= '<w:ind w:left="' . (int)$opts['indentLeft'] . '"/>';
    $pPr .= '<w:jc w:val="' . $align . '"/>';
    $pPr .= '</w:pPr>';
    return '<w:p>' . $pPr . $runsXml . '</w:p>';
}

/**
 * Builds the entire XML body for one employee.
 * $pageBreakBefore = true means "this employee starts on a new page".
 */
function formVIIIEmployeeXml(array $employee, $pageBreakBefore)
{
    $name        = isset($employee['emp_name'])     ? $employee['emp_name']     : '';
    $uan         = isset($employee['uan'])          ? $employee['uan']          : '';
    $aadhaar     = isset($employee['adharcard'])    ? $employee['adharcard']    : '';
    $mobile      = isset($employee['mno'])          ? $employee['mno']          : '';
    $serial      = isset($employee['employeecode']) ? $employee['employeecode'] : '';
    $designation = isset($employee['designation'])  ? $employee['designation']  : '';
    $period      = formVIIIWordPeriod($employee);

    $firstOpts = array('keepNext' => true, 'spaceAfter' => 120);
    if ($pageBreakBefore) {
        $firstOpts['pageBreakBefore'] = true;
    }

    $xml = '';

    /* Header */
    $xml .= docxPara(
        docxRun('FORM VIII', true, false, false, 44),
        'center',
        $firstOpts
    );
    $xml .= docxPara(
        docxRun('[Under rule 77 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]', false, false, false, 24),
        'center',
        array('keepNext' => true, 'spaceAfter' => 120)
    );
    $xml .= docxPara(
        docxRun('Service Certificate', true, false, false, 38),
        'center',
        array('keepNext' => true, 'spaceAfter' => 480)
    );

    /* Rows 1-4 */
    $xml .= docxPara(
        docxRun('1.   ') .
        docxRun('Name of employer: ') .
        docxRun('Shree Ganesh Engineering Co.', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('2.   ') .
        docxRun('LIN/PAN No. of the employer: ') .
        docxRun('AAMFS3884N', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('3.   ') .
        docxRun('Email Id of the employer: ') .
        docxRun('hkshah@sgeco.in', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('4.   ') .
        docxRun('Mobile No. of the employer: ') .
        docxRun('7984454082', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );

    /* Row 5 with underlined blanks */
    $xml .= docxPara(
        docxRun('5.   ') . docxRun('Nature and location of work:'),
        'left',
        array('keepNext' => true, 'spaceAfter' => 40)
    );
    $xml .= docxPara(
        docxRun('        ') . docxRun(str_repeat(' ', 55), false, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 40)
    );
    $xml .= docxPara(
        docxRun('        ') . docxRun(str_repeat(' ', 55), false, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 160)
    );

    /* Rows 6-11 */
    $xml .= docxPara(
        docxRun('6.   ') .
        docxRun('Name of the workman: ') .
        docxRun($name, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('7.   ') .
        docxRun('UAN / Aadhaar No.: UAN: ') .
        docxRun($uan, true, true) .
        docxRun(' / Aadhaar No.: ') .
        docxRun($aadhaar, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('8.   ') .
        docxRun('Mobile No.: ') .
        docxRun($mobile, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('9.   ') .
        docxRun('Serial Number in the Register of Workmen: ') .
        docxRun($serial, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('10.  ') .
        docxRun('Period of Employment: ') .
        docxRun($period, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('11.  ') .
        docxRun('Designation: ') .
        docxRun($designation, true, true),
        'left',
        array('spaceAfter' => 100)
    );

    /* Gap before signature */
    $xml .= docxPara(docxRun(''), 'left', array('spaceAfter' => 900));
    $xml .= docxPara(docxRun(''), 'left', array('spaceAfter' => 900));
    $xml .= docxPara(docxRun(''), 'left', array('spaceAfter' => 900));

    /* Signature */
    $xml .= docxPara(
        docxRun('Seal and Signature of Employer'),
        'right'
    );

    return $xml;
}

/* =========================================================
   REQUEST
   ========================================================= */
try {
    $companyId  = isset($_GET['Company'])        ? (int)$_GET['Company']         : 0;
    $month      = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId'])     ? (int)$_GET['employeeId']      : 0;

    $employees = formVIIIWordGetEmployees($dbconn, $companyId, $month, $employeeId);
} catch (Throwable $exception) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $exception->getMessage();
    exit;
}

/* =========================================================
   BUILD DOCX PARTS
   ========================================================= */

/* -------- word/document.xml -------- */
$documentXml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$documentXml .= '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">';
$documentXml .= '<w:body>';

if (!$employees) {
    $documentXml .= docxPara(
        docxRun('No Data Found !', true, false, false, 32),
        'center'
    );
} else {
    $total = count($employees);
    foreach ($employees as $index => $employee) {
        $documentXml .= formVIIIEmployeeXml($employee, $index > 0);
    }
}

/* Section properties: A4, 20/20/18/22 mm margins (in twentieths of a point) */
$documentXml .= '<w:sectPr>'
             .  '<w:pgSz w:w="11906" w:h="16838"/>'
             .  '<w:pgMar w:top="1134" w:right="1134" w:bottom="1021" w:left="1247" '
             .           'w:header="0" w:footer="0" w:gutter="0"/>'
             .  '<w:cols w:space="708"/>'
             .  '<w:docGrid w:linePitch="360"/>'
             .  '</w:sectPr>';

$documentXml .= '</w:body></w:document>';

/* -------- [Content_Types].xml -------- */
$contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
    . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
    . '</Types>';

/* -------- _rels/.rels -------- */
$rootRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
    . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
    . '</Relationships>';

/* -------- word/_rels/document.xml.rels -------- */
$docRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    . '</Relationships>';

/* -------- word/styles.xml -------- */
$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    . '<w:docDefaults>'
    .   '<w:rPrDefault><w:rPr>'
    .     '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
    .     '<w:sz w:val="26"/><w:szCs w:val="26"/>'
    .   '</w:rPr></w:rPrDefault>'
    .   '<w:pPrDefault><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:pPrDefault>'
    . '</w:docDefaults>'
    . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
    . '</w:styles>';

/* -------- docProps/core.xml -------- */
$coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<cp:coreProperties '
    .   'xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
    .   'xmlns:dc="http://purl.org/dc/elements/1.1/" '
    .   'xmlns:dcterms="http://purl.org/dc/terms/" '
    .   'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
    . '<dc:title>Form VIII - Service Certificate</dc:title>'
    . '<dc:creator>SGECO</dc:creator>'
    . '<cp:lastModifiedBy>SGECO</cp:lastModifiedBy>'
    . '</cp:coreProperties>';

/* -------- docProps/app.xml -------- */
$appXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
    . '<Application>SGECO</Application>'
    . '</Properties>';

/* =========================================================
   BUILD ZIP
   ========================================================= */
if (!class_exists('ZipArchive')) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('PHP ZipArchive extension is not enabled on this server.');
}

$tmpFile = tempnam(sys_get_temp_dir(), 'docx');
if ($tmpFile === false) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Unable to create temporary file.');
}
@unlink($tmpFile);

$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE) !== true) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Unable to open ZIP archive.');
}

$zip->addFromString('[Content_Types].xml',         $contentTypesXml);
$zip->addFromString('_rels/.rels',                 $rootRelsXml);
$zip->addFromString('word/document.xml',           $documentXml);
$zip->addFromString('word/_rels/document.xml.rels', $docRelsXml);
$zip->addFromString('word/styles.xml',             $stylesXml);
$zip->addFromString('docProps/core.xml',           $coreXml);
$zip->addFromString('docProps/app.xml',            $appXml);

if (!$zip->close()) {
    @unlink($tmpFile);
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Unable to write ZIP archive.');
}

/* =========================================================
   OUTPUT
   ========================================================= */
while (ob_get_level() > 0) { ob_end_clean(); }

$fileSize = filesize($tmpFile);

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="Form-VIII-Service-Certificate.docx"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: max-age=0');
header('Pragma: public');

readfile($tmpFile);
@unlink($tmpFile);
exit;