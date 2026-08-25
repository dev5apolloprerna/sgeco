<?php

/**
 * Data retrieval and supplied-template binding for Form XXIII.
 *
 * Overtime is stored on each company salary-detail entry.  Keep one register
 * row per detail (rather than grouping employees) so repeated entries remain
 * visible.  totalovertime is the persisted earning produced by the existing
 * payroll calculation; it must not be recalculated by this report.
 */
function getFormXXIIIEntries($dbconn, $companyId, $salaryMonth)
{
    $companyId = (int) $companyId;
    $salaryMonthSql = mysqli_real_escape_string($dbconn, $salaryMonth);
    if ($companyId <= 0) {
        throw new InvalidArgumentException('A valid company is required.');
    }

    $genderExpression = "''";
    foreach (array('gender', 'strGender', 'sex', 'strSex', 'employeeGender') as $genderColumn) {
        $columnResult = mysqli_query($dbconn, "SHOW COLUMNS FROM employee LIKE '" . $genderColumn . "'");
        if ($columnResult && mysqli_num_rows($columnResult) > 0) {
            $genderExpression = 'e.`' . $genderColumn . '`';
            break;
        }
    }
    $sql = "SELECT sd.salarydetailsId, sd.strEntryDate, sd.othours, sd.otrate,
                   sd.skillrate, sd.totalovertime, sd.salarypaiddate,
                    e.emp_name, e.strFatherName, e.designation,
                   " . $genderExpression . " AS gender
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId = sd.emp_id AND e.isDelete = '0'
            WHERE sd.companyId = " . $companyId . "
              AND sd.salaryId IN (
                  SELECT salarymasterId FROM salarymaster
                  WHERE month = '" . $salaryMonthSql . "'
                    AND isDelete = '0' AND istatus = '1'
              )
              AND sd.isDelete = '0' AND sd.istatus = '1'
              AND CAST(sd.othours AS DECIMAL(10,2)) > 0
            ORDER BY sd.salarydetailsId ASC";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Form XXIII overtime entries.');
    }
    $entries = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row;
    }
    return $entries;
}

function getFormXXIIICompanyName($dbconn, $companyId)
{
    $result = mysqli_query($dbconn, "SELECT companyname FROM companymaster WHERE companymasterId="
        . (int) $companyId . " AND isDelete='0' AND istatus='1' LIMIT 1");
    $company = $result ? mysqli_fetch_assoc($result) : null;
    if (!$company) {
        throw new RuntimeException('The selected company could not be found.');
    }
    return $company['companyname'];
}

function formXXIIIFormatNumber($number)
{
    return rtrim(rtrim(number_format((float) $number, 2, '.', ''), '0'), '.');
}

function formXXIIIFormatAmount($number)
{
    return number_format((float) $number, 2, '.', '');
}

function formXXIIIFormatDate($date)
{
    $date = trim((string) $date);
    foreach (array('d-m-Y H:i:s', 'Y-m-d H:i:s', 'd-m-Y', 'Y-m-d') as $format) {
        $value = DateTime::createFromFormat('!' . $format, $date);
        if ($value && $value->format($format) === $date) {
            return $value->format('d-m-Y');
        }
    }
    return '';
}

function formXXIIIFormatMonth($salaryMonth)
{
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    return $period->format('F-Y');
}

function formXXIIIFormatWorkMonth($salaryMonth)
{
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    return $period->format('M-y');
}

/**
 * Bind the selected month to the template without depending on the example
 * value currently present in the HTML file.
 */
function formXXIIIApplyMonth($html, $salaryMonth)
{
    $formattedMonth = formXXIIIFormatMonth($salaryMonth);
    if (strpos($html, '{{FORM_XXIII_MONTH}}') !== false) {
        return str_replace(
            '{{FORM_XXIII_MONTH}}',
            htmlspecialchars($formattedMonth, ENT_QUOTES, 'UTF-8'),
            $html
        );
    }
    $replacementCount = 0;
    $html = preg_replace_callback(
        '/(<span\b[^>]*class\s*=\s*(["\'])[^"\']*\bmonth-label\b[^"\']*\2[^>]*>.*?Month\s*:.*?<\/span>\s*<span\b[^>]*>).*?(<\/span>)/is',
        function ($matches) use ($formattedMonth) {
            return $matches[1] . htmlspecialchars($formattedMonth, ENT_QUOTES, 'UTF-8') . $matches[3];
        },
        $html,
        -1,
        $replacementCount
    );
    if ($html === null || $replacementCount === 0) {
        throw new RuntimeException('The Form XXIII month field could not be populated.');
    }
    return $html;
}

function renderFormXXIIIHtml(array $entries, $salaryMonth, $companyName, $repeatPageHeaders = true)
{
    // Browser/Excel output uses complete repeated form sections. PDF output is
    // rendered as one continuous table so TCPDF can fill all available space
    // before creating the next physical page.
    $rowsPerFormPage = 17;
    $columnWidths = array('3%', '20%', '17%', '4%', '7%', '9%', '9%', '6%', '7%', '5%', '8%', '5%');
    $template = file_get_contents(__DIR__ . '/SGECO-forms/Register_of_Overtime_Form_XXIII_Legal_Landscape.html');
    if ($template === false || !preg_match('/(<section class="form-page">.*?<tbody>)(.*?)(<\/tbody>.*?<\/section>)/s', $template, $matches)) {
        throw new RuntimeException('The Form XXIII template could not be loaded.');
    }
    if (!preg_match('/<tr class="column-number-row">.*?<\/tr>/s', $matches[2], $numberRow)) {
        throw new RuntimeException('The Form XXIII template has an unexpected format.');
    }
    $esc = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $detailRows = array();
    $totals = array('hours' => 0, 'overtime_wages' => 0, 'earnings' => 0);
    foreach ($entries as $index => $entry) {
        // This intermediate amount is the template's "Overtime of wages":
        // daily normal wage multiplied by the stored payroll OT multiplier.
        $overtimeWages = (float) $entry['skillrate'] * (float) $entry['otrate'];
        $values = array(
            $index + 1,
            $entry['emp_name'],
            $entry['strFatherName'],
            'Male', // $entry['gender'],
            $entry['designation'],
            formXXIIIFormatWorkMonth($salaryMonth), // formXXIIIFormatDate($entry['strEntryDate']),
            formXXIIIFormatNumber($entry['othours']),
            formXXIIIFormatAmount($entry['skillrate']), // formXXIIIFormatNumber($entry['skillrate']),
            formXXIIIFormatAmount($overtimeWages), // formXXIIIFormatNumber($overtimeWages),
            formXXIIIFormatAmount($entry['totalovertime']), // formXXIIIFormatNumber($entry['totalovertime']),
            formXXIIIFormatDate($entry['salarypaiddate']),
            ''
        );
        $row = '<tr class="data-row">';
        foreach ($values as $column => $value) {
            $row .= '<td width="' . $columnWidths[$column] . '" class="'
                // . (in_array($column, array(1, 2, 4), true) ? 'text-left' : 'text-center')
                . (in_array($column, array(1, 2, 4), true) ? 'text-left'
                    : (in_array($column, array(7, 8, 9), true) ? 'amount' : 'text-center'))
                . '">' . $esc($value) . '</td>';
        }
        $detailRows[] = $row . '</tr>';
        $totals['hours'] += (float) $entry['othours'];
        $totals['overtime_wages'] += $overtimeWages;
        $totals['earnings'] += (float) $entry['totalovertime'];
    }
    $totalValues = array(
        '',
        '<strong>TOTAL</strong>',
        '',
        '',
        '',
        '',
        formXXIIIFormatNumber($totals['hours']),
        '',
        formXXIIIFormatAmount($totals['overtime_wages']),
        formXXIIIFormatAmount($totals['earnings']),
        '',
        ''
    );
    $totalRow = '<tr class="total-row" nobr="true">';
    foreach ($totalValues as $column => $value) {
        $totalRow .= '<td width="' . $columnWidths[$column] . '" class="'
            . ($column === 1 ? 'text-left' : (in_array($column, array(7, 8, 9), true) ? 'amount' : 'text-center'))
            . '">' . $value . '</td>';
    }
    $detailRows[] = $totalRow . '</tr>';

    $prefix = formXXIIIApplyMonth($matches[1], $salaryMonth);
    $prefix = str_replace('{{FORM_XXIII_COMPANY}}', $esc($companyName), $prefix);

    $rowPages = $detailRows
        ? ($repeatPageHeaders ? array_chunk($detailRows, $rowsPerFormPage) : array($detailRows))
        : array(array());
    $sections = array();
    foreach ($rowPages as $pageIndex => $pageRows) {
        $pagePrefix = $prefix;
        $pageNumberRow = $numberRow[0];
        if (!$repeatPageHeaders && $pageIndex > 0) {
            // PDF continuation pages are part of the same register. Keep the
            // table grid, but do not reprint either the legal form heading or
            // the table/column-number headings on every explicitly split page.
            $pagePrefix = preg_replace(
                '/(<section\b[^>]*class=("|\')[^"\']*\bform-page\b[^"\']*\2[^>]*>\s*<table\b[^>]*class=("|\')[^"\']*\bpage-layout\b[^"\']*\3[^>]*>\s*<tr>\s*<td>).*?(<div\b[^>]*class=("|\')[^"\']*\bregister-frame\b[^"\']*\5[^>]*>)/is',
                '$1$4',
                $pagePrefix,
                1
            );
            $pagePrefix = preg_replace('/<thead\b[^>]*>.*?<\/thead>/is', '', $pagePrefix, 1);
            $pageNumberRow = '';
        }
        $sections[] = $pagePrefix . $pageNumberRow . implode('', $pageRows) . $matches[3];
    }
    $firstSection = strpos($template, $matches[0]);
    return substr($template, 0, $firstSection) . implode('', $sections)
        . substr($template, $firstSection + strlen($matches[0]));
}

function getFormXXIIIRequestData($dbconn, $repeatPageHeaders = true)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    return renderFormXXIIIHtml(
        getFormXXIIIEntries($dbconn, $companyId, $salaryMonth),
        $salaryMonth,
        getFormXXIIICompanyName($dbconn, $companyId),
        $repeatPageHeaders
    );
}
