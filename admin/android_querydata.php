<?php

ob_start();
error_reporting(E_ALL);
include('../common.php');
$connect = new connect();
include 'IsLogin.php';
include 'password_hash.php';


$action = $_REQUEST['action'];

switch ($action) {
    case "AddDocument":
        $chankDocument = mysqli_query($dbconn, "SELECT * FROM document where  strDocumentName = '" . $_POST['strDocument'] . "' and isDelete='0' and iStatus='1' ");
        if (mysqli_num_rows($chankDocument) == 0) {
            $data = array(
                "strDocumentName" => ucwords(strtolower($_POST['strDocument'])),
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $dealer_res = $connect->insertrecord($dbconn, 'document', $data);
            echo $statusMsg = $dealer_res ? '1' : '0';
        } else {
            echo $statusMsg = '3';
        }
    break;

    case "GetAdminDocument":
        $filterstr = "SELECT * FROM `document`  where  isDelete='0'  and  iStatus='1' and  iDocumentId=" . $_REQUEST['ID'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        print_r(json_encode($row));
    break;

    case "EditDocument":
        $chankDocument = mysqli_query($dbconn, "SELECT * FROM document where  strDocumentName = '" . $_POST['strDocument'] . "' and isDelete='0' and iStatus='1' and iDocumentId != '" . $_REQUEST['iDocumentId'] . "' ");
        if (mysqli_num_rows($chankDocument) == 0) {
            $data = array(
                "strDocumentName" => ucwords(strtolower($_POST['strDocument'])),
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $where = ' where  iDocumentId =' . $_REQUEST['iDocumentId'];
            $dealer_res = $connect->updaterecord($dbconn, 'document', $data, $where);
            echo $statusMsg = $dealer_res ? '2' : '0';
        } else {
            echo $statusMsg = '3';
        }
    break;
    
    
    case "AddAndroidUser":
        if (preg_match('/^\d{10}$/', $_REQUEST['iMobile'])) {
            $chankDocument = mysqli_query($dbconn, "SELECT * FROM androidusermaster where  iMobile = '" . $_POST['iMobile'] . "' and isDelete='0' and iStatus='1' ");
            
            $hash_result = create_hash($_REQUEST['strPassword']);
            $hash_params = explode(":", $hash_result);
            $salt = $hash_params[HASH_SALT_INDEX];
            $hash = $hash_params[HASH_PBKDF2_INDEX];
            
            if (mysqli_num_rows($chankDocument) == 0) {
                $data = array(
                    "strUserName" => ucwords(strtolower($_POST['strUserName'])),
                    "iMobile" => $_POST['iMobile'],
                    'strPassword' => $hash,
                    'strSalt' => $salt,
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR']
                );
                $dealer_res = $connect->insertrecord($dbconn, 'androidusermaster', $data);
                echo $statusMsg = $dealer_res ? '1' : '0';
            } else {
                echo $statusMsg = '3';
            }
        } else {
            echo $statusMsg = '4';
        }
    break;

    case "GetAdminAndroidUser":
        $filterstr = "SELECT * FROM `androidusermaster`  where  isDelete='0'  and  iStatus='1' and  iAndroidUserId=" . $_REQUEST['ID'] . "";
        $result = mysqli_query($dbconn, $filterstr);
        $row = mysqli_fetch_array($result);
        print_r(json_encode($row));
    break;

    case "EditAndroidUser":
        if (preg_match('/^\d{10}$/', $_REQUEST['iMobile'])) {
            $chankDocument = mysqli_query($dbconn, "SELECT * FROM androidusermaster where  iMobile = '" . $_POST['iMobile'] . "' and isDelete='0' and iStatus='1' and iAndroidUserId != '" . $_REQUEST['iAndroidUserId'] . "' ");
            if (mysqli_num_rows($chankDocument) == 0) {
                $data = array(
                    "strUserName" => ucwords(strtolower($_POST['strUserName'])),
                    "iMobile" => $_POST['iMobile'],
                    "strEntryDate" => date('d-m-Y H:i:s'),
                    "strIP" => $_SERVER['REMOTE_ADDR']
                );
                $where = ' where  iAndroidUserId =' . $_REQUEST['iAndroidUserId'];
                $dealer_res = $connect->updaterecord($dbconn, 'androidusermaster', $data, $where);
                echo $statusMsg = $dealer_res ? '2' : '0';
            } else {
                echo $statusMsg = '3';
            }
        } else {
            echo $statusMsg = '4';
        }
    break;
    
    case "AndroidUserChangePassword": 
        if($_REQUEST['password'] == $_REQUEST['cpassword']){
            $hash_result = create_hash($_REQUEST['password']);
            $hash_params = explode(":", $hash_result);
            $salt = $hash_params[HASH_SALT_INDEX];
            $hash = $hash_params[HASH_PBKDF2_INDEX];
            $getItems1 = mysqli_query($dbconn,"update androidusermaster SET strPassword = '" . $hash . "', strSalt = '" . $salt . "' where iAndroidUserId='" . $_REQUEST['iAndroidUser_Id'] . "'");
            echo "Sucess";
        } else {
            echo "Password Not found";
        }

    break;
    
    case "MoveEmployee":
        $empData = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM tempEmpolyeeMaster where iTempEmpId = '" . $_REQUEST['iTempEmpId'] . "' and isDelete='0' and istatus='1'"));
        $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $_POST['designation'] . "'"));
        $EntrDate = "";
        if ($empData['strEntryDate'] != NULL || $empData['strEntryDate'] != '') {
            $EntrDate = $empData['strEntryDate'];
        } else {
            $EntrDate = date('d-m-Y H:i:s');
        }

        $arr = explode(' ', $EntrDate);
        $dateArrar = explode('-', $arr[0]);

        $aadharImage = "";
        $passportImage = "";
        $pancardImage = "";
        $voteridImage = "";
        $filterTempDoc = mysqli_query($dbconn, "SELECT * FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."'");
        if(mysqli_num_rows($filterTempDoc) > 0){
            while($rowTempDoc = mysqli_fetch_assoc($filterTempDoc)){
                if($rowTempDoc['iDocumentId'] == 1){
                    $aadharImage = $rowTempDoc['strDocumentImage'];
                } 
                if($rowTempDoc['iDocumentId'] == 3){
                    $passportImage = $rowTempDoc['strDocumentImage'];
                } 
                if($rowTempDoc['iDocumentId'] == 4){
                    $pancardImage = $rowTempDoc['strDocumentImage'];
                } 
                if($rowTempDoc['iDocumentId'] == 2){
                    $voteridImage = $rowTempDoc['strDocumentImage'];
                } 
                
                if($rowTempDoc['iDocumentId'] == 1 || $rowTempDoc['iDocumentId'] == 2 || $rowTempDoc['iDocumentId'] == 3 || $rowTempDoc['iDocumentId'] == 4){
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
                    if (!file_exists($path)) {
                        mkdir($path, 0777, TRUE);
                    }
                    if($rowTempDoc['strDocumentImage'] != ""){
                        $target_path = 'TempDocument/'. $_REQUEST['iTempEmpId']. '/'.$rowTempDoc['strDocumentImage'];
                        rename($target_path, $path.$rowTempDoc['strDocumentImage']);
                        unlink($target_path);
                    }
                     
                } else {
                    if($rowTempDoc['strDocumentImage'] != ""){
                        $target_path = 'TempDocument/'. $_REQUEST['iTempEmpId']. '/'.$rowTempDoc['strDocumentImage'];
                        unlink($target_path);
                    }
                }
            }
        }
        $data = array(
            "emp_name" => ucwords(strtolower($_POST['emp_name'])),
            //"emp_other_info" => $_POST['emp_other_info'],
            "mno" => $_POST['mno'],
            "address" => ucwords(strtolower($_POST['address'])),
            "bankid" => $_POST['bankid'],
            // "otherbankname" => $_POST['otherbankname'],
            "ecsno" => $_POST['ecsno'],
            "accountno" => $_POST['accountno'],
            "ifsccode" => $_POST['ifsccode'],
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
           // "electioncard" => $_POST['voterid'],
            "voteridImage" => strtolower($voteridImage),
            "passport" => $_POST['passport'],
            "passportImage" => strtolower($passportImage),
            //  "other" => $_POST['other'],
            "dateofbirth" => (isset($_POST['dateofbirth']) && $_POST['dateofbirth'] != "") ? $_POST['dateofbirth'] : "",
            "uan" => $_POST['uan'],
            "dateofjoining" => (isset($_POST['dateofjoining']) && $_POST['dateofjoining'] != "") ? date('M-Y',strtotime($_POST['dateofjoining'])) : "",
            "strFatherName" => ucwords(strtolower($_POST['strFatherName'])),
            "strMaritalStatus" => ucwords(strtolower($_POST['strMaritalStatus'])),
            "strNomineeName" => ucwords(strtolower($_POST['strNomineeName'])),
            "strNomineeRelation" => ucwords(strtolower($_POST['strNomineeRelation'])),
            "strNomineeAdharNo" => $_POST['strNomineeAdharNo'],
            // "strFamilyDetails" => $_POST['strFamilyDetails'],
            // "strRelation" => $_POST['strRelation'],
            "strPermanentAddress" => ucwords(strtolower($_POST['strPermanentAddress'])),
            "strEntryDate" => $EntrDate,
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iCreatedBy" => $empData['iCreatedBy'],
            "created_at" => $empData['created_at'],
            "iUpdatedBy" => $empData['iUpdatedBy'],
            "updated_at" => $empData['updated_at'],
            "iMovedBy" => $_SESSION['AdminId'],
            "moved_at" => date('Y-m-d H:i:d')
        );
        $dealer_res = $connect->insertrecord($dbconn, 'employee', $data);
        
        mysqli_query($dbconn,"INSERT INTO EmployeeFamilyDetails (iEmpId, strFamilyDetails, iRelation) SELECT ".$dealer_res.", strFamilyDetails,iRelation FROM tempFamilyDetails where iTempEmpId='".$_REQUEST['iTempEmpId']."'");
        mysqli_query($dbconn, "Delete FROM `tempFamilyDetails` where iTempEmpId='".$_REQUEST['iTempEmpId']."'");
            
        mysqli_query($dbconn, "Delete FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."'");
        mysqli_query($dbconn, "delete FROM tempEmpolyeeMaster where iTempEmpId = '" . $_REQUEST['iTempEmpId'] . "'");
        
        echo $statusMsg = $dealer_res ? '1' : '0';
        
    break;
    
    case "EditTempEmployee":
        
        if($_POST['pfcode'] != ""){
            $filterTempEmpPFCode = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where pfcode='".$_POST['pfcode']."' and iTempEmpId!=".$_REQUEST['iTempEmpId']."
            UNION All 
            SELECT * from employee where pfcode='".$_POST['pfcode']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpPFCode) > 0){
                echo 3;
                exit;
            }
        }
        if($_POST['ecsno'] != ""){
            $filterTempEmpEcsno = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where ecsno='".$_POST['ecsno']."'  and iTempEmpId!=".$_REQUEST['iTempEmpId']."
            UNION All 
            SELECT * from employee where ecsno='".$_POST['ecsno']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpEcsno) > 0){
                echo 4;
                exit;
            }
        }
        if($_POST['uan'] != ""){
            $filterTempEmpUan = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where uan='".$_POST['uan']."' and iTempEmpId!=".$_REQUEST['iTempEmpId']."
            UNION All 
            SELECT * from employee where uan='".$_POST['uan']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpUan) > 0){
                echo 5;
                exit;
            }
        }
        if($_POST['accountno'] != ""){
            $filterTempEmpAccount = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where accountno='".$_POST['accountno']."' and iTempEmpId!=".$_REQUEST['iTempEmpId']."
            UNION All 
            SELECT * from employee where accountno='".$_POST['accountno']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpAccount) > 0){
                echo 6;
                exit;
            }
        }
        if($_POST['mno'] != ""){
            $filterTempEmpMno = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where mno='".$_POST['mno']."' and iTempEmpId!=".$_REQUEST['iTempEmpId']."
                UNION All 
                SELECT * from employee where mno='".$_POST['mno']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpMno) > 0){
                echo 7;
                exit;
            }
        }
        if($_POST['pancard'] != ""){
            $filterTempEmpPancard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where pancard='".$_POST['pancard']."' and iTempEmpId!=".$_REQUEST['iTempEmpId']."
            UNION All 
            SELECT * from employee where pancard='".$_POST['pancard']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpPancard) > 0){
                echo 8;
                exit;
            }
        }
        if($_POST['aadharcard'] != ""){
            $filterTempEmpAadharCard = mysqli_query($dbconn,"select * from tempEmpolyeeMaster where adharcard='".$_POST['aadharcard']."' and iTempEmpId!=".$_REQUEST['iTempEmpId']."
            UNION All 
            SELECT * from employee where adharcard='".$_POST['aadharcard']."' and isDelete=0 and istatus=1");
            if(mysqli_num_rows($filterTempEmpAadharCard) > 0){
                $output['message'] = 'Aadhar Card Number is already exists.';
                echo 9;
                exit;
            }
        }
        
        $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $_POST['designation'] . "'"));
        $data = array(
            "emp_name" => ucwords(strtolower($_POST['emp_name'])),
            //"emp_other_info" => $_POST['emp_other_info'],
            "designationid" => $_POST['designation'],
            "designation" => ucwords(strtolower($des['designation'])),
            "pfcode" => $_POST['pfcode'],
            "employeecode" => $_POST['employeecode'],
            "ecsno" => $_POST['ecsno'],
            "uan" => $_POST['uan'],
            "strFatherName" => ucwords(strtolower($_POST['strFatherName'])),
            "dateofjoining" => (isset($_POST['dateofjoining']) && $_POST['dateofjoining'] != "") ? $_POST['dateofjoining'] : "",
            "dateofbirth" => (isset($_POST['dateofbirth']) && $_POST['dateofbirth'] != "") ? $_POST['dateofbirth'] : "",
            "bankid" => $_POST['bankid'],
            "accountno" => $_POST['accountno'],
            "ifsccode" => $_POST['ifsccode'],
            "mno" => $_POST['mno'],
            "strMaritalStatus" => ucwords(strtolower($_POST['strMaritalStatus'])),
            "strNomineeName" => ucwords(strtolower($_POST['strNomineeName'])),
            "strNomineeRelation" => ucwords(strtolower($_POST['strNomineeRelation'])),
            "strNomineeAdharNo" => $_POST['strNomineeAdharNo'],
            // "strFamilyDetails" => $_POST['strFamilyDetails'],
            // "strRelation" => $_POST['strRelation'],
            "adharcard" => $_POST['aadharcard'],
            "pancard" => $_POST['pancard'],
            //"passport" => $_POST['passport'],
            "address" => ucwords(strtolower($_POST['address'])),
            "strPermanentAddress" => ucwords(strtolower($_POST['strPermanentAddress'])),
            // "otherbankname" => $_POST['otherbankname'],
            //"salaryamt" => $_POST['salaryamt'],
            // "aadharImage" => strtolower($aadharImage),
            // "pancardImage" => strtolower($pancardImage),
            //"drivinglicense" => $_POST['drivinglicense'],
            //"electioncard" => $_POST['voterid'],
            // "voteridImage" => strtolower($voteridImage),
            // "passportImage" => strtolower($passportImage),
            //"other" => $_POST['other'],
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iMovedBy" => $_SESSION['AdminId'],
            "moved_at" => date('Y-m-d H:i:d')
        );
        $where = ' where  iTempEmpId =' . $_REQUEST['iTempEmpId'];
        $dealer_res = $connect->updaterecord($dbconn, 'tempEmpolyeeMaster', $data, $where);
        
        $strFamilyDetails = sizeof($_POST['strFamilyDetails']);
        mysqli_query($dbconn,"delete from tempFamilyDetails where iTempEmpId='".$_REQUEST['iTempEmpId']."'");
        for($iCounter = 0;$iCounter < $strFamilyDetails; $iCounter++){
            $FamilyDetails = array(
                "iTempEmpId" => $_REQUEST['iTempEmpId'],
                "strFamilyDetails" => ucwords(strtolower($_POST['strFamilyDetails'][$iCounter])),
                "iRelation" => $_POST['strRelation'][$iCounter],
                "strEntryDate" => date('Y-m-d H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $dealerRes = $connect->insertrecord($dbconn, 'tempFamilyDetails', $FamilyDetails);
        }
        echo $statusMsg = $_REQUEST['iTempEmpId'] ? '2' : '0';
    break;
    
    case "UploadTempEmployeeDocuments":
        
        $valid_extensions = array('pdf', 'PDF','jpg','JPG','jpeg','JPEG','png','PNG'); // valid extensions
        if (!file_exists('TempDocument/'. $_REQUEST['iTempEmpId'])) {
            mkdir('TempDocument/'.$_REQUEST['iTempEmpId'], 0777, TRUE);
        }
        $path = 'TempDocument/' .$_REQUEST['iTempEmpId'] . "/";
        $aadharImage = "";
        $aadharbackImage = "";
        $PanCardImage ="";
        $OtherDocumentImage = "";
        
        if(isset($_FILES['AadharCardFront']['name']) && $_FILES['AadharCardFront']['name'] != ""){
            $AadharCardFrontSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='1'");
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
            mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='1'");
            $data = array(
                "iTempEmpId" => $_REQUEST['iTempEmpId'],
                "iDocumentId" => $_REQUEST['AadharCardFrontDocId'],
                "strDocumentImage" => $aadharImage,
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
        }
        
        if(isset($_FILES['AadharCardBack']['name']) && $_FILES['AadharCardBack']['name'] != ""){
            $AadharCardBackSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='2'");
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
            mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='2'");
            $data = array(
                "iTempEmpId" => $_REQUEST['iTempEmpId'],
                "iDocumentId" => $_REQUEST['AadharCardBackDocId'],
                "strDocumentImage" => $aadharbackImage,
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
        }
        
        
        if(isset($_FILES['PanCard']['name']) && $_FILES['PanCard']['name'] != ""){
            $PanCardSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='3'");
            if(mysqli_num_rows($PanCardSql) == 1){
                $PanCardData = mysqli_fetch_assoc($PanCardSql);
                if ($PanCardData['strDocumentImage'] != NULL || $PanCardData['strDocumentImage'] != '') {
                    unlink($path . $PanCardData['strDocumentImage']);
                }
            } 
            $pandcardimg = $_FILES['PanCard']['name'];
            $tmp = $_FILES['PanCard']['tmp_name'];
            // $ext = strtolower(pathinfo($pandcardimg, PATHINFO_EXTENSION));
            // $pancard_final_image = preg_replace('/\s+/', '_', $pandcardimg);
            $ext = pathinfo($_FILES['PanCard']['name'], PATHINFO_EXTENSION);
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
        
        if(isset($_FILES['PanCard']['name']) && $_FILES['PanCard']['name'] != ""){
            mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='3'");
            $data = array(
                "iTempEmpId" => $_REQUEST['iTempEmpId'],
                "iDocumentId" => $_REQUEST['PanCardDocId'],
                "strDocumentImage" => $PanCardImage,
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
        }
        
        
        if(isset($_FILES['OtherDocument']['name']) && $_FILES['OtherDocument']['name'] != ""){
            $OtherDocumentSql = mysqli_query($dbconn,"SELECT * FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='4'");
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
            mysqli_query($dbconn,"delete FROM `temempdocument` where iTempEmpId='".$_REQUEST['iTempEmpId']."' and iDocumentId='4'");
            $data = array(
                "iTempEmpId" => $_REQUEST['iTempEmpId'],
                "iDocumentId" => $_REQUEST['OtherDocumentDocId'],
                "strDocumentImage" => $OtherDocumentImage,
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR']
            );
            $dealer_res = $connect->insertrecord($dbconn, 'temempdocument', $data);
        }
        echo 1;       
    break;
    
    case "tempemployeemovedata":
        
        foreach($_REQUEST['check_list'] as $iTempEmpId){
            $empData = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM tempEmpolyeeMaster where iTempEmpId='".trim($iTempEmpId)."' and isDelete='0' and istatus='1'"));
        
            $des = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM designation where designationid='" . $empData['designationid'] . "'"));
            $EntrDate = "";
            if ($empData['strEntryDate'] != NULL || $empData['strEntryDate'] != '') {
                $EntrDate = $empData['strEntryDate'];
            } else {
                $EntrDate = date('d-m-Y H:i:s');
            }
    
            $arr = explode(' ', $EntrDate);
            $dateArrar = explode('-', $arr[0]);
    
            $aadharImage = "";
            $passportImage = "";
            $pancardImage = "";
            $voteridImage = "";
            $filterTempDoc = mysqli_query($dbconn, "SELECT * FROM `temempdocument` where iTempEmpId='".$empData['iTempEmpId']."'");
            if(mysqli_num_rows($filterTempDoc) > 0){
                while($rowTempDoc = mysqli_fetch_assoc($filterTempDoc)){
                    if($rowTempDoc['iDocumentId'] == 1){
                        $aadharImage = $rowTempDoc['strDocumentImage'];
                    } 
                    if($rowTempDoc['iDocumentId'] == 3){
                        $passportImage = $rowTempDoc['strDocumentImage'];
                    } 
                    if($rowTempDoc['iDocumentId'] == 4){
                        $pancardImage = $rowTempDoc['strDocumentImage'];
                    } 
                    if($rowTempDoc['iDocumentId'] == 2){
                        $voteridImage = $rowTempDoc['strDocumentImage'];
                    } 
                    
                    if($rowTempDoc['iDocumentId'] == 1 || $rowTempDoc['iDocumentId'] == 2 || $rowTempDoc['iDocumentId'] == 3 || $rowTempDoc['iDocumentId'] == 4){
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
                        if (!file_exists($path)) {
                            mkdir($path, 0777, TRUE);
                        }
                        if($rowTempDoc['strDocumentImage'] != ""){
                            $target_path = 'TempDocument/'. $empData['iTempEmpId']. '/'.$rowTempDoc['strDocumentImage'];
                            rename($target_path, $path.$rowTempDoc['strDocumentImage']);
                            unlink($target_path);
                        }
                    } else {
                        if($rowTempDoc['strDocumentImage'] != ""){
                            $target_path = 'TempDocument/'. $empData['iTempEmpId']. '/'.$rowTempDoc['strDocumentImage'];
                            unlink($target_path);
                        }
                    }
                }
            }
            $data = array(
                "emp_name" => ucwords(strtolower($empData['emp_name'])),
                "mno" => $empData['mno'],
                "address" => ucwords(strtolower($empData['address'])),
                "bankid" => $empData['bankid'],
                "ecsno" => $empData['ecsno'],
                "accountno" => $empData['accountno'],
                "ifsccode" => $empData['ifsccode'],
                "designationid" => $empData['designationid'],
                "designation" => ucwords(strtolower($des['designation'])),
                "pfcode" => $empData['pfcode'],
                "employeecode" => $empData['employeecode'],
                "adharcard" => $empData['adharcard'],
                "aadharImage" => strtolower($aadharImage),
                "pancard" => $empData['pancard'],
                "pancardImage" => strtolower($pancardImage),
                "voteridImage" => strtolower($voteridImage),
                "passport" => $empData['passport'],
                "passportImage" => strtolower($passportImage),
                "dateofbirth" => (isset($empData['dateofbirth']) && $empData['dateofbirth'] != "") ? $empData['dateofbirth'] : "",
                "uan" => $empData['uan'],
                "dateofjoining" => (isset($empData['dateofjoining']) && $empData['dateofjoining'] != "") ? $empData['dateofjoining'] : "",
                "strFatherName" => ucwords(strtolower($empData['strFatherName'])),
                "strMaritalStatus" => ucwords(strtolower($empData['strMaritalStatus'])),
                "strNomineeName" => ucwords(strtolower($empData['strNomineeName'])),
                "strNomineeRelation" => ucwords(strtolower($empData['strNomineeRelation'])),
                "strNomineeAdharNo" => $empData['strNomineeAdharNo'],
                // "strFamilyDetails" => $empData['strFamilyDetails'],
                // "strRelation" => $empData['strRelation'],
                "strPermanentAddress" => ucwords(strtolower($empData['strPermanentAddress'])),
                "strEntryDate" => $EntrDate,
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iCreatedBy" => $empData['iCreatedBy'],
                "created_at" => $empData['created_at'],
                "iUpdatedBy" => $empData['iUpdatedBy'],
                "updated_at" => $empData['updated_at'],
                "iMovedBy" => $_SESSION['AdminId'],
                "moved_at" => date('Y-m-d H:i:d')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'employee', $data);
            
            mysqli_query($dbconn,"INSERT INTO EmployeeFamilyDetails (iEmpId, strFamilyDetails, iRelation) SELECT ".$dealer_res.", strFamilyDetails,iRelation FROM tempFamilyDetails where iTempEmpId='".$empData['iTempEmpId']."'");
            mysqli_query($dbconn, "Delete FROM `tempFamilyDetails` where iTempEmpId='".$empData['iTempEmpId']."'");
            mysqli_query($dbconn, "Delete FROM `temempdocument` where iTempEmpId='".$empData['iTempEmpId']."'");
            mysqli_query($dbconn, "Delete FROM tempEmpolyeeMaster where iTempEmpId = '" . $empData['iTempEmpId'] . "'");
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
    break;
    
    case "employeeaddtoexportlist" :
        
        $dealer_res = 0;
        foreach($_POST['check_list'] as $empId){
            $data = array(
                "iEmpoyeeId" => $empId,
                "strType" => ucwords(strtolower($_POST['strType'])),
                "iType" => $_POST['iType'],
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "strEntryDate" => date('d-m-Y H:i:s')
            );
            $dealer_res = $connect->insertrecord($dbconn, 'exportemployeelist', $data);
        }
        echo $statusMsg = $dealer_res ? '1' : '0';
    break;
    
    case "clearemployeeexportdata":
        $empData = mysqli_query($dbconn, "TRUNCATE table exportemployeelist");
        echo 1;
    break;
    
    case "tempEmployeedeletedata" :
        $CheckList = $_POST['check_list'];
        foreach($_POST['check_list'] as $empId){
            if($empId != ""){
                $filterSql = mysqli_query($dbconn, 'select * from temempdocument where iTempEmpId in  ("' . trim($empId) . '")');
                if(mysqli_num_rows($filterSql) > 0)
                {
                    while($row = mysqli_fetch_assoc($filterSql)){
                        $path = "./TempDocument/".trim($empId)."/".$row['strDocumentImage'];
                        if(file_exists($path)){
                            unlink($path);    
                        }
                    }
                }
                mysqli_query($dbconn, 'delete from temempdocument where iTempEmpId in  ("' . trim($empId) . '")');
                mysqli_query($dbconn, 'delete from tempEmpolyeeMaster where iTempEmpId ="' . trim($empId) . '"');
            }
        }
        echo $statusMsg = '1';
    break;
    
    default:
        # code...
        echo "Page not Found";
    break;
}
?>