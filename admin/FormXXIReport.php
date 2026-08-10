<?php

/**
 * Data retrieval and supplied-template binding for Form XXI exports.
 */
function getFormXXIEmployees($dbconn, $companyId, $salaryMonth)
{
    // This is the same salary-detail filter used by Form C and Ajaxreport.php.
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
        throw new RuntimeException('Unable to retrieve Form XXI employees.');
    }

    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function renderFormXXIHtml(array $employees, $salaryMonth)
{
    $templatePath = __DIR__ . '/SGECO-forms/Register-of-fine-complete.html';
    $template = file_get_contents($templatePath);
    if ($template === false) {
        throw new RuntimeException('The Form XXI template could not be loaded.');
    }

    if (!preg_match('/(<section class="form-page">.*?<tbody>)(.*?)(<\/tbody>.*?<\/section>)/s', $template, $matches)) {
        throw new RuntimeException('The Form XXI template has an unexpected format.');
    }

    $columnNumberRow = '';
    if (preg_match('/<tr class="column-number-row">.*?<\/tr>/s', $matches[2], $numberRowMatch)) {
        $columnNumberRow = $numberRowMatch[0];
    }
    if ($columnNumberRow === '') {
        throw new RuntimeException('The Form XXI column-number row could not be found.');
    }

    $sectionPrefix = $matches[1];
    $sectionSuffix = $matches[3];
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    $periodLabel = $period ? $period->format('F-Y') : $salaryMonth;
    $sectionPrefix = preg_replace(
        '/(<span class="month-label">Month:<\/span>\s*<span>).*?(<\/span>)/s',
        '$1' . htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8') . '$2',
        $sectionPrefix,
        1
    );

    // The supplied legal-landscape design comfortably holds twelve data rows.
    $pages = count($employees) ? array_chunk($employees, 12) : array(array());
    $sections = '';
    $serialNumber = 1;
    foreach ($pages as $pageEmployees) {
        $rows = $columnNumberRow;
        foreach ($pageEmployees as $employee) {
            $dynamicCells = array(
                $serialNumber++,
                isset($employee['emp_name']) ? $employee['emp_name'] : '',
                isset($employee['strFatherName']) ? $employee['strFatherName'] : '',
                isset($employee['designation']) ? $employee['designation'] : ''
            );
            $rows .= '<tr class="data-row">';
            foreach ($dynamicCells as $index => $value) {
                $class = $index === 0 ? 'text-center' : 'text-left';
                $rows .= '<td class="' . $class . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $rows .= str_repeat('<td class="text-center">NIL</td>', 8) . '</tr>';
        }
        $sections .= $sectionPrefix . $rows . $sectionSuffix;
    }

    $firstSection = strpos($template, $matches[0]);
    return substr($template, 0, $firstSection)
        . $sections
        . substr($template, $firstSection + strlen($matches[0]));
}

function getFormXXIRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        http_response_code(400);
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    return renderFormXXIHtml(getFormXXIEmployees($dbconn, $companyId, $salaryMonth), $salaryMonth);
}
