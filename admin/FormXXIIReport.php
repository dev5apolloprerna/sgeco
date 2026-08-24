<?php

/**
 * Data retrieval and supplied-template binding for Form XXII.
 */
function getFormXXIIEmployees($dbconn, $companyId, $salaryMonth)
{
    $companyId = (int) $companyId;
    $salaryMonthSql = mysqli_real_escape_string($dbconn, $salaryMonth);
    if ($companyId <= 0) {
        throw new InvalidArgumentException('A valid company is required.');
    }

    $sql = "SELECT e.employeeId, e.emp_name, e.strFatherName, e.designation,
                   MAX(CAST(sd.workingdays AS DECIMAL(10,2))) AS workingdays,
                   MAX(CAST(sd.skillrate AS DECIMAL(10,2))) AS wage_rate
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId = sd.emp_id AND e.isDelete = '0'
            WHERE sd.companyId = " . $companyId . "
              AND sd.salaryId IN (
                  SELECT salarymasterId FROM salarymaster
                  WHERE month = '" . $salaryMonthSql . "' AND isDelete = '0' AND istatus = '1'
              )
              AND sd.isDelete = '0' AND sd.istatus = '1' AND sd.workingdays > 0
            GROUP BY e.employeeId, e.emp_name, e.strFatherName, e.designation
            ORDER BY MIN(sd.salarydetailsId) ASC";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Form XXII employees.');
    }

    $employees = array();
    $employeeIndexes = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $row['instalments'] = array();
        $row['advance_total'] = 0;
        $row['last_advance_date'] = '';
        $employeeIndexes[(int) $row['employeeId']] = count($employees);
        $employees[] = $row;
    }
    if (!$employees) {
        return $employees;
    }

    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    $periodStart = $period->format('Y-m-01');
    $periodEnd = (clone $period)->modify('+1 month')->format('Y-m-01');
    // An advance belongs to a reporting period through advanced_master; the
    // monetary/date/remarks entries for that period live in advanced_details.
    // Keep both sides of that relationship in this query instead of inferring
    // a period from advanced_details alone.
    $advanceSql = "SELECT ad.iEmployeeId, ad.strDate, ad.iAmount, ad.strRemarks
                   FROM advanced_master am
                   INNER JOIN advanced_details ad
                     ON ad.iAdvancedMasterId = am.iAdvancedMasterId
                    AND ad.iCompanyId = am.iCompanyId
                   WHERE am.iCompanyId = " . $companyId . "
                     AND am.strMonthYear = '" . $salaryMonthSql . "'
                     AND am.isDelete = 0 AND am.istatus = 1
                     AND ad.strDate >= '" . mysqli_real_escape_string($dbconn, $periodStart) . "'
                     AND ad.strDate < '" . mysqli_real_escape_string($dbconn, $periodEnd) . "'
                   ORDER BY ad.strDate ASC";
    $advanceResult = mysqli_query($dbconn, $advanceSql);
    if ($advanceResult === false) {
        throw new RuntimeException('Unable to retrieve Form XXII advance details.');
    }
    while ($advance = mysqli_fetch_assoc($advanceResult)) {
        $employeeId = (int) $advance['iEmployeeId'];
        if (!isset($employeeIndexes[$employeeId])) {
            continue;
        }
        $index = $employeeIndexes[$employeeId];
        $employees[$index]['instalments'][] = array(
            'date' => $advance['strDate'],
            'amount' => (float) $advance['iAmount'],
            'purpose' => formXXIIAdvancePurpose($advance['strRemarks'])
        );
        $employees[$index]['advance_total'] += (float) $advance['iAmount'];
        $employees[$index]['last_advance_date'] = $advance['strDate'];
    }
    return $employees;
}

function formXXIIAdvancePurpose($remarks)
{
    $remarks = trim((string) $remarks);
    if ($remarks === '') {
        return '';
    }
    if (preg_match('/\bpersonal\b/i', $remarks)) {
        return 'Personal';
    }
    if (preg_match('/\bfood\s*(allowance|allow\.?|allow)?\b/i', $remarks)) {
        return 'Food Allowance';
    }
    return '';
}

function getFormXXIICompanyName($dbconn, $companyId)
{
    $result = mysqli_query(
        $dbconn,
        "SELECT companyname FROM companymaster WHERE companymasterId=" . (int) $companyId
            . " AND isDelete='0' AND istatus='1' LIMIT 1"
    );
    $company = $result ? mysqli_fetch_assoc($result) : null;
    if (!$company) {
        throw new RuntimeException('The selected company could not be found.');
    }
    return $company['companyname'];
}

function formXXIIFormatNumber($number)
{
    return rtrim(rtrim(number_format((float) $number, 2, '.', ''), '0'), '.');
}

function formXXIIFormatDate($date)
{
    $value = DateTime::createFromFormat('!Y-m-d', $date);
    return $value ? $value->format('d/m/Y') : '';
}

function renderFormXXIIHtml(array $employees, $salaryMonth, $companyName)
{
    // TCPDF does not reliably apply CSS-only colgroup widths, so keep the
    // register proportions on each outer cell as well as in the template.
    $columnWidths = array('3%', '18%', '15%', '9%', '9%', '9%', '8%', '8%', '8%', '8%', '5%');
    $template = file_get_contents(__DIR__ . '/SGECO-forms/Register-of-advance-complete.html');
    if ($template === false || !preg_match('/(<section class="form-page">.*?<tbody>)(.*?)(<\/tbody>.*?<\/section>)/s', $template, $matches)) {
        throw new RuntimeException('The Form XXII template could not be loaded.');
    }
    if (!preg_match('/<tr class="column-number-row">.*?<\/tr>/s', $matches[2], $numberRow)) {
        throw new RuntimeException('The Form XXII template has an unexpected format.');
    }

    $rows = $numberRow[0];
    foreach ($employees as $index => $employee) {
        $esc = function ($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };
        $lastDate = formXXIIFormatDate($employee['last_advance_date']);
        $wageTotal = (float) $employee['workingdays'] * (float) $employee['wage_rate'];
        $wages = formXXIIFormatNumber($employee['workingdays']) . ' Days &times; Rs. '
            . formXXIIFormatNumber($employee['wage_rate']) . ' = Rs. '
            . formXXIIFormatNumber($wageTotal) . '/-';
        $advance = $lastDate === '' ? '' : $lastDate . '<br>Rs. '
            . formXXIIFormatNumber($employee['advance_total']) . '/-';
        $instalments = '';
        $purposes = array();
        foreach ($employee['instalments'] as $instalment) {
            $instalments .= '<tr><td><span class="installment-date">'
                . $esc(formXXIIFormatDate($instalment['date'])) . '</span>Rs. '
                . $esc(formXXIIFormatNumber($instalment['amount'])) . '/-</td></tr>';
            if ($instalment['purpose'] !== '') {
                $purposes[$instalment['purpose']] = true;
            }
        }
        $purpose = implode(' / ', array_keys($purposes));
        $rows .= '<tr class="data-row">'
            . '<td width="' . $columnWidths[0] . '" class="center">' . ($index + 1) . '</td>'
            . '<td width="' . $columnWidths[1] . '" class="left">' . $esc($employee['emp_name']) . '</td>'
            . '<td width="' . $columnWidths[2] . '" class="left">' . $esc($employee['strFatherName']) . '</td>'
            . '<td width="' . $columnWidths[3] . '" class="left">' . $esc($employee['designation']) . '</td>'
            . '<td width="' . $columnWidths[4] . '" class="center">' . $wages . '</td>'
            . '<td width="' . $columnWidths[5] . '" class="center">' . $advance . '</td>'
            . '<td width="' . $columnWidths[6] . '" class="center">' . $esc($purpose) . '</td>'
            . '<td width="' . $columnWidths[7] . '" class="center">' . count($employee['instalments']) . '</td>'
            . '<td width="' . $columnWidths[8] . '"><table class="installments">' . $instalments . '</table></td>'
            . '<td width="' . $columnWidths[9] . '" class="center">' . $esc($lastDate) . '</td>'
            . '<td width="' . $columnWidths[10] . '"></td></tr>';
    }

    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    $prefix = str_replace(
        '{{FORM_XXII_MONTH}}',
        htmlspecialchars($period->format('F-Y'), ENT_QUOTES, 'UTF-8'),
        $matches[1]
    );
    $escapedCompanyName = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
    $prefix = str_replace('{{FORM_XXII_COMPANY}}', $escapedCompanyName, $prefix);
    $firstSection = strpos($template, $matches[0]);
    return substr($template, 0, $firstSection) . $prefix . $rows . $matches[3]
        . substr($template, $firstSection + strlen($matches[0]));
}

function getFormXXIIRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? trim($_GET['Company']) : '';
    $salaryMonth = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    if ($companyId === '' || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    return renderFormXXIIHtml(
        getFormXXIIEmployees($dbconn, $companyId, $salaryMonth),
        $salaryMonth,
        getFormXXIICompanyName($dbconn, $companyId)
    );
}
