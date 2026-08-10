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
function advancedPaymentPdfLookupName($dbconn, $table, $idColumn, $nameColumn, $id, $fallback)
{
    if ($id <= 0) {
        return $fallback;
    }

    $query = "SELECT " . $nameColumn . " FROM " . $table . " WHERE " . $idColumn . "=" . (int) $id . " AND isDelete=0 AND istatus=1";
    $lookup = mysqli_query($dbconn, $query);
    $row = $lookup ? mysqli_fetch_assoc($lookup) : null;
    return $row ? $row[$nameColumn] : $fallback;
}

function advancedPaymentPdfEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$companyName = advancedPaymentPdfLookupName($dbconn, 'companymaster', 'companymasterId', 'companyname', $filters['companyId'], 'All Companies');
$bankName = advancedPaymentPdfLookupName($dbconn, 'bankmaster', 'bankmasterId', 'bankname', $filters['bankId'], 'All Banks');
$dateLabel = advancedPaymentReportDateLabel($filters);
$bankFormat = advancedPaymentReportUsesBankFormat($filters);

$html = '<table width="100%" cellspacing="0" cellpadding="5" border="0" style="font-size:18px;color:#000">';
$html .= '<tr><td align="right"><strong>Date : ' . date('d-m-Y') . '</strong></td></tr>';
$html .= '<tr><td><strong>SUB : ADVANCE SHEET<br>SITE : ' . advancedPaymentPdfEscape($companyName) . '<br>Date Range : ' . advancedPaymentPdfEscape($dateLabel) . ' - ' . advancedPaymentPdfEscape($bankName) . ' - Bank Payment</strong></td></tr>';
$html .= '<tr><td><table width="100%" cellspacing="0" cellpadding="5" border="1" style="font-size:18px;color:#000">';
$html .= '<thead><tr style="background-color:#ccc;font-size:16px;font-weight:bold;text-align:center">';
$html .= $bankFormat
    ? '<th width="7%">Sr.<br>No.</th><th width="23%">Beneficiary Account<br>Number</th><th width="15%">Amount</th><th width="35%">Beneficiary Name</th><th width="20%">IFSC Code</th>'
    : '<th width="5%">Sr.<br>No.</th><th width="18%">Beneficiary Account<br>Number</th><th width="11%">Amount</th><th width="25%">Beneficiary Name</th><th width="19%">Beneficiary Address</th><th width="14%">IFSC Code</th><th width="8%">Comm.</th>';
$html .= '</tr></thead><tbody>';

$serial = 1;
$total = 0;
$totalCommission = 0;
while ($result && $row = mysqli_fetch_assoc($result)) {
    $amount = (float) $row['iAmount'];
    $commission = $amount <= 10000 ? 2.36 : 4.72;
    $accountNumber = str_replace(array('A/C. ', 'A/C', '.'), '', trim($row['accountno']));
    $html .= '<tr>';
    $html .= '<td width="' . ($bankFormat ? '7' : '5') . '%" align="center">' . $serial . '</td>';
    $html .= '<td width="' . ($bankFormat ? '23' : '18') . '%">' . advancedPaymentPdfEscape($accountNumber) . '</td>';
    $html .= '<td width="' . ($bankFormat ? '15' : '11') . '%" align="right">' . number_format($amount, 2) . '</td>';
    $html .= '<td width="' . ($bankFormat ? '35' : '25') . '%">' . advancedPaymentPdfEscape(ucwords(strtolower($row['emp_name']))) . '</td>';
    if (!$bankFormat) {
        $html .= '<td width="19%"></td>';
    }
    $html .= '<td width="' . ($bankFormat ? '20' : '14') . '%">' . advancedPaymentPdfEscape(trim($row['ifsccode'])) . '</td>';
    if (!$bankFormat) {
        $html .= '<td width="8%" align="right">' . number_format($commission, 2) . '</td>';
    }
    $html .= '</tr>';
    $total += $amount;
    if (!$bankFormat) {
        $totalCommission += $commission;
    }
    $serial++;
}
$html .= $bankFormat
    ? '<tr style="background-color:#ccc;font-weight:bold"><td colspan="2" align="center">Total Amt.</td><td align="right">' . number_format($total, 2) . '</td><td colspan="2"></td></tr>'
    : '<tr style="background-color:#ccc;font-weight:bold"><td colspan="2" align="center">Total Amt.</td><td align="right">' . number_format($total, 2) . '</td><td colspan="3"></td><td align="right">' . number_format($totalCommission, 2) . '</td></tr>';
$html .= '</tbody></table></td></tr>';
$html .= '<tr><td>&nbsp;</td></tr><tr><td><table width="100%" cellspacing="0" cellpadding="5" border="0">';
$summary = $bankFormat ? '' : '<table width="100%" cellspacing="0" cellpadding="5" border="1"><tr><td>Amount</td><td align="right">' . number_format($total, 2) . '</td></tr><tr><td>Bank Comm.</td><td align="right">' . number_format($totalCommission, 2) . '</td></tr><tr style="font-weight:bold"><td>Total Amt.</td><td align="right">' . number_format($total + $totalCommission, 2) . '</td></tr></table>';
$html .= '<tr><td width="38%">' . $summary . '</td><td width="62%" align="right"><strong>FOR, SHREE GANESH ENGINEERING CO.</strong><br><br><br>HITESH.K.SHAH (PARTNER)</td></tr>';
$html .= '<tr><td width="38%"><br><table width="100%" cellspacing="0" cellpadding="5" border="0"><tr><td width="45%"><strong>Cheque No.</strong></td><td width="55%" height="28" border="1">&nbsp;</td></tr></table></td><td width="62%"></td></tr></table></td></tr>';
$html .= '<tr><td style="font-size:15px"><strong>Note:</strong> Soft Copy of the Bulk Transfer will be Sent from <strong>hkshah@sgeco.in</strong> and we are solely responsible for any discrepancy in the soft copy and the hard copy sent to you.</td></tr>';
$html .= '</table>';

$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);
$pdf->SetCreator($ProjectName);
$pdf->SetTitle('Advanced Payment Report');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(PDF_MARGIN_LEFT, 105, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 8);
$pdf->writeHTML($html, true, false, false, false, '');

while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('AdvancedPaymentReport_' . date('Ymd') . '.pdf', 'I');
exit;
