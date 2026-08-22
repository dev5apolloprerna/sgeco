<?php

/**
 * Data retrieval and supplied-template binding for Form XX.
 */
function getFormXXEmployees($dbconn, $companyId, $salaryMonth)
{
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $salaryMonth = mysqli_real_escape_string($dbconn, $salaryMonth);
    $sql = "SELECT employee.emp_name, employee.strFatherName, employee.designation
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
        throw new RuntimeException('Unable to retrieve Form XX employees.');
    }

    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function getFormXXCompanyName($dbconn, $companyId)
{
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $sql = "SELECT companyname FROM companymaster
            WHERE companymasterId = '" . $companyId . "'
              AND isDelete = '0' AND istatus = '1' LIMIT 1";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve the Form XX company.');
    }
    $company = mysqli_fetch_assoc($result);
    if (!$company) {
        throw new RuntimeException('The selected company could not be found.');
    }
    return $company['companyname'];
}

function renderFormXXHtml(array $employees, $salaryMonth, $companyName)
{
    $templatePath = __DIR__ . '/SGECO-forms/Register-of-deduction-for-damage-or-loss-complete.html';
    $template = file_get_contents($templatePath);
    if ($template === false) {
        throw new RuntimeException('The Form XX template could not be loaded.');
    }
    if (!preg_match('/(<section class="form-page">.*?<tbody>)(.*?)(<\/tbody>.*?<\/section>)/s', $template, $matches)) {
        throw new RuntimeException('The Form XX template has an unexpected format.');
    }
    if (!preg_match('/<tr class="column-number-row">.*?<\/tr>/s', $matches[2], $numberRowMatch)) {
        throw new RuntimeException('The Form XX column-number row could not be found.');
    }

    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    $periodLabel = $period ? $period->format('F-Y') : $salaryMonth;
    $prefix = preg_replace(
        '/(<span class="month-label">.*?Month:.*?<\/span>\s*<span>).*?(<\/span>)/s',
        '$1' . htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8') . '$2',
        $matches[1],
        1
    );

    $rows = $numberRowMatch[0];
    $serialNumber = 1;
    foreach ($employees as $employee) {
        $values = array(
            $serialNumber++,
            isset($employee['emp_name']) ? $employee['emp_name'] : '',
            isset($employee['strFatherName']) ? $employee['strFatherName'] : '',
            isset($employee['designation']) ? $employee['designation'] : ''
        );
        $rows .= '<tr class="data-row">';
        foreach ($values as $index => $value) {
            $rows .= '<td class="' . ($index === 0 ? 'text-center' : 'text-left') . '">'
                . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        // Form XX has thirteen statutory columns. The first four contain the
        // employee details and columns 5 through 13 contain deduction data.
        $rows .= str_repeat('<td class="text-center">NIL</td>', 9) . '</tr>';
    }

    $sectionPosition = strpos($template, $matches[0]);
    $html = substr($template, 0, $sectionPosition) . $prefix . $rows . $matches[3]
        . substr($template, $sectionPosition + strlen($matches[0]));
    return str_replace(
        '{{FORM_XX_COMPANY}}',
        htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'),
        $html
    );
}

function getFormXXRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        http_response_code(400);
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    return renderFormXXHtml(
        getFormXXEmployees($dbconn, $companyId, $salaryMonth),
        $salaryMonth,
        getFormXXCompanyName($dbconn, $companyId)
    );
}
