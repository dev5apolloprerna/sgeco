<?php

//include database configuration file
include('../config.php');
include('IsLogin.php');
//$connect = new connect();
//get records from database

$HeaderCompany = "";
$companymasterId = "";
$month = '';






$query = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token']  . "' order by multicompanyid desc";

$Total = array(0, 0, 0);


$result = mysqli_query($dbconn, $query);



if (mysqli_num_rows($result) > 0) {

    $delimiter = ",";
    $filename = "multicompanyreport" . date('Y-m-d H:i:s') . ".csv";
    //create a file pointer

    $f = fopen('php://memory', 'w');
    $comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");
    // while ($commaster = mysqli_fetch_array($comid)) {
    //     $month = $commaster['month'];
    //     $companymasterId = $companymasterId . ',' . $commaster['companyname'];
    // }
    // $companymasterId = rtrim($companymasterId, ", ");
    // $companymasterId = trim($companymasterId,',');
    //set column headers
    $fields = array('Name',
        'Rate', 
        'PresentDays',
        'O.THours', 
        'PresentAmount',
        'O.TAmount',
        'TotalAmoun', 
        'Adv', 
        'Total',
        'F.A.',
        'T.A.',
        'Balance');
    while ($commaster = mysqli_fetch_array($comid)) {

     array_push($fields,$commaster['companyname']  );
 }
 $headfield = array(
    'Balance',
    'BankName',
    'Bank A/c No.',
    'PaidDate'
);
 $fields = array_merge($fields,$headfield);
 fputcsv($f, $fields, $delimiter);
    //output each row of the data, format line as csv and write to file pointer

 while ($row = mysqli_fetch_assoc($result)) {


    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $row['emp_id'] . "'"));

    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));


$comid1 = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");

    while ($commaster = mysqli_fetch_array($comid1)) {
        $month = $commaster['month'];
        $companymasterId = $companymasterId . ',' . $commaster['companyname'];
    }
    $companymasterId = rtrim($companymasterId, ", ");
    $AllCompanyTotal='';
    $bal2 = $row['balance1'] - $Total[1];
    $Total[0] = $bal2 + $Total[0];
    $comnymasid = explode(',', $companymasterId);
   
    $iCounter = 0;
    $netamt = '0';
    $AllCompanyTotal = 0;

    $lineData = array(
        $row['name'],
        $row['rate'],
        $row['workingdays'], 
        $row['othours'], 
        $row['PresentAmount'],
        $row['otamt'],  
        $row['totalamt'], 
        $row['adv'], 
        $row['total'], 
        $row['Fa'], 
        $row['Ta'], 
        $row['balance1'], 
    );
    for ($iCounter = 1; $iCounter < count($comnymasid); $iCounter++) {
       $saleryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'"));
        $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $row['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");
        
            while ($rowfiltercom = mysqli_fetch_array($comp)) {
                if ($rowfiltercom['netamountpaid'] != '') {
                    $netamt = $rowfiltercom['netamountpaid'];
                    //array_push($lineData,$commaster['companyname']  );
                } else {
                    $netamt = 0;
                }
                if ($rowfiltercom['netamountpaid'] != '') {
                    $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                    $Total[5][$iCounter] = $rowfiltercom['netamountpaid'] + $Total[5][$iCounter];
                    array_push($lineData,$$Total[5][$iCounter] );

                }
            }
        
    }
    $valuefield = array(
        round($bal2),
        $bank['bankname'],
        $desg['accountno'],
        $row['date']
    );
    $lineData = array_merge($lineData,$valuefield);
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