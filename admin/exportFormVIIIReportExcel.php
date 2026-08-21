<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormVIIIReport.php');
require_once('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getFormVIIIEmployees($dbconn, $companyId, $month, $employeeId);
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function formVIIIBuildSheet($sheet, array $employee, $title)
{
    $d = formVIIIEmployeeData($employee);
    $sheet->setTitle($title);
    foreach (array(1, 2, 3, 16) as $row) $sheet->mergeCells('A' . $row . ':H' . $row);
    $sheet->setCellValue('A1', 'FORM VIII');
    $sheet->setCellValue('A2', '[Under rule 77 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]');
    $sheet->setCellValue('A3', 'Service Certificate');
    $rows = array(
        5 => array('1.', 'Name of employer:', 'Shree Ganesh Engineering Co.'),
        6 => array('2.', 'LIN/PAN No. of the employer:', 'AAMFS3884N'),
        7 => array('3.', 'Email Id of the employer:', 'hkshah@sgeco.in'),
        8 => array('4.', 'Mobile No. of the employer:', '7984454082'),
        9 => array('5.', 'Nature and location of work:', ''),
        10 => array('6.', 'Name of the workman:', $d['name']),
        11 => array('7.', 'UAN / Aadhaar No.:', 'UAN: ' . $d['uan'] . ' / Aadhaar No.: ' . $d['aadhaar']),
        12 => array('8.', 'Mobile No.:', $d['mobile']),
        13 => array('9.', 'Serial Number in the Register of Workmen:', $d['serial']),
        14 => array('10.', 'Period of Employment:', $d['period']),
        15 => array('11.', 'Designation:', $d['designation'])
    );
    foreach ($rows as $row => $values) {
        $sheet->setCellValue('A' . $row, $values[0]);
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('B' . $row, $values[1]);
        $sheet->mergeCells('E' . $row . ':H' . $row);
        $sheet->setCellValueExplicit('E' . $row, $values[2], DataType::TYPE_STRING);
    }
    $sheet->setCellValue('A16', 'Seal and Signature of Employer');
    $sheet->getStyle('A1:H16')->getFont()->setName('Times New Roman')->setSize(13);
    $sheet->getStyle('A1:A3')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(22);
    $sheet->getStyle('A3')->getFont()->setSize(19);
    $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('E5:H15')->getFont()->setBold(true)->setUnderline(true);
    $sheet->getStyle('A5:H15')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR);
    $sheet->getColumnDimension('A')->setWidth(5);
    foreach (range('B', 'H') as $column) $sheet->getColumnDimension($column)->setWidth(14);
    $sheet->getRowDimension(4)->setRowHeight(25);
    $sheet->getRowDimension(9)->setRowHeight(42);
    $sheet->getRowDimension(16)->setRowHeight(75);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT)->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(1);
    $sheet->getPageMargins()->setTop(0.7)->setRight(0.5)->setLeft(0.5)->setBottom(0.7);
}

$spreadsheet = new Spreadsheet();
if ($employees) {
    foreach ($employees as $index => $employee) {
        $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        formVIIIBuildSheet($sheet, $employee, 'Certificate ' . ($index + 1));
    }
} else {
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'No Data Found !');
}
ob_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Form-VIII-Service-Certificate.xlsx"');
header('Cache-Control: max-age=0');
(new Xlsx($spreadsheet))->save('php://output');
exit;
