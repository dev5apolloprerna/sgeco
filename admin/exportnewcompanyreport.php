<?php

//include database configuration file
include('../config.php');
include('IsLogin.php');
include_once 'companyReportAdvance.php';
//$connect = new connect();
//get records from database



//$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));
$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1')  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));

$comid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT companyname,skil,unskill,semiskill,highlyskilled FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
$MONTH = mysqli_fetch_array(mysqli_query($dbconn, "SELECT month,fromdate,todate FROM salarymaster where salarymasterId = '" . $query['salaryId'] . "'"));

$month =  str_replace('/','-',$_REQUEST['salarymasterId']);
$wageMonth = date('F-y',strtotime("01-".$month));

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
    if($comid['highlyskilled'] != ""){
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
    if($comid['highlyskilled'] != ""){
        $text = "Highly Skilled";
        $value = $comid['highlyskilled'];
    } 
$lineOne .= ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . "";
    if($comid['highlyskilled'] != ""){
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
    if($comid['highlyskilled'] != ""){
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
    if($comid['highlyskilled'] != ""){
        $lineOne .= "\t" . "";
    }
    $lineOne .= "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . "Month: " .$wageMonth
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
$lineOne .=''
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
$lineOne .='Sr. No'
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
        $advanceAmount = getEmployeeCompanyReportAdvance($companyReportAdvances, $row['emp_id']);
        $Deduction_total = $row['pf'] + $row['esi'] + $advanceAmount + $row['pt'];
        $netAmountPaid = ceil($row['netamountpaid'] - $advanceAmount);
        $emp_name="";
        if(isset($desg['emp_name']) && $desg['emp_name'] != ""){
            $emp_name = ucwords(strtolower($desg['emp_name']));
        } else {
            $emp_name = "";
        }
        $designation = "";
        if(isset($desg['designation']) && $desg['designation'] !=""){
            $designation = ucwords(strtolower($desg['designation']));
        }
        $othours = ($row['othours'] != '0.00') ? $row['othours'] : '';
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
            . "\t" . $row['skillrate']
            . "\t" . $othours
            . "\t" . $row['basicwages']
            // '',
            . "\t" . $da
            . "\t" . $hra
            . "\t" . round($row['totalovertime'])
            . "\t" . $iBonusAmt
            . "\t" . $iLeaveAmt
            . "\t" . $national_holiday_payment
            . "\t" . number_format(round($row['total']), 2, '.', '')
            . "\t" . number_format($row['pf'],2,'.','')
            . "\t" . number_format($row['esi'],2,'.','')
            . "\t" . number_format($advanceAmount,2,'.','')
            . "\t" . $pt
            . "\t" . ''
            . "\t" . ''
            . "\t" . ''
            . "\t" . ''
            . "\t" . $Deduction_total
            . "\t" . $netAmountPaid
            . "\t" . number_format($row['pf'],2,'.','')
            . "\t" . ''
            //. "\t" . ''
            . "\t" . $row['strPaymentDate']
            . "\n";
        $Total[4] += $row['workingdays'];
        $Total[5] += $row['skillrate'];
        $Total[6] += (int)$row['othours'];
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
    $Total[14] = number_format(round($Total[14]),2,'.','');
    $Total[24] = number_format($Total[24],2,'.','');
    $lastLine = ""
    . "\t" . ""
    . "\t" . "Total"
    . "\t" . ""
    . "\t" . $Total[4]
    . "\t" . ""
    . "\t" . ""
    . "\t" . $Total[7]
    . "\t" . $Total[9]
    . "\t" . $Total[11]
    . "\t" . round($Total[10])
    . "\t" . $Total[25]
    . "\t" . $Total[12]
    . "\t" . $Total[13]
    . "\t" . number_format(round($Total[14]),2,'.','')
    . "\t" . number_format($Total[15],2,'.','')
    . "\t" . number_format($Total[16],2,'.','')
    . "\t" . number_format($Total[17],2,'.','')
    . "\t" . $Total[18]
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . $Total[23]
    . "\t" . number_format($Total[24],2,'.','')
    . "\t" . number_format($Total[15],2,'.','')
    . "\t" . ""
    . "\t" . ""
    . "\n";
    
$filename = 'companyreport' . date('Y-m-d H:i:s') . '.xls';
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-disposition: attachment; filename=" . $filename);
ob_end_clean();
echo chr(255) . chr(254) .mb_convert_encoding($lineOne, 'UTF-16LE', 'UTF-8');
echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
echo chr(255) . chr(254) .mb_convert_encoding($lastLine, 'UTF-16LE', 'UTF-8');
}else {
    header('location: Report.php');
} ?>