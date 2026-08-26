<?php

//include database configuration file
include('../config.php');
include('IsLogin.php');
//$connect = new connect();
//get records from database

$HeaderCompany = "";
$companymasterId = '0';
$month = '';
$comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");
while ($commaster = mysqli_fetch_array($comid)) {
    $month = $commaster['month'];
    $companymasterId = $companymasterId . ',' . $commaster['companymasterId'];

    $HeaderCompany = $HeaderCompany . "\t" . $commaster['companyname'] . " ";
}
$companymasterId = rtrim($companymasterId, ", ");





$query = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token']  . "' order by multicompanyid desc";

$Total = array(0, 0, 0);


$result = mysqli_query($dbconn, $query);



if (mysqli_num_rows($result) > 0) {

    $delimiter = ",";
    $filename = "multicompanyreport" . date('Y-m-d H:i:s') . ".csv";
    //create a file pointer

    $f = fopen('php://memory', 'w');
    //set column headers
    $fields = array('Name','Rate', 'PresentDays', 'O.THours',  'PresentAmount','O.TAmount', 'TotalAmount',  'Adv',  'Total', 'Advance Paid By Bank', 'PF Amount', 'ESIC Amount', 'F.A.', 'T.A.',  'Balance',$HeaderCompany ,'Balance','BankName','Bank A/c No.','PaidDate');
    fputcsv($f, $fields, $delimiter);
    //output each row of the data, format line as csv and write to file pointer

    while ($row = mysqli_fetch_assoc($result)) {


        $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $row['emp_id'] . "'"));

        $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));
        /*$mailFormat = str_replace("#bank#", ucfirst(urldecode($HeaderCompany)), $mailFormat);*/


        $HeaderCompany = "";
        $comnymasid = explode(',', $companymasterId);
        $iCounter = 0;
        $netamt = '0';
        $AllCompanyTotal = 0;
        for ($iCounter = 1; $iCounter < count($comnymasid); $iCounter++) {

            $saleryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'"));
/*
print_r($comnymasid[$iCounter]);
exit;*/
            $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $row['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");
/*
print_r($comnymasid[$iCounter] );
exit;*/
/*print_r("SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $row['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");


exit;*/

            if (mysqli_num_rows($comp) > 0) {
                while ($rowfiltercom = mysqli_fetch_array($comp)) {


                    if ($rowfiltercom['netamountpaid'] != '') {

/*print_r($rowfiltercom['netamountpaid']);
exit;
         */               $netamt = $rowfiltercom['netamountpaid'];


                        $HeaderCompany = $HeaderCompany . "<td>" . $rowfiltercom['netamountpaid'] . "</td>";

/*print_r($HeaderCompany);
exit;*/



                    } else {
                        $netamt = 0;
                        $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
                    }


                    if ($rowfiltercom['netamountpaid'] != '') {
/*print_r($rowfiltercom['netamountpaid']);
exit;
         */               
                        $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];


                        $Total[1]=$rowfiltercom['netamountpaid']+ $Total[1];

                      /*  print_r($Total[1]);
                        exit;
*/
                    }
                }
            } else {

                $HeaderCompany = $HeaderCompany . "<td>" . '0' . "</td>";
            }
        }


        $AllCompanyTotal='';
        $bal2 = $row['balance1'] - $Total[1];
        $Total[0] = $bal2 + $Total[0];


        
        $lineData = array(
            ucwords(strtolower($row['name'])),
            $row['rate'],
            $row['workingdays'], 
            $row['othours'], 
            $row['PresentAmount'],
            $row['otamt'],  
            $row['totalamt'], 
            $row['adv'], 
            $row['total'], 
            $row['advance_paid_by_bank'],
            $row['pf_amount'],
            $row['esic_amount'],
            $row['Fa'], 
            $row['Ta'], 
            $row['balance1'], 
            $Total[1], 
            round($bal2),
            ucwords(strtolower($bank['bankname'])),
            $desg['accountno'],
            $row['date']
        );
        fputcsv($f, $lineData, $delimiter);
    }


    //move back to beginning of file
    fseek($f, 0);

    //set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    //output all remaining data on a file pointer
    fpassthru($f);

}else {
    //header('location:view-full-multicompany-SalaryDetails.php?tokan=2');
}
exit;
?>