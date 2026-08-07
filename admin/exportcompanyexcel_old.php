<?php

error_reporting(E_ALL);
ob_start();
ob_clean();
//require_once('tcpdf/config/tcpdf_config.php');
//require_once('tcpdf/tcpdf.php');
include('../config.php');

$HeaderCompany = "";
$companymasterId = "";
$month = '';
$comnymasid = array();

$i = 1;
$delimiter = ",";
$filename = "multicompanyreport" . date('Y-m-d H:i:s') . ".csv";
//create a file pointer

$f = fopen('php://memory', 'w');
$comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");
$month = "";
$companyname = "";
while ($companymaster = mysqli_fetch_array($comid)) {
    $month = $companymaster['month'];
    $companyname = $companymaster['companyname'];
}
$companyname = rtrim($companyname, ',');

$fields = array(
    'SUB:',
    'PAYMENT SHEET',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '');
fputcsv($f, $fields, $delimiter);
$fields = array(
    'SITE:',
    $companyname,
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '');
fputcsv($f, $fields, $delimiter);

$fields = array(
    'MONTH',
    $month,
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    'Date',
    date('d-m-Y'));
fputcsv($f, $fields, $delimiter);

$fields = array(
    'PF.NO.',
    'ESIC No',
    'Name',
    'Rate',
    'Present Days',
    'O.T Hours',
    'Present Amount',
    'O.T Amount',
    'Total Amount',
    'Adv',
    'ADV TWO',
    'Total',
    'F.A.',
    'T.A.',
    'Balance');


$comid1 = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");

while ($commaster = mysqli_fetch_array($comid1)) {
    $month = $commaster['month'];
    array_push($fields, $commaster['companyname']);
    array_push($comnymasid, $commaster['companymasterId']);
}
$headfield = array(
    'Total Balance',
    'Bank Name',
    'Bank A/c No.',
    'Paid Date'
);
$fields = array_merge($fields, $headfield);
fputcsv($f, $fields, $delimiter);

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

$query = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token'] . "' order by multicompanyid desc";
$Total = array("Total", "", "", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0");
$Total[0] = "Total";


$result = mysqli_query($dbconn, $query);
$PaidcompanyWiseTotal = array();
$balance2 = 0;
$TotalBalance2 = array("0");
while ($row = mysqli_fetch_assoc($result)) {
    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $row['emp_id'] . "'"));
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));
    $HeaderCompany = "0";
    //$comnymasid = explode(',', $companymasterId);
    $iCounter = 0;
    $netamt = '0';
    $AllCompanyTotal = 0;
    $EmployeeSalary = array();
    $bal2 = 0;
    $companyWiseTotal = array("0", "0");
//    $Total[0] = $bal2 + $Total[0];
    $CompanysTotal = '';
    $lineData = array(
        $desg['pfcode'],
        $desg['ecsno'],
        $row['name'],
        $row['rate'],
        $row['workingdays'],
        $row['othours'],
        $row['PresentAmount'],
        $row['otamt'],
        $row['totalamt'],
        $row['adv'],
        $row['adv_two'],
        $row['total'],
        $row['Fa'],
        $row['Ta'],
        $row['balance1']
    );
    $Total[3] += $row['rate'];
    $Total[4] += $row['workingdays'];
    $Total[5] += $row['othours'];
    $Total[6] += $row['PresentAmount'];
    $Total[7] += $row['otamt'];
    $Total[8] += $row['totalamt'];
    $Total[9] += $row['adv'];
    $Total[10] += $row['adv_two'];
    $Total[11] += $row['total'];
    $Total[12] += $row['Fa'];
    $Total[13] += $row['Ta'];
    $Total[14] += $row['balance1'];
//    $Total=array_merge($Total, $PaidcompanyWiseTotal);


    for ($iCounter = 0; $iCounter < sizeof($comnymasid); $iCounter++) {

        $Query = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'";
        $saleryid = mysqli_fetch_array(mysqli_query($dbconn, $Query));

        $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $row['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");
        if (mysqli_num_rows($comp) > 0) {
            while ($rowfiltercom = mysqli_fetch_array($comp)) {
                if ($rowfiltercom['netamountpaid'] != '') {
                    $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                    $companyWiseTotal[$iCounter] = $companyWiseTotal[$iCounter] + $rowfiltercom['netamountpaid'];
//                    if ($companyWiseTotal[$iCounter] == '') {
//                        $companyWiseTotal[$iCounter] = 0;
//                        array_sum($companyWiseTotal[$iCounter]);
//                    } else {
//                        array_sum($companyWiseTotal[$iCounter]);
//                    }

                    $array[1][5] = $rowfiltercom['netamountpaid'] + $array[1][5];
                    $array[3][5] = $rowfiltercom['netamountpaid'] + $array[3][5];
                    if ($desg['bankid'] == 2) {
                        $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                        $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                    } else if ($desg['bankid'] == 1) {
                        $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                        $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                    } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                        $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                        $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                    }

                    array_push($PaidcompanyWiseTotal, $companyWiseTotal[$iCounter]);
                    array_push($EmployeeSalary, $rowfiltercom['netamountpaid']);
                } else {
                    array_push($EmployeeSalary, 0);
//                    array_push($PaidcompanyWiseTotal,0);
                }
            }
        } else {
            array_push($EmployeeSalary, 0);
//            array_push($PaidcompanyWiseTotal,0);
        }
    }

    $bal2 = $row['balance1'] - $AllCompanyTotal;
    $bal2 = round($bal2);
    
    if ($bal2 > 0) {

        $array[2][5] = $bal2 + $array[2][5];
        $array[3][5] = $bal2 + $array[3][5];
        if ($rowapplication['pay_cash'] == 0) {
            if ($desg['bankid'] == 2) {
                $array[2][2] = $bal2 + $array[2][2];
                $array[3][2] = $bal2 + $array[3][2];
            } else if ($desg['bankid'] == 1) {
                $array[2][3] = $bal2 + $array[2][3];
                $array[3][3] = $bal2 + $array[3][3];
            } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                $array[2][4] = $bal2 + $array[2][4];
                $array[3][4] = $bal2 + $array[3][4];
            }
        } else {
            $array[2][1] = $bal2 + $array[2][1];
            $array[3][1] = $bal2 + $array[3][1];
        }
    } else {
        $array[4][5] = ($bal2 * (-1)) + $array[4][5];
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
    $array[5][1] = $array[3][1] + ($array[4][1] * (-1));
    $array[5][2] = $array[3][2] + ($array[4][2] * (-1));
    $array[5][3] = $array[3][3] + ($array[4][3] * (-1));
    $array[5][4] = $array[3][4] + ($array[4][4] * (-1));
    $array[5][5] = $array[3][5] + $array[4][5] * (-1);
   
   
    
    $TotalBalance2[0] += $bal2;
//    array_push($TotalBalance2, $balance2);

    $lineData = array_merge($lineData, $EmployeeSalary);
    $bankDetails = array(
        $bal2,
        $bank['bankname'],
        $desg['accountno'],
        $row['date']);

    $lineData = array_merge($lineData, $bankDetails);
    fputcsv($f, $lineData, $delimiter);
    $i++;
//    print_r($PaidcompanyWiseTotal);
}

$SummeryTable = "";
$TableSummery = "";
$iCounter = 0;
$SummeryTable .= '<table width="60%" cellspacing="0" cellpadding="5" border="1" style="font-size: 20px;margin: 0 0 0 -50px; text-align : center !important; color: #000;">';
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

$Total = array_merge($Total, $PaidcompanyWiseTotal);

$Total = array_merge($Total, $TotalBalance2);
//print_r($Total);
//exit;
fputcsv($f, $Total, $delimiter);

//fputcsv($f, $SummeryTable);
//move back to beginning of file
fseek($f, 0);


header('Content-Type: text/csv');
$filename = "multicompanyreport" . date('Y-m-d H:i:s') . ".csv";
header('Content-Disposition: attachment; filename="' . $filename . '";');

fpassthru($f);
?>