<?php
ob_start();
ini_set('display_errors', '0');
require '../vendor/autoload.php';
include('../common.php');
include('IsLogin.php');
include_once 'advancedPaymentReportData.php';
requireAdvancedPaymentReportAccess($dbconn);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filters = advancedPaymentReportFilters($dbconn, $_GET);
$result = mysqli_query($dbconn, advancedPaymentReportQuery(advancedPaymentReportWhere($dbconn, $filters)));
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Advanced Payments');
$sheet->fromArray(array('Sr. No.', 'Date', 'Employee Code', 'Beneficiary Name', 'Company', 'Bank', 'Account Number', 'IFSC Code', 'Amount', 'Remarks'), null, 'A1');
$sheet->getStyle('A1:J1')->getFont()->setBold(true);
$rowNumber = 2;
$serial = 1;
$total = 0;
while ($result && $row = mysqli_fetch_assoc($result)) {
    $amount = (float) $row['iAmount'];
    $total += $amount;
    $sheet->fromArray(array($serial++, date('d-m-Y', strtotime($row['strDate'])), $row['employeecode'], ucwords(strtolower($row['emp_name'])), $row['companyname'], $row['bankname'], str_replace('A/C. ', '', $row['accountno']), $row['ifsccode'], $amount, $row['strRemarks']), null, 'A' . $rowNumber++);
}
$sheet->setCellValue('H' . $rowNumber, 'Total')->setCellValue('I' . $rowNumber, $total);
$sheet->getStyle('H' . $rowNumber . ':I' . $rowNumber)->getFont()->setBold(true);
$sheet->getStyle('I2:I' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
foreach (range('A', 'J') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

$temporaryFile = tempnam(sys_get_temp_dir(), 'advanced-payment-report-');
$writer = new Xlsx($spreadsheet);
$writer->save($temporaryFile);

// common.php starts an output buffer and included files may emit whitespace.
// Any bytes before the ZIP signature make the XLSX package invalid in Excel.
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="AdvancedPaymentReport_' . date('Ymd') . '.xlsx"');
header('Cache-Control: max-age=0');
header('Content-Length: ' . filesize($temporaryFile));
readfile($temporaryFile);
unlink($temporaryFile);
exit;
