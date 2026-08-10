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

$sheet->setCellValue('G1', 'Date: ' . date('d-m-Y'));
$sheet->setCellValue('A3', 'SUB :ADVANCE SHEET');
$sheet->setCellValue('A4', 'SITE :' . $companyName);
$sheet->setCellValue('A5', 'Month : ' . $monthLabel);
// . ' - ' . $bankName . ' - Bank Payment'
$sheet->fromArray(array('Sr. No.', 'Beneficiary Account Number', 'Amount', 'Beneficiary Name','Beneficiary Address', 'IFSC Code','Comm.'), null, 'A6');

$sheet->getStyle('A3:A5')->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('G1')->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A6:G6')->applyFromArray(array(
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
$totalCommission = 0;
while ($result && $row = mysqli_fetch_assoc($result)) {
    $amount = (float) $row['iAmount'];
    $commission = $amount <= 10000 ? 2.36 : 4.72;
    $accountNumber = str_replace(array('A/C. ', 'A/C', '.'), '', trim($row['accountno']));

    $sheet->setCellValue('A' . $rowNumber, ' ' . $serial . ' ');
    $sheet->setCellValueExplicit('B' . $rowNumber, $accountNumber, DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNumber, $amount);
    $sheet->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])));
    $sheet->setCellValue('E' . $rowNumber, '');
    $sheet->setCellValue('F' . $rowNumber, trim($row['ifsccode']));
    $sheet->setCellValue('G' . $rowNumber, $commission);
    $sheet->getStyle('A' . $rowNumber . ':G' . $rowNumber)->getFont()->setSize(10);
    $sheet->getStyle('A' . $rowNumber . ':G' . $rowNumber)->getAlignment()->setWrapText(true);
    $sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);

    $total += $amount;
    $totalCommission += $commission;
    $serial++;
    $rowNumber++;
}

$sheet->setCellValue('B' . $rowNumber, 'Total Amt.');
$sheet->setCellValue('C' . $rowNumber, $total);
$sheet->setCellValue('G' . $rowNumber, $totalCommission);
$sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle('A' . $rowNumber . ':G' . $rowNumber)->applyFromArray(array(
    'font' => array('bold' => true, 'size' => 10),
    'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'CCCCCC')),
));
$sheet->getRowDimension($rowNumber)->setRowHeight(30);

$sheet->getStyle('A6:G' . $rowNumber)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)
    ->getColor()->setRGB('000000');

$amountTableRow = $rowNumber + 2;
$sheet->fromArray(array(
    array('Amount', $total),
    array('Bank Comm.', $totalCommission),
    array('Total Amt.', $total + $totalCommission),
), null, 'B' . $amountTableRow);
$sheet->getStyle('B' . $amountTableRow . ':C' . ($amountTableRow + 2))->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)
    ->getColor()->setRGB('000000');
$sheet->getStyle('C' . $amountTableRow . ':C' . ($amountTableRow + 2))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle('B' . ($amountTableRow + 2) . ':C' . ($amountTableRow + 2))->getFont()->setBold(true);

$signatureRow = $amountTableRow;

$sheet->setCellValue('E' . $signatureRow, 'For, SHREE GANESH ENGINEERING CO.');
$sheet->getStyle('E' . $signatureRow)->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('E' . $signatureRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$partnerRow = $signatureRow + 2;
$sheet->setCellValue('E' . $partnerRow, 'HITESH.K.SHAH (PARTNER)');
$sheet->getStyle('E' . $partnerRow)->getFont()->setBold(true)->setSize(10);
$sheet->getStyle('E' . $partnerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

$chequeRow = $amountTableRow + 4;
$sheet->setCellValue('B' . $chequeRow, 'Cheque No.');
$sheet->getStyle('B' . $chequeRow)->getFont()->setBold(true);
$sheet->setCellValue('C' . $chequeRow, '');
$sheet->getStyle('C' . $chequeRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getRowDimension($chequeRow)->setRowHeight(25);

$noteRow = $chequeRow + 2;
$sheet->mergeCells('A' . $noteRow . ':G' . ($noteRow + 1));
$sheet->setCellValue('A' . $noteRow, 'Note: Soft Copy of the Bulk Transfer will be Sent from hkshah@sgeco.in and we are solely responsible for any discrepancy in the soft copy and the hard copy sent to you.');
$sheet->getStyle('A' . $noteRow)->getFont()->setBold(true)->setSize(9);
$sheet->getStyle('A' . $noteRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
$sheet->getRowDimension($noteRow)->setRowHeight(32);

$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(40);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(16);
$sheet->getColumnDimension('G')->setWidth(10);
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
$sheet->getPageSetup()->setPrintArea('A1:G' . ($noteRow + 1));
$sheet->getHeaderFooter()->setOddHeader('');
$spreadsheet->setActiveSheetIndex(0);

$temporaryFile = tempnam(sys_get_temp_dir(), 'advanced-payment-report-');
$writer = new Xlsx($spreadsheet);
$writer->save($temporaryFile);

// Included legacy files can emit whitespace, which would corrupt the XLSX ZIP response.
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
