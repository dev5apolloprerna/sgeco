<?php
ob_start();
error_reporting(E_ALL);
header('Content-Type: application/json');
include_once '../common.php';

$connect = new connect();

include_once '../admin/password_hash.php';

$actions = isset($_REQUEST['action']) ? strtolower(trim($_REQUEST['action'])) : '';
extract($_REQUEST);

$output = array();

if ($actions == 'userlogin') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if (validate_password($obj->strPassword, $good_hash)) {
            if($row['strDeviceToken'] == ""){
                $data = array(
                    "strDeviceToken" => $obj->strDeviceToken,
                );
                $where = " where iAndroidUserId = " . $row['iAndroidUserId'];
                $dealer = $connect->updaterecord($dbconn, 'androidusermaster', $data, $where);
                
                $output['iAndroidUserId'] = $row['iAndroidUserId'];
                $output['iMobile'] = $row['iMobile'];
                $output['strUserName'] = $row['strUserName'];
                $output['strDeviceToken'] = $obj->strDeviceToken;
                $output['message'] = 'Login Successfully Done';
                $output['success'] = '1';
            } else if($row['strDeviceToken'] == $obj->strDeviceToken){
                $output['iAndroidUserId'] = $row['iAndroidUserId'];
                $output['iMobile'] = $row['iMobile'];
                $output['strUserName'] = $row['strUserName'];
                $output['strDeviceToken'] = $obj->strDeviceToken;
                $output['message'] = 'Login Successfully Done';
                $output['success'] = '1';
            } else {
                $output['iAndroidUserId'] = "";
                $output['iMobile'] = 0;
                $output['strUserName'] = "";
                $output['strDeviceToken'] = "";
                $output['message'] = 'Invalid Device Token';
                $output['success'] = '0';
            }
        } else {
            $output['iAndroidUserId'] = "";
            $output['iMobile'] = 0;
            $output['strUserName'] = "";
            $output['strDeviceToken'] = "";
            $output['message'] = 'Password not match';
            $output["success"] = '0';
        }

    } else {
        $output['iAndroidUserId'] = "";
        $output['iMobile'] = 0;
        $output['strUserName'] = "";
        $output['strDeviceToken'] = "";
        $output['message'] = 'User not found';
        $output['success'] = '0';
    }
} else if ($actions == 'changepassword') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);
    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                if($obj->strNewPassword == $obj->strConfirmPassword){
                    $hash_result = create_hash($obj->strNewPassword);
                    $hash_params = explode(":", $hash_result);
                    $salt = $hash_params[HASH_SALT_INDEX];
                    $hash = $hash_params[HASH_PBKDF2_INDEX];
                    
                    $getItems1 = mysqli_query($dbconn,"update androidusermaster SET strPassword = '" . $hash . "', strSalt = '" . $salt . "' where iAndroidUserId='" . $row['iAndroidUserId'] . "'");
                    
                    $output['message'] = 'Password Change Successfully.';
                    $output['success'] = '1';
                } else {
                    $output['message'] = 'New Password and Confirm Password not match.';
                    $output['success'] = '0';
                }
            } else {
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'searchemployeebyaadhar') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                //$empSql = "SELECT * FROM `employee` where adharcard like '%$obj->strAdharcardNo%' and isDelete=0 and istatus=1";
                $empSql = "SELECT * FROM `employee` where adharcard like '%$obj->strAdharcardNo%' and isDelete=0 and istatus=1
                UNION All 
                SELECT * from tempEmpolyeeMaster where adharcard like '%$obj->strAdharcardNo%' and isDelete=0 and istatus=1";
                $resultEmp = mysqli_query($dbconn, $empSql);
                if (mysqli_num_rows($resultEmp) >= 1) {
                    while($rowEmp = mysqli_fetch_assoc($resultEmp)){
                        $output['data'][] = $rowEmp;
                    }
                    $output['message'] = 'Employee List.';
                    $output['success'] = '1';
                } else {
                    $output['data'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['data'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['data'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['data'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'addemployee') {
    $request_body = @file_get_contents('php://input');
    file_put_contents('addemployee.txt', file_get_contents('php://input'));
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $isValid = true;
                if($obj->mno != ""){
                    $filterTempEmpMno = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where mno='".$obj->mno."'
                    UNION All 
                    SELECT * from employee where mno='".$obj->mno."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpMno) > 0){
                        $output['iTempEmpId'] = 0;
                        $output['message'] = 'Mobile number is already exists.';
                        $output["success"] = '0';  
                        //print($output);
                        print(json_encode($output));
                        $isValid = false;
                        exit;
                    }
                }
                if($obj->ecsno != ""){
                    $filterTempEmpEcsno = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where ecsno='".$obj->ecsno."' 
                    UNION All 
                    SELECT * from employee where ecsno='".$obj->ecsno."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpEcsno) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'ESIC Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->accountno != ""){
                    $filterTempEmpAccount = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where accountno='".$obj->accountno."'
                    UNION All 
                    SELECT * from employee where accountno='".$obj->accountno."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpAccount) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Account Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->pfcode != ""){
                    $filterTempEmpPFCode = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where pfcode='".$obj->pfcode."'
                    UNION All 
                    SELECT * from employee where pfcode='".$obj->pfcode."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpPFCode) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'PF Code Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->uan != ""){
                    $filterTempEmpUan = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where uan='".$obj->uan."'
                    UNION All 
                    SELECT * from employee where uan='".$obj->uan."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpUan) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'UAN Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                
                if($obj->pancard != ""){
                    $filterTempEmpPancard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where pancard='".$obj->pancard."'
                    UNION All 
                    SELECT * from employee where pancard='".$obj->pancard."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpPancard) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Pancard Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                
                
                if($obj->drivinglicense != ""){
                    $filterTempEmpDrivingLicense = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where drivinglicense='".$obj->drivinglicense."'
                    UNION All 
                    SELECT * from employee where drivinglicense='".$obj->drivinglicense."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpDrivingLicense) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Driving License Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->aadharcard != ""){
                    $filterTempEmpAadharCard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where adharcard='".$obj->aadharcard."'
                    UNION All 
                    SELECT * from employee where adharcard='".$obj->aadharcard."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Aadhar Card Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->passport != ""){
                    $filterTempEmpPassport = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where passport='".$obj->passport."'
                    UNION All 
                    SELECT * from employee where passport='".$obj->passport."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpPassport) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Passport Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->voterid != ""){
                    $filterTempEmpElectionCard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where electioncard='".$obj->voterid."'
                    UNION All 
                    SELECT * from employee where electioncard='".$obj->voterid."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpElectionCard) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Election Card Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($isValid == true){
                    $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $obj->designation . "' "));
                    $data = array(
                        "emp_name" => ucwords(strtolower($obj->emp_name)),
                        "emp_other_info" => ucwords(strtolower($obj->emp_other_info)),
                        "mno" => $obj->mno,
                        "address" => ucwords(strtolower($obj->address)),
                        "bankid" => $obj->bankid,
                        "ecsno" => $obj->ecsno,
                        "accountno" => $obj->accountno,
                        "ifsccode" => $obj->ifsccode,
                        "designationid" => $obj->designation,
                        "designation" => ucwords(strtolower($des['designation'])),
                        "pfcode" => $obj->pfcode,
                        "employeecode" => $obj->employeecode,
                        "adharcard" => $obj->aadharcard,
                        "pancard" => $obj->pancard,
                        //"electioncard" => $obj->voterid,
                        "passport" => $obj->passport,
                        "dateofbirth" => (isset($obj->dateofbirth) && $obj->dateofbirth != "") ? date('d-m-Y',strtotime($obj->dateofbirth)) : "",
                        "uan" => $obj->uan,
                        "dateofjoining" => (isset($obj->dateofjoining) && $obj->dateofjoining != "") ? date('d-m-Y',strtotime($obj->dateofjoining)) : "",
                        //"drivinglicense" => $obj->drivinglicense,
                        "strFatherName" => ucwords(strtolower($obj->strFatherName)),
                        "strMaritalStatus" => ucwords(strtolower($obj->strMaritalStatus)),
                        "strNomineeName" => ucwords(strtolower($obj->strNomineeName)),
                        "strNomineeRelation" => ucwords(strtolower($obj->strNomineeRelation)),
                        "strNomineeAdharNo" => $obj->strNomineeAdharNo,
                        // "strFamilyDetails" => $obj->strFamilyDetails,
                        // "strRelation" => $obj->strRelation,
                        "strPermanentAddress" => ucwords(strtolower($obj->strPermanentAddress)),
                        "strEntryDate" => date('d-m-Y H:i:s'),
                        "strIP" => $_SERVER['REMOTE_ADDR'],
                        "created_at" => date('Y-m-d H:i:s'),
                        "iCreatedBy" => $row['iAndroidUserId']
                    );
                    $dealer_res = $connect->insertrecord($dbconn, 'tempEmpolyeeMaster', $data);
                    
                    $FamilyDetails = array(
                        "iTempEmpId" => $dealer_res,
                        "strFamilyDetails" => ucwords(strtolower($obj->strFamilyDetails)),
                        "iRelation" => $obj->strRelation ?? 0,
                        "strEntryDate" => date('Y-m-d H:i:s'),
                        "strIP" => $_SERVER['REMOTE_ADDR']
                    );
                    $dealerRes = $connect->insertrecord($dbconn, 'tempFamilyDetails', $FamilyDetails);
            
                    if($dealer_res){
                        $output['iTempEmpId'] = $dealer_res;
                        $output['message'] = 'Employee added successfully.';
                        $output["success"] = '1';
                    } else {
                        $output['iTempEmpId'] = 0;
                        $output['message'] = 'Password not match.';
                        $output["success"] = '0';
                    }
                } else {
                    $output['iTempEmpId'] = 0;
                    $output['message'] = 'Employee Details already exists.';
                    $output["success"] = '0';
                }
            } else {
                $output['iTempEmpId'] = 0;
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['iTempEmpId'] = 0;
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['iTempEmpId'] = 0;
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'designationlist') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $queryCom = "SELECT * FROM `designation`  where isDelete='0'  and  istatus='1' order by  designationid asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                $output['DesignationList'][] = array(
                        "designationid" => 0,
                        "designation" => "Select Designation",
                        "strEntryDate" => "",
                        "strIP" => "",
                        "isDelete" => "0",
                        "iStatus" => 1
                    );
                if (mysqli_num_rows($resultCom) >= 1) {
                    
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $output['DesignationList'][] = $rowCom;
                    }
                    $output['message'] = 'Designation List.';
                    $output['success'] = '1';
                } else {
                    $output['DesignationList'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['DesignationList'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['DesignationList'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['DesignationList'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'banklist') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $queryCom = "SELECT * FROM `bankmaster`  where bankmasterId > 0 and  isDelete='0'  and  istatus='1' order by  bankmasterId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                $output['BankList'][] = array(
                        "bankmasterId" => 0,
                        "bankname" => "Select Bank",
                        'City'=> "",
                        "MedicalAllowance" => "",
                        "MedicalAllowancePer" => "",
                        "strEntryDate" => "",
                        "strIP" => "",
                        "isDelete" => "0",
                        "iStatus" => 1
                    );
                if (mysqli_num_rows($resultCom) >= 1) {
                    
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $output['BankList'][] = $rowCom;
                    }
                    $output['message'] = 'Bank List.';
                    $output['success'] = '1';
                } else {
                    $output['BankList'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['BankList'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['BankList'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['BankList'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'documentlist') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $queryCom = "SELECT * FROM `document`  where iDocumentId not in (select iDocumentId from temempdocument where temempdocument.iDocumentId and document.iDocumentId and iTempEmpId='".$obj->iTempEmpId."') and  isDelete='0'  and  istatus='1' order by  iDocumentId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                $output['Documentist'][] = array(
                        "iDocumentId" => 0,
                        "strDocumentName" => "Select Document",
                        "strEntryDate" => "",
                        "strIP" => "",
                        "isDelete" => "0",
                        "iStatus" => 1
                    );
                if (mysqli_num_rows($resultCom) >= 1) {
                    
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $output['Documentist'][] = $rowCom;
                    }
                    $output['message'] = 'Document List.';
                    $output['success'] = '1';
                } else {
                    $output['Documentist'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['Documentist'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['Documentist'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['Documentist'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'adddocumentphotos') {
    $request_body = @file_get_contents('php://input');
    file_put_contents('adddocumentphotos.txt', $_FILES['image']);
    $obj = json_decode($request_body);
    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '".$_REQUEST['iMobile']."' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $_REQUEST['strDeviceToken']){
            if (validate_password($_REQUEST['strPassword'], $good_hash)) {
                $queryCom = "SELECT * FROM `temempdocument`  where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='".$_REQUEST['iDocumentId']."' and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                
                if (mysqli_num_rows($resultCom) == 0) {
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = time() . '.' . $ext;
                    
                    $data = array(
                        "iTempEmpId" => $_REQUEST['iTempEmpId'],
                        "iDocumentId" => $_REQUEST['iDocumentId'],
                        "strDocumentImage" => $file_name,
                        "strEntryDate" => date('d-m-Y H:i:s'),
                        "strIP" => $_SERVER['REMOTE_ADDR']
                    );
                    $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
                    if (!file_exists('../admin/TempDocument/'. $_REQUEST['iTempEmpId'])) {
                        mkdir('../admin/TempDocument/'.$_REQUEST['iTempEmpId'], 0777, TRUE);
                    }
                    $target_path = '../admin/TempDocument/'. $_REQUEST['iTempEmpId']. '/' . $file_name;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $output['success'] = "0";
                        $output['message'] = 'Could not move the image!';
                    } else {
                        $output['message'] = 'sucess';
                        $output['success'] = '1';
                    }
                } else {
                    $output['message'] = 'Document already Uploaded..';
                    $output["success"] = '0';
                }
            } else {
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'documentphotoslist') {
    $request_body = @file_get_contents('php://input');
    //file_put_contents('adddocumentphotos.txt', file_get_contents('php://input'));
    $obj = json_decode($request_body);
    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '".$obj->iMobile."' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                
                $queryCom = "SELECT * FROM `temempdocument`  where iTempEmpId='".$obj->iTempEmpId."' and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                if (mysqli_num_rows($resultCom) >= 1) {
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $target_path = $web_url.'admin/TempDocument/'.$obj->iTempEmpId."/";
                        $queryDoc = "SELECT * FROM `document`  where iDocumentId='".$rowCom['iDocumentId']."' and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                        $resultDoc = mysqli_query($dbconn, $queryDoc) or die(mysqli_error($dbconn));
                        $rowDoc = mysqli_fetch_assoc($resultDoc);
                        $array[] = array(
                            "iTempDocId" => $rowCom['iTempDocId'],
                            "iTempEmpId" => $rowCom['iTempEmpId'],
                            "iDocumentId" => $rowCom['iDocumentId'],
                            "strDocumentName" => $rowDoc['strDocumentName'],
                            "strDocumentImage" => $target_path.$rowCom['strDocumentImage'],
                            "strEntryDate" => $rowCom['strEntryDate']
                        );
                        
                    }
                    $output['Documentist'] = $array;
                    $output['message'] = 'Document List.';
                    $output['success'] = '1';
                } else {
                    $output['Documentist'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }   
                
                
            } else {
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'deletedocumentphotos') {
    $request_body = @file_get_contents('php://input');
    //file_put_contents('adddocumentphotos.txt', file_get_contents('php://input'));
    $obj = json_decode($request_body);
    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '".$obj->iMobile."' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $queryCom = "SELECT * FROM `temempdocument`  where iTempEmpId='".$obj->iTempEmpId."' and iTempDocId='".$obj->iTempDocId."' and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                if (mysqli_num_rows($resultCom) >= 1) {
                    $rowCom = mysqli_fetch_assoc($resultCom);
                    $target_path = '../admin/TempDocument/'.$obj->iTempEmpId."/".$rowCom['strDocumentImage'];
                    
                    if(file_exists($target_path)){
                        unlink($target_path);    
                    }
                    mysqli_query($dbconn,"delete FROM `temempdocument`  where iTempEmpId='".$obj->iTempEmpId."' and iTempDocId='".$obj->iTempDocId."' ");
                    $output['message'] = 'success.';
                    $output['success'] = '1';
                } else {
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }   
            } else {
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'tempemployeereport') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $where = " where  1=1";
                if($obj->strFromDate != ""){
                    $where .=" and STR_TO_DATE(strEntryDate,'%d-%m-%Y') >= STR_TO_DATE('". $obj->strFromDate ."','%d-%m-%Y')";
                } 
                if($obj->strToDate != ""){
                    $where .=" and STR_TO_DATE(strEntryDate,'%d-%m-%Y') <= STR_TO_DATE('". $obj->strToDate ."','%d-%m-%Y')";
                } 
                if($obj->strEmployeeName != ""){
                    $where .=" and emp_name like '%". trim($obj->strEmployeeName) ."%'";
                } 
                $queryCom = "SELECT iTempEmpId,emp_name,adharcard,mno,strEntryDate FROM `tempEmpolyeeMaster`  ".$where." and  isDelete='0'  and  istatus='1' order by emp_name asc";
                
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                if (mysqli_num_rows($resultCom) >= 1) {
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $output['TempEmployeeList'][] = $rowCom;
                    }
                    $output['message'] = 'Temp Employee List.';
                    $output['success'] = '1';
                } else {
                    $output['Documentist'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['TempEmployeeList'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['TempEmployeeList'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['TempEmployeeList'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
}  else if ($actions == 'edit_tempemployee') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $where = " where  1=1";
                
                $queryCom = "SELECT * FROM `tempEmpolyeeMaster`  ".$where." and iTempEmpId=".$obj->iTempEmpId." and  isDelete='0'  and  istatus='1'";
                
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                if (mysqli_num_rows($resultCom) >= 1) {
                    $rowCom = mysqli_fetch_assoc($resultCom);
                   // $output = $rowCom;
                   
                    $queryBankCom = "SELECT * FROM `bankmaster`  where bankmasterId='".$rowCom['bankid']."' and  isDelete='0'  and  istatus='1' order by  bankmasterId asc";
                    $resultBankCom = mysqli_query($dbconn, $queryBankCom) or die(mysqli_error($dbconn));

                    $rowBankCom = mysqli_fetch_assoc($resultBankCom);
                    $output['bankname'] = isset($rowBankCom['bankname']) && $rowBankCom['bankname'] != "" ? ucwords(strtolower($rowBankCom['bankname'])) : "Select Bank";
                   
                   $output["designation"] = isset($rowCom['designation']) && $rowCom['designation'] != "" ? ucwords(strtolower($rowCom['designation'])) : 'Select Designation';
                   $output["iTempEmpId"] = $rowCom['iTempEmpId'];
                   $output["emp_name"] = isset($rowCom['emp_name']) && $rowCom['emp_name'] != "" ? ucwords(strtolower($rowCom['emp_name'])) : ''; 
                   $output["emp_other_info"] = isset($rowCom['emp_other_info']) && $rowCom['emp_other_info'] != "" ? $rowCom['emp_other_info'] : "";
                   $output["mno"] = isset($rowCom['mno']) && $rowCom['mno'] != "" ? $rowCom['mno'] : '';
                   $output["address"] = isset($rowCom['address']) && $rowCom['address'] != "" ? ucwords(strtolower($rowCom['address'])) : "";
                   $output["bankid"] = $rowCom['bankid'];
                   $output["otherbankname"] = isset($rowCom['otherbankname']) && $rowCom['otherbankname'] != "" ? ucwords(strtolower($rowCom['otherbankname'])) : "";
                   $output["ecsno"] = isset($rowCom['ecsno']) && ($rowCom['ecsno'] != "" || $rowCom['ecsno'] != "0") ? $rowCom['ecsno'] : '';
                   $output["accountno"] = isset($rowCom['accountno']) && $rowCom['accountno'] != "" ? $rowCom['accountno'] : "";
                   $output["ifsccode"] = isset($rowCom['ifsccode']) && $rowCom['ifsccode'] != "" ? $rowCom['ifsccode'] : '';
                   $output["salaryamt"] = isset($rowCom['salaryamt']) && $rowCom['salaryamt'] != "" ? $rowCom['salaryamt'] : '';
                   $output["strEntryDate"] = $rowCom['strEntryDate'];
                   $output["strIP"] = $rowCom['strIP'];
                   $output["istatus"] = $rowCom['istatus'];
                   $output["isDelete"] = $rowCom['isDelete'];
                   $output["designationid"] = $rowCom['designationid'];
                   $output["Skill"] = isset($rowCom['Skill']) && $rowCom['Skill'] != "" ? $rowCom['Skill'] : '' ;
                   $output["pfcode"] = $rowCom['pfcode'];
                   $output["employeecode"] = $rowCom['employeecode'];
                   $output["pancard"] = isset($rowCom['pancard']) && $rowCom['pancard'] != "" ? $rowCom['pancard'] : "";
                   //$output["drivinglicense"] = isset($rowCom['drivinglicense']) && $rowCom['drivinglicense'] != "" ? $rowCom['drivinglicense'] : "";
                   //$output["electioncard"] = isset($rowCom['electioncard']) && $rowCom['electioncard'] != "" ? $rowCom['electioncard'] : "";
                   $output["adharcard"] = isset($rowCom['adharcard']) && $rowCom['adharcard'] != "" ? $rowCom['adharcard'] : "";
                   $output["passport"] = isset($rowCom['passport']) && $rowCom['passport'] != "" ? $rowCom['passport'] : "";
                   $output["other"] = isset($rowCom['other']) && $rowCom['other'] != "" ? $rowCom['other'] : "";
                   $output["dateofbirth"] = isset($rowCom['dateofbirth']) && $rowCom['dateofbirth'] != "" ? $rowCom['dateofbirth'] : "";
                   $output["uan"] = isset($rowCom['uan']) && $rowCom['uan'] != "" ? $rowCom['uan'] : "";
                   $output["dateofjoining"] = isset($rowCom['dateofjoining']) && $rowCom['dateofjoining'] != "" ? $rowCom['dateofjoining'] : "";
                   $output["pancardImage"] = $rowCom['pancardImage'];
                   $output["voteridImage"] = $rowCom['voteridImage'];
                   $output["aadharImage"] = $rowCom['aadharImage'];
                   $output["passportImage"] = $rowCom['passportImage'];
                   
                    $output["strFatherName"] = isset($rowCom['strFatherName']) && $rowCom['strFatherName'] != "" ? ucwords(strtolower($rowCom['strFatherName'])) : "";
                    $output["strMaritalStatus"] = isset($rowCom['strMaritalStatus']) && $rowCom['strMaritalStatus'] != "" ? ucwords(strtolower($rowCom['strMaritalStatus'])) : "";
                    $output["strNomineeName"] = isset($rowCom['strNomineeName']) && $rowCom['strNomineeName'] != "" ? ucwords(strtolower($rowCom['strNomineeName'])) : "";
                    $output["strNomineeRelation"] = isset($rowCom['strNomineeRelation']) && $rowCom['strNomineeRelation'] != "" ? ucwords(strtolower($rowCom['strNomineeRelation'])) : "";
                    $output["strNomineeAdharNo"] = isset($rowCom['strNomineeAdharNo']) && $rowCom['strNomineeAdharNo'] != "" ? $rowCom['strNomineeAdharNo'] : "";
                    
                    $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `tempFamilyDetails` where iTempEmpId='".$rowCom['iTempEmpId']."' order by iTempFamilyDetailsId asc limit 1");
                    $rowRelation = mysqli_fetch_assoc($filterFamilyRelation);
                    
                    if($rowRelation['iRelation'] != ""){
                        $filterRelation = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT * FROM `relation` where iRelation = ".$rowRelation['iRelation']." and isDelete=0 limit 1"));
                        
                        $output["strFamilyDetails"] = isset($rowRelation['strFamilyDetails']) && $rowRelation['strFamilyDetails'] != "" ? ucwords(strtolower($rowRelation['strFamilyDetails'])) : "";
                        $output["strRelation"] = isset($filterRelation['strRelation']) && $filterRelation['strRelation'] != "" ? ucwords(strtolower($filterRelation['strRelation'])) : "";
                        $output["iRelation"] = isset($filterRelation['iRelation']) && $filterRelation['iRelation'] != "" ? $filterRelation['iRelation'] : "";
                    } else {
                        $output["strFamilyDetails"] = "";
                        $output["strRelation"] =  "";
                        $output["iRelation"] = "";
                    }
                    $output["strPermanentAddress"] = isset($rowCom['strPermanentAddress']) && $rowCom['strPermanentAddress'] != "" ? ucwords(strtolower($rowCom['strPermanentAddress'])) :"";
                    
                    $output['message'] = 'Temp Employee List.';
                    $output['success'] = '1';
                } else {
                    $output['Documentist'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['TempEmployeeList'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['TempEmployeeList'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['TempEmployeeList'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'update_tempemployee') {
    $request_body = @file_get_contents('php://input');
    file_put_contents('update_tempemployee.txt', file_get_contents('php://input'));
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                if($obj->mno != ""){
                    $filterTempEmpMno = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where mno='".$obj->mno."' and iTempEmpId!=$obj->iTempEmpId
                        UNION All 
                        SELECT * from employee where mno='".$obj->mno."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpMno) > 0){
                        $output['iTempEmpId'] = 0;
                        $output['message'] = 'Mobile number is already exists.';
                        $output["success"] = '0';  
                        //print($output);
                        print(json_encode($output));
                        $isValid = false;
                        exit;
                    }
                }
                if($obj->ecsno != ""){
                    $filterTempEmpEcsno = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where ecsno='".$obj->ecsno."'  and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where ecsno='".$obj->ecsno."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpEcsno) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'ESIC Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->accountno != ""){
                    $filterTempEmpAccount = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where accountno='".$obj->accountno."' and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where accountno='".$obj->accountno."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpAccount) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Account Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->pfcode != ""){
                    $filterTempEmpPFCode = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where pfcode='".$obj->pfcode."' and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where pfcode='".$obj->pfcode."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpPFCode) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'PF Code Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->uan != ""){
                    $filterTempEmpUan = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where uan='".$obj->uan."' and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where uan='".$obj->uan."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpUan) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'UAN Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                
                if($obj->pancard != ""){
                    $filterTempEmpPancard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where pancard='".$obj->pancard."' and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where pancard='".$obj->pancard."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpPancard) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Pancard Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                
                
                if($obj->drivinglicense != ""){
                    $filterTempEmpDrivingLicense = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where drivinglicense='".$obj->drivinglicense."' and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where drivinglicense='".$obj->drivinglicense."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpDrivingLicense) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Driving License Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                if($obj->aadharcard != ""){
                    $filterTempEmpAadharCard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where adharcard='".$obj->aadharcard."' and iTempEmpId!=$obj->iTempEmpId
                    UNION All 
                    SELECT * from employee where adharcard='".$obj->aadharcard."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Aadhar Card Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                // if($obj->passport != ""){
                //     $filterTempEmpPassport = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where passport='".$obj->passport."' and iTempEmpId!=$obj->iTempEmpId
                //     UNION All 
                //     SELECT * from employee where passport='".$obj->passport."' and isDelete=0 and istatus=1");
                //     if(mysqli_num_rows($filterTempEmpPassport) > 0){
                //             $output['iTempEmpId'] = 0;
                //             $output['message'] = 'Passport Number is already exists.';
                //             $output["success"] = '0';  
                //             $isValid = false;
                //             print(json_encode($output));
                //             exit;
                //     }
                // }
                if($obj->voterid != ""){
                    $filterTempEmpElectionCard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where electioncard='".$obj->voterid."' and iTempEmpId!=$obj->iTempEmpId
                        UNION All 
                        SELECT * from employee where electioncard='".$obj->voterid."' and isDelete=0 and istatus=1");
                    if(mysqli_num_rows($filterTempEmpElectionCard) > 0){
                            $output['iTempEmpId'] = 0;
                            $output['message'] = 'Election Card Number is already exists.';
                            $output["success"] = '0';  
                            $isValid = false;
                            print(json_encode($output));
                            exit;
                    }
                }
                
                $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $obj->designationid . "' "));
                $data = array(
                    "emp_name" => ucwords(strtolower($obj->emp_name)),
                    "designationid" => $obj->designationid,
                    "employeecode" => $obj->employeecode,
                    "mno" => $obj->mno,
                    "address" => ucwords(strtolower($obj->address)),
                    "ecsno" => $obj->ecsno,
                    "accountno" => ucwords(strtolower($obj->accountno)),
                    "bankid" => $obj->bankid,
                    "ifsccode" => $obj->ifsccode,
                    "pfcode" => $obj->pfcode,
                    "uan" => $obj->uan,
                    "pancard" => $obj->pancard,
                    //"drivinglicense" => $obj->drivinglicense,
                    "adharcard" => $obj->adharcard,
                    "passport" => $obj->passport,
                    //"electioncard" => $obj->electioncard,
                    "dateofbirth" => (isset($obj->dateofbirth) && $obj->dateofbirth != "") ? date('d-m-Y',strtotime($obj->dateofbirth)) : "",
                    "dateofjoining" => (isset($obj->dateofjoining) && $obj->dateofjoining != "") ? date('d-m-Y',strtotime($obj->dateofjoining)) : "",
                    "emp_other_info" => $obj->emp_other_info,
                    "designation" => $des['designation'],
                    "strFatherName" => ucwords(strtolower($obj->strFatherName)),
                    "strMaritalStatus" => ucwords(strtolower($obj->strMaritalStatus)),
                    "strNomineeName" => ucwords(strtolower($obj->strNomineeName)),
                    "strNomineeRelation" => ucwords(strtolower($obj->strNomineeRelation)),
                    "strNomineeAdharNo" => $obj->strNomineeAdharNo,
                    // "strFamilyDetails" => $obj->strFamilyDetails,
                    // "strRelation" => $obj->strRelation,
                    "strPermanentAddress" => ucwords(strtolower($obj->strPermanentAddress)),
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR'],
                    "updated_at" => date('Y-m-d H:i:s'),
                    "iUpdatedBy" => $row['iAndroidUserId']
                );
                $where = ' where iTempEmpId='.$obj->iTempEmpId;
                $dealer_res = $connect->updaterecord($dbconn, 'tempEmpolyeeMaster', $data, $where);
                
                mysqli_query($dbconn,"delete from tempFamilyDetails where iTempEmpId='".$obj->iTempEmpId."'");
                $FamilyDetails = array(
                    "iTempEmpId" => $obj->iTempEmpId,
                    "strFamilyDetails" => ucwords(strtolower($obj->strFamilyDetails)),
                    "iRelation" => $obj->strRelation ?? 0,
                    "strEntryDate" => date('Y-m-d H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR']
                );
                $dealerRes = $connect->insertrecord($dbconn, 'tempFamilyDetails', $FamilyDetails);
                
                if($dealer_res){
                    $output['iTempEmpId'] = $dealer_res;
                    $output['message'] = 'Employee Updated successfully.';
                    $output["success"] = '1';
                } else {
                    $output['iTempEmpId'] = 0;
                    $output['message'] = 'Password not match.';
                    $output["success"] = '0';
                }         
                
            } else {
                $output['TempEmployeeList'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['TempEmployeeList'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['TempEmployeeList'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'delete_tempemployee') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                
                $queryCom = "SELECT * FROM `temempdocument`  where iTempEmpId='".$obj->iTempEmpId."'  and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                if (mysqli_num_rows($resultCom) >= 1) {
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $target_path = '../admin/TempDocument/'.$obj->iTempEmpId."/".$rowCom['strDocumentImage'];
                        
                        if(file_exists($target_path)){
                            unlink($target_path);    
                        }
                        mysqli_query($dbconn,"delete FROM `temempdocument`  where iTempEmpId='".$obj->iTempEmpId."' and iTempDocId='".$rowCom['iTempDocId']."' ");
                    }
                }
                
                mysqli_query($dbconn, 'delete from tempEmpolyeeMaster where iTempEmpId="' . $obj->iTempEmpId . '"');
                $output['message'] = 'success.';
                $output['success'] = '1';
                
            } else {
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
}  else if ($actions == 'marital_status') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                
                $orderStatus = array(
                    array('id' => '', "value" => 'Select Marital Status'),
                    array('id' => 'Married', "value" => 'Married'),
                    array('id' => 'Unmarried', "value" => 'Unmarried'),
				);
                
                $output["MaritalStatus"] = $orderStatus;
                $output['message'] = 'success.';
                $output['success'] = '1';
                
            } else {
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'tempemployeereportexport') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                
                $url = $web_url."admin/tempEmployeeReporDownload.php?Search_Txt=". $obj->strEmployeeName ."&Search_Aadhar=".$obj->Search_Aadhar."&strFromDate=".$obj->strFromDate."&strToDate=".$obj->strToDate;
                $output['url'] = $url;
                $output['message'] = 'Temp Employee List.';
                $output['success'] = '1';
            
            } else {
                $output['url'] = "";
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['url'] = "";
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['url'] = "";
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
} else if ($actions == 'relationlist') {
    $request_body = @file_get_contents('php://input');
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $queryCom = "SELECT * FROM `relation`  where isDelete='0' order by strRelation asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                $output['relationlist'][] = array(
                        "iRelation" => 0,
                        "strRelation" => "Select Relation",
                        "isDelete" => "0"
                    );
                if (mysqli_num_rows($resultCom) >= 1) {
                    
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $output['relationlist'][] = $rowCom;
                    }
                    $output['message'] = 'Relation List.';
                    $output['success'] = '1';
                } else {
                    $output['relationlist'] = [];
                    $output['message'] = 'No Data Found.';
                    $output['success'] = '0';
                }         
                
            } else {
                $output['relationlist'] = [];
                $output['message'] = 'Password not match.';
                $output["success"] = '0';
            }
        } else {
            $output['relationlist'] = [];
            $output['message'] = 'Invalid Device Token';
            $output['success'] = '0';
        }
    } else {
        $output['relationlist'] = [];
        $output['message'] = 'User or Password not match';
        $output['success'] = '0';
    }
}


    

    


print(json_encode($output));


?>