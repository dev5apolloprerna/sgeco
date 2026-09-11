<?php
ob_start();
ini_set('display_errors', '0');
include '../common.php';
include 'IsLogin.php';
require_once 'PFESICDeductionReportData.php';
require_once '../vendor/autoload.php';

$report = pfEsicReportData($dbconn, isset($_GET['month']) ? $_GET['month'] : '', isset($_GET['year']) ? $_GET['year'] : '');
if (!$report['companies'] || !$report['employees']) {
    http_response_code(404);
    exit('No paid salary data found for the selected month and year.');
}

$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('PF ESIC Deductions');
$lastColumnNumber = 6 + count($report['companies']) * 4 + 3;
$lastColumn = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnNumber);
$sheet->mergeCells('A1:' . $lastColumn . '1')->setCellValue('A1', 'PF & ESIC Deduction Report');
$periodDate = DateTime::createFromFormat('!m/Y', $report['period']);
$sheet->mergeCells('A2:' . $lastColumn . '2')->setCellValue('A2', 'Month : ' . ($periodDate ? $periodDate->format('M-Y') : $report['period']));
$rowNumber = 3;
$writeEmployees = function ($employees, $sectionTitle, $isAadhar) use (&$rowNumber, $sheet, $report, $lastColumn) {
    if (!$employees) return;
    //$sheet->mergeCells('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->setCellValue('A' . $rowNumber, $sectionTitle);
    $sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->getFont()->setBold(true);
    $sheet->getStyle('A' . $rowNumber . ':' . $lastColumn . $rowNumber)->getFill()->setFillType('solid')->getStartColor()->setRGB('D9E2F3');
    $rowNumber++;
    $headerRow = $rowNumber;
    $identityHeaders = $isAadhar
        ? array('Sr. No.', 'Name as per Aadhar', 'Father Name', 'Aadhar No.', 'ESIC No.', 'D.O.B')
        : array('Sr. No.', 'Name', 'PF A/C No.', 'UAN No.', 'ESIC No.', 'D.O.B');
    foreach ($identityHeaders as $index => $label) {
        $column = $index + 1;
        $sheet->mergeCellsByColumnAndRow($column, $headerRow, $column, $headerRow + 1)->setCellValueByColumnAndRow($column, $headerRow, $label);
    }
    $column = 7;
    foreach ($report['companies'] as $name) {
        $sheet->mergeCellsByColumnAndRow($column, $headerRow, $column + 3, $headerRow)->setCellValueByColumnAndRow($column, $headerRow, $name);
        foreach (array('Present Days', 'Wages Rate', 'PF Amt.', 'ESIC Amt.') as $label) $sheet->setCellValueByColumnAndRow($column++, $headerRow + 1, $label);
    }
    $totalStart = $column;
    $sheet->mergeCellsByColumnAndRow($column, $headerRow, $column + 2, $headerRow)->setCellValueByColumnAndRow($column, $headerRow, 'Total Deduction');
    foreach (array('Present Days', 'PF Amt.', 'ESIC Amt.') as $label) $sheet->setCellValueByColumnAndRow($column++, $headerRow + 1, $label);
    $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . ($headerRow + 1))->getFont()->setBold(true);
    $sheet->getStyleByColumnAndRow($totalStart, $headerRow, $column - 1, $headerRow + 1)->getFill()->setFillType('solid')->getStartColor()->setRGB('9FD8F6');
    $rowNumber += 2;
    $serial = 1;
    foreach ($employees as $employee) {
        $values = $isAadhar
            ? array($serial++, $employee['name'], $employee['fatherName'], $employee['aadharNo'], $employee['esicNo'], $employee['dob'])
            : array($serial++, $employee['name'], $employee['pfAccount'], $employee['uan'], $employee['esicNo'], $employee['dob']);
        foreach ($report['companies'] as $companyId => $unused) {
            $v = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'wagesRate' => 0, 'pfAmount' => 0, 'esicAmount' => 0);
            array_push($values, $v['presentDays'], $v['wagesRate'], $v['pfAmount'], $v['esicAmount']);
        }
        array_push($values, $employee['totalDays'], $employee['totalPf'], $employee['totalEsic']);
        foreach ($values as $index => $value) $sheet->setCellValueByColumnAndRow($index + 1, $rowNumber, $value);
        $rowNumber++;
    }
    $rowNumber++;
};
$writeEmployees($report['pfEmployees'], 'PF Employees', false);
$writeEmployees($report['aadharEmployees'], 'Aadhar Employees', true);
$sheet->getStyle('A1:' . $lastColumn . '2')->getFont()->setBold(true);
$sheet->getStyle('A1')->getFont()->setSize(18);
$sheet->getStyle('A1:' . $lastColumn . ($rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle('A1:' . $lastColumn . ($rowNumber - 1))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType('solid')->getStartColor()->setRGB('C9FFFF');
$sheet->getColumnDimension('B')->setWidth(32);
for ($i = 1; $i <= $lastColumnNumber; $i++) if ($i !== 2) $sheet->getColumnDimensionByColumn($i)->setWidth(14);

$temporaryFile = tempnam(sys_get_temp_dir(), 'pf-esic-deduction-');
(new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($temporaryFile);
$spreadsheet->disconnectWorksheets();
while (ob_get_level() > 0) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="PF_ESIC_Deduction_' . str_replace('/', '-', $report['period']) . '.xlsx"');
header('Content-Length: ' . filesize($temporaryFile));
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($temporaryFile);
unlink($temporaryFile);
exit;
