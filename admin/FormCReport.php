<?php

/**
 * Shared data retrieval and template binding for the Form C exports.
 */
function getFormCEmployees($dbconn, $companyId, $salaryMonth)
{
    validateFormCDatabaseConnection($dbconn);
    // Keep this filter in step with Ajaxreport.php/newReport.php.  Exports must
    // not paginate: every matching salary detail belongs in the register.
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $salaryMonth = mysqli_real_escape_string($dbconn, $salaryMonth);
    $sql = "SELECT employee.emp_name
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
        throw new RuntimeException('Unable to retrieve Form C employees.');
    }

    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row['emp_name'];
    }
    return $employees;
}

function getFormCCompanyName($dbconn, $companyId)
{
    validateFormCDatabaseConnection($dbconn);
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $sql = "SELECT companyname
            FROM companymaster
            WHERE companymasterId = '" . $companyId . "'
              AND isDelete = '0'
              AND istatus = '1'
            LIMIT 1";

    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve the Form C company.');
    }

    $company = mysqli_fetch_assoc($result);
    if (!$company) {
        throw new RuntimeException('The selected company could not be found.');
    }

    return $company['companyname'];
}

function renderFormCHtml(array $employees, $salaryMonth, $companyName)
{
    $templatePath = __DIR__ . '/SGECO-forms/Form-C-complete.html';
    $template = file_get_contents($templatePath);
    if ($template === false) {
        throw new RuntimeException('The Form C template could not be loaded.');
    }

    if (!preg_match('/(<section class="form-page">.*?<tbody class="register-body">)(.*?)(<\/tbody>.*?<\/section>)/s', $template, $matches)) {
        throw new RuntimeException('The Form C template has an unexpected format.');
    }

    $serialNumber = 1;
    $rows = '';
    foreach ($employees as $employeeName) {
        $cells = '<td>' . $serialNumber . '</td><td class="name-cell">' . htmlspecialchars($employeeName, ENT_QUOTES, 'UTF-8') . '</td>';
        $cells .= str_repeat('<td>NIL</td>', 11);
        // Mark every employee as an indivisible PDF row. The shared PDF
        // formatter adds TCPDF's nobr attribute to data rows so a wrapped name
        // cannot be split above the repeated table heading on the next page.
        $rows .= '<tr class="data-row">' . $cells . '</tr>';
        $serialNumber++;
    }
    if (!$employees) {
        $rows = '<tr class="data-row">' . str_repeat('<td>NIL</td>', 13) . '</tr>';
    }
    $firstSection = strpos($template, $matches[0]);

    $html = substr($template, 0, $firstSection)
        . $matches[1] . $rows . $matches[3]
        . substr($template, $firstSection + strlen($matches[0]));

    $month = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if ($month === false || $month->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }

    return str_replace(
        array('{{FORM_C_MONTH}}', '{{FORM_C_COMPANY}}'),
        array($month->format('F-y'), htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8')),
        $html
    );
}

function validateFormCDatabaseConnection($dbconn)
{
    if (!($dbconn instanceof mysqli)) {
        throw new RuntimeException('A database connection could not be established.');
    }
}

function getFormCRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        http_response_code(400);
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    validateFormCDatabaseConnection($dbconn);
    return renderFormCHtml(
        getFormCEmployees($dbconn, $companyId, $salaryMonth),
        $salaryMonth,
        getFormCCompanyName($dbconn, $companyId)
    );
}
