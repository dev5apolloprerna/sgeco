<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> | Add Temp Employee </title>
        <?php include_once 'include.php'; ?>
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="page-container-bg-solid page-boxed">
        <a href="AddEmployee.php"></a>
        <?php include_once './header.php'; ?>
        <div style="display: none; z-index: 10060;" id="loading">
            <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif">
        </div>
        <div class="page-container">
            <div class="page-content-wrapper">
                <div class="page-content">
                    <div class="container">
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Temp Employee</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <span>Add Temp Employee</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase"> Add Temp Employee</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="AddTempEmployee" name="action" id="action">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Employee Name</label>
                                                            <input name="emp_name" id="emp_name"  class="form-control" placeholder="Enter Your Employee Name" type="text" required="">
                                                        </div>
                                                        <!--<div class="form-group col-md-4">-->
                                                        <!--    <label for="form_control_1">Other Info</label>-->
                                                        <!--    <input name="emp_other_info" id="emp_other_info"  class="form-control" placeholder="Enter Your Employee Other Information" type="text" required="">-->
                                                        <!--</div>-->
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Father Name</label>
                                                            <input name="strFatherName" id="strFatherName"  class="form-control" placeholder="Enter Your Father Name" type="text">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Designation Name.</label>
                                                            <?php
                                                            $queryCom = "SELECT * FROM `designation`  where isDelete='0'  and  istatus='1' order by  designationid asc";
                                                            $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                            echo '<select class="form-control" name="designation" id="designation" required="">';
                                                            echo "<option value='' >Select Designation Name</option>";
                                                            while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                                echo "<option value='" . $rowCom ['designationid'] . "'>" . $rowCom ['designation'] . "</option>";
                                                            }
                                                            echo "</select>";
                                                            ?>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">PF Code</label>
                                                            <input name="pfcode"   id="pfcode"  class="form-control" placeholder="Enter PF Code" type="text" >
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Employee Code</label>
                                                            <input name="employeecode"   id="employeecode"  class="form-control" placeholder="Enter Employee Code" type="text" >
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">ESIC No</label>
                                                            <input name="ecsno" id="ecsno"  class="form-control" placeholder="Enter Your ESIC No" maxlength="10" minlength="10"  type="text">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">UAN</label>
                                                            <input name="uan"   id="uan"  class="form-control" placeholder="Enter UAN" maxlength="12" minlength="12" type="text" >
                                                        </div> 
                                                        
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Date of Birth</label>
                                                            <input name="dateofbirth"   id="dateofbirth"  class="form-control" placeholder="Enter Date of Birth" type="text" >
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Date Of Joining</label>
                                                            <input name="dateofjoining"   id="dateofjoining"  class="form-control" placeholder="Enter Date Of Joining" type="text" >
                                                        </div> 
                                                        
                                                        
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Bank</label><br/>
                                                            <?php
                                                            $queryCom = "SELECT * FROM `bankmaster`  where bankmasterId > 0 and  isDelete='0'  and  istatus='1' order by  bankmasterId asc";
                                                            $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                            echo '<select class="form-control" name="bankid" id="bankid" required="">';
                                                            echo "<option value='' >Select Bank Name</option>";
                                                            while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                                echo "<option value='" . $rowCom ['bankmasterId'] . "'>" . $rowCom ['bankname'] . "</option>";
                                                            }
                                                            echo "</select>";
                                                            ?>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Account No.</label>
                                                            <input name="accountno" id="accountno"  class="form-control" placeholder="Enter Your Account No." type="text"  >
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">IFSC Code</label>
                                                            <input name="ifsccode" id="ifsccode"  class="form-control" placeholder="Enter Your IFSC Code" maxlength="11" minlength="11" type="text">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Mobile No.</label>
                                                            <input name="mno" id="mno"  class="form-control" placeholder="Enter Your Mobile No." pattern="[0-9]{10}" maxlength="10" minlength="10" type="text">
                                                        </div>
                                                        
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Marital Status</label>
                                                            <select name="strMaritalStatus" id="strMaritalStatus"  class="form-control">
                                                                <option value="">Select Marital Status</option>    
                                                                <option value="Married">Married</option>    
                                                                <option value="Unmarried">Unmarried</option>    
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Nominee Name</label>
                                                            <input name="strNomineeName" id="strNomineeName"  class="form-control" placeholder="Enter Your Nominee Name" type="text" >
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Nominee Relation</label>
                                                            <input name="strNomineeRelation" id="strNomineeRelation"  class="form-control" placeholder="Enter Your Nominee Relation" type="text">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Nominee Aadhar Card</label>
                                                            <input name="strNomineeAdharNo" id="strNomineeAdharNo"  class="form-control" placeholder="Enter Your Nominee Aadhar Card" maxlength="12" minlength="12" type="text">
                                                        </div>
                                                        <!--<div class="form-group col-md-4">-->
                                                        <!--    <label for="form_control_1">Family Details</label>-->
                                                        <!--    <input name="strFamilyDetails" id="strFamilyDetails"  class="form-control" placeholder="Enter Your Family Details" type="text">-->
                                                        <!--</div>-->
                                                        <!--<div class="form-group col-md-4">-->
                                                        <!--    <label for="form_control_1">Relation Name</label>-->
                                                        <!--    <input name="strRelation" id="strRelation"  class="form-control" placeholder="Enter Your Relation" type="text">-->
                                                        <!--</div>-->
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Present Address.</label>
                                                            <textarea name="address" id="address" class="form-control" placeholder="Enter Your Present Address."  type="text"></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Permanent Address.</label>
                                                            <textarea name="strPermanentAddress" id="strPermanentAddress" class="form-control" placeholder="Enter Your Permanent Address."  type="text"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Aadhar Card</label>
                                                            <input name="aadharcard"   id="aadharcard"  class="form-control" placeholder="Enter Aadhar Card Number" pattern="[0-9]{12}" maxlength="12" minlength="12" type="text" >
                                                        </div> 
                                                        
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Upload Aadhar Card</label>
                                                            <input name="AadharCardFront" id="AadharCardFront" class="form-control" type="file" />
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Upload Aadhar Card Back</label>
                                                            <input name="AadharCardBack" id="AadharCardBack" class="form-control" type="file" />
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Pan Card</label>
                                                            <input name="pancard" id="pancard"  class="form-control" placeholder="Enter Pan Card Number" maxlength="10" minlength="10" type="text" >
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Upload Pan Card</label>
                                                            <input name="PanCardImage" id="PanCardImage" class="form-control" type="file" />
                                                        </div>
                                                        <!--<div class="form-group col-md-4"></div>-->
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Upload Other Document</label>
                                                            <input name="OtherDocument" id="OtherDocument"  class="form-control" type="file"/>
                                                        </div>
                                                    </div>
                                                    <!--<div class="row">-->
                                                        <!--<div class="form-group col-md-6">-->
                                                        <!--    <label for="form_control_1">Voter Id</label>-->
                                                        <!--    <input name="voterid" id="voterid" class="form-control" placeholder="Enter Voter Id Number" type="text" >-->
                                                        <!--</div> -->
                                                        
                                                        <!--<div class="form-group col-md-4">-->
                                                        <!--    <label for="form_control_1"> Other Document</label>-->
                                                        <!--    <input name="passport" id="passport" class="form-control" placeholder="Enter Other Document Number" type="text" >-->
                                                        <!--</div> -->
                                                    <!--    <div class="form-group col-md-4">-->
                                                    <!--        <label for="form_control_1">Upload Other Document</label>-->
                                                    <!--        <input name="passportImage" id="passportImage"  class="form-control" type="file"/>-->
                                                    <!--    </div>-->
                                                    <!--    <div class="form-group col-md-4"></div>-->
                                                    <!--</div>-->
                                                    <div class="field_wrapperNew">
                                                        <div class="row  newaddmore">
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
                                                    </div>
                                                </div>
                                                <div class="form-actions noborder" style="text-align: right;">
                                                    <input class="btn blue margin-top-20" type="submit" id="Btnmybtn" onclick="return validation();"  value="Submit" name="submit">      
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script type="text/javascript">
            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
            }

            $(document).ready(function () {
                $("#salaryamt").keydown(function (e) {
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
                    url: 'querydata.php',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (response) {
                        console.log(response);
                        if (response == 1)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Temp Employee Added Sucessfully.');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';

                        } else if (response == 3) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Already Exiest!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 4) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Mobile Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 5) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('ESIC Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 6) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Account Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 7) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('PF Code Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 8) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('UAN Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 9) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Pan Card Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else if (response == 10) {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Aadhar Card Number is already exists!');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        } else {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid Request.');
                            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                        }
                    }

                });
                //}
                //return false;
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
            var maxField = 10; //Input fields increment limitation
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
//            $(document).ready(function ()
//            {
//                $("#gallery").on('change', function ()
//                {
//                    var galeryID = 0;
//                    galeryID = galeryID + 1;
//                    $("#galeryID").val(galeryID);
//                    $("#ImageGallery").html('<img src="<?php echo $web_url; ?>admin/images/input-spinner.gif" alt="Uploading...."/>');
//                    var formData = new FormData($('form#frmparameter')[0]);
//                    $.ajax({
//                        type: "POST",
//                        url: "uploadDocumentImg.php",
//                        processData: false,
//                        contentType: false,
//                        data: formData,
//                        success: function (msg) {
//                            alert(msg);
////                            $("#ImageGallery").show();
////                            $("#ImageGallery").html(msg);
//                        },
//                    });
//                    // $("#addTreatmentForm").attr('action', '');
//                });
//            });
//            
//            $(document).ready(function ()
//            {
//                $("#galerypancardID").on('change', function ()
//                {
//                    var galeryID = 0;
//                    galeryID = galeryID + 1;
//                    $("#galerypancardID").val(galeryID);
//                    $("#ImageGallery").html('<img src="<?php echo $web_url; ?>admin/images/input-spinner.gif" alt="Uploading...."/>');
//                    var formData = new FormData($('form#frmparameter')[0]);
//                    $.ajax({
//                        type: "POST",
//                        url: "uploadPancardImg.php",
//                        processData: false,
//                        contentType: false,
//                        data: formData,
//                        success: function (msg) {
//                            alert(msg);
//                            $("#ImageGallery").show();
//                            $("#ImageGallery").html(msg);
//                        },
//                    });
//                    // $("#addTreatmentForm").attr('action', '');
//                });
//            });
        </script>
    </body>
</html>