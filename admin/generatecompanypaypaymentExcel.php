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
$where ="";
if($_REQUEST['bank'] != ""){
    $where = " and employee.bankid= '" . $_REQUEST['bank'] . "'";
    if ($_REQUEST['bank'] == 3) {
        $where = " and employee.bankid not in (1,2)";
    }
}
$filterstr = "SELECT salarydetails.salarydetailsId,salarydetails.salaryId,emp_name,accountno,
        (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=employee.bankid) as BankName,
        netamountpaid,emp_other_info,ifsccode FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and iPaymentStatus=0 and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
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
        'COMPANY PAY PAYMENT',
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
        '',
        '',
        '',
        'Date',
        date('d-m-Y'));
    fputcsv($f, $fields, $delimiter);
    
    if ($_REQUEST['bank'] == 3) {
        $fields = array(
            'Sr. No',
            'Name',
            'Balance',
            'Bank Name',
            'IFSC Code',
            'Bank A/C No'
        );
        fputcsv($f, $fields, $delimiter);
    } else {
        $fields = array(
            'Sr. No',
            'Name',
            'Balance',
            'IFSC Code',
            'Bank A/C No'
        );
        fputcsv($f, $fields, $delimiter);
    }

    $Total = array("Total", "", "0", "", "", "");
    $Total[0] = "Total";
    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        //$bankname = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $row['bankid'] . "'"));
        
        $BankName = $row['BankName'];
        $Balance = $row['netamountpaid'];
       
        //if ($Balance > 0) {
             if ($_REQUEST['bank'] == 3) {
                $lineData = array(
                    $i,
                    $row['emp_name'],
                    number_format($Balance,2),
                    $BankName,
                    $row['ifsccode'],
                    $row['accountno']
                );
                $Total[2] += $Balance;
                $i++;
                fputcsv($f, $lineData, $delimiter);
            } else {
                $lineData = array(
                    $i,
                    $row['emp_name'],
                    number_format($Balance,2),
                    $row['ifsccode'],
                    $row['accountno']
                );
                $Total[2] += $Balance;
                $i++;
                fputcsv($f, $lineData, $delimiter);
            }
        // }
        
    }
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

    // $fields = array(
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     'FOR, SHREE GANESH ENGINEERING CO.'
    // );
    // fputcsv($f, $fields, $delimiter);
    $fields = array(
        '',
        '',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     'Cheque No.',
    //     '',
    //     '',
    //     '',
    //     '',
    //     'HITESH.K.SHAH (PARTNER)');
    // fputcsv($f, $fields, $delimiter);
//move back to beginning of file
    fseek($f, 0);


    header('Content-Type: text/csv');
    $filename = "CompanyPayPaymentReport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>