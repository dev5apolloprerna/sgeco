<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormVIIIReport.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getFormVIIIEmployees($dbconn, $companyId, $month, $employeeId);
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function formVIIIPdfHtml(array $employee)
{
    $d = formVIIIEmployeeData($employee);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $row = function ($number, $label, $value = '') use ($e) {
        return '<tr><td width="7%" align="right">' . $number . '.</td><td width="93%">' . $label .
            ($value === '' ? '' : ' <b><u>' . $e($value) . '</u></b>') . '</td></tr>';
    };
    return '<style>table{font-family:times;font-size:13pt;line-height:1.45}td{padding:5px}</style>' .
        '<table width="100%" cellpadding="3"><tr><td align="center" style="font-size:22pt;font-weight:bold">FORM VIII</td></tr>' .
        '<tr><td align="center">[Under rule 77 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]</td></tr>' .
        '<tr><td align="center" style="font-size:19pt;font-weight:bold">Service Certificate</td></tr><tr><td height="30"></td></tr></table>' .
        '<table width="100%" cellpadding="5">' .
        $row(1, 'Name of employer:', 'Shree Ganesh Engineering Co.') .
        $row(2, 'LIN/PAN No. of the employer:', 'AAMFS3884N') .
        $row(3, '<i>Email Id of the employer:</i>', 'hkshah@sgeco.in') .
        $row(4, '<i>Mobile No. of the employer:</i>', '7984454082') .
        $row(5, 'Nature and location of work: ________________________________<br>&nbsp;<br>________________________________') .
        $row(6, 'Name of the workman:', $d['name']) .
        $row(7, 'UAN / Aadhaar No.: UAN: <b><u>' . $e($d['uan']) . '</u></b> / Aadhaar No.:', $d['aadhaar']) .
        $row(8, 'Mobile No.:', $d['mobile']) .
        $row(9, 'Serial Number in the Register of Workmen:', $d['serial']) .
        $row(10, 'Period of Employment:', $d['period']) .
        $row(11, 'Designation:', $d['designation']) .
        '</table><table width="100%"><tr><td height="105"></td></tr><tr><td align="right">Seal and Signature of Employer</td></tr></table>';
}

ob_clean();
$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form VIII - Service Certificate');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(24, 24, 22);
$pdf->SetAutoPageBreak(false);
foreach ($employees as $employee) {
    $pdf->AddPage('P', 'A4');
    $pdf->writeHTML(formVIIIPdfHtml($employee), true, false, true, false, '');
}
if (!$employees) {
    $pdf->AddPage('P', 'A4');
    $pdf->writeHTML('<h2 style="text-align:center">No Data Found !</h2>');
}
$pdf->Output('Form-VIII-Service-Certificate.pdf', 'I');
