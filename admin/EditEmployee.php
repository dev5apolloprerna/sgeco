<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `employee` WHERE `employeeId`='" . $_REQUEST['token'] . "'");
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
        <title> <?php echo $ProjectName ?> |Edit Employee</title>
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

            <div class="page-content">
                <div class="container">                    
                    <ul class="page-breadcrumb breadcrumb">
                        <li>
                            <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <a href="<?php echo $web_url; ?>admin/Employee.php">List Of Employee</a>
                            <i class="fa fa-circle"></i>
                        </li>

                        <li>
                            <span> Edit Employee</span>

                        </li>
                    </ul>

                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">Edit Employee</span>
                                            
                                        </div>
                                        <a href="<?php echo $web_url; ?>admin/Employee.php" class="btn blue" style="float: right;">Back</a>
                                    </div>
                                    <div class="portlet-body form">

                                        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                            <input type="hidden" value="EditEmployee" name="action" id="action">
                                            <input type="hidden" value="<?php echo $row['employeeId'] ?>" name="employeeId" id="employeeId">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Employee Name</label>
                                                        <input name="emp_name" value="<?php echo $row['emp_name']; ?>" id="emp_name"  class="form-control" placeholder="Enter Your Employee Name" type="text" >
                                                    </div>
                                                    <!--<div class="form-group col-md-4">-->
                                                    <!--    <label for="form_control_1">Other Info</label>-->
                                                    <!--    <input name="emp_other_info" id="emp_other_info" value="<?php echo $row['emp_other_info']; ?>" class="form-control" placeholder="Enter Your Employee Other Information" type="text" required="">-->
                                                    <!--</div>-->
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Father Name</label>
                                                        <input name="strFatherName" value="<?php echo $row['strFatherName']; ?>"  id="strFatherName"  class="form-control" placeholder="Enter Father Name" type="text" >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Designation Name.</label>
    <!--                                                    <input name="designation" value="<?php echo $row['designation']; ?>" id="designation"  class="form-control" placeholder="Enter Employee Designation"  type="text">-->
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
                                                <div class="row">
                                                     <div class="form-group col-md-4">
                                                        <label for="form_control_1">PF Code</label>
                                                        <input name="pfcode" value="<?php echo $row['pfcode']; ?>"  id="pfcode"  class="form-control" placeholder="Enter PF Code" type="text" >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Employee Code</label>
                                                        <input name="employeecode" value="<?php echo $row['employeecode']; ?>"  id="employeecode"  class="form-control" placeholder="Enter Employee Code" type="text" >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">ESIC No</label>
                                                        <input name="ecsno" id="ecsno" value="<?php echo $row['ecsno']; ?>"  class="form-control" placeholder="Enter Your ESIC No" maxlength="10" minlength="10" type="text">
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">UAN</label>
                                                        <input name="uan" value="<?php echo $row['uan']; ?>"  id="uan"  class="form-control" placeholder="Enter UAN" maxlength="12" minlength="12" type="text" >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Date of Birth</label>
                                                        <input name="dateofbirth" value="<?php echo $row['dateofbirth']; ?>"  id="dateofbirth"  class="form-control" placeholder="Enter Date of Birth" type="text" >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Date Of Joining</label>
                                                        <input name="dateofjoining" value="<?php echo $row['dateofjoining']; ?>"  id="dateofjoining"  class="form-control" placeholder="Enter Date Of Joining" type="text" >
                                                    </div>
                                                </div>
                                                
                                                
                                                
                                                <!--<div class="row">
                                                    <div class="form-group col-md-4">
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
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Account No.</label><div id="errordiv"></div>
                                                        <input name="accountno" value="<?php echo $row['accountno']; ?>" id="accountno"  class="form-control" placeholder="Enter Your Account No." type="text" >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">IFSC Code</label>
                                                        <input name="ifsccode" value="<?php echo $row['ifsccode']; ?>" id="ifsccode"  class="form-control" maxlength="11" minlength="11" placeholder="Enter Your IFSC Code" type="text">
                                                    </div> 
                                                </div>-->
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Mobile No.</label>
                                                        <input name="mno" id="mno" value="<?php echo $row['mno']; ?>"   class="form-control" placeholder="Enter Your Mobile No." pattern="[0-9]{10}" maxlength="10" minlength="10" type="text">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Emergency Contact No.</label>
                                                        <input name="strEmergencyContactNo" id="strEmergencyContactNo" value="<?php echo $row['strEmergencyContactNo']; ?>" class="form-control" placeholder="Enter Your Emergency Contact No." pattern="[0-9]{10}" maxlength="10" minlength="10" type="text">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Qualification</label>
                                                        <input name="strQualification" id="strQualification" value="<?php echo $row['strQualification']; ?>"  class="form-control" placeholder="Enter Your Qualification" type="text"  >
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Experience</label>
                                                        <input name="strExperience" id="strExperience" value="<?php echo $row['strExperience']; ?>" class="form-control" placeholder="Enter Your Experience" type="text"  >
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Marital Status</label>
                                                        <select name="strMaritalStatus" id="strMaritalStatus"  class="form-control">
                                                            <option value="">Select Marital Status</option>    
                                                            <option value="Married" <?php if($row['strMaritalStatus'] == "Married") { echo "selected"; } ?> >Married</option>    
                                                            <option value="Unmarried" <?php if($row['strMaritalStatus'] == "Unmarried") { echo "selected"; } ?>>Unmarried</option>    
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-4" style="display:none" id="divMarriedDate">
                                                        <label for="form_control_1">Married Date</label>
                                                        <input name="strMarriedDate" id="strMarriedDate" value="<?php echo $row['strMarriedDate']; ?>" class="form-control" placeholder="Enter Married Date" type="text" >
                                                    </div>
                                                    <div class="form-group col-md-4" style="display:none" id="divSon">
                                                        <label for="form_control_1">Son</label>
                                                        <input name="iSon" id="iSon" value="<?php echo $row['iSon']; ?>" class="form-control" placeholder="Enter Number of Son" type="text" >
                                                    </div>
                                                    <div class="form-group col-md-4" style="display:none" id="divDoughter">
                                                        <label for="form_control_1">Doughter</label>
                                                        <input name="iDoughter" id="iDoughter" value="<?php echo $row['iDoughter']; ?>" class="form-control" placeholder="Enter Number of Doughter" type="text" >
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Nominee Name</label>
                                                        <input name="strNomineeName" id="strNomineeName" value="<?php echo $row['strNomineeName']; ?>" class="form-control" placeholder="Enter Your Nominee Name" type="text" >
                                                    </div>
                                                <!--</div>-->
                                                <!--<div class="row">-->
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Nominee Relation</label>
                                                        <input name="strNomineeRelation" id="strNomineeRelation" value="<?php echo $row['strNomineeRelation']; ?>" class="form-control" placeholder="Enter Your Nominee Relation" type="text">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Nominee Aadhar Card</label>
                                                        <input name="strNomineeAdharNo" id="strNomineeAdharNo" value="<?php echo $row['strNomineeAdharNo']; ?>" class="form-control" placeholder="Enter Your Nominee Aadhar Card" pattern="[0-9]{12}" maxlength="12" minlength="12" type="text">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Present Address.</label>
                                                        <textarea name="address" id="address" class="form-control" placeholder="Enter Your Present Address."  type="text"><?php echo $row['address']; ?></textarea>
                                                    </div>
                                                    <!--<div class="form-group col-md-4">-->
                                                    <!--    <label for="form_control_1">Family Details</label>-->
                                                    <!--    <input name="strFamilyDetails" id="strFamilyDetails" value="<?php echo $row['strFamilyDetails']; ?>" class="form-control" placeholder="Enter Your Family Details" type="text">-->
                                                    <!--</div>-->
                                                <!--</div>-->
                                                <!--<div class="row">-->
                                                    <!--<div class="form-group col-md-4">-->
                                                    <!--    <label for="form_control_1">Relation Name</label>-->
                                                    <!--    <input name="strRelation" id="strRelation" value="<?php echo $row['strRelation']; ?>" class="form-control" placeholder="Enter Your Relation" type="text">-->
                                                    <!--</div>-->
                                                    
                                                    
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Permanent Address.</label>
                                                        <textarea name="strPermanentAddress" id="strPermanentAddress" class="form-control" placeholder="Enter Your Permanent Address."  type="text"><?php echo $row['strPermanentAddress']; ?></textarea>
                                                    </div>
                                                </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Aadhar Card</label>
                                                    <input name="aadharcard"   id="aadharcard" value="<?php echo $row['adharcard']; ?>" class="form-control" placeholder="Enter Aadhar Card Number" pattern="[0-9]{12}" maxlength="12" minlength="12" type="text" >
                                                </div> 
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Upload Aadhar Card</label>
                                                    <input name="aadharImage" id="aadharImage"  class="form-control" type="file" />
                                                </div> 
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Upload Aadhar Card Back</label>
                                                    <input name="voteridImage" id="voteridImage" class="form-control" type="file" />
                                                </div> 
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Pan Card</label>
                                                    <input name="pancard" id="pancard" value="<?php echo $row['pancard']; ?>" class="form-control" placeholder="Enter Pan Card Number" maxlength="10" minlength="10" type="text" >
                                                </div> 
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Upload Pan Card</label>
                                                    <input name="pancardImage" id="pancardImage" class="form-control" type="file" />
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Upload Other Document</label>
                                                    <input name="passportImage" id="passportImage"  class="form-control" type="file"/>
                                                </div>
                                            </div>
                                                <!--<div class="form-group col-md-6">-->
                                                <!--    <label for="form_control_1">Voter Id</label>-->
                                                <!--    <input name="voterid" id="voterid" class="form-control" value="<?php echo $row['electioncard']; ?>" placeholder="Enter Voter Id Number" type="text" >-->
                                                <!--</div> -->
                                            <!--<div class="row">-->
                                                <!--<div class="form-group col-md-4">-->
                                                <!--    <label for="form_control_1">Other Document</label>-->
                                                <!--    <input name="passport" id="passport" class="form-control" value="<?php echo $row['passport']; ?>" placeholder="Enter Other Document Number" type="text" >-->
                                                <!--</div> -->
                                                
                                            <!--</div>-->
                                                <!--<div class="form-group col-md-12">-->
                                                <!--    <label for="form_control_1">Address.</label>-->
                                                <!--    <textarea name="address"  id="address"  class="form-control" placeholder="Enter Your Address."  type="text"><?php echo $row['address']; ?></textarea>-->
                                                <!--</div>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                                <div class="field_wrapperNew">
                                                    <?php $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `EmployeeFamilyDetails` where iEmpId='".$row['employeeId']."'");
                                                        $i = 0;
                                                        if(mysqli_num_rows($filterFamilyRelation) > 0){
                                                            while($rowRelation = mysqli_fetch_assoc($filterFamilyRelation)){ ?>
                                                            
                                                                <div class="row newaddmore<?= $i ?>">
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
                                                            <div class="row newaddmore">
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
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Date Of Exit</label>
                                                        <input name="strExitDate" value="<?php echo $row['strExitDate']; ?>"  id="strExitDate"  class="form-control" placeholder="Enter Date Of Exit" type="text" >
                                                    </div>
                                                </div>
                                            <div class="form-actions noborder" style="text-align: right;">
                                                <input class="btn blue margin-top-20" type="submit" id="Btnmybtn" onclick="return validation();"  value="Save" name="submit">      
                                                <button type="button" class="btn blue margin-top-20" onClick="checkclose();">Cancel</button>
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
            window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
        }

        $(document).ready(function () {
            changeMaritalStatus();
            // $("#salaryamt").keydown(function (e) {
            //     // Allow: backspace, delete, tab, escape, enter and .
            //     if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
            //     // Allow: Ctrl+A, Command+A
            //             (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            //             // Allow: home, end, left, right, down, up
            //                     (e.keyCode >= 35 && e.keyCode <= 40)) {
            //         // let it happen, don't do anything
            //         return;
            //     }
            //     // Ensure that it is a number and stop the keypress
            //     if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            //         e.preventDefault();
            //     }
            // });
        
            $("#iSonAndDoughter").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            
            $("#strEmergencyContactNo").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            
            $("#mno").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            
        });
        
        
        
        $("#strMaritalStatus").change(function (){
            changeMaritalStatus();
        });
        
        function changeMaritalStatus(){
            var strMaritalStatus = $("#strMaritalStatus").val();
            if(strMaritalStatus == "Married"){
                $("#divMarriedDate").show();
                $("#divSon").show();
                $("#divDoughter").show();
            } else {
                $("#divMarriedDate").hide();
                $("#divSon").hide();
                    $("#divDoughter").hide();
            }
        }
        
        $(document).ready(function () {
            
            $("#strMarriedDate").datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
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
            // var ifsccode = $('#ifsccode').val();
            // if (ifsccode != '' && ifsccode.length < 11) {
            //     alert("Please Entry Valid 11 Digit IFSC CODE");
            //     return false;
            // }
            e.preventDefault();
            var formData = new FormData($('form#frmparameter')[0]);
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: '<?php echo $web_url; ?>admin/querydata.php',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function (response) {
                    console.log(response);
                    if (response == 2)
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Employee Edited Sucessfully.');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';

                    } else if (response == 3) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Already Exiest!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 4) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Mobile Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 5) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('ESIC Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 6) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Account Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 7) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('PF Code Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 8) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('UAN Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 9) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Pan Card Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else if (response == 10) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Aadhar Card Number is already exists!');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    } else {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Invalid Request.');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
                    }
                }

            });
        });
    </script>
    <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {

            $("#dateofbirth").datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
            });
            
            $("#strExitDate").datepicker({
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
                        `<div class="row newaddmore` + x + `">
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
