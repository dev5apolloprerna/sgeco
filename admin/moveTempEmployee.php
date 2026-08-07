<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `tempEmpolyeeMaster` WHERE `iTempEmpId`='" . $_REQUEST['token'] . "'");
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
} else {
    echo 'somthig going worng! try again';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">

        <link rel="shortcut icon" href="images/favicon.png">
        <title> <?php echo $ProjectName ?> |Move Employee</title>
        <?php include_once './include.php'; ?>   
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
    </head>

    <body class="page-container-bg-solid page-boxed">
        <?php
        include('header.php');
        ?>
        <div style="display: none; z-index: 10060;" id="loading">
            <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif">
        </div>
        <div class="page-container">        
            <?php
            include('tempEmployeeTabMenu.php');
            ?>  
            <div class="page-content">
                <div class="container">                    
                    <ul class="page-breadcrumb breadcrumb">
                        <li>
                            <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <a href="<?php echo $web_url; ?>admin/TempEmployeeList.php">List Of Temp Employee</a>
                            <i class="fa fa-circle"></i>
                        </li>

                        <li>
                            <span> Move Employee</span>

                        </li>
                    </ul>

                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">Move Employee</span>
                                        </div>
                                        <a href="<?php echo $web_url; ?>admin/TempEmployeeList.php" class="btn blue" style="float: right;">Back</a>
                                    </div>
                                    <div class="portlet-body form">

                                        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                            <input type="hidden" value="MoveEmployee" name="action" id="action">
                                            <input type="hidden" value="<?php echo $row['iTempEmpId'] ?>" name="iTempEmpId" id="iTempEmpId">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <?php $filterDocOne = "SELECT *,(select document.strDocumentName from document where temempdocument.iDocumentId=document.iDocumentId) as strDocumentName FROM `temempdocument` where iTempEmpId='".$row['iTempEmpId']."' and iDocumentId=1"; 
                                                            $rowDocOne = mysqli_fetch_assoc(mysqli_query($dbconn,$filterDocOne));
                                                            if(isset($rowDocOne['strDocumentImage']) && $rowDocOne['strDocumentImage'] != ""){
                                                                if(file_exists("TempDocument/".$row['iTempEmpId'].'/'.$rowDocOne['strDocumentImage'])){
                                                        ?>
                                                        <div class="col-md-3">
                                                            <a href="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocOne['strDocumentImage'] ?>" target="_blank">
                                                                <img src="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocOne['strDocumentImage'] ?>"  alt="" height="300px" width="100%" style="object-fit: contain;"/>
                                                            </a>
                                                        </div>
                                                        <?php 
                                                                }
                                                            }
                                                            $filterDocThree = "SELECT *,(select document.strDocumentName from document where temempdocument.iDocumentId=document.iDocumentId) as strDocumentName FROM `temempdocument` where iTempEmpId='".$row['iTempEmpId']."' and iDocumentId=3"; 
                                                            $rowDocThree = mysqli_fetch_assoc(mysqli_query($dbconn,$filterDocThree));
                                                            if(isset($rowDocThree['strDocumentImage']) && $rowDocThree['strDocumentImage'] != ""){
                                                                if(file_exists("TempDocument/".$row['iTempEmpId'].'/'.$rowDocThree['strDocumentImage'])){
                                                        ?>
                                                        <div class="col-md-3">
                                                            <a href="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocThree['strDocumentImage'] ?>" target="_blank">
                                                                <img src="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocThree['strDocumentImage'] ?>"  alt="" height="300px" width="100%" style="object-fit: contain;"/>
                                                            </a>
                                                        </div>
                                                        <?php 
                                                                }
                                                            }
                                                            $filterDocTwo = "SELECT *,(select document.strDocumentName from document where temempdocument.iDocumentId=document.iDocumentId) as strDocumentName FROM `temempdocument` where iTempEmpId='".$row['iTempEmpId']."' and iDocumentId=2"; 
                                                            $rowDocTwo = mysqli_fetch_assoc(mysqli_query($dbconn,$filterDocTwo));
                                                            if(isset($rowDocTwo['strDocumentImage']) && $rowDocTwo['strDocumentImage'] != ""){
                                                                if(file_exists("TempDocument/".$row['iTempEmpId'].'/'.$rowDocTwo['strDocumentImage'])){
                                                        ?>
                                                        <div class="col-md-3">
                                                            <a href="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocTwo['strDocumentImage'] ?>" target="_blank">
                                                                <img src="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocTwo['strDocumentImage'] ?>"  alt="" height="300px" width="100%" style="object-fit: contain;"/>
                                                            </a>
                                                        </div>
                                                        <?php }
                                                        } 
                                                        $filterDocTwo = "SELECT *,(select document.strDocumentName from document where temempdocument.iDocumentId=document.iDocumentId) as strDocumentName FROM `temempdocument` where iTempEmpId='".$row['iTempEmpId']."' and iDocumentId=4"; 
                                                        $rowDocTwo = mysqli_fetch_assoc(mysqli_query($dbconn,$filterDocTwo));
                                                        if(isset($rowDocTwo['strDocumentImage']) && $rowDocTwo['strDocumentImage'] != ""){
                                                            if(file_exists("TempDocument/".$row['iTempEmpId'].'/'.$rowDocTwo['strDocumentImage'])){
                                                        ?>
                                                            <div class="col-md-3">
                                                                <a href="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocTwo['strDocumentImage'] ?>" target="_blank">
                                                                    <img src="<?php echo $web_url."admin/TempDocument/".$row['iTempEmpId'].'/'.$rowDocTwo['strDocumentImage'] ?>"  alt="" height="300px" width="100%" style="object-fit: contain;"/>
                                                                </a>
                                                            </div>
                                                        <?php }
                                                        } 
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Employee Name</label>
                                                                <input name="emp_name" value="<?php echo $row['emp_name']; ?>" id="emp_name"  class="form-control" placeholder="Enter Your Employee Name" type="text" >
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Father Name</label>
                                                                <input name="strFatherName" value="<?php echo $row['strFatherName']; ?>" id="strFatherName"  class="form-control" placeholder="Enter Your Father Name" type="text" >
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Designation Name.</label>
                                                                <?php
                                                                $queryCom = "SELECT * FROM `designation`  where isDelete='0'  and  istatus='1' order by  designationid asc";
                                                                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                                echo '<select class="form-control" name="designation" id="designation" >';
                                                                echo "<option value='' >Select Designation Name</option>";
                                                                while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                                    if ($row['designationid'] == $rowCom ['designationid']) {
        
                                                                        echo "<option value='" . $rowCom ['designationid'] . "' selected>" . $rowCom ['designation'] . "</option>";
                                                                    } else {
                                                                        echo "<option value='" . $rowCom ['designationid'] . "'>" . $rowCom ['designation'] . "</option>";
                                                                    }
                                                                }
                                                                echo "</select>";
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">PF Code</label>
                                                                <input name="pfcode" value="<?php echo $row['pfcode']; ?>"  id="pfcode"  class="form-control" placeholder="Enter PF Code" type="text" >
                                                            </div>  
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Employee Code</label>
                                                                <input name="employeecode" value="<?php echo $row['employeecode']; ?>"  id="employeecode"  class="form-control" placeholder="Enter Employee Code" type="text" >
                                                            </div> 
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">ESIC No</label>
                                                                <input name="ecsno" id="ecsno" value="<?php echo $row['ecsno']; ?>"  class="form-control" maxlength="10" minlength="10" placeholder="Enter Your ESIC No"  type="text">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">UAN</label>
                                                                <input name="uan" value="<?php echo $row['uan']; ?>"  id="uan"  class="form-control" placeholder="Enter UAN" maxlength="12" minlength="12" type="text" >
                                                            </div> 
                                                        </div>
                                                        
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Date of Birth</label>
                                                                <input name="dateofbirth" value="<?php echo date('d-m-Y',strtotime($row['dateofbirth'])); ?>"  id="dateofbirth"  class="form-control" placeholder="Enter Date of Birth" type="text" >
                                                            </div> 
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Date Of Joining</label>
                                                                <input name="dateofjoining" value="<?php echo date('d-m-Y',strtotime($row['dateofjoining'])); ?>"  id="dateofjoining"  class="form-control" placeholder="Enter Date Of Joining" type="text" >
                                                            </div>   
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Permanent Address.</label>
                                                                <textarea name="strPermanentAddress"  id="strPermanentAddress"  class="form-control" placeholder="Enter Your Permanent Address."  type="text"><?php echo $row['strPermanentAddress']; ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Present Address.</label>
                                                                <textarea name="address"  id="address"  class="form-control" placeholder="Enter Present Your Address."  type="text"><?php echo $row['address']; ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Aadhar Card</label>
                                                                <input name="aadharcard"   id="aadharcard" value="<?php echo $row['adharcard']; ?>" maxlength="12" minlength="12" pattern="[0-9]{12}" class="form-control" placeholder="Enter Aadhar Card Number" type="text" >
                                                            </div> 
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Pan Card</label>
                                                                <input name="pancard" id="pancard" maxlength="10" minlength="10" value="<?php echo $row['pancard']; ?>" class="form-control" placeholder="Enter Pan Card Number" type="text" >
                                                            </div>
                                                        </div>
                                                        <!--<div class="col-md-4">-->
                                                        <!--    <div class="form-group">-->
                                                        <!--        <label for="form_control_1">Other Document</label>-->
                                                        <!--        <input name="passport" id="passport" class="form-control" value="<?php echo $row['passport']; ?>" placeholder="Enter Other Document Number" type="text" >-->
                                                        <!--    </div> -->
                                                        <!--</div>-->
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Bank</label><br/>
                                                                <?php
                                                                $queryCom = "SELECT * FROM `bankmaster`  where bankmasterId > 0 and isDelete='0'  and  istatus='1' order by  bankmasterId asc";
                                                                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                                echo '<select class="form-control" name="bankid" id="bankid" >';
                                                                echo "<option value='' >Select Bank Name</option>";
                                                                while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                                    if ($row['bankid'] == $rowCom ['bankmasterId']) {
                                                                        echo "<option value='" . $rowCom ['bankmasterId'] . "' selected>" . $rowCom ['bankname'] . "</option>";
                                                                    } else {
                                                                        echo "<option value='" . $rowCom ['bankmasterId'] . "'>" . $rowCom ['bankname'] . "</option>";
                                                                    }
                                                                }
                                                                echo "</select>";
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Account No.</label><div id="errordiv"></div>
                                                                <input name="accountno" value="<?php echo $row['accountno']; ?>" id="accountno"  class="form-control" placeholder="Enter Your Account No." type="text" >
                                                            </div> 
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">IFSC Code</label>
                                                                <input name="ifsccode" value="<?php echo $row['ifsccode']; ?>" id="ifsccode"  class="form-control" placeholder="Enter Your IFSC Code" maxlength="11" minlength="11" type="text">
                                                            </div> 
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Mobile No.</label>
                                                                <input name="mno" id="mno" value="<?php echo $row['mno']; ?>"   class="form-control" placeholder="Enter Your Mobile No." pattern="[0-9]{10}" maxlength="10" minlength="10" type="text">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Marital Status</label>
                                                                <select name="strMaritalStatus" id="strMaritalStatus"  class="form-control">
                                                                    <option value="">Select Marital Status</option>    
                                                                    <option value="Married" <?php if($row['strMaritalStatus'] == "Married") { echo "selected"; } ?> >Married</option>    
                                                                <option value="Unmarried" <?php if($row['strMaritalStatus'] == "Unmarried") { echo "selected"; } ?>>Unmarried</option>    
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Nominee Name</label>
                                                                <input name="strNomineeName" id="strNomineeName" value="<?php echo $row['strNomineeName']; ?>" class="form-control" placeholder="Enter Your Nominee Name" type="text" >
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Nominee Relation</label>
                                                                <input name="strNomineeRelation" id="strNomineeRelation" value="<?php echo $row['strNomineeRelation']; ?>" class="form-control" placeholder="Enter Your Nominee Relation" type="text">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="form_control_1">Nominee Aadhar Card</label>
                                                                <input name="strNomineeAdharNo" id="strNomineeAdharNo" value="<?php echo $row['strNomineeAdharNo']; ?>"  class="form-control" placeholder="Enter Your Nominee Aadhar Card" maxlength="12" minlength="12" type="text">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="field_wrapperNew">
                                                        <?php $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `tempFamilyDetails` where iTempEmpId='".$row['iTempEmpId']."'");
                                                            $i = 0;
                                                            if(mysqli_num_rows($filterFamilyRelation) > 0){
                                                                while($rowRelation = mysqli_fetch_assoc($filterFamilyRelation)){ ?>
                                                                
                                                                    <div class="col-md-12 newaddmore<?= $i ?>">
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label for="form_control_1">Family Details</label>
                                                                                <input name="strFamilyDetails[]" id="strFamilyDetails" value="<?php echo $rowRelation['strFamilyDetails']; ?>"  class="form-control" placeholder="Enter Your Family Details" type="text">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label for="form_control_1">Relation Name</label>
                                                                                <select name="strRelation[]" id="strRelation" class="form-control" placeholder="Enter Your Relation" type="text">
                                                                                    <option  value="">Select Relation</option>
                                                                                    <?php $filterStr = mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0");
                                                                                        while($rowData = mysqli_fetch_assoc($filterStr)){?>
                                                                                        <option  value="<?= $rowData['iRelation']; ?>" <?php if($rowRelation['iRelation']==$rowData['iRelation']) { echo "selected"; } ?>><?= $rowData['strRelation']; ?></option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <?php if($i == 0){ ?>
                                                                        <div class="col-md-4">
                                                                            <div class="form-group d-flex">
                                                                                <label for="form_control_1"></label>
                                                                                <a href="javascript:void(0);" class="btn btn-success add_button_new add-more" style="margin: 25px 0px 0px 0px;" title="Add Option"><i class="fa fa-plus-circle lh-normal"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <?php } else { ?>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group d-flex">
                                                                                    <label for="form_control_1"></label>
                                                                                    <a href="javascript:void(0);" class="btn btn-danger remove_buttonNew add-more" style="margin: 25px 0px 0px 0px;" title="Remove Option"><i class="fa fa-minus-circle lh-normal"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        <?php } ?>
                                                                    </div>     
                                                                <?php $i++; } ?>
                                                                
                                                            <?php } else { ?>
                                                                <div class="col-md-12 newaddmore">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="form_control_1">Family Details</label>
                                                                            <input name="strFamilyDetails[]" id="strFamilyDetails" value="<?php echo $row['strFamilyDetails']; ?>"  class="form-control" placeholder="Enter Your Family Details" type="text">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="form_control_1">Relation Name</label>
                                                                            <select name="strRelation[]" id="strRelation" class="form-control" placeholder="Enter Your Relation" type="text">
                                                                                <option  value="">Select Relation</option>
                                                                                <?php $filterStr = mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0");
                                                                                    while($rowData = mysqli_fetch_assoc($filterStr)){?>
                                                                                    <option  value="<?= $rowData['iRelation']; ?>" <?php if($row['strRelation']==$rowData['iRelation']) { echo "selected"; } ?>><?= $rowData['strRelation']; ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group d-flex">
                                                                            <label for="form_control_1"></label>
                                                                            <a href="javascript:void(0);" class="btn btn-success add_button_new add-more" style="margin: 25px 0px 0px 0px;" title="Add Option"><i class="fa fa-plus-circle lh-normal"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>        
                                                            <?php } ?>
                                                            <input type="hidden" id="familyCount" value="<?= $i;?>">
                                                    </div>
                                                    
                                                </div>
                                        </div>
                                            <div class="form-actions noborder" style="text-align: end;">
                                                <button type="button" class="btn blue margin-top-20" onClick="checkclose();" style="float: left; background: #f1f1f1; border-color: #dddddd; color: black;">Cancel</button>
                                                <input class="btn blue margin-top-20" type="button" id="Btnmybtn" onclick="tempEmployeeEdit();" value="Save" name="submit">
                                                <input class="btn blue margin-top-20" type="submit" id="Btnmybtn" onclick="return validation();" value="Save & Move" name="submit">
                                                
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
    <?php include_once './footer.php'; ?>
    <script type="text/javascript">
        function checkclose() {
            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
        }

        $(document).ready(function () {
            $("#salaryamt").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                    // Allow: Ctrl+A, Command+A
                            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                            // Allow: home, end, left, right, down, up
                                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                        // let it happen, don't do anything
                        return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
        });
        
        function validation()
        {   
            //var regexp=/^[2-9]{1}[0-9]{3}\s{1}[0-9]{4}\s{1}[0-9]{4}$/;
            
            var aadhaar=document.getElementById("aadharcard").value;
            var retFlag=true;
            if(aadhaar != ""){
                var expr = /^([0-9]{4}[0-9]{4}[0-9]{4}$)|([0-9]{4}\s[0-9]{4}\s[0-9]{4}$)|([0-9]{4}-[0-9]{4}-[0-9]{4}$)/;
                if (!expr.test(aadhaar)) {
                    alert("Invalid Aadhaar Number.");
                    retFlag = false;
                } else {
                    retFlag = true;
                }
            }
            // var voterid=document.getElementById("voterid").value;
            // if(voterid != ""){
            //     var EPICregex = new RegExp(/^[A-Z]{3}[0-9]{7}$/);
            //     if (!EPICregex.test(voterid)) {
            //         alert("Invalid Voter Number.");
            //         retFlag = false;
            //     } else {
            //         retFlag = true;
            //     }
            // }
            
            var txtPANCard = document.getElementById("pancard").value;
            if(txtPANCard != ""){
                var regex = /([A-Z]){5}([0-9]){4}([A-Z]){1}$/;
                if (regex.test(txtPANCard.toUpperCase())) {
                    retFlag = true;
                } else {
                    alert("Invalid PAN Number.");
                    retFlag = false;
                }
            }
            
            return retFlag;
       }

        $('#frmparameter').submit(function (e) {
            var ifsccode = $('#ifsccode').val();
            if (ifsccode != '' && ifsccode.length < 11) {
                alert("Please Entry Valid 11 Digit IFSC CODE");
                return false;
            }
            e.preventDefault();
            var formData = new FormData($('form#frmparameter')[0]);
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: '<?php echo $web_url; ?>admin/android_querydata.php',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);
                    // if (response == 1)
                    // {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Employee Moved Sucessfully.');
                    window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                    // } else {
                    //     $('#loading').css("display", "none");
                    //     $("#Btnmybtn").attr('disabled', 'disabled');
                    //     alert('Invalid Request.');
                    //     window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                    // }
                }

            });
        });
        
        function tempEmployeeEdit(){
            var ifsccode = $('#ifsccode').val();
            if (ifsccode != '' && ifsccode.length < 11) {
                alert("Please Entry Valid 11 Digit IFSC CODE");
                return false;
            }
            
            var formData = new FormData($('form#frmparameter')[0]);
            formData.append('action',  'EditTempEmployee');
            //alert(JSON.parse(formData));
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: '<?php echo $web_url; ?>admin/android_querydata.php',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);
                    if(response == 2){
                        alert('Employee Edited Sucessfully.');   
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                    } else if(response == 3){
                        $("#pfcode").focus();
                        alert('PF Code Number is already exists.');
                        $('#loading').css("display", "none");
                    } else if(response == 4){
                        $("#ecsno").focus();
                        alert('ESIC Number is already exists.');
                        $('#loading').css("display", "none");
                    } else if(response == 5){
                        $("#uan").focus();
                        alert('UAN Number is already exists.');
                        $('#loading').css("display", "none");
                    } else if(response == 6){
                        $("#accountno").focus();
                        alert('Account Number is already exists.');
                        $('#loading').css("display", "none");
                    } else if(response == 7){
                        $("#mno").focus();
                        alert('Mobile number is already exists.');
                        $('#loading').css("display", "none");
                    } else if(response == 8){
                        $("#pancard").focus();
                        alert('Pancard Number is already exists.');
                        $('#loading').css("display", "none");
                    } else if(response == 9){
                        $("#aadharcard").focus();
                        alert('Aadhar Card Number is already exists.');
                        $('#loading').css("display", "none");
                    } else {
                        alert('invalid Request.');
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                    }
                    
                }

            });
        }
    </script>
    <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
    
    <script>
        $(document).ready(function () {

            $("#dateofbirth").datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
            });
        });
        $(document).ready(function () {

            $("#dateofjoining").datepicker({
                //format: 'dd-mm-yyyy',
                format: 'M-yyyy',
                autoclose: true,
                todayHighlight: true,
                defaultDate: "now",
            });

        });
        $(document).ready(function() {
            var familyCount = $("#familyCount").val();
            var maxField = 10 - (familyCount * 1) + 1; //Input fields increment limitation
            var addButton = $('.add_button_new'); //Add button selector
            var wrapper = $('.field_wrapperNew'); //Input field wrapper
    
            var x = 1; //Initial field counter is 1
    
            //Once add button is clicked
            $(addButton).click(function() {
                //Check maximum number of input fields
                if (x < maxField) {
                    var fieldHTML =
                        `<div class="col-md-12 newaddmore` + x + `">
                            <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_control_1">Family Details</label>
                                <input name="strFamilyDetails[]" id="strFamilyDetails"  class="form-control" placeholder="Enter Your Family Details" type="text">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="form_control_1">Relation Name</label>
                                <select name="strRelation[]" id="strRelation" class="form-control" placeholder="Enter Your Relation" type="text">
                                    <option  value="">Select Relation</option>`;
                                    <?php 
                                    $filterStr = mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0");
                                    while($rowData = mysqli_fetch_assoc($filterStr)){?>
                                        fieldHTML +=`<option  value="<?= $rowData['iRelation']; ?>"><?= $rowData['strRelation']; ?></option>`;
                                    <?php } ?>
                                    // <option  value="1">Father</option>
                                    // <option  value="2">Mother</option>
                                    // <option  value="3">Son</option>
                                    // <option  value="4">Daughter</option>
                                    // <option  value="5">Brother</option>
                                    // <option  value="6">Sister</option>
                                    // <option  value="7">Grandfather</option>
                                    // <option  value="8">Grandmother</option>
                                    // <option  value="9">Husband</option>
                                    // <option  value="10">Wife</option>
                                fieldHTML +=`</select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group d-flex">
                                <label for="form_control_1"></label>
                                <a href="javascript:void(0);" class="btn btn-danger remove_buttonNew add-more" style="margin: 25px 0px 0px 0px;" title="Remove Option"><i class="fa fa-minus-circle lh-normal"></i>
                                </a>
                            </div>
                        </div>
                        </div>`; //New input field html
                    x++; //Increment field counter
                    $(wrapper).append(fieldHTML); //Add field html
                }
            });
    
            //Once remove button is clicked
            $(wrapper).on('click', '.remove_buttonNew', function(e) {
                e.preventDefault();
                //$(this).parent('div').remove(); //Remove field html
                
                $(".newaddmore" + (x - 1)).remove(); //Remove field html
                x--; //Decrement field counter
            });
        });
    </script>

</body>
</html>
