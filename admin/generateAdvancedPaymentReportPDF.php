<?php
ob_start();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
include('../common.php');
include('IsLogin.php');
include_once 'advancedPaymentReportData.php';
requireAdvancedPaymentReportAccess($dbconn);

$filters = advancedPaymentReportFilters($dbconn, $_GET);
$result = mysqli_query($dbconn, advancedPaymentReportQuery(advancedPaymentReportWhere($dbconn, $filters)));
$html = '<h2 style="text-align:center">Advanced Payment Report</h2><table border="1" cellpadding="4"><thead><tr style="font-weight:bold;background-color:#eeeeee"><th width="5%">#</th><th width="10%">Date</th><th width="10%">Code</th><th width="15%">Employee</th><th width="15%">Company</th><th width="12%">Bank</th><th width="14%">Account</th><th width="9%">IFSC</th><th width="10%">Amount</th></tr></thead><tbody>';
$serial = 1;
$total = 0;
while ($result && $row = mysqli_fetch_assoc($result)) {
    $total += (float) $row['iAmount'];
    $values = array($serial++, date('d-m-Y', strtotime($row['strDate'])), $row['employeecode'], ucwords(strtolower($row['emp_name'])), $row['companyname'], $row['bankname'], str_replace('A/C. ', '', $row['accountno']), $row['ifsccode'], number_format($row['iAmount'], 2));
    $widths = array('5%', '10%', '10%', '15%', '15%', '12%', '14%', '9%', '10%');
    $html .= '<tr>';
    foreach ($values as $index => $value) {
        $html .= '<td width="' . $widths[$index] . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td>';
    }
    $html .= '</tr>';
}
$html .= '<tr style="font-weight:bold"><td colspan="8" align="right">Total</td><td>' . number_format($total, 2) . '</td></tr></tbody></table>';
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);
$pdf->SetCreator($ProjectName);
$pdf->SetTitle('Advanced Payment Report');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 8);
$pdf->writeHTML($html, true, false, false, false, '');
ob_end_clean();
$pdf->Output('AdvancedPaymentReport_' . date('Ymd') . '.pdf', 'I');
exit;
