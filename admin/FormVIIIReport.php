<?php

/** Data retrieval and template binding for Form VIII service certificates. */
function getFormVIIIEmployees($dbconn, $companyId, $salaryMonth, $employeeId = 0)
{
    $companyId = (int) $companyId;
    $employeeId = (int) $employeeId;
    if ($companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    $employeeWhere = $employeeId > 0 ? ' AND employee.employeeId=' . $employeeId : '';
    $sql = "SELECT DISTINCT employee.employeeId, employee.employeecode, employee.emp_name,
                   employee.uan, employee.adharcard, employee.mno, employee.designation,
                   employee.dateofjoining, employee.strExitDate
            FROM salarydetails
            INNER JOIN employee ON employee.employeeId=salarydetails.emp_id
                AND employee.isDelete='0'
            WHERE salarydetails.companyId=" . $companyId . "
              AND salarydetails.salaryId IN (SELECT salarymasterId FROM salarymaster
                  WHERE month='" . $month . "' AND isDelete='0' AND istatus='1')
              AND salarydetails.isDelete='0' AND salarydetails.istatus='1'
              AND salarydetails.workingdays > 0" . $employeeWhere . "
            ORDER BY employee.emp_name ASC";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve Form VIII employees.');
    }
    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function formVIIIFormatDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') return '';
    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date('d-m-Y', $timestamp);
}

function formVIIIPeriod(array $employee)
{
    $from = formVIIIFormatDate($employee['dateofjoining']);
    $to = formVIIIFormatDate($employee['strExitDate']);
    if ($from === '') return $to;
    return $from . ' to ' . ($to === '' ? 'Present' : $to);
}

function formVIIIEmployeeData(array $employee)
{
    return array(
        'name' => trim((string) $employee['emp_name']),
        'uan' => trim((string) $employee['uan']),
        'aadhaar' => trim((string) $employee['adharcard']),
        'mobile' => trim((string) $employee['mno']),
        'serial' => trim((string) $employee['employeecode']),
        'period' => formVIIIPeriod($employee),
        'designation' => trim((string) $employee['designation'])
    );
}

function renderFormVIIICertificate(array $employee)
{
    $template = file_get_contents(__DIR__ . '/SGECO-forms/Form-VIII-complete.html');
    if ($template === false) throw new RuntimeException('The Form VIII template could not be loaded.');
    $data = formVIIIEmployeeData($employee);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $replacements = array(
        'DILIP MAHENDRA YADAV' => $e($data['name']),
        '101740014604' => $e($data['uan']),
        '4360 7982 5423' => $e($data['aadhaar']),
        '7619818421' => $e($data['mobile']),
        '>99<' => '>' . $e($data['serial']) . '<',
        'Supervisor' => $e($data['designation'])
    );
    $html = strtr($template, $replacements);
    $period = $e($data['period']);
    return str_replace('<span class="employment-line"></span>', '<span class="employment-line">' . $period . '</span>', $html);
}


/**
 * Render the Word version without layout tables.
 *
 * Microsoft Word displays non-printing gridlines around borderless HTML
 * tables. Those gridlines made Form VIII look bordered while it was being
 * edited, so the Word-only layout deliberately uses block elements.
 */
function renderFormVIIIWordCertificate(array $employee)
{
    $data = formVIIIEmployeeData($employee);
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $rows = array(
        'Name of employer: <span class="value">Shree Ganesh Engineering Co.</span>',
        'LIN/PAN No. of the employer: <span class="value"><em>AAMFS3884N</em></span>',
        '<em>Email Id of the employer:</em> <span class="value"><em>hkshah@sgeco.in</em></span>',
        '<em>Mobile No. of the employer:</em> <span class="value">7984454082</span>',
        'Nature and location of work: <span class="blank-line">&nbsp;</span>',
        'Name of the workman: <span class="value">' . $e($data['name']) . '</span>',
        'UAN / Aadhaar No.: UAN: <span class="value">' . $e($data['uan']) . '</span> &nbsp;/&nbsp; Aadhaar No.: <span class="value">' . $e($data['aadhaar']) . '</span>',
        'Mobile No.: <span class="value">' . $e($data['mobile']) . '</span>',
        'Serial Number in the Register of Workmen: <span class="value">' . $e($data['serial']) . '</span>',
        'Period of Employment: <span class="value">' . $e($data['period']) . '</span>',
        'Designation: <span class="value">' . $e($data['designation']) . '</span>'
    );
    $details = '';
    foreach ($rows as $index => $row) {
        $details .= '<div class="detail-row row-' . ($index + 1) . '"><span class="number">' .
            ($index + 1) . '.</span><span class="detail">' . $row . '</span></div>';
    }

    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' .
        '*{box-sizing:border-box}html,body{margin:0;padding:0}body{font-family:"Times New Roman",Times,serif}' .
        '.certificate-page{width:210mm;height:246mm;padding:24mm 22mm 18mm 24mm;background:#fff;color:#000}' .
        '.heading{text-align:center}.form-title{font-size:22pt;font-weight:bold;line-height:1;padding-bottom:7mm}' .
        '.rule-title{font-size:13.3pt;line-height:1.25;white-space:nowrap;padding-bottom:2mm}' .
        '.certificate-title{font-size:19pt;font-weight:bold;line-height:1.1;margin-bottom:10mm}' .
        '.detail-row{font-size:13pt;line-height:1.28;margin:0 0 2.5mm 0;white-space:nowrap}' .
        '.number{display:inline-block;width:11mm;text-align:right;margin-right:2mm}.detail{display:inline}' .
        '.value{font-weight:bold;text-decoration:underline}.blank-line{display:inline-block;width:85mm;border-bottom:1px solid #000}' .
        '.row-5{margin-bottom:10mm}.signature{text-align:right;font-size:13pt;margin-top:43mm;white-space:nowrap}' .
        '@page{size:A4 portrait;margin:0}@media print{.certificate-page{margin:0;page-break-inside:avoid}}' .
        '</style></head><body><div class="certificate-page"><div class="heading">' .
        '<div class="form-title">FORM VIII</div>' .
        '<div class="rule-title">[Under rule 77 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]</div>' .
        '<div class="certificate-title">Service Certificate</div></div>' .
        '<div class="details">' . $details . '</div>' .
        '<div class="signature">Seal and Signature of Employer</div>' .
        '</div></body></html>';
}

function renderFormVIIIList(array $employees, $companyId, $salaryMonth)
{
    $query = 'Company=' . rawurlencode($companyId) . '&salarymasterId=' . rawurlencode($salaryMonth);
    $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;padding:18px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:9px;text-align:left}th{background:#f5f5f5}.actions{white-space:nowrap}.exports{margin-bottom:14px}.exports a,.actions a{color:#167ac6;text-decoration:none;font-weight:bold}.empty{text-align:center}</style></head><body>';
    $html .= '<div class="exports"><a target="_blank" href="generateFormVIIIReportPDF.php?' . $query . '">Export All PDF</a> | <a target="_blank" href="exportFormVIIIReportWord.php?' . $query . '">Export All Word</a></div>';
    $html .= '<table><thead><tr><th>Sr. No.</th><th>Employee Name</th><th>UAN / Aadhaar No.</th><th>Mobile</th><th>Designation</th><th>Period of Employment</th><th>Action</th></tr></thead><tbody>';
    foreach ($employees as $index => $employee) {
        $e = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };
        $data = formVIIIEmployeeData($employee);
        $employeeQuery = $query . '&employeeId=' . rawurlencode($employee['employeeId']);
        $html .= '<tr><td>' . ($index + 1) . '</td><td>' . $e($data['name']) . '</td><td>' . $e($data['uan']) . ' / ' . $e($data['aadhaar']) . '</td><td>' . $e($data['mobile']) . '</td><td>' . $e($data['designation']) . '</td><td>' . $e($data['period']) . '</td><td class="actions"><a target="_blank" href="generateFormVIIIReportPDF.php?' . $employeeQuery . '">PDF</a> | <a target="_blank" href="exportFormVIIIReportWord.php?' . $employeeQuery . '">Word</a></td></tr>';
    }
    if (!$employees) $html .= '<tr><td colspan="7" class="empty">No Data Found !</td></tr>';
    return $html . '</tbody></table></body></html>';
}
