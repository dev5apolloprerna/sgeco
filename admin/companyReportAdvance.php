<?php

/**
 * Return advance payments keyed by employee for a company and wage month.
 *
 * The wage month is supplied by newReport.php in MM/YYYY format.  Advances are
 * grouped here once so report rows do not issue one query per employee.
 */
function getCompanyReportAdvances($dbconn, $companyId, $wageMonth)
{
    $advances = array();
    $companyId = (int) $companyId;

    if ($companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/(\d{4})$/', $wageMonth, $matches)) {
        return $advances;
    }

    $month = (int) $matches[1];
    $year = (int) $matches[2];
    $periodStart = sprintf('%04d-%02d-01', $year, $month);
    $periodEnd = date('Y-m-d', strtotime($periodStart . ' +1 month'));
    $periodStart = mysqli_real_escape_string($dbconn, $periodStart);
    $periodEnd = mysqli_real_escape_string($dbconn, $periodEnd);
    $sql = "SELECT iEmployeeId, COALESCE(SUM(iAmount), 0) AS advanceAmount
            FROM advanced_details
            WHERE iCompanyId=" . $companyId . "
              AND strDate >= '" . $periodStart . "'
              AND strDate < '" . $periodEnd . "'
            GROUP BY iEmployeeId";
    $result = mysqli_query($dbconn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $advances[(int) $row['iEmployeeId']] = (float) $row['advanceAmount'];
        }
    }

    return $advances;
}

/**
 * Return advance payments for every company attached to a multicompany salary.
 */
function getMultiCompanyReportAdvances($dbconn, $companySalaryMasterId, $wageMonth)
{
    $advances = array();
    $companySalaryMasterId = (int) $companySalaryMasterId;

    if ($companySalaryMasterId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/(\d{4})$/', $wageMonth, $matches)) {
        return $advances;
    }

    $month = (int) $matches[1];
    $year = (int) $matches[2];
    $periodStart = sprintf('%04d-%02d-01', $year, $month);
    $periodEnd = date('Y-m-d', strtotime($periodStart . ' +1 month'));
    $periodStart = mysqli_real_escape_string($dbconn, $periodStart);
    $periodEnd = mysqli_real_escape_string($dbconn, $periodEnd);
    $sql = "SELECT advanced_details.iEmployeeId, COALESCE(SUM(advanced_details.iAmount), 0) AS advanceAmount
            FROM advanced_details
            INNER JOIN (
                SELECT DISTINCT companymasterId
                FROM multiycompanysalarymaster
                WHERE companysalarymasterId=" . $companySalaryMasterId . "
            ) selected_companies ON selected_companies.companymasterId = advanced_details.iCompanyId
            WHERE advanced_details.strDate >= '" . $periodStart . "'
              AND advanced_details.strDate < '" . $periodEnd . "'
            GROUP BY advanced_details.iEmployeeId";
    $result = mysqli_query($dbconn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $advances[(int) $row['iEmployeeId']] = (float) $row['advanceAmount'];
        }
    }

    return $advances;
}

/**
 * Return the PF and ESIC already calculated in each selected company's salary.
 *
 * A multi-company report is a roll-up of the selected companies.  Therefore its
 * statutory deductions must be summed from those companies' salary rows rather
 * than recalculated from the multi-company rate (or read from a stale saved
 * report row).
 */
function getMultiCompanyReportDeductions($dbconn, $companySalaryMasterId)
{
    $deductions = array();
    $companySalaryMasterId = (int) $companySalaryMasterId;

    if ($companySalaryMasterId <= 0) {
        return $deductions;
    }

    $sql = "SELECT salarydetails.emp_id,
                   COALESCE(SUM(salarydetails.pf), 0) AS pfAmount,
                   COALESCE(SUM(salarydetails.esi), 0) AS esicAmount
            FROM salarydetails
            INNER JOIN salarymaster
                ON salarymaster.salarymasterId = salarydetails.salaryId
               AND salarymaster.companymasterId = salarydetails.companyId
               AND salarymaster.isDelete = 0
               AND salarymaster.istatus = 1
            INNER JOIN multiycompanysalarymaster
                ON multiycompanysalarymaster.companymasterId = salarydetails.companyId
               AND multiycompanysalarymaster.companysalarymasterId = " . $companySalaryMasterId . "
               AND multiycompanysalarymaster.isDelete = 0
            INNER JOIN companysalarymaster
                ON companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId
               AND companysalarymaster.month = salarymaster.month
            WHERE salarydetails.isDelete = 0
            GROUP BY salarydetails.emp_id";
    $result = mysqli_query($dbconn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $deductions[(int) $row['emp_id']] = array(
                'pf' => (float) $row['pfAmount'],
                'esic' => (float) $row['esicAmount']
            );
        }
    }

    return $deductions;
}

function getEmployeeMultiCompanyReportDeductions($deductions, $employeeId)
{
    $employeeId = (int) $employeeId;
    return isset($deductions[$employeeId])
        ? $deductions[$employeeId]
        : array('pf' => 0, 'esic' => 0);
}

/**
 * Calculate the monetary columns shared by multi-company add, edit and exports.
 *
 * Keeping this in one place prevents a saved row (or an older row saved with a
 * previous formula) from showing a Total that does not match its deductions.
 */
function calculateMultiCompanySalary($presentAmount, $otAmount, $advanceOne, $advanceTwo, $advancePaidByBank, $pfAmount, $esicAmount, $fa, $ta)
{
    $totalAmount = (float) $presentAmount + (float) $otAmount;
    $total = $totalAmount
        - (float) $advanceOne
        - (float) $advanceTwo
        - (float) $advancePaidByBank
        - (float) $pfAmount
        - (float) $esicAmount;

    return array(
        'totalamt' => $totalAmount,
        'total' => $total,
        'balance1' => $total + (float) $fa + (float) $ta
    );
}

function getEmployeeCompanyReportAdvance($advances, $employeeId)
{
    $employeeId = (int) $employeeId;
    return isset($advances[$employeeId]) ? (float) $advances[$employeeId] : 0;
}

/** Prefer the advance saved with a salary, including an explicitly saved zero. */
function getSalaryReportAdvance($salary, $advances)
{
    if (array_key_exists('advance', $salary) && $salary['advance'] !== null) {
        return (float) $salary['advance'];
    }

    return getEmployeeCompanyReportAdvance($advances, $salary['emp_id']);
}

/** Avoid deducting an advance twice when it is already included in net pay. */
function getSalaryReportNetAmount($salary, $advance)
{
    $hasSavedAdvance = array_key_exists('advance', $salary) && $salary['advance'] !== null;
    return (float) $salary['netamountpaid'] - ($hasSavedAdvance ? 0 : (float) $advance);
}