<?php
// A spreadsheet must not contain any PHP notice, warning, or buffered HTML
// before the XLSX (ZIP) payload, otherwise Excel reports that it is corrupt.
ob_start();
ini_set('display_errors', '0');
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
$sheet->mergeCells('A1:B1')->setCellValueExplicit('A1', 'Employee Payment History', PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$rows = array('Employee Name' => $employee['emp_name'], 'UAN No.' => $employee['uan'], 'PF No.' => $employee['pfcode'], 'Employee Code' => $employee['employeecode'], 'Date of Joining' => $employee['dateofjoining'], 'Date of Exit (Last Month)' => $employee['dateOfExit']);
$row = 2;
foreach ($rows as $label => $value) {
    $sheet->setCellValueExplicit('A' . $row, $label . ' :', PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B' . $row, (string) $value, PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $row++;
}
$sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1:B1')->getAlignment()->setHorizontal('center');
$sheet->getStyle('A1:B7')->getBorders()->getAllBorders()->setBorderStyle(PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$sheet->getStyle('A2:A7')->getFont()->setBold(true);
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(35);

// Build the complete ZIP package before sending any response bytes. This
// prevents output from included files (or a writer warning) from being mixed
// into the XLSX payload and making Excel report an invalid file format.
$temporaryFile = tempnam(sys_get_temp_dir(), 'employee-payment-history-');
if ($temporaryFile === false) {
    http_response_code(500);
    exit('Unable to create the Excel file.');
}
(new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($temporaryFile);
$spreadsheet->disconnectWorksheets();

while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="EmployeePaymentHistory.xlsx"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($temporaryFile));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
readfile($temporaryFile);
unlink($temporaryFile);
exit;
