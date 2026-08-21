<?php

/** Data retrieval and template binding for Form XII employment cards. */
function getFormXIIEmployees($dbconn, $companyId, $salaryMonth, $employeeId = 0)
{
    $companyId = (int) $companyId;
    $employeeId = (int) $employeeId;
    if ($companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    $employeeWhere = $employeeId > 0 ? ' AND e.employeeId=' . $employeeId : '';
    $sql = "SELECT sd.salarydetailsId, sd.skillrate, e.employeeId, e.employeecode,
                   e.emp_name, e.uan, e.adharcard, e.mno, e.designation, e.dateofjoining
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId=sd.emp_id
                AND e.isDelete='0' AND e.istatus='1'
            WHERE sd.companyId=" . $companyId . "
              AND sd.salaryId IN (SELECT salarymasterId FROM salarymaster
                  WHERE month='" . $month . "' AND isDelete='0' AND istatus='1')
              AND sd.isDelete='0' AND sd.istatus='1' AND sd.workingdays > 0" .
        $employeeWhere . " ORDER BY e.emp_name ASC, sd.salarydetailsId ASC";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) throw new RuntimeException('Unable to retrieve Form XII employees.');
    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) $employees[] = $row;
    return $employees;
}

function formXIIFormatDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') return '';
    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date('d-m-Y', $timestamp);
}

function formXIIEmployeeData(array $employee)
{
    $rate = trim((string) $employee['skillrate']);
    return array(
        'name' => trim((string) $employee['emp_name']),
        'uan_aadhaar' => 'UAN: ' . trim((string) $employee['uan']) . ' / Aadhar: ' . trim((string) $employee['adharcard']),
        'mobile' => trim((string) $employee['mno']),
        'serial' => trim((string) $employee['employeecode']),
        'designation' => trim((string) $employee['designation']),
        'rate' => $rate === '' ? '' : number_format((float) $rate, 2, '.', ''),
        'joining' => formXIIFormatDate($employee['dateofjoining'])
    );
}

function renderFormXIICard(array $employee)
{
    $template = file_get_contents(__DIR__ . '/SGECO-forms/Form-XII-complete.html');
    if ($template === false) throw new RuntimeException('The Form XII template could not be loaded.');
    $d = formXIIEmployeeData($employee);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $html = strtr($template, array(
        'Rajiakbhai Sattarbhai Vahora' => $e($d['name']),
        'UAN: 100311841131 / Aadhar: 9164 3383 2027' => $e($d['uan_aadhaar']),
        '7984411021' => $e($d['mobile']),
        '<span class="short-input-line">1</span>' => '<span class="short-input-line">' . $e($d['serial']) . '</span>',
        '<span class="filled-value">Supervisor</span>' => '<span class="filled-value">' . $e($d['designation']) . '</span>',
        '<span class="filled-value">510.50</span>' => '<span class="filled-value">' . $e($d['rate']) . '</span>',
        '<span class="date-line"></span>' => '<span class="date-line">' . $e($d['joining']) . '</span>'
    ));
    return $html;
}

function renderFormXIIList(array $employees, $companyId, $salaryMonth)
{
    $query = 'Company=' . rawurlencode($companyId) . '&salarymasterId=' . rawurlencode($salaryMonth);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;padding:18px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:9px;text-align:left}th{background:#f5f5f5}.actions{white-space:nowrap}.exports{margin-bottom:14px}.exports a,.actions a{color:#167ac6;text-decoration:none;font-weight:bold}.empty{text-align:center}</style></head><body>';
    $html .= '<div class="exports"><a target="_blank" href="generateFormXIIReportPDF.php?' . $query . '">Export All PDF</a> | <a target="_blank" href="exportFormXIIReportExcel.php?' . $query . '">Export All Excel</a></div>';
    $html .= '<table><thead><tr><th>Sr. No.</th><th>Employee Name</th><th>UAN / Aadhaar No.</th><th>Mobile</th><th>Designation</th><th>Wages Rate</th><th>Joining Date</th><th>Action</th></tr></thead><tbody>';
    foreach ($employees as $index => $employee) {
        $d = formXIIEmployeeData($employee);
        $employeeQuery = $query . '&employeeId=' . rawurlencode($employee['employeeId']);
        $html .= '<tr><td>' . ($index + 1) . '</td><td>' . $e($d['name']) . '</td><td>' . $e($d['uan_aadhaar']) . '</td><td>' . $e($d['mobile']) . '</td><td>' . $e($d['designation']) . '</td><td>' . $e($d['rate']) . '</td><td>' . $e($d['joining']) . '</td><td class="actions"><a target="_blank" href="generateFormXIIReportPDF.php?' . $employeeQuery . '">PDF</a> | <a target="_blank" href="exportFormXIIReportExcel.php?' . $employeeQuery . '">Excel</a></td></tr>';
    }
    if (!$employees) $html .= '<tr><td colspan="8" class="empty">No Data Found !</td></tr>';
    return $html . '</tbody></table></body></html>';
}
