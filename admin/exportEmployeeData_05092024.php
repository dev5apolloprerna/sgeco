<?php

ob_start();

include('../config.php');
?>
<?php

$where = "where 1=1";

if (isset($_REQUEST['Search_Txt'])) {
    if ($_REQUEST['Search_Txt'] != '') {

        $where.=" and  emp_name like '%$_REQUEST[Search_Txt]%'";
    }
}

if (isset($_REQUEST['Search_Aadhar'])) {
    if ($_REQUEST['Search_Aadhar'] != '') {
        $where.=" and  adharcard like '%" . $_REQUEST['Search_Aadhar'] . "%' ";
    }
}
// if (isset($_REQUEST['token'])) {
//     if ($_REQUEST['token'] != '') {
//         $where.=" and iType= '".$_REQUEST['token']."' ";
//     }
// }
//$sql1 = "SELECT * FROM `employee`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
$sql1 = "SELECT iExportEmpId,pfcode,employeeId,emp_name,employeecode,ecsno,uan,strFatherName,dateofbirth,dateofjoining,strPermanentAddress,address,pancard,adharcard,bankid,accountno,ifsccode,mno,strMaritalStatus,strNomineeName,strNomineeRelation,strNomineeAdharNo,strFamilyDetails,strRelation,strExitDate,employee.istatus,isExitEmployee FROM `employee` inner join exportemployeelist on exportemployeelist.iEmpoyeeId=employee.employeeId  " . $where . " and iType= '1' and employee.isDelete='0' and  employee.istatus='1'
         UNION ALL
         SELECT iExportEmpId,iTempEmpId as employeeId,pfcode,emp_name,employeecode,ecsno,uan,strFatherName,dateofbirth,dateofjoining,strPermanentAddress,address,pancard,adharcard,bankid,accountno,ifsccode,mno,strMaritalStatus,strNomineeName,strNomineeRelation,strNomineeAdharNo,strFamilyDetails,strRelation,'' as strExitDate,tempEmpolyeeMaster.istatus,0 as isExitEmployee FROM `tempEmpolyeeMaster` inner join exportemployeelist on tempEmpolyeeMaster.iTempEmpId=exportemployeelist.iEmpoyeeId  " . $where . " and iType= '2'  and tempEmpolyeeMaster.isDelete='0' and  tempEmpolyeeMaster.istatus='1'";
               
$result1 = mysqli_query($dbconn, $sql1);

if (mysqli_num_rows($result1) > 0) {
    $hearder = "PF No."
    . "\t" . "Employee Code"
    . "\t" . "ESIC No."
    . "\t" . "UAN No."
    . "\t" . "Name As per Aadhar"
    . "\t" . "Father Name"
    . "\t" . "DOB"
    . "\t" . "DOJ"
    . "\t" . "Permanent Address"
    . "\t" . "Present Address"
    . "\t" . "Pan No."
    . "\t" . "Aadhar No."
    . "\t" . "Bank Name"
    . "\t" . "Account No."
    . "\t" . "IFSC Code"
    . "\t" . "Mobile No."
    . "\t" . "Marital Status"
    . "\t" . "Nominee Name"
    . "\t" . "Nominee Relation"
    . "\t" . "Nominee Adhar No."
    . "\t" . "Family Details"
    . "\t" . "Relation"
    . "\t" . "Status"
    . "\t" . "Is  Working"
    . "\t" . "Date of Exit";
    $hearder .=  "\n";
    $data = "";
    while ($rows = mysqli_fetch_assoc($result1)) {
        $bank = mysqli_fetch_array(mysqli_query($dbconn,"SELECT bankname FROM `bankmaster` where bankmasterId ='".$rows['bankid']."' "));
        $strStatus = "";
        if($rows['istatus'] == 0){
            $strStatus = "Block";
        } else {
            $strStatus = "Active";
        }
        
        $isWorking = "";
        if($rows['isExitEmployee'] == 1){
            $isWorking = "No";
        } else {
            $isWorking = "Yes";
        }
        $data .= trim($rows['pfcode'])
        . "\t" . trim($rows['employeecode'])
        . "\t" . trim($rows['ecsno'])
        . "\t" . trim($rows['uan'])
        . "\t" . trim(ucwords(strtolower($rows['emp_name'])))
        . "\t" . trim(ucwords(strtolower($rows['strFatherName'])))
        . "\t" . trim($rows['dateofbirth'])
        . "\t" . trim($rows['dateofjoining'])
        . "\t" . trim(ucwords(strtolower(preg_replace('/\r\n|\n\r|\n|\r/', '',$rows['strPermanentAddress']))))
        . "\t" . trim(ucwords(strtolower(preg_replace('/\r\n|\n\r|\n|\r/', '',$rows['address']))))
        . "\t" . trim($rows['pancard'])
        . "\t" . trim($rows['adharcard'])
        . "\t" . trim(ucwords(strtolower($bank['bankname'])))
        . "\t" . trim($rows['accountno'])
        . "\t" . trim($rows['ifsccode'])
        . "\t" . trim($rows['mno'])
        . "\t" . trim(ucwords(strtolower($rows['strMaritalStatus'])))
        . "\t" . trim(ucwords(strtolower($rows['strNomineeName'])))
        . "\t" . trim(ucwords(strtolower($rows['strNomineeRelation'])))
        . "\t" . trim($rows['strNomineeAdharNo'])
        . "\t" . trim(ucwords(strtolower($rows['strFamilyDetails'])))
        . "\t" . trim(ucwords(strtolower($rows['strRelation'])))
        . "\t" . trim($strStatus)
        . "\t" . trim($isWorking)
        . "\t" . trim($rows['strExitDate']);
        $data .=  "\n";   
        $iCounter++;
        
    }
    
    $filename = 'EmployeeReportDownloadExcel'.date('dmyHis') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-disposition: attachment; filename=" . $filename);
    ob_end_clean();
    echo chr(255) . chr(254) .mb_convert_encoding($hearder, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    
} else {
    header('location:Employee.php');
}
exit;
?>