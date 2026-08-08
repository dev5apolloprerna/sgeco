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
$monthLabel = 'All Dates';
if ($filters['month'] !== '' && $filters['year'] !== '') {
    $monthLabel = DateTime::createFromFormat('!m/Y', $filters['month'] . '/' . $filters['year'])->format('M-Y');
} elseif ($filters['month'] !== '') {
    $monthLabel = DateTime::createFromFormat('!m', $filters['month'])->format('M') . ' (All Years)';
} elseif ($filters['year'] !== '') {
    $monthLabel = $filters['year'];
}

$html = '<table width="100%" cellspacing="0" cellpadding="5" border="0" style="font-size:18px;color:#000">';
$html .= '<tr><td align="right"><strong>Date : ' . date('d-m-Y') . '</strong></td></tr>';
$html .= '<tr><td><strong>SUB : ADVANCE SHEET<br>SITE : ' . advancedPaymentPdfEscape($companyName) . '<br>Month : ' . advancedPaymentPdfEscape($monthLabel) . '</strong></td></tr>';
//  - ' . advancedPaymentPdfEscape($bankName) . ' - Bank Payment
$html .= '<tr><td><table width="100%" cellspacing="0" cellpadding="5" border="1" style="font-size:18px;color:#000">';
$html .= '<thead><tr style="background-color:#ccc;font-size:16px;font-weight:bold;text-align:center">';
$html .= '<th width="7%">Sr.<br>No.</th><th width="22%">Beneficiary Account<br>Number</th><th width="12%">Amount</th><th width="43%">Beneficiary Name</th><th width="16%">IFSC Code</th>';
$html .= '</tr></thead><tbody>';

$serial = 1;
$total = 0;
while ($result && $row = mysqli_fetch_assoc($result)) {
    $amount = (float) $row['iAmount'];
    $accountNumber = str_replace(array('A/C. ', 'A/C', '.'), '', trim($row['accountno']));
    $html .= '<tr>';
    $html .= '<td width="7%" align="center">' . $serial . '</td>';
    $html .= '<td width="22%">' . advancedPaymentPdfEscape($accountNumber) . '</td>';
    $html .= '<td width="12%" align="right">' . number_format($amount, 2) . '</td>';
    $html .= '<td width="43%">' . advancedPaymentPdfEscape(ucwords(strtolower($row['emp_name']))) . '</td>';
    $html .= '<td width="43%">' . advancedPaymentPdfEscape(ucwords(strtolower($row['address']))) . '</td>';
    $html .= '<td width="16%">' . advancedPaymentPdfEscape(trim($row['ifsccode'])) . '</td>';
    $html .= '<td width="16%">' . advancedPaymentPdfEscape(trim($row['comm'] ?? '')) . '</td>';
    $html .= '</tr>';
    $total += $amount;
    $serial++;
}
$html .= '<tr style="background-color:#ccc;font-weight:bold"><td colspan="2" align="center">Total</td><td align="right">' . number_format($total, 2) . '</td><td></td><td></td></tr>';
$html .= '</tbody></table></td></tr>';
$html .= '<tr><td>&nbsp;</td></tr><tr><td align="right"><strong>FOR, SHREE GANESH ENGINEERING CO.</strong></td></tr>';
$html .= '<tr><td><br><br><br></td></tr>';
$html .= '<tr><td><table width="100%" cellspacing="0" cellpadding="5" border="0"><tr><td width="15%"><strong>Cheque no.</strong></td><td width="30%" border="1">&nbsp;</td><td width="15%"></td><td width="40%" align="right"><strong>HITESH.K.SHAH (PARTNER)</strong></td></tr></table></td></tr>';
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
