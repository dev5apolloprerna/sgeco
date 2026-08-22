<?php

ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXXIReport.php');
require_once('OtherReportOutput.php');

try {
    $html = addOtherReportPdfSpacing(getFormXXIRequestData($dbconn));
} catch (Exception $exception) {
    ob_clean();
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
ob_clean();

$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XXI - Register of Fine');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 8, 0);
$pdf->SetAutoPageBreak(true, 8);
// $pdf->SetFont('helvetica', '', 9);
$pdf->SetFont('times', '', 9);
$pdf->AddPage('L', 'LEGAL');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-XXI-Register-of-Fine.pdf', 'I');