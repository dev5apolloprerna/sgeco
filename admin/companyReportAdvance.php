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

function getEmployeeCompanyReportAdvance($advances, $employeeId)
{
    $employeeId = (int) $employeeId;
    return isset($advances[$employeeId]) ? (float) $advances[$employeeId] : 0;
}
