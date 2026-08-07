<?php

//include database configuration file
include('../config.php');
include('IsLogin.php');
//$connect = new connect();
//get records from database



//$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));
$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1')  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));

$comid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT companyname,skil,unskill,semiskill,highlyskilled FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
$MONTH = mysqli_fetch_array(mysqli_query($dbconn, "SELECT month,fromdate,todate FROM salarymaster where salarymasterId = '" . $query['salaryId'] . "'"));

//$query1 = mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc");
$query1 = mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc");
if (mysqli_num_rows($query1) > 0) {
    $delimiter = ",";
    $filename = "companyreport" . date('Y-m-d H:i:s') . ".csv";
    //create a file pointer

    $f = fopen('php://memory', 'w');
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        'Rate of Minimum Wages and since the Date '.$MONTH['month'].'',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'REGISTER OF WAGES',
        '',
        '',
        'Name & Address of Establishment in/under which Contract is carried on'
        );
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        'Skilled',
        'Semi-Skilled',
        'Un Skilled',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'FORM XVII');
    fputcsv($f, $fields, $delimiter);
    
    $fields = array(
        '',
        'Minimum Basic',
        $comid['skil'],
        $comid['semiskill'],
        $comid['unskill'],
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '[ See rule 78(2) (a) ]',
        '',
        '',
        'Nature and Location of Work:');
    
    fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     'FORMAT FOR WAGE REGISTER');
    // fputcsv($f, $fields, $delimiter);

    // $fields = array(
    //     '',
    //     '',
    //     '',
    //     'Latest Minimum Wages',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    // fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     '',
    //     '',
    //     'Rate of Minimum Wages and since the Date '.$MONTH['month'].'',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    //fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     '',
    //     'Skilled',
    //     'Semi-Skilled',
    //     'Un Skilled',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    // fputcsv($f, $fields, $delimiter);

    // $fields = array(
    //     '',
    //     'Minimum Basic',
    //     $comid['skil'],
    //     $comid['semiskill'],
    //     $comid['unskill'],
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    // fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     'DA',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    // fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        'NAME & ADDRESS OF CONTRACTOR :',
        'SHREE GANESH ENGINEERING CO.',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     'Name of Owner :',
    //     //$comid['companyname'],
    //     'Mr. Hitesh K Shah',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    // fputcsv($f, $fields, $delimiter);

    // $fields = array(
    //     '',
    //     'LIN',
    //     //$MONTH['month'],
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '  Name & Address of the principal Employers :',
    //     $comid['companyname']);
    // fputcsv($f, $fields, $delimiter);

    $fields = array(
        '',
        '',
        'FF-8, Devshruti Complex,',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        'Mithakhali , Ahmedabad - 380006',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'Month: March-23',
        '',
        '',
        'Name & Address of the Principal Employer : GSFC Ltd., Vadodara',
        '');
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);

    //set column headers
    $fields = array(
        'Sr. No',
        'Sr. No. in Emp. Reg.',
        'Name of Workmen',
        'Designation/Nature of Work Done',
        'No. of Days worked',
        'Daily rate of wages/ piece Rate',
        'OT hours  worked',
        'Total Basic wages',
        'DA',
        'HRA',
        'Payments Overtime',
        'Bonus',
        'Leave',
        'National Holidays',
        'Gross Total',
        'PF',
        'ESIC',
        'Society',
        'Income Tax',
        'Professional Tax',
        'Insurance',
        'Recoveries',
        'Total Deduction',
        'Net Payment',
        'Employer Share/ PF Welfare Fund',
        'Signature /thumb impresssion of workman/Transaction ID',
        'Date of Payment'
    );
    fputcsv($f, $fields, $delimiter);
    
    $Total = array("Total", "", "","","0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", '0',"0","0","0","0","0","0","0","0","0",0,0);
    $Total[0] = "Total";
    $deductiontotal = array(0, 0, 0, 0);
    $comskill = mysqli_fetch_array(mysqli_query($dbconn, "SELECT unskill,semiskill,skil FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
    $iCounter = 1;
    while ($row = mysqli_fetch_assoc($query1)) {
        $Category = "";
        if ($comskill['unskill'] === $row['skillrate']) {
            $Category = 'Unskill';
        } else if ($comskill['semiskill'] === $row['skillrate']) {
            $Category = 'Semi skill';
        } else if ($comskill['skil'] === $row['skillrate']) {
            $Category = 'Skill';
        }
        $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $row['emp_id'] . "'"));
        $Deduction_total = $row['pf'] + $row['esi'] + $row['pt'];
        $emp_name="";
        if(isset($desg['emp_name']) && $desg['emp_name'] != ""){
            $emp_name = $desg['emp_name'];
        } else {
            $emp_name = "";
        }
        $designation = "";
        if(isset($desg['designation']) && $desg['designation'] !=""){
            $designation = $desg['designation'];
        }

        $lineData = array(
            $iCounter,
            '',
            $emp_name,
            $designation,
            $row['workingdays'],
            $row['skillrate'],
            ($row['othours'] != '0.00') ? $row['othours'] : '',
            $row['basicwages'],
            // '',
            ($row['da'] != '0.00') ? $row['da'] : '',
            ($row['hra'] != '0.00') ? $row['hra'] : '',
            $row['totalovertime'],
            ($row['iBonusAmt'] != '0.00') ? $row['iBonusAmt'] : '',
            ($row['iLeaveAmt'] != '0.00') ? $row['iLeaveAmt'] : '',
            ($row['national_holiday_payment'] != '0.00') ? $row['national_holiday_payment'] : '',
            number_format(round($row['total']),2),
            $row['pf'],
            $row['esi'],
            '',
            '',
            ($row['pt'] != '0.00') ? $row['pt'] : '',
            '',
            '',
            $Deduction_total,
            ceil($row['netamountpaid']),
            '',
            '',
            '',
            '',
        );
        
        
        $Total[4] += $row['workingdays'];
        $Total[5] += $row['skillrate'];
        $Total[6] += (int)$row['othours'];
        $Total[7] += $row['basicwages'];
        $Total[10] += (int)$row['totalovertime'];
        $Total[14] += $row['total'];
        $Total[16] += $row['esi'];
        $Total[15] += $row['pf'];
        $Total[19] += (int)$row['pt'];
        $Total[22] += $Deduction_total;
        //$Total[16] += $row['deductionifany'];
        $Total[23] += ceil($row['netamountpaid']);
        $Total[11] += ceil($row['iBonusAmt']);
        $Total[12] += ceil($row['iLeaveAmt']);
        $Total[9] += $row['da'];
        $Total[11] += $row['hra'];
        $Total[13] += $row['national_holiday_payment'];
        fputcsv($f, $lineData, $delimiter);
        $iCounter++;
    }
    $Total[14] = number_format(round($Total[14]),2);
    $Total[23] = number_format($Total[23],2);
    fputcsv($f, $Total, $delimiter);
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '');
    fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     '* In case of Mines Act any Leave Wages paid should be shown in the Others Column and specifically mentioned in the Remarks column also.',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '');
    // fputcsv($f, $fields, $delimiter);

    //move back to beginning of file
    fseek($f, 0);

    //set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    fpassthru($f);
} else {
    header('location: Report.php');
}
//output all remaining data on a file pointer


exit;
?>