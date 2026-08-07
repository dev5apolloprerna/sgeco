<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');

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

$Total = array(0, 0);
$mailFormat_main = file_get_contents("ViewmulticompanyPaypaymentHistor.html");
$i = 1;
$mailFormat_rows = "";

$data = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT iPaymentId,iCompanySalaryMasterId,paymentMaster.salarymonth,strPaymentDate,iPaymentMode,iBank,strTransactionNo,iAmount,(select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId
                                          	= multiycompanysalarymaster.companymasterId and multiycompanysalarymaster.companysalarymasterId in 
                                          	(select paymentMaster.iCompanySalaryMasterId from paymentMaster as pm where pm.iPaymentId=paymentMaster.iPaymentId) and multiycompanysalarymaster.isDelete=0
                                          	group by multiycompanysalarymaster.companysalarymasterId) as companyname,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=paymentMaster.iBank) as bankName FROM `paymentMaster` where paymentMaster.isDelete=0 and paymentMaster.iStatus=1 and paymentMaster.iPaymentId='".$_REQUEST['token']."'"));


$iPaymentMode = $data['iPaymentMode'] ? 'Cash' : 'Bank';
$mailFormat_main = str_replace("#companyname#", ucfirst(urldecode($data['companyname'])), $mailFormat_main);
$mailFormat_main = str_replace("#salarymonth#", ucfirst(urldecode($data['salarymonth'])), $mailFormat_main);
$mailFormat_main = str_replace("#iAmount#", ucfirst(urldecode($data['iAmount'])), $mailFormat_main);
$mailFormat_main = str_replace("#strPaymentDate#", ucfirst(urldecode($data['strPaymentDate'])), $mailFormat_main);
$mailFormat_main = str_replace("#iPaymentMode#", ucfirst(urldecode($iPaymentMode)), $mailFormat_main);
$mailFormat_main = str_replace("#bankName#", ucfirst(urldecode($data['bankName'])), $mailFormat_main);
$mailFormat_main = str_replace("#strTransactionNo#", ucfirst(urldecode($data['strTransactionNo'])), $mailFormat_main);

while ($rowapplication = mysqli_fetch_array($result)) {
     $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
              where salarymaster.salarymasterId = salarydetails.salaryId
              and salarymaster.month = '" . $rowapplication['salarymonth'] . "'  
              and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
              multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $rowapplication['iCompanySalaryMasterId'] . ")
              and salarydetails.emp_id = " . $rowapplication['employeeId'] . "  and salarymaster.isDelete=0 ";
    $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));
    $Balance = $rowapplication['balance1'] - $empdata['PaidAmount'];

    $mailFormat = file_get_contents("ViewMultiCompanyPaypaymentHistor_tr.html");
    $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
    $mailFormat = str_replace("#emp_name#", ucfirst(urldecode($rowapplication['emp_name'])), $mailFormat);
    $mailFormat = str_replace("#Balance#", ucfirst(urldecode( number_format(ceil($Balance),2) )), $mailFormat);
    $mailFormat = str_replace("#ifsccode#", ucfirst(urldecode($rowapplication['ifsccode'])), $mailFormat);
    $mailFormat = str_replace("#accountno#", ucfirst(urldecode(str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$rowapplication['accountno']))))), $mailFormat);
    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}

$mailFormat_main = str_replace("#viewmultihistorytr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);

$pdf = new TCPDF(P, PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 105, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
//$pdf->SetFont('dejavusans', '', 0, '', true);
$pdf->SetFont('helvetica', '', 8);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

$pdf->writeHTML($mailFormat_main, true, false, false, false, '');

//$pdf->writeHTML($html, true, 0);
//$pdf->writeHTML($html, true, 0);
ob_end_clean();

$pdf->Output('PaypaymentRepot.pdf', 'I');
?>