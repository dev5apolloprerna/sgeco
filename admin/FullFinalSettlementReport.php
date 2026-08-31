<?php

require_once(__DIR__ . '/companyReportAdvance.php');

function fullFinalAmount($value)
{
    return number_format((float) $value, 2, '.', '');
}

function fullFinalDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00') {
        return '';
    }
    $time = strtotime(str_replace('/', '-', $value));
    return $time === false ? $value : date('d-m-Y', $time);
}

function fullFinalAmountInWords($value)
{
    $number = round((float) $value, 2);
    $rupees = (int) floor(abs($number));
    $paise = (int) round((abs($number) - $rupees) * 100);
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    $underThousand = function ($amount) use (&$ones, &$tens) {
        $words = array();
        if ($amount >= 100) {
            $words[] = $ones[(int) floor($amount / 100)] . ' Hundred';
            $amount %= 100;
        }
        if ($amount >= 20) {
            $words[] = $tens[(int) floor($amount / 10)] . ($amount % 10 ? ' ' . $ones[$amount % 10] : '');
        } elseif ($amount > 0) {
            $words[] = $ones[$amount];
        }
        return implode(' ', $words);
    };
    $parts = array();
    foreach (array(10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand') as $unit => $label) {
        if ($rupees >= $unit) {
            $portion = (int) floor($rupees / $unit);
            $parts[] = $underThousand($portion) . ' ' . $label;
            $rupees %= $unit;
        }
    }
    if ($rupees > 0) {
        $parts[] = $underThousand($rupees);
    }
    $words = $parts ? implode(' ', $parts) : 'Zero';
    $words = ($number < 0 ? 'Minus ' : '') . 'Rupees ' . $words;
    if ($paise > 0) {
        $words .= ' and ' . $underThousand($paise) . ' Paise';
    }
    return $words . ' Only';
}

function getFullFinalSettlement($dbconn, $employeeId, $companyId, $salaryMonth)
{
    $employeeId = (int) $employeeId;
    $companyId = (int) $companyId;
    if ($employeeId <= 0 || $companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('Please select an employee, company, month and year.');
    }
    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    $sql = "SELECT sd.*, e.employeeId, e.emp_name, e.employeecode, e.address,
                   e.strPermanentAddress, e.dateofjoining, e.strExitDate,
                   c.companyname
            FROM salarydetails sd
            INNER JOIN employee e ON e.employeeId=sd.emp_id
                AND e.isDelete='0' AND e.istatus='1'
            INNER JOIN companymaster c ON c.companymasterId=sd.companyId
                AND c.isDelete='0' AND c.istatus='1'
            WHERE sd.emp_id=" . $employeeId . " AND sd.companyId=" . $companyId . "
              AND sd.salaryId IN (SELECT salarymasterId FROM salarymaster
                  WHERE month='" . $month . "' AND isDelete='0' AND istatus='1')
              AND sd.isDelete='0' AND sd.istatus='1'
            ORDER BY sd.salarydetailsId DESC LIMIT 1";
    $result = mysqli_query($dbconn, $sql);
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve the settlement payroll details.');
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        throw new RuntimeException('No payroll data was found for this employee and month.');
    }

    $advances = getCompanyReportAdvances($dbconn, $companyId, $salaryMonth);
    $advance = getSalaryReportAdvance($row, $advances);
    $earnings = (float) $row['basicwages'] + (float) $row['totalovertime'] +
        (float) $row['iBonusAmt'] + (float) $row['iLeaveAmt'] + (float) $row['national_holiday_payment'];
    $deductions = (float) $row['pf'] + (float) $row['pt'] + (float) $advance;
    $net = getSalaryReportNetAmount($row, $advance);
    $period = DateTime::createFromFormat('!m/Y', $salaryMonth);
    return array(
        'employee_name' => $row['emp_name'],
        'employee_code' => $row['employeecode'],
        'employee_address' => trim((string) $row['strPermanentAddress']) !== '' ? $row['strPermanentAddress'] : $row['address'],
        'company_name' => $row['companyname'],
        'joining_date' => fullFinalDate($row['dateofjoining']),
        'exit_date' => fullFinalDate($row['strExitDate']),
        'period' => $period ? $period->format('M-Y') : $salaryMonth,
        'working_days' => $row['workingdays'],
        'basic' => $row['basicwages'],
        'overtime' => $row['totalovertime'],
        'bonus' => $row['iBonusAmt'],
        'leave' => $row['iLeaveAmt'],
        'national_holiday' => $row['national_holiday_payment'],
        'pf' => $row['pf'],
        'pt' => $row['pt'],
        'advance' => $advance,
        'total_earnings' => $earnings,
        'total_deductions' => $deductions,
        'rounding' => $net - ($earnings - $deductions),
        'net' => $net,
        'notice_pay' => 0,
        'other_bonus' => 0,
        'other_total' => 0
    );
}

function getFullFinalSettlements($dbconn, $employeeId, $companyId, $salaryMonth)
{
    $employeeId = (int) $employeeId;
    if ($employeeId > 0) {
        return array(getFullFinalSettlement($dbconn, $employeeId, $companyId, $salaryMonth));
    }
    $companyId = (int) $companyId;
    if ($companyId <= 0 || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)) {
        throw new InvalidArgumentException('Please select a company, month and year.');
    }
    $month = mysqli_real_escape_string($dbconn, $salaryMonth);
    $result = mysqli_query($dbconn, "SELECT DISTINCT sd.emp_id, e.emp_name FROM salarydetails sd INNER JOIN employee e ON e.employeeId=sd.emp_id AND e.isDelete='0' AND e.istatus='1' WHERE sd.companyId=" . $companyId . " AND sd.salaryId IN (SELECT salarymasterId FROM salarymaster WHERE month='" . $month . "' AND isDelete='0' AND istatus='1') AND sd.isDelete='0' AND sd.istatus='1' ORDER BY e.emp_name");
    if ($result === false) {
        throw new RuntimeException('Unable to retrieve employees for the selected payroll.');
    }
    $settlements = array();
    while ($employee = mysqli_fetch_assoc($result)) {
        $settlements[] = getFullFinalSettlement($dbconn, $employee['emp_id'], $companyId, $salaryMonth);
    }
    if (!$settlements) {
        throw new RuntimeException('No payroll data was found for this company and month.');
    }
    return $settlements;
}

function renderFullFinalSettlement(array $data)
{
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $a = function ($value) {
        return fullFinalAmount($value);
    };
    return '<!doctype html><html lang="gu"><head><meta charset="utf-8"><title>Full &amp; Final Settlement</title><style>' .
        '@page{size:A4;margin:10mm 12mm}*{box-sizing:border-box}body{margin:0;color:#111;font-family:freesans;font-size:12px;line-height:1.35}.page{width:100%;max-width:186mm;margin:auto;page-break-inside:avoid}.page-break{display:block;clear:both;height:0;page-break-before:always;mso-break-type:page-break}.title{text-align:center;font-size:20px;font-weight:bold;margin:0 0 7px}.info{width:100%;border-collapse:collapse;margin-bottom:5px}.info td{padding:2px}.label{width:25%}.value{font-weight:bold;text-decoration:underline}.blank-line{display:inline-block;width:130px;height:1em;border-bottom:1px solid #111;vertical-align:bottom}.date{margin:4px 0 7px}.date span.right{float:right;width:48%}.salary{text-align:center;margin:5px 0 8px}.box{border:1px solid #555;font-weight:bold;padding:3px 18px;margin:0 7px}table.main,table.other{width:100%;border-collapse:collapse;table-layout:fixed}.main td,.other td{border:1px solid #555;padding:4px 5px;height:24px}.header{text-align:center;font-weight:bold}.amount{text-align:right}.bold td,.net td{font-weight:bold}.other-wrap{margin-top:10px;font-weight:bold}.words{margin-top:9px;font-weight:bold;border-bottom:1px solid #333;padding-bottom:4px}.ack{margin-top:10px;font-size:11.5px;text-align:justify;line-height:1.55}.signs{width:100%;margin-top:12px}.signs td{width:50%;vertical-align:bottom;border:0}.witness-title{font-weight:bold}.worker{text-align:center;padding-top:42px;font-weight:bold}.bottom{margin-top:10px}.month{float:right}@media screen{body{background:#eee;padding:18px}.page{background:#fff;min-height:277mm;padding:10mm 12mm;box-shadow:0 0 5px #aaa}.page-break{height:18px}}@media print{.page{max-width:none}}' .
        '</style></head><body><div class="page"><div class="title">ફુલ એન્ડ ફાયનલ સેટલમેન્ટ</div>' .
        '<table class="info"><tr><td class="label">કોન્ટ્રકટરનું નામ :</td><td class="value">SHREE GANESH ENGINEERING CO.</td></tr>' .
        '<tr><td>કોન્ટ્રકટરનું સરનામું :</td><td class="value">FF-8 Devshruti Complex, Mithakhali, Ahmedabad – 380006.</td></tr>' .
        '<tr><td>કામદારનું નામ :</td><td class="value">' . $e($data['employee_name']) . ' (' . $e($data['employee_code']) . ')</td></tr>' .
        '<tr><td>કામદારનું સરનામું :</td><td class="value">' . $e($data['employee_address']) . '</td></tr></table>' .
        '<div class="date">દાખલ થયાની તારીખ : <span class="blank-line"></span><span class="right">છુટા થયાની તારીખ : <span class="blank-line"></span></span></div>' .
        '<div class="salary">પગાર નો માસ <span class="box">' . $e($data['period']) . '</span> દિવસો <span class="box">' . $e($data['working_days']) . '</span></div>' .
        '<table class="main"><tr><td class="header" width="32%">ચુકવણાનો પ્રકાર</td><td class="header" width="18%">રકમ રૂ.</td><td class="header" width="31%">કપાત રૂ.</td><td class="header" width="19%">રકમ રૂ.</td></tr>' .
        '<tr><td>બેઝીક</td><td class="amount">' . $a($data['basic']) . '</td><td>PF –</td><td class="amount">' . $a($data['pf']) . '</td></tr>' .
        '<tr><td>સ્પે. એલાઉન્સ (OT)</td><td class="amount">' . $a($data['overtime']) . '</td><td>PT –</td><td class="amount">' . $a($data['pt']) . '</td></tr>' .
        '<tr><td>અન્ય એલાઉન્સ Bonus</td><td class="amount">' . $a($data['bonus']) . '</td><td>Advance –</td><td class="amount">' . $a($data['advance']) . '</td></tr>' .
        '<tr><td>લીવ એન્કેશમેન્ટ</td><td class="amount">' . $a($data['leave']) . '</td><td></td><td></td></tr>' .
        '<tr><td>National Holiday</td><td class="amount">' . $a($data['national_holiday']) . '</td><td></td><td></td></tr>' .
        '<tr class="bold"><td>કુલ ચુકવણું</td><td class="amount">' . $a($data['total_earnings']) . '</td><td>કુલ કપાતો</td><td class="amount">' . $a($data['total_deductions']) . '</td></tr>' .
        '<tr class="bold"><td></td><td></td><td>R/O</td><td class="amount">' . ($data['rounding'] >= 0 ? '+' : '') . $a($data['rounding']) . '</td></tr>' .
        '<tr class="net"><td>ચોખ્ખી રકમ</td><td></td><td></td><td class="amount">' . $a($data['net']) . '</td></tr></table>' .
        '<div class="other-wrap">અન્ય ચુકવણા :-<table class="other"><tr><td class="header">નોટીસ પે</td><td class="header">બોનસ</td><td></td><td></td><td class="header">કુલ રકમ</td></tr><tr><td class="amount">' . $a($data['notice_pay']) . '</td><td class="amount">' . $a($data['other_bonus']) . '</td><td></td><td></td><td class="amount">' . $a($data['other_total']) . '</td></tr></table></div>' .
        '<div class="words">શબ્દોમાં રકમ રૂ. : ' . $e(fullFinalAmountInWords($data['net'])) . '</div><div class="words">કામદાર નું મુખત્યાર નામ :</div>' .
        '<div class="ack">ફુલ એન્ડ ફાયનલ સેટલમેન્ટ તરીકે મને રૂ. <u>' . $a($data['net']) . '</u> મળેલ છે. મારા નીકળતા લેણાની રકમની ગણતરી સાચી અને ખરી છે. હવે મારે પગાર તેમજ અન્ય કાયદેસરના હક્ક-હિસ્સાની કોઈ રકમ લેવાની નીકળતી નથી. મને ચૂકવવામાં આવેલ રકમથી મને સંપૂર્ણ સંતોષ છે, અને આ અંગે હું કોઈ જ વિવાદ ઉભો કરીશ નહીં.</div>' .
        '<table class="signs"><tr><td><span class="witness-title">સાક્ષીની સહી :</span> <span class="blank-line"></span><br><b>સાક્ષીનું નામ :</b> Kishor V Raval<br><b>સરનામું :</b> 4, Pavansut Society,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IOC Tragad Road, Tragad, Ahmedabad.</td><td class="worker">કામદારની સહી</td></tr></table>' .
        '<div class="bottom">તારીખ : ___________________________ <span class="month"></span><br>સ્થળ : <u>Ahmedabad</u></div></div></body></html>';
}

function renderFullFinalSettlements(array $settlements)
{
    $documents = array_map('renderFullFinalSettlement', $settlements);
    preg_match('/<head>(.*)<\/head>/sU', $documents[0], $head);
    $pages = '';
    foreach ($documents as $index => $document) {
        if (preg_match('/<body>(.*)<\/body>/sU', $document, $body)) {
            if ($index > 0) {
                // The explicit element is honoured by browsers, PDF renderers and
                // Microsoft Word's HTML importer, unlike sibling CSS alone.
                $pages .= '<br class="page-break" clear="all">';
            }
            $pages .= $body[1];
        }
    }
    return '<!doctype html><html lang="gu"><head>' . $head[1] . '</head><body>' . $pages . '</body></html>';
}
