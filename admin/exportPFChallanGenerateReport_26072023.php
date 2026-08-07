<?php

ob_start();

include('../config.php');
?>
<?php

$where = "where 1=1";

if (isset($_REQUEST['month'])) {
    if ($_REQUEST['month'] != '') {
        //$where.=" AND MONTH(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['month']."'";
        $where.=" AND MONTH(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['month']."'";
    }
}

if (isset($_REQUEST['Year'])) {
    if ($_REQUEST['Year'] != '') {
        // $where.=" and YEAR(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['Year']."'";
        $where.=" and YEAR(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['Year']."'";
    }
}

$salaryMonth = $_REQUEST['month'] . '/'. $_REQUEST['Year'];

$sql1 = "SELECT * FROM `employee`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
$result1 = mysqli_query($dbconn, $sql1);

if (mysqli_num_rows($result1) > 0) {
    
    $hearderTitle = ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . "FOR EPF & ESIC ONLY"
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . "";
    
    $companyTitle =  "\t" . "SHREE GANESH ENGINEERING CO. \n";
    $month =  "\t" . "Employee List of PF and ESIC For the Month of: ". $_REQUEST['month'] ."-".$_REQUEST['Year']." - GSFC-Vadodara,RIL,Kribhco \n";
    
    $hearder = "SR. No."
    . "\t" . "NAME"
    . "\t" . "P.F. A/c No."
    . "\t" . "UAN NO"
    . "\t" . "ESIC NO"
    . "\t" . "D.O.B"
    . "\t" . "PRESENT DAYS"
    . "\t" . "WAGES"
    . "\t" . "Difference  in ESIC"
    . "\t" . "OT AMOUNT FOR ESIC";
    
    $hearder .=  "\n";
    $data = "";
    $iCounter = 1;
    while ($rows = mysqli_fetch_assoc($result1)) {
        // $sql = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate,workingdays FROM `salarydetails` where  emp_id='" . $rows['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
        // $result = mysqli_fetch_assoc($sql);
        $sql = mysqli_query($dbconn,"SELECT max(rate) as skillrate,workingdays FROM `multicompany` where  companysalarymasterId in ( SELECT companysalarymasterId FROM `companysalarymaster` where month='".$salaryMonth."' and istatus=1 and isDelete=0) and isDelete=0 order by name asc ");
        $result = mysqli_fetch_assoc($sql);
        $workingdays= "";
        if(isset($result['workingdays']))
        {
            $workingdays=$result['workingdays'];
        }
        $skillrate = "";
        if(isset($result['skillrate'])){
            $skillrate = $result['skillrate'];
        }
        $data .= $iCounter
        . "\t" . trim(ucwords(strtolower($rows['emp_name'])))
        . "\t" . trim($rows['pfcode'])
        . "\t" . trim($rows['uan'])
        . "\t" . trim($rows['ecsno'])
        . "\t" . trim(date('d-m-Y',strtotime($rows['dateofbirth'])))
        . "\t" . $workingdays
        . "\t" . $skillrate
        . "\t" . ""
        . "\t" . "";
        $data .=  "\n";   
        $iCounter++;
        
    }
    
    $tempEmpolyeeMastersql = "SELECT * FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
    $tempEmpolyeeMasterResult = mysqli_query($dbconn, $tempEmpolyeeMastersql);
    
    $hearderNewLine = ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . "NEW NAME"
    . "\t" . ""
    . "\t" . ""
    . "\t" . ""
    . "\t" . "";
    $hearderNewLine .=  "\n";
    
    $hearderTwo = "SR. No."
    . "\t" . "Name as per Aadhar"
    . "\t" . "Father Name"
    . "\t" . "Aadhar no."
    . "\t" . "ESIC No."
    . "\t" . "DOB"
    . "\t" . "PRESENT DAYS"
    . "\t" . "WAGES"
    . "\t" . "Difference  in ESIC"
    . "\t" . "Joining Date";
    $hearderTwo .=  "\n";
    
    $dataNew = "";
    $jCounter = 1;
    while ($row = mysqli_fetch_assoc($tempEmpolyeeMasterResult)) {
        
        $dataNew .= $jCounter
        . "\t" . trim(ucwords(strtolower($row['emp_name'])))
        . "\t" . trim(ucwords(strtolower($row['strFatherName'])))
        . "\t" . trim($row['adharcard'])
        . "\t" . trim($row['ecsno'])
        . "\t" . trim(date('d-m-Y',strtotime($row['dateofbirth'])))
        . "\t" . ""
        . "\t" . ""
        . "\t" . ""
        . "\t" . trim(date('d-m-Y',strtotime($row['dateofjoining'])));
        $dataNew .=  "\n";   
        $jCounter++;
    }
    
    $filename = 'PF_Challan_Generate_Report_'.date('dmyHis') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-disposition: attachment; filename=" . $filename);
    ob_end_clean();
    
    echo chr(255) . chr(254) .mb_convert_encoding($hearderTitle, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($companyTitle, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($month, 'UTF-16LE', 'UTF-8');
    
    echo chr(255) . chr(254) .mb_convert_encoding($hearder, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($hearderNewLine, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($hearderTwo, 'UTF-16LE', 'UTF-8');
    echo chr(255) . chr(254) .mb_convert_encoding($dataNew, 'UTF-16LE', 'UTF-8');
    
} else {
    header('location:summaryreport.php');
}
exit;
?>