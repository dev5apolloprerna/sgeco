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
    $formCDetails = $worksheetTitle === 'Form C' ? extractFormCDetails($html) : array();
    $formXXIDetails = $worksheetTitle === 'Form XXI' ? extractFormXXIDetails($html) : array();
    $formXXDetails = $worksheetTitle === 'Form XX' ? extractFormXXDetails($html) : array();
    $formXIIIDetails = $worksheetTitle === 'Form XIII' ? extractFormXIIIDetails($html) : array();
    $formXXIIDetails = $worksheetTitle === 'Form XXII' ? extractFormXXIIDetails($html) : array();
    $formXXIIIDetails = $worksheetTitle === 'Form XXIII' ? extractFormXXIIIDetails($html) : array();
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
        formatFormCWorksheet($sheet, $formCDetails);
    } elseif ($worksheetTitle === 'Form XXI') {
        formatFormXXIWorksheet($sheet, $formXXIDetails);
    } elseif ($worksheetTitle === 'Form XX') {
        formatFormXXWorksheet($sheet, $formXXDetails);
    } elseif ($worksheetTitle === 'Form XIII') {
        formatFormXIIIWorksheet($sheet, $formXIIIDetails);
    } elseif ($worksheetTitle === 'Form XXII') {
        formatFormXXIIWorksheet($sheet, $formXXIIDetails);
    } elseif ($worksheetTitle === 'Form XXIII') {
        formatFormXXIIIWorksheet($sheet, $formXXIIIDetails);
    }

    rightAlignOtherReportAmountColumnsInWorksheet($sheet);

    return $spreadsheet;
}

/**
 * Align only the data below headings containing "Amount" or "Amt.".
 */
function rightAlignOtherReportAmountColumnsInWorksheet($sheet)
{
    $highestRow = $sheet->getHighestRow();
    $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
        $sheet->getHighestColumn()
    );
    $dataStartRow = null;

    // Statutory registers place a numbered column row immediately above the
    // records. Using it as the boundary keeps both heading rows unchanged.
    for ($row = 1; $row <= $highestRow; $row++) {
        $numberedCells = 0;
        for ($column = 1; $column <= $highestColumn; $column++) {
            if ((string) $sheet->getCellByColumnAndRow($column, $row)->getValue() === (string) $column) {
                $numberedCells++;
            }
        }
        if ($numberedCells >= min(3, $highestColumn)) {
            $dataStartRow = $row + 1;
        }
    }

    for ($row = 1; $row <= $highestRow; $row++) {
        for ($column = 1; $column <= $highestColumn; $column++) {
            $heading = (string) $sheet->getCellByColumnAndRow($column, $row)->getValue();
            if (!preg_match('/\b(?:amt|amount)\.?\b/i', $heading)) {
                continue;
            }
            $firstDataRow = $dataStartRow === null ? $row + 1 : max($row + 1, $dataStartRow);
            if ($firstDataRow <= $highestRow) {
                $sheet->getStyleByColumnAndRow($column, $firstDataRow, $column, $highestRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
    }
}

function extractFormXXIIIDetails($html)
{
    preg_match('/<span class="month-label">Month :<\/span>\s*<span>(.*?)<\/span>/is', $html, $month);
    preg_match('/Name and Address of the principal Employer\s*:\s*<span[^>]*>(.*?)<\/span>/is', $html, $employer);
    $details = array(
        'month' => isset($month[1]) ? trim(strip_tags($month[1])) : '',
        'employer' => isset($employer[1]) ? trim(html_entity_decode(strip_tags($employer[1]), ENT_QUOTES, 'UTF-8')) : '',
        'rows' => array()
    );
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $xpath = new DOMXPath($document);
    foreach ($xpath->query('//tr[contains(concat(" ", normalize-space(@class), " "), " data-row ")]') as $row) {
        $values = array();
        foreach ($xpath->query('./td', $row) as $cell) {
            $values[] = trim(preg_replace('/\s+/', ' ', $cell->textContent));
        }
        $details['rows'][] = array_slice(array_pad($values, 12, ''), 0, 12);
    }
    return $details;
}

function formatFormXXIIIWorksheet($sheet, array $details)
{
    $sheet->removeRow(1, max(1, $sheet->getHighestRow()));
    $sheet->mergeCells('A1:L1');
    foreach (
        array(
            'A2:B2',
            'C2:D2',
            'A3:B3',
            'C3:D3',
            'A4:B4',
            'C4:D4',
            'A5:B5',
            'C5:D5',
            'E2:H2',
            'E3:H3',
            'E4:H5',
            'I2:L3',
            'I4:L5',
            'A6:L6'
        ) as $range
    ) {
        $sheet->mergeCells($range);
    }
    $sheet->setCellValue('A1', 'Register of Overtime');
    $sheet->setCellValue('A2', 'NAME AND ADDRESS OF CONTRACTOR :');
    $sheet->setCellValue('C2', 'SHREE GANESH ENGINEERING CO.');
    $sheet->setCellValue('C3', 'FF-8, Devshruti Complex,');
    $sheet->setCellValue('C4', 'Nr. HCG Hospital,');
    $sheet->setCellValue('C5', 'Mithakhali, Ahmedabad - 380006');
    $sheet->setCellValue('E2', 'FORM NO. XXIII');
    $sheet->setCellValue('E3', '[See Rule 78 (1) (a) (iii)]');
    $sheet->setCellValue('E4', 'Month: ' . $details['month']);
    $sheet->setCellValue('I2', 'Name and Address of establishment in/under which contract is carried on');
    $sheet->setCellValue('I4', 'Name and Address of the principal Employer : ' . $details['employer']);
    $sheet->setCellValue('A6', 'NATURE AND LOCATION OF WORK');
    $headings = array(
        'Sr. No.',
        'Name of Workmen',
        "Father's / Husband's Name",
        'Sex',
        'Designation/Nature of Employment',
        'Date on Which Overtime worked',
        'Total overtime worked or Production in case of piece-rated',
        'Normal rate of wages',
        'Overtime of wages',
        'Overtime earning',
        'Date on which overtime wages paid',
        'Remarks'
    );
    foreach ($headings as $column => $heading) {
        $sheet->setCellValueByColumnAndRow($column + 1, 7, $heading);
        $sheet->setCellValueByColumnAndRow($column + 1, 8, $column + 1);
    }
    foreach ($details['rows'] as $rowIndex => $values) {
        foreach ($values as $column => $value) {
            $sheet->setCellValueByColumnAndRow($column + 1, $rowIndex + 9, $value);
        }
    }
    $lastRow = max(8, count($details['rows']) + 8);
    $sheet->getStyle('A1:L' . $lastRow)->getFont()->setName('Times New Roman')->setSize(9);
    $sheet->getStyle('A1:L6')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(18);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E2:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A7:L8')->getFont()->setBold(true);
    $sheet->getStyle('A7:L' . $lastRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A7:L' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $widths = array(5, 24, 20, 7, 17, 14, 17, 12, 13, 13, 16, 10);
    foreach (range('A', 'L') as $index => $column) {
        $sheet->getColumnDimension($column)->setWidth($widths[$index]);
    }
    $sheet->getRowDimension(1)->setRowHeight(26);
    $sheet->getRowDimension(7)->setRowHeight(70);
    for ($row = 9; $row <= $lastRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(28);
    }
    // $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
    configureOtherReportPage($sheet, 'A1:L' . $lastRow, 9);
}

function extractFormXXIIDetails($html)
{
    preg_match('/<span class="month-label">Month:<\/span>\s*([^<]*)/is', $html, $month);
    preg_match(
        '/Name and Address of the principal Employer\s*:\s*<span[^>]*>(.*?)<\/span>/is',
        $html,
        $employer
    );
    $details = array(
        'month' => isset($month[1]) ? trim(strip_tags($month[1])) : '',
        'employer' => isset($employer[1]) ? trim(html_entity_decode(strip_tags($employer[1]), ENT_QUOTES, 'UTF-8')) : '',
        'rows' => array()
    );

    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $xpath = new DOMXPath($document);
    foreach ($xpath->query('//tr[contains(concat(" ", normalize-space(@class), " "), " data-row ")]') as $row) {
        $values = array();
        foreach ($xpath->query('./td', $row) as $cell) {
            if (count($values) === 8) {
                $parts = array();
                foreach ($xpath->query('.//table[contains(@class, "installments")]/tr/td', $cell) as $instalment) {
                    $dateNode = $xpath->query('.//span[contains(@class, "installment-date")]', $instalment)->item(0);
                    $date = $dateNode ? trim($dateNode->textContent) : '';
                    $text = trim(preg_replace('/\s+/', ' ', $instalment->textContent));
                    $amount = trim(substr($text, strlen($date)));
                    $parts[] = trim($date . "\n" . $amount);
                }
                $values[] = implode("\n\n", $parts);
            } else {
                $values[] = trim(preg_replace('/\s+/', ' ', $cell->textContent));
            }
        }
        $details['rows'][] = array_slice(array_pad($values, 11, ''), 0, 11);
    }
    return $details;
}

function formatFormXXIIWorksheet($sheet, array $details)
{
    $sheet->removeRow(1, max(1, $sheet->getHighestRow()));
    $sheet->mergeCells('A1:K1');
    $sheet->mergeCells('A2:B2');
    $sheet->mergeCells('C2:D2');
    $sheet->mergeCells('A3:B3');
    $sheet->mergeCells('C3:D3');
    $sheet->mergeCells('A4:B4');
    $sheet->mergeCells('C4:D4');
    $sheet->mergeCells('A5:B5');
    $sheet->mergeCells('C5:D5');
    $sheet->mergeCells('E2:G2');
    $sheet->mergeCells('E3:G3');
    $sheet->mergeCells('E4:G5');
    $sheet->mergeCells('H2:K3');
    $sheet->mergeCells('H4:K5');
    $sheet->mergeCells('A6:K6');
    $sheet->setCellValue('A1', 'Register of Advance');
    $sheet->setCellValue('A2', 'NAME AND ADDRESS OF CONTRACTOR :');
    $sheet->setCellValue('C2', 'SHREE GANESH ENGINEERING CO.');
    $sheet->setCellValue('C3', 'FF-8, Devshruti Complex,');
    $sheet->setCellValue('C4', 'Nr. HCG Hospital,');
    $sheet->setCellValue('C5', 'Mithakhali, Ahmedabad - 380006');
    $sheet->setCellValue('E2', 'FORM NO. XXII');
    $sheet->setCellValue('E3', '[See Rule 78 (1) (a) (ii)]');
    $sheet->setCellValue('E4', 'Month: ' . $details['month']);
    $sheet->setCellValue('H2', 'Name and Address of establishment in/under which contract is carried on');
    $sheet->setCellValue('H4', 'Name and Address of the principal Employer : ' . $details['employer']);
    $sheet->setCellValue('A6', 'NATURE AND LOCATION OF WORK');
    $headings = array(
        'Sr. No.',
        'Name',
        "Father's / Husband's Name",
        'Name of employment / Designation',
        'Wage Period and wages payable',
        'Date and amount of advance given',
        'Purpose(s) for which advance made',
        'No. of instalments by which advance to be paid',
        'Date and amount of each instalment repaid',
        'Date on which last instalment was repaid',
        'Remarks'
    );
    foreach ($headings as $column => $heading) {
        $sheet->setCellValueByColumnAndRow($column + 1, 7, $heading);
        $sheet->setCellValueByColumnAndRow($column + 1, 8, $column + 1);
    }
    foreach ($details['rows'] as $rowIndex => $values) {
        foreach ($values as $column => $value) {
            $sheet->setCellValueByColumnAndRow($column + 1, $rowIndex + 9, $value);
        }
    }
    $lastRow = max(8, count($details['rows']) + 8);
    $sheet->getStyle('A1:K' . $lastRow)->getFont()->setName('Times New Roman')->setSize(9);
    $sheet->getStyle('A1:K6')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(18);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E2:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A7:K8')->getFont()->setBold(true);
    $sheet->getStyle('A7:K' . $lastRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A7:K' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $widths = array(4, 28, 24, 16, 15, 17, 18, 17, 19, 18, 9);
    foreach (range('A', 'K') as $index => $column) {
        $sheet->getColumnDimension($column)->setWidth($widths[$index]);
    }
    $sheet->getRowDimension(1)->setRowHeight(26);
    $sheet->getRowDimension(7)->setRowHeight(70);
    $sheet->getRowDimension(8)->setRowHeight(20);
    for ($row = 9; $row <= $lastRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(60);
    }
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
    configureOtherReportPage($sheet, 'A1:K' . $lastRow, 9);
}

function extractFormXIIIDetails($html)
{
    preg_match('/<strong>Month:\s*(.*?)<\/strong>/is', $html, $month);
    preg_match(
        '/Name and Address of the principal Employer\s*:\s*<\/strong>\s*<span[^>]*>(.*?)<\/span>/is',
        $html,
        $employer
    );
    $clean = function ($value) {
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    };
    return array(
        'month' => isset($month[1]) ? $clean($month[1]) : '',
        'employer' => isset($employer[1]) ? $clean($employer[1]) : '',
    );
}

function formatFormXIIIWorksheet($sheet, array $details)
{
    // Rebuild the template's three-part header because the HTML reader cannot
    // preserve its nested-table column proportions in a spreadsheet.
    $sheet->removeRow(1, 7);
    $sheet->insertNewRowBefore(1, 8);
    $sheet->mergeCells('A1:L1');
    $sheet->mergeCells('A2:B2');
    $sheet->mergeCells('C2:D2');
    $sheet->mergeCells('A3:B3');
    $sheet->mergeCells('C3:D3');
    $sheet->mergeCells('A4:B4');
    $sheet->mergeCells('C4:D4');
    $sheet->mergeCells('A5:B5');
    $sheet->mergeCells('C5:D5');
    $sheet->mergeCells('E2:H2');
    $sheet->mergeCells('E3:H3');
    $sheet->mergeCells('E4:H5');
    $sheet->mergeCells('I2:L3');
    $sheet->mergeCells('I4:L5');
    $sheet->mergeCells('A6:L6');

    $sheet->setCellValue('A1', 'Register of Workmen Employed by Contractor');
    $sheet->setCellValue('A2', 'NAME AND ADDRESS OF CONTRACTOR :');
    $sheet->setCellValue('C2', 'SHREE GANESH ENGINEERING CO.');
    $sheet->setCellValue('C3', 'FF-8, Devshruti Complex,');
    $sheet->setCellValue('C4', 'Nr. HCG Hospital,');
    $sheet->setCellValue('C5', 'Mithakhali, Ahmedabad - 380006');
    $sheet->setCellValue('E2', 'FORM NO. XIII');
    $sheet->setCellValue('E3', '[See Rule 75]');
    // Form XIII does not require a month in the Excel header. The selected
    // salary period is still used to choose employees and calculate ages.
    $sheet->setCellValue('E4', '');
    $sheet->setCellValue('I2', 'Name and Address of establishment in/under which contract is carried on');
    $sheet->setCellValue('I4', 'Name and Address of the principal Employer :  ' . $details['employer']);
    $sheet->setCellValue('A6', 'NATURE AND LOCATION OF WORK');

    $headings = array(
        "Sr.\nNo.",
        "Name and surname\nof workman",
        'Age & Sex',
        "Father's / Husband's Name",
        "Nature of employment /\ndesignation",
        "Permanent Home Address of workman\n(village, Taluka and District)",
        'Local Address',
        "Date of commencement\nof employment",
        "Signature or thumb\nimpression of workman",
        "Date of termination\nof employment",
        'Reason for Termination',
        'Remarks'
    );
    foreach ($headings as $index => $heading) {
        $sheet->setCellValueByColumnAndRow($index + 1, 7, $heading);
        $sheet->setCellValueByColumnAndRow($index + 1, 8, $index + 1);
    }

    $lastRow = $sheet->getHighestDataRow();
    for ($row = 9, $serial = 1; $row <= $lastRow; $row++, $serial++) {
        $sheet->setCellValueByColumnAndRow(1, $row, $serial);
    }
    $sheet->getStyle('A1:L' . $lastRow)->getFont()->setName('Times New Roman')->setSize(9);
    $sheet->getStyle('A1:L6')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E2:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A7:L8')->getFont()->setBold(true);
    $sheet->getStyle('A7:L8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
    $sheet->getStyle('A7:L' . $lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
    $sheet->getStyle('A9:L' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

    $widths = array(5, 24, 10, 22, 18, 30, 24, 14, 17, 14, 15, 12);
    foreach (range('A', 'L') as $index => $column) {
        $sheet->getColumnDimension($column)->setWidth($widths[$index]);
    }
    $sheet->getRowDimension(1)->setRowHeight(24);
    $sheet->getRowDimension(7)->setRowHeight(82);
    $sheet->getRowDimension(8)->setRowHeight(22);
    for ($row = 9; $row <= $lastRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(36);
    }
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
    configureOtherReportPage($sheet, 'A1:L' . $lastRow, 9);
}

function extractFormXXDetails($html)
{
    $clean = function ($value) {
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    };
    $monthValue = '';
    $employerValue = '';
    $rows = array();
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if ($loaded) {
        $xpath = new DOMXPath($document);
        $monthNodes = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " month-row ")]'
            . '/*[not(contains(concat(" ", normalize-space(@class), " "), " month-label "))][last()]'
        );
        if ($monthNodes->length > 0) {
            $monthValue = $monthNodes->item(0)->textContent;
        }

        $employerNodes = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " right-line ")]'
            . '[contains(translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"),'
            . ' "name and address of the principal employer")]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), " normal-weight ")]'
        );
        if ($employerNodes->length > 0) {
            $employerValue = $employerNodes->item(0)->textContent;
        }

        foreach ($xpath->query('//tr[contains(concat(" ", normalize-space(@class), " "), " data-row ")]') as $row) {
            $values = array();
            foreach ($xpath->query('./td', $row) as $cell) {
                $values[] = $clean(preg_replace('/\s+/', ' ', $cell->textContent));
            }
            $rows[] = array_slice(array_pad($values, 13, ''), 0, 13);
        }
    }
    return array(
        'month' => $clean($monthValue),
        'employer' => $clean($employerValue),
        'rows' => $rows,
    );
}

function formatFormXXWorksheet($sheet, array $details)
{
    // Rebuild the sheet from the extracted records. This avoids the HTML
    // reader's extra highlighted row and preserves all 13 statutory columns.
    // Clear the HTML reader's merged ranges before removing its rows. Row
    // removal shifts those ranges instead of deleting them, which otherwise
    // leaves them overlapping the rebuilt header and makes Excel repair the
    // workbook when it is opened.
    foreach ($sheet->getMergeCells() as $range) {
        $sheet->unmergeCells($range);
    }
    $sheet->removeRow(1, max(1, $sheet->getHighestRow()));
    $sheet->mergeCells('A1:M1');
    $sheet->mergeCells('A2:B2');
    $sheet->mergeCells('C2:D2');
    $sheet->mergeCells('A3:B3');
    $sheet->mergeCells('C3:D3');
    $sheet->mergeCells('A4:B4');
    $sheet->mergeCells('C4:D4');
    $sheet->mergeCells('A5:B5');
    $sheet->mergeCells('C5:D5');
    $sheet->mergeCells('E2:H2');
    $sheet->mergeCells('E3:H3');
    $sheet->mergeCells('E4:H5');
    $sheet->mergeCells('I2:M3');
    $sheet->mergeCells('I4:M5');
    $sheet->mergeCells('A6:M6');

    // Keep the exported header text identical to the approved PDF form.
    $sheet->setCellValue('A1', 'Register of Deductions for Damage or Loss');
    $sheet->setCellValue('A2', 'NAME AND ADDRESS OF CONTRACTOR :');
    $sheet->setCellValue('C2', 'SHREE GANESH ENGINEERING CO.');
    $sheet->setCellValue('C3', 'FF-8, Devshruti Complex,');
    $sheet->setCellValue('C4', 'Nr. HCG Hospital,');
    $sheet->setCellValue('C5', 'Mithakhali, Ahmedabad - 380006');
    $sheet->setCellValue('E2', 'FORM NO. XX');
    $sheet->setCellValue('E3', '[See rule 78 (2)(d)]');
    $sheet->setCellValue('E4', 'Month: ' . $details['month']);
    $sheet->setCellValue('I2', 'Name and Address of establishment in/under which contract is carried on');
    $sheet->setCellValue('I4', 'Name and Address of the principal Employer :  ' . $details['employer']);
    $sheet->setCellValue('A6', 'NATURE AND LOCATION OF WORK');

    // Match the PDF's two-tier table heading: columns 1-10 and 13 span both
    // rows, while "Date of recovery" groups the first/last installment cells.
    foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'M') as $column) {
        $sheet->mergeCells($column . '7:' . $column . '8');
    }
    $sheet->mergeCells('K7:L7');
    $headings = array(
        'A7' => "Sr.\nNo.",
        'B7' => 'Name of Workmen',
        'C7' => "Father's / Husband's\nName",
        'D7' => "Designation / Nature\nof Employment",
        'E7' => "Particulars of\nDamage or Loss",
        'F7' => "Date of Damage\nor Loss",
        'G7' => "Whether workman\nshowed cause against\ndeduction",
        'H7' => "Name of person in whose\npresence workman's\nexplanation was heard",
        'I7' => "Amount of deduction\nimposed",
        'J7' => "No. of\nInstallments",
        'K7' => 'Date of recovery',
        'K8' => "First\ninstallment",
        'L8' => "Last\ninstallment",
        'M7' => 'Remarks',
    );

    foreach ($headings as $cell => $heading) {
        $sheet->setCellValue($cell, $heading);
    }
    foreach (range(1, 13) as $column) {
        $sheet->setCellValueByColumnAndRow($column, 9, $column);
    }

    foreach ($details['rows'] as $rowIndex => $values) {
        foreach ($values as $column => $value) {
            $sheet->setCellValueByColumnAndRow($column + 1, $rowIndex + 10, $value);
        }
    }

    $lastRow = max(10, count($details['rows']) + 9);
    $sheet->getStyle('A1:M' . $lastRow)->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FFFFFF');
    $sheet->getStyle('A1:M' . $lastRow)->getFont()->setName('Times New Roman')->setSize(10);
    $sheet->getStyle('A1:M6')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E2:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A7:M9')->getFont()->setBold(true);
    // Do not retain a font colour inherited by the HTML reader. In particular,
    // the vertically merged heading cells must remain visible in Excel just as
    // they are in the PDF table header.
    $sheet->getStyle('A7:M9')->getFont()->getColor()->setRGB('000000');
    $sheet->getStyle('A7:M9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
    $sheet->getStyle('A7:M' . $lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
    // Use the same 3/18/17/8/6... proportions as the Form XX HTML consumed by
    // generateFormXXReportPDF.php. Keeping these proportions in one common
    // scale makes every Excel border and heading line up with its PDF column.
    $widths = array(5, 30, 28.333, 13.333, 10, 10, 10, 10, 10, 10, 10, 10, 10);
    foreach (range('A', 'M') as $index => $column) {
        $sheet->getColumnDimension($column)->setWidth($widths[$index]);
    }
    $sheet->getRowDimension(1)->setRowHeight(24);
    $sheet->getRowDimension(7)->setRowHeight(70);
    $sheet->getRowDimension(8)->setRowHeight(32);
    $sheet->getRowDimension(9)->setRowHeight(22);
    for ($row = 10; $row <= $lastRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(24);
    }
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 9);
    configureOtherReportPage($sheet, 'A1:M' . $lastRow, 10);
}

function extractFormXXIDetails($html)
{
    // preg_match('/<span class="month-label">Month:<\/span>\s*<span>(.*?)<\/span>/is', $html, $month);
    // Both labels are wrapped in <strong> while their values are sibling
    // spans. Account for that closing element instead of assuming the value
    // span immediately follows the label span.
    preg_match(
        // '/Name and Address of the principal Employer\s*:\s*<span[^>]*>(.*?)<\/span>/is',
        '/<span\b[^>]*class=("|\')[^"\']*\bmonth-label\b[^"\']*\1[^>]*>\s*Month\s*:\s*<\/span>\s*<\/strong>\s*<span\b[^>]*>(.*?)<\/span>/is',
        $html,
        $month
    );
    preg_match(
        '/Name and Address of the principal Employer\s*:\s*<\/strong>\s*<span\b[^>]*>(.*?)<\/span>/is',
        $html,
        $employer
    );

    $clean = function ($value) {
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    };

    return array(
        'month' => isset($month[2]) ? $clean($month[2]) : '',
        'employer' => isset($employer[1]) ? $clean($employer[1]) : '',
    );
}

function extractFormCDetails($html)
{
    preg_match_all('/<td[^>]*class="[^"]*company-value[^"]*"[^>]*>(.*?)<\/td>/is', $html, $companies);
    preg_match('/<tr class="month-row">.*?<td[^>]*>(.*?)<\/td>/is', $html, $month);

    $clean = function ($value) {
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    };

    return array(
        'establishment' => isset($companies[1][0]) ? $clean($companies[1][0]) : '',
        'principal' => isset($companies[1][1]) ? $clean($companies[1][1]) : '',
        'month' => isset($month[1]) ? trim(preg_replace('/^For Month of\s*/i', '', $clean($month[1]))) : '',
    );
}

function formatFormCWorksheet($sheet, array $details)
{
    $lastRow = $sheet->getHighestRow();
    $lastDataRow = max(10, $lastRow - 2);

    // The HTML reader creates a merge for the colspan in the details table.
    // Remove it before applying the final layout: intersecting merge ranges
    // produce an invalid worksheet that Excel attempts to repair on opening.
    foreach ($sheet->getMergeCells() as $range) {
        $sheet->unmergeCells($range);
    }

    $sheet->mergeCells('A1:M1');
    $sheet->mergeCells('A2:M2');
    $sheet->mergeCells('A3:C3');
    $sheet->mergeCells('D3:F3');
    $sheet->mergeCells('G3:I3');
    $sheet->mergeCells('J3:M3');
    $sheet->mergeCells('A4:C4');
    $sheet->mergeCells('D4:M4');
    $sheet->mergeCells('A5:C5');
    $sheet->mergeCells('D5:M5');
    $sheet->mergeCells('A6:C6');
    $sheet->mergeCells('D6:M6');
    $sheet->mergeCells('A7:M7');

    $sheet->setCellValue('A3', 'Name of the Establishment :-');
    $sheet->setCellValue('D3', $details['establishment']);
    $sheet->setCellValue('G3', 'Name and Address of Principal Employer:');
    $sheet->setCellValue('J3', $details['principal']);
    $sheet->setCellValue('A4', 'Name of Owner :-');
    $sheet->setCellValue('D4', 'Mr. Hitesh K Shah');
    $sheet->setCellValue('A5', 'LIN');
    $sheet->setCellValue('A6', 'For Month of');
    $sheet->setCellValue('D6', $details['month']);

    $sheet->getStyle('A1:M2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1:M2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A3:M6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A3:C6')->getFont()->setBold(true);
    $sheet->getStyle('D3')->getFont()->setBold(true);
    $sheet->getStyle('G3')->getFont()->setBold(true);
    $sheet->getStyle('J3')->getFont()->setBold(true);
    $sheet->getStyle('A8:M9')->getFont()->setBold(true);
    $sheet->getStyle('A8:M9')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle('A8:M' . $lastDataRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()->setRGB('000000');
    $sheet->getStyle('A10:M' . $lastDataRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getColumnDimension('A')->setWidth(13);
    $sheet->getColumnDimension('B')->setWidth(34);
    foreach (range('C', 'M') as $column) {
        $sheet->getColumnDimension($column)->setWidth(14);
    }
    $sheet->getRowDimension(1)->setRowHeight(22);
    $sheet->getRowDimension(2)->setRowHeight(24);
    $sheet->getRowDimension(8)->setRowHeight(72);
    $sheet->getRowDimension(9)->setRowHeight(22);
    for ($row = 10; $row <= $lastDataRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    $sheet->getRowDimension($row)->setRowHeight(24);
}

function formatFormXXIWorksheet($sheet, array $details)
{
    // The HTML reader flattens the three-part report header into unrelated
    // worksheet cells. It creates seven heading rows, so remove exactly those
    // rows; removing nine also discarded employees numbered 1 and 2.
    $sheet->removeRow(1, 7);
    $sheet->insertNewRowBefore(1, 8);
    $sheet->mergeCells('A1:L1');
    $sheet->mergeCells('A2:B2');
    $sheet->mergeCells('C2:D2');
    $sheet->mergeCells('A3:B3');
    $sheet->mergeCells('C3:D3');
    $sheet->mergeCells('A4:B4');
    $sheet->mergeCells('C4:D4');
    $sheet->mergeCells('A5:B5');
    $sheet->mergeCells('C5:D5');
    $sheet->mergeCells('E2:H2');
    $sheet->mergeCells('E3:H3');
    $sheet->mergeCells('E4:H5');
    $sheet->mergeCells('I2:L3');
    $sheet->mergeCells('I4:L5');
    $sheet->mergeCells('A6:L6');

    $sheet->setCellValue('A1', 'Register of Fines');
    $sheet->setCellValue('A2', 'NAME AND ADDRESS OF CONTRACTOR :');
    $sheet->setCellValue('C2', 'SHREE GANESH ENGINEERING CO.');
    $sheet->setCellValue('C3', 'FF-8, Devshruti Complex,');
    $sheet->setCellValue('C4', 'Nr. HCG Hospital,');
    $sheet->setCellValue('C5', 'Mithakhali, Ahmedabad - 380006');
    $sheet->setCellValue('E2', 'FORM NO. XXI');
    $sheet->setCellValue('E3', '[See rule 78 (2)(d)]');
    $sheet->setCellValue('E4', 'Month: ' . $details['month']);
    $sheet->setCellValue('I2', 'Name and Address of establishment in/under which contract is carried on');
    $sheet->setCellValue('I4', 'Name and Address of the principal Employer :  ' . $details['employer']);
    $sheet->setCellValue('A6', 'NATURE AND LOCATION OF WORK');

    $headings = array(
        "Sr.\nNo.",
        'Name of Workmen',
        "Father's / Husband's\nName",
        "Designation/\nNature of\nEmployment",
        "Act/Omission\nfor which fine\nimposed",
        "Date of\noffence",
        "Whether workman\nshowed cause\nagainst fine",
        "Name of person\nin whose presence\nworkman explanation\nwas heard",
        "Wage periods\nand wages payable",
        "Amount of fine\nimposed",
        "Date on which\nfine realised",
        'Remarks'
    );
    foreach ($headings as $index => $heading) {
        $sheet->setCellValueByColumnAndRow($index + 1, 7, $heading);
        $sheet->setCellValueByColumnAndRow($index + 1, 8, $index + 1);
    }

    $lastRow = $sheet->getHighestDataRow();
    // Serial numbers are derived from the final worksheet row order rather
    // than from HTML-reader offsets, guaranteeing an unbroken 1..N sequence.
    for ($row = 9, $serialNumber = 1; $row <= $lastRow; $row++, $serialNumber++) {
        $sheet->setCellValueByColumnAndRow(1, $row, $serialNumber);
    }
    $sheet->getStyle('A1:L' . $lastRow)->getFont()->setName('Times New Roman')->setSize(10);
    $sheet->getStyle('A1:L6')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(16);
    $sheet->getStyle('E2')->getFont()->setSize(13);
    $sheet->getStyle('E4')->getFont()->setSize(12);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E2:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A7:L8')->getFont()->setBold(true);
    $sheet->getStyle('A7:L8')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle('A7:L' . $lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()->setRGB('000000');
    $sheet->getStyle('A9:L' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $columnWidths = array(5, 27, 23, 18, 11, 8, 11, 12, 9, 9, 9, 11);
    foreach (range('A', 'L') as $index => $column) {
        $sheet->getColumnDimension($column)->setWidth($columnWidths[$index]);
    }
    $sheet->getRowDimension(1)->setRowHeight(24);
    foreach (range(2, 5) as $row) {
        $sheet->getRowDimension($row)->setRowHeight(18);
    }
    $sheet->getRowDimension(6)->setRowHeight(22);
    $sheet->getRowDimension(7)->setRowHeight(82);
    $sheet->getRowDimension(8)->setRowHeight(22);
    for ($row = 9; $row <= $lastRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(24);
    }

    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
    configureOtherReportPage($sheet, 'A1:L' . $lastRow, 9);
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
