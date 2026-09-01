<?php

ob_start();
include('../config.php');
include_once 'companyReportAdvance.php';

$sql = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token'] . "' and isDelete=0 order by name asc";

$result = mysqli_query($dbconn, $sql);
$mailFormat_main = file_get_contents("form_multicompany_full_1.html");

$i = 1;
$mailFormat_rows = "";
//$Total = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0,0,0,0,0,0,0,0,0);
//$Total[5] = array();

$array[][] = array();
$array[0][0] = "";
$array[0][1] = "CASH";
$array[0][2] = "SBI";
$array[0][3] = "BOB";
$array[0][4] = "OTHER";
$array[0][5] = "TOTAL";
$array[1][0] = "BANK PAYMENT";
$array[1][1] = 0;
$array[1][2] = 0;
$array[1][3] = 0;
$array[1][4] = 0;
$array[1][5] = 0;
$array[2][0] = "BALANCE PAYMENT";
$array[2][1] = 0;
$array[2][2] = 0;
$array[2][3] = 0;
$array[2][4] = 0;
$array[2][5] = 0;
$array[3][0] = "TOTAL";
$array[3][1] = 0;
$array[3][2] = 0;
$array[3][3] = 0;
$array[3][4] = 0;
$array[3][5] = 0;
$array[4][0] = "ADVANCE PAYMENT";
$array[4][1] = 0;
$array[4][2] = 0;
$array[4][3] = 0;
$array[4][4] = 0;
$array[4][5] = 0;
$array[5][0] = "TOTAL PAYMENT";
$array[5][1] = 0;
$array[5][2] = 0;
$array[5][3] = 0;
$array[5][4] = 0;
$array[5][5] = 0;

$totalofTotal = 0;
$TotalFa = 0;
$TotalTa = 0;
$Totalbalance1 = 0;
$Totalrate = 0;
$Totalworkingdays = 0;
$Totalothours = 0;
$TotalPresentAmount = 0;
$Totalotamt = 0;
$Totaltotalamt = 0;
$Totaladv = 0;
$TotaladvTwo = 0;
$TotalAdvPaidByBank = 0;
$TotalPfAmount = 0;
$TotalEsicAmount = 0;
$TotalBalance2 = 0;
$companyWiseTotal = array();
// $companySalary = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT month FROM companysalarymaster WHERE companysalarymasterId='" . (int) $_REQUEST['token'] . "'"));
// $reportMonth = $companySalary ? $companySalary['month'] : '';
// $reportAdvances = getMultiCompanyReportAdvances($dbconn, $_REQUEST['token'], $reportMonth);
$reportDeductions = getMultiCompanyReportDeductions($dbconn, $_REQUEST['token']);
while ($rowapplication = mysqli_fetch_array($result)) {

    // $totalofTotal += $rowapplication['total'];
    // $TotalFa += (float) $rowapplication['Fa'];
    // $TotalTa += (float) $rowapplication['Ta'];
    // $Totalbalance1 += (float) $rowapplication['balance1'];
    $Totalrate += (float) $rowapplication['rate'];
    $Totalworkingdays += (float) $rowapplication['workingdays'];
    $Totalothours += (float) $rowapplication['othours'];
    $TotalPresentAmount += (float) $rowapplication['PresentAmount'];

    $Totalotamt += (float) $rowapplication['otamt'];
    $Totaltotalamt += (float) $rowapplication['totalamt'];
    $Totaladv += (float) $rowapplication['adv'];
    $TotaladvTwo += (float) $rowapplication['adv_two'];
    // $advPaidByBank = (float) $rowapplication['advance_paid_by_bank'];
    // $advPaidByBank = getEmployeeCompanyReportAdvance($reportAdvances, $rowapplication['emp_id']);
    // Use the value saved on this report so the PDF matches the reviewed list.
    $advPaidByBank = (float) $rowapplication['advance_paid_by_bank'];
    $TotalAdvPaidByBank += $advPaidByBank;

    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0' and employeeId='" . $rowapplication['emp_id'] . "'"));

    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  isDelete='0' and  bankmasterId='" . $desg['bankid'] . "'"));

    $HeaderCompany = "";
    $companymasterId = '';
    $month = '';
    $comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");
    while ($commaster = mysqli_fetch_array($comid)) {
        $month = $commaster['month'];
        $companymasterId .= $commaster['companymasterId'] . ',';
        $HeaderCompany = $HeaderCompany . "<td><strong>" . $commaster['companyname'] . "</strong></td>";
    }
    $companymasterId = rtrim($companymasterId, ',');

    $companymasterId = rtrim($companymasterId, ", ");
    $employeeDeductions = getEmployeeMultiCompanyReportDeductions($reportDeductions, $rowapplication['emp_id']);
    $pfAmount = $employeeDeductions['pf'];
    $esicAmount = $employeeDeductions['esic'];
    $calculation = calculateMultiCompanySalary($rowapplication['PresentAmount'], $rowapplication['otamt'], $rowapplication['adv'], $rowapplication['adv_two'], $advPaidByBank, $pfAmount, $esicAmount, $rowapplication['Fa'], $rowapplication['Ta']);
    // Round the highlighted Total and Balance columns upward and accumulate
    // those displayed values so the footer remains consistent with each row.
    $rowTotal = ceil($calculation['total']);
    $rowBalance = ceil($calculation['balance1']);
    $totalofTotal += $rowTotal;
    $TotalFa += (float) $rowapplication['Fa'];
    $TotalTa += (float) $rowapplication['Ta'];
    $Totalbalance1 += $rowBalance;

    $TotalPfAmount += $pfAmount;
    $TotalEsicAmount += $esicAmount;
    $mailFormat_main = str_replace("#hedar#", ucfirst(urldecode($HeaderCompany)), $mailFormat_main);
    $mailFormat = file_get_contents("form_multicompany1_full_tr.html");

    if($rowapplication['pay_cash'] == 0){
        $bankname = $bank['bankname'] ." - ". $desg['ifsccode'];
    }else{
        $bankname = "Cash";
    }
    
    $mailFormat = str_replace("#Sr.No#", ucfirst(urldecode($i)), $mailFormat);
    $mailFormat = str_replace("#pfcode#", ucfirst(urldecode($desg['pfcode'])), $mailFormat);
    $mailFormat = str_replace("#ecsno#", ucfirst(urldecode($desg['ecsno'])), $mailFormat);
    $mailFormat = str_replace("#emp_name#", ucfirst(urldecode($desg['emp_name'])), $mailFormat);
    $mailFormat = str_replace("#rate#", ucfirst(urldecode($rowapplication['rate'])), $mailFormat);
    $mailFormat = str_replace("#workingdays#", ucfirst(urldecode($rowapplication['workingdays'])), $mailFormat);
    $mailFormat = str_replace("#othours#", ucfirst(urldecode($rowapplication['othours'])), $mailFormat);
    $mailFormat = str_replace("#PresentAmount#", ucfirst(urldecode($rowapplication['PresentAmount'])), $mailFormat);
    $mailFormat = str_replace("#otamt#", ucfirst(urldecode(round($rowapplication['otamt']))), $mailFormat);
    $mailFormat = str_replace("#totalamt#", ucfirst(urldecode($rowapplication['totalamt'])), $mailFormat);
    $mailFormat = str_replace("#adv#", ucfirst(urldecode($rowapplication['adv'])), $mailFormat);
    $mailFormat = str_replace("#advtTwo#", ucfirst(urldecode($rowapplication['adv_two'])), $mailFormat);
    $mailFormat = str_replace("#advPaidByBank#", ucfirst(urldecode($advPaidByBank)), $mailFormat);
    $mailFormat = str_replace("#pfAmount#", ucfirst(urldecode($pfAmount)), $mailFormat);
    $mailFormat = str_replace("#esicAmount#", ucfirst(urldecode($esicAmount)), $mailFormat);
    $mailFormat = str_replace("#total#", ucfirst(urldecode($rowTotal)), $mailFormat);
    $mailFormat = str_replace("#Fa#", ucfirst(urldecode($rowapplication['Fa'])), $mailFormat);
    $mailFormat = str_replace("#Ta#", ucfirst(urldecode($rowapplication['Ta'])), $mailFormat);
        $mailFormat = str_replace("#balance1#", ucfirst(urldecode($rowBalance)), $mailFormat);
    $mailFormat = str_replace("#Bank Name#", ucfirst(urldecode($bankname)), $mailFormat);

    $HeaderCompany = "";
    $comnymasid = explode(',', $companymasterId);
//    $iCounter = 0;
    $netamt = '0';
    $AllCompanyTotal = 0;
//    echo "<pre>";
    $strPaymentDate = "";
    for ($iCounter = 0; $iCounter < count($comnymasid); $iCounter++) {
        $saleryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'"));
        $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid,strPaymentDate FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $rowapplication['emp_id'] . "' and salarydetails.isDelete=0  and salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");
        if (mysqli_num_rows($comp) > 0) {
            while ($rowfiltercom = mysqli_fetch_array($comp)) {
                if ($rowfiltercom['netamountpaid'] != '') {
                    $netamt = $rowfiltercom['netamountpaid'];
                    $HeaderCompany = $HeaderCompany . "<td>" . $rowfiltercom['netamountpaid'] . "</td>";
                } else {
                    $netamt = 0;
                    $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
                }
                $strPaymentDate = $rowfiltercom['strPaymentDate']; 
                if ($rowfiltercom['netamountpaid'] != '') {
                    $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                    $companyWiseTotal[$iCounter] += $rowfiltercom['netamountpaid'];

//                    if ($rowapplication['balance1'] > 0) {
//                    $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
//                    $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
//                    if ($desg['bankid'] == 2) {
//                        $array[1][1] = $rowfiltercom['netamountpaid'] + $array[1][1];
//                        $array[3][1] = $rowfiltercom['netamountpaid'] + $array[3][1];
//                    } else if ($desg['bankid'] == 1) {
//                        $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
//                        $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
//                    } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
//                        $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
//                        $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
//                    }

                    // $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                    // $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
//                    if ($rowapplication['pay_cash'] == 0) {
                        // if ($desg['bankid'] == 2) {
                        //     $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                        //     $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                        // } else if ($desg['bankid'] != 2) {
                        //     $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                        //     $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                        // } 

                        if ($rowapplication['pay_cash'] == 0) {
                            if ($desg['bankid'] == 2) {
                                $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                                $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                            } else if ($desg['bankid'] == 1) {
                                $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                                $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                            //}
                            } else if ($desg['bankid'] != 2 || $desg['bankid'] != 1) {
                                $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                                $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                            }
                       } else {
                           $array[1][1] = $rowfiltercom['netamountpaid'] + $array[1][1];
                           $array[3][1] = $rowfiltercom['netamountpaid'] + $array[3][1];
                       }

                        /*else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                            $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                            $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                        }*/
//                    } 
//                    else {
//                        $array[1][1] = $rowfiltercom['netamountpaid'] + $array[1][1];
//                        $array[3][1] = $rowfiltercom['netamountpaid'] + $array[3][1];
//                    }

//                    }
                }
            }
        } else {
            $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
        }
    }

    $mailFormat = str_replace("#bank#", ucfirst(urldecode($HeaderCompany)), $mailFormat);
    $netamts = $netamt;
    $bal2 = ceil($rowBalance - $AllCompanyTotal);

    if ($bal2 > 0) {

        // $array[2][4] = $bal2 + $array[2][4];
        // $array[3][4] = $bal2 + $array[3][4];
        if ($rowapplication['pay_cash'] == 0) {
            if ($desg['bankid'] == 2) {
                $array[2][2] = $bal2 + $array[2][2];
                $array[3][2] = $bal2 + $array[3][2];
            } else if ($desg['bankid'] == 1) {
                $array[2][3] = $bal2 + $array[2][3];
                $array[3][3] = $bal2 + $array[3][3];
            } 
            else if ($desg['bankid'] != 2 || $desg['bankid'] != 1) {
                $array[2][4] = $bal2 + $array[2][4];
                $array[3][4] = $bal2 + $array[3][4];
            }
        } else {
            $array[2][1] = $bal2 + $array[2][1];
            $array[3][1] = $bal2 + $array[3][1];
        }
    } else {
        // $array[4][4] = ($bal2 * (-1)) + $array[4][4];
        if ($rowapplication['pay_cash'] == 0) {
            if ($desg['bankid'] == 2) {
                $array[4][2] = ($bal2 * (-1)) + $array[4][2];
            } else if ($desg['bankid'] == 1) {
                $array[4][3] = ($bal2 * (-1)) + $array[4][3];
            } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                $array[4][4] = ($bal2 * (-1)) + $array[4][4];
            }
        } else {
            $array[4][1] = ($bal2 * (-1)) + $array[4][1];
        }
    }
    $array[1][5] = $array[1][1] + $array[1][2] + $array[1][3] + $array[1][4];
    $array[2][5] = $array[2][1] + $array[2][2] + $array[2][3] + $array[2][4];
    $array[3][5] = $array[3][1] + $array[3][2] + $array[3][3] + $array[3][4];
    $array[4][5] = $array[4][1] + $array[4][2] + $array[4][3] + $array[4][4];
    $array[5][5] = $array[5][1] + $array[5][2] + $array[5][3] + $array[5][4];


    $array[5][1] = $array[3][1] + ($array[4][1] * (-1));
    $array[5][2] = $array[3][2] + ($array[4][2] * (-1));
    $array[5][3] = $array[3][3] + ($array[4][3] * (-1));
    $array[5][4] = $array[3][4] + ($array[4][4] * (-1));
    $array[5][5] = $array[3][5] + $array[4][5] * (-1);


//    if ($bal2 > 0) {
//        $array[2][4] = $bal2 + $array[2][4];
//        $array[3][4] = $bal2 + $array[3][4];
//        if ($desg['bankid'] == 2) {
//            $array[2][1] = $bal2 + $array[2][1];
//            $array[3][1] = $bal2 + $array[3][1];
//        } else if ($desg['bankid'] == 1) {
//            $array[2][2] = $bal2 + $array[2][2];
//            $array[3][2] = $bal2 + $array[3][2];
//        } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
//            $array[2][3] = $bal2 + $array[2][3];
//            $array[3][3] = $bal2 + $array[3][3];
//        }
//    } else {
//        $array[4][4] = ($bal2 * (-1)) + ($array[4][4] * (-1));
//        if ($desg['bankid'] == 2) {
//            $array[4][1] = ($bal2 * (-1)) + $array[4][1];
//        } else if ($desg['bankid'] == 1) {
//            $array[4][2] = ($bal2 * (-1)) + $array[4][2];
//        } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
//            $array[4][3] = ($bal2 * (-1)) + $array[4][3];
//        }
//    }
//    $array[5][1] = $array[3][1] + ($array[4][1] * (-1));
//    $array[5][2] = $array[3][2] + ($array[4][2] * (-1));
//    $array[5][3] = $array[3][3] + ($array[4][3] * (-1));
//    $array[5][4] = $array[3][4] + $array[4][4] * (-1);
    $TotalBalance2 += $bal2;

    $CompanysTotal = '';
    for ($iCounter = 0; $iCounter < count($comnymasid); $iCounter++) {
        $CompanysTotal = $CompanysTotal . "<td>" . $companyWiseTotal[$iCounter] . "</td>";
    }

    $mailFormat = str_replace("#netamountpaid#", ucfirst(urldecode(round($bal2))), $mailFormat);
    $mailFormat = str_replace("#acno#", ucfirst(urldecode($desg['accountno'])), $mailFormat);
    if($bal2 > 0){
        $mailFormat = str_replace("#date#", ucfirst(urldecode($rowapplication['strPaymentDate'])), $mailFormat);
    } else {
        $mailFormat = str_replace("#date#", ucfirst(urldecode($strPaymentDate)), $mailFormat);
    }
    
    
    $mailFormat_rows = $mailFormat_rows . $mailFormat;
    $i++;
}

$mailFormat_main = str_replace("#Company#", ucfirst(urldecode($CompanysTotal)), $mailFormat_main);
$mailFormat_main = str_replace("#Total#", ucfirst(urldecode(round($totalofTotal))), $mailFormat_main);
$mailFormat_main = str_replace("#Total FA#", ucfirst(urldecode($TotalFa)), $mailFormat_main);
$mailFormat_main = str_replace("#Total TA#", ucfirst(urldecode($TotalTa)), $mailFormat_main);
$mailFormat_main = str_replace("#Balance#", ucfirst(urldecode($Totalbalance1)), $mailFormat_main);
$mailFormat_main = str_replace("#Total Balance#", ucfirst(urldecode($TotalBalance2)), $mailFormat_main);

$mailFormat_main = str_replace("#Rate#", ucfirst(urldecode(round($Totalrate))), $mailFormat_main);
$mailFormat_main = str_replace("#working day#", ucfirst(urldecode($Totalworkingdays)), $mailFormat_main);
$mailFormat_main = str_replace("#Total OT#", ucfirst(urldecode($Totalothours)), $mailFormat_main);
$mailFormat_main = str_replace("#perent amt#", ucfirst(urldecode($TotalPresentAmount)), $mailFormat_main);
$mailFormat_main = str_replace("#OT amount#", ucfirst(urldecode($Totalotamt)), $mailFormat_main);
$mailFormat_main = str_replace("#Total Amount#", ucfirst(urldecode($Totaltotalamt)), $mailFormat_main);
$mailFormat_main = str_replace("#advt#", ucfirst(urldecode($Totaladv)), $mailFormat_main);
$mailFormat_main = str_replace("#advtTwo#", ucfirst(urldecode($TotaladvTwo)), $mailFormat_main);
$mailFormat_main = str_replace("#Total Adv Paid By Bank#", ucfirst(urldecode($TotalAdvPaidByBank)), $mailFormat_main);
$mailFormat_main = str_replace("#Total PF Amount#", ucfirst(urldecode($TotalPfAmount)), $mailFormat_main);
$mailFormat_main = str_replace("#Total ESIC Amount#", ucfirst(urldecode($TotalEsicAmount)), $mailFormat_main);

$companymasterId1 = '';
$month1 = '';
$comid1 = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.fromdate FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "' ");
while ($commaster1 = mysqli_fetch_array($comid1)) {
    $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
    $month1 = date('M-Y',strtotime($commaster1['month']));
}
$companymasterId1 = rtrim($companymasterId1, ", ");
$mailFormat_main = str_replace("#SITE#", ucfirst(urldecode($companymasterId1)), $mailFormat_main);
$mailFormat_main = str_replace("#MONTH#", ucfirst(urldecode($month1)), $mailFormat_main);



$SummeryTable = "";
$TableSummery = "";
$iCounter = 0;
$SummeryTable .= '<table width="60%" cellspacing="0" cellpadding="6" border="1" style="font-size: 20px;margin: 0 0 0 -50px; text-align : center !important; color: #000;">';
$SummeryTable .= '<tr style="font-weight: bold; font-size: 20px;text-align: center;background-color:#eee;"> <th colspan="6">SUMMARY</th></tr>';
while ($iCounter < sizeof($array)) {
    if ($iCounter == 0) {
        $SummeryTable .= '<tr style="font-weight: bold; font-size: 20px;text-align: center;background-color:#eee;">';
    } else if ($iCounter == 3) {
        $SummeryTable .= '<tr style="background-color: #ccc;font-weight: bold;">';
    } else if ($iCounter == 5) {
        $SummeryTable .= '<tr style="background-color: #ccc;font-weight: bold;">';
    } else {
        $SummeryTable .= '<tr style="text-align: right;">';
    }
    for ($jCounter = 0; $jCounter < sizeof($array[$iCounter]); $jCounter++) {
        if ($iCounter == 0) {
            $SummeryTable .= "<th>" . $array[$iCounter][$jCounter] . "</th>";
        } else {
            $SummeryTable .='<td style="text-align:right;">' . $array[$iCounter][$jCounter] . '</td>';
        }
    }
    if ($iCounter == 0) {
        $SummeryTable .= "</tr>";
    } else {
        $SummeryTable .= "</tr>";
    }
    $iCounter++;
}
$SummeryTable .= "</table>";

//$mailFormat_main = $mailFormat_main . $TableSummery;
$mailFormat_main = str_replace("#form_multi_tr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);
$mailFormat_main = str_replace("#form_table_summery#", ucfirst(urldecode($SummeryTable)), $mailFormat_main);

require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
// create new PDF document
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
$pdf->SetMargins(5, PDF_MARGIN_TOP, 5); // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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