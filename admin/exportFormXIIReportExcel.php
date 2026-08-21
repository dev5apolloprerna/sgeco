<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIIReport.php');
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
    $employees = getFormXIIEmployees($dbconn, $companyId, $month, $employeeId);
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function formXIIBuildSheet($sheet, array $employee, $title)
{
    $d = formXIIEmployeeData($employee);
    $sheet->setTitle($title);
    foreach (array(1, 2, 3, 20) as $row) $sheet->mergeCells('A' . $row . ':H' . $row);
    $sheet->setCellValue('A1', 'FORM XII');
    $sheet->setCellValue('A2', '[Under rule 76 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]');
    $sheet->setCellValue('A3', 'Employment Card');
    $rows = array(
        5 => array('A.', 'Name Contractor:', 'Shree Ganesh Engineering Co.'),
        6 => array('A1.', 'LIN/PAN No. of the contractor:', 'AAMFS3884N'),
        7 => array('A2.', 'Email Id of the contractor:', 'hkshah@sgeco.in'),
        8 => array('A3.', 'Mobile No. of the contractor:', '7984454082'),
        10 => array('B.', 'Nature and location of work:', ''),
        12 => array('C.', 'Name of workmen:', $d['name']),
        13 => array('C1.', 'UAN/Aadhar No.:', $d['uan_aadhaar']),
        14 => array('C2.', 'Mobile No.:', $d['mobile']),
        15 => array('1.', 'Serial number in the register of workmen employed:', $d['serial']),
        16 => array('2.', 'Nature of Designation:', $d['designation']),
        17 => array('3.', 'Wages rate (with particulars of unit, in case of piece-work):', $d['rate']),
        18 => array('4.', 'Date of commencement of employment:', $d['joining']),
        19 => array('5.', 'Remarks', '')
    );
    foreach ($rows as $row => $values) {
        $sheet->setCellValue('A' . $row, $values[0]);
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('B' . $row, $values[1]);
        $valueEndColumn = $row >= 5 && $row <= 8 ? 'F' : 'H';
        $sheet->mergeCells('E' . $row . ':' . $valueEndColumn . $row);
        $sheet->setCellValueExplicit('E' . $row, $values[2], DataType::TYPE_STRING);
    }
    $sheet->mergeCells('G5:H8');
    $sheet->setCellValue('G5', "Passport Size\nPhoto");
    $sheet->getStyle('G5:H8')->getAlignment()
        ->setVertical(Alignment::VERTICAL_BOTTOM)
        ->setWrapText(true);
    $sheet->getStyle('G5:H8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->setCellValue('A20', 'Seal and Signature of Contractor');
    $sheet->getStyle('A1:H20')->getFont()->setName('Times New Roman')->setSize(14);
    $sheet->getStyle('A1:A3')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(19);
    $sheet->getStyle('A3')->getFont()->setSize(16);
    $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A20')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('E5:H18')->getFont()->setBold(true)->setUnderline(true);
    $sheet->getStyle('A5:H19')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR);
    $sheet->getColumnDimension('A')->setWidth(6);
    foreach (range('B', 'H') as $column) $sheet->getColumnDimension($column)->setWidth(14);
    $sheet->getRowDimension(4)->setRowHeight(24);
    $sheet->getRowDimension(9)->setRowHeight(32);
    $sheet->getRowDimension(10)->setRowHeight(42);
    $sheet->getRowDimension(20)->setRowHeight(100);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT)->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(1);
    $sheet->getPageMargins()->setTop(0.7)->setRight(0.5)->setLeft(0.5)->setBottom(0.7);
}

$spreadsheet = new Spreadsheet();
if ($employees) {
    foreach ($employees as $index => $employee) {
        $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        formXIIBuildSheet($sheet, $employee, 'Employment Card ' . ($index + 1));
    }
} else $spreadsheet->getActiveSheet()->setCellValue('A1', 'No Data Found !');
ob_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Form-XII-Employment-Card.xlsx"');
header('Cache-Control: max-age=0');
(new Xlsx($spreadsheet))->save('php://output');
exit;
