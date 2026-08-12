<?php

use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Convert an Other Reports HTML document to a spreadsheet.
 */
function createOtherReportSpreadsheet($html, $worksheetTitle)
{
    // PhpSpreadsheet uses the HTML title as a worksheet name. Replace report
    // titles containing characters such as '/' before parsing the document.
    $safeTitle = htmlspecialchars($worksheetTitle, ENT_QUOTES, 'UTF-8');
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . $safeTitle . '</title>', $html, 1);
    $html = str_ireplace(array('<section', '</section>'), array('<div', '</div>'), $html);

    $reader = new Html();
    $spreadsheet = $reader->loadFromString($html);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($worksheetTitle);

    // Keep every exported cell white, irrespective of browser or Excel theme.
    $dimension = $sheet->calculateWorksheetDimension();
    $sheet->getStyle($dimension)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FFFFFF');

    if ($worksheetTitle === 'Form C') {
        formatFormCWorksheet($sheet);
    } elseif ($worksheetTitle === 'Form XXI') {
        formatFormXXIWorksheet($sheet);
    }

    return $spreadsheet;
}

function formatFormCWorksheet($sheet)
{
    $lastRow = $sheet->getHighestRow();
    $lastDataRow = max(10, $lastRow - 2);

    $sheet->mergeCells('A1:M1');
    $sheet->mergeCells('A2:M2');
    $sheet->mergeCells('B3:M3');
    $sheet->mergeCells('B4:M4');
    $sheet->mergeCells('B5:M5');
    $sheet->mergeCells('B6:M6');
    $sheet->mergeCells('A7:M7');

    $sheet->getStyle('A1:M2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1:M2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A3:A7')->getFont()->setBold(true);
    $sheet->getStyle('B3:B5')->getFont()->setBold(true);
    $sheet->getStyle('A9:M10')->getFont()->setBold(true);
    $sheet->getStyle('A9:M10')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle('A9:M' . $lastDataRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()->setRGB('000000');
    $sheet->getStyle('A11:M' . $lastDataRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getColumnDimension('A')->setWidth(13);
    $sheet->getColumnDimension('B')->setWidth(34);
    foreach (range('C', 'M') as $column) {
        $sheet->getColumnDimension($column)->setWidth(14);
    }
    $sheet->getRowDimension(1)->setRowHeight(22);
    $sheet->getRowDimension(2)->setRowHeight(24);
    $sheet->getRowDimension(9)->setRowHeight(72);
    $sheet->getRowDimension(10)->setRowHeight(22);
    for ($row = 11; $row <= $lastDataRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    configureOtherReportPage($sheet, 'A1:M' . $lastRow, 9);
}

function formatFormXXIWorksheet($sheet)
{
    $lastRow = $sheet->getHighestRow();
    $sheet->getStyle('A1:L5')->getFont()->setBold(true);
    $sheet->getStyle('A6:L7')->getFont()->setBold(true);
    $sheet->getStyle('A6:L7')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()->setRGB('000000');

    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(28);
    $sheet->getColumnDimension('C')->setWidth(24);
    $sheet->getColumnDimension('D')->setWidth(22);
    foreach (range('E', 'L') as $column) {
        $sheet->getColumnDimension($column)->setWidth(15);
    }
    $sheet->getRowDimension(6)->setRowHeight(72);
    $sheet->getRowDimension(7)->setRowHeight(22);
    for ($row = 8; $row <= $lastRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    configureOtherReportPage($sheet, 'A1:L' . $lastRow, 6);
}

function configureOtherReportPage($sheet, $printArea, $freezeRow)
{
    $sheet->freezePane('A' . $freezeRow);
    $sheet->setShowGridlines(false);
    $sheet->getSheetView()->setZoomScale(80);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
    $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
    $sheet->getPageSetup()->setPrintArea($printArea);
    $sheet->getPageMargins()->setTop(0.3)->setBottom(0.3)->setLeft(0.3)->setRight(0.3);
}

/**
 * Stream an Other Reports spreadsheet as a genuine XLSX download.
 */
function exportOtherReportExcel($html, $filename, $worksheetTitle)
{
    $temporaryFile = null;
    $spreadsheet = null;

    try {
        $spreadsheet = createOtherReportSpreadsheet($html, $worksheetTitle);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'other-report-');
        if ($temporaryFile === false) {
            throw new RuntimeException('Unable to create a temporary Excel file.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($temporaryFile);
        if (!is_file($temporaryFile) || filesize($temporaryFile) === 0) {
            throw new RuntimeException('Excel file was not generated.');
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($temporaryFile));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($temporaryFile);
    } catch (Throwable $exception) {
        error_log(
            'Other Report Excel Error: ' . $exception->getMessage() .
                ' in ' . $exception->getFile() . ':' . $exception->getLine()
        );
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Unable to generate Excel report.';
    } finally {
        if ($spreadsheet !== null) {
            $spreadsheet->disconnectWorksheets();
        }
        if ($temporaryFile !== null && is_file($temporaryFile)) {
            unlink($temporaryFile);
        }
    }

    exit;
}
