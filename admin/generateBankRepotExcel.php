<?php

error_reporting(E_ALL);
ob_start();
ob_clean();
//require_once('tcpdf/config/tcpdf_config.php');
//require_once('tcpdf/tcpdf.php');
include('../config.php');

$HeaderCompany = "";
$companymasterId = "";
$month = '';
$comnymasid = array();

$i = 1;
$delimiter = ",";
$filename = "multicompanyreport" . date('Y-m-d H:i:s') . ".csv";
//create a file pointer

$f = fopen('php://memory', 'w');
if ($_REQUEST['Company'] != NULL && $_REQUEST['bank'] != NULL && $_REQUEST['salaryId'] != NULL) {
    $where = " and employee.bankid= '" . $_REQUEST['bank'] . "'";
    if ($_REQUEST['bank'] == 3) {
        $where = " and employee.bankid not in (1,2)";
        //$where = " and employee.bankid not in (2)";
    }
}
$query = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId  in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete=0 and employee.istatus=1";
//$query = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId='" . $_REQUEST['salaryId'] . "' and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete=0 and employee.istatus=1";
$filterstr = mysqli_query($dbconn, $query);
if (mysqli_num_rows($filterstr) > 0) {
    $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_REQUEST['Company'] . "'"));
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'"));
    $salaryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and salarymasterId='" . $_REQUEST['salaryId'] . "'"));
    if ($_REQUEST['bank'] == 3) {
        $bankname = 'Other';
    } else {
        $bankname = $bank['bankname'];
    }
    $fields = array(
        'SUB:',
        'PAYMENT SHEET',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        'SITE:',
        $comp['companyname'],
        '',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);

    $fields = array(
        $bankname,
        $_REQUEST['salaryId'],
        ' Bank Payment',
        '',
        '',
        'Date',
        date('d-m-Y'));
    fputcsv($f, $fields, $delimiter);

    if($_REQUEST['bank'] == 3){
        $fields = array(
            'Sr. No',
            'Name',
            'Balance',
            'IFSC Code',
            'Bank Name',
            'Bank A/C No',
        );    
    } else {
        $fields = array(
            'Sr. No',
            'Name',
            'Balance',
            'IFSC Code',
            'Bank A/C No',
        );
    }
    
    fputcsv($f, $fields, $delimiter);

    $Total = array("Total", "", "0.00", "", "", "");
    $Total[0] = "Total";
    $i = 1;
    while ($row = mysqli_fetch_array($filterstr)) {
        $bankname = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $row['bankid'] . "'"));
        if($_REQUEST['bank'] == 3){
            $lineData = array(
                $i,
                ucwords(strtolower($row['emp_name'])),
                number_format($row['netamountpaid'],2),
                $row['ifsccode'],
                $bankname['bankname'],
                str_replace('A/C. ','',$row['accountno'])
            );
        } else {
            $lineData = array(
                $i,
                ucwords(strtolower($row['emp_name'])),
                number_format($row['netamountpaid'],2),
                $row['ifsccode'],
                str_replace('A/C. ','',$row['accountno'])
            );
        }
        

        $Total[2] += $row['netamountpaid'];
        fputcsv($f, $lineData, $delimiter);
        $i++;
    }
    //$Total = number_format($Total,2);
    fputcsv($f, $Total, $delimiter);

    $fields = array(
        '',
        '',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);

    $fields = array(
        '',
        '',
        '',
        '',
        '',
        'FOR, SHREE GANESH ENGINEERING CO.'
    );
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        'Cheque No.',
        '',
        '',
        '',
        '',
        'HITESH.K.SHAH (PARTNER)');
    fputcsv($f, $fields, $delimiter);

//move back to beginning of file
    fseek($f, 0);

    header('Content-Type: text/csv');
    $filename = "companyBankReport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>