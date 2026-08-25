<?php
ob_start();
require '../vendor/autoload.php';
include('../common.php');
include('IsLogin.php');
include_once 'summaryAdvancePaymentData.php';
requireAdvancedPaymentReportAccess($dbconn);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$report = summaryAdvancePaymentData($dbconn, $_GET);
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Summary Advance Payment');
$lastIndex = count($report['columns']) + 3;
$lastColumn = Coordinate::stringFromColumnIndex($lastIndex);
$sheet->setCellValue('A1', 'SUB: Summary Advance Payment');
$sheet->mergeCells('A1:' . $lastColumn . '1');
$sheet->setCellValue('A2', 'SITE: ' . implode(', ', $report['sites']));
$sheet->mergeCells('A2:' . $lastColumn . '2');
$sheet->setCellValue('A3', 'MONTH: ' . summaryAdvancePaymentMonth($report['filters']));
$sheet->mergeCells('A3:' . $lastColumn . '3');
$sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);

$sheet->setCellValue('A5', 'Sr. No.')->setCellValue('B5', 'Name');
$sheet->mergeCells('A5:A6')->mergeCells('B5:B6');
$columnIndex = 3;
foreach ($report['dates'] as $date => $keys) {
    $start = Coordinate::stringFromColumnIndex($columnIndex);
    $end = Coordinate::stringFromColumnIndex($columnIndex + count($keys) - 1);
    $sheet->setCellValue($start . '5', date('d-m-Y', strtotime($date)));
    if ($start !== $end) $sheet->mergeCells($start . '5:' . $end . '5');
    foreach ($keys as $key) {
        $letter = Coordinate::stringFromColumnIndex($columnIndex++);
        $sheet->setCellValue($letter . '6', $report['columns'][$key]['company']);
    }
}
$sheet->setCellValue($lastColumn . '5', 'Total Advance')->mergeCells($lastColumn . '5:' . $lastColumn . '6');
$sheet->getStyle('A5:' . $lastColumn . '6')->getFont()->setBold(true);
$sheet->getStyle('A5:B6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D0D0D0');
$dateEnd = Coordinate::stringFromColumnIndex(max(3, $lastIndex - 1));
$sheet->getStyle('C5:' . $dateEnd . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('70D5ED');
$sheet->getStyle('C6:' . $dateEnd . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('A9DF73');
$sheet->getStyle($lastColumn . '5:' . $lastColumn . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('00ED18');

$row = 7;
$serial = 1;
foreach ($report['groups'] as $group => $employees) {
    $sheet->setCellValue('B' . $row, $group)->mergeCells('B' . $row . ':' . $lastColumn . $row);
    $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2F3');
    $sheet->getStyle('B' . $row)->getFont()->setBold(true)->setItalic(true)->getColor()->setRGB('9C2525');
    $row++;
    foreach ($employees as $employee) {
        $sheet->setCellValue('A' . $row, $serial++)->setCellValue('B' . $row, $employee['name']);
        $columnIndex = 3;
        foreach ($report['columns'] as $key => $column) {
            if (isset($employee['amounts'][$key])) $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex) . $row, $employee['amounts'][$key]);
            $columnIndex++;
        }
        $sheet->setCellValue($lastColumn . $row, $employee['total']);
        $row++;
    }
}
$sheet->setCellValue('A' . $row, 'Total')->mergeCells('A' . $row . ':B' . $row);
$columnIndex = 3;
foreach ($report['columns'] as $key => $column) $sheet->setCellValue(Coordinate::stringFromColumnIndex($columnIndex++) . $row, $report['columnTotals'][$key]);
$sheet->setCellValue($lastColumn . $row, $report['grandTotal']);
$sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFont()->setBold(true);
$sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('00ED18');
$sheet->getStyle('A5:' . $lastColumn . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('A5:' . $lastColumn . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
$sheet->getStyle('A5:' . $lastColumn . '6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('C7:' . $lastColumn . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('C7:' . $lastColumn . $row)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getRowDimension(5)->setRowHeight(24);
$sheet->getRowDimension(6)->setRowHeight(24);
for ($dataRow = 7; $dataRow <= $row; $dataRow++) $sheet->getRowDimension($dataRow)->setRowHeight(22);
$sheet->getRowDimension($row)->setRowHeight(26);
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(32);
for ($i = 3; $i <= $lastIndex; $i++) $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(15);
$sheet->freezePane('C7');
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setFitToWidth(1)->setFitToHeight(0);
$sheet->getPageSetup()->setPrintArea('A1:' . $lastColumn . $row);

$file = tempnam(sys_get_temp_dir(), 'summary-advance-');
$writer = new Xlsx($spreadsheet);
$writer->save($file);
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="SummaryAdvancePayment_' . date('Ymd') . '.xlsx"');
header('Content-Length: ' . filesize($file));
readfile($file);
unlink($file);
exit;
