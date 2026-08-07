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

$where = "where 1=1 ";
if ($_REQUEST['token'] != NULL && $_REQUEST['token'] != NULL) {
    $where .= " and paymentMaster.iPaymentId = " . $_REQUEST['token']. " ";
}
    
$filterstr = "select employee.employeeId,paymentMaster.iCompanySalaryMasterId,employee.emp_name,employee.ifsccode,employee.employeecode,employee.accountno,multicompany.balance1,paymentMaster.strPaymentDate,paymentMaster.salarymonth,
            paymentMaster.iPaymentMode,paymentMaster.iBank,paymentMaster.strTransactionNo,paymentMaster.iAmount,
            (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=paymentMaster.iBank) as bankName from paymentMaster 
            inner join multicompany on paymentMaster.iPaymentId=multicompany.iPaymentId join employee on employee.employeeId=multicompany.emp_id 
            " . $where . "  and paymentMaster.isDelete=0 and paymentMaster.iStatus=1 order by paymentMaster.iPaymentId desc";
            
$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    
    $data = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT iPaymentId,iCompanySalaryMasterId,paymentMaster.salarymonth,strPaymentDate,iPaymentMode,iBank,strTransactionNo,iAmount,(select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId
                                          	= multiycompanysalarymaster.companymasterId and multiycompanysalarymaster.companysalarymasterId in 
                                          	(select paymentMaster.iCompanySalaryMasterId from paymentMaster as pm where pm.iPaymentId=paymentMaster.iPaymentId) and multiycompanysalarymaster.isDelete=0
                                          	group by multiycompanysalarymaster.companysalarymasterId) as companyname,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=paymentMaster.iBank) as bankName FROM `paymentMaster` where paymentMaster.isDelete=0 and paymentMaster.iStatus=1 and paymentMaster.iPaymentId='".$_REQUEST['token']."'"));
    
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
        ''
    );
    fputcsv($f, $fields, $delimiter);
    
    $fields = array(
        'Company Name',
        'Salary Month',
        'Amount',
        'Paid Date',
        'Mode',
        'Bank',
        'Cheque / Transaction No'
    );
    fputcsv($f, $fields, $delimiter);
    $iPaymentMode = "";
    if($data['iPaymentMode'] == 1){
        $iPaymentMode = "Cash";    
    } else {
        $iPaymentMode = "Bank";    
    }
    $fields = array(
        $data['companyname'],
        $data['salarymonth'],
        $data['iAmount'],
        $data['strPaymentDate'],
        $iPaymentMode,
        $data['bankName'],
        $data['strTransactionNo']
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
        '',
        '',
        '',
        '',
        '',
        ''
    );
    fputcsv($f, $fields, $delimiter);
    
    
    $fields = array(
        'Sr.No',
        'Employee Name',
        'Balance',
        'IFSC Code',
        'Bank A/C No'
    );
    fputcsv($f, $fields, $delimiter);
    
    
    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
            where salarymaster.salarymasterId = salarydetails.salaryId
            and salarymaster.month = '" . $row['salarymonth'] . "'  
            and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
            multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $row['iCompanySalaryMasterId'] . ")
            and salarydetails.emp_id = " . $row['employeeId'] . "  and salarymaster.isDelete=0 ";
        $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));
        $Balance = $row['balance1'] - $empdata['PaidAmount'];
        
        $lineData = array(
            $i,
            $row['emp_name'],
            number_format($Balance,2),
            $row['ifsccode'],
            str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$row['accountno'])))
        );
        $i++;
        fputcsv($f, $lineData, $delimiter);
    }
//     fputcsv($f, $Total, $delimiter);
//     $fields = array(
//         '',
//         '',
//         '',
//         '',
//         '',
//         ''
//     );
//     fputcsv($f, $fields, $delimiter);

//     $fields = array(
//         '',
//         '',
//         '',
//         '',
//         '',
//         'FOR, SHREE GANESH ENGINEERING CO.'
//     );
//     fputcsv($f, $fields, $delimiter);
//     $fields = array(
//         '',
//         '',
//         '',
//         '',
//         '',
//         ''
//     );
//     fputcsv($f, $fields, $delimiter);
//     $fields = array(
//         'Cheque No.',
//         '',
//         '',
//         '',
//         '',
//         'HITESH.K.SHAH (PARTNER)');
//     fputcsv($f, $fields, $delimiter);
// //move back to beginning of file
//     fseek($f, 0);

    fseek($f, 0);
    header('Content-Type: text/csv');
    $filename = "ViewMulticompanyPaypaymentHistoryReport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>