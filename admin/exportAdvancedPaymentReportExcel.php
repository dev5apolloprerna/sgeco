<?php
ob_start();
ini_set('display_errors', '0');
require '../vendor/autoload.php';
include('../common.php');
include('IsLogin.php');
include_once 'advancedPaymentReportData.php';
requireAdvancedPaymentReportAccess($dbconn);

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filters = advancedPaymentReportFilters($dbconn, $_GET);
$result = mysqli_query($dbconn, advancedPaymentReportQuery(advancedPaymentReportWhere($dbconn, $filters)));

function advancedPaymentReportLookupName($dbconn, $table, $idColumn, $nameColumn, $id, $fallback)
{
    if ($id <= 0) {
        return $fallback;
    }

    $query = "SELECT " . $nameColumn . " FROM " . $table . " WHERE " . $idColumn . "=" . (int) $id . " AND isDelete=0 AND istatus=1";
    $lookup = mysqli_query($dbconn, $query);
    $row = $lookup ? mysqli_fetch_assoc($lookup) : null;
    return $row ? $row[$nameColumn] : $fallback;
}

$companyName = advancedPaymentReportLookupName($dbconn, 'companymaster', 'companymasterId', 'companyname', $filters['companyId'], 'All Companies');
$bankName = advancedPaymentReportLookupName($dbconn, 'bankmaster', 'bankmasterId', 'bankname', $filters['bankId'], 'All Banks');
$monthLabel = 'All Dates';
if ($filters['month'] !== '' && $filters['year'] !== '') {
    $monthLabel = DateTime::createFromFormat('!m/Y', $filters['month'] . '/' . $filters['year'])->format('F-Y');
} elseif ($filters['month'] !== '') {
    $monthLabel = DateTime::createFromFormat('!m', $filters['month'])->format('F') . ' (All Years)';
} elseif ($filters['year'] !== '') {
    $monthLabel = $filters['year'];
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Worksheet');
$spreadsheet->getProperties()
    ->setTitle('Advanced Payment Bank Report')
    ->setSubject('Advanced Payment Bank Report')
    ->setDescription('Advanced payment report in bank payment sheet format.');

$sheet->setCellValue('E1', 'Date: ' . date('d-m-Y'));
$sheet->setCellValue('A3', 'SUB :PAYMENT SHEET');
$sheet->setCellValue('A4', 'SITE :' . $companyName);
$sheet->setCellValue('A5', 'Month : ' . $monthLabel . ' - ' . $bankName . ' - Bank Payment');
$sheet->fromArray(array('Sr. No.', 'Beneficiary Account Number', 'Amount', 'Beneficiary Name', 'IFSC Code'), null, 'A6');

$sheet->getStyle('A3:A5')->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('E1')->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A6:E6')->applyFromArray(array(
    'font' => array('bold' => true, 'size' => 10),
    'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'CCCCCC')),
    'alignment' => array(
        'wrapText' => true,
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ),
));
$sheet->getRowDimension(1)->setRowHeight(21);
$sheet->getRowDimension(3)->setRowHeight(21);
$sheet->getRowDimension(4)->setRowHeight(21);
$sheet->getRowDimension(5)->setRowHeight(20);
$sheet->getRowDimension(6)->setRowHeight(33);

$rowNumber = 7;
$serial = 1;
$total = 0;
while ($result && $row = mysqli_fetch_assoc($result)) {
    $amount = (float) $row['iAmount'];
    $accountNumber = str_replace(array('A/C. ', 'A/C', '.'), '', trim($row['accountno']));

    $sheet->setCellValue('A' . $rowNumber, ' ' . $serial . ' ');
    $sheet->setCellValueExplicit('B' . $rowNumber, $accountNumber, DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNumber, $amount);
    $sheet->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])));
    $sheet->setCellValue('E' . $rowNumber, trim($row['ifsccode']));
    $sheet->getStyle('A' . $rowNumber . ':E' . $rowNumber)->getFont()->setSize(10);
    $sheet->getStyle('A' . $rowNumber . ':E' . $rowNumber)->getAlignment()->setWrapText(true);
    $sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);

    $total += $amount;
    $serial++;
    $rowNumber++;
}
$sheet->setCellValue('B' . $rowNumber, 'Total Amt.');
$sheet->setCellValue('C' . $rowNumber, $total);
$sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle('A' . $rowNumber . ':E' . $rowNumber)->applyFromArray(array(
    'font' => array('bold' => true, 'size' => 10),
    'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'CCCCCC')),
));
$sheet->getRowDimension($rowNumber)->setRowHeight(30);

$sheet->getStyle('A6:E' . $rowNumber)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)
    ->getColor()->setRGB('000000');

$signatureRow = $rowNumber + 2;
$sheet->setCellValue('E' . $signatureRow, 'For, SHREE GANESH ENGINEERING CO.');
$sheet->getStyle('E' . $signatureRow)->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('E' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$partnerRow = $signatureRow + 2;
$sheet->setCellValue('E' . $partnerRow, 'HITESH.K.SHAH (PARTNER)');
$sheet->getStyle('E' . $partnerRow)->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('E' . $partnerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$chequeRow = $partnerRow + 1;
$sheet->setCellValue('B' . $chequeRow, 'Cheque No');
$sheet->getStyle('B' . $chequeRow)->getFont()->setBold(true);
$sheet->setCellValue('C' . $chequeRow, '');
$sheet->getStyle('C' . $chequeRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(40);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getDefaultStyle()->getFont()->setSize(10);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
$sheet->getPageMargins()->setTop(2.5);
$sheet->getHeaderFooter()->setOddHeader('');
$spreadsheet->setActiveSheetIndex(0);

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
