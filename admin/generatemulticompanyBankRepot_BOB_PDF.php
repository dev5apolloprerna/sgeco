<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');
$where = "where 1=1";

if ($_REQUEST['companysalarymasterId'] != NULL && $_REQUEST['salarymonthId'] != NULL) {
    
    echo $where = " and multicompany.companysalarymasterId = " . $_REQUEST['companysalarymasterId'] . " " and "  
        companysalarymaster.month = " . $_REQUEST['salarymonthId'] . " ";
}
echo "<br/>";
//if ($_REQUEST['bank'] != NULL) {
//    if($_REQUEST['bank'] == 3){
//        $where1 = " and employee.bankid NOT IN (0,1,2)";
//    }else{
//        $where1 = " and employee.bankid = " . $_REQUEST['bank'] . "";
//    }
//}

if ($_REQUEST['bank'] != NULL) {
    if ($_REQUEST['bank'] == 3) {
        //$where1 .= " and employee.bankid not in (1,2) and multicompany.pay_cash='0'";
        $where1 .= " and employee.bankid not in (2) and multicompany.pay_cash='0'";
    } 

    if ($_REQUEST['bank'] == 4) {
            //$where = " and employee.bankid not in (1,2)";
            $where1 .= " and employee.bankid not in (1,2) and multicompany.pay_cash='0'";
        }


    else if ($_REQUEST['bank'] == 1 || $_REQUEST['bank'] == 2) {
        $where1 .= " and employee.bankid = " . $_REQUEST['bank'] . " and multicompany.pay_cash='0'";
    } else {
        $where1 .= " and multicompany.pay_cash='1'";
    }
}

 $filterstr = "SELECT multicompany.balance1,companysalarymaster.companysalarymasterId,employee.employeeId,employee.emp_name
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    employee.bankid) as BankName ,employee.ifsccode ,employee.accountno,companysalarymaster.fromdate
    ,(select companysalarymaster.month from companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId) as DisplayMonth 
    FROM `multicompany`,employee,companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and 
    multicompany.emp_id = employee.employeeId  " . $where . "  " . $where1 . "   ORDER BY employee.emp_name asc ";

$result = mysqli_query($dbconn, $filterstr);

$Total = array(0, 0);
$mailFormat_main = file_get_contents("multicompanybankwisedetaild.html");
$i = 1;
$mailFormat_rows = "";

while ($rowapplication = mysqli_fetch_array($result)) {
//    $Total[1] = $Balance + $Total[1];
    if ($_REQUEST['bank'] != 0) {
        if ($rowapplication['BankName'] == 'SBI') {
            $BankName = $rowapplication['BankName'] . " Bank";
        } elseif ($rowapplication['BankName'] == 'BOB') {
             $BankName = $rowapplication['BankName'] . " Bank";
        }

    }
    if ($_REQUEST['bank'] == 3) {
        $BankName = 'Other(Incl BOB)';
    } else if ($_REQUEST['bank'] == 4) {
        $BankName = 'Other(Excl BOB, SBI)';
     } 
    // if ($_REQUEST['bank'] != 0) {
    //     if ($rowapplication['BankName'] == 'BOB') {
    //         $BankName = $rowapplication['BankName'] . " Bank";
    //     } else {
    //         $BankName = 'Other Bank';
    //     }
    // } else {
    //     $BankName = "Cash";
    // }
    $comp = mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")");
    $companymasterId1 = '';
    while ($commaster1 = mysqli_fetch_array($comp)) {
        $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
    }
    $companymasterId1 = rtrim($companymasterId1, ", ");
    $mailFormat_main = str_replace("#SITE#", ucfirst(urldecode($companymasterId1)), $mailFormat_main);
//    $mailFormat_main = str_replace("#Company#", ucfirst(urldecode($comp['companyname'])), $mailFormat_main);
    $mailFormat_main = str_replace("#BankName#", ucfirst(urldecode($BankName)), $mailFormat_main);
    $mailFormat_main = str_replace("#date#", ucfirst(urldecode(date('d-m-Y'))), $mailFormat_main);


      $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
               where salarymaster.salarymasterId = salarydetails.salaryId
               and salarymaster.month = '" . $_REQUEST['salarymonthId'] . "'  
               and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")
               and salarydetails.emp_id = " . $rowapplication['employeeId'] . " and salarymaster.isDelete = '0'";

    $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));


    /*echo $date ='01'.'/'.$rowapplication['DisplayMonth'];
    //echo date($date);
    echo date('M/Y',strtotime($rowapplication['fromdate']));
    exit;*/

    /*  if($Balance > 0)
      { */
     $Balance = $rowapplication['balance1'] - $empdata['PaidAmount'];
     $balanchecstatus=true;
    if ($Balance > 0) {
    
        $Total[1] = $Balance + $Total[1];
        $mailFormat = file_get_contents("multicompanybank_tr.html");
        $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
        $mailFormat = str_replace("#emp_name#", ucfirst(urldecode($rowapplication['emp_name'])), $mailFormat);
        $mailFormat = str_replace("#Balance#", ucfirst(urldecode(ceil($Balance))), $mailFormat);
        $mailFormat = str_replace("#BankName#", ucfirst(urldecode($rowapplication['BankName'])), $mailFormat);
        $mailFormat = str_replace("#ifsccode#", ucfirst(urldecode($rowapplication['ifsccode'])), $mailFormat);
        $mailFormat = str_replace("#accountno#", ucfirst(urldecode($rowapplication['accountno'])), $mailFormat);
        $mailFormat_main = str_replace("#DisplayMonth#", ucfirst(urldecode(date('M-Y',strtotime($rowapplication['fromdate'])))), $mailFormat_main);
        $mailFormat_rows = $mailFormat_rows . $mailFormat;
        $i++;
    }
    
}

$mailFormat_main = str_replace("#Balance#", ucfirst(urldecode($Total[1])), $mailFormat_main);
$mailFormat_main = str_replace("#multicompanybank#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);

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

$pdf->Output('multicompanybankreport.pdf', 'I');
?>