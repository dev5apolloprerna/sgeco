<?php
ob_start();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
include('../common.php');
include('IsLogin.php');
include_once 'summaryAdvancePaymentData.php';
requireAdvancedPaymentReportAccess($dbconn);
$report = summaryAdvancePaymentData($dbconn, $_GET);
function summaryPdfEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
$columnCount = max(1, count($report['columns']));
// $nameWidth = 18;
// $serialWidth = 4;
// $totalWidth = 8;
// $dataWidth = (70 / $columnCount);
$nameWidth = 20;
$serialWidth = 5;
$totalWidth = 10;
$dataWidth = (65 / $columnCount);
$html = '<div style="font-size:10px;font-weight:bold">SUB: Summary Advance Payment<br>SITE: ' . summaryPdfEscape(implode(', ', $report['sites'])) . '<br>MONTH: ' . summaryPdfEscape(summaryAdvancePaymentMonth($report['filters'])) . '</div><br>';
// $html .= '<table border="1" cellpadding="3" cellspacing="0" style="font-size:7px"><thead><tr style="text-align:center;font-weight:bold;background-color:#d0d0d0"><th rowspan="2" width="' . $serialWidth . '%">Sr.<br>No.</th><th rowspan="2" width="' . $nameWidth . '%">Name</th>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" style="font-size:7px"><thead><tr style="text-align:center;font-weight:bold;background-color:#d0d0d0"><th rowspan="2" width="' . $serialWidth . '%">Sr.<br>No.</th><th rowspan="2" width="' . $nameWidth . '%">Name</th>';
foreach ($report['dates'] as $date => $keys) $html .= '<th colspan="' . count($keys) . '" width="' . ($dataWidth * count($keys)) . '%" style="background-color:#70d5ed">' . date('d-m-Y', strtotime($date)) . '</th>';
$html .= '<th rowspan="2" width="' . $totalWidth . '%" style="background-color:#00ed18">Total<br>Advance</th></tr><tr style="text-align:center;font-weight:bold;background-color:#a9df73">';
foreach ($report['columns'] as $column) $html .= '<th width="' . $dataWidth . '%">' . summaryPdfEscape($column['company']) . '</th>';
$html .= '</tr></thead><tbody>';
$serial = 1;
foreach ($report['groups'] as $group => $employees) {
    // $html .= '<tr style="font-weight:bold;font-style:italic;color:#9c2525;background-color:#d9e2f3"><td width="' . $serialWidth . '%"></td><td width="' . $nameWidth . '%">' . summaryPdfEscape($group) . '</td><td colspan="' . ($columnCount + 1) . '" width="' . (70 + $totalWidth) . '%"></td></tr>';
    $html .= '<tr style="font-weight:bold;font-style:italic;color:#9c2525;background-color:#d9e2f3"><td width="' . $serialWidth . '%"></td><td width="' . $nameWidth . '%">' . summaryPdfEscape($group) . '</td><td colspan="' . ($columnCount + 1) . '" width="' . (65 + $totalWidth) . '%"></td></tr>';
    foreach ($employees as $employee) {
        $html .= '<tr><td width="' . $serialWidth . '%" align="center">' . $serial++ . '</td><td width="' . $nameWidth . '%">' . summaryPdfEscape($employee['name']) . '</td>';
        foreach ($report['columns'] as $key => $column) $html .= '<td width="' . $dataWidth . '%" align="right">' . (isset($employee['amounts'][$key]) ? summaryAdvancePaymentNumber($employee['amounts'][$key]) : '') . '</td>';
        $html .= '<td width="' . $totalWidth . '%" align="right"><b>' . summaryAdvancePaymentNumber($employee['total']) . '</b></td></tr>';
    }
}
// $html .= '<tr style="font-weight:bold;background-color:#00ed18"><td colspan="2" width="' . ($serialWidth + $nameWidth) . '%" align="center">Total</td>';
$html .= '<tr nobr="true" style="font-weight:bold;background-color:#00ed18"><td colspan="2" width="' . ($serialWidth + $nameWidth) . '%" align="center">Total</td>';
foreach ($report['columns'] as $key => $column) $html .= '<td width="' . $dataWidth . '%" align="right">' . summaryAdvancePaymentNumber($report['columnTotals'][$key]) . '</td>';
$html .= '<td width="' . $totalWidth . '%" align="right">' . summaryAdvancePaymentNumber($report['grandTotal']) . '</td></tr></tbody></table>';
$pdf = new TCPDF('L', PDF_UNIT, $columnCount > 12 ? 'A3' : 'A4', 'UTF-8', false);
$pdf->SetCreator($ProjectName);
$pdf->SetTitle('Summary Advance Payment');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(5, 7, 5);
$pdf->SetAutoPageBreak(true, 7);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 7);
$pdf->writeHTML($html, true, false, false, false, '');
while (ob_get_level()) ob_end_clean();
$pdf->Output('SummaryAdvancePayment_' . date('Ymd') . '.pdf', 'I');
exit;
