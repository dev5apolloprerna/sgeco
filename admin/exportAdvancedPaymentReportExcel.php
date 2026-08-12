<?php
ob_start();

ini_set('display_errors', '0');
ini_set('log_errors', '1');
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
$monthLabel = advancedPaymentReportMonthLabel($filters);
$bankFormat = advancedPaymentReportUsesBankFormat($filters);
$showTransferNote = advancedPaymentReportShowsTransferNote($filters);
$lastColumn = $bankFormat ? 'E' : 'G';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Worksheet');
$spreadsheet->getProperties()
    ->setTitle('Advanced Payment Bank Report')
    ->setSubject('Advanced Payment Bank Report')
    ->setDescription('Advanced payment report in bank payment sheet format.');

$sheet->setCellValue($lastColumn . '1', 'Date: ' . date('d-m-Y'));
$sheet->setCellValue('A3', 'SUB :ADVANCE SHEET');
$sheet->setCellValue('A4', 'SITE :' . $companyName);
$sheet->setCellValue('A5', 'Month : ' . $monthLabel . ' - ' . $bankName);
$headers = $bankFormat
    ? array('Sr. No.', 'Beneficiary Account Number', 'Amount', 'Beneficiary Name', 'IFSC Code')
    : array('Sr. No.', 'Beneficiary Account Number', 'Amount', 'Beneficiary Name', 'Beneficiary Address', 'IFSC Code', 'Comm.');
$sheet->fromArray($headers, null, 'A6');

$sheet->getStyle('A3:A5')->getFont()->setBold(true)->setSize(10);
$sheet->getStyle($lastColumn . '1')->getFont()->setBold(true)->setSize(10);
$sheet->getStyle($lastColumn . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A6:' . $lastColumn . '6')->applyFromArray(array(
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
    if ($bankFormat) {
        $sheet->setCellValue('E' . $rowNumber, trim($row['ifsccode']));
    } else {
        $sheet->setCellValue('E' . $rowNumber, '');
        $sheet->setCellValue('F' . $rowNumber, trim($row['ifsccode']));
        $sheet->setCellValue('G' . $rowNumber, $commission);
        $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    }
    $sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->getFont()->setSize(10);
    $sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->getAlignment()->setWrapText(true);
    $sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);

    $total += $amount;
    if (!$bankFormat) {
        $totalCommission += $commission;
    }
    $serial++;
    $rowNumber++;
}

$sheet->setCellValue('B' . $rowNumber, 'Total Amt.');
$sheet->setCellValue('C' . $rowNumber, $total);
$sheet->setCellValue($lastColumn . $rowNumber, $bankFormat ? '' : $totalCommission);
$sheet->getStyle('C' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
if (!$bankFormat) {
    $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
}
$sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->applyFromArray(array(
    'font' => array('bold' => true, 'size' => 10),
    'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'CCCCCC')),
));
$sheet->getRowDimension($rowNumber)->setRowHeight(30);

$sheet->getStyle('A6:' . $lastColumn . $rowNumber)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)
    ->getColor()->setRGB('000000');

$amountTableRow = $rowNumber + 2;
if (!$bankFormat) {
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
}
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

$lastPrintRow = $chequeRow;
if ($showTransferNote) {
    $noteRow = $chequeRow + 2;
    $sheet->mergeCells('A' . $noteRow . ':' . $lastColumn . ($noteRow + 1));
    $sheet->setCellValue('A' . $noteRow, 'Note: Soft Copy of the Bulk Transfer will be Sent from hkshah@sgeco.in and we are solely responsible for any discrepancy in the soft copy and the hard copy sent to you.');
    $sheet->getStyle('A' . $noteRow)->getFont()->setBold(true)->setSize(9);
    $sheet->getStyle('A' . $noteRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    $sheet->getRowDimension($noteRow)->setRowHeight(32);
    $lastPrintRow = $noteRow + 1;
}

$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(40);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(16);
$sheet->getColumnDimension('G')->setWidth(10);
$sheet->getColumnDimension('D')->setWidth($bankFormat ? 45 : 40);
$sheet->getColumnDimension('E')->setWidth($bankFormat ? 18 : 20);
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
$sheet->getPageSetup()->setPrintArea('A1:' . $lastColumn . $lastPrintRow);
$sheet->getHeaderFooter()->setOddHeader('');
$spreadsheet->setActiveSheetIndex(0);

// $temporaryFile = tempnam(sys_get_temp_dir(), 'advanced-payment-report-');
$temporaryFile = tempnam(sys_get_temp_dir(), 'advanced-payment-report-');

try {
    $writer = new Xlsx($spreadsheet);
    $writer->save($temporaryFile);

    // Verify the XLSX file was actually created
    if (!file_exists($temporaryFile) || filesize($temporaryFile) === 0) {
        throw new Exception('Excel file was not generated.');
    }

    // Remove ALL buffered output before sending XLSX
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Clear any existing output headers
    header_remove();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header(
        'Content-Disposition: attachment; filename="AdvancedPaymentReport_' .
        date('Ymd') .
        '.xlsx"'
    );
    header('Content-Length: ' . filesize($temporaryFile));
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');

    readfile($temporaryFile);

} catch (Throwable $e) {

    error_log(
        'Advanced Payment Excel Error: ' .
        $e->getMessage() .
        ' in ' .
        $e->getFile() .
        ':' .
        $e->getLine()
    );

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);
    echo 'Unable to generate Excel report.';
}

if (file_exists($temporaryFile)) {
    unlink($temporaryFile);
}

exit;
$writer = new Xlsx($spreadsheet);
$writer->save($temporaryFile);

$fileContent = file_get_contents($temporaryFile);

error_log('Excel file size: ' . strlen($fileContent));
error_log('Excel first bytes: ' . bin2hex(substr($fileContent, 0, 10)));

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
