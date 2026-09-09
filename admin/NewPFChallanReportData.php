<?php

/**
 * Shared data source for the New PF Challan Generate list and Excel export.
 * The selected month's salary masters determine the dynamic company columns.
 */
function getNewPFChallanReportData($dbconn, $month, $year)
{
    $month = (int) $month;
    $year = (int) $year;
    if ($month < 1 || $month > 12 || $year < 2000 || $year > 9999) {
        throw new InvalidArgumentException('Please select a valid month and year.');
    }

    $salaryMonth = sprintf('%02d/%04d', $month, $year);
    $escapedMonth = mysqli_real_escape_string($dbconn, $salaryMonth);
    $companies = array();
    $companyResult = mysqli_query($dbconn, "SELECT DISTINCT companymaster.companymasterId, companymaster.companyname
        FROM salarymaster
        INNER JOIN companymaster ON salarymaster.companymasterId=companymaster.companymasterId
        WHERE salarymaster.month='" . $escapedMonth . "' AND salarymaster.isDelete='0' AND salarymaster.istatus='1'
        ORDER BY companymaster.companyname ASC, companymaster.companymasterId ASC");
    if ($companyResult === false) {
        throw new RuntimeException('Unable to load companies: ' . mysqli_error($dbconn));
    }
    while ($company = mysqli_fetch_assoc($companyResult)) {
        $companies[(int) $company['companymasterId']] = $company['companyname'];
    }

    $employees = array();
    $detailResult = mysqli_query($dbconn, "SELECT employee.employeeId, employee.emp_name, employee.employeecode,
        employee.pfcode, employee.uan, employee.ecsno, employee.dateofbirth, employee.dateofjoining,
        salarymaster.companymasterId,
        SUM(CONVERT(salarydetails.workingdays,DECIMAL(12,2))) AS workingdays,
        SUM(COALESCE(salarydetails.iNoOfNatioanHoliday,0)) AS nationalHolidays,
        MAX(CONVERT(salarydetails.skillrate,DECIMAL(12,2))) AS wages,
        SUM(CASE WHEN UPPER(companymaster.ESI)='YES' THEN COALESCE(salarydetails.iBonusAmt,0) + COALESCE(salarydetails.iLeaveAmt,0) ELSE 0 END) AS differenceInESIC,
        SUM(COALESCE(salarydetails.totalovertime,0)) AS overtime,
        SUM(COALESCE(salarydetails.pt,0)) AS professionalTax
        FROM salarydetails
        INNER JOIN employee ON salarydetails.emp_id=employee.employeeId
        INNER JOIN salarymaster ON salarydetails.salaryId=salarymaster.salarymasterId
        INNER JOIN companymaster ON salarymaster.companymasterId=companymaster.companymasterId
        WHERE salarymaster.month='" . $escapedMonth . "' AND salarymaster.isDelete='0' AND salarymaster.istatus='1'
        AND salarydetails.isDelete='0' AND salarydetails.istatus='1' AND salarydetails.workingdays > 0
        GROUP BY employee.employeeId, salarymaster.companymasterId
        ORDER BY employee.emp_name ASC, employee.employeeId ASC");
    if ($detailResult === false) {
        throw new RuntimeException('Unable to load PF challan details: ' . mysqli_error($dbconn));
    }

    while ($detail = mysqli_fetch_assoc($detailResult)) {
        $employeeId = (int) $detail['employeeId'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = array(
                'employeeId' => $employeeId,
                'name' => ucwords(strtolower($detail['emp_name'])),
                // The existing PF Challan labels the employee code as PF No.
                'pfNo' => $detail['employeecode'],
                'uan' => $detail['uan'],
                'esicNo' => $detail['ecsno'],
                'dob' => $detail['dateofbirth'] === '01/01/1970' ? '' : $detail['dateofbirth'],
                'joiningDate' => $detail['dateofjoining'] === '01/70' ? '' : $detail['dateofjoining'],
                'companies' => array(),
                'overtime' => 0,
                'professionalTax' => 0
            );
        }
        $companyId = (int) $detail['companymasterId'];
        $employees[$employeeId]['companies'][$companyId] = array(
            'presentDays' => (float) $detail['workingdays'],
            'nationalHoliday' => (float) $detail['nationalHolidays'],
            'wages' => (float) $detail['wages'],
            'differenceInESIC' => round((float) $detail['differenceInESIC'])
        );
        $employees[$employeeId]['overtime'] += ceil((float) $detail['overtime']);
        $employees[$employeeId]['professionalTax'] += (float) $detail['professionalTax'];
    }

    // Keep the permanent-employee source used by the existing PF Challan report.
    // Its monthly entry replaces regular days/wages for the same company.
    $permanentResult = mysqli_query($dbconn, "SELECT employee.employeeId, employee.emp_name, employee.employeecode,
        employee.pfcode, employee.uan, employee.ecsno, employee.dateofbirth, employee.dateofjoining,
        salarymaster.companymasterId, MAX(permanentemployeesalarydetails.workingdays) AS workingdays,
        MAX(permanentemployeesalarydetails.netamountpaid) AS wages
        FROM permanentemployeesalarydetails
        INNER JOIN employee ON permanentemployeesalarydetails.emp_id=employee.employeeId
        INNER JOIN salarymaster ON permanentemployeesalarydetails.salaryId=salarymaster.salarymasterId
        WHERE salarymaster.month='" . $escapedMonth . "' AND salarymaster.isDelete='0' AND salarymaster.istatus='1'
        AND employee.isDelete='0' AND employee.istatus='1'
        GROUP BY employee.employeeId, salarymaster.companymasterId
        ORDER BY employee.emp_name ASC, employee.employeeId ASC");
    if ($permanentResult === false) {
        throw new RuntimeException('Unable to load permanent PF challan details: ' . mysqli_error($dbconn));
    }
    while ($detail = mysqli_fetch_assoc($permanentResult)) {
        $employeeId = (int) $detail['employeeId'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = array(
                'employeeId' => $employeeId,
                'name' => ucwords(strtolower($detail['emp_name'])),
                'pfNo' => $detail['employeecode'],
                'uan' => $detail['uan'],
                'esicNo' => $detail['ecsno'],
                'dob' => $detail['dateofbirth'] === '01/01/1970' ? '' : $detail['dateofbirth'],
                'joiningDate' => $detail['dateofjoining'] === '01/70' ? '' : $detail['dateofjoining'],
                'companies' => array(),
                'overtime' => 0,
                'professionalTax' => 0
            );
        }
        $companyId = (int) $detail['companymasterId'];
        $existing = isset($employees[$employeeId]['companies'][$companyId])
            ? $employees[$employeeId]['companies'][$companyId]
            : array('presentDays' => 0, 'nationalHoliday' => 0, 'wages' => 0, 'differenceInESIC' => 0);
        $existing['presentDays'] = (float) $detail['workingdays'];
        $existing['wages'] = (float) $detail['wages'];
        $employees[$employeeId]['companies'][$companyId] = $existing;
    }

    uasort($employees, function ($first, $second) {
        $nameComparison = strcasecmp($first['name'], $second['name']);
        return $nameComparison !== 0 ? $nameComparison : $first['employeeId'] - $second['employeeId'];
    });

    return array('salaryMonth' => $salaryMonth, 'companies' => $companies, 'employees' => array_values($employees));
}

function newPFChallanValue($value)
{
    if ((float) $value == 0) {
        return '';
    }
    return ((float) $value == (int) $value) ? (string) (int) $value : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
}
