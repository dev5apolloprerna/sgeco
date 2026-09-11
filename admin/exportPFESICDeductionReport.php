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
$lastColumnNumber = 4 + count($report['companies']) * 4 + 3;
$lastColumn = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnNumber);
$sheet->mergeCells('A1:' . $lastColumn . '1')->setCellValue('A1', 'PF & ESIC Deduction Report');
$periodDate = DateTime::createFromFormat('!m/Y', $report['period']);
$sheet->mergeCells('A2:' . $lastColumn . '2')->setCellValue('A2', 'Month : ' . ($periodDate ? $periodDate->format('M-Y') : $report['period']));
$sheet->mergeCells('A3:A4')->setCellValue('A3', 'Sr. No.');
$sheet->mergeCells('B3:B4')->setCellValue('B3', 'Name');
$sheet->mergeCells('C3:C4')->setCellValue('C3', 'PF A/C No.');
$sheet->mergeCells('D3:D4')->setCellValue('D3', 'UAN No.');
$column = 5;
foreach ($report['companies'] as $name) {
    $start = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column);
    $end = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column + 3);
    $sheet->mergeCells($start . '3:' . $end . '3')->setCellValue($start . '3', $name);
    foreach (array('Present Days', 'Wages Rate', 'PF Amt.', 'ESIC Amt.') as $label) $sheet->setCellValueByColumnAndRow($column++, 4, $label);
}
$totalStart = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column);
$sheet->mergeCells($totalStart . '3:' . $lastColumn . '3')->setCellValue($totalStart . '3', 'Total Deduction');
foreach (array('Present Days', 'PF Amt.', 'ESIC Amt.') as $label) $sheet->setCellValueByColumnAndRow($column++, 4, $label);

$rowNumber = 5;
$serial = 1;
foreach ($report['employees'] as $employee) {
    $values = array($serial++, $employee['name'], $employee['pfAccount'], $employee['uan']);
    foreach ($report['companies'] as $companyId => $unused) {
        $v = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'wagesRate' => 0, 'pfAmount' => 0, 'esicAmount' => 0);
        array_push($values, $v['presentDays'], $v['wagesRate'], $v['pfAmount'], $v['esicAmount']);
    }
    array_push($values, $employee['totalDays'], $employee['totalPf'], $employee['totalEsic']);
    foreach ($values as $index => $value) $sheet->setCellValueByColumnAndRow($index + 1, $rowNumber, $value);
    $rowNumber++;
}
$sheet->getStyle('A1:' . $lastColumn . '4')->getFont()->setBold(true);
$sheet->getStyle('A1')->getFont()->setSize(18);
$sheet->getStyle('A1:' . $lastColumn . ($rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle('A1:' . $lastColumn . ($rowNumber - 1))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType('solid')->getStartColor()->setRGB('C9FFFF');
$sheet->getStyle($totalStart . '3:' . $lastColumn . '4')->getFill()->setFillType('solid')->getStartColor()->setRGB('9FD8F6');
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
