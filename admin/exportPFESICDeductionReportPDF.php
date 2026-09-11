<?php
ob_start();
ini_set('display_errors', '0');
include '../common.php';
include 'IsLogin.php';
require_once 'PFESICDeductionReportData.php';
require_once 'tcpdf/config/tcpdf_config.php';
require_once 'tcpdf/tcpdf.php';

$report = pfEsicReportData(
    $dbconn,
    isset($_GET['month']) ? $_GET['month'] : '',
    isset($_GET['year']) ? $_GET['year'] : '',
    isset($_GET['employee']) ? $_GET['employee'] : ''
);
if (!$report['companies'] || !$report['employees']) {
    http_response_code(404);
    exit('No paid salary data found for the selected filters.');
}

function pfEsicPdfSection($report, $employees, $sectionTitle, $companies, $part, $partCount, $isAadhar)
{
    $e = 'pfEsicReportEscape';
    $date = DateTime::createFromFormat('!m/Y', $report['period']);
    $identity = $isAadhar
        ? array('Name as per Aadhar', 'Father Name', 'Aadhar No.', 'ESIC No.', 'D.O.B')
        : array('Name', 'PF A/C No.', 'UAN No.', 'ESIC No.', 'D.O.B');
    $widths = array(3, 11, 7, 8, 7, 6);
    $companyWidth = 16;
    $totalWidth = 10;
    $html = '<style>table{border-collapse:collapse;table-layout:fixed;width:100%}th,td{border:0.2mm solid #333;text-align:center;vertical-align:middle;padding:2px;font-size:6.2pt}th{font-weight:bold}.title{background-color:#c9ffff;font-size:11pt}.company{background-color:#d9e2f3}.total{background-color:#9fd8f6;font-weight:bold}.name{text-align:left}</style>';
    $html .= '<table border="1" cellpadding="2"><thead>';
    $columnCount = 6 + count($companies) * 5 + 4;
    $partLabel = $partCount > 1 ? ' &mdash; Companies ' . $part . ' of ' . $partCount : '';
    $html .= '<tr><th class="title" colspan="' . $columnCount . '">PF &amp; ESIC Deduction Report</th></tr>';
    $html .= '<tr><th colspan="' . $columnCount . '">' . $e($sectionTitle) . ' | Month: ' . $e($date ? $date->format('M-Y') : $report['period']) . $partLabel . '</th></tr>';
    $html .= '<tr><th width="' . $widths[0] . '%" rowspan="2">Sr.<br>No.</th>';
    foreach ($identity as $index => $label) {
        $html .= '<th width="' . $widths[$index + 1] . '%" rowspan="2">' . $e($label) . '</th>';
    }
    foreach ($companies as $name) {
        $html .= '<th class="company" width="' . $companyWidth . '%" colspan="5">' . $e($name) . '</th>';
    }
    $html .= '<th class="total" width="' . $totalWidth . '%" colspan="4">Total Deduction</th></tr><tr>';
    foreach ($companies as $unused) {
        foreach (array('Present Days', 'National Holiday', 'Wages Rate', 'PF Amt.', 'ESIC Amt.') as $label) {
            $html .= '<th class="company" width="' . ($companyWidth / 5) . '%">' . $label . '</th>';
        }
    }
    foreach (array('Present Days', 'National Holiday', 'PF Amt.', 'ESIC Amt.') as $label) {
        $html .= '<th class="total" width="' . ($totalWidth / 4) . '%">' . $label . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    $serial = 1;
    foreach ($employees as $employee) {
        $identityValues = $isAadhar
            ? array($employee['name'], $employee['fatherName'], $employee['aadharNo'], $employee['esicNo'], $employee['dob'])
            : array($employee['name'], $employee['pfAccount'], $employee['uan'], $employee['esicNo'], $employee['dob']);
        $html .= '<tr nobr="true"><td width="' . $widths[0] . '%">' . $serial++ . '</td>';
        foreach ($identityValues as $index => $value) {
            $class = $index === 0 ? ' class="name"' : '';
            $html .= '<td' . $class . ' width="' . $widths[$index + 1] . '%">' . $e($value) . '</td>';
        }
        foreach ($companies as $companyId => $unused) {
            $values = isset($employee['companies'][$companyId])
                ? $employee['companies'][$companyId]
                : array('presentDays' => 0, 'nationalHoliday' => 0, 'wagesRate' => 0, 'pfAmount' => 0, 'esicAmount' => 0);
            foreach (array('presentDays', 'nationalHoliday', 'wagesRate', 'pfAmount', 'esicAmount') as $key) {
                $html .= '<td width="' . ($companyWidth / 5) . '%">' . pfEsicReportNumber($values[$key], in_array($key, array('wagesRate', 'pfAmount', 'esicAmount'))) . '</td>';
            }
        }
        foreach (array($employee['totalDays'], $employee['totalNationalHoliday'], $employee['totalPf'], $employee['totalEsic']) as $index => $value) {
            $html .= '<td class="total" width="' . ($totalWidth / 4) . '%">' . pfEsicReportNumber($value, $index > 1) . '</td>';
        }
        $html .= '</tr>';
    }
    $totals = pfEsicReportTotals($employees, $companies);
    $html .= '<tr nobr="true"><td class="total" colspan="6">Total</td>';
    foreach ($totals['companies'] as $values) {
        foreach (array('presentDays', 'nationalHoliday', 'wagesRate', 'pfAmount', 'esicAmount') as $key) {
            $html .= '<td class="total" width="' . ($companyWidth / 5) . '%">' . pfEsicReportNumber($values[$key]) . '</td>';
        }
    }
    foreach (array($totals['totalDays'], $totals['totalNationalHoliday'], $totals['totalPf'], $totals['totalEsic']) as $value) {
        $html .= '<td class="total" width="' . ($totalWidth / 4) . '%">' . pfEsicReportNumber($value) . '</td>';
    }
    $html .= '</tr>';
    return $html . '</tbody></table>';
}

while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf = new TCPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('SGECO');
$pdf->SetTitle('PF & ESIC Deduction Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);
$pdf->SetFont('helvetica', '', 6.2);

// Three companies plus identity and total columns fit within legal landscape.
// Additional companies are rendered in horizontal continuation parts so no
// dynamically generated company columns can run beyond the printable page.
$companyParts = array_chunk($report['companies'], 3, true);
$sections = array(
    array($report['pfEmployees'], 'PF Employees', false),
    array($report['aadharEmployees'], 'Aadhar Employees', true)
);
foreach ($sections as $section) {
    if (!$section[0]) {
        continue;
    }
    foreach ($companyParts as $partIndex => $companies) {
        $pdf->AddPage('L', 'LEGAL');
        $pdf->writeHTML(
            pfEsicPdfSection($report, $section[0], $section[1], $companies, $partIndex + 1, count($companyParts), $section[2]),
            true,
            false,
            true,
            false,
            ''
        );
    }
}
$pdf->Output('PF_ESIC_Deduction_' . str_replace('/', '-', $report['period']) . '.pdf', 'I');
exit;