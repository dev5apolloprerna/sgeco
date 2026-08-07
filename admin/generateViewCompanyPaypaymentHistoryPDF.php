<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');
$where = "where 1=1";


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

$Total = array(0, 0);
$mailFormat_main = file_get_contents("ViewCompanyPaypaymentHistor.html");
$i = 1;
$mailFormat_rows = "";

$data = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT iCompanyPaymentId,iCompanySalaryMasterId,companypaymentMaster.salarymonth,strPaymentDate,iPaymentMode,
        iBank,strTransactionNo,iAmount,companymaster.companyname,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=companypaymentMaster.iBank) as bankName 
        FROM `companypaymentMaster` inner join companymaster on companypaymentMaster.iCompanySalaryMasterId=companymaster.companymasterId where companypaymentMaster.isDelete=0 
        and companypaymentMaster.iStatus=1 and iCompanyPaymentId='".$_REQUEST['token']."'"));


$iPaymentMode = $data['iPaymentMode'] ? 'Cash' : 'Bank';
$mailFormat_main = str_replace("#companyname#", ucfirst(urldecode($data['companyname'])), $mailFormat_main);
$mailFormat_main = str_replace("#salarymonth#", ucfirst(urldecode($data['salarymonth'])), $mailFormat_main);
$mailFormat_main = str_replace("#iAmount#", ucfirst(urldecode($data['iAmount'])), $mailFormat_main);
$mailFormat_main = str_replace("#strPaymentDate#", ucfirst(urldecode($data['strPaymentDate'])), $mailFormat_main);
$mailFormat_main = str_replace("#iPaymentMode#", ucfirst(urldecode($iPaymentMode)), $mailFormat_main);
$mailFormat_main = str_replace("#bankName#", ucfirst(urldecode($data['bankName'])), $mailFormat_main);
$mailFormat_main = str_replace("#strTransactionNo#", ucfirst(urldecode($data['strTransactionNo'])), $mailFormat_main);

while ($rowapplication = mysqli_fetch_array($result)) {
    
        $mailFormat = file_get_contents("ViewCompanyPaypaymentHistor_tr.html");
        $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
        $mailFormat = str_replace("#emp_name#", ucfirst(urldecode($rowapplication['emp_name'])), $mailFormat);
        $mailFormat = str_replace("#Balance#", ucfirst(urldecode( number_format(ceil($rowapplication['netamountpaid']),2) )), $mailFormat);
        $mailFormat = str_replace("#ifsccode#", ucfirst(urldecode($rowapplication['ifsccode'])), $mailFormat);
        $mailFormat = str_replace("#accountno#", ucfirst(urldecode(str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$rowapplication['accountno']))))), $mailFormat);
        $mailFormat_rows = $mailFormat_rows . $mailFormat;
        $i++;
}

$mailFormat_main = str_replace("#viewhistorytr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);

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