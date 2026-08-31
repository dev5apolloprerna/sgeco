<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FullFinalSettlementReport.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');

try {
    $settlements = getFullFinalSettlements(
        $dbconn,
        isset($_GET['employeeId']) ? $_GET['employeeId'] : 0,
        isset($_GET['Company']) ? $_GET['Company'] : 0,
        isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : ''
    );
    $html = renderFullFinalSettlements($settlements);
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}
ob_clean();
$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Full and Final Settlement');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 8, 10);
$pdf->SetAutoPageBreak(true, 8);
// FreeSans is bundled with TCPDF, contains Gujarati glyphs, and most closely
// matches the sans-serif Gujarati font used in the source document.
$pdf->setFontSubsetting(true);
$pdf->SetFont('freesans', '', 9);
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');
$fileSuffix = count($settlements) === 1 ? '-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $settlements[0]['employee_name']) : '-All-Employees';
$pdf->Output('Full-Final-Settlement' . $fileSuffix . '.pdf', 'I');