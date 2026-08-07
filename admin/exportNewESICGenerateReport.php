<?php

ob_start();

include('../config.php');
?>
<?php

$where = "where 1=1";

if (isset($_REQUEST['month'])) {
    if ($_REQUEST['month'] != '') {
        //$where.=" AND MONTH(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['month']."'";
        $where.=" AND (MONTH(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['month']."' or MONTH(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['month']."' or MONTH(str_to_date(dateofjoining,'%d/%m/%Y'))='".$_REQUEST['month']."')";
    }
}

if (isset($_REQUEST['Year'])) {
    if ($_REQUEST['Year'] != '') {
        //$where.=" and YEAR(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['Year']."'";
        $where.=" and (YEAR(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['Year']."' or YEAR(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['Year']."'  or YEAR(str_to_date(dateofjoining,'%d/%m/%Y'))='".$_REQUEST['Year']."')";
    }
}
$salaryMonth = $_REQUEST['month'] . '/'. $_REQUEST['Year'];

$sql1 = "SELECT * FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0'  and  istatus='1'";
        // UNION All
        // select * from employee where employee.ecsno='' and isDelete='0' and istatus='1'  order by employeecode DESC";

$result1 = mysqli_query($dbconn, $sql1);

if (mysqli_num_rows($result1) > 0) {
    
    $hearder = "SR. No."
    . "\t" . "Name As Per Aadhar"
    . "\t" . "MARRIED/ UNMARRIED"
    . "\t" . "FATHER NAME"
    . "\t" . "HUSBAND NAME"
    . "\t" . "DOB"
    . "\t" . "DOJ"
    . "\t" . "Present Address"
    . "\t" . "Permanat Address"
    . "\t" . "Aadhar No."
    . "\t" . "Nominee name."
    . "\t" . "Relation"
    . "\t" . "Nominee Aadhar"
    . "\t" . "Aadhar No."
    . "\t" . "Family Member Name"
    . "\t" . "Relation"
    . "\t" . "Mobile No."
    . "\t" . "Bank Name"
    . "\t" . "Account No."
    . "\t" . "IFSC Code"
    . "\t" . "Rate of Wage";
    
    $hearder .=  "\n";
    $data = "";
    $iCounter = 1;
    while ($rows = mysqli_fetch_assoc($result1)) {
        $bank = mysqli_fetch_array(mysqli_query($dbconn,"SELECT bankname FROM `bankmaster` where bankmasterId ='".$rows['bankid']."' "));
        $sql = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate FROM `salarydetails` where  emp_id='" . $rows['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
        $result = mysqli_fetch_assoc($sql);
        $skillrate = "";
        if(isset($result['skillrate'])){
            $skillrate = $result['skillrate'];
        }
        
        $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `tempFamilyDetails` where iTempEmpId='".$rowfilter['iTempEmpId']."' order by iTempFamilyDetailsId asc limit 1");
        $strFamilyDetails = "";
        $strRelation = "";
        if(mysqli_num_rows($filterFamilyRelation) == 1){
            $rowFamilyRelation = mysqli_fetch_assoc($filterFamilyRelation);
            $filterRelationStr = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0 and iRelation='".$rowFamilyRelation['iRelation']."'"));
            $strFamilyDetails = $rowFamilyRelation['strFamilyDetails'];
            $strRelation = $filterRelationStr['strRelation'];
        }
                                    
        $data .= $iCounter
        . "\t" . trim(ucwords(strtolower($rows['emp_name'])))
        . "\t" . trim(ucwords(strtolower($rows['strMaritalStatus'])))
        . "\t" . trim(ucwords(strtolower($rows['strFatherName'])))
        . "\t" . ""
        . "\t" . trim(date('d-m-Y',strtotime($rows['dateofbirth'])))
        . "\t" . trim(date('d-m-Y',strtotime($rows['dateofjoining'])))
        . "\t" . trim(ucwords(strtolower($rows['address'])))
        . "\t" . trim(ucwords(strtolower($rows['strPermanentAddress'])))
        . "\t" . trim($rows['adharcard'])
        . "\t" . trim(ucwords(strtolower($rows['strNomineeName'])))
        . "\t" . trim(ucwords(strtolower($rows['strNomineeRelation'])))
        . "\t" . trim($rows['strNomineeAdharNo'])
        . "\t" . trim($rows['adharcard'])
        . "\t" . trim(ucwords(strtolower($rows['strFamilyDetails'])))
        . "\t" . trim(ucwords(strtolower($rows['strRelation'])))
        . "\t" . trim($rows['mno'])
        . "\t" . trim(ucwords(strtolower($bank['bankname'])))
        . "\t" . trim(str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$rows['accountno']))))
        . "\t" . trim($rows['ifsccode'])
        . "\t" . $skillrate;
        $data .=  "\n";   
        $iCounter++;
        
    }
    
    $filename = 'New_ESIC_Generate_'.date('dmyHis') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-disposition: attachment; filename=" . $filename);
    ob_end_clean();
    echo chr(255) . chr(254) .mb_convert_encoding($hearder, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    
} else {
    header('location:New_ESIC_Generate.php');
}
exit;
?>