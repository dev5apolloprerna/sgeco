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
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);
$pdf->SetCreator($ProjectName);
$pdf->SetTitle('Employee Payment History');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 11);
$pdf->writeHTML(employeePaymentHistoryHtml($employee), true, false, false, false, '');
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('EmployeePaymentHistory.pdf', 'I');
exit;
