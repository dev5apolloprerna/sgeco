<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXXIIIReport.php');
require_once('OtherReportOutput.php');

try {
    $html = addOtherReportPdfSpacing(getFormXXIIIRequestData($dbconn, false), false);
    // Apply the request month to the final PDF document as well. This keeps
    // the export dynamic even if PDF-only preprocessing changes the header.
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $html = formXXIIIApplyMonth($html, $salaryMonth);
} catch (Throwable $exception) {
    ob_clean();
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
ob_clean();
$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XXIII - Register of Overtime');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->SetFont('helvetica', '', 9);
foreach (splitOtherReportPdfPages($html) as $pageHtml) {
    $pdf->AddPage('L', 'LEGAL');
    $pdf->writeHTML($pageHtml, true, false, true, false, '');
}
$pdf->Output('Form-XXIII-Register-of-Overtime.pdf', 'I');
