<?php

ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormCReport.php');

try {
    $html = getFormCRequestData($dbconn);
} catch (Exception $exception) {
    ob_clean();
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
ob_clean();

$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form C');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// $pdf->SetMargins(0, 0, 0);
// $pdf->SetAutoPageBreak(false, 0);
// Keep a small printable inset, matching the compact wage-register export, so
// the legal-landscape page can accommodate substantially more employee rows.
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage('L', 'LEGAL');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-C.pdf', 'I');
