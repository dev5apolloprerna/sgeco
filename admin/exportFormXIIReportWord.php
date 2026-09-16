<?php
/*
 * Form XII - Employment Card
 * Word Export — REAL .docx (OOXML), paragraph-only (no tables anywhere).
 *
 * Same delivery as exportFormVIIIReportWord.php. Because there is NO
 * <w:tbl> in the document, Word cannot draw table gridlines. The only
  * visible lines are the intentional underline on form fields and the
 * passport-photo placeholder border.
 */

ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIIReport.php');

/* =========================================================
   DOCX HELPERS (identical to Form VIII)
   ========================================================= */

function docxEscape($s)
{
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function docxRun($text, $bold = false, $underline = false, $italic = false, $size = 26)
{
    $rPr  = '<w:rPr>';
    $rPr .= '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
    $rPr .= '<w:sz w:val="' . $size . '"/><w:szCs w:val="' . $size . '"/>';
    if ($bold)      $rPr .= '<w:b/>';
    if ($underline) $rPr .= '<w:u w:val="single"/>';
    if ($italic)    $rPr .= '<w:i/>';
    $rPr .= '</w:rPr>';
    return '<w:r>' . $rPr . '<w:t xml:space="preserve">' . docxEscape($text) . '</w:t></w:r>';
}

/**
 * A paragraph.
 * Extra opts: tabStops (array), rightTabPos (twips)
 */
function docxPara($runsXml, $align = 'left', array $opts = array())
{
    $pPr = '<w:pPr>';

    /* Optional tab stops (used for the Passport Photo right alignment) */
    if (isset($opts['rightTabPos'])) {
        $pPr .= '<w:tabs><w:tab w:val="right" w:pos="' . (int)$opts['rightTabPos'] . '"/></w:tabs>';
    }

    if (!empty($opts['keepNext']))        $pPr .= '<w:keepNext/>';
    if (!empty($opts['pageBreakBefore'])) $pPr .= '<w:pageBreakBefore/>';
    if (isset($opts['spaceBefore']))      $pPr .= '<w:spacing w:before="' . (int)$opts['spaceBefore'] . '"/>';
    if (isset($opts['spaceAfter']))       $pPr .= '<w:spacing w:after="' . (int)$opts['spaceAfter'] . '"/>';
    if (isset($opts['line']))             $pPr .= '<w:spacing w:line="' . (int)$opts['line'] . '" w:lineRule="auto"/>';
    $pPr .= '<w:jc w:val="' . $align . '"/>';
    $pPr .= '</w:pPr>';
    return '<w:p>' . $pPr . $runsXml . '</w:p>';
}

/** Tab run */
function docxTab()
{
    return '<w:r><w:tab/></w:r>';
}

/**
 * Floating passport-photo placeholder used by the printed employment card.
 *
 * VML is used here because it is supported by the older desktop Word versions
 * used to open these exports. The shape is anchored to the page's text margin,
 * so it remains in the top-right corner without requiring a layout table.
 */
function docxPhotoBox()
{
    return '<w:r><w:pict>'
        . '<v:rect id="FormXIIPhotoBox" '
        . 'style="position:absolute;width:105pt;height:115pt;z-index:1;'
        . 'mso-position-horizontal:right;mso-position-horizontal-relative:margin;'
        . 'mso-position-vertical:top;mso-position-vertical-relative:paragraph" '
        . 'stroked="t" strokeweight="1pt" strokecolor="#000000" fillcolor="#ffffff">'
        . '<v:textbox inset="8pt,6pt,6pt,6pt" style="mso-fit-shape-to-text:f">'
        . '<w:txbxContent>'
        . docxPara(docxRun('Passport Size', false, false, false, 22), 'left', array('spaceBefore' => 1080))
        . docxPara(docxRun('Photo', false, false, false, 22), 'left')
        . '</w:txbxContent>'
        . '</v:textbox>'
        . '<w10:wrap type="square" side="left"/>'
        . '</v:rect>'
        . '</w:pict></w:r>';
}

/* =========================================================
   ONE EMPLOYEE = ONE PAGE  (paragraphs only)
   ========================================================= */

function formXIIEmployeeXml(array $employee, $pageBreakBefore)
{
    $d = formXIIEmployeeData($employee);

    $name        = $d['name'];
    $uanAadhaar  = $d['uan_aadhaar'];
    $mobile      = $d['mobile'];
    $serial      = $d['serial'];
    $designation = $d['designation'];
    $rate        = $d['rate'];
    $joining     = $d['joining'];

    $firstOpts = array('keepNext' => true, 'spaceAfter' => 120);
    if ($pageBreakBefore) $firstOpts['pageBreakBefore'] = true;

    $xml = '';

    /* ---------- Header ---------- */
    $xml .= docxPara(docxRun('FORM XII', true, false, false, 44), 'center', $firstOpts);
    $xml .= docxPara(
        docxRun('[Under rule 76 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]', false, false, false, 24),
        'center',
        array('keepNext' => true, 'spaceAfter' => 120)
    );
    $xml .= docxPara(
        docxRun('Employment Card', true, false, false, 38),
        'center',
        array('keepNext' => true, 'spaceAfter' => 360)
    );

    /* ---------- A / A1 / A2 / A3 (photo box anchored at the right) ---------- */
    $xml .= docxPara(
        docxPhotoBox() .
        docxRun('A. ', true) .
        docxRun('Name Contractor: ') .
        docxRun('Shree Ganesh Engineering Co.', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('A1. ', true) .
        docxRun('LIN/PAN No. of the contractor: ') .
        docxRun('AAMFS3884N', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('A2. ', true) .
        docxRun('Email Id of the contractor: ') .
        docxRun('hkshah@sgeco.in', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('A3. ', true) .
        docxRun('Mobile No. of the contractor: ') .
        docxRun('7984454082', true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 200)
    );

    /* ---------- B ---------- */
    $xml .= docxPara(
        docxRun('B. ', true) .
        docxRun('Nature and location of work: '),
        'left',
        array('keepNext' => true, 'spaceAfter' => 160)
    );

    /* ---------- C / C1 / C2 ---------- */
    $xml .= docxPara(
        docxRun('C. ', true) .
        docxRun('Name of workmen: ') .
        docxRun($name, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('C1. ', true) .
        docxRun('UAN/Aadhar No.: ') .
        docxRun($uanAadhaar, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 100)
    );
    $xml .= docxPara(
        docxRun('C2. ', true) .
        docxRun('Mobile No.: ') .
        docxRun($mobile, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 240)
    );

    /* ---------- 1..5 ---------- */
    $xml .= docxPara(
        docxRun('1. ', true) .
        docxRun('Serial number in the register of workmen employed: ') .
        docxRun($serial, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 120)
    );
    $xml .= docxPara(
        docxRun('2. ', true) .
        docxRun('Nature of Designation: ') .
        docxRun($designation, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 120)
    );
    $xml .= docxPara(
        docxRun('3. ', true) .
        docxRun('Wages rate (with particulars of unit, in case of piece-work): ') .
        docxRun($rate, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 120)
    );
    $xml .= docxPara(
        docxRun('4. ', true) .
        docxRun('Date of commencement of employment: ') .
        docxRun($joining, true, true),
        'left',
        array('keepNext' => true, 'spaceAfter' => 120)
    );
    $xml .= docxPara(
        docxRun('5. ', true) .
        docxRun('Remarks ') .
        docxRun(str_repeat('.', 70), false, false),
        'left',
        array('spaceAfter' => 100)
    );

    /* ---------- Gap before signature ---------- */
    $xml .= docxPara(docxRun(''), 'left', array('spaceAfter' => 900));
    $xml .= docxPara(docxRun(''), 'left', array('spaceAfter' => 900));
    $xml .= docxPara(docxRun(''), 'left', array('spaceAfter' => 900));

    /* ---------- Signature ---------- */
    $xml .= docxPara(docxRun('Seal and Signature of Employer'), 'right');

    return $xml;
}

/* =========================================================
   REQUEST
   ========================================================= */
try {
    $companyId  = isset($_GET['Company'])        ? (int)$_GET['Company']         : 0;
    $month      = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId'])     ? (int)$_GET['employeeId']      : 0;

    $employees = getFormXIIEmployees($dbconn, $companyId, $month, $employeeId);
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

$documentXml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$documentXml .= '<w:document '
             .  'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
             .  'xmlns:v="urn:schemas-microsoft-com:vml" '
             .  'xmlns:w10="urn:schemas-microsoft-com:office:word">';
$documentXml .= '<w:body>';

if (!$employees) {
    $documentXml .= docxPara(docxRun('No Data Found !', true, false, false, 32), 'center');
} else {
    foreach ($employees as $index => $employee) {
        $documentXml .= formXIIEmployeeXml($employee, $index > 0);
    }
}

$documentXml .= '<w:sectPr>'
             .  '<w:pgSz w:w="11906" w:h="16838"/>'
             .  '<w:pgMar w:top="1134" w:right="1134" w:bottom="1021" w:left="1247" '
             .           'w:header="0" w:footer="0" w:gutter="0"/>'
             .  '<w:cols w:space="708"/>'
             .  '<w:docGrid w:linePitch="360"/>'
             .  '</w:sectPr>';

$documentXml .= '</w:body></w:document>';

$contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
    . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
    . '</Types>';

$rootRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
    . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
    . '</Relationships>';

$docRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    . '</Relationships>';

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

$coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<cp:coreProperties '
    .   'xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
    .   'xmlns:dc="http://purl.org/dc/elements/1.1/" '
    .   'xmlns:dcterms="http://purl.org/dc/terms/" '
    .   'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
    . '<dc:title>Form XII - Employment Card</dc:title>'
    . '<dc:creator>SGECO</dc:creator>'
    . '<cp:lastModifiedBy>SGECO</cp:lastModifiedBy>'
    . '</cp:coreProperties>';

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

$zip->addFromString('[Content_Types].xml',          $contentTypesXml);
$zip->addFromString('_rels/.rels',                  $rootRelsXml);
$zip->addFromString('word/document.xml',            $documentXml);
$zip->addFromString('word/_rels/document.xml.rels', $docRelsXml);
$zip->addFromString('word/styles.xml',              $stylesXml);
$zip->addFromString('docProps/core.xml',            $coreXml);
$zip->addFromString('docProps/app.xml',             $appXml);

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
header('Content-Disposition: attachment; filename="Form-XII-Employment-Card.docx"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: max-age=0');
header('Pragma: public');

readfile($tmpFile);
@unlink($tmpFile);
exit;