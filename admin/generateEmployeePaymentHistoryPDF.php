<?php
ob_start();
require_once 'tcpdf/config/tcpdf_config.php';
require_once 'tcpdf/tcpdf.php';
include '../common.php';
include 'IsLogin.php';
require_once 'employeePaymentHistoryData.php';
$employee = employeePaymentHistoryFind($dbconn, isset($_GET['employeeId']) ? $_GET['employeeId'] : 0);
if (!$employee) {
    http_response_code(404);
    exit('Employee not found.');
}
// Do not use PDF_PAGE_FORMAT here: this installation defaults it to A2.
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator($ProjectName);
$pdf->SetTitle('Employee Payment History');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(15, 18, 15);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

$fields = array(
    'Employee Name' => $employee['emp_name'],
    'UAN No.' => $employee['uan'],
    'PF No.' => $employee['pfcode'],
    'Employee Code' => $employee['employeecode'],
    'Date of Joining' => $employee['dateofjoining'],
    'Date of Exit (Last Month)' => $employee['dateOfExit']
);
$html = '<table cellpadding="7" cellspacing="0" border="1" style="border-color:#8090a0">';
$html .= '<tr style="background-color:#d9e2f3"><th colspan="2" align="center"><span style="font-size:17px;font-weight:bold">Employee Payment History</span></th></tr>';
foreach ($fields as $label => $value) {
    $html .= '<tr><td width="40%" style="font-weight:bold;background-color:#f4f6f8">' . employeePaymentHistoryEscape($label) . ' :</td>';
    $html .= '<td width="60%">' . employeePaymentHistoryEscape($value) . '</td></tr>';
}
$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('EmployeePaymentHistory.pdf', 'I');
exit;
