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
    $where .= " and companypaymentMaster.iCompanyPaymentId = " . $_REQUEST['token']. " ";
}

$filterstr = "select employee.emp_name,employee.ifsccode,employee.employeecode,employee.accountno,salarydetails.netamountpaid,companypaymentMaster.strPaymentDate,
                    companypaymentMaster.salarymonth,companypaymentMaster.iPaymentMode,companypaymentMaster.iBank,companypaymentMaster.strTransactionNo,companypaymentMaster.iAmount,
                    (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=companypaymentMaster.iBank) as bankName 
                    from companypaymentMaster inner join salarydetails on companypaymentMaster.iCompanyPaymentId=salarydetails.iPaymentId join employee on employee.employeeId=salarydetails.emp_id 
                    " . $where . "  and companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1 order by companypaymentMaster.iCompanyPaymentId desc";
                
$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    
    $data = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT iCompanyPaymentId,iCompanySalaryMasterId,companypaymentMaster.salarymonth,strPaymentDate,iPaymentMode,
            iBank,strTransactionNo,iAmount,companymaster.companyname,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=companypaymentMaster.iBank) as bankName 
            FROM `companypaymentMaster` inner join companymaster on companypaymentMaster.iCompanySalaryMasterId=companymaster.companymasterId where companypaymentMaster.isDelete=0 
            and companypaymentMaster.iStatus=1 and iCompanyPaymentId='".$_REQUEST['token']."'"));
    
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
        $lineData = array(
            $i,
            $row['emp_name'],
            number_format($row['netamountpaid'],2),
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
    $filename = "ViewCompanyPaypaymentHistoryReport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}
?>