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
    
    // The original PF Challan is made from two AJAX responses.  Its first
    // response always lists every active permanent employee, even when that
    // employee has no salary row for the selected month.  Start with the same
    // employee set here; the monthly queries below add the company-wise values.
    $permanentEmployeeResult = mysqli_query($dbconn, "SELECT employeeId, emp_name, employeecode,
        pfcode, uan, ecsno, dateofbirth, dateofjoining
        FROM employee
        WHERE isPermanent=1 AND isDelete=0 AND istatus=1
        ORDER BY emp_name ASC, employeeId ASC");
    if ($permanentEmployeeResult === false) {
        throw new RuntimeException('Unable to load permanent employees: ' . mysqli_error($dbconn));
    }
    while ($employee = mysqli_fetch_assoc($permanentEmployeeResult)) {
        $employeeId = (int) $employee['employeeId'];
        $employees[$employeeId] = newPFChallanEmployee($employee, 'pf');
    }

    $detailResult = mysqli_query($dbconn, "SELECT employee.employeeId, employee.emp_name, employee.employeecode,
        employee.pfcode, employee.uan, employee.ecsno, employee.dateofbirth, employee.dateofjoining,
        employee.strFatherName, employee.adharcard, employee.isPermanent,
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
        AND (employee.isPermanent=0 OR (employee.isPermanent=1 AND employee.isDelete=0 AND employee.istatus=1))
        AND salarydetails.isDelete='0' AND salarydetails.istatus='1'
        AND salarydetails.workingdays > 0
        GROUP BY employee.employeeId, salarymaster.companymasterId
        ORDER BY employee.emp_name ASC, employee.employeeId ASC");
    if ($detailResult === false) {
        throw new RuntimeException('Unable to load PF challan details: ' . mysqli_error($dbconn));
    }

    while ($detail = mysqli_fetch_assoc($detailResult)) {
        $employeeId = (int) $detail['employeeId'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = newPFChallanEmployee($detail, (int) $detail['employeecode'] === 0 ? 'aadhar' : 'pf');
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
        AND employee.isPermanent=1 AND employee.isDelete='0' AND employee.istatus='1'
        GROUP BY employee.employeeId, salarymaster.companymasterId
        ORDER BY employee.emp_name ASC, employee.employeeId ASC");
    if ($permanentResult === false) {
        throw new RuntimeException('Unable to load permanent PF challan details: ' . mysqli_error($dbconn));
    }
    while ($detail = mysqli_fetch_assoc($permanentResult)) {
        $employeeId = (int) $detail['employeeId'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = newPFChallanEmployee($detail, 'pf');
        }
        $companyId = (int) $detail['companymasterId'];
        $existing = isset($employees[$employeeId]['companies'][$companyId])
            ? $employees[$employeeId]['companies'][$companyId]
            : array('presentDays' => 0, 'nationalHoliday' => 0, 'wages' => 0, 'differenceInESIC' => 0);
        $existing['presentDays'] = (float) $detail['workingdays'];
        $existing['wages'] = (float) $detail['wages'];
        $employees[$employeeId]['companies'][$companyId] = $existing;
    }

    // uasort($employees, function ($first, $second) {
    //     $nameComparison = strcasecmp($first['name'], $second['name']);
    //     return $nameComparison !== 0 ? $nameComparison : $first['employeeId'] - $second['employeeId'];
    // });

    $pfEmployees = array();
    $aadharEmployees = array();
    foreach ($employees as $employee) {
        if ($employee['listType'] === 'aadhar') {
            $aadharEmployees[] = $employee;
        } else {
            $pfEmployees[] = $employee;
        }
    }
    // P.F. A/c No. is the employee code in both challan reports. Keep the New
    // PF Challan rows in the same ascending PF-code order as the existing one.
    usort($pfEmployees, function ($first, $second) {
        $pfCodeComparison = (int) $first['pfNo'] - (int) $second['pfNo'];
        return $pfCodeComparison !== 0
            ? $pfCodeComparison
            : $first['employeeId'] - $second['employeeId'];
    });
    
    return array(
        'salaryMonth' => $salaryMonth,
        'companies' => $companies,
        'pfEmployees' => $pfEmployees,
        'aadharEmployees' => $aadharEmployees
    );
}

function newPFChallanEmployee($employee, $listType)
{
    return array(
        'employeeId' => (int) $employee['employeeId'],
        'name' => ucwords(strtolower($employee['emp_name'])),
        'listType' => $listType,
        // The existing PF Challan labels the employee code as PF No.
        'pfNo' => $employee['employeecode'],
        'uan' => $employee['uan'],
        'esicNo' => $employee['ecsno'],
        'dob' => $employee['dateofbirth'] === '01/01/1970' ? '' : $employee['dateofbirth'],
        'joiningDate' => newPFChallanJoiningDate($employee['dateofjoining']),
        'fatherName' => isset($employee['strFatherName']) ? ucwords(strtolower($employee['strFatherName'])) : '',
        'aadharNo' => isset($employee['adharcard']) ? $employee['adharcard'] : '',
        'companies' => array(),
        'overtime' => 0,
        'professionalTax' => 0
    );
}

/**
 * Normalise the historical joining-date values to a single Mon-YYYY format.
 * The employee table contains both complete dates and month/year values, with
 * either numeric or abbreviated month names.
 */
function newPFChallanJoiningDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '01/70') {
        return '';
    }

    $formats = array(
        '!d-m-Y', '!j-n-Y', '!d/m/Y', '!j/n/Y', '!Y-m-d', '!Y/m/d',
        '!m/y', '!n/y', '!m/Y', '!n/Y',
        '!M-y', '!M-Y', '!F-y', '!F-Y'
    );
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('M-Y');
        }
    }

    // Do not hide an unexpected legacy value; leave it visible for correction.
    return $value;
}

function newPFChallanValue($value)
{
    if ((float) $value == 0) {
        return '';
    }
    return ((float) $value == (int) $value) ? (string) (int) $value : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
}

function newPFChallanPresentDays($value)
{
    return (float) $value == 0 ? '0' : number_format((float) $value, 2, '.', '');
}

function newPFChallanNationalHoliday($value)
{
    return (string) (int) round((float) $value);
}

function newPFChallanWages($value)
{
    return number_format((float) $value, 2, '.', '');
}
