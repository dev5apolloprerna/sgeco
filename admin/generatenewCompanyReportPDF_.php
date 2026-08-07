<?php

ob_start();
ob_clean();

//include('common.php');
//$connect = new connect();
include('../config.php');


//$sql = "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "' and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc";
//echo $_REQUEST['salarymasterId'];
$sql = "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc";

$Total = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
$deductiontotal = array(0, 0, 0, 0);
$result = mysqli_query($dbconn, $sql);
$mailFormat_main = file_get_contents("newform.html");
$i = 1;
$mailFormat_rows = "";
while ($rowapplication = mysqli_fetch_array($result)) {


    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $rowapplication['emp_id'] . "'"));
    $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_REQUEST['Company'] . "'"));
    $wege = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and salarymasterId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1')"));
    $Category="";
    if($comp['unskill'] === $rowapplication['skillrate']){
        $Category = 'Unskill';
    }else if($comp['semiskill'] === $rowapplication['skillrate']){
        $Category = 'Semi skill';
    }else if($comp['skil'] === $rowapplication['skillrate']){
        $Category = 'Skill';
    }
    $mailFormat = file_get_contents("newform_tr.html");
    $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
    $mailFormat_main = str_replace("#wage#", ucfirst(urldecode($wege['month'])), $mailFormat_main);

    $mailFormat_main = str_replace("#FromDate#", ucfirst(urldecode($wege['fromdate'])), $mailFormat_main);
    $mailFormat_main = str_replace("#ToDate#", ucfirst(urldecode($wege['todate'])), $mailFormat_main);
    
    $mailFormat_main = str_replace("#month#", ucfirst(urldecode(date('M'))), $mailFormat_main);
    $mailFormat_main = str_replace("#Year#", ucfirst(urldecode(date('Y'))), $mailFormat_main);
    $mailFormat_main = str_replace("#comname#", ucfirst(urldecode($comp['companyname'])), $mailFormat_main);

    $mailFormat_main = str_replace("#HighlySkilled#", ucfirst(urldecode($comp['highlyskilled'])), $mailFormat_main);
    $mailFormat_main = str_replace("#Skilled#", ucfirst(urldecode($comp['skil'])), $mailFormat_main);
    $mailFormat_main = str_replace("#SemiSkilled#", ucfirst(urldecode($comp['semiskill'])), $mailFormat_main);
    $mailFormat_main = str_replace("#UnSkilled#", ucfirst(urldecode($comp['unskill'])), $mailFormat_main);

    $mailFormat = str_replace("#Category#", ucfirst(urldecode($Category)), $mailFormat);
    $mailFormat = str_replace("#Name_of_workman#", ucfirst(urldecode($desg['emp_name'])), $mailFormat);
    $mailFormat = str_replace("#Serial_of_in_the_register_of_workman#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Designation_Nature_of_work_done#", ucfirst(urldecode($desg['designation'])), $mailFormat);
    $mailFormat = str_replace("#No_of_Days_Worked#", ucfirst(urldecode($rowapplication['workingdays'])), $mailFormat);
    $mailFormat = str_replace("#Rate_of_Daily_work_done#", ucfirst(urldecode($rowapplication['skillrate'])), $mailFormat);
    $mailFormat = str_replace("#othours#", ucfirst(urldecode($rowapplication['othours'])), $mailFormat);
    $mailFormat = str_replace("#Price_Rate#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Amount_of_Wages#", ucfirst(urldecode($rowapplication['basicwages'])), $mailFormat);
    $mailFormat = str_replace("#Of_Wages_Erned#", ucfirst(urldecode(($rowapplication['totalovertime'] != '') ? $rowapplication['totalovertime'] : '')), $mailFormat);
    $mailFormat = str_replace("#MedicalAllowanceamt#", ucfirst(urldecode($rowapplication['MedicalAllowanceamt'])), $mailFormat);

    $mailFormat = str_replace("#DA#", ucfirst(urldecode(($rowapplication['da'] != '0.00') ? $rowapplication['da'] : '')), $mailFormat);
    $mailFormat = str_replace("#HRA#", ucfirst(urldecode(($rowapplication['hra'] != '0.00') ? $rowapplication['hra'] : '')), $mailFormat);
    $mailFormat = str_replace("#NationalHolidayPayment#", ucfirst(urldecode(($rowapplication['national_holiday_payment'] != '0.00') ? $rowapplication['national_holiday_payment'] : '')), $mailFormat);
    $mailFormat = str_replace("#iBonusAmt#", ucfirst(urldecode(($rowapplication['iBonusAmt'] != '0.00') ? $rowapplication['iBonusAmt'] : '')), $mailFormat);
    $mailFormat = str_replace("#iLeaveAmt#", ucfirst(urldecode(($rowapplication['iLeaveAmt'] != '0.00') ? $rowapplication['iLeaveAmt'] : '')), $mailFormat);

    $mailFormat = str_replace("#Total#", ucfirst(urldecode(number_format(round($rowapplication['total']),2))), $mailFormat);
    $mailFormat = str_replace("#E.S.I.#", ucfirst(urldecode($rowapplication['esi'])), $mailFormat);
    $mailFormat = str_replace("#P.F.#", ucfirst(urldecode(($rowapplication['pf'] != '0.00') ? $rowapplication['pf'] : '')), $mailFormat);
    $mailFormat = str_replace("#F.P.#", ucfirst(urldecode(($rowapplication['pt'] != '0.00') ? $rowapplication['pt'] : '')), $mailFormat);
    $mailFormat = str_replace("#Deductionifany#", ucfirst(urldecode($rowapplication['deductionifany'])), $mailFormat);
    $Deduction_total = $rowapplication['pf'] + $rowapplication['esi'] + $rowapplication['pt'];
    $mailFormat = str_replace("#Deduction_total#", ucfirst(urldecode( number_format((float)$Deduction_total, 2, '.', '') )), $mailFormat);
    $mailFormat = str_replace("#Net_Amount_Paid#", ucfirst(urldecode(ceil($rowapplication['netamountpaid']))), $mailFormat);
    $mailFormat = str_replace("#Signature_Thumb_impression_of_Workman#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Initials_of_Contractor_of_his_Representive#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Account_No#", ucfirst(urldecode($desg['accountno'])), $mailFormat);
    
    $Total[0] = $rowapplication['total'] + $Total[0];
    $Total[1] = $rowapplication['esi'] + $Total[1];
    $Total[2] = $rowapplication['pf'] + $Total[2];
    $Total[4] = $rowapplication['pt'] + $Total[4];
    $Total[5] = $rowapplication['deductionifany'] + $Total[5];
    $Total[3] = ceil($rowapplication['netamountpaid']) + $Total[3];
    //$Total[6] = (!empty($rowapplication['workingdays']) ? $rowapplication['workingdays'] : 0) + !empty($Total[6]) ? $Total[6] : 0;
    $Total[6] = $rowapplication['workingdays'] + $Total[6];
    //$Total[7] = $rowapplication['basicwages'] + !empty($Total[7]) ? $Total[7] : 0;
    $Total[7] = $rowapplication['basicwages'] + $Total[7];
    $Total[8] = $rowapplication['othours'] + $Total[8];
    $Total[9] = $rowapplication['totalovertime'] + $Total[9];

    $Total[10] = $rowapplication['da'] + $Total[10];
    $Total[11] = $rowapplication['hra'] + $Total[11];
    $Total[12] = $rowapplication['national_holiday_payment'] + $Total[12];
    $Total[13] = $rowapplication['iBonusAmt'] + $Total[13];
    $Total[14] = $rowapplication['iLeaveAmt'] + $Total[14];
    //$Total[1] = $rowapplication['esi'] + $Total[1];
    $deductiontotal[0] = $Deduction_total + $deductiontotal[0];
    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}
$mailFormat_main = str_replace("#newform_tr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);
$mailFormat_main = str_replace("#total#", ucfirst(urldecode(number_format(round($Total[0]),2))), $mailFormat_main);
$mailFormat_main = str_replace("#totalDay#", ucfirst(urldecode($Total[6])), $mailFormat_main);
$mailFormat_main = str_replace("#TotalOTHours#", ucfirst(urldecode($Total[8])), $mailFormat_main);
$mailFormat_main = str_replace("#basic#", ucfirst(urldecode($Total[7])), $mailFormat_main);
$mailFormat_main = str_replace("#OTPay#", ucfirst(urldecode($Total[9])), $mailFormat_main);

$mailFormat_main = str_replace("#TotalDA#", ucfirst(urldecode($Total[10])), $mailFormat_main);
$mailFormat_main = str_replace("#TotalHRA#", ucfirst(urldecode($Total[11])), $mailFormat_main);
$mailFormat_main = str_replace("#TotalNationalHolidayPayment#", ucfirst(urldecode($Total[12])), $mailFormat_main);
$mailFormat_main = str_replace("#TotalBonusAmt#", ucfirst(urldecode($Total[13])), $mailFormat_main);
$mailFormat_main = str_replace("#TotalLeaveAmt#", ucfirst(urldecode($Total[14])), $mailFormat_main);

$mailFormat_main = str_replace("#esi#", ucfirst(urldecode($Total[1])), $mailFormat_main);
$mailFormat_main = str_replace("#pf#", ucfirst(urldecode($Total[2])), $mailFormat_main);
$mailFormat_main = str_replace("#pt#", ucfirst(urldecode($Total[4])), $mailFormat_main);
$mailFormat_main = str_replace("#deductiontotal#", ucfirst(urldecode( number_format((float)$deductiontotal[0], 2, '.', '') )), $mailFormat_main);
$mailFormat_main = str_replace("#netamountpaid#", ucfirst(urldecode(number_format($Total[3],2))), $mailFormat_main);
// print_r($mailFormat_main);
// exit;
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
$width = "600";
$height = "210";
$pageLayout = array($width, $height);

ob_clean();

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

/*$pdf = new CUSTOMPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
//Add a custom size  
$width = 175;  
$height = 266; 
$orientation = ($height>$width) ? 'P' : 'L';  
$pdf->addFormat("custom", $width, $height);  
$pdf->reFormat("custom", $orientation);*/ 


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
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

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


/* * ********** OLD Format ********** */

//    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
//
//
//
//// set default font subsetting mode
//$pdf->setFontSubsetting(true);
//// set margins
////$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
////$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//// set default header data
////$pdf->SetHeaderData('logo.png', '40', '', '', array(0, 64, 255), array(0, 64, 128));
////$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
//// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
//
//// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//
//// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, 16, PDF_MARGIN_RIGHT);
////$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
////$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//
//// set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
//
//// Set font
//// dejavusans is a UTF-8 Unicode font, if you only need to
//// print standard ASCII chars, you can use core fonts like
//// helvetica or times to reduce file size.
//$pdf->SetFont('dejavusans', '', 0, '', true);
//$pdf->setPrintHeader(false);
//$pdf->setPrintFooter(false);
////$pdf->AddPage('L', 'A4');
////$pdf->Cell(0, 0, 'A4 LANDSCAPE', 1, 1, 'C');
////     $pdf->SetFont('helvetica', '', 8);
//// Add a page
//// This method has several options, check the source code documentation for more information.
//$pdf->AddPage();
//
//$pdf->writeHTML($mailFormat_main, FALSE, FALSE, FALSE, FALSE, '');
//
////$pdf->writeHTML($html, true, 0);
////$pdf->writeHTML($html, true, 0);
//ob_end_clean();

$pdf->Output('newgnReport.pdf', 'I');
?>