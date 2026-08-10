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
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage('L', 'LEGAL');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-C.pdf', 'I');
