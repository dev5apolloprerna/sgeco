<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');
//$where = "where 1=1";
// && $_REQUEST['bank'] != NULL
if ($_REQUEST['Company'] != NULL  && $_REQUEST['salaryId'] != NULL) {
    // $where = " and employee.bankid= '" . $_REQUEST['bank'] . "'";
    // if ($_REQUEST['bank'] == 3) {
    //     $where = " and employee.bankid not in (1,2)";
    //     //$where = " and employee.bankid not in (2)";
    // }
    if($_REQUEST['bank'] != ""){
        $where = " and employee.bankid= '" . $_REQUEST['bank'] . "'";
        if ($_REQUEST['bank'] == 3) {
            $where = " and employee.bankid not in (1,2)";
        }
    }
    //$filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId='" . $_REQUEST['salaryId'] . "' and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete=0 and employee.istatus=1";
    //$filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId  in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete=0 and employee.istatus=1";
    $filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and iPaymentStatus=0 and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
    
}

$result = mysqli_query($dbconn, $filterstr);
$Total = array(0, 0);
$mailFormat_main = file_get_contents("companyPayPayment.html");
$i = 1;
$mailFormat_rows = "";

while ($rowapplication = mysqli_fetch_array($result)) {
    $Total[1] = $rowapplication['netamountpaid'] + $Total[1];
    $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_REQUEST['Company'] . "'"));
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $rowapplication['bankid'] . "'"));
    if ($_REQUEST['bank'] == 3) {
        $bankname = 'Other';
        $bankstyle = "display: block;";
        $BankNameStyle = "width: 370px;";
    } else {
        $bankname = $bank['bankname'];
        $bankstyle = "display: none;";
        $BankNameStyle = "width: 520px;";
    }
    $bankName = $bank['bankname'];
    //echo "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and salarymasterId='" . $_REQUEST['salaryId'] . "'";
    $salaryid = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $_REQUEST['salaryId'] . "' and companymasterId='".$_REQUEST['Company']."'"));

    $mailFormat = file_get_contents("companyPayPaymenttr.html");
    $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
    $mailFormat = str_replace("#emp_name#", ucfirst(urldecode($rowapplication['emp_name'])), $mailFormat);
    $mailFormat = str_replace("#netamountpaid#", ucfirst(urldecode(number_format( ceil($rowapplication['netamountpaid']) ,2))), $mailFormat);
    $month = $salaryid['fromdate'];
    if ($_REQUEST['bank'] == 3) {
        $mailFormat_main = str_replace("#bankname#", ucfirst(urldecode($bankname)), $mailFormat_main);
        $mailFormat_main = str_replace("#BankNameStyle#", ucfirst(urldecode($BankNameStyle)), $mailFormat_main);
        $mailFormat = str_replace("#bankname#", ucfirst(urldecode($bankName)), $mailFormat); 
        $mailFormat = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat); 
        $mailFormat_main = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat_main);
    } else {
        $mailFormat_main = str_replace("#BankNameStyle#", ucfirst(urldecode($BankNameStyle)), $mailFormat_main);
        $mailFormat_main = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat_main);
        $mailFormat_main = str_replace("#bankname#", ucfirst(urldecode($bankName)), $mailFormat_main); 
        $mailFormat = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat); 
    }
    // $mailFormat_main = str_replace("#bankname#", ucfirst(urldecode($bankname)), $mailFormat_main);
    // $mailFormat = str_replace("#bankname#", ucfirst(urldecode($bankName)), $mailFormat);
    $mailFormat = str_replace("#accountno#", ucfirst(urldecode($rowapplication['accountno'])), $mailFormat);
    $mailFormat_main = str_replace("#DisplayMonth#", ucfirst(urldecode(date('M-Y',strtotime($month)))), $mailFormat_main);
    $mailFormat = str_replace("#ifsccode#", ucfirst(urldecode($rowapplication['ifsccode'])), $mailFormat);
    $mailFormat_main = str_replace("#city#", ucfirst(urldecode($bank['City'])), $mailFormat_main);
    $mailFormat_main = str_replace("#Company#", ucfirst(urldecode($comp['companyname'])), $mailFormat_main);
    $mailFormat_main = str_replace("#date#", ucfirst(urldecode(date('d-m-Y'))), $mailFormat_main);

    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}
$mailFormat_main = str_replace("#netamounttotal#", ucfirst(number_format(urldecode($Total[1]),2)), $mailFormat_main);
$mailFormat_main = str_replace("#banktr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);
// create new PDF document

$pdf = new TCPDF(P, PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);

// set default font subsetting mode
//$pdf->setFontSubsetting(true);
// set margins
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set default header data
//$pdf->SetHeaderData('logo.png', '40', '', '', array(0, 64, 255), array(0, 64, 128));
//$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
// set header and footer fonts
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
$pdf->SetFont('dejavusans', '', 0, '', true);
//     $pdf->SetFont('helvetica', '', 8);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

$pdf->writeHTML($mailFormat_main, true, false, false, false, '');

//$pdf->writeHTML($html, true, 0);
//$pdf->writeHTML($html, true, 0);
ob_end_clean();

$pdf->Output('BankPayment.pdf', 'I');
?>