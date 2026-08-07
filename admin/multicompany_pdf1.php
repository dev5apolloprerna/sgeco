<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
include('../config.php');

$sql = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token'] . "' order by multicompanyid desc";

//$comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_REQUEST['Company'] . "'"));
//$salaryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and salarymasterId='" . $_REQUEST['salarymasterId'] . "'"));





$result = mysqli_query($dbconn, $sql);
$mailFormat_main = file_get_contents("form_multicompany1.html");
$i = 1;
$mailFormat_rows = "";
while ($rowapplication = mysqli_fetch_array($result)) {

    // print_r($rowapplication);
    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $rowapplication['emp_id'] . "'"));

    //  $HeaderCompany = "";
//    $compe = mysqli_query($dbconn, "SELECT * FROM `companymaster`");
//    while ($rowfiltercom = mysqli_fetch_array($compe)) {
//        $HeaderCompany = $HeaderCompany . "<td><strong>" . $rowfiltercom['companyname'] . "</strong></td>";
//    }
    $PresentAmount = $rowapplication['SalaryAmount'] * $rowapplication['workingdays'];
    $otamt = ($rowapplication['SalaryAmount'] / 8) * $rowapplication['othours'];
    $totalamt = $PresentAmount + $otamt;
    $ledger = mysqli_fetch_array(mysqli_query($dbconn, "SELECT SUM(`credit`) as totalcredit,SUM(`debit`) as totaldebit ,SUM(`balance`) as totalbalance FROM `ledger` where emp_id='" . $rowapplication['employeeId'] . "'  and isDelete='0' "));
    $cradit = $ledger['totalcredit'];
    $debit = $ledger['totaldebit'];
    $adv = $cradit - $debit;
    $total = $totalamt - $adv;
    $fata = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `multicompany` where emp_id='" . $rowapplication['employeeId'] . "' "));
    $Fa = $fata['Fa'];
    $Ta = $fata['Ta'];
    $balance1 = $total + $Fa + $Ta;

//    $mailFormat_main = str_replace("#hedar#", ucfirst(urldecode($HeaderCompany)), $mailFormat_main);

    $mailFormat = file_get_contents("form_multicompany1_tr.html");

    $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
    $mailFormat = str_replace("#pfcode#", ucfirst(urldecode($desg['pfcode'])), $mailFormat);
    $mailFormat = str_replace("#ecsno#", ucfirst(urldecode($desg['ecsno'])), $mailFormat);
    $mailFormat = str_replace("#emp_name#", ucfirst(urldecode($rowapplication['name'])), $mailFormat);
    $mailFormat = str_replace("#rate#", ucfirst(urldecode($rowapplication['rate'])), $mailFormat);
    $mailFormat = str_replace("#workingdays#", ucfirst(urldecode($rowapplication['workingdays'])), $mailFormat);
    $mailFormat = str_replace("#othours#", ucfirst(urldecode($rowapplication['othours'])), $mailFormat);
    $mailFormat = str_replace("#PresentAmount#", ucfirst(urldecode($rowapplication['PresentAmount'])), $mailFormat);
    $mailFormat = str_replace("#otamt#", ucfirst(urldecode(round($rowapplication['otamt']))), $mailFormat);
    $mailFormat = str_replace("#totalamt#", ucfirst(urldecode($rowapplication['totalamt'])), $mailFormat);
    $mailFormat = str_replace("#adv#", ucfirst(urldecode($rowapplication['adv'])), $mailFormat);
    $mailFormat = str_replace("#total#", ucfirst(urldecode($rowapplication['total'])), $mailFormat);
    $mailFormat = str_replace("#Fa#", ucfirst(urldecode($rowapplication['Fa'])), $mailFormat);
    $mailFormat = str_replace("#Ta#", ucfirst(urldecode($rowapplication['Ta'])), $mailFormat);
    $mailFormat = str_replace("#balance1#", ucfirst(urldecode($rowapplication['balance1'])), $mailFormat);
   
//    $HeaderCompany = "";
//    $compes = mysqli_query($dbconn, "SELECT * FROM `companymaster` ");
//
//    $iCounter = 0;
//    $AllCompanyTotal =0;
//    while ($rowfiltercompes = mysqli_fetch_array($compes)) {
//        //echo "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (SELECT companymaster.companymasterId FROM `companymaster` WHERE companymaster.companymasterId in ( " . $_REQUEST['Company']  . ") )  and salarydetails.emp_id ='" . $rowapplication['employeeId'] . "' and   salarydetails.salaryId = '" . $_REQUEST['salarymasterId'] . "'";
//        $comp12 = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (SELECT companymaster.companymasterId FROM `companymaster` WHERE companymaster.companymasterId in ( " . $rowfiltercompes['companymasterId'] . ") )  and salarydetails.emp_id ='" . $rowapplication['employeeId'] . "' and   salarydetails.salaryId = '" . $_REQUEST['salarymasterId'] . "'");
//        if (mysqli_num_rows($comp12) > 0) {
//            $iLoopCounter = 0;
//            while ($rowfiltercom = mysqli_fetch_array($comp12)) {
//
//                $toalcomamt = $rowfiltercom['netamountpaid'];
//                if($rowfiltercom['netamountpaid'] != '')
//                {
//                    $HeaderCompany = $HeaderCompany . "<td>" . $rowfiltercom['netamountpaid'] . "</td>";
//                }
//                else
//                {
//                    $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
//                }
//                if($rowfiltercom['netamountpaid'] != '')
//                {
//                    $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
//                }
//            }
//        } else {
//            $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
//        }
//    }
//
//
//    $mailFormat = str_replace("#bank#", ucfirst(urldecode($HeaderCompany)), $mailFormat);
//  
//    $bal2 = $balance1 - $AllCompanyTotal;
//    $mailFormat = str_replace("#netamountpaid#", ucfirst(urldecode(round($bal2))), $mailFormat);
//    $mailFormat = str_replace("#acno#", ucfirst(urldecode($fata['accountno'])), $mailFormat);
//    $mailFormat = str_replace("#date#", ucfirst(urldecode($rowapplication['salarypaiddate'])), $mailFormat);

    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}
$companymasterId = '';
$month = '';
$comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "' ");
while ($commaster = mysqli_fetch_array($comid)) {
    $companymasterId = $commaster['companyname'] . ',' . $companymasterId;
    $month = $commaster['month'];
}
$companymasterId = rtrim($companymasterId, ", ");
 $mailFormat_main = str_replace("#SITE#", ucfirst(urldecode($companymasterId)), $mailFormat_main);
    $mailFormat_main = str_replace("#MONTH#", ucfirst(urldecode($month)), $mailFormat_main);

$mailFormat_main = str_replace("#form_multi_tr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



// set default font subsetting mode
$pdf->setFontSubsetting(true);
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

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

$pdf->Output('gnReport.pdf', 'I');
?>