<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIXReport.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
try {
    $html = getFormXIXRequestData($dbconn);
} catch (Throwable $exception) {
    ob_clean();
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}
ob_clean();
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form XIX - Wages Slip');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 8);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage('L', 'A4');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-XIX-Wages-Slip.pdf', 'I');
