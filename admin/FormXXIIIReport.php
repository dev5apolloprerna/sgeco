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

    $sql = "SELECT sd.salarydetailsId, sd.strEntryDate, sd.othours, sd.otrate,
                   sd.skillrate, sd.totalovertime, sd.salarypaiddate,
                   e.emp_name, e.strFatherName, e.designation
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

function renderFormXXIIIHtml(array $entries, $salaryMonth, $companyName)
{
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
    $rows = $numberRow[0];
    foreach ($entries as $index => $entry) {
        // This intermediate amount is the template's "Overtime of wages":
        // daily normal wage multiplied by the stored payroll OT multiplier.
        $overtimeWages = (float) $entry['skillrate'] * (float) $entry['otrate'];
        $values = array(
            $index + 1,
            $entry['emp_name'],
            $entry['strFatherName'],
            '',
            $entry['designation'],
            formXXIIIFormatDate($entry['strEntryDate']),
            formXXIIIFormatNumber($entry['othours']),
            formXXIIIFormatNumber($entry['skillrate']),
            formXXIIIFormatNumber($overtimeWages),
            formXXIIIFormatNumber($entry['totalovertime']),
            formXXIIIFormatDate($entry['salarypaiddate']),
            ''
        );
        $rows .= '<tr class="data-row">';
        foreach ($values as $column => $value) {
            $rows .= '<td class="' . (in_array($column, array(1, 2, 4), true) ? 'text-left' : 'text-center')
                . '">' . $esc($value) . '</td>';
        }
        $rows .= '</tr>';
    }

    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    $prefix = preg_replace(
        '/(<span class="month-label">Month :<\/span>)\s*<span>.*?<\/span>/s',
        '$1<span>' . $esc($period->format('F-Y')) . '</span>',
        $matches[1],
        1
    );
    $prefix = preg_replace(
        '/(Name and Address of the principal Employer\s*:\s*<span class="normal">).*?(<\/span>)/s',
        '$1' . $esc($companyName) . '$2',
        $prefix,
        1
    );
    $firstSection = strpos($template, $matches[0]);
    return substr($template, 0, $firstSection) . $prefix . $rows . $matches[3]
        . substr($template, $firstSection + strlen($matches[0]));
}

function getFormXXIIIRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    return renderFormXXIIIHtml(
        getFormXXIIIEntries($dbconn, $companyId, $salaryMonth),
        $salaryMonth,
        getFormXXIIICompanyName($dbconn, $companyId)
    );
}
