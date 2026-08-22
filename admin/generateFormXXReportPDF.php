<?php

ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXXReport.php');
require_once('OtherReportOutput.php');

try {
    // $html = addOtherReportPdfSpacing(getFormXXRequestData($dbconn));
    $html = addFormXXPdfFormatting(
        addOtherReportPdfSpacing(getFormXXRequestData($dbconn), true, '7px 5px')
    );
} catch (Throwable $exception) {
    ob_clean();
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
ob_clean();

$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XX - Register of Deduction for Damage or Loss');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 8, 0);
$pdf->SetAutoPageBreak(true, 8);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage('L', 'LEGAL');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-XX-Register-of-Deduction-for-Damage-or-Loss.pdf', 'I');
