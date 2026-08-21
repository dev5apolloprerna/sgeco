<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('BonusFormCReport.php');
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
    
    // Keep the PDF colgroup aligned with the explicit header-cell widths from
    // the renderer. TCPDF uses the width attributes on those cells (rather than
    // relying only on CSS colgroups), so the wider name columns are preserved.
    $pdfColumns = '<colgroup>' .
        '<col style="width:1.5%"><col style="width:16.5%"><col style="width:16%">' .
        '<col style="width:6.5%"><col style="width:5.5%"><col style="width:4%">' .
        '<col style="width:4.5%"><col style="width:6.5%"><col style="width:6.5%">' .
        '<col style="width:5.5%"><col style="width:5.5%"><col style="width:5.5%">' .
        '<col style="width:6%"><col style="width:4.5%"><col style="width:5.5%">' .
        '</colgroup>';
    $html = preg_replace('/<colgroup>.*?<\/colgroup>/s', $pdfColumns, $html, 1);
    // Keep an explicit HTML border as well as the CSS borders. TCPDF supports
    // the table border attribute consistently, including around the outer edge.
    $html = str_replace(
        '<table class="main">',
        '<table class="main" border="1" cellspacing="0" cellpadding="3">',
        $html
    );
    $html = preg_replace(
        '/(<table class="main"[^>]*><colgroup>.*?<\/colgroup>)(<tr>.*?<\/tr><tr>.*?<\/tr><tr>.*?<\/tr>)/s',
        '$1<thead>$2</thead><tbody>',
        $html,
        1
    );
    $html = str_replace('</table></body></html>', '</tbody></table></body></html>', $html);
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
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
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
