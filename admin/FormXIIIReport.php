<?php

/**
 * Data retrieval and supplied-template binding for Form XIII.
 */
function getFormXIIIEmployees($dbconn, $companyId, $salaryMonth)
{
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $salaryMonth = mysqli_real_escape_string($dbconn, $salaryMonth);
    $genderExpression = "''";
    foreach (array('gender', 'strGender', 'sex', 'strSex', 'employeeGender') as $genderColumn) {
        $columnResult = mysqli_query($dbconn, "SHOW COLUMNS FROM employee LIKE '" . $genderColumn . "'");
        if ($columnResult && mysqli_num_rows($columnResult) > 0) {
            $genderExpression = 'employee.`' . $genderColumn . '`';
            break;
        }
    }
    $sql = "SELECT employee.emp_name, employee.dateofbirth, " . $genderExpression . " AS gender,
                   employee.strFatherName,
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

function formatFormXIIIAgeAndSex($dateOfBirth, $gender, $salaryMonth)
{
    $age = getFormXIIIAge($dateOfBirth, $salaryMonth);
    $gender = trim((string) $gender);
    if ($age === '') {
        return $gender;
    }
    return $gender === '' ? $age : $age . ' / ' . $gender;
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
    $dateOfBirth = trim((string) $dateOfBirth);
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if ($dateOfBirth === '' || !$period) {
        return '';
    }
    // Employee records are entered through a dd-mm-yyyy date picker, while
    // older imports also contain slash-separated and ISO dates. strtotime()
    // cannot parse values such as 31/12/1990, so parse the known database
    // formats explicitly before calculating the age.
    $birthDate = false;
    foreach (array('!d-m-Y', '!d/m/Y', '!Y-m-d', '!Y/m/d') as $format) {
        $candidate = DateTime::createFromFormat($format, $dateOfBirth);
        $errors = DateTime::getLastErrors();
        if ($candidate && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            $birthDate = $candidate;
            break;
        }
    }
    if (!$birthDate) {
        return '';
    }
    
    $period->modify('last day of this month');
    return $birthDate > $period ? '' : (string) $birthDate->diff($period)->y;
}

function renderFormXIIIHtml(array $employees, $salaryMonth, $companyName)
{
    $columnWidths = array('3%', '12%', '5%', '14%', '7%', '16%', '13%', '7%', '6.5%', '7%', '6%', '5%');
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
    $header = str_replace(
        array('{{FORM_XIII_MONTH}}', '{{FORM_XIII_COMPANY}}'),
        array(
            htmlspecialchars($period->format('F-Y'), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8')
        ),
        $matches[1]
    );

    $rows = $numberRow[0];
    foreach ($employees as $index => $employee) {
        $cells = array(
            $index + 1,
            $employee['emp_name'],
            formatFormXIIIAgeAndSex($employee['dateofbirth'], $employee['gender'], $salaryMonth),
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
            $rows .= '<td width="' . $columnWidths[$cellIndex] . '" class="' . $class . '">'
                . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</td>';
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
