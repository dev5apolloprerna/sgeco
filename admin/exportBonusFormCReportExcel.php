<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('BonusFormCReport.php');
require_once('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getBonusFormCEmployees($dbconn, $companyId, $month, $employeeId);
    $rows = getBonusFormCRows($employees, $month);
    $company = getBonusFormCCompanyName($dbconn, $companyId);
    $period = DateTime::createFromFormat('!m/Y', $month);
    $startYear = (int) $period->format('Y') - ((int) $period->format('n') < 4 ? 1 : 0);
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Register of Bonus');
    $sheet->mergeCells('A1:E4')->setCellValue('A1', "NAME & ADDRESS OF CONTRACTOR\nSHREE GANESH ENGINEERING CO.\nFF-8, Devshruti Complex, Nr. HCG Hospital,\nMithakhali, Ahmedabad - 380006");
    $sheet->mergeCells('F1:J2')->setCellValue('F1', 'REGISTER OF BONUS');
    $sheet->mergeCells('F3:J3')->setCellValue('F3', 'FORM C');
    $sheet->mergeCells('F4:J4')->setCellValue('F4', 'See rule 4(b)');
    $sheet->mergeCells('K1:O4')->setCellValue('K1', 'Bonus paid to the employees for the accounting year ' . $startYear . '-' . substr((string) ($startYear + 1), -2) . "\nMonth No. " . $period->format('m') . "\nName & Address of the principal Employers : " . $company);
    $headers = array('Sr. No.', 'Name of Workmen', 'Father Name', 'Whether he has Completed 15 years of age at the beginning of the accounting year', 'Designation/ Nature of Work', 'No. of days Worked', 'Daily Rate', 'Total salary or wage in respect of the accounting year', 'Amount of Bonus Payable under section 10/11 as the case may be', 'Puja or other customary bonus paid during the accounting year', 'Interim bonus or bonus paid in advance', 'Amount of income tax deducted 10-A', 'Actually Amount Paid', 'Date of Payment', 'Signature / Thumb impression of workmen');
    foreach ($headers as $column => $header) {
        $sheet->setCellValueByColumnAndRow($column + 1, 5, $header);
    }
    $totals = array('days' => 0, 'salary' => 0, 'bonus' => 0, 'paid' => 0);
    foreach ($rows as $index => $row) {
        $values = array($index + 1, $row['name'], $row['father'], $row['adult'], $row['designation'], $row['days'], $row['rate'], $row['salary'], $row['bonus'], $row['customary'], $row['advance'], $row['tax'], $row['paid'], $row['payment_date'], '');
        foreach ($values as $column => $value) {
            $sheet->setCellValueByColumnAndRow($column + 1, $index + 6, $value);
        }
        foreach (array_keys($totals) as $key) {
            $totals[$key] += (float) $row[$key];
        }
    }
    $totalRow = count($rows) + 6;
    $sheet->mergeCells('B' . $totalRow . ':C' . $totalRow)->setCellValue('B' . $totalRow, 'TOTAL');
    $sheet->setCellValue('F' . $totalRow, $totals['days'])->setCellValue('G' . $totalRow, 0)->setCellValue('H' . $totalRow, $totals['salary'])->setCellValue('I' . $totalRow, $totals['bonus'])->setCellValue('J' . $totalRow, 0)->setCellValue('K' . $totalRow, 0)->setCellValue('L' . $totalRow, 0)->setCellValue('M' . $totalRow, $totals['paid'])->setCellValue('N' . $totalRow, 0);
    $sheet->getStyle('A1:O' . $totalRow)->getFont()->setName('Times New Roman')->setSize(10);
    $sheet->getStyle('F1')->getFont()->setBold(true)->setSize(18);
    $sheet->getStyle('F3')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1:O' . $totalRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('F1:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A5:O' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A5:O5')->getFont()->setBold(true);
    $sheet->getStyle('A5:O5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $widths = array(6, 20, 20, 23, 18, 12, 12, 18, 18, 18, 18, 18, 15, 15, 18);
    foreach ($widths as $index => $width) {
        $sheet->getColumnDimensionByColumn($index + 1)->setWidth($width);
    }
    $sheet->getRowDimension(5)->setRowHeight(90);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0);
    $sheet->getPageSetup()->setPrintArea('A1:O' . $totalRow);
} catch (Throwable $exception) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($exception->getMessage());
}
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Form-C-Register-of-Bonus.xlsx"');
header('Cache-Control: max-age=0');
(new Xlsx($spreadsheet))->save('php://output');
exit;
