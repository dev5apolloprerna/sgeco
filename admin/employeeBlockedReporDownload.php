<?php

ob_start();

include('../config.php');
?>
<?php

$where = "where 1=1";

// if (isset($_REQUEST['Search_Txt'])) {
//     if ($_REQUEST['Search_Txt'] != '') {

//         $where.=" and  emp_name like '%$_REQUEST[Search_Txt]%'";
//     }
// }

// if (isset($_REQUEST['Search_Aadhar'])) {
//     if ($_REQUEST['Search_Aadhar'] != '') {
//         $where.=" and  adharcard like '%" . $_REQUEST['Search_Aadhar'] . "%' ";
//     }
// }

$sql1 = "SELECT * FROM `employee`  " . $where . " and isDelete='0' and istatus=0  order by employeecode desc";
$result1 = mysqli_query($dbconn, $sql1);

/*$filename = 'EmployeeReportDownloadExcel.xls';

header("Content-Type: application/vnd.ms-excel");
header("Content-disposition: attachment; filename=" . $filename);

ob_end_clean();

echo
"PF Code"
 . "\t Employee Code"
 . "\t ESIC No."
 . "\t Name"
 . "\t Pan Card"
 . "\t Driving License"
 . "\t Election Card"
 . "\t Adharcard"
 . "\t Passport"
 . "\t Other"
 . "\t Date of Birth"
 . "\t IFSC Code"
 . "\t Bank"
 . "\t UAN"
 . "\t Mobile No"
 . "\t Date Of Joining"
 . "\n";
$i = 1;
while ($rows = mysqli_fetch_array($result1)) {
    echo
    $rows['pfcode']
    . "\t" . trim($rows['employeecode'])
    . "\t" . trim($rows['ecsno'])
    . "\t" . trim($rows['emp_name'])
    . "\t" . trim($rows['pancard'])
    . "\t" . trim($rows['drivinglicense'])
    . "\t" . trim($rows['electioncard'])
    . "\t" . trim($rows['adharcard'])
    . "\t" . trim($rows['passport'])
    . "\t" . trim($rows['other'])
    . "\t" . trim($rows['dateofbirth'])
    . "\t" . trim($rows['ifsccode'])
    . "\t" . trim($rows['bankid'])
    . "\t" . trim($rows['uan'])
    . "\t" . trim($rows['mno'])
    . "\t" . trim($rows['dateofjoining'])
    . "\n";
    $i++;*/
//}

    //$query = mysqli_query($dbconn, $filterstr); . "\t" . "Other Documents"
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
    
    . "\t" . "Emergency Contact No"
    . "\t" . "Qualification"
    . "\t" . "Experience"
    . "\t" . "Married Date"
    . "\t" . "Son"
    . "\t" . "Doughter"
    
    . "\t" . "Family Details"
    . "\t" . "Relation"
    . "\t" . "Created By"
    . "\t" . "Updated By"
    . "\t" . "Status"
    // . "\t" . "Is  Working"
    . "\t" . "Date of Exit";
    // . "\t" . "Driving License"
    // . "\t" . "Election Card"
    // . "\t" . "Passport"
    //$hearder .= "<b>".$hearder."</b>";
    $hearder .=  "\n";
    $data = "";
    while ($rows = mysqli_fetch_assoc($result1)) {
        $bank = mysqli_fetch_array(mysqli_query($dbconn,"SELECT bankname FROM `bankmaster` where bankmasterId ='".$rows['bankid']."' "));
        $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `EmployeeFamilyDetails` where iEmpId='".$rows['employeeId']."' order by iEmpFamilyDetailsId asc limit 1");
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
        
        $strStatus = "";
        // if($rows['istatus'] == 0){
        //     $strStatus = "Block";
        // } else {
        //     $strStatus = "Active";
        // }
        if($rows['istatus'] == 0){
            $strStatus = "Block";
        } else if($rows['isExitEmployee'] == 1){
            $strStatus = "Exit";
        } else {
            $strStatus = "Active";
        }
        
        $isWorking = "";
        if($rows['isExitEmployee'] == 1){
            $isWorking = "No";
        } else {
            $isWorking = "Yes";
        }
        
        /*$data .= trim($rows['pfcode'])
        . "\t" . trim($rows['employeecode'])
        . "\t" . trim($rows['ecsno'])
        . "\t" . trim($rows['uan'])
        . "\t" . trim($rows['emp_name'])
        . "\t" . trim($rows['strFatherName'])
        . "\t" . trim($rows['dateofbirth'])
        . "\t" . trim($rows['dateofjoining'])
        . "\t" . trim(preg_replace('/\r\n|\n\r|\n|\r/', '',$rows['strPermanentAddress']))
        . "\t" . trim(preg_replace('/\r\n|\n\r|\n|\r/', '',$rows['address']))
        . "\t" . trim($rows['pancard'])
        . "\t" . trim($rows['adharcard'])
        
        . "\t" . trim($bank['bankname'])
        . "\t" . trim($rows['accountno'])
        . "\t" . trim($rows['ifsccode'])
        . "\t" . trim($rows['mno'])
        . "\t" . trim($rows['strMaritalStatus'])
        . "\t" . trim($rows['strNomineeName'])
        . "\t" . trim($rows['strNomineeRelation'])
        . "\t" . trim($rows['strNomineeAdharNo'])
        
        . "\t" . trim($rows['strEmergencyContactNo'])
        . "\t" . trim($rows['strQualification'])
        . "\t" . trim($rows['strExperience'])
        . "\t" . trim($rows['strMarriedDate'])
        . "\t" . trim($rows['iSon'])
        . "\t" . trim($rows['iDoughter'])
        
        . "\t" . trim($strFamilyDetails)
        . "\t" . trim($strRelation)
        . "\t" . trim($CreatedBy['strUserName'])
        . "\t" . trim($UpdatedBy)
        . "\t" . trim($strStatus)
        . "\t" . trim($isWorking)
        . "\t" . trim($rows['strExitDate']);
        $data .=  "\n";   */
        $data .= implode("\t", [
            trim($rows['pfcode']),
            trim($rows['employeecode']),
            trim($rows['ecsno']),
            trim($rows['uan']),
            trim($rows['emp_name']),
            trim($rows['strFatherName']),
            trim($rows['dateofbirth']),
            trim($rows['dateofjoining']),
            trim(preg_replace('/\r\n|\n\r|\n|\r/', '',$rows['strPermanentAddress'])),
            trim(preg_replace('/\r\n|\n\r|\n|\r/', '',$rows['address'])),
            trim($rows['pancard']),
            trim($rows['adharcard']),
            
            trim($bank['bankname']),
            trim($rows['accountno']),
            trim($rows['ifsccode']),
            trim($rows['mno']),
            trim($rows['strMaritalStatus']),
            trim($rows['strNomineeName']),
            trim($rows['strNomineeRelation']),
            trim($rows['strNomineeAdharNo']),
            
            trim($rows['strEmergencyContactNo']),
            trim($rows['strQualification']),
            trim($rows['strExperience']),
            trim($rows['strMarriedDate']),
            trim($rows['iSon']),
            trim($rows['iDoughter']),
            
            trim($strFamilyDetails),
            trim($strRelation),
            trim($CreatedBy['strUserName']),
            $UpdatedBy,
            trim($strStatus),
            // trim($isWorking),
            trim($rows['strExitDate'])
            ]) . "\n";
        $iCounter++;
        
        // $data .= "\t" . trim($rows['drivinglicense']);
        // $data .= "\t" . trim($rows['electioncard']);
        // $data .= "\t" . trim($rows['other']); . "\t" . trim($rows['passport'])
    }
    
    $filename = 'EmployeeBlockedReportDownloadExcel'.date('dmyHis') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-disposition: attachment; filename=" . $filename);
    ob_end_clean();
    echo chr(255) . chr(254) .mb_convert_encoding($hearder, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    
    // $delimiter = ',';
    // $filename = "EmployeeReportDownloadExcel.csv";
    
    // //create a file pointer
    // $f = fopen('php://memory', 'w');
    // //set column headers
    // $fields = array('PF Code','Employee Code','ESIC No.','Name','Pan Card','Driving License','Election Card','Adharcard','Passport','Other','Date of Birth','Account No.','IFSC Code','Bank','UAN','Mobile No','Date Of Joining');
    // fputcsv($f, $fields, $delimiter);
    // //output each row of the data, format line as csv and write to file pointer
    // $i = 1;
    // /*$Total = array("Total", "-", 0, 0, 0, 0, 0,0);
    // $Total[0] = "Total";
    // $Total[1] = "-";
    // $Total[2] = "-";*/

    // while ($rows = mysqli_fetch_assoc($result1)) {
    //     $bank = mysqli_fetch_array(mysqli_query($dbconn,"SELECT bankname FROM `bankmaster` where bankmasterId ='".$rows['bankid']."' "));
    //     $lineData = array(trim($rows['pfcode']),trim($rows['employeecode']), trim($rows['ecsno']),trim($rows['emp_name']),trim($rows['pancard']),trim($rows['drivinglicense']),trim($rows['electioncard']),
    //     trim($rows['adharcard']),trim($rows['passport']),trim($rows['other']),trim($rows['dateofbirth']),trim($rows['accountno']),trim($rows['ifsccode']),trim($bank['bankname']),trim($rows['uan']),
    //     trim($rows['mno']),trim($rows['dateofjoining']));
    //     fputcsv($f, $lineData, $delimiter);
    //     $i++;
    // }
    // /*fputcsv($f, $Total, $delimiter);*/
    // fseek($f, 0);

    // header('Content-Type: text/csv');
    // header('Content-Disposition: attachment; filename="' . $filename . '";');

    // fpassthru($f);
} else {
    header('location:Block_Report.php');
}
exit;
?>