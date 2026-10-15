<?php
ob_start();
include '../config.php';
include 'IsLogin.php';
if (!isset($_SESSION['AdminId'])) {
    exit;
}
require_once __DIR__ . '/AnnualComplianceReportData.php';

try {
    $format = isset($_GET['format']) ? $_GET['format'] : 'preview';
    if (!in_array($format, array('preview', 'pdf', 'excel'), true)) {
        throw new InvalidArgumentException('Select a valid export format.');
    }
    $data = getAnnualComplianceData($dbconn, $_GET);
    if ($format === 'excel') {
        require_once __DIR__ . '/../vendor/autoload.php';
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load(__DIR__ . '/templates/AnnualComplianceFormC.xlsx');
        $sheet = $book->getActiveSheet();
        if (count($data['rows']) > 30) {
            $sheet->insertNewRowBefore(42, count($data['rows']) - 30);
        }
        $totalRow = max(42, count($data['rows']) + 12);
        for ($r = 12; $r <= $totalRow; $r++) {
            for ($c = 1; $c <= 17; $c++) {
                $sheet->setCellValueByColumnAndRow($c, $r, null);
            }
        }
        $text = function ($cell, $value) use ($sheet) {
            $sheet->setCellValueExplicit($cell, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        };
        $text('A2', 'Bonus paid to employees for the accounting year ending on the F.Y. April ' . $data['year'] . ' to March ' . ($data['year'] + 1));
        $text('A4', 'Name and address of Contractor: SHREE GANESH ENGINEERING CO., FF-8, Devshruti Complex, Nr. HCG Hospital, Mithakhali, Ahmedabad - 380006');
        $text('M4', $data['company']);
        $text('M6', $data['company']);
        $totals = array(5 => 0, 6 => 0, 7 => 0, 11 => 0, 12 => 0, 13 => 0);
        foreach ($data['rows'] as $i => $row) {
            foreach (annualComplianceValues($row, $i) as $column => $value) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column + 1) . ($i + 12);
                if (array_key_exists($column, $totals)) {
                    $sheet->setCellValue($cell, $value);
                    $totals[$column] += $value;
                } else {
                    $text($cell, $value);
                }
            }
        }
        $text('B' . $totalRow, 'TOTAL');
        foreach ($totals as $column => $value) {
            $sheet->setCellValueByColumnAndRow($column + 1, $totalRow, $value);
        }
        if (!$data['rows']) {
            $text('B12', 'No salary records found');
        }
        $sheet->getStyle('G12:N' . $totalRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('A12:Q' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A1:Q' . $totalRow)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(48);
        for ($r = 12; $r <= $totalRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(32);
        }
        $sheet->setCellValue('A' . ($totalRow + 2), 'Payable bonus = annual basic salary × 8.33%. Amount actually paid = bonus recorded in salary. Individual deduction details are unavailable.');
        $sheet->mergeCells('A' . ($totalRow + 2) . ':Q' . ($totalRow + 2));
        $sheet->getStyle('A' . ($totalRow + 2))->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($totalRow + 2)->setRowHeight(30);
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0)->setPrintArea('A1:Q' . ($totalRow + 2))->setRowsToRepeatAtTopByStartAndEnd(9, 11);
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Annual-Compliance-Form-C-' . $data['year'] . '.xlsx"');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
        exit;
    }
    $html = renderAnnualComplianceHtml($data);
    if ($format === 'pdf') {
        require_once __DIR__ . '/tcpdf/tcpdf.php';
        $pdf = new TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle('Annual Compliance Report — Form C');
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(true, 5);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->AddPage();
        $pdf->writeHTML($html);
        while (ob_get_level()) {
            ob_end_clean();
        }
        $pdf->Output('Annual-Compliance-Form-C-' . $data['year'] . '.pdf', 'I');
        exit;
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
} catch (Throwable $exception) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $exception->getMessage();
}
