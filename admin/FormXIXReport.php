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

function renderFormXIXHtml(array $employees, $salaryMonth, $companyName, array $advances = array())
{
    $template = file_get_contents(__DIR__ . '/SGECO-forms/wages-complete.html');
    if ($template === false || !preg_match('/<body[^>]*>(.*)<\/body>/is', $template, $body)) {
        throw new RuntimeException('The Form XIX template could not be loaded.');
    }
    $headEnd = stripos($template, '</head>');
    $documentHead = substr($template, 0, $headEnd + 7);
    $page = trim($body[1]);
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    $esc = function ($value) {
        return htmlspecialchars(formXIXValue($value), ENT_QUOTES, 'UTF-8');
    };
    $pages = array();
    foreach ($employees as $index => $employee) {
        $advance = getEmployeeCompanyReportAdvance($advances, $employee['employeeId']);
        $gross = (float) $employee['total'] + (float) $employee['MedicalAllowanceamt'];
        $deductions = (float) $employee['pt'] + (float) $employee['pf'] + (float) $employee['esi'] +
            (float) $employee['deductionifany'] + (float) $advance;
        $net = (float) $employee['netamountpaid'] - (float) $advance;
        $name = trim(formXIXValue($employee['emp_name']) . ' ' .
            (formXIXValue($employee['strFatherName']) === '0' ? '' : $employee['strFatherName']));
        $wages = array($employee['skillrate'], 0, 0, 0, 0, 0, $employee['othours']);
        $earnings = array(
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
        );
        $deductionValues = array(
            $employee['pt'],
            $employee['pf'],
            $employee['esi'],
            0,
            $employee['deductionifany'],
            $advance
        );
        $current = $page;
        $replacements = array(
            '5',
            '12443',
            'September-2024',
            'SHREE GANESH ENGINEERING COMPANY',
            'Dilip Mahendra Yadav',
            'TATA Chemicals Limited, Mithapur'
        );
        $values = array(
            $index + 1,
            $employee['pfcode'],
            $period ? $period->format('F-Y') : '0',
            $companyName,
            $name,
            $companyName
        );
        foreach ($replacements as $key => $literal) {
            $current = preg_replace('/' . preg_quote($literal, '/') . '/', $esc($values[$key]), $current, 1);
        }
        // Worker number is the second literal 5 in the header after Sr. No.
        $current = preg_replace(
            '/(<span class="field-title">Worker No\.:<\/span>\s*<span class="field-value">).*?(<\/span>)/s',
            '$1' . $esc($employee['employeecode']) . '$2',
            $current,
            1
        );
        $amounts = array_merge($wages, $earnings, $deductionValues);
        $position = 0;
        $current = preg_replace_callback(
            '/<table class="payroll-lines amount-column">(.*?)<\/table>/s',
            function ($match) use (&$position, $amounts) {
                return preg_replace_callback('/(<td>).*?(<\/td>)/s', function ($cell) use (&$position, $amounts) {
                    $value = isset($amounts[$position]) ? formXIXAmount($amounts[$position]) : '0.00';
                    $position++;
                    return $cell[1] . $value . $cell[2];
                }, $match[0]);
            },
            $current
        );
        $bankValues = array($employee['bankname'], $employee['ifsccode'], $employee['uan']);
        $bankPosition = 0;
        $current = preg_replace_callback(
            '/(<td class="bank-value">).*?(<\/td>)/s',
            function ($match) use (&$bankPosition, $bankValues, $esc) {
                return $match[1] . $esc($bankValues[$bankPosition++]) . $match[2];
            },
            $current
        );
        $totals = array($employee['workingdays'], $gross, $deductions, $net);
        $totalPosition = 0;
        $current = preg_replace_callback(
            '/(<td class="total-value"[^>]*>).*?(<\/td>)/s',
            function ($match) use (&$totalPosition, $totals) {
                return $match[1] . formXIXAmount($totals[$totalPosition++]) . $match[2];
            },
            $current
        );
        $current = preg_replace_callback(
            '/(<td style="text-align: right;">)\s*17086\.00\s*(<\/td>)/s',
            function ($match) use ($net) {
                return $match[1] . formXIXAmount($net) . $match[2];
            },
            $current,
            1
        );
        // The supplied address and work description have no Employee/Salary fields.
        $current = preg_replace(
            '/FF-8, DEVSHRUTI COMPLEX, NR\. HCG HOSPITAL,\s*MITHAKHALI, AHMEDABAD-380006\./',
            '0',
            $current,
            1
        );
        $current = str_replace('Work of HPB-4 Boiler Valve/Piping Replacement.', '0', $current);
        if ($index < count($employees) - 1) {
            $current = preg_replace(
                '/class="print-page"/',
                'class="print-page" style="page-break-after:always"',
                $current,
                1
            );
        }
        $pages[] = $current;
    }
    return $documentHead . '<body>' . ($pages ? implode("\n", $pages) :
        '<div class="print-page"><h2 style="text-align:center">No Data Found !</h2></div>') . '</body></html>';
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
