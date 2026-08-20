<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIXReport.php');
require_once('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function formXIXBuildWorksheet($sheet, array $slip, $title)
{
    $sheet->setTitle($title);
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'FORM - XIX - [ SEE RULE - 78(I)(B) ] - WAGES SLIP');
    $sheet->setCellValue('A2', 'Sr. No.: ' . $slip['serial']);
    $sheet->setCellValue('C2', 'PF No.: ' . $slip['pf_number']);
    $sheet->setCellValue('E2', 'Worker No.: ' . $slip['worker_number']);
    $sheet->setCellValue('G2', 'Period: ' . $slip['period']);
    $sheet->mergeCells('A3:B4');
    $sheet->mergeCells('C3:G3');
    $sheet->mergeCells('C4:G4');
    $sheet->setCellValue('A3', 'Name & Address of Contractor');
    $sheet->setCellValue('C3', 'SHREE GANESH ENGINEERING COMPANY');
    $sheet->setCellValue('C4', 'FF-8, DEVSHRUTI COMPLEX, NR. HCG HOSPITAL, MITHAKHALI, AHMEDABAD-380006.');
    $sheet->mergeCells('A5:C5');
    $sheet->mergeCells('D5:G5');
    $sheet->setCellValue('A5', "Code/Name and Father's/Husband's Name of Workman:");
    $sheet->setCellValue('D5', $slip['workman']);
    $sheet->mergeCells('A6:B7');
    $sheet->mergeCells('C6:G6');
    $sheet->mergeCells('C7:G7');
    $sheet->setCellValue('A6', 'Nature and Location of Work :');
    $sheet->setCellValue('C6', $slip['company']);
    $sheet->setCellValue('C7', '0');
    $sheet->setCellValue('A8', 'Head');
    $sheet->setCellValue('B8', 'Wages');
    $sheet->mergeCells('C8:D8');
    $sheet->setCellValue('C8', 'Earning');
    $sheet->mergeCells('E8:F8');
    $sheet->setCellValue('E8', 'Deduction');
    $sheet->setCellValue('G8', 'Signature');

    $wageLabels = array('Minimum', 'HRA', 'Conveyance', 'Education', 'Medical', 'Washing', 'OT Hours');
    $earningLabels = array(
        'Basic',
        'HRA',
        'Conveyance',
        'Education',
        'Medical',
        'Washing',
        'OT Earn',
        'Food Allow.',
        'Other Allow.',
        'Mobile Allow.',
        'Bonus',
        'Leave',
        'National Holiday'
    );
    $deductionLabels = array('Prof. Tax', 'PF', 'ESIC', 'GL.W.F.', 'Other Deduction', 'Advance');
    for ($offset = 0; $offset < 13; $offset++) {
        $row = $offset + 9;
        if (isset($wageLabels[$offset])) {
            $sheet->setCellValue('A' . $row, $wageLabels[$offset]);
            $sheet->setCellValue('B' . $row, (float) $slip['wages'][$offset]);
        }
        $sheet->setCellValue('C' . $row, $earningLabels[$offset]);
        $sheet->setCellValue('D' . $row, (float) $slip['earnings'][$offset]);
        if (isset($deductionLabels[$offset])) {
            $sheet->setCellValue('E' . $row, $deductionLabels[$offset]);
            $sheet->setCellValue('F' . $row, (float) $slip['deductions'][$offset]);
        }
    }
    $sheet->setCellValue('G9', 'Bank Name: ' . $slip['bank_name']);
    $sheet->setCellValue('G10', 'IFSC Code: ' . $slip['ifsc']);
    $sheet->setCellValueExplicit('G11', 'UAN No.: ' . $slip['uan'], DataType::TYPE_STRING);
    $sheet->setCellValue('A22', 'Work Days');
    $sheet->setCellValue('B22', (float) $slip['work_days']);
    $sheet->setCellValue('C22', 'Gross Earn.');
    $sheet->setCellValue('D22', (float) $slip['gross']);
    $sheet->setCellValue('E22', 'Total Deduction');
    $sheet->setCellValue('F22', (float) $slip['total_deduction']);
    $sheet->setCellValue('G22', 'Net Pay.    ' . formXIXAmount($slip['net_pay']));
    $sheet->mergeCells('A23:G25');
    $sheet->setCellValue('A23', 'Initials of the Contractor or his Representative     ____________________________');

    $sheet->getStyle('A1:G25')->getFont()->setName('Arial')->setSize(10);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A8:G8')->getFont()->setBold(true);
    $sheet->getStyle('A8:G8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A22:G22')->getFont()->setBold(true);
    $sheet->getStyle('A3:A7')->getFont()->setBold(true);
    $sheet->getStyle('A23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)
        ->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A1:G25')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A1:G25')->getAlignment()->setWrapText(true);
    $sheet->getStyle('B9:B22')->getNumberFormat()->setFormatCode('0.00');
    $sheet->getStyle('D9:D22')->getNumberFormat()->setFormatCode('0.00');
    $sheet->getStyle('F9:F22')->getNumberFormat()->setFormatCode('0.00');
    foreach (array('A1:G1', 'A2:G7', 'A8:G22', 'A23:G25') as $range) {
        $sheet->getStyle($range)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    }
    $sheet->getStyle('A8:G22')->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A8:G8')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A22:G22')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
    $widths = array('A' => 17, 'B' => 12, 'C' => 20, 'D' => 13, 'E' => 18, 'F' => 13, 'G' => 34);
    foreach ($widths as $column => $width) {
        $sheet->getColumnDimension($column)->setWidth($width);
    }
    $sheet->getRowDimension(1)->setRowHeight(28);
    $sheet->getRowDimension(3)->setRowHeight(22);
    $sheet->getRowDimension(4)->setRowHeight(22);
    $sheet->getRowDimension(23)->setRowHeight(42);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
        ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(1);
    $sheet->getPageMargins()->setTop(0.25)->setRight(0.25)->setBottom(0.25)->setLeft(0.25);
    $sheet->getPageSetup()->setPrintArea('A1:G25');
}

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $slips = getFormXIXSlipData(
        getFormXIXEmployees($dbconn, $companyId, $month, $employeeId),
        $month,
        getFormXIXCompanyName($dbconn, $companyId),
        getCompanyReportAdvances($dbconn, $companyId, $month)
    );
    $spreadsheet = new Spreadsheet();
    foreach ($slips as $index => $slip) {
        $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        formXIXBuildWorksheet($sheet, $slip, 'Wages Slip ' . ($index + 1));
    }
    if (!$slips) {
        $spreadsheet->getActiveSheet()->setTitle('Wages Slip')->setCellValue('A1', 'No Data Found !');
    }
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
header('Content-Disposition: attachment; filename="Form-XIX-Wages-Slip.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
