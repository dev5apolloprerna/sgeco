<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIIReport.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getFormXIIEmployees($dbconn, $companyId, $month, $employeeId);
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function formXIIPdfHtml(array $employee)
{
    $d = formXIIEmployeeData($employee);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $line = function ($label, $value = '') use ($e) {
        return '<tr><td width="42%">' . $label . '</td><td width="58%"><b><u>' . $e($value) . '</u></b></td></tr>';
    };
    return '<style>table{font-family:times;font-size:13.5pt;line-height:1.35}td{padding:5px}</style>' .
        '<table cellpadding="2"><tr><td align="center" style="font-size:19pt;font-weight:bold">FORM XII</td></tr>' .
        '<tr><td align="center" style="font-size:11pt">[Under rule 76 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]</td></tr>' .
        '<tr><td align="center" style="font-size:16pt;font-weight:bold">Employment Card</td></tr><tr><td height="28"></td></tr></table>' .
        '<table cellpadding="4"><tr><td width="77%"><table cellpadding="4">' .
        $line('A. &nbsp; Name Contractor:', 'Shree Ganesh Engineering Co.') .
        $line('A1. LIN/PAN No. of the contractor:', 'AAMFS3884N') .
        $line('A2. Email Id of the contractor:', 'hkshah@sgeco.in') .
        $line('A3. Mobile No. of the contractor:', '7984454082') .
        '</table></td><td width="23%"><table cellpadding="4"><tr><td height="112" style="border:1px solid #000" valign="bottom">Passport Size<br>Photo</td></tr></table></td></tr></table>' .
        '<table cellpadding="4">' .
        '<tr><td height="18"></td><td></td></tr>' .
        $line('B. &nbsp; Nature and location of work:', '') .
        '<tr><td></td><td>________________________________</td></tr>' .
        $line('C. &nbsp; Name of workmen:', $d['name']) .
        $line('C1. UAN/Aadhar No.:', $d['uan_aadhaar']) .
        $line('C2. Mobile No.:', $d['mobile']) . '</table>' .
        '<table cellpadding="5"><tr><td width="5%">1.</td><td width="65%">Serial number in the register of workmen employed:</td><td width="30%"><b><u>' . $e($d['serial']) . '</u></b></td></tr>' .
        '<tr><td>2.</td><td colspan="2">Nature of Designation: <b><u>' . $e($d['designation']) . '</u></b></td></tr>' .
        '<tr><td>3.</td><td colspan="2">Wages rate (with particulars of unit, in case of piece-work): <b><u>' . $e($d['rate']) . '</u></b></td></tr>' .
        '<tr><td>4.</td><td colspan="2">Date of commencement of employment: <b><u>' . $e($d['joining']) . '</u></b></td></tr>' .
        '<tr><td>5.</td><td colspan="2">Remarks ....................................................................................</td></tr></table>' .
        '<table><tr><td height="130"></td></tr><tr><td width="58%"></td><td width="42%" align="center">Seal and Signature of Contractor</td></tr></table>';
}

ob_clean();
$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XII - Employment Card');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(24, 20, 24);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 9);
foreach ($employees as $employee) {
    $pdf->AddPage('P', 'A4');
    $pdf->writeHTML(formXIIPdfHtml($employee), true, false, true, false, '');
}
if (!$employees) {
    $pdf->AddPage('P', 'A4');
    $pdf->writeHTML('<h2 style="text-align:center">No Data Found !</h2>');
}
$pdf->Output('Form-XII-Employment-Card.pdf', 'I');
