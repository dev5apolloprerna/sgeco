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
$lastColumnNumber = 6 + ($companyCount * 4) + 5;
$lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnNumber);
$sheet->mergeCells('A1:' . $lastColumn . '1')->setCellValue('A1', 'FOR EPF & ESIC ONLY');
$sheet->mergeCells('A2:' . $lastColumn . '2')->setCellValue('A2', 'SHREE GANESH ENGINEERING CO.');
$sheet->mergeCells('A3:' . $lastColumn . '3')->setCellValue('A3', 'P.F.CODE : GJ 16240    GROUP : IV');
$sheet->mergeCells('A4:' . $lastColumn . '4')->setCellValue('A4', 'Employee List of PF and ESIC For the Month of : ' . date('F-Y', strtotime('01-' . str_replace('/', '-', $report['salaryMonth']))));

$thinBorder = array('borders' => array('allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
$row = 5;
$writeSection = function ($employees, $isAadhar) use (&$row, $sheet, $report, $companyCount, $lastColumn, $thinBorder) {
    if (count($employees) === 0) {
        return;
    }
    $headerStart = $row;
    $fixed = $isAadhar
        ? array('SR. No.', 'Name as per Aadhar', 'Father Name', 'Aadhar no.', 'ESIC NO', 'D.O.B')
        : array('SR. No.', 'NAME', 'P.F. A/c No.', 'UAN NO', 'ESIC NO', 'D.O.B');
    foreach ($fixed as $index => $heading) {
        $column = $index + 1;
        $sheet->mergeCellsByColumnAndRow($column, $row, $column, $row + 1)->setCellValueByColumnAndRow($column, $row, $heading);
    }
    $column = 7;
    foreach ($report['companies'] as $companyName) {
        $sheet->mergeCellsByColumnAndRow($column, $row, $column + 3, $row)->setCellValueByColumnAndRow($column, $row, $companyName);
        foreach (array('Present Days', 'National Holiday', 'Wages', 'Difference in ESIC') as $heading) {
            $sheet->setCellValueByColumnAndRow($column++, $row + 1, $heading);
        }
    }
    $totalStart = $column;
    $sheet->mergeCellsByColumnAndRow($column, $row, $column + 3, $row)->setCellValueByColumnAndRow($column, $row, 'Total');
    foreach (array('Present Days', 'National Holiday', 'Wages', 'Difference in ESIC') as $heading) {
        $sheet->setCellValueByColumnAndRow($column++, $row + 1, $heading);
    }
    $trailingHeading = $isAadhar ? 'Joining Date' : 'OT AMOUNT FOR ESIC';
    $sheet->mergeCellsByColumnAndRow($column, $row, $column, $row + 1)->setCellValueByColumnAndRow($column, $row, $trailingHeading);
    $sheet->getStyle('A' . $row . ':' . $lastColumn . ($row + 1))->getFont()->setBold(true);
    $sheet->getStyle('A' . $row . ':' . $lastColumn . ($row + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setWrapText(true);
    $sheet->getStyle('A' . $row . ':F' . ($row + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDE9DD');
    if ($companyCount > 0) {
        $sheet->getStyleByColumnAndRow(7, $row, 6 + ($companyCount * 4), $row + 1)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAD0D0');
    }
    $sheet->getStyleByColumnAndRow($totalStart, $row, $totalStart + 3, $row + 1)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF00ED20');
    $row += 2;
    foreach ($employees as $index => $employee) {
        $values = $isAadhar
            ? array($index + 1, $employee['name'], $employee['fatherName'], $employee['aadharNo'], $employee['esicNo'], $employee['dob'])
            : array($index + 1, $employee['name'], $employee['pfNo'], $employee['uan'], $employee['esicNo'], $employee['dob']);
        $totals = array(0, 0, 0, 0);
        foreach ($report['companies'] as $companyId => $companyName) {
            $company = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'nationalHoliday' => 0, 'wages' => 0, 'differenceInESIC' => 0);
            foreach (array_values($company) as $key => $value) {
                $values[] = (float) $value;
                $totals[$key] += $value;
            }
        }
        foreach ($totals as $value) {
            $values[] = (float) $value;
        }
        $values[] = $isAadhar ? $employee['joiningDate'] : (float) $employee['overtime'];
        foreach ($values as $indexValue => $value) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indexValue + 1) . $row;
            if ($indexValue >= 1 && $indexValue <= 5) {
                $sheet->setCellValueExplicit($cell, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($cell, $value);
            }
        }
        $row++;
    }
    $sheet->getStyle('A' . $headerStart . ':' . $lastColumn . ($row - 1))->applyFromArray($thinBorder);
    $row += 1;
};

$writeSection($report['pfEmployees'], false);
$writeSection($report['aadharEmployees'], true);
$lastDataRow = max(4, $row - 2);
$sheet->getStyle('A1:' . $lastColumn . $lastDataRow)->getFont()->setName('Arial')->setSize(11);
$sheet->getStyle('A1:' . $lastColumn . $lastDataRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setWrapText(true);
$sheet->getStyle('A1:' . $lastColumn . '4')->getFont()->setBold(true);
$sheet->getStyle('A1:' . $lastColumn . '4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(30);
for ($column = 3; $column <= $lastColumnNumber; $column++) {
    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column))->setWidth(15);
}
$sheet->freezePane('A7');
while (ob_get_level() > 0) {
    ob_end_clean();
}
$filename = 'New_PF_Challan_Generate_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
$spreadsheet->disconnectWorksheets();
exit;
