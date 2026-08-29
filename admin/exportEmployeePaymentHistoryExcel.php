<?php
include '../common.php';
include 'IsLogin.php';
require_once 'employeePaymentHistoryData.php';
require_once '../vendor/autoload.php';

$employee = employeePaymentHistoryFind($dbconn, isset($_GET['employeeId']) ? $_GET['employeeId'] : 0);
if (!$employee) {
    http_response_code(404);
    exit('Employee not found.');
}
$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Payment History');
$sheet->mergeCells('A1:B1')->setCellValue('A1', 'Employee Payment History');
$rows = array('Employee Name' => $employee['emp_name'], 'UAN No.' => $employee['uan'], 'PF No.' => $employee['pfcode'], 'Employee Code' => $employee['employeecode'], 'Date of Joining' => $employee['dateofjoining'], 'Date of Exit (Last Month)' => $employee['dateOfExit']);
$row = 2;
foreach ($rows as $label => $value) {
    $sheet->setCellValue('A' . $row, $label . ' :')->setCellValue('B' . $row, $value);
    $row++;
}
$sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1:B1')->getAlignment()->setHorizontal('center');
$sheet->getStyle('A1:B7')->getBorders()->getAllBorders()->setBorderStyle(PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle('A2:A7')->getFont()->setBold(true);
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(35);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="EmployeePaymentHistory.xlsx"');
(new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
