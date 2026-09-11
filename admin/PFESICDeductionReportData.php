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
        return array('period' => '', 'companies' => array(), 'employees' => array());
    }
    $periodSql = mysqli_real_escape_string($dbconn, $period);

    // This is a multi-company payment report, so paymentMaster/multicompany
    // decide which paid employees and company groups belong in the report.
    // The companies in each paid group are expanded through
    // multiycompanysalarymaster; their salarydetails rows provide the requested
    // company-wise days, rate, PF and ESIC breakdown.
    echo $sql = "SELECT cm.companymasterId AS companyId, cm.companyname,
                   e.employeeId, e.emp_name, e.pfcode, e.uan,
                   COALESCE(SUM(sd.workingdays), 0) AS presentDays,
                   COALESCE(MAX(CONVERT(sd.skillrate, DECIMAL(12,2))), 0) AS wagesRate,
                   COALESCE(SUM(sd.pf), 0) AS pfAmount,
                   COALESCE(SUM(sd.esi), 0) AS esicAmount
            FROM paymentMaster pm
            INNER JOIN multicompany mc ON mc.iPaymentId=pm.iPaymentId
                AND mc.iPaymentStatus=1 AND mc.isDelete=0 AND mc.istatus=1
            INNER JOIN employee e ON e.employeeId=mc.emp_id AND e.isDelete=0
            INNER JOIN multiycompanysalarymaster msm
                ON msm.companysalarymasterId=pm.iCompanySalaryMasterId AND msm.isDelete=0
            INNER JOIN companymaster cm ON cm.companymasterId=msm.companymasterId AND cm.isDelete=0
            INNER JOIN salarymaster sm ON sm.companymasterId=cm.companymasterId
                AND sm.month=pm.salarymonth AND sm.isDelete=0 AND sm.istatus=1
            INNER JOIN salarydetails sd ON sd.salaryId=sm.salarymasterId
                AND sd.companyId=cm.companymasterId AND sd.emp_id=mc.emp_id
                AND sd.isDelete=0 AND sd.istatus=1
            WHERE pm.salarymonth='" . $periodSql . "' AND pm.isDelete=0 AND pm.iStatus=1
            GROUP BY cm.companymasterId, cm.companyname, e.employeeId, e.emp_name, e.pfcode, e.uan
            ORDER BY cm.companyname, e.emp_name, e.employeeId";
    $result = mysqli_query($dbconn, $sql);
    if (!$result) {
        throw new RuntimeException('Unable to load PF & ESIC deduction report: ' . mysqli_error($dbconn));
    }

    $companies = array();
    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $companyId = (int) $row['companyId'];
        $employeeId = (int) $row['employeeId'];
        $companies[$companyId] = $row['companyname'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = array(
                'name' => $row['emp_name'],
                'pfAccount' => $row['pfcode'],
                'uan' => $row['uan'],
                'companies' => array(),
                'totalDays' => 0,
                'totalPf' => 0,
                'totalEsic' => 0
            );
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
        return strcasecmp($a['name'], $b['name']);
    });
    return array('period' => $period, 'companies' => $companies, 'employees' => $employees);
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
    $html = '<div class="table-responsive"><table class="table table-bordered pf-esic-report"><thead>';
    $html .= '<tr class="report-title"><th colspan="' . (4 + count($report['companies']) * 4 + 3) . '">PF &amp; ESIC Deduction Report</th></tr>';
    $html .= '<tr><th colspan="' . (4 + count($report['companies']) * 4 + 3) . '">Month : ' . $e($date ? $date->format('M-Y') : $report['period']) . '</th></tr>';
    $html .= '<tr><th rowspan="2">Sr.<br>No.</th><th rowspan="2">Name</th><th rowspan="2">PF A/C<br>No.</th><th rowspan="2">UAN No.</th>';
    foreach ($report['companies'] as $name) $html .= '<th colspan="4" class="company-heading">Company Name {' . $e($name) . '}</th>';
    $html .= '<th colspan="3" class="total-heading">Total Deduction</th></tr><tr>';
    foreach ($report['companies'] as $unused) $html .= '<th>Present<br>Days</th><th>Wages<br>Rate</th><th>PF Amt.</th><th>ESIC Amt.</th>';
    $html .= '<th class="total-heading">Present<br>Days</th><th class="total-heading">PF Amt.</th><th class="total-heading">ESIC Amt.</th></tr></thead><tbody>';
    $index = 1;
    foreach ($report['employees'] as $employee) {
        $html .= '<tr><td>' . $index++ . '</td><td class="employee-name">' . $e($employee['name']) . '</td><td>' . $e($employee['pfAccount']) . '</td><td>' . $e($employee['uan']) . '</td>';
        foreach ($report['companies'] as $companyId => $unused) {
            $v = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'wagesRate' => 0, 'pfAmount' => 0, 'esicAmount' => 0);
            $html .= '<td>' . pfEsicReportNumber($v['presentDays']) . '</td><td>' . pfEsicReportNumber($v['wagesRate'], true) . '</td><td>' . pfEsicReportNumber($v['pfAmount'], true) . '</td><td>' . pfEsicReportNumber($v['esicAmount'], true) . '</td>';
        }
        $html .= '<td class="report-total">' . pfEsicReportNumber($employee['totalDays']) . '</td><td class="report-total">' . pfEsicReportNumber($employee['totalPf'], true) . '</td><td class="report-total">' . pfEsicReportNumber($employee['totalEsic'], true) . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}
