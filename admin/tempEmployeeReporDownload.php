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
if (isset($_REQUEST['Search_Aadhar'])) {
    if ($_REQUEST['Search_Aadhar'] != '') {
        $where.=" and  adharcard like '%" . $_REQUEST['Search_Aadhar'] . "%' ";
    }
}
if (isset($_REQUEST['strFromDate'])) {
    if ($_REQUEST['strFromDate'] != '') {
        $where.=" and STR_TO_DATE(strEntryDate,'%d-%m-%Y') >= STR_TO_DATE('".$_REQUEST['strFromDate']."','%d-%m-%Y')";
    }
}
if (isset($_REQUEST['strToDate'])) {
    if ($_REQUEST['strToDate'] != '') {
        $where.=" and STR_TO_DATE(strEntryDate,'%d-%m-%Y') <= STR_TO_DATE('". $_REQUEST['strToDate'] ."','%d-%m-%Y')";
    }
}
$sql1 = "SELECT * FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0'  and  istatus='1' order by iTempEmpId desc";

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
    . "\t" . "Created By"
    . "\t" . "Updated By";
    $hearder .=  "\n";
    $data = "";
    while ($rows = mysqli_fetch_assoc($result1)) {
        $bank = mysqli_fetch_array(mysqli_query($dbconn,"SELECT bankname FROM `bankmaster` where bankmasterId ='".$rows['bankid']."' "));
        $bankname = "";
        if(isset($bank['bankname']) && $bank['bankname'] != ""){
            $bankname = $bank['bankname'];    
        }
        
        $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `tempFamilyDetails` where iTempEmpId='".$rows['iTempEmpId']."' order by iTempFamilyDetailsId asc limit 1");
        $strFamilyDetails = "";
        $strRelation = "";
        if(mysqli_num_rows($filterFamilyRelation) == 1){
            $rowFamilyRelation = mysqli_fetch_assoc($filterFamilyRelation);
            $filterRelationStr = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0 and iRelation='".$rowFamilyRelation['iRelation']."'"));
            $strFamilyDetails = $rowFamilyRelation['strFamilyDetails'];
            $strRelation = $filterRelationStr['strRelation'];
        }
        $CreatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT strUserName FROM `androidusermaster` where iAndroidUserId='".$rows['iCreatedBy']."' and iStatus=1 and isDelete=0"));
        if($rows['iMovedBy'] == ""){
            $UpdatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT strUserName FROM `androidusermaster` where iAndroidUserId='".$rows['iUpdatedBy']."' and iStatus=1 and isDelete=0")); 
        } else {
            $UpdatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT username AS strUserName FROM `admin` where id='".$rows['iMovedBy']."' and istatus=1 and isDelete=0"));
        }
        
        $data .= trim($rows['pfcode'])
        . "\t" . trim($rows['employeecode'])
        . "\t" . trim($rows['ecsno'])
        . "\t" . trim($rows['uan'])
        . "\t" . trim($rows['emp_name'])
        . "\t" . trim($rows['strFatherName'])
        . "\t" . trim($rows['dateofbirth'])
        . "\t" . trim($rows['dateofjoining'])
        . "\t" . trim($rows['strPermanentAddress'])
        . "\t" . trim($rows['address'])
        . "\t" . trim($rows['pancard'])
        . "\t" . trim($rows['adharcard'])
        . "\t" . trim($bankname)
        . "\t" . trim($rows['accountno'])
        . "\t" . trim($rows['ifsccode'])
        . "\t" . trim($rows['mno'])
        . "\t" . trim($rows['strMaritalStatus'])
        . "\t" . trim($rows['strNomineeName'])
        . "\t" . trim($rows['strNomineeRelation'])
        . "\t" . trim($rows['strNomineeAdharNo'])
        . "\t" . trim($strFamilyDetails)
        . "\t" . trim($strRelation)
        . "\t" . trim($CreatedBy['strUserName'])
        . "\t" . trim($UpdatedBy);
        $data .=  "\n";   

    }
    
    $filename = 'TempEmployeeReportDownloadExcel'.date('dmyHis') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-disposition: attachment; filename=" . $filename);
    ob_end_clean();
    echo chr(255) . chr(254) .mb_convert_encoding($hearder, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    
} else {
    header('location:TempEmployeeList.php');
}
exit;
?>