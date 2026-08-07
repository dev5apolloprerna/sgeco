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

$filterstr =  "SELECT paymentMaster.iAmount,paymentMaster.iBank,paymentMaster.strPaymentDate,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = paymentMaster.iBank) as BankName,
(select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId= multiycompanysalarymaster.companymasterId
  and multiycompanysalarymaster.companysalarymasterId in (select pm.iCompanySalaryMasterId from paymentMaster as pm where pm.iCompanySalaryMasterId=paymentMaster.iCompanySalaryMasterId) and multiycompanysalarymaster.isDelete=0
  group by multiycompanysalarymaster.companysalarymasterId) as companyname FROM `paymentMaster`,companysalarymaster where 
companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId 
and month='" . $_REQUEST['month'] . "' and paymentMaster.isDelete='0' and paymentMaster.iStatus='1' 
UNION All
SELECT companypaymentMaster.iAmount,companypaymentMaster.iBank,companypaymentMaster.strPaymentDate,
(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = companypaymentMaster.iBank) as BankName,
(select companymaster.companyname from companymaster where companymaster.companymasterId = companypaymentMaster.iCompanySalaryMasterId and isDelete=0) as companyname FROM companypaymentMaster 
where 1=1 and salarymonth='" . $_REQUEST['month'] . "' and companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1";

$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    
    $fields = array(
        "",
        "",
        'Summary',
        '',
        ''
        );
    fputcsv($f, $fields, $delimiter);
        $fields = array(
            '',
            'SBI',
            'BOB',
            'Other',
            'Total'
        );
        fputcsv($f, $fields, $delimiter);
    
    $Total = array("Total", "0", "0", "0", "0");
    $Total[0] = "Total";
    $i = 1;
    
    $SBITotal = 0;
    $BOBTotal = 0;
    $OtherTotal = 0;
    $TotalAmount = 0;
    while ($row = mysqli_fetch_array($result)) {
        
        $iAmount = 0;
        if($row['iBank'] == "2"){
            $SBI = $row['iAmount'];
            $iAmount += $row['iAmount'];
            $SBITotal += $row['iAmount'];
        } else {
            $SBI = "0";
        }
        if($row['iBank'] == "1"){
            $BOB = $row['iAmount'];
            $iAmount += $row['iAmount'];
            $BOBTotal += $row['iAmount'];
        } else {
            $BOB = "0";
        }
        
        if($row['iBank'] == "0" || $row['iBank'] > "2" || $row['iBank'] == ""){
            $Other = $row['iAmount'];
            $iAmount += $row['iAmount'];
            $OtherTotal += $row['iAmount'];
        } else {
            $Other = "0";
        }
        $TotalAmount+=$iAmount;           
        $lineData = array(
            $row['companyname'] ." - ". $row['strPaymentDate'],
            $SBI,
            $BOB,
            $Other,
            $iAmount
        );
        
        $i++;
        fputcsv($f, $lineData, $delimiter);
    }
    $Total[1] += $SBITotal;
    $Total[2] += $BOBTotal;
    $Total[3] += $OtherTotal;
    $Total[4] += $TotalAmount;
    fputcsv($f, $Total, $delimiter);
    
//move back to beginning of file
    fseek($f, 0);


    header('Content-Type: text/csv');
    $filename = "CompanyPaymentHistoryreport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>