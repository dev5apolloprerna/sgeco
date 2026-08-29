<?php

function employeePaymentHistoryEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function employeePaymentHistoryFind($dbconn, $employeeId)
{
    $employeeId = (int) $employeeId;
    if ($employeeId <= 0) {
        return null;
    }

    $sql = "SELECT employeeId, emp_name, uan, pfcode, employeecode, strFatherName, dateofjoining
            FROM employee WHERE employeeId=" . $employeeId . " AND isDelete=0 LIMIT 1";
    $result = mysqli_query($dbconn, $sql);
    $employee = $result ? mysqli_fetch_assoc($result) : null;
    if (!$employee) {
        return null;
    }

    // salarymaster.month is stored as MM/YYYY. STR_TO_DATE makes the result
    // independent of insertion order when an older salary is entered later.
    $lastPfSql = "SELECT sm.month FROM salarydetails sd
                  INNER JOIN salarymaster sm ON sm.salarymasterId=sd.salaryId
                  WHERE sd.emp_id=" . $employeeId . " AND sd.pf>0
                    AND sd.isDelete=0 AND sd.istatus=1
                    AND sm.isDelete=0 AND sm.istatus=1
                  ORDER BY STR_TO_DATE(sm.month, '%m/%Y') DESC LIMIT 1";
    $lastPfResult = mysqli_query($dbconn, $lastPfSql);
    $lastPf = $lastPfResult ? mysqli_fetch_assoc($lastPfResult) : null;
    $employee['dateOfExit'] = $lastPf ? employeePaymentHistoryMonth($lastPf['month']) : '';
    $employee['dateofjoining'] = employeePaymentHistoryDate($employee['dateofjoining']);

    return $employee;
}

function employeePaymentHistoryDate($value)
{
    if (!$value || $value === '0000-00-00') {
        return '';
    }
    $time = strtotime($value);
    return $time ? date('d-m-Y', $time) : $value;
}

function employeePaymentHistoryMonth($value)
{
    $date = DateTime::createFromFormat('!m/Y', trim((string) $value));
    return $date ? $date->format('F Y') : (string) $value;
}

function employeePaymentHistoryHtml($employee)
{
    if (!$employee) {
        return '<div class="alert alert-warning">Please select a valid employee.</div>';
    }
    $e = 'employeePaymentHistoryEscape';
    $fields = array(
        'Employee Name' => $employee['emp_name'],
        'UAN No.' => $employee['uan'],
        'PF No.' => $employee['pfcode'],
        'Employee Code' => $employee['employeecode'],
        'Date of Joining' => $employee['dateofjoining'],
        'Date of Exit (Last Month)' => $employee['dateOfExit']
    );
    $html = '<table class="table table-bordered employee-payment-history" style="max-width:700px;font-family:serif;font-size:18px">';
    $html .= '<tr style="background:#d9e2f3"><th colspan="2" style="text-align:center;font-size:24px">Employee Payment History</th></tr>';
    foreach ($fields as $label => $value) {
        $html .= '<tr><th style="width:42%">' . $e($label) . ' :</th><td>' . $e($value) . '</td></tr>';
    }
    return $html . '</table>';
}
