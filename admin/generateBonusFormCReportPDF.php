<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('BonusFormCReport.php');
require_once('OtherReportOutput.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getBonusFormCEmployees($dbconn, $companyId, $month, $employeeId);

    // Older records use zero as the default for fields that were not entered.
    // Keep that implementation detail out of this PDF without changing the data.
    foreach ($employees as &$employee) {
        if (trim((string) $employee['designation']) === '0') {
            $employee['designation'] = '';
        }
        if (trim((string) $employee['salarypaiddate']) === '0') {
            $employee['salarypaiddate'] = '';
        }
    }
    unset($employee);

    $html = renderBonusFormCHtml(
        $employees,
        $month,
        getBonusFormCCompanyName($dbconn, $companyId)
    );
    $html = rightAlignOtherReportAmountColumns($html);
    // Column widths are defined once by renderBonusFormCHtml() and applied to
    // its colgroup, headers, numbering row, and every body cell. Do not replace
    // that grid here, otherwise TCPDF can calculate a different body layout.
    // Keep an explicit HTML border as well as the CSS borders. TCPDF supports
    // the table border attribute consistently, including around the outer edge.
    $html = str_replace(
        '<table class="main">',
        '<table class="main" border="1" cellspacing="0" cellpadding="3">',
        $html
    );
    // Keep the rows in the normal table flow, as generateFormCReportPDF.php
    // does. Wrapping them in THEAD makes TCPDF repeat the complete three-row
    // heading at every automatic page break.
    $html = str_replace(
        '</style>',
        '.main{width:100%;table-layout:fixed}.main th{font-weight:bold!important}' .
            '.main th strong,.main thead td{font-weight:bold}.main th,.main td{padding:3px;font-size:9px}' .
            '</style>',
        $html
    );
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}
ob_clean();
$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('Form C - Register of Bonus');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage('L', 'LEGAL');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Form-C-Register-of-Bonus.pdf', 'I');
