<?php

require_once(__DIR__ . '/companyReportAdvance.php');

/**
 * Wages-slip data is deliberately read from the persisted payroll result.
 * querydata.php is the single place that calculates these amounts; reports
 * must not reproduce that calculation and risk producing different figures.
 */
function getFormXIXEmployees($dbconn, $companyId, $salaryMonth, $employeeId = 0)
{
    $companyId = (int) $companyId;
    $employeeId = (int) $employeeId;
    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    if ($companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('A valid company, month, and year are required.');
    }
    $employeeWhere = $employeeId > 0 ? ' AND e.employeeId=' . $employeeId : '';
    $sql = "SELECT sd.*, e.employeeId, e.emp_name, e.strFatherName, e.employeecode,
                   e.pfcode, e.uan, e.ifsccode, e.bankid,
                   COALESCE(b.bankname, '0') AS bankname
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId=sd.emp_id
                AND e.isDelete='0' AND e.istatus='1'
            LEFT JOIN bankmaster b ON b.bankmasterId=e.bankid AND b.isDelete='0'
            WHERE sd.companyId=" . $companyId . "
              AND sd.salaryId IN (SELECT salarymasterId FROM salarymaster
                  WHERE month='" . $month . "' AND isDelete='0' AND istatus='1')
              AND sd.isDelete='0' AND sd.istatus='1' AND sd.workingdays > 0"
        . $employeeWhere . " ORDER BY e.emp_name ASC, sd.salarydetailsId ASC";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve wages-slip employees.');
    }
    $employees = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function getFormXIXCompanyName($dbconn, $companyId)
{
    $result = mysqli_query($dbconn, 'SELECT companyname FROM companymaster WHERE companymasterId=' .
        (int) $companyId . " AND isDelete='0' AND istatus='1' LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if (!$row) {
        throw new RuntimeException('The selected company could not be found.');
    }
    return trim((string) $row['companyname']) !== '' ? $row['companyname'] : '0';
}

function formXIXValue($value)
{
    return trim((string) $value) === '' ? '0' : (string) $value;
}

function formXIXAmount($value)
{
    return number_format((float) formXIXValue($value), 2, '.', '');
}

function getFormXIXSlipData(array $employees, $salaryMonth, $companyName, array $advances = array())
{
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    $slips = array();
    foreach ($employees as $index => $employee) {
        $advance = getEmployeeCompanyReportAdvance($advances, $employee['employeeId']);
        $fatherName = formXIXValue($employee['strFatherName']);
        $slips[] = array(
            'serial' => $index + 1,
            'pf_number' => formXIXValue($employee['pfcode']),
            'worker_number' => formXIXValue($employee['employeecode']),
            'period' => $period ? $period->format('F-Y') : '0',
            'company' => formXIXValue($companyName),
            'workman' => trim(formXIXValue($employee['emp_name']) . ($fatherName === '0' ? '' : ' ' . $fatherName)),
            'wages' => array($employee['skillrate'], 0, 0, 0, 0, 0, $employee['othours']),
            'earnings' => array(
                $employee['basicwages'],
                $employee['hra'],
                0,
                0,
                $employee['MedicalAllowanceamt'],
                0,
                $employee['totalovertime'],
                0,
                0,
                0,
                $employee['iBonusAmt'],
                $employee['iLeaveAmt'],
                $employee['national_holiday_payment']
            ),
            'deductions' => array(
                $employee['pt'],
                $employee['pf'],
                $employee['esi'],
                0,
                $employee['deductionifany'],
                $advance
            ),
            'bank_name' => formXIXValue($employee['bankname']),
            'ifsc' => formXIXValue($employee['ifsccode']),
            'uan' => formXIXValue($employee['uan']),
            'work_days' => $employee['workingdays'],
            'gross' => (float) $employee['total'] + (float) $employee['MedicalAllowanceamt'],
            'total_deduction' => (float) $employee['pt'] + (float) $employee['pf'] +
                (float) $employee['esi'] + (float) $employee['deductionifany'] + (float) $advance,
            'net_pay' => (float) $employee['netamountpaid'] - (float) $advance
        );
    }
    return $slips;
}

function renderFormXIXSlipTable(array $slip)
{
    $esc = function ($value) {
        return htmlspecialchars(formXIXValue($value), ENT_QUOTES, 'UTF-8');
    };
    $lines = function (array $labels, array $values) use ($esc) {
        $html = '';
        foreach ($labels as $index => $label) {
            $html .= '&nbsp;&nbsp;' . $esc($label) . '<br>';
        }
        return $html;
    };
    $amountLines = function (array $values) {
        $html = '';
        foreach ($values as $value) {
            // Keep figures away from the following column rule in TCPDF.
            $html .= formXIXAmount($value) . '&nbsp;&nbsp;<br>';
        }
        return $html;
    };
    $wageLabels = array('Minimum', 'HRA', 'Conveyance', 'Education', 'Medical', 'Washing', 'OT Hours');
    $earningLabels = array(
        'Basic',
        'HRA',
        'Conveyance',
        'Education',
        'Medical',
        'Washing',
        'OT Earn',
        'Food Allow.',
        'Other Allow.',
        'Mobile Allow.',
        'Bonus',
        'Leave',
        'National Holiday'
    );
    $deductionLabels = array('Prof. Tax', 'PF', 'ESIC', 'GL.W.F.', 'Other Deduction', 'Advance');

    $sideBorders = 'border-left:1px solid #000;border-right:1px solid #000;';
    //$totalBorder = 'border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;';
    $totalBorder = 'border-top:1px solid #000;border-right:1px solid #000;';

    /*
     * TCPDF supports a deliberately small subset of browser CSS.  Keep this
     * layout table based, and set each edge explicitly, so the generated PDF
     * matches the approved HTML form without depending on unsupported flex,
     * float, or pseudo-element rules.
     */
    return '<style>table{font-family:helvetica;font-size:9pt;color:#000}td{line-height:1.25}</style>' .
        '<table width="100%" border="0" cellpadding="0" cellspacing="0" nobr="true">' .
        '<tr><td colspan="7" height="34" align="center" valign="middle" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;font-size:16pt;font-weight:bold">' .
        'FORM - XIX - [ SEE RULE - 78(I)(B) ] - WAGES SLIP</td></tr>' .
        '<tr><td colspan="7" height="30" valign="middle" style="' . $sideBorders . '"><table width="100%" cellpadding="4"><tr>' .
        '<td width="25%">Sr. No.: <b>' . $esc($slip['serial']) . '</b></td>' .
        '<td width="25%">PF No.: <b>' . $esc($slip['pf_number']) . '</b></td>' .
        '<td width="25%">Worker No.: <b>' . $esc($slip['worker_number']) . '</b></td>' .
        '<td width="25%" align="right">Period: <b>' . $esc($slip['period']) . '</b></td>' .
        '</tr></table></td></tr>' .
        '<tr><td colspan="7" style="' . $sideBorders . '"><table width="100%" cellpadding="4"><tr>' .
        '<td width="22%"><b>Name &amp; Address of Contractor</b></td><td width="78%">' .
        'SHREE GANESH ENGINEERING COMPANY<br>FF-8, DEVSHRUTI COMPLEX, NR. HCG HOSPITAL, ' .
        'MITHAKHALI, AHMEDABAD-380006.</td></tr></table></td></tr>' .
        '<tr><td colspan="7" height="28" style="' . $sideBorders . '"><table width="100%" cellpadding="4"><tr>' .
        '<td width="40%"><b>Code/Name and Father\'s/Husband\'s Name of Workman:</b></td><td width="60%">' .
        $esc($slip['workman']) . '</td></tr></table></td></tr>' .
        '<tr><td colspan="7" height="34" style="' . $sideBorders . '"><table width="100%" cellpadding="4"><tr>' .
        '<td width="22%"><b>Nature and Location of Work :</b></td><td width="78%">' . $esc($slip['company']) .
        '</td></tr></table></td></tr>' .
        '<tr style="font-weight:bold;text-align:center">' .
        '<td width="14%" height="25" align="left" style="border-top:1px solid #000;border-bottom:1px solid #000;border-left:1px solid #000">&nbsp;&nbsp;Head</td>' .
        '<td width="8%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000">Wages&nbsp;&nbsp;</td>' .
        '<td width="27%" colspan="2" style="border:1px solid #000">Earning</td>' .
        '<td width="24%" colspan="2" style="border:1px solid #000">Deduction</td>' .
        '<td width="27%" style="border:1px solid #000">Signature</td></tr>' .
        '<tr><td width="14%" height="250" style="border-left:1px solid #000">' . $lines($wageLabels, $slip['wages']) . '</td>' .
        '<td width="8%" align="right" style="border: none">' . $amountLines($slip['wages']) . '</td>' .
        '<td width="17%" style="border-left:1px solid #000">' . $lines($earningLabels, $slip['earnings']) . '</td>' .
        '<td width="10%" align="right" style="none">' . $amountLines($slip['earnings']) . '</td>' .
        '<td width="14%" style="border-left:1px solid #000">' . $lines($deductionLabels, $slip['deductions']) . '</td>' .
        '<td width="10%" align="right" style="border:none">' . $amountLines($slip['deductions']) . '</td>' .
        '<td width="27%" style="border:1px solid #000">&nbsp;&nbsp;Bank Name:&nbsp;&nbsp; ' . $esc($slip['bank_name']) . '<br>' .
        '&nbsp;&nbsp;IFSC Code:&nbsp;&nbsp; ' . $esc($slip['ifsc']) . '<br>&nbsp;&nbsp;UAN No.:&nbsp;&nbsp; ' .
        $esc($slip['uan']) . '</td></tr>' .
        '<tr style="font-weight:bold"><td width="14%" height="26" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">&nbsp;&nbsp;Work Days</td>' .
        '<td width="8%" align="right" style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;">' .
        formXIXAmount($slip['work_days']) . '&nbsp;&nbsp;</td> <td width="17%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">&nbsp;&nbsp;Gross Earn.</td>' .
        '<td width="10%" align="right" style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;">' . formXIXAmount($slip['gross']) . '&nbsp;&nbsp;' .
        '</td><td width="14%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;">&nbsp;&nbsp;Total Deduction</td>' .
        '<td width="10%" align="right" style="border-right:1px solid #000;border-top:1px solid #000;border-bottom:1px solid #000;">' .
        formXIXAmount($slip['total_deduction']) . '&nbsp;&nbsp;</td><td width="27%" style="' . $totalBorder . ';border-left:1px solid #000;border-bottom:1px solid #000;">' .
        '<table width="100%"><tr>' .
        '<td width="50%"><b>&nbsp;&nbsp;Net Pay.</b></td><td width="50%" align="right"><b>' .
        formXIXAmount($slip['net_pay']) . '&nbsp;&nbsp;</b></td></tr></table></td></tr>' .
        '<tr><td colspan="7" height="58" align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;border-bottom:1px solid #000;border-top:0.5px solid #777"><br><br><br><br>' .
        'Initials of the Contractor or his Representative&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
        '____________________________&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table>';
}

function renderFormXIXHtml(array $employees, $salaryMonth, $companyName, array $advances = array())
{
    $html = '<html><head><meta charset="UTF-8"><title>Wages Slip</title></head><body>';
    foreach (getFormXIXSlipData($employees, $salaryMonth, $companyName, $advances) as $index => $slip) {
        if ($index > 0) {
            $html .= '<br pagebreak="true">';
        }
        $html .= renderFormXIXSlipTable($slip);
    }
    return $html . '</body></html>';
}

function renderFormXIXList(array $employees, $companyId, $salaryMonth)
{
    $esc = function ($value) {
        return htmlspecialchars(formXIXValue($value), ENT_QUOTES, 'UTF-8');
    };
    $query = 'Company=' . rawurlencode($companyId) . '&salarymasterId=' . rawurlencode($salaryMonth);
    $html = '<style>body{font-family:Arial,sans-serif;padding:15px}table{border-collapse:collapse;width:100%}' .
        'th,td{border:1px solid #ddd;padding:9px;text-align:left}th{background:#f3f3f3}.exports{margin-bottom:14px}' .
        'a{color:#337ab7;text-decoration:none}</style><div class="exports"><a href="generateFormXIXReportPDF.php?' .
        $query . '" target="_blank">Export All PDF</a> | <a href="exportFormXIXReportExcel.php?' . $query .
        '" target="_blank">Export All Excel</a></div><table><thead><tr><th>Sr. No.</th><th>Worker No.</th>' .
        '<th>Employee Name</th><th>Action</th></tr></thead><tbody>';
    foreach ($employees as $index => $employee) {
        $individual = $query . '&employeeId=' . rawurlencode($employee['employeeId']);
        $html .= '<tr><td>' . ($index + 1) . '</td><td>' . $esc($employee['employeecode']) . '</td><td>' .
            $esc($employee['emp_name']) . '</td><td><a target="_blank" href="generateFormXIXReportPDF.php?' . $individual .
            '">PDF</a> | <a target="_blank" href="exportFormXIXReportExcel.php?' . $individual . '">Excel</a></td></tr>';
    }
    return $html . ($employees ? '' : '<tr><td colspan="4" style="text-align:center">No Data Found !</td></tr>') .
        '</tbody></table>';
}

function getFormXIXRequestData($dbconn)
{
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getFormXIXEmployees($dbconn, $companyId, $month, $employeeId);
    return renderFormXIXHtml(
        $employees,
        $month,
        getFormXIXCompanyName($dbconn, $companyId),
        getCompanyReportAdvances($dbconn, $companyId, $month)
    );
}
