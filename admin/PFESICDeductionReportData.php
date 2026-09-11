<?php

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
        return array('period' => '', 'companies' => array(), 'employees' => array(), 'permanentEmployees' => array(), 'otherEmployees' => array());
    }
    $periodSql = mysqli_real_escape_string($dbconn, $period);

    // A generated multi-company salary is sufficient for this report; it may
    // not have a bank/cash paymentMaster row yet. Start with
    // companysalarymaster/multicompany so newly generated salary months are
    // visible, then expand the group's companies for the company-wise values.
    $sql = "SELECT cm.companymasterId AS companyId, cm.companyname,
                   e.employeeId, e.emp_name, e.employeecode, e.isPermanent, e.pfcode, e.uan,
                   COALESCE(SUM(sd.workingdays), 0) AS presentDays,
                   COALESCE(MAX(CONVERT(sd.skillrate, DECIMAL(12,2))), 0) AS wagesRate,
                   COALESCE(SUM(sd.pf), 0) AS pfAmount,
                   COALESCE(SUM(sd.esi), 0) AS esicAmount
            FROM companysalarymaster csm
            INNER JOIN multicompany mc ON mc.companysalarymasterId=csm.companysalarymasterId
                AND mc.isDelete=0 AND mc.istatus=1
            INNER JOIN employee e ON e.employeeId=mc.emp_id AND e.isDelete=0
            INNER JOIN multiycompanysalarymaster msm
                ON msm.companysalarymasterId=csm.companysalarymasterId AND msm.isDelete=0
            INNER JOIN companymaster cm ON cm.companymasterId=msm.companymasterId AND cm.isDelete=0
            LEFT JOIN salarymaster sm ON sm.companymasterId=cm.companymasterId
                AND sm.month=csm.month AND sm.isDelete=0 AND sm.istatus=1
            LEFT JOIN salarydetails sd ON sd.salaryId=sm.salarymasterId
                AND sd.companyId=cm.companymasterId AND sd.emp_id=mc.emp_id
                AND sd.isDelete=0 AND sd.istatus=1
            WHERE csm.month='" . $periodSql . "' AND csm.isDelete=0 AND csm.istatus=1
            GROUP BY cm.companymasterId, cm.companyname, e.employeeId, e.emp_name,
                e.employeecode, e.isPermanent, e.pfcode, e.uan
            ORDER BY cm.companyname, e.isPermanent DESC, e.employeecode ASC, e.employeeId ASC";
    $result = mysqli_query($dbconn, $sql);
    if (!$result) {
        throw new RuntimeException('Unable to load PF & ESIC deduction report: ' . mysqli_error($dbconn));
    }

    $companies = array();
    $employees = array();
    // Match the New PF Challan report by keeping every active permanent
    // employee in the first list, even when the selected month has no detail
    // row for that employee.
    $permanentResult = mysqli_query($dbconn, "SELECT employeeId, emp_name, employeecode,
            isPermanent, pfcode, uan
        FROM employee
        WHERE isPermanent=1 AND isDelete=0 AND istatus=1
        ORDER BY employeecode ASC, employeeId ASC");
    if (!$permanentResult) {
        throw new RuntimeException('Unable to load permanent employees: ' . mysqli_error($dbconn));
    }
    while ($permanent = mysqli_fetch_assoc($permanentResult)) {
        $employeeId = (int) $permanent['employeeId'];
        $employees[$employeeId] = pfEsicReportEmployee($permanent);
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $companyId = (int) $row['companyId'];
        $employeeId = (int) $row['employeeId'];
        $companies[$companyId] = $row['companyname'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = pfEsicReportEmployee($row);
        }
        $values = array(
            'presentDays' => (float) $row['presentDays'],
            'wagesRate' => (float) $row['wagesRate'],
            'pfAmount' => (float) $row['pfAmount'],
            'esicAmount' => (float) $row['esicAmount']
        );
        $employees[$employeeId]['companies'][$companyId] = $values;
        $employees[$employeeId]['totalDays'] += $values['presentDays'];
        $employees[$employeeId]['totalPf'] += $values['pfAmount'];
        $employees[$employeeId]['totalEsic'] += $values['esicAmount'];
    }
    uasort($employees, function ($a, $b) {
        $codeComparison = strcmp((string) $a['employeeCode'], (string) $b['employeeCode']);
        return $codeComparison !== 0 ? $codeComparison : $a['employeeId'] - $b['employeeId'];
    });
    $permanentEmployees = array();
    $otherEmployees = array();
    foreach ($employees as $employeeId => $employee) {
        if ($employee['isPermanent'] === 1) {
            $permanentEmployees[$employeeId] = $employee;
        } else {
            $otherEmployees[$employeeId] = $employee;
        }
    }
    return array(
        'period' => $period,
        'companies' => $companies,
        'employees' => $employees,
        'permanentEmployees' => $permanentEmployees,
        'otherEmployees' => $otherEmployees
    );
}

function pfEsicReportEmployee($employee)
{
    return array(
        'employeeId' => (int) $employee['employeeId'],
        'name' => $employee['emp_name'],
        'employeeCode' => $employee['employeecode'],
        'isPermanent' => (int) $employee['isPermanent'],
        'pfAccount' => $employee['pfcode'],
        'uan' => $employee['uan'],
        'companies' => array(),
        'totalDays' => 0,
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
    $html .= pfEsicReportSectionHtml($report, $report['permanentEmployees'], 'Permanent Employees', $date);
    $html .= pfEsicReportSectionHtml($report, $report['otherEmployees'], 'Non-Permanent Employees', $date);
    return $html;
}

function pfEsicReportSectionHtml($report, $employees, $sectionTitle, $date)
{
    if (!$employees) {
        return '';
    }
    $e = 'pfEsicReportEscape';
    $html = '<div class="table-responsive pf-esic-section"><table class="table table-bordered pf-esic-report"><thead>';
    $html .= '<tr class="report-title"><th colspan="' . (4 + count($report['companies']) * 4 + 3) . '">PF &amp; ESIC Deduction Report - ' . $e($sectionTitle) . '</th></tr>';
    $html .= '<tr><th colspan="' . (4 + count($report['companies']) * 4 + 3) . '">Month : ' . $e($date ? $date->format('M-Y') : $report['period']) . '</th></tr>';
    $html .= '<tr><th rowspan="2">Sr.<br>No.</th><th rowspan="2">Name</th><th rowspan="2">PF A/C<br>No.</th><th rowspan="2">UAN No.</th>';
    foreach ($report['companies'] as $name) $html .= '<th colspan="4" class="company-heading">' . $e($name) . '</th>';
    $html .= '<th colspan="3" class="total-heading">Total Deduction</th></tr><tr>';
    foreach ($report['companies'] as $unused) $html .= '<th>Present<br>Days</th><th>Wages<br>Rate</th><th>PF Amt.</th><th>ESIC Amt.</th>';
    $html .= '<th class="total-heading">Present<br>Days</th><th class="total-heading">PF Amt.</th><th class="total-heading">ESIC Amt.</th></tr></thead><tbody>';
    $index = 1;
    foreach ($employees as $employee) {
        $html .= '<tr><td>' . $index++ . '</td><td class="employee-name">' . $e($employee['name']) . '</td><td>' . $e($employee['pfAccount']) . '</td><td>' . $e($employee['uan']) . '</td>';
        foreach ($report['companies'] as $companyId => $unused) {
            $v = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'wagesRate' => 0, 'pfAmount' => 0, 'esicAmount' => 0);
            $html .= '<td>' . pfEsicReportNumber($v['presentDays']) . '</td><td>' . pfEsicReportNumber($v['wagesRate'], true) . '</td><td>' . pfEsicReportNumber($v['pfAmount'], true) . '</td><td>' . pfEsicReportNumber($v['esicAmount'], true) . '</td>';
        }
        $html .= '<td class="report-total">' . pfEsicReportNumber($employee['totalDays']) . '</td><td class="report-total">' . pfEsicReportNumber($employee['totalPf'], true) . '</td><td class="report-total">' . pfEsicReportNumber($employee['totalEsic'], true) . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}
