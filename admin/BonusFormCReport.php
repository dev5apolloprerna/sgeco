<?php

/** Register of Bonus (Form C) data and template renderer. */
function getBonusFormCListEmployees($dbconn, $companyId, $salaryMonth)
{
    if (!($dbconn instanceof mysqli)) {
        throw new RuntimeException('A database connection could not be established.');
    }

    // Match the Form C listing filter exactly. Keep this query separate from
    // getBonusFormCEmployees(), which is shared by the existing PDF and Excel
    // exports and must retain its current behaviour.
    $companyId = mysqli_real_escape_string($dbconn, $companyId);
    $salaryMonth = mysqli_real_escape_string($dbconn, $salaryMonth);
    $sql = "SELECT sd.salarydetailsId, sd.workingdays, sd.skillrate, sd.basicwages,
                   sd.iBonusAmt, sd.salarypaiddate, e.employeeId, e.employeecode, e.emp_name,
                   e.strFatherName, e.dateofbirth, e.designation
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId = sd.emp_id
                AND e.isDelete = '0'
            WHERE sd.companyId = '" . $companyId . "'
              AND sd.salaryId IN (
                  SELECT salarymasterId FROM salarymaster
                  WHERE month = '" . $salaryMonth . "'
                    AND isDelete = '0' AND istatus = '1'
              )
              AND sd.isDelete = '0'
              AND sd.istatus = '1'
              AND sd.workingdays > 0
            ORDER BY sd.salarydetailsId ASC";

    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Register of Bonus employees.');
    }

    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

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
                   sd.iBonusAmt, sd.salarypaiddate, e.employeeId, e.employeecode, e.emp_name,
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
        $paymentDate = bonusFormCDate($employee['salarypaiddate']);
        $rows[] = array(
            'name' => bonusFormCText($employee['emp_name']),
            'father' => bonusFormCText($employee['strFatherName']),
            'adult' => bonusFormCAdult($employee['dateofbirth'], $yearStart),
            'designation' => trim((string) $employee['designation']),
            'days' => bonusFormCNumber($employee['workingdays']),
            'rate' => bonusFormCNumber($employee['skillrate']),
            'salary' => bonusFormCNumber($employee['basicwages']),
            'bonus' => bonusFormCNumber($bonus),
            'customary' => '',
            'advance' => '',
            'tax' => '',
            'paid' => bonusFormCNumber($bonus),
            'payment_date' => $paymentDate === '0' ? '' : $paymentDate
        );
    }
    return $rows;
}

function getBonusFormCPeriod($salaryMonth)
{
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    if (!$period || $period->format('m/Y') !== $salaryMonth) {
        throw new InvalidArgumentException('A valid salary month is required.');
    }
    $startYear = (int) $period->format('Y') - ((int) $period->format('n') < 4 ? 1 : 0);
    return array(
        'month_number' => $period->format('m'),
        'bonus_month' => $period->format('F-Y'),
        'accounting_year' => $startYear . '-' . substr((string) ($startYear + 1), -2)
    );
}

/**
 * Return the single column grid used by every Form C table row.
 *
 * TCPDF does not reliably apply HTML colgroups after rowspans/colspans, so the
 * renderer also writes these percentages on every header and body cell.
 */
function getBonusFormCColumnWidths()
{
    return array(2, 16.5, 16, 6.5, 5.5, 4, 4.5, 6.5, 6.5, 5.5, 5.5, 5.5, 6, 4.5, 5);
}

function renderBonusFormCHtml(array $employees, $salaryMonth, $companyName)
{
    $period = getBonusFormCPeriod($salaryMonth);
    $rows = getBonusFormCRows($employees, $salaryMonth);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    // TCPDF calculates widths independently for rows after a rowspan header.
    // Repeat the width on every cell so header and body keep one grid.
    $columnWidths = getBonusFormCColumnWidths();
    $cell = function ($tag, $column, $value, $attributes = '') use ($columnWidths) {
        return '<' . $tag . ' width="' . $columnWidths[$column] . '%"' . $attributes . '>' .
            $value . '</' . $tag . '>';
    };
    $colgroup = '<colgroup>';
    foreach ($columnWidths as $width) {
        $colgroup .= '<col width="' . $width . '%">';
    }
    $colgroup .= '</colgroup>';
    // $css = '@page{size:A4 landscape;margin:15mm 10mm}body{font-family:"Times New Roman",Times,serif;color:#000;margin:0;padding:0;font-size:12px}table{border-collapse:collapse}.head{width:100%;margin-bottom:10px}.head td{vertical-align:top;padding:0;font-size:12px}.contractor{white-space:nowrap;font-weight:bold}.title{text-align:center;font-size:12px;font-weight:bold;letter-spacing:1px;border-top:3px double #000;border-bottom:3px double #000;padding:4px 0}.main{width:100%;border:1px solid #000;margin-top:15px;table-layout:fixed}.main th,.main td{border:1px solid #000;padding:4px;text-align:center;vertical-align:middle;font-size:12px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word}.left{text-align:left!important;padding-left:6px!important}.info{font-size:12px!important;line-height:1.6}.note{width:100%;margin-top:6px;text-align:right;font-family:"Brush Script MT",cursive;font-style:italic;font-size:12px;padding-right:260px}';
    // $html = '<html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body><table class="head"><tr>' .
    //     '<td width="45%"><span class="contractor">NAME &amp; ADDRESS OF CONTRACTOR</span>&nbsp;&nbsp;SHREE GANESH ENGINEERING CO.<br><span style="margin-left:214px">FF-8, Devshruti Complex,</span><br><span style="margin-left:214px">Nr. HCG Hospital,</span><br><span style="margin-left:214px">Mithakhali, Ahmedabad - 380006</span></td>' .
    //     '<td width="25%"><div class="title">REGISTER OF BONUS</div><div style="text-align:center;font-size:12px;font-weight:bold;padding-top:6px">FORM C</div><div style="text-align:center;text-decoration:underline;font-size:12px">See rule 4(b)</div></td>' .
    //     '<td width="30%" class="info"><strong>Bonus paid to the employees for the accounting year ' . $e($period['accounting_year']) . '<br><br>Bonus for Month of ' . $e($period['bonus_month']) . '<br><br>Name &amp; Address of the principal Employers : ' . $e($companyName) . '</strong></td></tr></table>' .
    $css = '@page{size:A4 landscape;margin:15mm 10mm}body{font-family:"Times New Roman",Times,serif;color:#000;margin:0;padding:0;font-size:12px}table{border-collapse:collapse}.header-table{margin-bottom:10px}.title{text-align:center;font-size:12px;font-weight:bold;letter-spacing:1px;border-top:3px double #000;border-bottom:3px double #000;padding:4px 0}.main{width:100%;border:1px solid #000;margin-top:15px;table-layout:fixed}.main th,.main td{border:1px solid #000;padding:4px;text-align:center;vertical-align:middle;font-size:12px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word}.left{text-align:left!important;padding-left:6px!important}.amount{text-align:right!important;padding-right:6px!important}.note{width:100%;margin-top:6px;text-align:right;font-family:"Brush Script MT",cursive;font-style:italic;font-size:12px;padding-right:260px}';
    $html = '<html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body><table class="header-table" border="0" cellspacing="0" cellpadding="0"><tr>' .
        '<td class="left-header" width="41%"><table class="info-table" border="0" cellspacing="0" cellpadding="0"><tr><td class="info-label" width="55%" style="font-size:10px;"><strong>NAME AND ADDRESS OF CONTRACTOR :</strong></td><td width="45%" class="normal-weight" style="font-size:10px;">SHREE GANESH ENGINEERING CO.</td></tr><tr><td></td><td class="address-line" style="font-size:10px;">FF-8, Devshruti Complex,</td></tr><tr><td></td><td class="address-line" style="font-size:10px;">Nr. HCG Hospital,</td></tr><tr><td></td><td class="address-line" style="font-size:10px;">Mithakhali, Ahmedabad - 380006</td></tr></table></td>' .
        '<td class="center-header" width="18%"><div class="title">REGISTER OF BONUS</div><div class="form-number" style="padding-top:6px;text-align:center;"><strong>FORM C</strong></div><div style="text-decoration:underline;text-align:center"><strong>See rule 4(b)</strong></div></td>' .
        '<td class="right-header" width="41%"><div class="right-line" style="font-size:10px;"><strong>Name and Address of establishment in/under which contract is carried on</strong></div><div class="right-line" style="font-size:10px;"><strong>Bonus paid to the employees for the accounting year:</strong> <span class="normal-weight">' . $e($period['bonus_month']) . '</span></div><div class="right-line" style="font-size:10px;"><strong>Name and Address of the principal Employer :</strong> <span class="normal-weight">' . $e($companyName) . '</span></div></td></tr></table>' .
        '<table class="main">' . $colgroup . '<tr>' .
        $cell('th', 0, 'Sr.<br>No.', ' style="text-align:center" rowspan="2"') .
        $cell('th', 1, 'Name of Workmen', ' style="text-align:center" rowspan="2"') .
        $cell('th', 2, 'Father Name', ' style="text-align:center" rowspan="2"') .
        $cell('th', 3, 'Whether he has Completed 15 years of age at the beginning of the accounting year', ' style="text-align:center" rowspan="2"') .
        $cell('th', 4, 'Designation/ Nature of Work', ' style="text-align:center" rowspan="2"') .
        $cell('th', 5, 'No. of days Worked', ' style="text-align:center" rowspan="2"') .
        $cell('th', 6, 'Daily Rate', ' style="text-align:center" rowspan="2"') .
        $cell('th', 7, 'Total salary or wage in respect of the accounting year', ' style="text-align:center" rowspan="2"') .
        $cell('th', 8, 'Amount of Bonus Payable under section 10/11 as the case may be', ' style="text-align:center" rowspan="2"') .
        '<th width="16.5%" style="text-align:center" colspan="3">Deduction</th>' .
        $cell('th', 12, 'Actually Amount Paid', ' style="text-align:center" rowspan="2"') .
        $cell('th', 13, 'Date of Payment', ' style="text-align:center" rowspan="2"') .
        $cell('th', 14, 'Signature / Thumb impression of workmen', ' style="text-align:center" rowspan="2"') .
        '</tr><tr>' .
        $cell('th', 9, 'Puja or other customary bonus paid during the accounting year', ' style="text-align:center"') .
        $cell('th', 10, 'Interim bonus or bonus paid in advance', ' style="text-align:center"') .
        $cell('th', 11, 'Amount of income tax deducted 10-A', ' style="text-align:center"') .
        '</tr><tr>' . implode('', array_map(function ($n) use ($cell) {
            return $cell('td', $n - 1, $n, ' align="center" style="text-align:center;font-weight: 700;"');
        }, range(1, 15))) . '</tr>';
    $totals = array('days' => 0, 'salary' => 0, 'bonus' => 0, 'paid' => 0);
    foreach ($rows as $index => $row) {
        foreach (array_keys($totals) as $key) {
            $totals[$key] += (float) $row[$key];
        }
        $values = array(
            $index + 1,
            $e($row['name']),
            $e($row['father']),
            $e($row['adult']),
            $e($row['designation']),
            $row['days'],
            $row['rate'],
            $row['salary'],
            $row['bonus'],
            $row['customary'],
            $row['advance'],
            $row['tax'],
            $row['paid'],
            $row['payment_date'],
            ''
        );
        $html .= '<tr>';
        foreach ($values as $column => $value) {
            // $html .= $cell('td', $column, $value, in_array($column, array(1, 2), true) ? ' class="left"' : '');
            $cellClass = in_array($column, array(1, 2), true)
                ? 'left'
                : (in_array($column, array(8, 11, 12), true) ? 'amount' : '');
            $html .= $cell('td', $column, $value, $cellClass === '' ? '' : ' class="' . $cellClass . '"');
        }
        $html .= '</tr>';
    }
    $totalValues = array(
        '',
        '<b>TOTAL</b>',
        '',
        '',
        '',
        bonusFormCNumber($totals['days']),
        '',
        bonusFormCNumber($totals['salary']),
        bonusFormCNumber($totals['bonus']),
        '',
        '',
        '',
        bonusFormCNumber($totals['paid']),
        '',
        ''
    );
    $html .= '<tr>';
    foreach ($totalValues as $column => $value) {
        // $html .= $cell('td', $column, $value);
        $html .= $cell(
            'td',
            $column,
            $value,
            in_array($column, array(8, 11, 12), true) ? ' class="amount"' : ''
        );
    }
    $html .= '</tr></table></body></html>';
    return preg_replace('/(<th(?:\s[^>]*)?>)(.*?)(<\/th>)/s', '$1<strong>$2</strong>$3', $html);
}
