<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');
include_once 'companyReportAdvance.php';
$where = "where 1=1";

if ($_REQUEST['companysalarymasterId'] != NULL && $_REQUEST['salarymonthId'] != NULL) {
    $where = " and multicompany.companysalarymasterId = " . $_REQUEST['companysalarymasterId'] . " " and "  
        salarymaster.month = " . $_REQUEST['salarymonthId'] . " ";
}
//if ($_REQUEST['bank'] != NULL) {
//    if($_REQUEST['bank'] == 3){
//        $where1 = " and employee.bankid NOT IN (0,1,2)";
//    }else{
//        $where1 = " and employee.bankid = " . $_REQUEST['bank'] . "";
//    }
//}

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
$filterstr = "SELECT multicompany.*,companysalarymaster.companysalarymasterId,employee.employeeId,employee.emp_name
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    employee.bankid) as BankName ,employee.ifsccode ,employee.accountno,companysalarymaster.fromdate
    ,(select companysalarymaster.month from companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId) as DisplayMonth 
    FROM `multicompany`,employee,companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and 
    multicompany.emp_id = employee.employeeId  " . $where . "  " . $where1 . "   ORDER BY employee.emp_name asc ";

$result = mysqli_query($dbconn, $filterstr);
$reportDeductions = getMultiCompanyReportDeductions($dbconn, $_REQUEST['companysalarymasterId']);

$Total = array(0, 0);
$mailFormat_main = file_get_contents("newmulticompanybankwisedetaild.html");
$i = 1;
$mailFormat_rows = "";

while ($rowapplication = mysqli_fetch_array($result)) {
//    $Total[1] = $Balance + $Total[1];
    if ($_REQUEST['bank'] != 0) {
        if ($rowapplication['BankName'] == 'SBI' || $rowapplication['BankName'] == 'BOB') {
            $BankName = $rowapplication['BankName'] . " Bank";
        } else {
            $BankName = 'Other Bank';
        }
    } else {
        $BankName = "Cash";
    }
    $comp = mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")");
    $companymasterId1 = '';
    while ($commaster1 = mysqli_fetch_array($comp)) {
        $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
    }
    $companymasterId1 = rtrim($companymasterId1, ", ");
    $mailFormat_main = str_replace("#SITE#", ucfirst(urldecode($companymasterId1)), $mailFormat_main);
//    $mailFormat_main = str_replace("#Company#", ucfirst(urldecode($comp['companyname'])), $mailFormat_main);
    //$mailFormat_main = str_replace("#BankName#", ucfirst(urldecode($BankName)), $mailFormat_main);
    $mailFormat_main = str_replace("#date#", ucfirst(urldecode(date('d-m-Y'))), $mailFormat_main);
    if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
        $BankName = 'Other';
        $bankstyle = "display: block";
        $BankNameStyle = "width: 350px;";
        $CommBankstyle = "display: block;";
        $Comm_Bankstyle = "display: none;";
    } else {
        $BankName = $rowapplication['BankName'];
        $bankstyle = "display: none";
        $BankNameStyle = "width: 520px;";
        $CommBankstyle = "display: none;";
        $Comm_Bankstyle = "display: block;";
    }
    $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
               where salarymaster.salarymasterId = salarydetails.salaryId
                and salarydetails.companyId = salarymaster.companymasterId
               and salarymaster.month = '" . $_REQUEST['salarymonthId'] . "'  
               and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")
               and salarydetails.emp_id = " . $rowapplication['employeeId'] . "
               and salarymaster.isDelete=0 and salarymaster.istatus=1
               and salarydetails.isDelete=0 ";

    $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));


    //echo $date ='01'.'/'.$rowapplication['DisplayMonth'];
    // echo date('d-m-Y',$rowapplication['fromdate']);
    // echo date('M-Y',strtotime($rowapplication['fromdate']));
    // exit;

    /*  if($Balance > 0)
      { */
    $employeeDeductions = getEmployeeMultiCompanyReportDeductions($reportDeductions, $rowapplication['employeeId']);
    $calculation = calculateMultiCompanySalary(
        $rowapplication['PresentAmount'],
        $rowapplication['otamt'],
        $rowapplication['adv'],
        $rowapplication['adv_two'],
        $rowapplication['advance_paid_by_bank'],
        $employeeDeductions['pf'],
        $employeeDeductions['esic'],
        $rowapplication['Fa'],
        $rowapplication['Ta']
    );
    $Balance = ceil(ceil($calculation['balance1']) - (float) $empdata['PaidAmount']);
    if ($Balance > 0) {
        $Total[1] = $Balance + $Total[1];
        $mailFormat = file_get_contents("newmulticompanybank_tr.html");
        $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
        $mailFormat = str_replace("#emp_name#", ucwords(strtolower(urldecode($rowapplication['emp_name']))), $mailFormat);
        $mailFormat = str_replace("#Balance#", ucfirst(urldecode(number_format($Balance, 2))), $mailFormat);
        if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
            $mailFormat_main = str_replace("#BankName#", ucfirst(urldecode($BankName)), $mailFormat_main);
            $mailFormat = str_replace("#BankName#", ucfirst(urldecode($rowapplication['BankName'])), $mailFormat); 
            $mailFormat = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat); 
            $mailFormat_main = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat_main);
            $mailFormat_main = str_replace("#BankNameStyle#", ucfirst(urldecode($BankNameStyle)), $mailFormat_main);
            $mailFormat_main = str_replace("#CommBankstyle#", urldecode($CommBankstyle), $mailFormat_main);
            $mailFormat_main = str_replace("#Comm_Bankstyle#", urldecode($Comm_Bankstyle), $mailFormat_main);
            
            $Comm = 0;
            if($Balance <= 10000){
                $Comm = "2.36";
            } else {
                $Comm = "4.72";
            }
            $mailFormat = str_replace("#Comm#", urldecode(number_format($Comm ,2)), $mailFormat);
            $Total[2] = $Comm + $Total[2];
        } else {
            $mailFormat_main = str_replace("#BankName#", ucfirst(urldecode($BankName)), $mailFormat_main); 
            $mailFormat_main = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat_main);
            $mailFormat = str_replace("#bankstyle#", ucfirst(urldecode($bankstyle)), $mailFormat); 
            $mailFormat_main = str_replace("#BankNameStyle#", ucfirst(urldecode($BankNameStyle)), $mailFormat_main);
            $mailFormat_main = str_replace("#CommBankstyle#", urldecode($CommBankstyle), $mailFormat_main);
            $mailFormat_main = str_replace("#Comm_Bankstyle#", urldecode($Comm_Bankstyle), $mailFormat_main);
            
            $mailFormat = str_replace("#Comm#", " ", $mailFormat);
        }
        // $mailFormat = str_replace("#BankName#", ucfirst(urldecode($rowapplication['BankName'])), $mailFormat);
        $mailFormat = str_replace("#ifsccode#", ucfirst(urldecode($rowapplication['ifsccode'])), $mailFormat);
        $mailFormat = str_replace("#accountno#", ucfirst(urldecode(str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$rowapplication['accountno']))))), $mailFormat);
        $mailFormat_main = str_replace("#DisplayMonth#", ucfirst(urldecode(date('M-Y',strtotime($rowapplication['fromdate'])))), $mailFormat_main);
        //$mailFormat_main = str_replace("#DisplayMonth#", ucfirst(urldecode(str_replace('/','-',$rowapplication['DisplayMonth']))), $mailFormat_main);
        $mailFormat_rows = $mailFormat_rows . $mailFormat;
        $i++;
    }
    
}
$TotalAmt = $Total[1] + $Total[2];

$mailFormat_main = str_replace("#BankComm#", number_format(urldecode($Total[2]),2), $mailFormat_main);
$mailFormat_main = str_replace("#TotalAmt#", number_format(urldecode($TotalAmt),2), $mailFormat_main);
$mailFormat_main = str_replace("#netamounttotal#", number_format(urldecode($Total[1]),2), $mailFormat_main);

$mailFormat_main = str_replace("#Balance#", ucfirst(urldecode(number_format($Total[1],2))), $mailFormat_main);
$mailFormat_main = str_replace("#multicompanybank#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);
if ($_REQUEST['bank'] == 3) {
    $transferNote = '<table width="100%" cellspacing="0" cellpadding="5" border="0"><tr><td style="font-size:15px"><strong>Note:</strong> Soft Copy of the Bulk Transfer will be Sent from <strong>hkshah@sgeco.in</strong> and we are solely responsible for any discrepancy in the soft copy and the hard copy sent to you.</td></tr></table>';
    $mailFormat_main = str_replace('</html>', $transferNote . '</html>', $mailFormat_main);
}
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