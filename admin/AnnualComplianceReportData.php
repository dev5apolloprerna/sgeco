<?php
require_once __DIR__ . '/BonusFormCReport.php';

function annualComplianceYear($value)
{
    if (!preg_match('/^\d{4}$/D', (string) $value) || (int) $value < 1900 || (int) $value > 9998) {
        throw new InvalidArgumentException('Select a valid financial year.');
    }
    return (int) $value;
}

function annualComplianceMonths($year)
{
    $year = annualComplianceYear($year);
    $months = array();
    for ($i = 0; $i < 12; $i++) {
        $months[] = sprintf('%02d/%04d', ($i + 3) % 12 + 1, $year + ($i >= 9 ? 1 : 0));
    }
    return $months;
}

/** One row per employee, using the existing salary report's active-record filters. */
function annualComplianceAggregate(array $records, $year)
{
    $start = new DateTime(annualComplianceYear($year) . '-04-01');
    $employees = array();
    foreach ($records as $record) {
        $id = $record['employeeId'];
        if (!isset($employees[$id])) {
            $employees[$id] = array(
                'name' => $record['emp_name'],
                'father' => $record['strFatherName'],
                'adult' => bonusFormCAdult($record['dateofbirth'], $start),
                'designation' => $record['designation'] === '0' ? '' : $record['designation'],
                'days' => 0,
                'salary_cents' => 0,
                'paid_cents' => 0,
                'dates' => array()
            );
        }
        $row = &$employees[$id];
        $row['days'] += (float) $record['workingdays'];
        $row['salary_cents'] += (int) round((float) $record['basicwages'] * 100);
        $row['paid_cents'] += (int) round((float) $record['iBonusAmt'] * 100);
        if ((float) $record['iBonusAmt'] != 0) {
            $date = bonusFormCDate($record['salarypaiddate']);
            if ($date !== '0') {
                $row['dates'][$date] = true;
            }
        }
        unset($row);
    }
    foreach ($employees as &$row) {
        $row['salary'] = $row['salary_cents'] / 100;
        $row['bonus'] = round($row['salary_cents'] * 0.0833) / 100;
        $row['paid'] = $row['paid_cents'] / 100;
        $row['payment_date'] = implode(', ', array_keys($row['dates']));
    }
    unset($row);
    return array_values($employees);
}

function getAnnualComplianceData($dbconn, array $request)
{
    $year = annualComplianceYear(isset($request['Year']) ? $request['Year'] : '');
    $company = isset($request['Company']) ? $request['Company'] : '';
    if (!ctype_digit((string) $company) || (int) $company < 1) {
        throw new InvalidArgumentException('Select a company.');
    }
    $companyName = getBonusFormCCompanyName($dbconn, (int) $company);
    // All twelve values are generated from the strictly validated year.
    $months = "'" . implode("','", annualComplianceMonths($year)) . "'";
    $sql = "SELECT e.employeeId, e.emp_name, e.strFatherName, e.dateofbirth, e.designation,
                   sd.workingdays, sd.basicwages, sd.iBonusAmt, sd.salarypaiddate
            FROM salarydetails sd
            INNER JOIN salarymaster sm ON sm.salarymasterId=sd.salaryId
            INNER JOIN employee e ON e.employeeId=sd.emp_id AND e.isDelete='0'
            WHERE sd.companyId=" . (int) $company . " AND sm.month IN (" . $months . ")
              AND sm.isDelete='0' AND sm.istatus='1'
              AND sd.isDelete='0' AND sd.istatus='1'
              AND (sd.workingdays > 0 OR sd.basicwages <> 0 OR sd.iBonusAmt <> 0)
            ORDER BY e.emp_name, e.employeeId, sd.salarydetailsId";
    $result = mysqli_query($dbconn, $sql);
    if (!$result) {
        throw new RuntimeException('Unable to retrieve annual salary records.');
    }
    $records = array();
    while ($record = mysqli_fetch_assoc($result)) {
        $records[] = $record;
    }
    return array(
        'year' => $year,
        'company' => $companyName,
        'rows' => annualComplianceAggregate($records, $year)
    );
}

function annualComplianceHeaders()
{
    return array(
        'Sr. No.',
        'Name of employee',
        "Name of employee father’s name",
        'Whether he has completed 15 years of age at the beginning of the accounting year',
        'Designation',
        'No. of days worked in year',
        'Total salary or wage in respect of the accounting year',
        'Amount of bonus payable under section 10/11 as the case may be',
        'Puja or other customary bonus paid during the accounting year',
        'Interim bonus or bonus paid in advance',
        'Deduction on account of financial loss caused by misconduct of the employee',
        'Total sum deducted (cols 9+10+11)',
        'Net amount payable (col 8 minus 12)',
        'Amount actually paid',
        'Date on which paid',
        'Signature / thumb impression of the employee',
        'Remarks'
    );
}

function annualComplianceValues(array $row, $index)
{
    // Deductions are not recorded in the existing salary data; leave their
    // individual fields blank rather than inventing deduction transactions.
    return array(
        $index + 1,
        $row['name'],
        $row['father'],
        $row['adult'],
        $row['designation'],
        $row['days'],
        $row['salary'],
        $row['bonus'],
        '',
        '',
        '',
        0,
        $row['bonus'],
        $row['paid'],
        $row['payment_date'],
        '',
        ''
    );
}

function annualComplianceTotalDays(array $rows)
{
    return array_sum(array_column($rows, 'days'));
}

function renderAnnualComplianceHtml(array $data)
{
    $e = function ($s) {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    };
    $totalDays = bonusFormCDaysNumber(annualComplianceTotalDays($data['rows']));
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:"Times New Roman",serif;font-size:11px;margin:12px}table{border-collapse:collapse;width:100%;table-layout:fixed}th,td{border:1px solid #000;padding:4px;vertical-align:middle;overflow-wrap:break-word}h2,p{text-align:center}thead{display:table-header-group}.form-heading td{border:0;text-align:center}.details td{border:0;text-align:left}.number{text-align:right}@media screen{body{min-width:1900px}.register th{height:140px}.register td{height:30px}}</style></head><body>';
    $html .= '<table class="form-heading"><tr><td><strong>FORM C<br>[See Rule 4 D]</strong></td></tr><tr><td>Bonus paid to employees for the accounting year ending on the F.Y. April ' . $data['year'] . ' to March ' . ($data['year'] + 1) . '</td></tr></table>';
    // Match the workbook rows A4:Q8: contractor/nature on the left,
    // establishment, principal employer and total worked days on the right.
    $html .= '<table class="details" cellpadding="4"><tr>'
        . '<td width="52%" rowspan="3">Name and address of Contractor:<br>SHREE GANESH ENGINEERING CO.<br>FF-8, Devshruti Complex, Nr. HCG Hospital,<br>Mithakhali, Ahmedabad - 380006</td>'
        . '<td width="23%" rowspan="2">Name &amp; Address of Establishment in/under which contract is carried on:</td>'
        . '<td width="25%" rowspan="2">' . $e($data['company']) . '</td></tr><tr></tr><tr>'
        . '<td width="23%" rowspan="2">Name &amp; Address of Principal Employer:</td>'
        . '<td width="25%" rowspan="2">' . $e($data['company']) . '</td></tr><tr>'
        . '<td width="52%" rowspan="2">Nature and location of work: ____________________</td></tr><tr>'
        . '<td width="23%">No. of working days in the year</td><td width="25%">' . $e($totalDays) . '</td></tr></table>';
    $html .= '<table class="register" border="1" cellpadding="3"><thead><tr>';
    $headers = annualComplianceHeaders();
    $widths = array(3, 10, 9, 6, 6, 4, 7, 7, 6, 6, 6, 5, 6, 6, 5, 5, 3);
    foreach ($headers as $i => $header) {
        if ($i === 8) {
            $html .= '<th width="18%" colspan="3">Deduction</th>';
        }
        if ($i < 8 || $i > 10) {
            $html .= '<th width="' . $widths[$i] . '%" rowspan="2">' . $e($header) . '</th>';
        }
    }
    $html .= '</tr><tr>';
    foreach (array_slice($headers, 8, 3) as $header) {
        $html .= '<th width="6%">' . $e($header) . '</th>';
    }
    $html .= '</tr><tr>';
    foreach (range(1, 17) as $n) {
        $html .= '<th width="' . $widths[$n - 1] . '%">' . $n . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    $totals = array_fill(0, 17, '');
    $totals[1] = 'TOTAL';
    foreach (array(5, 6, 7, 11, 12, 13) as $i) {
        $totals[$i] = 0;
    }
    foreach ($data['rows'] as $index => $row) {
        $html .= '<tr nobr="true">';
        foreach (annualComplianceValues($row, $index) as $i => $value) {
            if (in_array($i, array(5, 6, 7, 11, 12, 13), true)) {
                $totals[$i] += $value;
                $value = $i === 5 ? bonusFormCDaysNumber($value) : number_format($value, 2, '.', '');
            }
            $html .= '<td width="' . $widths[$i] . '%">' . $e($value) . '</td>';
        }
        $html .= '</tr>';
    }
    if (!$data['rows']) {
        $html .= '<tr><td colspan="17">No salary records found for this company and financial year.</td></tr>';
    }
    $html .= '<tr>';
    foreach ($totals as $i => $value) {
        $html .= '<td width="' . $widths[$i] . '%"><strong>' . $e(is_numeric($value) && $i !== 5 ? number_format($value, 2, '.', '') : $value) . '</strong></td>';
    }
    return $html . '</tr></tbody></table><p>Payable bonus = annual basic salary × 8.33%. Amount actually paid = bonus recorded in salary. Deduction details are not available in salary records; individual deduction fields are blank.</p></body></html>';
}
