<?php

ob_start();
error_reporting(0);
include('../common.php');
$connect = new connect();
include 'IsLogin.php';
include 'password_hash.php';
include '../PHPMailer-master/PHPMailerAutoload.php';
include_once 'companyReportAdvance.php';

$action = $_REQUEST['action'];

switch ($action) {
    case "UserProfileChangePassword":
        $hash_result = create_hash($_POST['oldpassword']);
        $hash_params = explode(":", $hash_result);
        $salt = $hash_params[HASH_SALT_INDEX];
        $hash = $hash_params[HASH_PBKDF2_INDEX];
        $existsmail = "SELECT * FROM admin where id='" . $_SESSION['AdminId'] . "'";
        $result = mysqli_query($dbconn,$existsmail);
        $num_rows = mysqli_num_rows($result);
        $row = mysqli_fetch_array($result);
    
        $existscode = "SELECT * FROM `secretcode` where secretcode like '" . $_POST['secretcode'] . "'";
        $resultcount = mysqli_query($dbconn,$existscode);
        $num_rows_code_count = mysqli_num_rows($resultcount);
        
        if($num_rows_code_count == 1){
            if ($num_rows >= 1) {
                $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['salt'] . ":" . $row['password'];
                $oldpassword = mysqli_real_escape_string($dbconn,$_REQUEST['oldpassword']);
                if (validate_password($_REQUEST['oldpassword'], $good_hash)) {
                    $hash_result = create_hash($_REQUEST['password']);
                    $hash_params = explode(":", $hash_result);
                    $salt = $hash_params[HASH_SALT_INDEX];
                    $hash = $hash_params[HASH_PBKDF2_INDEX];
                    $getItems1 = mysqli_query($dbconn,"update admin SET password = '" . $hash . "', salt = '" . $salt . "' where id='" . $_SESSION['AdminId'] . "'");
                    echo "Sucess";
                } else {
                    echo "OldNot";
                }
            } else {
                echo "ID not found";
            }
        } else {
            echo "Sorry, Secret Code Not Match";
        }
        break;

    case "AddBank":
        $chankbank = mysqli_query($dbconn, "SELECT * FROM bankmaster where  bankname = '" . $_POST['Bank'] . "' and isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($chankbank) == 0) {
            $data = array(
                "bankname" => $_POST['Bank'],
                "City" => $_POST['City'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'bankmaster', $data);
            echo $statusMsg = $dealer_res ? '1' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "GetAdminBank":
        $filterstr = "SELECT * FROM `bankmaster`  where  isDelete='0'  and  istatus='1' and  bankmasterId=" . $_REQUEST['ID'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        print_r(json_encode($row));
        break;

    case "Editbankname":
        $chankbank = mysqli_query($dbconn, "SELECT * FROM bankmaster where  bankname = '" . $_POST['Bank'] . "' and isDelete='0' and istatus='1' and bankmasterId != '" . $_REQUEST['bankmasterId'] . "' ");
        if (mysqli_num_rows($chankbank) == 0) {
            $data = array(
                "bankname" => $_POST['Bank'],
                "City" => $_POST['City'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iUpdatedBy" => $_SESSION['AdminId'],
                "UpdatedDate" => date('d-m-Y H:i:s')
            );
            $where = ' where  bankmasterId =' . $_REQUEST['bankmasterId'];
            $dealer_res = $connect->updaterecord($dbconn, 'bankmaster', $data, $where);
            echo $statusMsg = $dealer_res ? '2' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "Adddesignation":
        $chankdisi = mysqli_query($dbconn, "SELECT * FROM designation where  designation = '" . $_POST['Designation'] . "' and isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($chankdisi) == 0) {
            $data = array(
                "designation" => $_POST['Designation'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'designation', $data);
            echo $statusMsg = $dealer_res ? '1' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "GetAdminDesignation":
        $filterstr = "SELECT * FROM `designation`  where  isDelete='0'  and  istatus='1' and  designationid=" . $_REQUEST['ID'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        print_r(json_encode($row));
        break;

    case "Editdesignation":
        $chankdisi = mysqli_query($dbconn, "SELECT * FROM designation where  designation = '" . $_POST['Designation'] . "' and isDelete='0' and istatus='1' and designationid != '" . $_REQUEST['designationid'] . "' ");
        if (mysqli_num_rows($chankdisi) == 0) {
            $data = array(
                "designation" => $_POST['Designation'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iUpdatedBy" => $_SESSION['AdminId'],
                "UpdatedDate" => date('d-m-Y H:i:s')
            );
            $where = ' where  designationid =' . $_REQUEST['designationid'];
            $dealer_res = $connect->updaterecord($dbconn, 'designation', $data, $where);
            echo $statusMsg = $dealer_res ? '2' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "AddCompany":
        $chankcomp = mysqli_query($dbconn, "SELECT * FROM companymaster where  companyname = '" . $_POST['Company'] . "' and isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($chankcomp) == 0) {
            $data = array(
                "companyname" => $_POST['Company'],
                "skil" => $_POST['Skil'],
                "unskill" => $_POST['unSkil'],
                "semiskill" => $_POST['SemiSkil'],
                "highlyskilled" => $_POST['highlyskilled'],
                "ESI" => $_POST['ESI'],
                "pf" => $_POST['pf'],
                "MedicalAllowance" => $_POST['MedicalAllowance'],
                "MedicalAllowancePer" => $_POST['MedicalAllowancePer'],
                "strBonus" => $_POST['strBonus'],
                "strLeave" => $_POST['strLeave'],
                "iDailyWorkingRate" => $_POST['iDailyWorkingRate'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'companymaster', $data);
            echo $statusMsg = $dealer_res ? '1' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "GetAdminCompany":
        $filterstr = "SELECT * FROM `companymaster`  where  isDelete='0'  and  istatus='1' and  companymasterId=" . $_REQUEST['ID'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        print_r(json_encode($row));
        break;

    case "EditCompanyname":
        $chankcomp = mysqli_query($dbconn, "SELECT * FROM companymaster where  companyname = '" . $_POST['Company'] . "' and companymasterId != '" . $_REQUEST['companymasterId'] . "' and  isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($chankcomp) == 0) {
            $data = array(
                "companyname" => $_POST['Company'],
                "skil" => $_POST['Skil'],
                "unskill" => $_POST['unSkil'],
                "semiskill" => $_POST['SemiSkil'],
                "highlyskilled" => $_POST['highlyskilled'],
                "ESI" => $_POST['ESI'],
                "pf" => $_POST['pf'],
                "MedicalAllowance" => $_POST['MedicalAllowance'],
                "MedicalAllowancePer" => $_POST['MedicalAllowancePer'],
                "strBonus" => $_POST['strBonus'],
                "strLeave" => $_POST['strLeave'],
                "iDailyWorkingRate" => $_POST['iDailyWorkingRate'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iUpdatedBy" => $_SESSION['AdminId'],
                "UpdatedDate" => date('d-m-Y H:i:s')
            );
            $where = ' where  companymasterId =' . $_REQUEST['companymasterId'];
            $dealer_res = $connect->updaterecord($dbconn, 'companymaster', $data, $where);
            echo $statusMsg = $dealer_res ? '2' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "AddSalary":
        $Salary = mysqli_query($dbconn, "SELECT * FROM salarymaster where  companymasterId = '" . $_POST['companymasterId'] . "' and month= '" . $_POST['month'] . "' and isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($Salary) == 0) {

            $data = array(
                "companymasterId" => $_POST['companymasterId'],
                "salarypaiddate" => $_POST['salarypaiddate'],
                "month" => $_POST['month'],
                "fromdate" => $_POST['fromdate'],
                "todate" => $_POST['todate'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'salarymaster', $data);
            echo $statusMsg = $dealer_res ? '1' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "GetAdminSalary":
        $filterstr = "SELECT * FROM `salarymaster`  where  isDelete='0'  and  istatus='1' and  salarymasterId=" . $_REQUEST['ID'] . "";

        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        print_r(json_encode($row));
        break;

    case "EditSalaryname":
        $Salary = mysqli_query($dbconn, "SELECT * FROM salarymaster where  companymasterId = '" . $_POST['companymasterId'] . "' and month= '" . $POST['month'] . "' and salarymasterId !='" . $_REQUEST['salarymasterId'] . "' and isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($Salary) == 0) {
            $data = array(
                "companymasterId" => $_POST['companymasterId'],
                "salarypaiddate" => $_POST['salarypaiddate'],
                "month" => $_POST['month'],
                "fromdate" => $_POST['fromdate'],
                "todate" => $_POST['todate'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iUpdatedBy" => $_SESSION['AdminId'],
                "UpdatedDate" => date('d-m-Y H:i:s')
            );
            $where = ' where  salarymasterId =' . $_REQUEST['salarymasterId'];
            $dealer_res = $connect->updaterecord($dbconn, 'salarymaster', $data, $where);
            echo $statusMsg = $dealer_res ? '2' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;

    case "AddCompanySalary":

        $data = array(
            "salarypaiddate" => $_POST['salarypaiddate'],
            "month" => $_POST['month'],
            "fromdate" => $_POST['fromdate'],
            "todate" => $_POST['todate'],
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iEntryBy" => $_SESSION['AdminId'],
            "EntryDate" => date('d-m-Y H:i:s')
        );
        $dealer_res = $connect->insertrecord($dbconn, 'companysalarymaster', $data);

        $Company = $_POST['Company'];
        foreach ($Company as $key => $value) {
            mysqli_query($dbconn, "INSERT INTO `multiycompanysalarymaster`(companysalarymasterId,companymasterId,strEntryDate,strIP,iEntryBy,EntryDate) VALUES ('" . $dealer_res . "','" . $value . "', '" . date('d-m-Y H:i:s') . "', '" . $_SERVER['REMOTE_ADDR'] . "','".$_SESSION['AdminId']."', '" . date('d-m-Y H:i:s') . "') ");
        }
        echo $statusMsg = $dealer_res ? '1' : '0';

        break;

    case "GetAdminCompanySalary":
        $filterstr = "SELECT * FROM `companysalarymaster`  where  isDelete='0'  and  istatus='1' and  companysalarymasterId=" . $_REQUEST['ID'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row[] = mysqli_fetch_assoc($result);

        $multiycompanysalarymaster = mysqli_query($dbconn, "SELECT companymasterId FROM `multiycompanysalarymaster`  where companysalarymasterId =" . $_REQUEST['ID'] . "");

        while ($row_multiycompanysalarymaster = mysqli_fetch_assoc($multiycompanysalarymaster)) {
            $row['companymasterId'][] = $row_multiycompanysalarymaster;
        }
        print_r(json_encode($row));
        break;

    case "EditCompanySalary":

        $data = array(
            "salarypaiddate" => $_POST['salarypaiddate'],
            "month" => $_POST['month'],
            "fromdate" => $_POST['fromdate'],
            "todate" => $_POST['todate'],
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iUpdatedBy" => $_SESSION['AdminId'],
            "UpdatedDate" => date('d-m-Y H:i:s')
        );
        $where = ' where  companysalarymasterId =' . $_REQUEST['companysalarymasterId'];
        $dealer_res = $connect->updaterecord($dbconn, 'companysalarymaster', $data, $where);

        $sql_res = mysqli_query($dbconn, "delete from multiycompanysalarymaster where  companysalarymasterId = " . $_REQUEST['companysalarymasterId'] . " ");

        $resultCategory = mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc");
        while ($rowC = mysqli_fetch_array($resultCategory)) {
            if (isset($_POST['Company' . $rowC['companymasterId']]))
            //   mysql_query("INSERT INTO `clientemployeecategory`(iclientEmployeeId,iCategoryId,strEntryDate,strIP) VALUES ('" . $dealer_res . "','" . $rowC['iCategoryId'] . "', '" . date('d-m-Y H:i:s'). "', '" . $_SERVER['REMOTE_ADDR'] . "' ) ")or die(mysql_error());
                mysqli_query($dbconn, "INSERT INTO `multiycompanysalarymaster`(companysalarymasterId,companymasterId,strEntryDate,strIP,iEntryBy,iUpdatedBy,UpdatedDate) VALUES ('" . $_REQUEST['companysalarymasterId'] . "','" . $rowC['companymasterId'] . "', '" . date('d-m-Y H:i:s') . "', '" . $_SERVER['REMOTE_ADDR'] . "','".$_SESSION['AdminId']."','".$_SESSION['AdminId']."', '" . date('d-m-Y H:i:s') . "' ) ");
        }
        echo $statusMsg = $dealer_res ? '2' : '0';
        break;

    case "AddEmployee":
        
        $valid_extensions = array('pdf', 'PDF','jpg','JPG','jpeg','JPEG','png','PNG'); // valid extensions
        $EntrDate = date('d-m-Y');
        $arr = explode(' ', $EntrDate);
        $dateArrar = explode('-', $arr[0]);

        if (!file_exists('Document/' . $dateArrar[2] . "/")) {
            mkdir('Document/' . $dateArrar[2], 0777, TRUE);
        }
        if (!file_exists('Document/' . $dateArrar[2] . "/" . $dateArrar[1])) {
            mkdir('Document/' . $dateArrar[2] . "/" . $dateArrar[1], 0777, TRUE);
        }
        if (!file_exists('Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0])) {
            mkdir('Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0], 0777, TRUE);
        }

        $path = 'Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/";
//        $path = 'Document/'; // upload directory
        if (!file_exists($path)) {
            mkdir($path, 0777, TRUE);
        }
        $aadharImage = "";
        $pancardImage = "";
        $voteridImage = "";
        $passportImage = "";
        if ($_FILES['aadharImage']) {
            $aadharimg = $_FILES['aadharImage']['name'];
            $tmp = $_FILES['aadharImage']['tmp_name'];

            $ext = strtolower(pathinfo($aadharimg, PATHINFO_EXTENSION));
            $aadhar_final_image = preg_replace('/\s+/', '_', $aadharimg);
            if (in_array($ext, $valid_extensions)) {
                $path = $path . strtolower($aadhar_final_image);
                if (move_uploaded_file($tmp, $path)) {
                    $aadharImage = $aadhar_final_image;
                }
            }
        } else {
            $aadharImage = "";
        }
        if ($_FILES['pancardImage']) {
            $pancardimg = $_FILES['pancardImage']['name'];
            $tmp = $_FILES['pancardImage']['tmp_name'];

            $ext = strtolower(pathinfo($pancardimg, PATHINFO_EXTENSION));
            $pancard_final_image = preg_replace('/\s+/', '_', $pancardimg);
            if (in_array($ext, $valid_extensions)) {
                $path = $path . strtolower($pancard_final_image);
                if (move_uploaded_file($tmp, $path)) {
                    $pancardImage = $pancard_final_image;
                }
            }
        } else {
            $pancardImage = "";
        }

        if ($_FILES['voteridImage']) {
            $voteridimg = $_FILES['voteridImage']['name'];
            $tmp = $_FILES['voteridImage']['tmp_name'];

            $ext = strtolower(pathinfo($voteridimg, PATHINFO_EXTENSION));
            $voterid_final_image = preg_replace('/\s+/', '_', $voteridimg);
            if (in_array($ext, $valid_extensions)) {
                $path = $path . strtolower($voterid_final_image);
                if (move_uploaded_file($tmp, $path)) {
                    $voteridImage = $voterid_final_image;
                }
            }
        } else {
            $voteridImage = "";
        }

        if ($_FILES['passportImage']) {
            $passportimg = $_FILES['passportImage']['name'];
            $tmp = $_FILES['passportImage']['tmp_name'];

            $ext = strtolower(pathinfo($passportimg, PATHINFO_EXTENSION));
            $passport_final_image = preg_replace('/\s+/', '_', $passportimg);
            if (in_array($ext, $valid_extensions)) {
                $path = $path . strtolower($passport_final_image);
                if (move_uploaded_file($tmp, $path)) {
                    $passportImage = $passport_final_image;
                }
            }
        } else {
            $passportImage = "";
        }

        if($_POST['mno'] != ""){
            $filterTempEmpMno = mysqli_query($dbconn,"SELECT mno from employee where mno='".$_POST['mno']."' and isDelete=0 and istatus=1 and isExitEmployee=0");
            if(mysqli_num_rows($filterTempEmpMno) > 0){
                echo 4;
                exit;
            }
        }
        if($_POST['ecsno'] != ""){
            $filterTempEmpEcsno = mysqli_query($dbconn,"SELECT ecsno from employee where ecsno='".$_POST['ecsno']."' and isDelete=0 and istatus=1 and isExitEmployee=0");
            if(mysqli_num_rows($filterTempEmpEcsno) > 0){
                echo 5;
                exit;
            }
        }
        // if($_POST['accountno'] != ""){
        //     $filterTempEmpAccount = mysqli_query($dbconn,"SELECT accountno from employee where accountno='".$_POST['accountno']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpAccount) > 0){
        //             echo 6;
        //     }
        // }
        if($_POST['pfcode'] != ""){
            $filterTempEmpPFCode = mysqli_query($dbconn,"SELECT pfcode from employee where pfcode='".$_POST['pfcode']."' and isDelete=0 and istatus=1 and isExitEmployee=0");
            if(mysqli_num_rows($filterTempEmpPFCode) > 0){
                echo 7;
                exit;
                //$output['message'] = 'PF Code Number is already exists.';
            }
        }
        if($_POST['uan'] != ""){
            $filterTempEmpUan = mysqli_query($dbconn,"SELECT uan from employee where uan='".$_POST['uan']."' and isDelete=0 and istatus=1 and isExitEmployee=0");
            if(mysqli_num_rows($filterTempEmpUan) > 0){
                echo 8;
                exit;
                //$output['message'] = 'UAN Number is already exists.';
            }
        }
        if($_POST['pancard'] != ""){
            $filterTempEmpPancard = mysqli_query($dbconn,"SELECT pancard from employee where pancard='".$_POST['pancard']."' and isDelete=0 and istatus=1 and isExitEmployee=0");
            if(mysqli_num_rows($filterTempEmpPancard) > 0){
                echo 9;
                exit;
                //$output['message'] = 'Pancard Number is already exists.';
            }
        }
        
        if($_POST['aadharcard'] != ""){
            $filterTempEmpAadharCard = mysqli_query($dbconn,"SELECT adharcard from employee where adharcard='".$_POST['aadharcard']."' and isDelete=0 and istatus=1 and isExitEmployee=0");
            if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
                echo 10;
                exit;
                //$output['message'] = 'Aadhar Card Number is already exists.';
            }
        }
        
        // $emp = mysqli_query($dbconn, "SELECT * FROM employee where  emp_name = '" . $_POST['emp_name'] . "' and isDelete='0' and istatus='1'  and isExitEmployee=0");
        // if (mysqli_num_rows($emp) == 0) {

            $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $_POST['designation'] . "' "));

            $data = array(
                "emp_name" => strtoupper($_POST['emp_name']),
                //"emp_other_info" => $_POST['emp_other_info'],
                "mno" => $_POST['mno'],
                "address" => $_POST['address'],
                "bankid" => $_POST['bankid'],
                "ecsno" => $_POST['ecsno'],
                "accountno" => $_POST['accountno'],
                "ifsccode" => $_POST['ifsccode'],
                "designationid" => $_POST['designation'],
                "designation" => $des['designation'],
                "pfcode" => $_POST['pfcode'],
                "employeecode" => $_POST['employeecode'],
                "adharcard" => $_POST['aadharcard'],
                "aadharImage" => strtolower($aadharImage),
                "pancard" => strtoupper($_POST['pancard']),
                "pancardImage" => strtolower($pancardImage),
//                "drivinglicense" => $_POST['drivinglicense'],
                //"electioncard" => $_POST['voterid'],
                "voteridImage" => strtolower($voteridImage),
                "passport" => $_POST['passport'],
                "passportImage" => strtolower($passportImage),
                //  "other" => $_POST['other'],
                "dateofbirth" => $_POST['dateofbirth'],
                "uan" => $_POST['uan'],
                //"dateofjoining" => date('m/y',strtotime($_POST['dateofjoining'])),
                "dateofjoining" => $_POST['dateofjoining'],
                "strFatherName" => $_POST['strFatherName'],
                "strMaritalStatus" => $_POST['strMaritalStatus'],
                "strNomineeName" => $_POST['strNomineeName'],
                "strNomineeRelation" => $_POST['strNomineeRelation'],
                "strNomineeAdharNo" => $_POST['strNomineeAdharNo'],
                // "strFamilyDetails" => $_POST['strFamilyDetails'],
                // "strRelation" => $_POST['strRelation'],
                "strPermanentAddress" => $_POST['strPermanentAddress'],
                
                "strEmergencyContactNo" => $_POST['strEmergencyContactNo'],
                "strQualification" => $_POST['strQualification'],
                "strExperience" => $_POST['strExperience'],
                "strMarriedDate" => $_POST['strMarriedDate'],
                "iSon" => isset($_POST['iSon']) ? $_POST['iSon'] : 0,
                "iDoughter" => isset($_POST['iDoughter']) ? $_POST['iDoughter'] : 0,
            
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "created_at" => date('Y-m-d H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'employee', $data);
            
            $strFamilyDetails = sizeof($_POST['strFamilyDetails']);
            mysqli_query($dbconn,"delete from EmployeeFamilyDetails where iEmpId='".$dealer_res."'");
            for($iCounter = 0;$iCounter < $strFamilyDetails; $iCounter++){
                if($_POST['strFamilyDetails'][$iCounter] != "" || $_POST['strRelation'][$iCounter] != 0){
                    $FamilyDetails = array(
                        "iEmpId" => $dealer_res,
                        "strFamilyDetails" => $_POST['strFamilyDetails'][$iCounter],
                        "iRelation" => $_POST['strRelation'][$iCounter],
                        "strEntryDate" => date('Y-m-d H:i:s'),
                        "strIP" => $_SERVER['REMOTE_ADDR'],
                        "iEntryBy" => $_SESSION['AdminId'],
                        "EntryDate" => date('Y-m-d H:i:s')
                    );
                    $dealerRes = $connect->insertrecord($dbconn, 'EmployeeFamilyDetails', $FamilyDetails);
                }
            }
            
            echo $statusMsg = $dealer_res ? '1' : '0';
        // } else {
        //     echo $statusMsg = '3';
        // }
        break;

    case "EditEmployee":
// //        echo "SELECT * FROM employee where  employeeId != '" . $_REQUEST['employeeId'] . "' and isDelete='0' and istatus='1' ";
//         $emp = mysqli_query($dbconn, "SELECT * FROM employee where employeeId = '" . $_REQUEST['employeeId'] . "' and isDelete='0' and istatus='1' ");
// //        if (mysqli_num_rows($emp) == 0) {
//         $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $_POST['designation'] . "' "));

//         $empData = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM employee where employeeId = '" . $_REQUEST['employeeId'] . "' and isDelete='0' and istatus='1' "));

//         $valid_extensions = array('pdf', 'PDF','jpg','JPG','jpeg','JPEG','png','PNG'); // valid extensions
//         $EntrDate = "";
//         if ($empData['strEntryDate'] != NULL || $empData['strEntryDate'] != '') {
//             $EntrDate = $empData['strEntryDate'];
//         } else {
//             $EntrDate = date('d-m-Y H:i:s');
//         }

//         $arr = explode(' ', $EntrDate);
//         $dateArrar = explode('-', $arr[0]);

//         if (!file_exists('Document/' . $dateArrar[2] . "/")) {
//             mkdir('Document/' . $dateArrar[2], 0777, TRUE);
//         }
//         if (!file_exists('Document/' . $dateArrar[2] . "/" . $dateArrar[1])) {
//             mkdir('Document/' . $dateArrar[2] . "/" . $dateArrar[1], 0777, TRUE);
//         }
//         if (!file_exists('Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0])) {
//             mkdir('Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0], 0777, TRUE);
//         }

//         $path = 'Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/";
//         //        $path = 'Document/'; // upload directory
//         if (!file_exists($path)) {
//             mkdir($path, 0777, TRUE);
//         }
//         $aadharImage = "";
//         $pancardImage = "";
//         $voteridImage = "";
//         $passportImage = "";
        
//         if(isset($_FILES['aadharImage']['name']) && $_FILES['aadharImage']['name'] != ""){
//             if ($_FILES['aadharImage']) {
//                 if ($empData['aadharImage'] != NULL || $empData['aadharImage'] != '') {
//                     unlink($path . $empData['aadharImage']);
//                 }
    
//                 $aadhar_img = $_FILES['aadharImage']['name'];
//                 $tmp = $_FILES['aadharImage']['tmp_name'];
    
//                 $ext = strtolower(pathinfo($aadhar_img, PATHINFO_EXTENSION));
//                 $aadhar_final_image = preg_replace('/\s+/', '_', $aadhar_img);
//                 if (in_array($ext, $valid_extensions)) {
//                     $aadhar_path = $path . strtolower($aadhar_final_image);
//                     if (move_uploaded_file($tmp, $aadhar_path)) {
//                         $aadharImage = $aadhar_final_image;
//                     }
//                 }
//             } else {
//                 $aadharImage = $empData['aadharImage'];
//             }
//         } else {
//             $aadharImage = $empData['aadharImage'];
//         }
//         if(isset($_FILES['pancardImage']['name']) && $_FILES['pancardImage']['name'] != ""){
//             if ($_FILES['pancardImage']) {
//                 if ($empData['pancardImage'] != NULL || $empData['pancardImage'] != '') {
//                     unlink($path . $empData['pancardImage']);
//                 }
//                 $pancard_img = $_FILES['pancardImage']['name'];
//                 $tmp = $_FILES['pancardImage']['tmp_name'];
    
//                 $ext = strtolower(pathinfo($pancard_img, PATHINFO_EXTENSION));
//                 $pancard_final_image = preg_replace('/\s+/', '_', $pancard_img);
//                 if (in_array($ext, $valid_extensions)) {
//                     $pancard_path = $path . strtolower($pancard_final_image);
//                     if (move_uploaded_file($tmp, $pancard_path)) {
//                         $pancardImage = $pancard_final_image;
//                     }
//                 }
//             } else {
//                 $pancardImage = $empData['pancardImage'];
//             }
//         } else {
//             $pancardImage = $empData['pancardImage'];
//         }
//         if(isset($_FILES['voteridImage']['name']) && $_FILES['voteridImage']['name'] != ""){
//             if ($_FILES['voteridImage']) {
//                 if ($empData['voteridImage'] != NULL || $empData['voteridImage'] != '') {
//                     unlink($path . $empData['voteridImage']);
//                 }
//                 $voterid_img = $_FILES['voteridImage']['name'];
//                 $tmp = $_FILES['voteridImage']['tmp_name'];
    
//                 $ext = strtolower(pathinfo($voterid_img, PATHINFO_EXTENSION));
//                 $voterid_final_image = preg_replace('/\s+/', '_', $voterid_img);
//                 if (in_array($ext, $valid_extensions)) {
//                     $voterid_path = $path . strtolower($voterid_final_image);
//                     if (move_uploaded_file($tmp, $voterid_path)) {
//                         $voteridImage = $voterid_final_image;
//                     }
//                 }
//             } else {
//                 $voteridImage = $empData['voteridImage'];
//             }
//         } else {
//             $voteridImage = $empData['voteridImage'];
//         }
//         if(isset($_FILES['passportImage']['name']) && $_FILES['passportImage']['name'] != ""){
//             if ($_FILES['passportImage']) {
//                 if ($empData['passportImage'] != NULL || $empData['passportImage'] != '') {
//                     unlink($path . $empData['passportImage']);
//                 }
//                 $passport_img = $_FILES['passportImage']['name'];
//                 $tmp = $_FILES['passportImage']['tmp_name'];
    
//                 $ext = strtolower(pathinfo($passport_img, PATHINFO_EXTENSION));
//                 $passport_final_image = preg_replace('/\s+/', '_', $passport_img);
//                 if (in_array($ext, $valid_extensions)) {
//                     $passport_path = $path . strtolower($passport_final_image);
//                     if (move_uploaded_file($tmp, $passport_path)) {
//                         $passportImage = $passport_final_image;
//                     }
//                 }
//             } else {
//                 $passportImage = $empData['passportImage'];
//             }
//         } else {
//             $passportImage = $empData['passportImage'];
//         }
//         if($_POST['mno'] != ""){
//             $filterTempEmpMno = mysqli_query($dbconn,"SELECT mno from employee where mno='".$_POST['mno']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
//             if(mysqli_num_rows($filterTempEmpMno) > 0){
//                 echo 4;
//                 exit;
//             }
//         }
//         if($_POST['ecsno'] != ""){
//             $filterTempEmpEcsno = mysqli_query($dbconn,"SELECT ecsno from employee where ecsno='".$_POST['ecsno']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
//             if(mysqli_num_rows($filterTempEmpEcsno) > 0){
//                 echo 5;
//                 exit;
//             }
//         }
//         // if($_POST['accountno'] != ""){
//         //     $filterTempEmpAccount = mysqli_query($dbconn,"SELECT accountno from employee where accountno='".$_POST['accountno']."' and isDelete=0 and istatus=1");
//         //     if(mysqli_num_rows($filterTempEmpAccount) > 0){
//         //             echo 6;
//         //     }
//         // }
//         if($_POST['pfcode'] != ""){
//             if($_POST['pfcode'] != 0){
//                 $filterTempEmpPFCode = mysqli_query($dbconn,"SELECT pfcode from employee where pfcode='".$_POST['pfcode']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
//                 if(mysqli_num_rows($filterTempEmpPFCode) > 0){
//                     echo 7;
//                     //$output['message'] = 'PF Code Number is already exists.';
//                     exit;
//                 }
//             }
//         }
//         if($_POST['uan'] != ""){
//             $filterTempEmpUan = mysqli_query($dbconn,"SELECT uan from employee where uan='".$_POST['uan']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
//             if(mysqli_num_rows($filterTempEmpUan) > 0){
//                 echo 8;
//                 //$output['message'] = 'UAN Number is already exists.';
//                 exit;
//             }
//         }
//         if($_POST['pancard'] != ""){
//             $filterTempEmpPancard = mysqli_query($dbconn,"SELECT pancard from employee where pancard='".$_POST['pancard']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
//             if(mysqli_num_rows($filterTempEmpPancard) > 0){
//                 echo 9;
//                 //$output['message'] = 'Pancard Number is already exists.';
//                 exit;
//             }
//         }
        
//         if($_POST['aadharcard'] != ""){
//             $filterTempEmpAadharCard = mysqli_query($dbconn,"SELECT adharcard from employee where adharcard='".$_POST['aadharcard']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
//             if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
//                 echo 10;
//                 //$output['message'] = 'Aadhar Card Number is already exists.';
//                 exit;
//             }
//         }
//         $dateofjoining = isset($_POST['dateofjoining']) ? date('m/y',strtotime($_POST['dateofjoining'])) : "";
//         $data = array(
//             "emp_name" => $_POST['emp_name'],
//             "emp_other_info" => isset($_POST['emp_other_info']) ? $_POST['emp_other_info'] : "",
//             "mno" => $_POST['mno'],
//             "address" => $_POST['address'],
//             "bankid" => $_POST['bankid'],
//             // "otherbankname" => $_POST['otherbankname'],
//             "ecsno" => $_POST['ecsno'],
//             "accountno" => $_POST['accountno'],
//             "ifsccode" => $_POST['ifsccode'],
//             //"salaryamt" => $_POST['salaryamt'],
//             "designationid" => $_POST['designation'],
//             "designation" => $des['designation'],
//             "pfcode" => $_POST['pfcode'],
//             "employeecode" => $_POST['employeecode'],
//             "adharcard" => $_POST['aadharcard'],
//             "aadharImage" => strtolower($aadharImage),
//             "pancard" => $_POST['pancard'],
//             "pancardImage" => strtolower($pancardImage),
// //                "drivinglicense" => $_POST['drivinglicense'],
//             //"electioncard" => $_POST['voterid'],
//             "voteridImage" => strtolower($voteridImage),
//             "passport" => isset($_POST['passport']) ? $_POST['passport'] : "",
//             "passportImage" => strtolower($passportImage),
//             //  "other" => $_POST['other'],
//             "dateofbirth" => $_POST['dateofbirth'],
//             "uan" => $_POST['uan'],
//             "dateofjoining" => $dateofjoining,
//             "strFatherName" => $_POST['strFatherName'],
//             "strMaritalStatus" => $_POST['strMaritalStatus'],
//             "strNomineeName" => $_POST['strNomineeName'],
//             "strNomineeRelation" => $_POST['strNomineeRelation'],
//             "strNomineeAdharNo" => $_POST['strNomineeAdharNo'],
//             // "strFamilyDetails" => $_POST['strFamilyDetails'],
//             // "strRelation" => $_POST['strRelation'],
//             "strPermanentAddress" => $_POST['strPermanentAddress'],
//             "strEntryDate" => $EntrDate,
//             "strIP" => $_SERVER['REMOTE_ADDR'],
//             "iUpdatedBy" => $_SESSION['AdminId'],
//             "UpdatedDate" => date('Y-m-d H:i:s')
//         );
//         $where = ' where  employeeId =' . $_REQUEST['employeeId'];
//         $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
        
//         $strFamilyDetails = sizeof($_POST['strFamilyDetails']);
//         mysqli_query($dbconn,"delete from EmployeeFamilyDetails where iEmpId='".$_REQUEST['employeeId']."'");
//         for($iCounter = 0;$iCounter < $strFamilyDetails; $iCounter++){
//             $FamilyDetails = array(
//                 "iEmpId" => $_REQUEST['employeeId'],
//                 "strFamilyDetails" => $_POST['strFamilyDetails'][$iCounter],
//                 "iRelation" => $_POST['strRelation'][$iCounter],
//                 "strEntryDate" => date('Y-m-d H:i:s'),
//                 "strIP" => $_SERVER['REMOTE_ADDR'],
//                 "iUpdatedBy" => $_SESSION['AdminId'],
//                 "UpdatedDate" => date('Y-m-d H:i:s')
//             );
//             $dealerRes = $connect->insertrecord($dbconn, 'EmployeeFamilyDetails', $FamilyDetails);
//         }
            
//         echo $statusMsg = $_REQUEST['employeeId'] ? '2' : '0';
// //        } 
// //        else {
// //            echo $statusMsg = '3';
// //        }
//        echo "SELECT * FROM employee where  employeeId != '" . $_REQUEST['employeeId'] . "' and isDelete='0' and istatus='1' ";
        $emp = mysqli_query($dbconn, "SELECT * FROM employee where employeeId = '" . $_REQUEST['employeeId'] . "' and isDelete='0' and istatus='1' ");
//        if (mysqli_num_rows($emp) == 0) {
        $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $_POST['designation'] . "' "));

        $empData = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM employee where employeeId = '" . $_REQUEST['employeeId'] . "' and isDelete='0' and istatus='1' "));

        $valid_extensions = array('pdf', 'PDF','jpg','JPG','jpeg','JPEG','png','PNG'); // valid extensions
        $EntrDate = "";
        if ($empData['strEntryDate'] != NULL || $empData['strEntryDate'] != '') {
            $EntrDate = $empData['strEntryDate'];
        } else {
            $EntrDate = date('d-m-Y H:i:s');
        }

        $arr = explode(' ', $EntrDate);
        $dateArrar = explode('-', $arr[0]);

        if (!file_exists('Document/' . $dateArrar[2] . "/")) {
            mkdir('Document/' . $dateArrar[2], 0777, TRUE);
        }
        if (!file_exists('Document/' . $dateArrar[2] . "/" . $dateArrar[1])) {
            mkdir('Document/' . $dateArrar[2] . "/" . $dateArrar[1], 0777, TRUE);
        }
        if (!file_exists('Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0])) {
            mkdir('Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0], 0777, TRUE);
        }

        $path = 'Document/' . $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/";
//        $path = 'Document/'; // upload directory
        if (!file_exists($path)) {
            mkdir($path, 0777, TRUE);
        }
        $aadharImage = "";
        $pancardImage = "";
        $voteridImage = "";
        $passportImage = "";
        
        if(isset($_FILES['aadharImage']['name']) && $_FILES['aadharImage']['name'] != ""){
            if ($_FILES['aadharImage']) {
                if ($empData['aadharImage'] != NULL || $empData['aadharImage'] != '') {
                    unlink($path . $empData['aadharImage']);
                }
    
                $aadhar_img = $_FILES['aadharImage']['name'];
                $tmp = $_FILES['aadharImage']['tmp_name'];
    
                $ext = strtolower(pathinfo($aadhar_img, PATHINFO_EXTENSION));
                $aadhar_final_image = preg_replace('/\s+/', '_', $aadhar_img);
                if (in_array($ext, $valid_extensions)) {
                    $aadhar_path = $path . strtolower($aadhar_final_image);
                    if (move_uploaded_file($tmp, $aadhar_path)) {
                        $aadharImage = $aadhar_final_image;
                    }
                }
            } else {
                $aadharImage = $empData['aadharImage'];
            }
        } else {
            $aadharImage = $empData['aadharImage'];
        }
        if(isset($_FILES['pancardImage']['name']) && $_FILES['pancardImage']['name'] != ""){
            if ($_FILES['pancardImage']) {
                if ($empData['pancardImage'] != NULL || $empData['pancardImage'] != '') {
                    unlink($path . $empData['pancardImage']);
                }
                $pancard_img = $_FILES['pancardImage']['name'];
                $tmp = $_FILES['pancardImage']['tmp_name'];
    
                $ext = strtolower(pathinfo($pancard_img, PATHINFO_EXTENSION));
                $pancard_final_image = preg_replace('/\s+/', '_', $pancard_img);
                if (in_array($ext, $valid_extensions)) {
                    $pancard_path = $path . strtolower($pancard_final_image);
                    if (move_uploaded_file($tmp, $pancard_path)) {
                        $pancardImage = $pancard_final_image;
                    }
                }
            } else {
                $pancardImage = $empData['pancardImage'];
            }
        } else {
            $pancardImage = $empData['pancardImage'];
        }
        if(isset($_FILES['voteridImage']['name']) && $_FILES['voteridImage']['name'] != ""){
            if ($_FILES['voteridImage']) {
                if ($empData['voteridImage'] != NULL || $empData['voteridImage'] != '') {
                    unlink($path . $empData['voteridImage']);
                }
                $voterid_img = $_FILES['voteridImage']['name'];
                $tmp = $_FILES['voteridImage']['tmp_name'];
    
                $ext = strtolower(pathinfo($voterid_img, PATHINFO_EXTENSION));
                $voterid_final_image = preg_replace('/\s+/', '_', $voterid_img);
                if (in_array($ext, $valid_extensions)) {
                    $voterid_path = $path . strtolower($voterid_final_image);
                    if (move_uploaded_file($tmp, $voterid_path)) {
                        $voteridImage = $voterid_final_image;
                    }
                }
            } else {
                $voteridImage = $empData['voteridImage'];
            }
        } else {
            $voteridImage = $empData['voteridImage'];
        }
        if(isset($_FILES['passportImage']['name']) && $_FILES['passportImage']['name'] != ""){
            if ($_FILES['passportImage']) {
                if ($empData['passportImage'] != NULL || $empData['passportImage'] != '') {
                    unlink($path . $empData['passportImage']);
                }
                $passport_img = $_FILES['passportImage']['name'];
                $tmp = $_FILES['passportImage']['tmp_name'];
    
                $ext = strtolower(pathinfo($passport_img, PATHINFO_EXTENSION));
                $passport_final_image = preg_replace('/\s+/', '_', $passport_img);
                if (in_array($ext, $valid_extensions)) {
                    $passport_path = $path . strtolower($passport_final_image);
                    if (move_uploaded_file($tmp, $passport_path)) {
                        $passportImage = $passport_final_image;
                    }
                }
            } else {
                $passportImage = $empData['passportImage'];
            }
        } else {
            $passportImage = $empData['passportImage'];
        }
        // if($_POST['mno'] != ""){
        //     $filterTempEmpMno = mysqli_query($dbconn,"SELECT mno from employee where mno='".$_POST['mno']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpMno) > 0){
        //         echo 4;
        //         exit;
        //     }
        // }
        // if($_POST['ecsno'] != ""){
        //     $filterTempEmpEcsno = mysqli_query($dbconn,"SELECT ecsno from employee where ecsno='".$_POST['ecsno']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpEcsno) > 0){
        //         echo 5;
        //         exit;
        //     }
        // }
        // // if($_POST['accountno'] != ""){
        // //     $filterTempEmpAccount = mysqli_query($dbconn,"SELECT accountno from employee where accountno='".$_POST['accountno']."' and isDelete=0 and istatus=1");
        // //     if(mysqli_num_rows($filterTempEmpAccount) > 0){
        // //             echo 6;
        // //     }
        // // }
        // if($_POST['pfcode'] != ""){
        //     if($_POST['pfcode'] != 0){
        //         $filterTempEmpPFCode = mysqli_query($dbconn,"SELECT pfcode from employee where pfcode='".$_POST['pfcode']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
        //         if(mysqli_num_rows($filterTempEmpPFCode) > 0){
        //             echo 7;
        //             //$output['message'] = 'PF Code Number is already exists.';
        //             exit;
        //         }
        //     }
        // }
        // if($_POST['uan'] != ""){
        //     $filterTempEmpUan = mysqli_query($dbconn,"SELECT uan from employee where uan='".$_POST['uan']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpUan) > 0){
        //         echo 8;
        //         //$output['message'] = 'UAN Number is already exists.';
        //         exit;
        //     }
        // }
        // if($_POST['pancard'] != ""){
        //     $filterTempEmpPancard = mysqli_query($dbconn,"SELECT pancard from employee where pancard='".$_POST['pancard']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpPancard) > 0){
        //         echo 9;
        //         //$output['message'] = 'Pancard Number is already exists.';
        //     }
        // }
        
        // if($_POST['aadharcard'] != ""){
        //     $filterTempEmpAadharCard = mysqli_query($dbconn,"SELECT adharcard from employee where adharcard='".$_POST['aadharcard']."' and employeeId!='". $_REQUEST['employeeId']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
        //         echo 10;
        //         //$output['message'] = 'Aadhar Card Number is already exists.';
        //         exit;
        //     }
        // }
        //$dateofjoining = isset($_POST['dateofjoining']) && $_POST['dateofjoining'] != "" ? date('d-m-Y',strtotime($_POST['dateofjoining'])) : "";
        $strExitDate = "";
        $isExitEmployee = 0;
        if(isset($_POST['strExitDate']) && $_POST['strExitDate'] != ""){
            $strExitDate = $_POST['strExitDate'];
            $isExitEmployee = 1;
        } else {
            $strExitDate = "";
            $isExitEmployee = "0";
        }
        
        $data = array(
            "emp_name" => ucwords(strtolower($_POST['emp_name'])),
            "emp_other_info" => ucwords(strtolower($_POST['emp_other_info'])),
            "mno" => $_POST['mno'],
            "address" => ucwords(strtolower($_POST['address'])),
            //"bankid" => $_POST['bankid'],
            // "otherbankname" => $_POST['otherbankname'],
            "ecsno" => $_POST['ecsno'],
            //"accountno" => $_POST['accountno'],
            //"ifsccode" => $_POST['ifsccode'],
            //"salaryamt" => $_POST['salaryamt'],
            "designationid" => $_POST['designation'],
            "designation" => ucwords(strtolower($des['designation'])),
            "pfcode" => $_POST['pfcode'],
            "employeecode" => $_POST['employeecode'],
            "adharcard" => $_POST['aadharcard'],
            "aadharImage" => strtolower($aadharImage),
            "pancard" => $_POST['pancard'],
            "pancardImage" => strtolower($pancardImage),
//                "drivinglicense" => $_POST['drivinglicense'],
            //"electioncard" => $_POST['voterid'],
            "voteridImage" => strtolower($voteridImage),
            "passport" => $_POST['passport'],
            "passportImage" => strtolower($passportImage),
            //  "other" => $_POST['other'],
            "dateofbirth" => $_POST['dateofbirth'],
            "uan" => $_POST['uan'],
            "dateofjoining" => $_POST['dateofjoining'],
            "strFatherName" => ucwords(strtolower($_POST['strFatherName'])),
            "strMaritalStatus" => ucwords(strtolower($_POST['strMaritalStatus'])),
            "strNomineeName" => ucwords(strtolower($_POST['strNomineeName'])),
            "strNomineeRelation" => ucwords(strtolower($_POST['strNomineeRelation'])),
            "strNomineeAdharNo" => $_POST['strNomineeAdharNo'],
            // "strFamilyDetails" => $_POST['strFamilyDetails'],
            // "strRelation" => $_POST['strRelation'],
            "strPermanentAddress" => ucwords(strtolower($_POST['strPermanentAddress'])),
            
            "strEmergencyContactNo" => $_POST['strEmergencyContactNo'],
            "strQualification" => $_POST['strQualification'],
            "strExperience" => $_POST['strExperience'],
            "strMarriedDate" => $_POST['strMarriedDate'],
            "iSon" => isset($_POST['iSon']) ? $_POST['iSon'] : 0,
            "iDoughter" => isset($_POST['iDoughter']) ? $_POST['iDoughter'] : 0,
            
            "strEntryDate" => $EntrDate,
            "isExitEmployee" => $isExitEmployee,
            "strExitDate" => $strExitDate,
            "strIP" => $_SERVER['REMOTE_ADDR']
        );
        
        
        $where = ' where  employeeId =' . $_REQUEST['employeeId'];
        $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
        
        $strFamilyDetails = sizeof($_POST['strFamilyDetails']);
        mysqli_query($dbconn,"delete from EmployeeFamilyDetails where iEmpId='".$_REQUEST['employeeId']."'");
        for($iCounter = 0;$iCounter < $strFamilyDetails; $iCounter++){
            
            if($_POST['strFamilyDetails'][$iCounter] != "" || $_POST['strRelation'][$iCounter] != 0){
                $FamilyDetails = array(
                    "iEmpId" => $_REQUEST['employeeId'],
                    "strFamilyDetails" => ucwords(strtolower($_POST['strFamilyDetails'][$iCounter])),
                    "iRelation" => $_POST['strRelation'][$iCounter],
                    "strEntryDate" => date('Y-m-d H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR']
                );
                $dealerRes = $connect->insertrecord($dbconn, 'EmployeeFamilyDetails', $FamilyDetails);
            }
        }
            
        echo $statusMsg = $_REQUEST['employeeId'] ? '2' : '0';
//        } 
//        else {
//            echo $statusMsg = '3';
//        }
        break;

    case "AddSalaryDetails":
        $iCounter = 0;
        $Company = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_POST['companyId'] . "'"));
        $inc = $_POST['inc'];
        $emp_id = $_POST['emp_id_' . $inc];
        $Ename = $_POST['Ename_' . $inc];
        $workingdays = $_POST['workingdays_' . $inc];
        $othours = $_POST['othours_' . $inc];
        $otrate = $_POST['otrate_' . $inc];
        $salaryMaster = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT month FROM salarymaster WHERE salarymasterId='" . (int) $_POST['salaryId'] . "'"));
        $salaryAdvances = $salaryMaster ? getCompanyReportAdvances($dbconn, $_POST['companyId'], $salaryMaster['month']) : array(); // $deductionifany = $_POST['deductionifany_' . $inc];

        
        $count = $inc;
        $dailyrate = 0;
        $otrate = 0;
        $basicrate = 0;
        $othours = 0;
        $dealer_res = '';
        for ($iCounter = 1; $iCounter <= $count; $iCounter++) {
            $inc = $iCounter;
            $da = $_POST['da_' . $inc];
            $hra = $_POST['hra_' . $inc];
            //$pt = $_POST['pt_'.$inc];
            $national_holiday = $_POST['national_holiday_payment_' . $inc];
            $deductionifany = isset($_POST['deductionifany_' . $inc]) ? max(0, (float) $_POST['deductionifany_' . $inc]) : 0;
            $storedAdvance = getEmployeeCompanyReportAdvance($salaryAdvances, $_POST['emp_id_' . $inc]);
            $advance = $storedAdvance > 0
                ? $storedAdvance
                : (isset($_POST['advance_' . $inc]) ? max(0, (float) $_POST['advance_' . $inc]) : 0);

            if ($_POST['workingdays_' . $inc] != '' || $_POST['workingdays_' . $inc] != null) {
                $Sql123 = mysqli_query($dbconn, "delete from salarydetails where salarydetails.salaryId = '" . $_POST['salaryId'] . "' and salarydetails.companyId = '" . $_POST['companyId'] . "' and salarydetails.emp_id = '" . $_POST['emp_id_' . $inc] . "'");
                //    echo "SELECT * FROM `comskill`  where   empid='" . $_POST['emp_id'][$iCounter] . "' and companyid = '" . $_POST['companyId'] . "' and skill != '' ";
                $Companyskill = mysqli_query($dbconn, "SELECT * FROM `comskill`  where   empid='" . $_POST['emp_id_' . $inc] . "' and companyid = '" . $_POST['companyId'] . "' and skill != '' ");

                if (mysqli_num_rows($Companyskill) > 0) {
                    $CompanySkillArry = mysqli_fetch_array($Companyskill);
                    if ($CompanySkillArry['skill'] == 'Skill') {
                        $dailyrate = $Company['skil'];
                    }
                    if ($CompanySkillArry['skill'] == 'UnSkill') {
                        $dailyrate = $Company['unskill'];
                    }
                    if ($CompanySkillArry['skill'] == 'SemiSkill') {
                        $dailyrate = $Company['semiskill'];
                    }
                    if ($CompanySkillArry['skill'] == 'highlyskilled') {
                        $dailyrate = $Company['highlyskilled'];
                    }
                } else {
                    if ($_POST['Skill_' . $inc] == 'Skill') {
                        $dailyrate = $Company['skil'];
                    } else if ($_POST['Skill_' . $inc] == 'UnSkill') {
                        $dailyrate = $Company['unskill'];
                    } else if ($_POST['Skill_' . $inc] == 'SemiSkill') {
                        $dailyrate = $Company['semiskill'];
                    } else if ($_POST['Skill_' . $inc] == 'HighlySkill') {
                        $dailyrate = $Company['highlyskilled'];
                    }else {
                        $dailyrate = $Company['semiskill'];
                    }
                }

                if (isset($_POST['workingdays_' . $inc])) {
                    $basicrate = $_POST['workingdays_' . $inc] * $dailyrate;
                }
                if ($Company['MedicalAllowance'] == 'YES') {

                    $MedicalAllowance = ($basicrate * $Company['MedicalAllowancePer']) / 100;
                } else {
                    $MedicalAllowance = 0;
                }
                $national_holiday_payment = (float)$dailyrate * (float)$national_holiday;
                $basicpf = (float)$basicrate + (float)$national_holiday_payment;
                $iBonusAmt = 0;
                if ($Company['strBonus'] == 'YES') {
                    $iBonusAmt = (float)$basicrate * 0.0833;
                } else {
                    $iBonusAmt = 0;
                }
                $iLeaveAmt = 0;
                if ($Company['strLeave'] == 'YES') {
                    $iLeaveAmt = (float)$basicrate / 20;
                } else {
                    $iLeaveAmt = 0;
                }
                
                //$pf = $basicrate * 0.12;
                $pf = $basicpf * 0.12;
                if($Company['iDailyWorkingRate'] > 0){
                    $iDailyWorkingRate = $Company['iDailyWorkingRate'];
                } else {
                    $iDailyWorkingRate = 8;
                }
                // Old Code
                //$overtimerate = $dailyrate / 8;

                // New Code 
                $overtimerate = $dailyrate / $iDailyWorkingRate;
                if ($_POST['otrate_' . $inc] == '') {
                    $otrate = 0;
                } else {
                    $otrate = $_POST['otrate_' . $inc];
                }

                if ($_POST['othours_' . $inc] == '') {
                    $othours = 0;
                } else {
                    $othours = $_POST['othours_' . $inc];
                }
                $overtime = $overtimerate * $otrate * $othours;
                $total = (float)$basicrate + (float)$overtime + (float)$national_holiday_payment + (float)$da + (float)$hra + (float)$iBonusAmt + (float)$iLeaveAmt;
                if ($Company['ESI'] == 'YES') {
//                    $ecs = round($total) * 0.0175;
                    $ecs = round($total) * 0.0075;
                    //0.0475
                } else {
                    $ecs = 0;
                }

                // professional tex calculation
                $pt = '0';
                if ($Company['pf'] == 'YES') {
                    if ($total < 5999) {
                        $pt = 0;
                    } 
                    // else if ($total > 5999 && $total < 8999) {
                    //     $pt = 80;
                    // } else if ($total > 8999 && $total < 11999) {
                    //     $pt = 150;
                    // } 
                    else if ($total > 11999) {
                        $pt = 200;
                    }
                } else {
                    $pt = '0';
                }
                // $pf = round($total) * 0.12;

                $netamt1 = $total - $ecs - $pf - $pt - $deductionifany - $advance; // $netamt1 = $total - $ecs - $pf - $pt - $deductionifany;
                $netamt = $netamt1 + $MedicalAllowance;
                $dataarray = array(
                    "salaryId" => $_POST['salaryId'],
                    "salarypaiddate" => $_POST['salarypaiddate'],
                    "emp_id" => $_POST['emp_id_' . $inc],
                    "name" => $_POST['Ename_' . $inc],
                    // "designation" => $_POST['designation'],
                    "companyId" => $_POST['companyId'],
                    //"workinghours" => $_POST['workinghours'],
                    "workingdays" => $_POST['workingdays_' . $inc],
                    "basicwages" => round($basicrate,2),
                    "skillrate" => round($dailyrate,2),
                    "othours" => $othours,
                    //"workrate" => $_POST['workrate'],
                    "otrate" => $otrate,
                    "totalovertime" => round($overtime,2),
                    "da" => round((float)$da,2),
                    "hra" => round((float)$hra,2),
                    "national_holiday_payment" => round((float)$national_holiday_payment),
                    "MedicalAllowanceamt" => round((float)$MedicalAllowance),
                    "total" => round((float)$total,2),
                    "esi" => round((float)$ecs,2),
                    "pf" => round((float)$pf,2),
                    "pt" => $pt,
                    "iNoOfNatioanHoliday" => $national_holiday,
                    "deductionifany" => $deductionifany,
                    "advance" => round((float) $advance, 2),
                    "netamountpaid" => ceil((float)$netamt),
                    //"salaryamt" => $_POST['salaryamt'][$iCounter],
                    "iBonusAmt" => round((float)$iBonusAmt),
                    "iLeaveAmt" => round((float)$iLeaveAmt),
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                );
                $dealer_res = $connect->insertrecord($dbconn, 'salarydetails', $dataarray);
            }
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "Editmulticompany":

        if ($_POST['workingdays'] != '') {
            $otamt = (($_POST['rate'] / 8) * $_POST['otamt']) * $_POST['othours'];
            $PresentAmount = $_POST['workingdays'] * $_POST['rate'];
            $totalamt = $otamt + $PresentAmount;
            if (!isset($_POST['adv'])) {
                $adv = 0;
            } else {
                $adv = $_POST['adv'];
            }
            if (!isset($_POST['adv_two'])) {
                $adv_two = 0;
            } else {
                $adv_two = $_POST['adv_two'];
            }
            if ($otamt == 0) {
                $otamt = '0';
            }
            $totalAdv = $adv + $adv_two;
            $total = $totalamt - $totalAdv;
            // $total = $totalamt - $_POST['adv'];
            $balance1 = $total + $_POST['Fa'] + $_POST['Ta'];
            if ($_POST['pay_cash'] == 0) {
                $pay_cash = '0';
            } else {
                $pay_cash = '1';
            }

            $data = array
                (
                "name" => $_POST['name'],
                "workingdays" => $_POST['workingdays'],
                "rate" => $_POST['rate'],
                "othours" => $_POST['othours'],
                "PresentAmount" => $PresentAmount,
                "otamt" => $otamt,
                "totalamt" => $totalamt,
                "adv" => $_POST['adv'],
                "adv_one_paid" => $_POST['adv_one_paid'],
                "adv_two" => $_POST['adv_two'],
                "adv_two_paid" => $_POST['adv_two_paid'],
                "total" => $total,
                "Fa" => $_POST['Fa'],
                "Ta" => $_POST['Ta'],
                "pay_cash" => $pay_cash,
                "balance1" => $balance1,
                "date" => $_POST['date'],
                "iUpdatedBy" => $_SESSION['AdminId'],
                "UpdatedDate" => date('d-m-Y H:i:s')
            );
            $where = ' where  multicompanyid =' . trim($_POST['multicompanyid']);
            $dealer_res = $connect->updaterecord($dbconn, 'multicompany', $data, $where);
        }
        echo $statusMsg = $dealer_res ? '2' : '0';
        break;

    case "editSalaryDetails":
        // print_r($_POST);
        // $emp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId ='" . $_POST['emp_id'] . "'"));

        // $Company = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_POST['companyId'] . "'"));




        // if (isset($_POST['workingdays'])) {
        //     $basicrate = $_POST['workingdays'] * $_POST['skillrate'];
        // }

        // if ($Company['MedicalAllowance'] == 'YES') {

        //     $MedicalAllowance = ($basicrate * $Company['MedicalAllowancePer']) / 100;
        // } else {
        //     $MedicalAllowance = 0;
        // }
        // $da = $_POST['da'];
        // $hra = $_POST['hra'];
        // $national_holiday = $_POST['national_holiday_payment'];
        
        // if(isset($national_holiday) || $national_holiday != 0){
        //     $national_holiday_payment = $_POST['skillrate'] * $national_holiday;
        // }else {
        //     $national_holiday_payment = 0;
        // }
        // $basicpf = $basicrate + $national_holiday_payment;
        // //$pf = $basicrate * 0.12;
        // $pf = $basicpf * 0.12;
        // // $iBonusAmt = $basicrate * 0.0833;
        // // $iLeaveAmt = $basicrate / 20;
        // $iBonusAmt = 0;
        // if ($Company['strBonus'] == 'YES') {
        //     $iBonusAmt = (float)$basicrate * 0.0833;
        // } else {
        //     $iBonusAmt = 0;
        // }
        // $iLeaveAmt = 0;
        // if ($Company['strLeave'] == 'YES') {
        //     $iLeaveAmt = (float)$basicrate / 20;
        // } else {
        //     $iLeaveAmt = 0;
        // }
        // $overtimerate = $_POST['skillrate'] / 8;
        
        // $overtime = $overtimerate * $_POST['otrate'] * $_POST['othours'];
        // $total = $basicrate + $overtime + $national_holiday_payment + $da + $hra + $iBonusAmt + $iLeaveAmt;
        // //$total = $basicrate + round($overtime);
        // if ($Company['ESI'] == 'YES') {
        //     $totalEsi = round($total) * 0.0075;
        //     $ecs = round($totalEsi);
        // } else {
        //     $ecs = "0";
        // }
        // //$pf = round($total) * 0.12;
        // $deductionifany = $_POST['deductionifany'];
        // //$pt = $_POST['pt'];
        // $pt = '0';
        // if ($Company['pf'] == 'YES') {
        //     if ($total < 5999) {
        //         5710 > 5999;
        //         $pt = 0;
        //     } 
        //     // else if ($total > 5999 && $total < 8999) {
        //     //     $pt = 80;
        //     // } else if ($total > 8999 && $total < 11999) {
        //     //     $pt = 150;
        //     // } 
        //     else if ($total > 11999) {
        //         $pt = 200;
        //     }
        // } else {
        //     $pt = '0';
        // }
        // // $pf = round($total) * 0.12;
        // $netamt1 = $total - $ecs - $pf - $pt - $deductionifany;
        // $netamt = $netamt1 + $MedicalAllowance;

        // /*$netamt1 = $total - $ecs - $pf - $pt;

        // $netamt1 = $total - $ecs - $pf - $deductionifany;
        // $netamt = $netamt1 + $MedicalAllowance;*/

        // $data = array(
        //     "salaryId" => $_POST['salaryId'],
        //     "name" => $_POST['name'],
        //     "companyId" => $_POST['companyId'],
        //     "workingdays" => $_POST['workingdays'],
        //     "othours" => $_POST['othours'],
        //     "otrate" => $_POST['otrate'],
        //     "basicwages" => round($basicrate),
        //     "skillrate" => round($_POST['skillrate']),
        //     "totalovertime" => round($overtime),
        //     "MedicalAllowanceamt" => $MedicalAllowance,
        //     "da" => round($da,2),
        //     "hra" => round($hra,2),
        //     "national_holiday_payment" => $national_holiday_payment,
        //     "total" => round($total),
        //     "esi" => $ecs,
        //     "iNoOfNatioanHoliday" => $national_holiday,
        //     "pf" => round($pf),
        //     "pt" => $pt,
        //     "deductionifany" => $deductionifany,
        //     "netamountpaid" => ceil($netamt),
        //     "iBonusAmt" => round($iBonusAmt),
        //     "iLeaveAmt" => round($iLeaveAmt),
        //     "strEntryDate" => date('d-m-Y H:i:s'),
        //     "strIP" => $_SERVER['REMOTE_ADDR'],
        //     "iUpdatedBy" => $_SESSION['AdminId']
        // );
        // $where = ' where  salarydetailsId =' . $_REQUEST['salarydetailsId'];
        // $query = "update salarydetails set salaryId='".$_POST['salaryId']."',name='".$_POST['name']."',
        //     companyId='".$_POST['companyId']."',workingdays='".$_POST['workingdays']."' ,
        //     othours='".$_POST['othours']."',otrate='".$_POST['otrate']."',
        //     basicwages='".round($basicrate)."',skillrate='".round($_POST['skillrate'])."',
        //     totalovertime='".round($overtime)."',MedicalAllowanceamt='".$MedicalAllowance."',
        //     da='".round($da,2)."',hra='".round($hra,2)."', national_holiday_payment='".$national_holiday_payment."',
        //     total='".round($total)."',esi='".$ecs."', iNoOfNatioanHoliday='".$national_holiday."',
        //     pf='".round($pf)."', pt='".$pt."',deductionifany='".$deductionifany."', netamountpaid='".ceil($netamt)."',
        //     strEntryDate='".date('d-m-Y H:i:s')."',strIP='".$_SERVER['REMOTE_ADDR']."', iBonusAmt='".ceil($iBonusAmt)."',
        //     , iLeaveAmt='".ceil($iLeaveAmt)."', iUpdatedBy='".$_SESSION['AdminId']."',UpdatedDate='".date('d-m-Y H:i:s')."' " . $where;
        // $result = mysqli_query($dbconn,$query) or die(mysqli_connect_error());
        // $id = mysqli_affected_rows($dbconn);
        // //$dealer_res = $connect->updaterecord($dbconn, 'salarydetails', $data, $where);
        // echo $statusMsg = $id ? '2' : '0';
        
        $emp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId ='" . $_POST['emp_id'] . "'"));

        $Company = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $_POST['companyId'] . "'"));




        if (isset($_POST['workingdays'])) {
            $basicrate = $_POST['workingdays'] * $_POST['skillrate'];
        }

        if ($Company['MedicalAllowance'] == 'YES') {

            $MedicalAllowance = ($basicrate * $Company['MedicalAllowancePer']) / 100;
        } else {
            $MedicalAllowance = 0;
        }
        $da = $_POST['da'];
        $hra = $_POST['hra'];
        $national_holiday = $_POST['national_holiday_payment'];
        
        if(isset($national_holiday) || $national_holiday != 0){
            $national_holiday_payment = $_POST['skillrate'] * $national_holiday;
        }else {
            $national_holiday_payment = 0;
        }
        $basicpf = $basicrate + $national_holiday_payment;
        //$pf = $basicrate * 0.12;
        $pf = $basicpf * 0.12;
        // $iBonusAmt = $basicrate * 0.0833;
        // $iLeaveAmt = $basicrate / 20;
        $iBonusAmt = 0;
        if ($Company['strBonus'] == 'YES') {
            $iBonusAmt = (float)$basicrate * 0.0833;
        } else {
            $iBonusAmt = 0;
        }
        $iLeaveAmt = 0;
        if ($Company['strLeave'] == 'YES') {
            $iLeaveAmt = (float)$basicrate / 20;
        } else {
            $iLeaveAmt = 0;
        }
        $iDailyWorkingRate=0;
        if($Company['iDailyWorkingRate'] > 0){
            $iDailyWorkingRate = $Company['iDailyWorkingRate'];
        } else {
            $iDailyWorkingRate = 8;
        }
        //$overtimerate = $_POST['skillrate'] / 8;
        $overtimerate = $_POST['skillrate'] / $iDailyWorkingRate;
        
        $overtime = $overtimerate * $_POST['otrate'] * $_POST['othours'];
        $total = $basicrate + $overtime + $national_holiday_payment + $da + $hra + $iBonusAmt + $iLeaveAmt;
        //$total = $basicrate + round($overtime);
        if ($Company['ESI'] == 'YES') {
            $totalEsi = round($total) * 0.0075;
            $ecs = round($totalEsi,2);
        } else {
            $ecs = "0";
        }
        //$pf = round($total) * 0.12;
        $deductionifany = $_POST['deductionifany'];
        //$pt = $_POST['pt'];
        $pt = '0';
        if ($Company['pf'] == 'YES') {
            if ($total < 5999) {
                5710 > 5999;
                $pt = 0;
            } 
            // else if ($total > 5999 && $total < 8999) {
            //     $pt = 80;
            // } else if ($total > 8999 && $total < 11999) {
            //     $pt = 150;
            // } 
            else if ($total > 11999) {
                $pt = 200;
            }
        } else {
            $pt = '0';
        }
        // $pf = round($total) * 0.12;
        $netamt1 = $total - $ecs - $pf - $pt - $deductionifany;
        $netamt = $netamt1 + $MedicalAllowance;

        /*$netamt1 = $total - $ecs - $pf - $pt;

        $netamt1 = $total - $ecs - $pf - $deductionifany;
        $netamt = $netamt1 + $MedicalAllowance;*/

        $data = array(
            "salaryId" => $_POST['salaryId'],
            "name" => ucwords(strtolower($_POST['name'])),
            "companyId" => $_POST['companyId'],
            "workingdays" => $_POST['workingdays'],
            "othours" => $_POST['othours'],
            "otrate" => $_POST['otrate'],
            "basicwages" => round($basicrate),
            "skillrate" => round($_POST['skillrate']),
            "totalovertime" => round($overtime),
            "MedicalAllowanceamt" => $MedicalAllowance,
            "da" => round($da,2),
            "hra" => round($hra,2),
            "national_holiday_payment" => $national_holiday_payment,
            "total" => round($total),
            "esi" => $ecs,
            "iNoOfNatioanHoliday" => $national_holiday,
            "pf" => round($pf),
            "pt" => $pt,
            "deductionifany" => $deductionifany,
            "netamountpaid" => ceil($netamt),
            "iBonusAmt" => round($iBonusAmt),
            "iLeaveAmt" => round($iLeaveAmt),
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR']
        );
        $where = ' where  salarydetailsId =' . $_REQUEST['salarydetailsId'];
        $query = "update salarydetails set salaryId='".$_POST['salaryId']."',name='".$_POST['name']."',
            companyId='".$_POST['companyId']."',workingdays='".$_POST['workingdays']."' ,
            othours='".$_POST['othours']."',otrate='".$_POST['otrate']."',
            basicwages='".round($basicrate)."',skillrate='".round($_POST['skillrate'])."',
            totalovertime='".round($overtime)."',MedicalAllowanceamt='".$MedicalAllowance."',
            da='".round($da,2)."',hra='".round($hra,2)."', national_holiday_payment='".$national_holiday_payment."',
            total='".round($total)."',esi='".$ecs."', iNoOfNatioanHoliday='".$national_holiday."',
            pf='".round($pf,2)."', pt='".$pt."',deductionifany='".$deductionifany."', netamountpaid='".ceil($netamt)."',
            strEntryDate='".date('d-m-Y H:i:s')."',strIP='".$_SERVER['REMOTE_ADDR']."', iBonusAmt='".ceil($iBonusAmt)."', iLeaveAmt='".ceil($iLeaveAmt)."' " . $where;
        $result = mysqli_query($dbconn,$query) or die(mysqli_connect_error());
        $id = mysqli_affected_rows($dbconn);
        //$dealer_res = $connect->updaterecord($dbconn, 'salarydetails', $data, $where);
        echo $statusMsg = $_REQUEST['salarydetailsId'] ? '2' : '0';
        
        break;

    case "AddEmployeeLedger":
        $ledger = mysqli_query($dbconn, "SELECT SUM(`credit`) as totalcredit,SUM(`debit`) as totaldebit ,SUM(`balance`) as totalbalance FROM `ledger` where emp_id='" . $_POST['emp_id'] . "' and isDelete='0' ");
        if (mysqli_num_rows($ledger) > 0) {
            $ledgerdata = mysqli_fetch_array($ledger);
            $cradit = $ledgerdata['totalcredit'];
            $debit = $ledgerdata['totaldebit'];
            $opbalance = $cradit - $debit;
        }
        $cradit = 0;
        $debit = 0;
        if (isset($_POST['Type'])) {
            if ($_POST['Type'] == 'credit') {
                $cradit = $_POST['Amount'];
                $balance = $opbalance + $_POST['Amount'];
            } else {
                $debit = $_POST['Amount'];
                $balance = $opbalance - $_POST['Amount'];
            }
        }
        // $balance = $debit - $cradit;
        $data = array(
            "emp_id" => $_POST['emp_id'],
            "credit" => $cradit,
            "debit" => $debit,
            "balance" => $balance,
            'opbalance' => $opbalance,
            "comment" => $_POST['Comment'],
            "strEntryDate" => date('d-m-Y'),
            "strIP" => $_SERVER['REMOTE_ADDR']
        );
        $dealer_res = $connect->insertrecord($dbconn, 'ledger', $data);
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "daleteall":
        //  print_r($_POST);
        //        exit;
        $CheckList = $_POST['check_list'];
        foreach ($CheckList as $key => $value) {
            $data = array(
                "isDelete" => '1',
                "strEntryDate" => date('d-m-Y H:i:s')
            );
            $where = ' where ledgerid =' . trim($value);
            //$update = $connect->updaterecord($dbconn, 'ledger', $where);
            $dealer_res = $connect->updaterecord($dbconn, 'ledger', $data, $where);
        }

        break;

    case "multicompany":
        $iCounter = 0;
        $empname = $_POST['emp_id'];
        $count = count($empname);

        for ($iCounter = 0; $iCounter < $count; $iCounter++) {
            $dataarray = array
                (
                "emp_id" => $_POST['emp_id'][$iCounter],
                "name" => $_POST['name'][$iCounter],
                "salaryamt" => $_POST['salaryamt'][$iCounter],
                "workingdays" => $_POST['workingdays'][$iCounter],
                "othours" => $_POST['othours'][$iCounter],
                "PresentAmount" => $_POST['PresentAmount'][$iCounter],
                "otamt" => $_POST['otamt'][$iCounter],
                "totalamt" => $_POST['totalamt'][$iCounter],
                "adv" => $_POST['adv'][$iCounter],
                "total" => $_POST['total'][$iCounter],
                "Fa" => $_POST['Fa'][$iCounter],
                "Ta" => $_POST['Ta'][$iCounter],
                "balance1" => $_POST['balance1'][$iCounter],
                "netamountpaid" => $_POST['netamountpaid'][$iCounter],
                "balance2" => $_POST['balance2'][$iCounter],
                "date" => $_POST['date'][$iCounter],
                "bankid" => $_POST['bankid'][$iCounter],
                "ecsno" => $_POST['ecsno'][$iCounter],
                "pfcode" => $_POST['pfcode'][$iCounter],
                "accountno" => $_POST['accountno'][$iCounter],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'multicompany', $dataarray);
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "multicompanydeletedata":

        $CheckList = $_POST['check_list'];
        //echo 'delete from employee where employeeId in ('.implode("," ,  $_POST['check_list']).')';
        $dealer_res = mysqli_query($dbconn, 'delete from multicompany where multicompanyid in (' . implode(",", $_POST['check_list']) . ')');
        //$dealer_res = $connect->deleterecord($dbconn, 'employee', $where);
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "companydeletedata":
        $CheckList = $_POST['check_list'];
        //echo 'delete from employee where employeeId in ('.implode("," ,  $_POST['check_list']).')';
        $dealer_res = mysqli_query($dbconn, 'delete from salarydetails where salarydetailsId in (' . implode(",", $_POST['check_list']) . ')');
        //$dealer_res = $connect->deleterecord($dbconn, 'employee', $where);
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "employeedeletedata":
        $CheckList = $_POST['check_list'];
        //echo 'delete from employee where employeeId in ('.implode("," ,  $_POST['check_list']).')';
        $dealer_res = mysqli_query($dbconn, 'delete from employee where employeeId in (' . implode(",", $_POST['check_list']) . ')');
        //$dealer_res = $connect->deleterecord($dbconn, 'employee', $where);
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "AddmultycompanySalaryDetails":
        
        $inc = $_POST['emp_id'];
        //echo $_POST['workingdays_'.$inc];

        if ($_POST['workingdays_' . $inc] != '') {
            $otamt = (($_POST['rate_' . $inc] / 8) * $_POST['otrate_' . $inc]) * $_POST['othours_' . $inc];
            $PresentAmount = $_POST['workingdays_' . $inc] * $_POST['rate_' . $inc];
            $totalamt = $otamt + $PresentAmount;
            $totalAdv = $_POST['adv_' . $inc] + $_POST['adv_two_' . $inc];
            $total = $totalamt - $totalAdv;
            $balance1 = $total + $_POST['fa_' . $inc] + $_POST['ta_' . $inc];
            $dataarray = array
                (
                "emp_id" => $_POST['emp_id'],
                "name" => $_POST['Ename_' . $inc],
                "workingdays" => $_POST['workingdays_' . $inc],
                "rate" => $_POST['rate_' . $inc],
                "othours" => $_POST['othours_' . $inc],
                "PresentAmount" => $PresentAmount,
                "otamt" => $otamt,
                "totalamt" => $totalamt,
                "adv" => $_POST['adv_' . $inc],
                "adv_one_paid" => $_POST['adv_one_paid_' . $inc],
                "adv_two" => $_POST['adv_two_' . $inc],
                "adv_two_paid" => $_POST['adv_two_paid_' . $inc],
                "total" => $total,
                "Fa" => $_POST['fa_' . $inc],
                "Ta" => $_POST['ta_' . $inc],
                //"pay_cash" => $_POST['pay_cash_' . $inc],
                "balance1" => $balance1,
                /*"da" => $_POST['da_'.$inc],
                "hra" => $_POST['hra_'.$inc],
                "national_holiday_payment" => $_POST['national_holiday_payment_'.$inc],*/
//                "netamountpaid" => $_POST['netamountpaid'][$iCounter],
//                "balance2" => $_POST['balance2'][$iCounter],
                //"date" => $_POST['date_' . $inc],
//                "bankid" => $_POST['bankid'][$iCounter],
//                "ecsno" => $_POST['ecsno'][$iCounter],
//                "pfcode" => $_POST['pfcode'][$iCounter],
                "companysalarymasterId" => $_POST['companysalarymasterId'],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            //print_r($dataarray);exit;
            $dealer_res = $connect->insertrecord($dbconn, 'multicompany', $dataarray);
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;

    case "empcompskill":

        $iCounter = 0;
        $comp = $_POST['companymasterId'];
        $count = count($comp);

        for ($iCounter = 0; $iCounter < $count; $iCounter++) {
            $q = mysqli_fetch_array(mysqli_query($dbconn, "DELETE FROM `comskill` WHERE `empid`='" . $_POST['employeeId'] . "' and companyid='" . $_POST['companymasterId'][$iCounter] . "'"));
            $dataarray = array
                (
                "empid" => $_POST['employeeId'],
                "companyid" => $_POST['companymasterId'][$iCounter],
                "skill" => $_POST['Skill'][$iCounter],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'comskill', $dataarray);
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;
        
    case "editProfile":
        $data = array(
            "strCompanyName" => $_POST['strCompanyName'],
            "strAddress" => $_POST['strAddress'],
            "strEstablishedYear" => $_POST['strEstablishedYear'],
            "strFirmRegistrationNo" => $_POST['strFirmRegistrationNo'],
            "strBusinessPartner" => $_POST['strBusinessPartner'],
            "PhoneNumber" => $_POST['PhoneNumber'],
            "MobileNumber" => $_POST['MobileNumber'],
            "strEmail" => $_POST['Email'],
            "strTypeOfBusiness" => $_POST['strTypeOfBusiness'],
            "iGSTIN" => $_POST['iGSTIN'],
            "strPINno" => $_POST['strPINno'],
            "strPFCodeNo" => $_POST['strPFCodeNo'],
            "strESICNo" => $_POST['strESICNo'],
            "MSMEUdyogAadharNo" => $_POST['MSMEUdyogAadharNo'],
            "strBankName" => $_POST['strBankName'],
            "strBranchNameandAddress" => $_POST['strBranchNameandAddress'],
            "IFSCCode" => $_POST['IFSCCode'],
            "TypeOfAccount" => $_POST['TypeOfAccount'],
            "AccountNumber" => $_POST['AccountNumber'],
            "MicrCode" => $_POST['MicrCode'],
        );
        $where = " where id='".$_POST['id']."'";
        $update = $connect->updaterecord($dbconn,"admin",$data,$where);
        echo $status = $update ? 1 : 0;
    break;
    
    case "editsecretcode":
        $data = array(
            "secretcode" => $_POST['secretcode'],
        );
        $where = " where id='".$_POST['id']."'";
        $update = $connect->updaterecord($dbconn,"secretcode",$data,$where);
        echo $status = $update ? 1 : 0;
    break;
    
    case "AddTempEmployee":
        
        if($_POST['mno'] != ""){
            $filterTempEmpMno = mysqli_query($dbconn,"SELECT mno from tempEmpolyeeMaster where mno='".$_POST['mno']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpMno) > 0){
                echo 4;
            }
        }
        if($_POST['ecsno'] != ""){
            $filterTempEmpEcsno = mysqli_query($dbconn,"SELECT ecsno from tempEmpolyeeMaster where ecsno='".$_POST['ecsno']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpEcsno) > 0){
                echo 5;
            }
        }
        // if($_POST['accountno'] != ""){
        //     $filterTempEmpAccount = mysqli_query($dbconn,"SELECT accountno from employee where accountno='".$_POST['accountno']."' and isDelete=0 and istatus=1");
        //     if(mysqli_num_rows($filterTempEmpAccount) > 0){
        //             echo 6;
        //     }
        // }
        if($_POST['pfcode'] != ""){
            $filterTempEmpPFCode = mysqli_query($dbconn,"SELECT pfcode from tempEmpolyeeMaster where pfcode='".$_POST['pfcode']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpPFCode) > 0){
                echo 7;
                //$output['message'] = 'PF Code Number is already exists.';
            }
        }
        if($_POST['uan'] != ""){
            $filterTempEmpUan = mysqli_query($dbconn,"SELECT uan from tempEmpolyeeMaster where uan='".$_POST['uan']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpUan) > 0){
                echo 8;
                //$output['message'] = 'UAN Number is already exists.';
            }
        }
        if($_POST['pancard'] != ""){
            $filterTempEmpPancard = mysqli_query($dbconn,"SELECT pancard from tempEmpolyeeMaster where pancard='".$_POST['pancard']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpPancard) > 0){
                echo 9;
                //$output['message'] = 'Pancard Number is already exists.';
            }
        }
        
        if($_POST['aadharcard'] != ""){
            $filterTempEmpAadharCard = mysqli_query($dbconn,"SELECT adharcard from tempEmpolyeeMaster where adharcard='".$_POST['aadharcard']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
                echo 10;
                //$output['message'] = 'Aadhar Card Number is already exists.';
            }
        }
        
        $emp = mysqli_query($dbconn, "SELECT * FROM tempEmpolyeeMaster where  emp_name = '" . $_POST['emp_name'] . "' and isDelete='0' and istatus='1' ");
        if (mysqli_num_rows($emp) == 0) {

            $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $_POST['designation'] . "' "));

            $data = array(
                "emp_name" => strtoupper($_POST['emp_name']),
                //"emp_other_info" => $_POST['emp_other_info'],
                "mno" => $_POST['mno'],
                "address" => $_POST['address'],
                "bankid" => $_POST['bankid'],
                "ecsno" => $_POST['ecsno'],
                "accountno" => $_POST['accountno'],
                "ifsccode" => $_POST['ifsccode'],
                "designationid" => $_POST['designation'],
                "designation" => $des['designation'],
                "pfcode" => $_POST['pfcode'],
                "employeecode" => $_POST['employeecode'],
                "adharcard" => $_POST['aadharcard'],
                //"aadharImage" => strtolower($aadharImage),
                "pancard" => strtoupper($_POST['pancard']),
                //"pancardImage" => strtolower($pancardImage),
//                "drivinglicense" => $_POST['drivinglicense'],
                //"electioncard" => $_POST['voterid'],
                //"voteridImage" => strtolower($voteridImage),
                "passport" => $_POST['passport'],
                //"passportImage" => strtolower($passportImage),
                //  "other" => $_POST['other'],
                "dateofbirth" => $_POST['dateofbirth'],
                "uan" => $_POST['uan'],
                //"dateofjoining" => date('m/y',strtotime($_POST['dateofjoining'])),
                "dateofjoining" => $_POST['dateofjoining'],
                "strFatherName" => $_POST['strFatherName'],
                "strMaritalStatus" => $_POST['strMaritalStatus'],
                "strNomineeName" => $_POST['strNomineeName'],
                "strNomineeRelation" => $_POST['strNomineeRelation'],
                "strNomineeAdharNo" => $_POST['strNomineeAdharNo'],
                // "strFamilyDetails" => $_POST['strFamilyDetails'],
                // "strRelation" => $_POST['strRelation'],
                "strPermanentAddress" => $_POST['strPermanentAddress'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "created_at" => date('Y-m-d H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'tempEmpolyeeMaster', $data);
            
            /*********************************************/
            $valid_extensions = array('pdf', 'PDF','jpg','JPG','jpeg','JPEG','png','PNG'); // valid extensions
            if (!file_exists('TempDocument/'. $dealer_res)) {
                mkdir('TempDocument/'.$dealer_res, 0777, TRUE);
            }
            $path = 'TempDocument/' .$dealer_res. "/";
            $aadharImage = "";
            $aadharbackImage = "";
            $PanCardImage ="";
            $OtherDocumentImage = "";
            
            if(isset($_FILES['AadharCardFront']['name']) && $_FILES['AadharCardFront']['name'] != ""){
                $AadharCardFrontSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='1'");
                if(mysqli_num_rows($AadharCardFrontSql) == 1){
                    $AadharCardFrontData = mysqli_fetch_assoc($AadharCardFrontSql);
                    if ($AadharCardFrontData['strDocumentImage'] != NULL || $AadharCardFrontData['strDocumentImage'] != '') {
                        unlink($path . $AadharCardFrontData['strDocumentImage']);
                    }
                }
                $aadharimg = $_FILES['AadharCardFront']['name'];
                $tmp = $_FILES['AadharCardFront']['tmp_name'];
                $ext = pathinfo($_FILES['AadharCardFront']['name'], PATHINFO_EXTENSION);
                $aadhar_final_image = rand(0,1000)."_".time() . '.' . $ext;
                // $ext = strtolower(pathinfo($aadharimg, PATHINFO_EXTENSION));
                // $aadhar_final_image = preg_replace('/\s+/', '_', $aadharimg);
                if (in_array($ext, $valid_extensions)) {
                    $aadhar_path = $path . strtolower($aadhar_final_image);
                    if (move_uploaded_file($tmp, $aadhar_path)) {
                        $aadharImage = $aadhar_final_image;
                    }
                }
            } else {
                $aadharImage = $AadharCardFrontData['strDocumentImage'];
            }
            
            if(isset($_FILES['AadharCardFront']['name']) && $_FILES['AadharCardFront']['name'] != ""){
                mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='1'");
                $data = array(
                    "iTempEmpId" => $dealer_res,
                    "iDocumentId" => 1,
                    "strDocumentImage" => $aadharImage,
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                    
                );
                $dealer = $connect->insertrecord($dbconn, 'temempdocument', $data);
            }
            
            if(isset($_FILES['AadharCardBack']['name']) && $_FILES['AadharCardBack']['name'] != ""){
                $AadharCardBackSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='2'");
                if(mysqli_num_rows($AadharCardBackSql) == 1){
                    $AadharCardBackData = mysqli_fetch_assoc($AadharCardBackSql);
                    if ($AadharCardBackData['strDocumentImage'] != NULL || $AadharCardBackData['strDocumentImage'] != '') {
                        unlink($path . $AadharCardBackData['strDocumentImage']);
                    }
                } 
                $aadharbackimg = $_FILES['AadharCardBack']['name'];
                $tmp = $_FILES['AadharCardBack']['tmp_name'];
                // $ext = strtolower(pathinfo($aadharbackimg, PATHINFO_EXTENSION));
                // $aadhar_back_image = preg_replace('/\s+/', '_', $aadharbackimg);
                $ext = pathinfo($_FILES['AadharCardBack']['name'], PATHINFO_EXTENSION);
                $aadhar_back_image = rand(0,1000)."_".time() . '.' . $ext;
                if (in_array($ext, $valid_extensions)) {
                    $aadhar_back_path = $path . strtolower($aadhar_back_image);
                    if (move_uploaded_file($tmp, $aadhar_back_path)) {
                        $aadharbackImage = $aadhar_back_image;
                    }
                }
            } else {
                $aadharbackImage = $AadharCardBackData['strDocumentImage'];
            }
            
            if(isset($_FILES['AadharCardBack']['name']) && $_FILES['AadharCardBack']['name'] != ""){
                mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='2'");
                $data = array(
                    "iTempEmpId" => $dealer_res,
                    "iDocumentId" => 2,
                    "strDocumentImage" => $aadharbackImage,
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                );
                $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
            }
            
            
            if(isset($_FILES['PanCardImage']['name']) && $_FILES['PanCardImage']['name'] != ""){
                $PanCardSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='3'");
                if(mysqli_num_rows($PanCardSql) == 1){
                    $PanCardData = mysqli_fetch_assoc($PanCardSql);
                    if ($PanCardData['strDocumentImage'] != NULL || $PanCardData['strDocumentImage'] != '') {
                        unlink($path . $PanCardData['strDocumentImage']);
                    }
                } 
                $pandcardimg = $_FILES['PanCardImage']['name'];
                $tmp = $_FILES['PanCardImage']['tmp_name'];
                // $ext = strtolower(pathinfo($pandcardimg, PATHINFO_EXTENSION));
                // $pancard_final_image = preg_replace('/\s+/', '_', $pandcardimg);
                $ext = pathinfo($_FILES['PanCardImage']['name'], PATHINFO_EXTENSION);
                $pancard_final_image = rand(0,1000)."_".time() . '.' . $ext;
                if (in_array($ext, $valid_extensions)) {
                    $pancard_path = $path . strtolower($pancard_final_image);
                    if (move_uploaded_file($tmp, $pancard_path)) {
                        $PanCardImage = $pancard_final_image;
                    }
                }
            } else {
                $PanCardImage = $PanCardData['strDocumentImage'];
            }
            
            if(isset($_FILES['PanCardImage']['name']) && $_FILES['PanCardImage']['name'] != ""){
                mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='3'");
                $data = array(
                    "iTempEmpId" => $dealer_res,
                    "iDocumentId" => 3,
                    "strDocumentImage" => $PanCardImage,
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                );
                $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
            }
            
            
            if(isset($_FILES['OtherDocument']['name']) && $_FILES['OtherDocument']['name'] != ""){
                $OtherDocumentSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='4'");
                if(mysqli_num_rows($OtherDocumentSql) == 1){
                    $OtherDocumentData = mysqli_fetch_assoc($OtherDocumentSql);
                    if ($OtherDocumentData['strDocumentImage'] != NULL || $OtherDocumentData['strDocumentImage'] != '') {
                        unlink($path . $OtherDocumentData['strDocumentImage']);
                    }
                } 
                $otherimg = $_FILES['OtherDocument']['name'];
                $tmp = $_FILES['OtherDocument']['tmp_name'];
                // $ext = strtolower(pathinfo($otherimg, PATHINFO_EXTENSION));
                // $other_final_image = preg_replace('/\s+/', '_', $otherimg);
                $ext = pathinfo($_FILES['OtherDocument']['name'], PATHINFO_EXTENSION);
                $other_final_image = rand(0,1000)."_".time() . '.' . $ext;
                if (in_array($ext, $valid_extensions)) {
                    $Other_path = $path . strtolower($other_final_image);
                    if (move_uploaded_file($tmp, $Other_path)) {
                        $OtherDocumentImage = $other_final_image;
                    }
                }
            } else {
                $OtherDocumentImage = $OtherDocumentData['strDocumentImage'];
            }
            
            if(isset($_FILES['OtherDocument']['name']) && $_FILES['OtherDocument']['name'] != ""){
                mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$dealer_res."' and iDocumentId='4'");
                $data = array(
                    "iTempEmpId" => $dealer_res,
                    "iDocumentId" => 4,
                    "strDocumentImage" => $OtherDocumentImage,
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                );
                $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
            }
            /*******************************/
            
            $strFamilyDetails = sizeof($_POST['strFamilyDetails']);
            mysqli_query($dbconn,"delete from tempFamilyDetails where iTempEmpId='".$dealer_res."'");
            for($iCounter = 0;$iCounter < $strFamilyDetails; $iCounter++){
                $FamilyDetails = array(
                    "iTempEmpId" => $dealer_res,
                    "strFamilyDetails" => $_POST['strFamilyDetails'][$iCounter],
                    "iRelation" => $_POST['strRelation'][$iCounter],
                    "strEntryDate" => date('Y-m-d H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                );
                $dealerRes = $connect->insertrecord($dbconn, 'tempFamilyDetails', $FamilyDetails);
            }
            
            echo $statusMsg = $dealer_res ? '1' : '0';
        } else {
            echo $statusMsg = '3';
        }
        break;
    case "AddPermanentSalaryDetails":
        $iCounter = 0;
        $inc = $_POST['inc'];
        
        $count = $inc;
        $dealer_res = '';
        for ($iCounter = 1; $iCounter <= $count; $iCounter++) {
            $inc = $iCounter;
            if ($_POST['salary_' . $inc] != '' || $_POST['salary_' . $inc] != null) {
                $Sql123 = mysqli_query($dbconn, "delete from permanentemployeesalarydetails where salaryId = '" . $_POST['salaryId'] . "' and companyId = '" . $_POST['companyId'] . "' and emp_id = '" . $_POST['emp_id_' . $inc] . "'");
                $dataarray = array(
                    "salaryId" => $_POST['salaryId'],
                    "salarypaiddate" => $_POST['salarypaiddate'],
                    "emp_id" => $_POST['emp_id_' . $inc],
                    "name" => $_POST['Ename_' . $inc],
                    "companyId" => $_POST['companyId'],
                    "workingdays" => $_POST['workingdays_' . $inc],
                    "total" => round((float)$_POST['salary_'. $inc],2),
                    "netamountpaid" => ceil((float)$_POST['salary_'. $inc]),
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "iEntryBy" => $_SESSION['AdminId'],
                    "EntryDate" => date('d-m-Y H:i:s')
                );
                $dealer_res = $connect->insertrecord($dbconn, 'permanentemployeesalarydetails', $dataarray);
            }
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
        break;
    
    case "employeeBankDetails":
        
        $OTP = mt_rand(100000, 999999);
        //$_SESSION['TOP'] = $OTP;
        setSessionVariableWithExpiry('TOP', $OTP, 60);
        $detail = "<html><p>Hello Sir,</p><br /> <p>One Time Password for edit bank details authentication is: ". $OTP. "</p> </html>";

        $mailHost = "smtp.google.com";
        $mailUsername = "atprajapati@sgeco.in";
        $mailPassword = "ggavkeyxhhemxhsm";
        $mailSMTPSecure = 'ssl';
        $mailFrom = "no-replay@sgeco.in";
        $mailFromName = "OTP";
        $mailAddReplyTo = "no-replay@sgeco.in";
        $sub ="Employee Bank Details Edit - OTP";
        $giveorder = "hkshah@sgeco.in";
        //hkshah@sgeco.in,
        $mail = new PHPMailer();
        try {
            $mail->IsSMTP();
            $mail->SMTPAuth = TRUE;
            //$mail->SMTPDebug  = 1;
            $mail->Host = "smtp.gmail.com";
            // $mail->Username = "atprajapati@sgeco.in";
            // $mail->Password = "ggav keyx hhem xhsm";
            $mail->Username = "hkshah@sgeco.in";
            $mail->Password = "hptk kzwf fktf lyby";
            $mail->SMTPSecure = "ssl"; //$mailSMTPSecure;                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;
            $mail->Mailer   = "smtp";
            $mail->From = "hkshah@sgeco.in";
            $mail->FromName = "hkshah@sgeco.in";
            $mail->AddReplyTo($mailAddReplyTo);
            // $mail->AddCC('dev1.apolloinfotech@gmail.com');
            $mail->AddCC('sgbaroda@sgeco.in');
            

            $emailids = explode(',', $giveorder);

            foreach ($emailids as $key => $value) {
                $mail->AddAddress($value);
            }

            $mail->IsHTML(true);
            $mail->Subject = $sub;
            $mail->Body = $detail;
            $res_ofmail = $mail->Send();
        } 
          catch (Exception $e) {
            //echo $e->getMessage(); //Boring error messages from anything else!
        }
        $filterstr = "SELECT * FROM `employee`  where  isDelete='0' and  employeeId=" . $_REQUEST['Id'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        //header('Content-Type: application/json');
        print_r(json_encode($row));
        break;
        
    case "EditBankDetails" : 
        $value = getSessionVariableWithExpiry('TOP');
        if ($value !== null) {
            
            if($_POST['iOTP'] == $value){
                $data = array(
                    "bankid" => $_POST['bankid'],
                    "ifsccode" => $_POST['ifsccode'],
                    "accountno" => $_POST['accountno']
                );
                $where = " where employeeId='".$_POST['iEmpId']."'";
                $update = $connect->updaterecord($dbconn,"employee",$data,$where);
                echo 1;
                exit;
            } else {
                echo 2;
                exit;
            }
        } else {
            echo 3;
            exit;
        }
        echo 0;
        exit;
        break;
        
    case "resendOTP":
        
        $OTP = mt_rand(100000, 999999);
        //$_SESSION['TOP'] = $OTP;
        setSessionVariableWithExpiry('TOP', $OTP, 60);
        $detail = "<html><p>Hello Sir,</p><br /> <p>One Time Password for edit bank details authentication is: ". $OTP. "</p> </html>";

        // $mailHost = "smtp.gmail.com";
        // $mailUsername = "atprajapati@sgeco.in";
        // $mailPassword = "ggav keyx hhem xhsm";
        // //$mailPassword = "ggavkeyxhhemxhsm";
        // $mailSMTPSecure = 'ssl';
        // $mailFrom = "no-replay@sgeco.in";
        // $mailFromName = "OTP";
        // $mailAddReplyTo = "no-replay@sgeco.in";
        $sub ="Employee Bank Details Edit - OTP";
        // $giveorder = "hkshah@sgeco.in";
        $giveorder = "hkshah@sgeco.in";
    
        $mail = new PHPMailer();
        try {
            $mail->IsSMTP();
            $mail->SMTPAuth = TRUE;
            $mail->SMTPDebug  = 2;
            $mail->Host = "smtp.gmail.com";
            // $mail->Username = "atprajapati@sgeco.in";
            // $mail->Password = "ggav keyx hhem xhsm";
            $mail->Username = "hkshah@sgeco.in";
            $mail->Password = "hptk kzwf fktf lyby";
            
            $mail->SMTPSecure = "ssl"; //$mailSMTPSecure;                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;
            //$mail->Port = 587;
            $mail->Mailer   = "smtp";
            $mail->From = "hkshah@sgeco.in";
            $mail->FromName = "hkshah@sgeco.in";
            $mail->AddReplyTo($mailAddReplyTo);
            $mail->AddCC('sgbaroda@sgeco.in');
            // $mail->AddCC('edit.margi@gmail.com');

            $emailids = explode(',', $giveorder);

            foreach ($emailids as $key => $value) {
                $mail->AddAddress($value);
            }


            $mail->IsHTML(true);
            $mail->Subject = $sub;
            $mail->Body = $detail;
            
            $res_ofmail = $mail->Send();
            // print_r($mail);
            // echo "<pre>";
            // print_r($res_ofmail);
            // exit;
        } 
          catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
        break;

    case "AddAdvanced":
        if (!hasAdvancedAccess($dbconn)) { http_response_code(403); echo '0'; break; }
        $companyId = isset($_POST['iCompanyId']) ? (int) $_POST['iCompanyId'] : 0;
        $monthYear = trim($_POST['strMonthYear']);
        $fromDate = isset($_POST['fromdate']) ? trim($_POST['fromdate']) : '';
        $toDate = isset($_POST['todate']) ? trim($_POST['todate']) : '';
        $escapedMonthYear = mysqli_real_escape_string($dbconn, $monthYear);
        $company = mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE companymasterId=" . $companyId . " AND isDelete=0 AND istatus=1");
        if ($companyId < 1 || !$company || mysqli_num_rows($company) === 0) {
            echo '5';
            break;
        }
        $existing = mysqli_query($dbconn, "SELECT iAdvancedMasterId FROM advanced_master WHERE iCompanyId=" . $companyId . " AND strMonthYear='" . $escapedMonthYear . "' AND isDelete=0");
        if (!validAdvancedDates($monthYear, $fromDate, $toDate) || mysqli_num_rows($existing) > 0) {
            echo mysqli_num_rows($existing) > 0 ? '3' : '4';
            break;
        }
        $data = array(
            "iCompanyId" => $companyId,
            "strMonthYear" => $monthYear,
            "fromdate" => $fromDate,
            "todate" => $toDate,
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iEntryBy" => $_SESSION['AdminId'],
            "EntryDate" => date('Y-m-d')
        );
        $result = $connect->insertrecord($dbconn, 'advanced_master', $data);
        echo $result ? '1' : '0';
        break;

    case "GetAdvanced":
        if (!hasAdvancedAccess($dbconn)) { http_response_code(403); echo '0'; break; }
        $id = (int) $_REQUEST['ID'];
        $result = mysqli_query($dbconn, "SELECT * FROM advanced_master WHERE iAdvancedMasterId=" . $id . " AND isDelete=0 AND istatus=1");
        echo json_encode(mysqli_fetch_assoc($result));
        break;

    case "EditAdvanced":
        if (!hasAdvancedAccess($dbconn)) { http_response_code(403); echo '0'; break; }
        $id = (int) $_POST['iAdvancedMasterId'];
        $companyId = isset($_POST['iCompanyId']) ? (int) $_POST['iCompanyId'] : 0;
        $monthYear = trim($_POST['strMonthYear']);
        $fromDate = isset($_POST['fromdate']) ? trim($_POST['fromdate']) : '';
        $toDate = isset($_POST['todate']) ? trim($_POST['todate']) : '';
        $escapedMonthYear = mysqli_real_escape_string($dbconn, $monthYear);
        $company = mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE companymasterId=" . $companyId . " AND isDelete=0 AND istatus=1");
        if ($companyId < 1 || !$company || mysqli_num_rows($company) === 0) {
            echo '5';
            break;
        }
        $existing = mysqli_query($dbconn, "SELECT iAdvancedMasterId FROM advanced_master WHERE iCompanyId=" . $companyId . " AND strMonthYear='" . $escapedMonthYear . "' AND iAdvancedMasterId!=" . $id . " AND isDelete=0");
        if ($id < 1 || !validAdvancedDates($monthYear, $fromDate, $toDate) || mysqli_num_rows($existing) > 0) {
            echo mysqli_num_rows($existing) > 0 ? '3' : '4';
            break;
        }
        $data = array(
            "iCompanyId" => $companyId,
            "strMonthYear" => $monthYear,
            "fromdate" => $fromDate,
            "todate" => $toDate,
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iUpdatedBy" => $_SESSION['AdminId'],
            "UpdatedDate" => date('Y-m-d')
        );
        $result = $connect->updaterecord($dbconn, 'advanced_master', $data, ' where iAdvancedMasterId=' . $id);
        echo $result ? '2' : '0';
        break;
    
    default:
# code...
        echo "Page not Found";
        break;
}


function setSessionVariableWithExpiry($key, $value, $expiry_in_seconds) {
    $_SESSION[$key] = [
        'value' => $value,
        'expiry' => time() + $expiry_in_seconds
    ];
}


function getSessionVariableWithExpiry($key) {
    if (isset($_SESSION[$key])) {
        $session_data = $_SESSION[$key];
        if (time() < $session_data['expiry']) {
            return $session_data['value'];
        } else {
            // Variable has expired
            unset($_SESSION[$key]);
        }
    }
    return null; // Variable does not exist or has expired
}


function hasAdvancedAccess($dbconn) {
    if ($_SESSION['AdminType'] == 1) {
        return true;
    }
    $result = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
    $rights = mysqli_fetch_assoc($result);
    return isset($rights['isAdvancedEntry']) && $rights['isAdvancedEntry'] == 1;
}

function validAdvancedDates($monthYear, $fromDate, $toDate) {
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{4})$/', $monthYear, $matches)) {
        return false;
    }
    $expectedMonth = $matches[2] . '-' . $matches[1];
    $from = DateTime::createFromFormat('!Y-m-d', $fromDate);
    $to = DateTime::createFromFormat('!Y-m-d', $toDate);
    return $from && $to
        && $from->format('Y-m-d') === $fromDate
        && $to->format('Y-m-d') === $toDate
        && $from->format('Y-m') === $expectedMonth
        && $to->format('Y-m') === $expectedMonth
        && $from <= $to;
}

?>