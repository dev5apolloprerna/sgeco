<?php

use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

    return $spreadsheet;
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