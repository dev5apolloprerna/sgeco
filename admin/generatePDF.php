<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');


//$sql = "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId='" . $_REQUEST['salarymasterId'] . "' and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc";
//echo $_REQUEST['salarymasterId'];
$sql = "SELECT * FROM `salarydetails` where  companyId='" . $_REQUEST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc";

$Total = array(0, 0, 0, 0);
$result = mysqli_query($dbconn, $sql);
$mailFormat_main = file_get_contents("form.html");
$i = 1;
$mailFormat_rows = "";
while ($rowapplication = mysqli_fetch_array($result)) {


    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $rowapplication['emp_id'] . "'"));
    $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_REQUEST['Company'] . "'"));
    $wege = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and salarymasterIdin (select salarymasterId from salarymaster where  month='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and  istatus='1')"));
    $Category="";
    if($comp['unskill'] === $rowapplication['skillrate']){
        $Category = 'Unskill';
    }else if($comp['semiskill'] === $rowapplication['skillrate']){
        $Category = 'Semi skill';
    }else if($comp['skil'] === $rowapplication['skillrate']){
        $Category = 'Skill';
    }
    $mailFormat = file_get_contents("form_tr.html");
    $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
    $mailFormat_main = str_replace("#wage#", ucfirst(urldecode($wege['month'])), $mailFormat_main);
    $mailFormat_main = str_replace("#month#", ucfirst(urldecode(date('M'))), $mailFormat_main);
    $mailFormat_main = str_replace("#comname#", ucfirst(urldecode($comp['companyname'])), $mailFormat_main);
    $mailFormat = str_replace("#Category#", ucfirst(urldecode($Category)), $mailFormat);
    $mailFormat = str_replace("#Name_of_workman#", ucfirst(urldecode($desg['emp_name'])), $mailFormat);
    $mailFormat = str_replace("#Serial_of_in_the_register_of_workman#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Designation_Nature_of_work_done#", ucfirst(urldecode($desg['designation'])), $mailFormat);
    $mailFormat = str_replace("#No_of_Days_Worked#", ucfirst(urldecode($rowapplication['workingdays'])), $mailFormat);
    $mailFormat = str_replace("#Rate_of_Daily_work_done#", ucfirst(urldecode($rowapplication['skillrate'])), $mailFormat);
    $mailFormat = str_replace("#Price_Rate#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Amount_of_Wages#", ucfirst(urldecode($rowapplication['basicwages'])), $mailFormat);
    $mailFormat = str_replace("#Of_Wages_Erned#", ucfirst(urldecode($rowapplication['totalovertime'])), $mailFormat);
    $mailFormat = str_replace("#MedicalAllowanceamt#", ucfirst(urldecode($rowapplication['MedicalAllowanceamt'])), $mailFormat);
    $mailFormat = str_replace("#Total#", ucfirst(urldecode($rowapplication['total'])), $mailFormat);
    $mailFormat = str_replace("#E.S.I.#", ucfirst(urldecode($rowapplication['esi'])), $mailFormat);
    $mailFormat = str_replace("#P.F.#", ucfirst(urldecode($rowapplication['pf'])), $mailFormat);
    $mailFormat = str_replace("#F.P.#", ucfirst(urldecode($rowapplication['pt'])), $mailFormat);
    $mailFormat = str_replace("#Deductionifany#", ucfirst(urldecode($rowapplication['deductionifany'])), $mailFormat);
    $mailFormat = str_replace("#Net_Amount_Paid#", ucfirst(urldecode(ceil($rowapplication['netamountpaid']))), $mailFormat);
    $mailFormat = str_replace("#Signature_Thumb_impression_of_Workman#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Initials_of_Contractor_of_his_Representive#", ucfirst(urldecode('')), $mailFormat);
    $mailFormat = str_replace("#Account_No#", ucfirst(urldecode($desg['accountno'])), $mailFormat);
    $Total[0] = $rowapplication['total'] + $Total[0];
    $Total[1] = $rowapplication['esi'] + $Total[1];
    $Total[2] = $rowapplication['pf'] + $Total[2];
    $Total[4] = $rowapplication['pt'] + $Total[4];
    $Total[5] = $rowapplication['deductionifany'] + $Total[5];
    $Total[3] = $rowapplication['netamountpaid'] + $Total[3];
    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}

$mailFormat_main = str_replace("#form_tr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);
$mailFormat_main = str_replace("#total#", ucfirst(urldecode($Total[0])), $mailFormat_main);
$mailFormat_main = str_replace("#esi#", ucfirst(urldecode($Total[1])), $mailFormat_main);
$mailFormat_main = str_replace("#pf#", ucfirst(urldecode($Total[2])), $mailFormat_main);
$mailFormat_main = str_replace("#pt#", ucfirst(urldecode($Total[4])), $mailFormat_main);
$mailFormat_main = str_replace("#deductionifany#", ucfirst(urldecode($Total[5])), $mailFormat_main);
$mailFormat_main = str_replace("#netamountpaid#", ucfirst(urldecode($Total[3])), $mailFormat_main);

$width = "400";
$height = "210";
$pageLayout = array($width, $height);

ob_clean();
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

$pdf->Output('gnReport.pdf', 'I');
?>

