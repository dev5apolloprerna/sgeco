<?php

//include database configuration file
include('../config.php');
include('IsLogin.php');
include_once 'companyReportAdvance.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//$connect = new connect();
//get records from database

// Return a real number. The workbook applies an Excel number format later, so
// amounts retain two decimal places without becoming text-only formula values.
function formatCompanyReportExcelAmount($amount)
{
    return (float)$amount;
}


//$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));
$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1')  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));

$comid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT companyname,skil,unskill,semiskill,highlyskilled FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
$MONTH = mysqli_fetch_array(mysqli_query($dbconn, "SELECT month,fromdate,todate FROM salarymaster where salarymasterId = '" . $query['salaryId'] . "'"));

$month =  str_replace('/', '-', $_REQUEST['salarymasterId']);
$wageMonth = date('F-y', strtotime("01-" . $month));

//$query1 = mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc");
$query1 = mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc");
$companyReportAdvances = getCompanyReportAdvances($dbconn, $_REQUEST['Company'], $_REQUEST['salarymasterId']);
if (mysqli_num_rows($query1) > 0) {
    $lineOne = "";
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";

    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "Rate of Minimum Wages and since the Date"
        . "\t" . ""
        . "\t" . "";
    if ($comid['highlyskilled'] != "") {
        $lineOne .= "\t" . "";
    }
    $lineOne .=  "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "REGISTER OF WAGES"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "Name & Address of Establishment in/under which Contract is carried on"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $text = "";
    $value = "";
    if ($comid['highlyskilled'] != "") {
        $text = "Highly Skilled";
        $value = $comid['highlyskilled'];
    }
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "";
    if ($comid['highlyskilled'] != "") {
        $lineOne .= "\t" . "Highly Skilled";
    }
    $lineOne .= "\t" . "Skilled"
        . "\t" . "Semi-Skilled"
        . "\t" . "Un Skilled"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "FORM XVII"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";

    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "Minimum Basic";
    if ($comid['highlyskilled'] != "") {
        $lineOne .= "\t" . $comid['highlyskilled'];
    }
    $lineOne .= "\t" . $comid['skil']
        . "\t" . $comid['semiskill']
        . "\t" . $comid['unskill']
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "[ See rule 78(2) (a) ]"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        // . "\t" . ""
        . "\t" . "Nature and Location of Work:"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "NAME & ADDRESS OF CONTRACTOR :"
        . "\t" . "SHREE GANESH ENGINEERING CO."
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "FF-8, Devshruti Complex,"
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "Mithakhali , Ahmedabad - 380006,"
        . "\t" . "";
    if ($comid['highlyskilled'] != "") {
        $lineOne .= "\t" . "";
    }
    $lineOne .= "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . "Month: " . $wageMonth
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        // . "\t" . ""
        . "\t" . "Name & Address of the Principal Employer : " . ucwords(strtolower($comid['companyname']))
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";

    $lineOne .= ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\n";
    $lineOne .= ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . 'Amount of wages earned'
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . 'Deducation'
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\t" . ''
        . "\n";
    $lineOne .= 'Sr. No'
        . "\t" . 'Sr. No. in Emp. Reg.'
        . "\t" . 'Name of Workmen'
        . "\t" . 'Designation/Nature of Work Done'
        . "\t" . 'No. of Days worked'
        . "\t" . 'Daily rate of wages/ piece Rate'
        . "\t" . 'OT hours  worked'
        . "\t" . 'Total Basic wages'
        . "\t" . 'DA'
        . "\t" . 'HRA'
        . "\t" . 'Payments Overtime'
        . "\t" . 'Bonus'
        . "\t" . 'Leave'
        . "\t" . 'National Holidays'
        . "\t" . 'Gross Total'
        . "\t" . 'PF'
        . "\t" . 'ESIC'
        . "\t" . 'Advance'
        . "\t" . 'Professional Tax'
        . "\t" . 'Society'
        . "\t" . 'Income Tax'
        . "\t" . 'Insurance'
        . "\t" . 'Recoveries'
        . "\t" . 'Total Deduction'
        . "\t" . 'Net Payment'
        . "\t" . 'Employer Share/ PF Welfare Fund'
        . "\t" . 'Signature /thumb impresssion of workman/Transaction ID'
        . "\t" . 'Date of Payment'
        . "\n";
    $Total = array_fill(0, 28, 0);
    $Total[0] = "Total";
    $deductiontotal = array(0, 0, 0, 0);
    $comskill = mysqli_fetch_array(mysqli_query($dbconn, "SELECT unskill,semiskill,skil FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
    $iCounter = 1;
    $data = "";
    while ($row = mysqli_fetch_assoc($query1)) {
        $Category = "";
        if ($comskill['unskill'] === $row['skillrate']) {
            $Category = 'Unskill';
        } else if ($comskill['semiskill'] === $row['skillrate']) {
            $Category = 'Semi skill';
        } else if ($comskill['skil'] === $row['skillrate']) {
            $Category = 'Skill';
        }
        // and  istatus='1'
        $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0' and employeeId='" . $row['emp_id'] . "'"));
        // $advanceAmount = getEmployeeCompanyReportAdvance($companyReportAdvances, $row['emp_id']);
        $advanceAmount = getSalaryReportAdvance($row, $companyReportAdvances);
        $Deduction_total = $row['pf'] + $row['esi'] + $advanceAmount + $row['pt'];
        // $netAmountPaid = ceil($row['netamountpaid'] - $advanceAmount);
        $netAmountPaid = ceil(getSalaryReportNetAmount($row, $advanceAmount));
        $emp_name = "";
        if (isset($desg['emp_name']) && $desg['emp_name'] != "") {
            $emp_name = ucwords(strtolower($desg['emp_name']));
        } else {
            $emp_name = "";
        }
        $designation = "";
        if (isset($desg['designation']) && $desg['designation'] != "") {
            $designation = ucwords(strtolower($desg['designation']));
        }
        $othours = ($row['othours'] != '0.00') ? formatCompanyReportExcelAmount($row['othours']) : ''; // $othours = ($row['othours'] != '0.00') ? $row['othours'] : '';
        $da = ($row['da'] != '0.00') ? $row['da'] : '';
        $hra = ($row['hra'] != '0.00') ? $row['hra'] : '';
        $iBonusAmt = ($row['iBonusAmt'] != '0.00') ? $row['iBonusAmt'] : '';
        $iLeaveAmt = ($row['iLeaveAmt'] != '0.00') ? $row['iLeaveAmt'] : '';
        $national_holiday_payment = ($row['national_holiday_payment'] != '0.00') ? $row['national_holiday_payment'] : '';
        $pt = ($row['pt'] != '0.00') ? $row['pt'] : '';
        $data .= $iCounter
            . "\t" . ''
            . "\t" . $emp_name
            . "\t" . $designation
            . "\t" . $row['workingdays']
            . "\t" . formatCompanyReportExcelAmount($row['skillrate']) // . "\t" . $row['skillrate']
            . "\t" . $othours
            . "\t" . formatCompanyReportExcelAmount($row['basicwages']) // . "\t" . $row['basicwages']
            // '',
            . "\t" . $da
            . "\t" . $hra
            . "\t" . round($row['totalovertime'])
            . "\t" . $iBonusAmt
            . "\t" . $iLeaveAmt
            . "\t" . $national_holiday_payment
            . "\t" . number_format(round($row['total']), 2, '.', '')
            . "\t" . number_format($row['pf'], 2, '.', '')
            . "\t" . number_format($row['esi'], 2, '.', '')
            . "\t" . number_format($advanceAmount, 2, '.', '')
            . "\t" . $pt
            . "\t" . ''
            . "\t" . ''
            . "\t" . ''
            . "\t" . ''
            . "\t" . $Deduction_total
            . "\t" . $netAmountPaid
            . "\t" . number_format($row['pf'], 2, '.', '')
            . "\t" . ''
            //. "\t" . ''
            . "\t" . $row['strPaymentDate']
            . "\n";
        $Total[4] += $row['workingdays'];
        $Total[5] += $row['skillrate'];
        $Total[6] += (float)$row['othours']; // $Total[6] += (int)$row['othours'];
        $Total[7] += $row['basicwages'];
        $Total[10] += (int)$row['totalovertime'];
        $Total[14] += $row['total'];
        $Total[16] += $row['esi'];
        $Total[15] += $row['pf'];
        $Total[17] += $advanceAmount;
        $Total[18] += (int)$row['pt'];
        $Total[23] += $Deduction_total;
        //$Total[16] += $row['deductionifany'];
        $Total[24] += $netAmountPaid;
        $Total[25] += ceil($row['iBonusAmt']);
        $Total[12] += ceil($row['iLeaveAmt']);
        $Total[9] += $row['da'];
        $Total[11] += $row['hra'];
        $Total[13] += $row['national_holiday_payment'];

        $iCounter++;
    }
    $Total[14] = number_format(round($Total[14]), 2, '.', '');
    $Total[24] = number_format($Total[24], 2, '.', '');
    $lastLine = ""
        . "\t" . ""
        . "\t" . "Total"
        . "\t" . ""
        . "\t" . $Total[4]
        . "\t" . ""
        . "\t" . formatCompanyReportExcelAmount($Total[6]) // . "\t" . ""
        . "\t" . formatCompanyReportExcelAmount($Total[7]) // . "\t" . $Total[7]
        . "\t" . $Total[9]
        . "\t" . $Total[11]
        . "\t" . round($Total[10])
        . "\t" . $Total[25]
        . "\t" . $Total[12]
        . "\t" . $Total[13]
        . "\t" . number_format(round($Total[14]), 2, '.', '')
        . "\t" . number_format($Total[15], 2, '.', '')
        . "\t" . number_format($Total[16], 2, '.', '')
        . "\t" . number_format($Total[17], 2, '.', '')
        . "\t" . $Total[18]
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . $Total[23]
        . "\t" . number_format($Total[24], 2, '.', '')
        . "\t" . number_format($Total[15], 2, '.', '')
        . "\t" . ""
        . "\t" . ""
        . "\n";

    $reportRows = array();
    $reportText = rtrim($lineOne . $data . $lastLine, "\r\n");
    foreach (preg_split('/\r\n|\r|\n/', $reportText) as $reportLine) {
        $reportRows[] = explode("\t", $reportLine);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Company Report');
    $sheet->fromArray($reportRows, null, 'A1', true);
    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

    $lastRow = count($reportRows);
    $lastColumnNumber = 28;
    $lastColumn = Coordinate::stringFromColumnIndex($lastColumnNumber);
    $headerRow = 1;
    foreach ($reportRows as $rowIndex => $reportRow) {
        if (isset($reportRow[0]) && trim($reportRow[0]) === 'Sr. No') {
            $headerRow = $rowIndex + 1;
            break;
        }
    }

    // Rebuild the statutory heading with merged sections. The legacy tab-separated
    // export positioned each phrase in a single narrow cell, which caused Excel to
    // wrap the heading one word per line in a real workbook.
    for ($row = 1; $row < $headerRow - 1; $row++) {
        for ($column = 1; $column <= $lastColumnNumber; $column++) {
            $sheet->getCellByColumnAndRow($column, $row)->setValue(null);
        }
    }

    $minimumWages = array();
    if ($comid['highlyskilled'] !== '') {
        $minimumWages[] = array('Highly Skilled', $comid['highlyskilled']);
    }
    $minimumWages[] = array('Skilled', $comid['skil']);
    $minimumWages[] = array('Semi-Skilled', $comid['semiskill']);
    $minimumWages[] = array('Un Skilled', $comid['unskill']);

    $sheet->mergeCells('D3:H3');
    $sheet->setCellValue('D3', 'Rate of Minimum Wages and since the Date');
    $sheet->mergeCells('D4:D5');
    $sheet->setCellValue('D4', 'Minimum Basic');
    foreach ($minimumWages as $wageIndex => $minimumWage) {
        $wageColumn = Coordinate::stringFromColumnIndex(5 + $wageIndex);
        $sheet->setCellValue($wageColumn . '4', $minimumWage[0]);
        $sheet->setCellValue($wageColumn . '5', (float)$minimumWage[1]);
    }

    $sheet->mergeCells('L3:N3');
    $sheet->setCellValue('L3', 'REGISTER OF WAGES');
    $sheet->mergeCells('L4:N4');
    $sheet->setCellValue('L4', 'FORM XVII');
    $sheet->mergeCells('L5:N5');
    $sheet->setCellValue('L5', '[ See rule 78(2) (a) ]');

    $sheet->mergeCells('P3:V4');
    $sheet->setCellValue('P3', 'Name & Address of Establishment in/under which Contract is carried on');
    $sheet->mergeCells('P5:V5');
    $sheet->setCellValue('P5', 'Nature and Location of Work:');

    $sheet->mergeCells('D7:D9');
    $sheet->setCellValue('D7', 'NAME & ADDRESS OF CONTRACTOR:');
    $sheet->mergeCells('E7:H7');
    $sheet->setCellValue('E7', 'SHREE GANESH ENGINEERING CO.');
    $sheet->mergeCells('E8:H8');
    $sheet->setCellValue('E8', 'FF-8, Devshruti Complex,');
    $sheet->mergeCells('E9:H9');
    $sheet->setCellValue('E9', 'Mithakhali, Ahmedabad - 380006');
    $sheet->mergeCells('L9:N9');
    $sheet->setCellValue('L9', 'Month: ' . $wageMonth);
    $sheet->mergeCells('P9:V9');
    $sheet->setCellValue('P9', 'Name & Address of the Principal Employer: ' . ucwords(strtolower($comid['companyname'])));

    $sectionRange = 'D3:V9';
    $sheet->getStyle($sectionRange)->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle('D3:H3')->getFont()->setBold(true);
    $sheet->getStyle('L3:N4')->getFont()->setBold(true);
    $sheet->getStyle('D7:D9')->getFont()->setBold(true);
    $sheet->getStyle('P3:V5')->getFont()->setBold(true);
    $sheet->getStyle('D3:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('L3:N5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D7:D9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    // Keep the report-information blocks borderless, matching the Register of
    // Bonus workbook while allowing the statutory table below to remain boxed.
    $sheet->getRowDimension(3)->setRowHeight(24);
    $sheet->getRowDimension(4)->setRowHeight(24);
    $sheet->getRowDimension(5)->setRowHeight(24);
    $sheet->getRowDimension(7)->setRowHeight(24);
    $sheet->getRowDimension(8)->setRowHeight(22);
    $sheet->getRowDimension(9)->setRowHeight(28);

    // Draw a clear, printable table instead of relying on Excel's worksheet gridlines.
    $groupHeaderRow = $headerRow - 1;
    $sheet->mergeCells('H' . $groupHeaderRow . ':O' . $groupHeaderRow);
    $sheet->setCellValue('H' . $groupHeaderRow, 'Amount of Wages Earned');
    $sheet->mergeCells('P' . $groupHeaderRow . ':X' . $groupHeaderRow);
    $sheet->setCellValue('P' . $groupHeaderRow, 'Deductions');
    $tableRange = 'A' . ($headerRow - 1) . ':' . $lastColumn . $lastRow;
    $sheet->getStyle($tableRange)->applyFromArray(array(
        'borders' => array(
            'allBorders' => array(
                'borderStyle' => Border::BORDER_THIN,
                'color' => array('rgb' => '000000'),
            ),
        ),
        'alignment' => array(
            'vertical' => Alignment::VERTICAL_CENTER,
        ),
    ));
    $sheet->getStyle('A' . $groupHeaderRow . ':' . $lastColumn . $groupHeaderRow)->applyFromArray(array(
        'font' => array('bold' => true),
        'alignment' => array(
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ),
    ));
    $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->applyFromArray(array(
        'font' => array('bold' => true),
        'alignment' => array(
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ),
    ));
    $sheet->getStyle('A' . $lastRow . ':' . $lastColumn . $lastRow)->getFont()->setBold(true);
    $sheet->getStyle('A1:' . $lastColumn . ($headerRow - 1))->getAlignment()->setWrapText(true);

    // Preserve payroll values as numbers and display them consistently. This also
    // keeps totals usable for filtering and calculations in the downloaded file.
    $sheet->getStyle('F' . ($headerRow + 1) . ':Z' . $lastRow)
        ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    $sheet->getStyle('A' . ($headerRow + 1) . ':' . $lastColumn . $lastRow)
        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    // Keep identifier and normally-empty deduction columns compact so the useful
    // employee and wage details remain readable without excessive horizontal space.
    $columnWidths = array(
        'A' => 6, 'B' => 8, 'C' => 23, 'D' => 18, 'E' => 9, 'F' => 11,
        'G' => 9, 'H' => 12, 'I' => 8, 'J' => 8, 'K' => 11, 'L' => 9,
        'M' => 9, 'N' => 9, 'O' => 12, 'P' => 10, 'Q' => 10, 'R' => 10,
        'S' => 10, 'T' => 8, 'U' => 8, 'V' => 8, 'W' => 8, 'X' => 11,
        'Y' => 12, 'Z' => 13, 'AA' => 22, 'AB' => 12,
    );
    foreach ($columnWidths as $columnLetter => $columnWidth) {
        $sheet->getColumnDimension($columnLetter)->setWidth($columnWidth);
    }
    $sheet->getRowDimension($headerRow)->setRowHeight(62);
    $sheet->getRowDimension($groupHeaderRow)->setRowHeight(22);
    $sheet->freezePane('A' . ($headerRow + 1));

    $sheet->setShowGridlines(false);
    $sheet->getSheetView()->setZoomScale(85);
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
    $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($groupHeaderRow, $headerRow);
    $sheet->getPageMargins()->setTop(0.3)->setRight(0.3)->setBottom(0.3)->setLeft(0.3);
    $sheet->getPageSetup()->setHorizontalCentered(true);
    $sheet->getPageSetup()->setPrintArea('A1:' . $lastColumn . $lastRow);

    $filename = 'companyreport_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    (new Xlsx($spreadsheet))->save('php://output');
    $spreadsheet->disconnectWorksheets();
    exit;
} else {
    header('location: Report.php');
}
