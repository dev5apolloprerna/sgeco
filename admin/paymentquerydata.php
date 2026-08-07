<?php

ob_start();
error_reporting(E_ALL);
include('../common.php');
$connect = new connect();
include 'IsLogin.php';
include 'password_hash.php';


$action = $_REQUEST['action'];
switch ($action) {
    
    case "paidpayement":
        $paymentData = array(
            "iCompanySalaryMasterId" => $_POST['companysalarymasterId'],
            "salarymonth" => $_POST['salarymonthId'],
            "strPaymentDate" => date('d-m-Y',strtotime($_POST['strDate'])),
            "iPaymentMode" => $_POST['iMode'],
            "iBank" => $_POST['bank'] ? $_POST['bank'] : 0,
            "strTransactionNo" => $_POST['strTransactionNo'],
            "iAmount" => $_POST['iAmount'],
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iEntryBy" => $_SESSION['AdminId'],
            "EntryDate" => date('d-m-Y H:i:s')
        );
        $iPaymentId = $connect->insertrecord($dbconn, 'paymentMaster', $paymentData);
        $Emp_list = $_POST['check_list'];
        $multicompanyid = $_POST['multicompanyid'];
        for($iCounter= 0; $iCounter < sizeof($Emp_list); $iCounter++){
            $empId = $Emp_list[$iCounter];
            $multicompanyid = trim("multicompanyid_".$empId);
            $_POST["$multicompanyid"];
            if(isset($_POST["$multicompanyid"])){
                $data = array(
                    "iPaymentId" => $iPaymentId,
                    "iPaymentStatus" => 1,
                    "strPaymentDate" => date('d-m-Y',strtotime($_POST['strDate'])),
                    "iPaymentBy" => $_SESSION['AdminId']
                );
                $where = ' where  emp_id =' . $empId .' and multicompanyid='. $_POST["$multicompanyid"];
                $dealer_res = $connect->updaterecord($dbconn, 'multicompany', $data, $where);
            }
        }
        echo $statusMsg = $iPaymentId ? '1' : '0';
    break;

    case "AddUsers":
        
        $result = mysqli_query($dbconn, "SELECT * FROM `admin` WHERE `loginid`='" . $_POST['loginid'] . "'");
        if (mysqli_num_rows($result) > 0) {
            echo 3;
        } else {
            $hash_result = create_hash($_REQUEST['password']);
            $hash_params = explode(":", $hash_result);
            $salt = $hash_params[HASH_SALT_INDEX];
            $hash = $hash_params[HASH_PBKDF2_INDEX];
            
            $data = array(
                "username" => $_POST['username'],
                "loginid" => $_POST['loginid'],
                "type" => 2,
                "email" => $_POST['email'],
                "mobileNo" => $_POST['mobileNo'],
                "password" => $hash,
                "salt" => $salt,
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iEntryBy" => $_SESSION['AdminId'],
                "EntryDate" => date('d-m-Y H:i:s')
            );
            $iPaymentId = $connect->insertrecord($dbconn, 'admin', $data);
        }
        echo $statusMsg = $iPaymentId ? '1' : '0';
    break;
    
    case "EditUsers":
        $dealer_res = 0;
        $result = mysqli_query($dbconn, "SELECT * FROM `admin` WHERE id!='". $_POST['id']."' and `loginid`='" . $_POST['loginid'] . "'");
        if (mysqli_num_rows($result) > 0) {
            echo $dealer_res = 3;
            break;
        } else {
            $hash_result = create_hash($_REQUEST['password']);
            $hash_params = explode(":", $hash_result);
            $salt = $hash_params[HASH_SALT_INDEX];
            $hash = $hash_params[HASH_PBKDF2_INDEX];
            
            $data = array(
                "username" => $_POST['username'],
                "loginid" => $_POST['loginid'],
                "type" => 2,
                "email" => $_POST['email'],
                "mobileNo" => $_POST['mobileNo'],
                "strEntryDate" => date('d-m-Y H:i:s'),
                "strIP" => $_SERVER['REMOTE_ADDR'],
                "iUpdatedBy" => $_SESSION['AdminId'],
                "UpdatedDate" => date('d-m-Y H:i:s')
            );
            $where = ' where  id =' . $_POST['id'];
            $dealer_res = $connect->updaterecord($dbconn, 'admin', $data, $where);
        }
        echo $statusMsg = $dealer_res ? '2' : '0';
    break;
    
    
    case "AddChangePassword":
        $existsmail = "SELECT * FROM admin where id='" . $_POST['iUserId'] . "'";
        $result = mysqli_query($dbconn,$existsmail);
        $num_rows = mysqli_num_rows($result);
        $row = mysqli_fetch_array($result);
        if ($num_rows == 1) {
            $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['salt'] . ":" . $row['password'];
            $hash_result = create_hash($_POST['password']);
            $hash_params = explode(":", $hash_result);
            $salt = $hash_params[HASH_SALT_INDEX];
            $hash = $hash_params[HASH_PBKDF2_INDEX];
            $getItems1 = mysqli_query($dbconn,"update admin SET password = '" . $hash . "', salt = '" . $salt . "' where id='" . $_POST['iUserId'] . "'");
            echo "Sucess";
        } else {
            echo "ID not found";
        }
       
    break;
    
    
    case "addUserRights": 
        
        mysqli_query($dbconn, "delete from user_rights where iUserId='".$_POST['id']."'");
        
        $data = array(
            "iUserId" => $_POST['id'],
            "isMasterMenu" => $_POST['isMasterMenu'],
            "isEmployeeEntry" => $_POST['isEmployeeEntry'],
            "isSalaryMenu" => $_POST['isSalaryMenu'],
            "isReportMenu" => $_POST['isReportMenu'],
            "isBankReportMenu" => $_POST['isBankReportMenu'],
            "isPaymentMenu" => $_POST['isPaymentMenu'],
            "isBankEntry" => $_POST['isBankEntry'],
            "isCompanyEntry" => $_POST['isCompanyEntry'],
            "isDesignationEntry" => $_POST['isDesignationEntry'],
            "isAndroidUserEntry" => $_POST['isAndroidUserEntry'],
            "isTempEmployeeEntry" => $_POST['isTempEmployeeEntry'],
            "isUserEntry" => $_POST['isUserEntry'],
            "isViewSalary" => $_POST['isViewSalary'],
            "isSalaryCreateEntry" => $_POST['isSalaryCreateEntry'],
            "isComplanySalaryEntry" => $_POST['isComplanySalaryEntry'],
            "isViewCompanySalary" => $_POST['isViewCompanySalary'],
            "isViewCompanyReport" => $_POST['isViewCompanyReport'],
            "isViewMultiCompanyReport" => $_POST['isViewMultiCompanyReport'],
            "isViewESICReport" => $_POST['isViewESICReport'],
            "isViewPFChallanReport" => $_POST['isViewPFChallanReport'],
            "isViewCompanyBankReport" => $_POST['isViewCompanyBankReport'],
            "isViewMultiCompanyBankReport" => $_POST['isViewMultiCompanyBankReport'],
            "isViewPayPayment" => $_POST['isViewPayPayment'],
            "isViewPaidPayment" => $_POST['isViewPaidPayment'],
            "isViewMultiCompanyPayPayment" => $_POST['isViewMultiCompanyPayPayment'],
            "isViewCompanyPaymentHistory" => $_POST['isViewCompanyPaymentHistory'],
            "isViewMultiCompanyPaidPayment" => $_POST['isViewMultiCompanyPaidPayment'],
            "isViewMultiCompanyPaymentHistory" => $_POST['isViewMultiCompanyPaymentHistory'],
            "isPaymentHistoryMenu" => $_POST['isPaymentHistoryMenu'],
            "strEntryDate" => date('d-m-Y'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iEntryBy" => $_SESSION['AdminId']
        );
        $dealer_res = $connect->insertrecord($dbconn, 'user_rights', $data);
        
        echo $statusMsg = $dealer_res ? '1' : '0';
    break;
    
    case "companypaidpayement" :
        
        $paymentData = array(
            "iCompanySalaryMasterId" => $_POST['Companyid'],
            "salarymonth" => $_POST['salaryId'],
            "strPaymentDate" => date('d-m-Y',strtotime($_POST['strDate'])),
            "iPaymentMode" => $_POST['iMode'],
            "iBank" => $_POST['bank'] ? $_POST['bank'] : 0,
            "strTransactionNo" => $_POST['strTransactionNo'],
            "iAmount" => $_POST['iAmount'],
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR'],
            "iEntryBy" => $_SESSION['AdminId'],
            "EntryDate" => date('d-m-Y H:i:s')
        );
        $iPaymentId = $connect->insertrecord($dbconn, 'companypaymentMaster', $paymentData);
        $Emp_list = $_POST['check_list'];
        //$salarydetailsId = $_POST['salarydetailsId'];
        for($iCounter= 0; $iCounter < sizeof($Emp_list); $iCounter++){
            $empId = $Emp_list[$iCounter];
            $salarydetailsId = trim("salarydetailsId_".$empId);
            $_POST["$salarydetailsId"];
            if(isset($_POST["$salarydetailsId"])){
                $data = array(
                    "iPaymentId" => $iPaymentId,
                    "iPaymentStatus" => 1,
                    "strPaymentDate" => date('d-m-Y',strtotime($_POST['strDate'])),
                    "iPaymentBy" => $_SESSION['AdminId']
                );
                $where = ' where emp_id =' . $empId .' and salarydetailsId='. $_POST["$salarydetailsId"];
                $dealer_res = $connect->updaterecord($dbconn, 'salarydetails', $data, $where);
            }
        }
        echo $statusMsg = $iPaymentId ? '1' : '0';
    break;    
    
    case "AddChangePaymentDate" :
        
        $iPaymentId = $_POST['iPaymentId'];
        $paymentData = array(
            "strPaymentDate" => date('d-m-Y',strtotime($_POST['strUpdatePaymentDate'])),
            "iEntryBy" => $_SESSION['AdminId'],
        );
        $whereData = ' where iPaymentId =' . $iPaymentId;
        $connect->updaterecord($dbconn, 'paymentMaster', $paymentData,$whereData);
        
        $data = array(
            "strPaymentDate" => date('d-m-Y',strtotime($_POST['strUpdatePaymentDate'])),
            "iPaymentBy" => $_SESSION['AdminId']
        );
        $where = ' where  iPaymentId =' . $iPaymentId ;
        $dealer_res = $connect->updaterecord($dbconn, 'multicompany', $data, $where);
         
        echo $statusMsg = $dealer_res ? '1' : '0';
    break;    
    
    
    case "AddChangeCompanyPaymentDate" :
        
        $iCompanyPaymentId = $_POST['iCompanyPaymentId'];
        $paymentData = array(
            "strPaymentDate" => date('d-m-Y',strtotime($_POST['strUpdatePaymentDate'])),
            "iEntryBy" => $_SESSION['AdminId'],
        );
        $whereData = ' where iCompanyPaymentId =' . $iCompanyPaymentId;
        $connect->updaterecord($dbconn, 'companypaymentMaster', $paymentData,$whereData);
        
        $data = array(
            "strPaymentDate" => date('d-m-Y',strtotime($_POST['strUpdatePaymentDate'])),
            "iPaymentBy" => $_SESSION['AdminId']
        );
        $where = ' where iPaymentId =' . $iCompanyPaymentId;
        $dealer_res = $connect->updaterecord($dbconn, 'salarydetails', $data, $where);
            
        echo $statusMsg = $dealer_res ? '1' : '0';
    break;   
    
    default:
# code...
        echo "Page not Found";
        break;
}
?>