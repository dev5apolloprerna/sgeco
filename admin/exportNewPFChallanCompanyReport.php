<?php
ob_start();
include('../config.php');
require_once('../vendor/autoload.php');
require_once('NewPFChallanReportData.php');

try {
    $report = getNewPFChallanReportData($dbconn, isset($_GET['month']) ? $_GET['month'] : 0, isset($_GET['Year']) ? $_GET['Year'] : 0);
} catch (Exception $exception) {
    http_response_code(400);
    exit($exception->getMessage());
}

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('New PF Challan Report');
$companyCount = count($report['companies']);
$lastColumnNumber = 6 + ($companyCount * 4) + 7;
$lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnNumber);
$sheet->mergeCells('A1:' . $lastColumn . '1')->setCellValue('A1', 'FOR EPF & ESIC ONLY');
$sheet->mergeCells('A2:' . $lastColumn . '2')->setCellValue('A2', 'SHREE GANESH ENGINEERING CO.');
$sheet->mergeCells('A3:' . $lastColumn . '3')->setCellValue('A3', 'P.F.CODE : GJ 16240    GROUP : IV');
$sheet->mergeCells('A4:' . $lastColumn . '4')->setCellValue('A4', 'Employee List of PF and ESIC For the Month of : ' . date('F-Y', strtotime('01-' . str_replace('/', '-', $report['salaryMonth']))));
$fixed = array('Sr. No.', 'Name as per Aadhar', 'PF. No.', 'UAN No.', 'ESIC No.', 'D.O.B');
foreach ($fixed as $index => $heading) {
    $column = $index + 1;
    $sheet->mergeCellsByColumnAndRow($column, 5, $column, 6)->setCellValueByColumnAndRow($column, 5, $heading);
}
$column = 7;
foreach ($report['companies'] as $companyName) {
    $sheet->mergeCellsByColumnAndRow($column, 5, $column + 3, 5)->setCellValueByColumnAndRow($column, 5, $companyName);
    foreach (array('Present Days', 'National Holiday', 'Wages Rate', 'Difference in ESIC (Bonus + Leave)') as $heading) {
        $sheet->setCellValueByColumnAndRow($column++, 6, $heading);
    }
}
$sheet->mergeCellsByColumnAndRow($column, 5, $column + 3, 5)->setCellValueByColumnAndRow($column, 5, 'Total');
foreach (array('Present Days', 'National Holiday', 'Wages', 'Difference in ESIC (Bonus + Leave)') as $heading) {
    $sheet->setCellValueByColumnAndRow($column++, 6, $heading);
}
foreach (array('OT AMOUNT FOR ESIC', 'Joining Date', 'Profess. Tax Amt.') as $heading) {
    $sheet->mergeCellsByColumnAndRow($column, 5, $column, 6)->setCellValueByColumnAndRow($column++, 5, $heading);
}

$row = 7;
$presentDayColumns = array();
$nationalHolidayColumns = array();
$wagesColumns = array();
foreach ($report['employees'] as $index => $employee) {
    $values = array($index + 1, $employee['name'], $employee['pfNo'], $employee['uan'], $employee['esicNo'], $employee['dob']);
    $totals = array(0, 0, 0, 0);
    foreach ($report['companies'] as $companyId => $companyName) {
        $company = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'nationalHoliday' => 0, 'wages' => 0, 'differenceInESIC' => 0);
        $companyValues = array_values($company);
        foreach ($companyValues as $key => $value) {
            $values[] = $key < 3 ? (float) $value : ($value ?: '');
            $totals[$key] += $value;
        }
    }
    foreach ($totals as $key => $value) {
        $values[] = $key < 3 ? (float) $value : ($value ?: '');
    }
    $values[] = $employee['overtime'] ?: '';
    $values[] = $employee['joiningDate'];
    $values[] = $employee['professionalTax'] ?: '';
    foreach ($values as $indexValue => $value) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indexValue + 1) . $row;
        if ($indexValue >= 1 && $indexValue <= 5) $sheet->setCellValueExplicit($cell, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        else $sheet->setCellValue($cell, $value);
    }
    $row++;
}
$dataEndRow = max(7, $row - 1);
for ($companyIndex = 0; $companyIndex <= $companyCount; $companyIndex++) {
    $presentDayColumns[] = 7 + ($companyIndex * 4);
    $nationalHolidayColumns[] = 8 + ($companyIndex * 4);
    $wagesColumns[] = 9 + ($companyIndex * 4);
}
foreach ($presentDayColumns as $formatColumn) {
    $sheet->getStyleByColumnAndRow($formatColumn, 7, $formatColumn, $dataEndRow)->getNumberFormat()->setFormatCode('0.00;-0.00;0');
}
foreach ($nationalHolidayColumns as $formatColumn) {
    $sheet->getStyleByColumnAndRow($formatColumn, 7, $formatColumn, $dataEndRow)->getNumberFormat()->setFormatCode('0');
}
foreach ($wagesColumns as $formatColumn) {
    $sheet->getStyleByColumnAndRow($formatColumn, 7, $formatColumn, $dataEndRow)->getNumberFormat()->setFormatCode('0.00');
}

$border = array('borders' => array('allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
$sheet->getStyle('A1:' . $lastColumn . max(6, $row - 1))->applyFromArray($border)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setWrapText(true);
$sheet->getStyle('A1:' . $lastColumn . '6')->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A5:' . $lastColumn . '6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A5:F6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDE9DD');
$companyHeaderEnd = 6 + ($companyCount * 4);
if ($companyCount > 0) $sheet->getStyleByColumnAndRow(7, 5, $companyHeaderEnd, 6)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAD0D0');
$totalStart = 7 + ($companyCount * 4);
$sheet->getStyleByColumnAndRow($totalStart, 5, $totalStart + 3, 6)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF00ED20');
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(30);
for ($i = 3; $i <= $lastColumnNumber; $i++) $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(15);
$sheet->getRowDimension(6)->setRowHeight(44);
$sheet->freezePane('G7');
$sheet->setAutoFilter('A6:' . $lastColumn . '6');
while (ob_get_level() > 0) ob_end_clean();
$filename = 'New_PF_Challan_Generate_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
$spreadsheet->disconnectWorksheets();
exit;
