<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIXReport.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getFormXIXEmployees($dbconn, $companyId, $month, $employeeId);
    $slips = getFormXIXSlipData(
        $employees,
        $month,
        getFormXIXCompanyName($dbconn, $companyId),
        getCompanyReportAdvances($dbconn, $companyId, $month)
    );
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

ob_clean();
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XIX - Wages Slip');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('helvetica', '', 9);
foreach ($slips as $slip) {
    $pdf->AddPage('L', 'A4');
    $pdf->writeHTML(renderFormXIXSlipTable($slip), true, false, true, false, '');
}
if (!$slips) {
    $pdf->AddPage('L', 'A4');
    $pdf->writeHTML('<h2 style="text-align:center">No Data Found !</h2>');
}
$pdf->Output('Form-XIX-Wages-Slip.pdf', 'I');
