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

$where = "";
if ($_REQUEST['companysalarymasterId'] != NULL ){
    $where .= " and paymentMaster.iCompanySalaryMasterId = " . $_REQUEST['companysalarymasterId'] . " ";
}
if ($_REQUEST['salarymonthId'] != NULL) {
    $where .= " and paymentMaster.salarymonth = '" . $_REQUEST['salarymonthId'] . "'";
}
if ($_REQUEST['bank'] != NULL) {
    $where .= " and paymentMaster.iBank = " . $_REQUEST['bank'] . "";
}

// $filterstr = "SELECT paymentMaster.iPaymentId,paymentMaster.iCompanySalaryMasterId,paymentMaster.salarymonth,paymentMaster.strPaymentDate,paymentMaster.iPaymentMode,paymentMaster.iBank,paymentMaster.strTransactionNo,paymentMaster.iAmount
//     ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = paymentMaster.iBank) as BankName,
//     (select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId= multiycompanysalarymaster.companymasterId
//   	and multiycompanysalarymaster.companysalarymasterId in (select pm.iCompanySalaryMasterId from paymentMaster as pm where pm.iCompanySalaryMasterId=paymentMaster.iCompanySalaryMasterId) and multiycompanysalarymaster.isDelete=0
//   	group by multiycompanysalarymaster.companysalarymasterId) as companyname FROM `paymentMaster`,companysalarymaster where 
//     companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId  " . $where . " and paymentMaster.isDelete='0' and paymentMaster.iStatus='1' ORDER BY paymentMaster.iPaymentId desc";
$filterstr = "SELECT paymentMaster.iPaymentId,paymentMaster.iCompanySalaryMasterId,paymentMaster.salarymonth,paymentMaster.strPaymentDate,paymentMaster.iPaymentMode,paymentMaster.iBank,paymentMaster.strTransactionNo,paymentMaster.iAmount
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = paymentMaster.iBank) as BankName,
    (select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId= multiycompanysalarymaster.companymasterId
  	and multiycompanysalarymaster.companysalarymasterId in (select pm.iCompanySalaryMasterId from paymentMaster as pm where pm.iCompanySalaryMasterId=paymentMaster.iCompanySalaryMasterId) and multiycompanysalarymaster.isDelete=0
  	group by multiycompanysalarymaster.companysalarymasterId) as companyname FROM `paymentMaster`,companysalarymaster where 
    companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId " . $where . "  and paymentMaster.isDelete='0' and paymentMaster.iStatus='1' ORDER BY paymentMaster.iPaymentId desc";

$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    if($_REQUEST['companysalarymasterId'] != ""){
        $comp = mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId in (select multiycompanysalarymaster.companymasterId from 
                   multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")");
        $companymasterId1 = '';
        while ($commaster1 = mysqli_fetch_array($comp)) {
            $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
        }
        $companymasterId1 = rtrim($companymasterId1, ", ");
    } else {
        $companymasterId1 = "";
    }
//    echo "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'";
if($_REQUEST['bank'] != ""){
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'"));
    // if ($_REQUEST['bank'] != 0) {
    //     if ($bank['bankname'] == 'BOB' || $bank['bankname'] == 'SBI') {
            $BankName = $bank['bankname'];
    //     } else {
    //         $BankName = 'Other';
    //     }
    // } else {
    //     $BankName = "Cash";
    // }
} else {
    $BankName = "";
}

    $fields = array(
        'SUB:',
        'MULTICOMPANY PAYMENT HISTORY',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);
    if($companymasterId1 != ""){
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
    }
    if($BankName !=""){
        $fields = array(
            $BankName,
            $_REQUEST['salarymonthId'],
            '',
            '',
            '',
            'Date',
            date('d-m-Y'));
        fputcsv($f, $fields, $delimiter);
    } else {
        $fields = array(
            $_REQUEST['salarymonthId'],
            '',
            '',
            '',
            'Date',
            date('d-m-Y'));
        fputcsv($f, $fields, $delimiter);
    }
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
    
    $Total = array("Total", "", "", "", "", "","",0);
    $Total[0] = "Total";
    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        
        $BankName = $row['BankName'];
        if($row['iPaymentMode'] == 1){
            $iPaymentMode =  'Cash';
        } else {
            $iPaymentMode = 'Bank';
        }
        $lineData = array(
            $i,
            $row['companyname'],
            $row['salarymonth'],
            $row['strPaymentDate'],
            $iPaymentMode,
            $BankName,
            $row['strTransactionNo'],
            $row['iAmount'],
        );
        $Total[7] += $row['iAmount'];
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

    // $fields = array(
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     'FOR, SHREE GANESH ENGINEERING CO.'
    // );
    // fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     ''
    // );
    // fputcsv($f, $fields, $delimiter);
    // $fields = array(
    //     //'Cheque No.',
    //     '',
    //     '',
    //     '',
    //     '',
    //     '',
    //     'HITESH.K.SHAH (PARTNER)');
    // fputcsv($f, $fields, $delimiter);
//move back to beginning of file
    fseek($f, 0);


    header('Content-Type: text/csv');
    $filename = "multicompanypaidpaymenthistoryRepot" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>