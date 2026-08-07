<?php
ob_start();
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
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $obj->designation . "' "));
                $data = array(
                    "emp_name" => $obj->emp_name,
                    "emp_other_info" => $obj->emp_other_info,
                    "mno" => $obj->mno,
                    "address" => $obj->address,
                    "bankid" => $obj->bankid,
                    "ecsno" => $obj->ecsno,
                    "accountno" => $obj->accountno,
                    "ifsccode" => $obj->ifsccode,
                    "designationid" => $obj->designation,
                    "designation" => $des['designation'],
                    "pfcode" => $obj->pfcode,
                    "employeecode" => $obj->employeecode,
                    "adharcard" => $obj->aadharcard,
                    "pancard" => $obj->pancard,
                    "electioncard" => $obj->voterid,
                    "passport" => $obj->passport,
                    "dateofbirth" => $obj->dateofbirth,
                    "uan" => $obj->uan,
                    "dateofjoining" => $obj->dateofjoining,
                    "drivinglicense" => $obj->drivinglicense,
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR']
                );
                $dealer_res = $connect->insertrecord($dbconn, 'tempEmpolyeeMaster', $data);
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
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                if (mysqli_num_rows($resultCom) >= 1) {
                    $output['DesignationList'][] = array(
                        "designationid" => 0,
                        "designation" => "Select Designation",
                        "strEntryDate" => "",
                        "strIP" => "",
                        "isDelete" => "0",
                        "iStatus" => 1
                    );
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
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                if (mysqli_num_rows($resultCom) >= 1) {
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
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                if (mysqli_num_rows($resultCom) >= 1) {
                    $output['Documentist'][] = array(
                        "iDocumentId" => 0,
                        "strDocumentName" => "Select Document",
                        "strEntryDate" => "",
                        "strIP" => "",
                        "isDelete" => "0",
                        "iStatus" => 1
                    );
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
    //file_put_contents('adddocumentphotos.txt', file_get_contents('php://input'));
    $obj = json_decode($request_body);
    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '".$_REQUEST['iMobile']."' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $_REQUEST['strDeviceToken']){
            if (validate_password($_REQUEST['strPassword'], $good_hash)) {
                $queryCom = "SELECT * FROM `temempdocument`  where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='".$_REQUEST['iDocumentId']."' and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                
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
                    $output['message'] = 'Document Already Uploaded..';
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
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                if (mysqli_num_rows($resultCom) >= 1) {
                    while($rowCom = mysqli_fetch_assoc($resultCom)){
                        $target_path = $web_url.'admin/TempDocument/'.$obj->iTempEmpId."/";
                        $queryDoc = "SELECT * FROM `document`  where iDocumentId='".$rowCom['iDocumentId']."' and  isDelete='0'  and  iStatus='1' order by  iDocumentId asc";
                        $resultDoc = mysqli_query($dbconn, $queryDoc) or die(mysql_error());
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
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
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
                    $where .=" and emp_name like '%". $obj->strEmployeeName ."%'";
                } 
                $queryCom = "SELECT iTempEmpId,emp_name,adharcard,mno,strEntryDate FROM `tempEmpolyeeMaster`  ".$where." and  isDelete='0'  and  istatus='1' order by emp_name asc";
                
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
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
                
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                if (mysqli_num_rows($resultCom) >= 1) {
                    $rowCom = mysqli_fetch_assoc($resultCom);
                   // $output = $rowCom;
                   
                    $queryBankCom = "SELECT * FROM `bankmaster`  where bankmasterId='".$rowCom['bankid']."' and  isDelete='0'  and  istatus='1' order by  bankmasterId asc";
                    $resultBankCom = mysqli_query($dbconn, $queryBankCom) or die(mysql_error());

                    $rowBankCom = mysqli_fetch_assoc($resultBankCom);
                    $output['bankname'] = isset($rowBankCom['bankname']) && $rowBankCom['bankname'] != "" ? $rowBankCom['bankname'] : "Select Bank";
                   
                   $output["designation"] = isset($rowCom['designation']) && $rowCom['designation'] != "" ? $rowCom['designation'] : 'Select Designation';
                   $output["iTempEmpId"] = $rowCom['iTempEmpId'];
                   $output["emp_name"] = isset($rowCom['emp_name']) && $rowCom['emp_name'] != "" ? $rowCom['emp_name'] : ''; 
                   $output["emp_other_info"] = isset($rowCom['emp_other_info']) && $rowCom['emp_other_info'] != "" ? $rowCom['emp_other_info'] : "";
                   $output["mno"] = isset($rowCom['mno']) && $rowCom['mno'] != "" ? $rowCom['mno'] : '';
                   $output["address"] = isset($rowCom['address']) && $rowCom['address'] != "" ? $rowCom['address'] : "";
                   $output["bankid"] = $rowCom['bankid'];
                   $output["otherbankname"] = isset($rowCom['otherbankname']) && $rowCom['otherbankname'] != "" ? $rowCom['otherbankname'] : "";
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
                   $output["drivinglicense"] = isset($rowCom['drivinglicense']) && $rowCom['drivinglicense'] != "" ? $rowCom['drivinglicense'] : "";
                   $output["electioncard"] = isset($rowCom['electioncard']) && $rowCom['electioncard'] != "" ? $rowCom['electioncard'] : "";
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
    $obj = json_decode($request_body);

    $sql = "select iAndroidUserId,iMobile,strPassword,strSalt,strUserName,strDeviceToken from androidusermaster where iMobile = '$obj->iMobile' and isDelete=0 and iStatus=1";
    $result = mysqli_query($dbconn, $sql);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['strSalt'] . ":" . $row['strPassword'];
        if($row['strDeviceToken'] == $obj->strDeviceToken){
            if (validate_password($obj->strPassword, $good_hash)) {
                
                
                
                $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $obj->designationid . "' "));
                $data = array(
                    "emp_name" => $obj->emp_name,
                    "designationid" => $obj->designationid,
                    "employeecode" => $obj->employeecode,
                    "mno" => $obj->mno,
                    "address" => $obj->address,
                    "ecsno" => $obj->ecsno,
                    "accountno" => $obj->accountno,
                    "bankid" => $obj->bankid,
                    "ifsccode" => $obj->ifsccode,
                    "pfcode" => $obj->pfcode,
                    "uan" => $obj->uan,
                    "pancard" => $obj->pancard,
                    "drivinglicense" => $obj->drivinglicense,
                    "adharcard" => $obj->adharcard,
                    "passport" => $obj->passport,
                    "electioncard" => $obj->electioncard,
                    "dateofbirth" => $obj->dateofbirth,
                    "dateofjoining" => $obj->dateofjoining,
                    "emp_other_info" => $obj->emp_other_info,
                    "designation" => $des['designation'],
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR']
                );
                $where = ' where iTempEmpId='.$obj->iTempEmpId;
                $dealer_res = $connect->updaterecord($dbconn, 'tempEmpolyeeMaster', $data, $where);
            
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
                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
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
}


    

    


print(json_encode($output));


?>