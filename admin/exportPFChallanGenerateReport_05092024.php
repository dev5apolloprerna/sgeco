<?php

ob_start();
ob_clean();
include('../config.php');

$i = 1;
$delimiter = ",";
$filename = "PF_Challan_Generate_Report_" . date('Y-m-d H:i:s') . ".csv";
$f = fopen('php://memory', 'w');
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

//$sql1 = "SELECT * FROM `employee`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
$sql1 = "SELECT employee.employeeId,max(skillrate) as skillrate,(max(skillrate)- min(skillrate)) as Diff,employee.employeecode,
        case when (max(skillrate)- min(skillrate)) > 0 then max(CONVERT(workingdays,UNSIGNED)) + 2 else max(CONVERT(workingdays,UNSIGNED)) end as 
        workingdays,employee.emp_name,employee.pfcode,employee.uan,employee.ecsno,employee.dateofbirth,max(salarydetails.basicwages) as 'basicAmount',max(salarydetails.total) as 'grossAmount',employee.employeeId, salarydetails.totalovertime
        FROM `salarydetails` inner join employee on salarydetails.emp_id=employee.employeeId where  salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0 and employee.employeecode !=0 
        and employeecode not in (829,257,815,1063,256,84,2060,259,1131,1444,229,306,1834,1275,1967,1606,2305)  and isPermanent=0
        GROUP by employee.employeeId order by employee.employeecode asc";
$result1 = mysqli_query($dbconn, $sql1);

if (mysqli_num_rows($result1) > 0) {
    // $hearderTitle = "";
    // $hearderTitle .= ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "\n";
    $fields = array(
        "",
        "",
        "",
        '',
        '',
        '',
        "",
        "",
        ""
    );
    fputcsv($f, $fields, $delimiter);
    // $hearderTitle .= ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "FOR EPF & ESIC ONLY"
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "\n";
    $fields = array(
        "",
        "",
        "",
        '',
        'FOR EPF & ESIC ONLY',
        '',
        "",
        "",
        ""
    );
    fputcsv($f, $fields, $delimiter);
    // $hearderTitle .= ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "SHREE GANESH ENGINEERING CO."
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "\n";
    $fields = array(
        "",
        "",
        "",
        '',
        'SHREE GANESH ENGINEERING CO.',
        '',
        "",
        "",
        ""
    );
    fputcsv($f, $fields, $delimiter);
    // $hearderTitle .= ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "P.F.CODE : GJ-16240 GROUP : IV"
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "\n";
    
    $fields = array(
        "",
        "",
        "",
        '',
        'P.F.CODE : GJ-16240 GROUP : IV',
        '',
        "",
        "",
        ""
    );
    fputcsv($f, $fields, $delimiter);
    
    
    //$companyTitle =  "\t" . " \n";
    $comp = mysqli_query($dbconn,"select salarymaster.companymasterId,(select companymaster.companyname from companymaster where companymaster.companymasterId=salarymaster.companymasterId) as companyname from salarymaster where month='".$salaryMonth."' and isDelete=0 and istatus=1");
    $companyname = "";
    while($rowCom = mysqli_fetch_assoc($comp)){
        $companyname .= $rowCom['companyname'].",";
    }
    $companyname = rtrim($companyname,',');
    $mnth = date("F", strtotime("01-".$_REQUEST['month']."-".$_REQUEST['Year']));
    //$month =  "\t" . "Employee List of PF and ESIC For the Month of: ". $mnth ."-".$_REQUEST['Year']." - ".$companyname." \n";
    $fields = array(
        'Employee List of PF and ESIC For the Month of: '. $mnth .'-'.$_REQUEST['Year'].' - '.$companyname.'',
        // "",
        // "",
        // "",
        // '',
        // '',
        // "",
        // "",
        // ""
    );
    fputcsv($f, $fields, $delimiter);
    
    // $hearder = "SR. No."
    // . "\t" . "NAME"
    // . "\t" . "P.F. A/c No."
    // . "\t" . "UAN NO"
    // . "\t" . "ESIC NO"
    // . "\t" . "D.O.B"
    // . "\t" . "PRESENT DAYS"
    // . "\t" . "WAGES"
    // . "\t" . "Difference  in ESIC"
    // . "\t" . "OT AMOUNT FOR ESIC";
    $fields = array(
        "SR. No.",
        "NAME",
        "P.F. A/c No.",
        'UAN NO',
        'ESIC NO',
        'D.O.B',
        "PRESENT DAYS",
        "WAGES",
        "Difference  in ESIC",
        "OT AMOUNT FOR ESIC"
    );
    fputcsv($f, $fields, $delimiter);
    //$hearder .=  "\n";
    $data = "";
    $iCounter = 1;
    $resfilter = mysqli_query($dbconn,"SELECT employee.employeeId,employee.employeecode,employee.emp_name,employee.pfcode,employee.uan,employee.ecsno,employee.dateofbirth,employee.employeeId FROM `employee` where employeecode in (829,257,815,1063,256,84,2060,259,1131,1444,229,306,1834,1275,1967,1606,2305) and isPermanent=1 and isDelete=0 and istatus=1 order by employeecode asc");
    if (mysqli_num_rows($resfilter) > 0) {
        while ($row = mysqli_fetch_array($resfilter)) { 
            $filterdetails = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate,(max(skillrate)- min(skillrate)) as Diff,
                case when (max(skillrate)- min(skillrate)) > 0 then max(CONVERT(workingdays,UNSIGNED)) + 2 else max(CONVERT(workingdays,UNSIGNED)) end as 
                workingdays,max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount',salarydetails.totalovertime
                FROM `salarydetails` where  salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0 and salarydetails.emp_id='".$row['employeeId']."' GROUP by salarydetails.emp_id");
            $workingdays = 0;
            $skillrate = 0;
            $DifferenceInESIC=0;
            $totalovertime = 0;
            if(mysqli_num_rows($filterdetails) == 1){
                $rowDetails = mysqli_fetch_array($filterdetails);
                $workingdays = $rowDetails['workingdays'];
                $skillrate = $rowDetails['skillrate'];
                
                if($rowDetails['Diff'] == 0){
                    $grossAmount = $rowDetails['grossAmount'];
                    $basicAmount = $rowDetails['basicAmount'];
                    $DifferenceInESIC = $grossAmount - $basicAmount;
                    //echo round($DifferenceInESIC);
                } else {
                    $workingdays = $workingdays - 2;
                    $sql = mysqli_query($dbconn,"SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$rowDetails['skillrate']."' and emp_id='" . $row['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
                    if(mysqli_num_rows($sql) == 1){
                        $rowDays= mysqli_fetch_assoc($sql);
                        $grossAmount = $rowDays['grossAmount'];
                        $basicAmount = $rowDays['basicAmount'];
                        $DifferenceInESIC = $grossAmount - $basicAmount;
                        //echo round($DifferenceInESIC);
                    } else {
                        //echo $DifferenceInESIC;
                    }
                }
                
                $totalovertime = $rowDetails['totalovertime'];
            }    
            $salary = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT max(workingdays) as workingdays,max(netamountpaid) as netamountpaid FROM permanentemployeesalarydetails where salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and emp_id='".$row['employeeId']."' group by emp_id"));
            if(isset($skillrate) && $skillrate ==0){
                if(isset($salary['netamountpaid']) && $salary['netamountpaid'] != 0){
                    $skillrate = $salary['netamountpaid'];
                }
            }
            if(isset($workingdays) && $workingdays == 0){
                if(isset($salary['workingdays']) && $salary['workingdays'] != 0){
                    $workingdays=$salary['workingdays'];
                }
            }
            $dateofbirth = $row['dateofbirth']=='01/01/1970' ? "" : $row['dateofbirth'];

            // $data .= $iCounter
            //     . "\t" . trim(ucwords(strtolower($row['emp_name'])))
            //     . "\t" . trim($row['employeecode'])
            //     . "\t" . trim($row['uan'])
            //     . "\t" . trim($row['ecsno'])
            //     . "\t" . $dateofbirth
            //     . "\t" . $workingdays
            //     . "\t" . $skillrate
            //     . "\t" . round($DifferenceInESIC)
            //     . "\t" . $totalovertime;
            //     $data .=  "\n";  
            $DifferenceInESIC = $DifferenceInESIC - $totalovertime;
            $fields = array(
                $iCounter,
                trim(ucwords(strtolower($row['emp_name']))),
                trim($row['employeecode']),
                trim($row['uan']),
                trim($row['ecsno']),
                $dateofbirth,
                $workingdays,
                $skillrate,
                round($DifferenceInESIC),
                ceil($totalovertime)
            );
            fputcsv($f, $fields, $delimiter);
            $iCounter++;
        }
    }
    while ($rows = mysqli_fetch_assoc($result1)) {
        // $sql = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate,workingdays FROM `salarydetails` where  emp_id='" . $rows['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
        // $result = mysqli_fetch_assoc($sql);
        // $sql = mysqli_query($dbconn,"SELECT max(rate) as skillrate,workingdays FROM `multicompany` where  companysalarymasterId in ( SELECT companysalarymasterId FROM `companysalarymaster` where month='".$salaryMonth."' and istatus=1 and isDelete=0) and isDelete=0 order by name asc ");
        // $result = mysqli_fetch_assoc($sql);
        $workingdays= "0";
        if(isset($rows['workingdays']))
        {
            $workingdays=$rows['workingdays'];
        }
        $skillrate = "0";
        if(isset($rows['skillrate'])){
            $skillrate = $rows['skillrate'];
        }
        $DifferenceInESIC=0;
        if($rows['Diff'] == 0){
            $grossAmount = $rows['grossAmount'];
            $basicAmount = $rows['basicAmount'];
            $DifferenceInESIC = $grossAmount - $basicAmount;
            $DifferenceInESIC= round($DifferenceInESIC);
        } else {
            $workingdays = $workingdays - 2;
            $sql = mysqli_query($dbconn,"SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$rows['skillrate']."' and emp_id='" . $rows['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
            if(mysqli_num_rows($sql) == 1){
                $rowDays= mysqli_fetch_assoc($sql);
                $grossAmount = $rowDays['grossAmount'];
                $basicAmount = $rowDays['basicAmount'];
                $DifferenceInESIC = $grossAmount - $basicAmount;
                $DifferenceInESIC= round($DifferenceInESIC);
            } else {
                $DifferenceInESIC= $DifferenceInESIC;
            }
        }
        $totalovertime = isset($rows['totalovertime']) ? $rows['totalovertime'] : 0;
        $dateofbirth = $rows['dateofbirth'];// isset($rows['dateofbirth']) && $rows['dateofbirth'] != "" ? trim(date('d-m-Y',strtotime($rows['dateofbirth']))) :"";
        // $data .= $iCounter
        // . "\t" . trim(ucwords(strtolower($rows['emp_name'])))
        // . "\t" . trim($rows['employeecode'])
        // . "\t" . trim($rows['uan'])
        // . "\t" . trim($rows['ecsno'])
        // . "\t" . $dateofbirth
        // . "\t" . $workingdays
        // . "\t" . $skillrate
        // . "\t" . round($DifferenceInESIC)
        // . "\t" . $totalovertime;
        // $data .=  "\n";   
        $DifferenceInESIC = $DifferenceInESIC - $totalovertime;
        $fields = array(
            $iCounter,
            trim(ucwords(strtolower($rows['emp_name']))),
            trim($rows['employeecode']),
            trim($rows['uan']),
            trim($rows['ecsno']),
            $dateofbirth,
            $workingdays,
            $skillrate,
            round($DifferenceInESIC),
            ceil($totalovertime)
        );
        fputcsv($f, $fields, $delimiter);
        $iCounter++;
        
    }
    
    //$tempEmpolyeeMastersql = "SELECT * FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
    // SELECT  tempEmpolyeeMaster.iTempEmpId as employeeId,'' as skillrate, '' as workingdays,emp_name,pfcode,
    //     uan,ecsno,dateofbirth,'' as 'basicAmount','' as 'grossAmount', 0 as totalovertime,strFatherName,adharcard,dateofjoining FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0'  and  istatus='1' 
    //     UNION ALL
    $tempEmpolyeeMastersql = "
        SELECT employee.employeeId,max(skillrate) as skillrate,(max(skillrate)- min(skillrate)) as Diff,
        case when (max(skillrate)- min(skillrate)) > 0 then max(CONVERT(workingdays,UNSIGNED)) + 2 else max(CONVERT(workingdays,UNSIGNED)) end as 
        workingdays,employee.emp_name,employee.pfcode,employee.uan,employee.ecsno,employee.dateofbirth,max(salarydetails.basicwages) as 'basicAmount',max(salarydetails.total) as 'grossAmount',employee.employeeId, salarydetails.totalovertime,employee.dateofjoining
        ,strFatherName,adharcard FROM `salarydetails` inner join employee on salarydetails.emp_id=employee.employeeId where  salaryId in (select salarymasterId from salarymaster where   month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  
        salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0 and employee.employeecode=0 GROUP by employee.employeeId order by employeeId";
    $tempEmpolyeeMasterResult = mysqli_query($dbconn, $tempEmpolyeeMastersql);
    
    // $hearderNewLine = ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "NEW NAME"
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . ""
    // . "\t" . "";
    // $hearderNewLine .=  "\n";
    $fields = array(
        "",
        "",
        "",
        '',
        'NEW NAME',
        '',
        "",
        "",
        ""
    );
    fputcsv($f, $fields, $delimiter);
    
    // $hearderTwo = "SR. No."
    // . "\t" . "Name as per Aadhar"
    // . "\t" . "Father Name"
    // . "\t" . "Aadhar no."
    // . "\t" . "ESIC No."
    // . "\t" . "DOB"
    // . "\t" . "PRESENT DAYS"
    // . "\t" . "WAGES"
    // . "\t" . "Difference  in ESIC"
    // . "\t" . "Joining Date";
    // $hearderTwo .=  "\n";
    $fields = array(
        "SR. No.",
        "Name as per Aadhar",
        "Father Name",
        'Aadhar No.',
        'ESIC No.',
        'DOB',
        "PRESENT DAYS",
        "WAGES",
        "Difference  in ESIC",
        "Joining Date"
    );
    fputcsv($f, $fields, $delimiter);
    $dataNew = "";
    $jCounter = 1;
    while ($row = mysqli_fetch_assoc($tempEmpolyeeMasterResult)) {
        $dateofbirth = $row['dateofbirth']; //isset($row['dateofbirth']) && $row['dateofbirth'] != "" ? date('d-m-Y',strtotime(trim($row['dateofbirth']))) :"";
        $dateofjoining =  $row['dateofjoining'] == '01/70' ? "" : $row['dateofjoining']; //isset($row['dateofjoining']) && $row['dateofjoining'] != "" ? date('d-m-Y',strtotime(trim($row['dateofjoining']))) :"";
        // $DifferenceInESIC=0;
        // if($row['Diff'] == 0){
        //     $grossAmount = $row['grossAmount'];
        //     $basicAmount = $row['basicAmount'];
        //     $DifferenceInESIC = $grossAmount - $basicAmount;
        //     echo round($DifferenceInESIC);
        // } else {
        //     echo $DifferenceInESIC;
        // }
        $DifferenceInESIC=0;
        if($row['Diff'] == 0){
            $grossAmount = $row['grossAmount'];
            $basicAmount = $row['basicAmount'];
            $DifferenceInESIC = $grossAmount - $basicAmount;
            $DifferenceInESIC = round($DifferenceInESIC);
        } else {
            $workingdays = $workingdays - 2;
            $sql = mysqli_query($dbconn,"SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$row['skillrate']."' and emp_id='" . $row['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
            if(mysqli_num_rows($sql) == 1){
                $rowDays= mysqli_fetch_assoc($sql);
                $grossAmount = $rowDays['grossAmount'];
                $basicAmount = $rowDays['basicAmount'];
                $DifferenceInESIC = $grossAmount - $basicAmount;
                $DifferenceInESIC = round($DifferenceInESIC);
            } else {
                $DifferenceInESIC=$DifferenceInESIC;
            }
        }
        // $dataNew .= $jCounter
        // . "\t" . trim(ucwords(strtolower($row['emp_name'])))
        // . "\t" . trim($row['strFatherName'])
        // . "\t" . trim($row['adharcard'])
        // . "\t" . trim($row['ecsno'])
        // . "\t" . $dateofbirth
        // . "\t" . trim($row['workingdays'])
        // . "\t" . trim($row['skillrate'])
        // . "\t" . trim(round($DifferenceInESIC))
        // . "\t" . $dateofjoining;
        
        $fields = array(
            $jCounter,
            trim(ucwords(strtolower($row['emp_name']))),
            trim($row['strFatherName']),
            trim($row['adharcard']),
            trim($row['ecsno']),
            $dateofbirth,
            trim($row['workingdays']),
            trim($row['skillrate']),
            trim(round($DifferenceInESIC)),
            $dateofjoining
        );
        fputcsv($f, $fields, $delimiter);
        //$dataNew .=  "\n";   
        $jCounter++;
    }
    
    
    
    fseek($f, 0);
    header('Content-Type: text/csv');
    $filename = "companyBankReport" . date('Y-m-d H:i:s') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
    //$filename = 'PF_Challan_Generate_Report_'.date('dmyHis') . '.xls';
    // header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    // header("Content-disposition: attachment; filename=" . $filename);
    // ob_end_clean();
    
    // echo chr(255) . chr(254) .mb_convert_encoding($hearderTitle, 'UTF-16LE', 'UTF-8');
    // echo chr(255) . chr(254) .mb_convert_encoding($companyTitle, 'UTF-16LE', 'UTF-8');
    // echo chr(255) . chr(254) .mb_convert_encoding($month, 'UTF-16LE', 'UTF-8');
    
    // echo chr(255) . chr(254) .mb_convert_encoding($hearder, 'UTF-16LE', 'UTF-8');
    // echo chr(255) . chr(254) .mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    // echo chr(255) . chr(254) .mb_convert_encoding($hearderNewLine, 'UTF-16LE', 'UTF-8');
    // echo chr(255) . chr(254) .mb_convert_encoding($hearderTwo, 'UTF-16LE', 'UTF-8');
    // echo chr(255) . chr(254) .mb_convert_encoding($dataNew, 'UTF-16LE', 'UTF-8');
    
} else {
    header('location:summaryreport.php');
}
exit;
?>