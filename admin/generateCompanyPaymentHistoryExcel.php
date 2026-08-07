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
//$filename = "CompanyPaymentHistoryreport" . date('Y-m-d H:i:s') . ".csv";
//create a file pointer

$f = fopen('php://memory', 'w');

$where = "where 1=1 ";
if ($_REQUEST['companysalarymasterId'] != NULL) {
    
    $where .= " and companypaymentMaster.iCompanySalaryMasterId = " . $_REQUEST['companysalarymasterId'] . " ";
}
if ($_REQUEST['salarymonthId'] != NULL) {
    $where .= " and companypaymentMaster.salarymonth ='" . $_REQUEST['salarymonthId'] . "'";
}
if ($_REQUEST['bank'] != NULL) {
    $where .= " and companypaymentMaster.iBank = " . $_REQUEST['bank'] . "";
}
$filterstr = "SELECT companypaymentMaster.iCompanyPaymentId,companypaymentMaster.iCompanySalaryMasterId,companypaymentMaster.salarymonth,companypaymentMaster.iBank,companypaymentMaster.strTransactionNo,companypaymentMaster.iPaymentMode,companypaymentMaster.iAmount,
        (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = companypaymentMaster.iBank) as PaidBankName,
        CASE
            WHEN companypaymentMaster.iPaymentMode = 1 THEN 'Cash'
            ELSE 'Bank'
        END as iPaymentMode,companypaymentMaster.strPaymentDate,
        (select companymaster.companyname from companymaster where companymaster.companymasterId = companypaymentMaster.iCompanySalaryMasterId and isDelete=0) as companyname
        FROM companypaymentMaster " . $where . "  and companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1 order by companypaymentMaster.iCompanyPaymentId desc";

$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    if($_REQUEST['companysalarymasterId'] != ""){
        $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId=" . $_REQUEST['companysalarymasterId'] . ""));
        $companymasterId1 = $commaster1['companyname'];
        $companymasterId1 = rtrim($companymasterId1, ", ");
    } else {
        $companymasterId1 ="";
    }
    if($_REQUEST['bank'] != ""){
        $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'"));
        $BankName = $bank['bankname'];
    } else {
        $BankName = "";
    }

    $fields = array(
        'SUB:',
        'COMPANY PAYMENT HISTORY',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);
    $fields = array(
        'SITE:',
        $companymasterId1,
        '',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);

    $fields = array(
        $BankName,
        $_REQUEST['salarymonthId'],
        'Balance Payment',
        '',
        '',
        'Date',
        date('d-m-Y'));
    fputcsv($f, $fields, $delimiter);
        $fields = array(
            'Sr. No',
            'Company Name',
            'Salary Month',
            'Paid Date',
            'Mode',
            'Bank',
            'Cheque / Transaction No',
            'Amount',
        );
        fputcsv($f, $fields, $delimiter);
    
    $Total = array("Total", "", "0", "", "", "");
    $Total[0] = "Total";
    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        
        $BankName = $row['BankName'];
        $lineData = array(
            $i,
            $row['companyname'],
            $row['salarymonth'],
            $row['strPaymentDate'],
            $row['iPaymentMode'],
            $BankName,
            $row['strTransactionNo'],
            $row['iAmount'],
        );
        $Total[2] += $row['iAmount'];
        $i++;
        fputcsv($f, $lineData, $delimiter);
        
        
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

    /*$fields = array(
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
        //'Cheque No.',
        '',
        '',
        '',
        '',
        '',
        'HITESH.K.SHAH (PARTNER)');
    fputcsv($f, $fields, $delimiter);*/
//move back to beginning of file
    fseek($f, 0);


    header('Content-Type: text/csv');
    $filename = "CompanyPaymentHistoryreport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>