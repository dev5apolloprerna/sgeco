<?php

ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIIIReport.php');
require_once('OtherReportOutput.php');

try {
    $html = addOtherReportPdfSpacing(getFormXIIIRequestData($dbconn));
} catch (Throwable $exception) {
    ob_clean();
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
ob_clean();

$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XIII - Register of Workmen Employed by Contractor');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage('L', 'LEGAL');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-XIII-Register-of-Workmen.pdf', 'I');
