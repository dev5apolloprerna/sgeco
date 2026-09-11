<?php
require_once __DIR__ . '/NewPFChallanReportData.php';

function pfEsicReportPeriod($month, $year)
{
    $month = str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT);
    $year = (string) (int) $year;
    return preg_match('/^(0[1-9]|1[0-2])$/', $month) && preg_match('/^\d{4}$/', $year)
        ? $month . '/' . $year : null;
}

function pfEsicReportData($dbconn, $month, $year)
{
    $period = pfEsicReportPeriod($month, $year);
    if ($period === null) {
        return array('period' => '', 'companies' => array(), 'employees' => array(), 'pfEmployees' => array(), 'aadharEmployees' => array());
    }
    // Use the PF Challan data source as the canonical employee order, employee
    // set, company set, present days and wage rate. This keeps both screens in
    // lockstep while this query adds the deduction-only PF and ESIC amounts.
    $challan = getNewPFChallanReportData($dbconn, $month, $year);
    $periodSql = mysqli_real_escape_string($dbconn, $period);

     $sql = "SELECT sd.emp_id AS employeeId, sm.companymasterId AS companyId,
                   COALESCE(SUM(sd.pf), 0) AS pfAmount,
                   COALESCE(SUM(sd.esi), 0) AS esicAmount
            FROM salarydetails sd
            INNER JOIN salarymaster sm ON sm.salarymasterId=sd.salaryId
            INNER JOIN employee e ON e.employeeId=sd.emp_id
            WHERE sm.month='" . $periodSql . "' AND sm.isDelete=0 AND sm.istatus=1
                AND sd.isDelete=0 AND sd.istatus=1 AND sd.workingdays > 0
                AND (e.isPermanent=0 OR (e.isPermanent=1 AND e.isDelete=0 AND e.istatus=1))
            GROUP BY sd.emp_id, sm.companymasterId";
    $result = mysqli_query($dbconn, $sql);
    if (!$result) {
        throw new RuntimeException('Unable to load PF & ESIC deduction report: ' . mysqli_error($dbconn));
    }

    $companies = $challan['companies'];
    $employees = array();
    foreach (array_merge($challan['pfEmployees'], $challan['aadharEmployees']) as $challanEmployee) {
        $employeeId = $challanEmployee['employeeId'];
        $employees[$employeeId] = pfEsicReportEmployee($challanEmployee);
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $companyId = (int) $row['companyId'];
        $employeeId = (int) $row['employeeId'];
        if (!isset($employees[$employeeId])) {
            continue;
        }
        $employees[$employeeId]['companies'][$companyId]['pfAmount'] = (float) $row['pfAmount'];
        $employees[$employeeId]['companies'][$companyId]['esicAmount'] = (float) $row['esicAmount'];
        $employees[$employeeId]['totalPf'] += (float) $row['pfAmount'];
        $employees[$employeeId]['totalEsic'] += (float) $row['esicAmount'];
    }

    $pfEmployees = array();
    $aadharEmployees = array();
    foreach ($employees as $employeeId => $employee) {
        if ($employee['listType'] === 'aadhar') {
            $aadharEmployees[$employeeId] = $employee;
        } else {
            $pfEmployees[$employeeId] = $employee;
        }
    }
    return array(
        'period' => $period,
        'companies' => $companies,
        'employees' => $employees,
        // Keep the same two lists and insertion order as New PF Challan.
        'pfEmployees' => $pfEmployees,
        'aadharEmployees' => $aadharEmployees
    );
}

function pfEsicReportEmployee($employee)
{
    $companies = array();
    $totalDays = 0;
    foreach ($employee['companies'] as $companyId => $values) {
        $companies[$companyId] = array(
            'presentDays' => $values['presentDays'],
            'wagesRate' => $values['wages'],
            'pfAmount' => 0,
            'esicAmount' => 0
        );
        $totalDays += (float) $values['presentDays'];
    }
    return array(
        'employeeId' => (int) $employee['employeeId'],
        'name' => $employee['name'],
        'employeeCode' => $employee['pfNo'],
        'listType' => $employee['listType'],
        'isPermanent' => (int) $employee['isPermanent'],
        'pfAccount' => $employee['pfNo'],
        'uan' => $employee['uan'],
        'esicNo' => $employee['esicNo'],
        'dob' => $employee['dob'],
        'fatherName' => $employee['fatherName'],
        'aadharNo' => $employee['aadharNo'],
        'companies' => $companies,
        'totalDays' => $totalDays,
        'totalPf' => 0,
        'totalEsic' => 0
    );
}

function pfEsicReportEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function pfEsicReportNumber($value, $money = false)
{
    return $money ? number_format((float) $value, 2, '.', '') : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
}

function pfEsicReportHtml($report)
{
    if (!$report['companies'] || !$report['employees']) {
        return '<div class="alert alert-info">No paid salary data found for the selected month and year.</div>';
    }
    $e = 'pfEsicReportEscape';
    $date = DateTime::createFromFormat('!m/Y', $report['period']);
    $html = '';
    $html .= pfEsicReportSectionHtml($report, $report['pfEmployees'], 'PF Employees', $date, false);
    $html .= pfEsicReportSectionHtml($report, $report['aadharEmployees'], 'Aadhar Employees', $date, true);
    return $html;
}

function pfEsicReportSectionHtml($report, $employees, $sectionTitle, $date, $isAadhar)
{
    if (!$employees) {
        return '';
    }
    $e = 'pfEsicReportEscape';
    $html = '<div class="table-responsive pf-esic-section"><table class="table table-bordered pf-esic-report"><thead>';
    $columnCount = 6 + count($report['companies']) * 4 + 3;
    $html .= '<tr class="report-title"><th colspan="' . $columnCount . '">PF &amp; ESIC Deduction Report </th></tr>';
    $html .= '<tr><th colspan="' . $columnCount . '">Month : ' . $e($date ? $date->format('M-Y') : $report['period']) . '</th></tr>';
    $html .= '<tr><th rowspan="2">Sr.<br>No.</th><th rowspan="2">' . ($isAadhar ? 'Name as per Aadhar' : 'Name') . '</th>';
    $html .= $isAadhar
        ? '<th rowspan="2">Father Name</th><th rowspan="2">Aadhar No.</th>'
        : '<th rowspan="2">PF A/C<br>No.</th><th rowspan="2">UAN No.</th>';
    $html .= '<th rowspan="2">ESIC No.</th><th rowspan="2">D.O.B</th>';
    foreach ($report['companies'] as $name) $html .= '<th colspan="4" class="company-heading">' . $e($name) . '</th>';
    $html .= '<th colspan="3" class="total-heading">Total Deduction</th></tr><tr>';
    foreach ($report['companies'] as $unused) $html .= '<th>Present<br>Days</th><th>Wages<br>Rate</th><th>PF Amt.</th><th>ESIC Amt.</th>';
    $html .= '<th class="total-heading">Present<br>Days</th><th class="total-heading">PF Amt.</th><th class="total-heading">ESIC Amt.</th></tr></thead><tbody>';
    $index = 1;
    foreach ($employees as $employee) {
        $html .= '<tr><td>' . $index++ . '</td><td class="employee-name">' . $e($employee['name']) . '</td>';
        $html .= $isAadhar
            ? '<td>' . $e($employee['fatherName']) . '</td><td>' . $e($employee['aadharNo']) . '</td>'
            : '<td>' . $e($employee['pfAccount']) . '</td><td>' . $e($employee['uan']) . '</td>';
        $html .= '<td>' . $e($employee['esicNo']) . '</td><td>' . $e($employee['dob']) . '</td>';
        foreach ($report['companies'] as $companyId => $unused) {
            $v = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'wagesRate' => 0, 'pfAmount' => 0, 'esicAmount' => 0);
            $html .= '<td>' . pfEsicReportNumber($v['presentDays']) . '</td><td>' . pfEsicReportNumber($v['wagesRate'], true) . '</td><td>' . pfEsicReportNumber($v['pfAmount'], true) . '</td><td>' . pfEsicReportNumber($v['esicAmount'], true) . '</td>';
        }
        $html .= '<td class="report-total">' . pfEsicReportNumber($employee['totalDays']) . '</td><td class="report-total">' . pfEsicReportNumber($employee['totalPf'], true) . '</td><td class="report-total">' . pfEsicReportNumber($employee['totalEsic'], true) . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}
