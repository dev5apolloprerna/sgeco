<?php

/** Register of Bonus (Form C) data and template renderer. */
function getBonusFormCEmployees($dbconn, $companyId, $salaryMonth, $employeeId = 0)
{
    $companyId = (int) $companyId;
    $employeeId = (int) $employeeId;
    if ($companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    $employeeFilter = $employeeId > 0 ? ' AND e.employeeId=' . $employeeId : '';
    $sql = "SELECT sd.salarydetailsId, sd.workingdays, sd.skillrate, sd.basicwages,
                   sd.iBonusAmt, sd.salarypaiddate, e.employeeId, e.emp_name,
                   e.strFatherName, e.dateofbirth, e.designation
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId=sd.emp_id
                AND e.isDelete='0' AND e.istatus='1'
            WHERE sd.companyId=" . $companyId . "
              AND sd.salaryId IN (SELECT salarymasterId FROM salarymaster
                  WHERE month='" . $month . "' AND isDelete='0' AND istatus='1')
              AND sd.isDelete='0' AND sd.istatus='1' AND sd.workingdays > 0" .
        $employeeFilter . " ORDER BY e.emp_name ASC, sd.salarydetailsId ASC";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Register of Bonus employees.');
    }
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getBonusFormCCompanyName($dbconn, $companyId)
{
    $result = mysqli_query($dbconn, 'SELECT companyname FROM companymaster WHERE companymasterId=' .
        (int) $companyId . " AND isDelete='0' AND istatus='1' LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if (!$row) {
        throw new RuntimeException('The selected company could not be found.');
    }
    return trim((string) $row['companyname']) === '' ? '0' : $row['companyname'];
}

function bonusFormCNumber($value)
{
    return number_format((float) ($value === null || $value === '' ? 0 : $value), 2, '.', '');
}

function bonusFormCText($value)
{
    return trim((string) $value) === '' ? '0' : (string) $value;
}

function bonusFormCDate($value)
{
    $value = trim((string) $value);
    foreach (array('d-m-Y H:i:s', 'Y-m-d H:i:s', 'd/m/Y', 'd-m-Y', 'Y-m-d') as $format) {
        $date = DateTime::createFromFormat('!' . $format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->format('d-m-Y');
        }
    }
    return '0';
}

function bonusFormCAdult($dateOfBirth, DateTime $accountingYearStart)
{
    $value = bonusFormCDate($dateOfBirth);
    if ($value === '0') {
        return '0';
    }
    $birth = DateTime::createFromFormat('!d-m-Y', $value);
    return $birth && $birth->diff($accountingYearStart)->y >= 15 ? 'YES' : 'NO';
}

function getBonusFormCRows(array $employees, $salaryMonth)
{
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    $startYear = (int) $period->format('Y') - ((int) $period->format('n') < 4 ? 1 : 0);
    $yearStart = new DateTime($startYear . '-04-01');
    $rows = array();
    foreach ($employees as $employee) {
        $bonus = $employee['iBonusAmt'];
        $rows[] = array(
            'name' => bonusFormCText($employee['emp_name']),
            'father' => bonusFormCText($employee['strFatherName']),
            'adult' => bonusFormCAdult($employee['dateofbirth'], $yearStart),
            'designation' => bonusFormCText($employee['designation']),
            'days' => bonusFormCNumber($employee['workingdays']),
            'rate' => bonusFormCNumber($employee['skillrate']),
            'salary' => bonusFormCNumber($employee['basicwages']),
            'bonus' => bonusFormCNumber($bonus),
            'customary' => bonusFormCNumber(0),
            'advance' => bonusFormCNumber(0),
            'tax' => bonusFormCNumber(0),
            'paid' => bonusFormCNumber($bonus),
            'payment_date' => bonusFormCDate($employee['salarypaiddate'])
        );
    }
    return $rows;
}

function renderBonusFormCHtml(array $employees, $salaryMonth, $companyName)
{
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    $startYear = (int) $period->format('Y') - ((int) $period->format('n') < 4 ? 1 : 0);
    $rows = getBonusFormCRows($employees, $salaryMonth);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $css = 'body{font-family:"Times New Roman";font-size:11px}table{border-collapse:collapse}.head{width:100%;margin-bottom:12px}.title{text-align:center;font-size:20px;font-weight:bold;border-top:3px double #000;border-bottom:3px double #000}.main{width:100%;table-layout:fixed}.main th,.main td{border:1px solid #000;padding:4px;text-align:center}.left{text-align:left}.info{font-size:12px;line-height:1.6}';
    $html = '<html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body><table class="head"><tr>' .
        '<td width="38%"><b>NAME &amp; ADDRESS OF CONTRACTOR</b><br>SHREE GANESH ENGINEERING CO.<br>FF-8, Devshruti Complex,<br>Nr. HCG Hospital,<br>Mithakhali, Ahmedabad - 380006</td>' .
        '<td width="28%"><div class="title">REGISTER OF BONUS</div><div style="text-align:center;font-size:16px;font-weight:bold">FORM C</div><div style="text-align:center;text-decoration:underline">See rule 4(b)</div></td>' .
        '<td width="34%" class="info">Bonus paid to the employees for the accounting year ' . $startYear . '-' . substr((string) ($startYear + 1), -2) . '<br><br>Month No. ' . $e($period->format('m')) . '<br><br>Name &amp; Address of the principal Employers : ' . $e($companyName) . '</td></tr></table>' .
        '<table class="main"><tr><th rowspan="2">Sr.<br>No.</th><th rowspan="2">Name of Workmen</th><th rowspan="2">Father Name</th><th rowspan="2">Whether he has Completed 15 years of age at the beginning of the accounting year</th><th rowspan="2">Designation/ Nature of Work</th><th rowspan="2">No. of days Worked</th><th rowspan="2">Daily Rate</th><th rowspan="2">Total salary or wage in respect of the accounting year</th><th rowspan="2">Amount of Bonus Payable under section 10/11 as the case may be</th><th colspan="3">Deduction</th><th rowspan="2">Actually Amount Paid</th><th rowspan="2">Date of Payment</th><th rowspan="2">Signature / Thumb impression of workmen</th></tr><tr><th>Puja or other customary bonus paid during the accounting year</th><th>Interim bonus or bonus paid in advance</th><th>Amount of income tax deducted 10-A</th></tr>' .
        '<tr>' . implode('', array_map(function ($n) {
            return '<td>' . $n . '</td>';
        }, range(1, 15))) . '</tr>';
    $totals = array('days' => 0, 'salary' => 0, 'bonus' => 0, 'paid' => 0);
    foreach ($rows as $index => $row) {
        foreach (array_keys($totals) as $key) {
            $totals[$key] += (float) $row[$key];
        }
        $html .= '<tr><td>' . ($index + 1) . '</td><td class="left">' . $e($row['name']) . '</td><td class="left">' . $e($row['father']) . '</td><td>' . $e($row['adult']) . '</td><td>' . $e($row['designation']) . '</td><td>' . $row['days'] . '</td><td>' . $row['rate'] . '</td><td>' . $row['salary'] . '</td><td>' . $row['bonus'] . '</td><td>' . $row['customary'] . '</td><td>' . $row['advance'] . '</td><td>' . $row['tax'] . '</td><td>' . $row['paid'] . '</td><td>' . $row['payment_date'] . '</td><td></td></tr>';
    }
    $html .= '<tr><td></td><td colspan="2"><b>TOTAL</b></td><td></td><td></td><td>' . bonusFormCNumber($totals['days']) . '</td><td>0.00</td><td>' . bonusFormCNumber($totals['salary']) . '</td><td>' . bonusFormCNumber($totals['bonus']) . '</td><td>0.00</td><td>0.00</td><td>0.00</td><td>' . bonusFormCNumber($totals['paid']) . '</td><td>0</td><td></td></tr></table><div style="text-align:right;font-style:italic">i.e. Basic x 8.33 %</div></body></html>';
    return $html;
}

function renderBonusFormCList(array $employees, $companyId, $salaryMonth)
{
    $e = function ($value) {
        return htmlspecialchars(bonusFormCText($value), ENT_QUOTES, 'UTF-8');
    };
    $query = 'Company=' . rawurlencode($companyId) . '&salarymasterId=' . rawurlencode($salaryMonth);
    $html = '<style>body{font-family:Arial;padding:15px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:9px;text-align:left}th{background:#f3f3f3}.exports{margin-bottom:14px}a{color:#337ab7;text-decoration:none}</style><div class="exports"><a target="_blank" href="generateBonusFormCReportPDF.php?' . $query . '">Export All PDF</a> | <a target="_blank" href="exportBonusFormCReportExcel.php?' . $query . '">Export All Excel</a></div><table><thead><tr><th>Sr. No.</th><th>Employee Name</th><th>Action</th></tr></thead><tbody>';
    foreach ($employees as $index => $employee) {
        $one = $query . '&employeeId=' . rawurlencode($employee['employeeId']);
        $html .= '<tr><td>' . ($index + 1) . '</td><td>' . $e($employee['emp_name']) . '</td><td><a target="_blank" href="generateBonusFormCReportPDF.php?' . $one . '">PDF</a> | <a target="_blank" href="exportBonusFormCReportExcel.php?' . $one . '">Excel</a></td></tr>';
    }
    return $html . ($employees ? '' : '<tr><td colspan="3" style="text-align:center">No Data Found !</td></tr>') . '</tbody></table>';
}
