<?php

//include database configuration file
include('../config.php');
include('IsLogin.php');
//$connect = new connect();
//get records from database



//$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));
$query = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1')  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc"));

$comid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT companyname FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
$MONTH = mysqli_fetch_array(mysqli_query($dbconn, "SELECT month FROM salarymaster where salarymasterId = '" . $query['salaryId'] . "'"));

//$query1 = mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "'  and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc");
$query1 = mysqli_query($dbconn, "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1'and workingdays > 0 order by salarydetailsId asc");
if (mysqli_num_rows($query1) > 0) {
    $delimiter = ",";
    $filename = "companyreport" . date('Y-m-d H:i:s') . ".csv";
    //create a file pointer

    $f = fopen('php://memory', 'w');

    $fields = array(
        'SUB:',
        'PAYMENT SHEET',
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
        'SITE:',
        $comid['companyname'],
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
        'MONTH',
        $MONTH['month'],
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'Date',
        date('d-m-Y'));
    fputcsv($f, $fields, $delimiter);

    //set column headers
    $fields = array(
        'Name of workman',
        'PF No.',
        'Category',
        'Serial of in the register of workman',
        'Designation Nature of work done',
        'No of Days Worked',
        'Rate of Daily work done',
        'OT Hours',
        'Price Rate',
        'Amount of Wages',
        'OT Amount',
        'MedicalAllowanceamt',
        'Total',
        'E.S.I.',
        'P.F.',
        'Professional Tax',
        'Deductionifany',
        'Net Amount Paid',
        'Signature Thumb impression of Workman',
        'Initials of Contractor of his_Representive',
        'Bank Account No'
    );
    fputcsv($f, $fields, $delimiter);
    //output each row of the data, format line as csv and write to file pointer

    $Total = array("Total", "", "", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", '0',"0","0","0");
    $Total[0] = "Total";
    $comskill = mysqli_fetch_array(mysqli_query($dbconn, "SELECT unskill,semiskill,skil FROM companymaster where companymasterId = '" . $_REQUEST['Company'] . "'"));
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

        $lineData = array(
            ucwords(strtolower($desg['emp_name'])),
            $desg['pfcode'],
            $Category,
            '',
            $desg['designation'],
            $row['workingdays'],
            $row['skillrate'],
            $row['othours'],
            '',
            $row['basicwages'],
            $row['totalovertime'],
            $row['MedicalAllowanceamt'],
            $row['total'],
            $row['esi'],
            $row['pf'],
            $row['pt'],
            $row['deductionifany'],
            $row['netamountpaid'],
            '',
            '',
            $desg['accountno']
        );
        
        
        $Total[5] += $row['workingdays'];
        $Total[6] += $row['skillrate'];
        $Total[9] += $row['basicwages'];
        $Total[10] += $row['totalovertime'];
        $Total[11] += $row['MedicalAllowanceamt'];
        $Total[12] += $row['total'];
        $Total[13] += $row['esi'];
        $Total[14] += $row['pf'];
        $Total[15] += $row['pt'];
        $Total[16] += $row['deductionifany'];
        $Total[17] += $row['netamountpaid'];
        fputcsv($f, $lineData, $delimiter);
    }
    fputcsv($f, $Total, $delimiter);
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