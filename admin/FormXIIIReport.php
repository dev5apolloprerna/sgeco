<?php

/**
 * Data retrieval and supplied-template binding for Form XIII.
 */
function getFormXIIIEmployees($dbconn, $companyId, $salaryMonth)
{
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $salaryMonth = mysqli_real_escape_string($dbconn, $salaryMonth);
    $sql = "SELECT employee.emp_name, employee.dateofbirth, employee.strFatherName,
                   employee.designation, employee.strPermanentAddress, employee.address,
                   employee.dateofjoining, employee.strExitDate
            FROM salarydetails
            INNER JOIN employee ON employee.employeeId = salarydetails.emp_id
                AND employee.isDelete = '0'
            WHERE salarydetails.companyId = '" . $companyId . "'
              AND salarydetails.salaryId IN (
                  SELECT salarymasterId FROM salarymaster
                  WHERE month = '" . $salaryMonth . "'
                    AND isDelete = '0' AND istatus = '1'
              )
              AND salarydetails.isDelete = '0'
              AND salarydetails.istatus = '1'
              AND salarydetails.workingdays > 0
            ORDER BY salarydetails.salarydetailsId ASC";

    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Form XIII employees.');
    }

    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function getFormXIIICompanyName($dbconn, $companyId)
{
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $result = mysqli_query(
        $dbconn,
        "SELECT companyname FROM companymaster
         WHERE companymasterId = '" . $companyId . "' AND isDelete = '0' AND istatus = '1' LIMIT 1"
    );
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve the Form XIII company.');
    }
    $company = mysqli_fetch_assoc($result);
    if (!$company) {
        throw new RuntimeException('The selected company could not be found.');
    }
    return $company['companyname'];
}

function formatFormXIIIDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return '';
    }
    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date('d-m-Y', $timestamp);
}

function getFormXIIIAge($dateOfBirth, $salaryMonth)
{
    $timestamp = strtotime(trim((string) $dateOfBirth));
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if ($timestamp === false || !$period) {
        return '';
    }
    $birthDate = new DateTime(date('Y-m-d', $timestamp));
    $period->modify('last day of this month');
    return $birthDate > $period ? '' : (string) $birthDate->diff($period)->y;
}

function renderFormXIIIHtml(array $employees, $salaryMonth, $companyName)
{
    $template = file_get_contents(__DIR__ . '/SGECO-forms/Register-of-workmen-complete.html');
    if ($template === false) {
        throw new RuntimeException('The Form XIII template could not be loaded.');
    }
    if (!preg_match('/(<section class="form-page">.*?<tbody>)(.*?)(<\/tbody>.*?<\/section>)/s', $template, $matches)) {
        throw new RuntimeException('The Form XIII template has an unexpected format.');
    }
    if (!preg_match('/<tr class="column-number-row">.*?<\/tr>/s', $matches[2], $numberRow)) {
        throw new RuntimeException('The Form XIII column-number row could not be found.');
    }

    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    $header = preg_replace(
        '/(<div class="rule-text">\[See Rule 75\]<\/div>)/',
        '$1<div class="rule-text"><strong>Month: ' . htmlspecialchars($period->format('F-Y'), ENT_QUOTES, 'UTF-8') . '</strong></div>',
        $matches[1],
        1
    );
    $header = preg_replace(
        '/(Name and Address of the principal Employer\s*:\s*<span class="normal-weight">).*?(<\/span>)/s',
        '$1' . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . '$2',
        $header,
        1
    );

    $rows = $numberRow[0];
    foreach ($employees as $index => $employee) {
        $cells = array(
            $index + 1,
            $employee['emp_name'],
            getFormXIIIAge($employee['dateofbirth'], $salaryMonth),
            $employee['strFatherName'],
            $employee['designation'],
            $employee['strPermanentAddress'],
            $employee['address'],
            formatFormXIIIDate($employee['dateofjoining']),
            '',
            formatFormXIIIDate($employee['strExitDate']),
            '',
            ''
        );
        $rows .= '<tr class="data-row">';
        foreach ($cells as $cellIndex => $value) {
            $class = in_array($cellIndex, array(0, 2, 7, 8, 9), true) ? 'text-center' : 'text-left';
            $rows .= '<td class="' . $class . '">' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        $rows .= '</tr>';
    }

    $firstSection = strpos($template, $matches[0]);
    $html = substr($template, 0, $firstSection)
        . $header . $rows . $matches[3]
        . substr($template, $firstSection + strlen($matches[0]));

    // The supplied example is a single fixed-height sheet. Report results can
    // contain more rows, so allow the register to grow rather than clipping
    // otherwise applicable employees in the on-screen preview or exports.
    return preg_replace(
        '/<\/head>/i',
        '<style>.form-page { height: auto; min-height: 8.5in; overflow: visible; }</style></head>',
        $html,
        1
    );
}

function getFormXIIIRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    return renderFormXIIIHtml(
        getFormXIIIEmployees($dbconn, $companyId, $salaryMonth),
        $salaryMonth,
        getFormXIIICompanyName($dbconn, $companyId)
    );
}
