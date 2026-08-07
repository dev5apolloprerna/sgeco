<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
include('../config.php');

$sql = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token'] . "' and isDelete=0 order by multicompanyid desc";

$result = mysqli_query($dbconn, $sql);
$mailFormat_main = file_get_contents("form_multicompany_full_1.html");
$i = 1;
$mailFormat_rows = "";
$Total = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
//$Total[5] = array();

$array[][] = array();
$array[0][0] = "";
$array[0][1] = "SBI";
$array[0][2] = "BOB";
$array[0][3] = "OTHER";
$array[0][4] = "TOTAL";
$array[1][0] = "BANK PAYMENT";
$array[1][1] = 0;
$array[1][2] = 0;
$array[1][3] = 0;
$array[1][4] = 0;
$array[2][0] = "BALANCE PAYMENT";
$array[2][1] = 0;
$array[2][2] = 0;
$array[2][3] = 0;
$array[2][4] = 0;
$array[3][0] = "TOTAL";
$array[3][1] = 0;
$array[3][2] = 0;
$array[3][3] = 0;
$array[3][4] = 0;

while ($rowapplication = mysqli_fetch_array($result)) {
    $Total[0] = $rowapplication['total'] + $Total[0];
    $Total[1] = $rowapplication['Fa'] + $Total[1];
    $Total[2] = $rowapplication['Ta'] + $Total[2];
    $Total[3] = $rowapplication['balance1'] + $Total[3];

    $Total[6] = $rowapplication['rate'] + $Total[6];
    $Total[7] = $rowapplication['workingdays'] + $Total[7];
    $Total[8] = $rowapplication['othours'] + $Total[8];
    $Total[9] = $rowapplication['PresentAmount'] + $Total[9];

    $Total[10] = $rowapplication['otamt'] + $Total[10];
    $Total[11] = $rowapplication['totalamt'] + $Total[11];
    $Total[12] = $rowapplication['adv'] + $Total[12];

    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $rowapplication['emp_id'] . "'"));

    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));

    $HeaderCompany = "";
    $companymasterId = '0';
    $month = '';
    $comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");
    while ($commaster = mysqli_fetch_array($comid)) {
        $month = $commaster['month'];
        $companymasterId = $companymasterId . ',' . $commaster['companymasterId'];
        $HeaderCompany = $HeaderCompany . "<td><strong>" . $commaster['companyname'] . "</strong></td>";
    }

    $companymasterId = rtrim($companymasterId, ", ");
    $mailFormat_main = str_replace("#hedar#", ucfirst(urldecode($HeaderCompany)), $mailFormat_main);
    $mailFormat = file_get_contents("form_multicompany1_full_tr.html");

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
    $mailFormat = str_replace("#Bank Name#", ucfirst(urldecode($bank['bankname'])), $mailFormat);

    $HeaderCompany = "";
    $comnymasid = explode(',', $companymasterId);
    $iCounter = 0;
    $netamt = '0';
    $AllCompanyTotal = 0;
    for ($iCounter = 1; $iCounter < count($comnymasid); $iCounter++) {

        $saleryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'"));
        $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $rowapplication['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");
        if (mysqli_num_rows($comp) > 0) {
            while ($rowfiltercom = mysqli_fetch_array($comp)) {
                if ($rowfiltercom['netamountpaid'] != '') {
                    $netamt = $rowfiltercom['netamountpaid'];
                    $HeaderCompany = $HeaderCompany . "<td>" . $rowfiltercom['netamountpaid'] . "</td>";
                } else {
                    $netamt = 0;
                    $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
                }
                if ($rowfiltercom['netamountpaid'] != '') {
                    $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                    $Total[5][$iCounter] = $rowfiltercom['netamountpaid'] + $Total[5][$iCounter];
                    //print_r($Total[5][$iCounter]);

                    $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                    $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                    if ($desg['bankid'] == 2) {
                        $array[1][1] = $rowfiltercom['netamountpaid'] + $array[1][1];
                        $array[3][1] = $rowfiltercom['netamountpaid'] + $array[3][1];
                    } else if ($desg['bankid'] == 1) {
                        $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                        $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                    } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                        $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                        $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                    }
                }
            }
        } else {
            $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
        }
    }

    $mailFormat = str_replace("#bank#", ucfirst(urldecode($HeaderCompany)), $mailFormat);
    $netamts = $netamt;

    $bal2 = $rowapplication['balance1'] - $AllCompanyTotal;

    $array[2][4] = $bal2 + $array[2][4];
    $array[3][4] = $bal2 + $array[3][4];
    if ($desg['bankid'] == 2) {
        $array[2][1] = $bal2 + $array[2][1];
        $array[3][1] = $bal2 + $array[3][1];
    } else if ($desg['bankid'] == 1) {
        $array[2][2] = $bal2 + $array[2][2];
        $array[3][2] = $bal2 + $array[3][2];
    } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
        $array[2][3] = $bal2 + $array[2][3];
        $array[3][3] = $bal2 + $array[3][3];
    }

    $Total[4] = $bal2 + $Total[4];

    $CompanysTotal = '';
    for ($iCounter = 1; $iCounter < count($comnymasid); $iCounter++) {
        $CompanysTotal = $CompanysTotal . "<td>" . $Total[5][$iCounter] . "</td>";
    }

    $mailFormat = str_replace("#netamountpaid#", ucfirst(urldecode(round($bal2))), $mailFormat);
    $mailFormat = str_replace("#acno#", ucfirst(urldecode($desg['accountno'])), $mailFormat);
    $mailFormat = str_replace("#date#", ucfirst(urldecode($rowapplication['date'])), $mailFormat);
    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}

$mailFormat_main = str_replace("#Company#", ucfirst(urldecode($CompanysTotal)), $mailFormat_main);
$mailFormat_main = str_replace("#Total#", ucfirst(urldecode(round($Total[0]))), $mailFormat_main);
$mailFormat_main = str_replace("#Total FA#", ucfirst(urldecode($Total[1])), $mailFormat_main);
$mailFormat_main = str_replace("#Total TA#", ucfirst(urldecode($Total[2])), $mailFormat_main);
$mailFormat_main = str_replace("#Balance#", ucfirst(urldecode($Total[3])), $mailFormat_main);
$mailFormat_main = str_replace("#Total Balance#", ucfirst(urldecode($Total[4])), $mailFormat_main);

$mailFormat_main = str_replace("#Rate#", ucfirst(urldecode(round($Total[6]))), $mailFormat_main);
$mailFormat_main = str_replace("#working day#", ucfirst(urldecode($Total[7])), $mailFormat_main);
$mailFormat_main = str_replace("#Total OT#", ucfirst(urldecode($Total[8])), $mailFormat_main);
$mailFormat_main = str_replace("#perent amt#", ucfirst(urldecode($Total[9])), $mailFormat_main);
$mailFormat_main = str_replace("#OT amount#", ucfirst(urldecode($Total[10])), $mailFormat_main);
$mailFormat_main = str_replace("#Total Amount#", ucfirst(urldecode($Total[11])), $mailFormat_main);
$mailFormat_main = str_replace("#advt#", ucfirst(urldecode($Total[12])), $mailFormat_main);

$companymasterId1 = '';
$month1 = '';
$comid1 = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "' ");
while ($commaster1 = mysqli_fetch_array($comid1)) {
    $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
    $month1 = $commaster1['month'];
}
$companymasterId1 = rtrim($companymasterId1, ", ");
$mailFormat_main = str_replace("#SITE#", ucfirst(urldecode($companymasterId1)), $mailFormat_main);
$mailFormat_main = str_replace("#MONTH#", ucfirst(urldecode($month1)), $mailFormat_main);

$SummeryTable = "";
$iCounter = 0;
while ($iCounter < sizeof($array)) {
    if ($iCounter == 0) {
        $SummeryTable = $SummeryTable . "<thead><tr><th  style='width: 100%;text-align: center;border: 1px solid #fff;' colspan='5'>SUMMARY</th></tr><tr class='none'>";
    } else if ($iCounter == 0) {
        $SummeryTable = $SummeryTable . "<tbody><tr>";
    } else {
        $SummeryTable = $SummeryTable . "<tr>";
    }

    for ($jCounter = 0; $jCounter < sizeof($array[$iCounter]); $jCounter++) {
        if ($iCounter == 0) {
            $SummeryTable = $SummeryTable . "<th>" . $array[$iCounter][$jCounter] . "</th>";
        } else {
            $SummeryTable = $SummeryTable . "<td>" . $array[$iCounter][$jCounter] . "</td>";
        }
    }
    if ($iCounter == 0) {
        $SummeryTable = $SummeryTable . "</tr></thead>";
    } else {
        $SummeryTable = $SummeryTable . "</tr>";
    }
    $iCounter++;
}


$mailFormat_main = str_replace("#form_table_summery#", ucfirst(urldecode($SummeryTable)), $mailFormat_main);

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
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
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