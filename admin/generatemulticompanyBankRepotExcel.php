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

if ($_REQUEST['companysalarymasterId'] != NULL && $_REQUEST['salarymonthId'] != NULL) {
    $where = " and multicompany.companysalarymasterId = " . $_REQUEST['companysalarymasterId'] . " " and "  
        salarymaster.month = " . $_REQUEST['salarymonthId'] . " ";
}
$where1 = "";
if ($_REQUEST['bank'] != NULL) {
    if ($_REQUEST['bank'] == 3) {
        $where1 .= " and employee.bankid not in (1,2) and multicompany.pay_cash='0'";
        //$where1 .= " and employee.bankid not in (2) and multicompany.pay_cash='0'";
    } else if ($_REQUEST['bank'] == 1 || $_REQUEST['bank'] == 2) {
        $where1 .= " and employee.bankid = " . $_REQUEST['bank'] . " and multicompany.pay_cash='0'";
    } else {
        $where1 .= " and multicompany.pay_cash='1'";
    }
}
$filterstr = "SELECT multicompany.balance1,companysalarymaster.companysalarymasterId,employee.employeeId,employee.emp_name
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    employee.bankid) as BankName ,employee.ifsccode ,employee.accountno 
    ,(select companysalarymaster.month from companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId) as DisplayMonth 
    FROM `multicompany`,employee,companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and 
    multicompany.emp_id = employee.employeeId  " . $where . "  " . $where1 . "   ORDER BY employee.emp_name asc ";

$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    $comp = mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")");
    $companymasterId1 = '';
    while ($commaster1 = mysqli_fetch_array($comp)) {
        $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
    }
    $companymasterId1 = rtrim($companymasterId1, ", ");
//    echo "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'";
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'"));
    if ($_REQUEST['bank'] != 0) {
        if ($bank['bankname'] == 'BOB' || $bank['bankname'] == 'SBI') {
            $BankName = $bank['bankname'];
        } else {
            $BankName = 'Other';
        }
    } else {
        $BankName = "Cash";
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
    if ($_REQUEST['bank'] == 3) {
        $fields = array(
            'Sr. No',
            'Name',
            'Balance',
            'Bank Name',
            'IFSC Code',
            'Bank A/C No',
        );
        fputcsv($f, $fields, $delimiter);
    } else {
        $fields = array(
            'Sr. No',
            'Name',
            'Balance',
            'IFSC Code',
            'Bank A/C No',
        );
        fputcsv($f, $fields, $delimiter);
    }

    $Total = array("Total", "", "0", "", "", "");
    $Total[0] = "Total";
    $i = 1;
    while ($row = mysqli_fetch_array($result)) {
        $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
               where salarymaster.salarymasterId = salarydetails.salaryId
               and salarymaster.month = '" . $_REQUEST['salarymonthId'] . "'  
               and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")
               and salarydetails.emp_id = " . $row['employeeId'] . "  and salarymaster.isDelete=0 ";

        $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));
        if ($_REQUEST['bank'] != 0) {
            if ($row['BankName'] == 'BOB' || $row['BankName'] == 'SBI') {
                $BankName = $row['BankName'];
            } else if(isset($row['BankName']) || $row['BankName'] != ""){ 
                $BankName = $row['BankName'];
            } else {
                $BankName = 'Other';
            }
        } else {
            $BankName = "Cash";
        }
        $Balance = $row['balance1'] - $empdata['PaidAmount'];
        if ($Balance > 0) {
            if ($_REQUEST['bank'] == 3) {
                $lineData = array(
                    $i,
                    ucwords(strtolower($row['emp_name'])),
                    number_format($Balance,2),
                    ucwords(strtolower($BankName)),
                    $row['ifsccode'],
                    str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$row['accountno'])))
                );
                $Total[2] += $Balance;
                $i++;
                fputcsv($f, $lineData, $delimiter);
            } else {
                $lineData = array(
                    $i,
                    ucwords(strtolower($row['emp_name'])),
                    number_format($Balance,2),
                    $row['ifsccode'],
                    str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$row['accountno'])))
                );
                $Total[2] += $Balance;
                $i++;
                fputcsv($f, $lineData, $delimiter);
            }
        }
        
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