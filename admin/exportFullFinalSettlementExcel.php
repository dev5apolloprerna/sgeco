<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FullFinalSettlementReport.php');
require_once('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$outputFile = null;

function fullFinalExcelSheetTitle($employeeCode, $employeeName, $index, array &$usedTitles)
{
    $title = preg_replace('/[\\\\\/\?\*\[\]:]+/u', '', trim($employeeCode . '-' . $employeeName));
    $characters = preg_split('//u', $title, -1, PREG_SPLIT_NO_EMPTY);
    if ($characters === false) {
        $characters = str_split(preg_replace('/[^A-Za-z0-9 _-]/', '', $title));
    }
    $title = implode('', array_slice($characters, 0, 31));
    if ($title === '' || $title === '-') {
        $title = 'Employee ' . ($index + 1);
    }
    $baseTitle = $title;
    $suffix = 2;
    $titleKey = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
    while (isset($usedTitles[$titleKey])) {
        $append = ' (' . $suffix++ . ')';
        $title = implode('', array_slice($characters, 0, 31 - strlen($append))) . $append;
        if ($baseTitle !== '' && !$characters) {
            $title = substr($baseTitle, 0, 31 - strlen($append)) . $append;
        }
        $titleKey = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
    }
    $usedTitles[$titleKey] = true;
    return $title;
}

try {
    $settlements = getFullFinalSettlements(
        $dbconn,
        isset($_GET['employeeId']) ? $_GET['employeeId'] : 0,
        isset($_GET['Company']) ? $_GET['Company'] : 0,
        isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : ''
    );
    $spreadsheet = new Spreadsheet();
    $usedTitles = array();
    foreach ($settlements as $index => $data) {
        $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        $sheet->setTitle(fullFinalExcelSheetTitle($data['employee_code'], $data['employee_name'], $index, $usedTitles));
        $sheet->mergeCells('A1:D1')->setCellValue('A1', 'ફુલ એન્ડ ફાયનલ સેટલમેન્ટ');
        $details = array(
            3 => array('કોન્ટ્રકટરનું નામ :', 'SHREE GANESH ENGINEERING CO.'),
            4 => array('કોન્ટ્રકટરનું સરનામું :', 'FF-8 Devshruti Complex, Mithakhali, Ahmedabad - 380006.'),
            5 => array('કામદારનું નામ :', $data['employee_name'] . ' (' . $data['employee_code'] . ')'),
            6 => array('કામદારનું સરનામું :', $data['employee_address']),
            7 => array('દાખલ થયાની તારીખ :', $data['joining_date']),
            8 => array('છુટા થયાની તારીખ :', $data['exit_date']),
            9 => array('પગાર નો માસ :', $data['period'] . '     દિવસો: ' . $data['working_days'])
        );
        foreach ($details as $row => $values) {
            $sheet->setCellValue('A' . $row, $values[0])->mergeCells('B' . $row . ':D' . $row)
                ->setCellValue('B' . $row, $values[1]);
        }
        $sheet->fromArray(array('ચુકવણાનો પ્રકાર', 'રકમ રૂ.', 'કપાત રૂ.', 'રકમ રૂ.'), null, 'A11');
        $rows = array(
            array('બેઝીક', $data['basic'], 'PF', $data['pf']),
            array('સ્પે. એલાઉન્સ (OT)', $data['overtime'], 'PT', $data['pt']),
            array('અન્ય એલાઉન્સ Bonus', $data['bonus'], 'Advance', $data['advance']),
            array('લીવ એન્કેશમેન્ટ', $data['leave'], '', ''),
            array('National Holiday', $data['national_holiday'], '', ''),
            array('કુલ ચુકવણું', $data['total_earnings'], 'કુલ કપાતો', $data['total_deductions']),
            array('', '', 'R/O', $data['rounding']),
            array('ચોખ્ખી રકમ', '', '', $data['net'])
        );
        $sheet->fromArray($rows, null, 'A12');
        $sheet->fromArray(array('નોટીસ પે', 'બોનસ', '', 'કુલ અન્ય રકમ'), null, 'A22');
        $sheet->fromArray(array($data['notice_pay'], $data['other_bonus'], '', $data['other_total']), null, 'A23');
        $sheet->mergeCells('A25:D25')->setCellValue('A25', 'શબ્દોમાં રકમ રૂ. : ' . number_format((float) $data['net'], 2) . ' only');
        $sheet->mergeCells('A27:D29')->setCellValue('A27', 'Employee acknowledgement / કામદારની સહી: __________________________');

        $sheet->getStyle('A1:D29')->getFont()->setName('Nirmala UI')->setSize(10);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11:D11')->getFont()->setBold(true);
        $sheet->getStyle('A11:D11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A16:D19')->getFont()->setBold(true);
        $sheet->getStyle('A11:D19')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A22:D23')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B12:B19')->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('D12:D23')->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('A1:D29')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        foreach (array('A' => 30, 'B' => 22, 'C' => 25, 'D' => 20) as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(1)->setPrintArea('A1:D29');
    }
    $spreadsheet->setActiveSheetIndex(0);
    $outputFile = tempnam(sys_get_temp_dir(), 'full-final-');
    if ($outputFile === false) {
        throw new RuntimeException('Unable to create the Excel download.');
    }
    (new Xlsx($spreadsheet))->save($outputFile);
} catch (Throwable $exception) {
    if ($outputFile !== null && is_file($outputFile)) {
        unlink($outputFile);
    }
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
header('Content-Disposition: attachment; filename="Full-Final-Settlement.xlsx"');
header('Cache-Control: max-age=0');
header('Content-Length: ' . filesize($outputFile));
readfile($outputFile);
unlink($outputFile);
exit;
