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
        <title><?php echo $ProjectName; ?> | Add Salary Details</title>
        <?php include_once 'include.php'; ?>
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="page-container-bg-solid page-boxed">
        <?php include_once './header.php'; ?>
        <div style="display: none; z-index: 10060;" id="loading">
            <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif">
        </div>
        <div class="page-container">
            <div class="page-content-wrapper">
                <!--                <div class="page-head">
                                    <div class="container">
                                        <div class="page-title">
                                            <h1>Add Company
                                            </h1>
                                        </div>
                                    </div>
                                </div>-->
                <div class="page-content">
                    <div class="container">
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Salary Details</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <span>Add Salary Details</span>
                            </li>
                        </ul>

                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase"> Add Salary Details</span>

                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="AddSalaryDetails" name="action" id="action">
                                                <div class="form-body">

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">salary Month</label>
<!--                                                        <input type="text"  id="salaryId"  name="salaryId" class="form-control date-picker" placeholder="Enter the Company UnSkil" required>-->
                                                        <?php
                                                        $queryCom = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' order by  salarymasterId asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                        echo '<select class="form-control" name="salaryId" id="salaryId" required="">';
                                                        echo "<option value='' >Select salary Month</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['salarymasterId'] . "'>" . $rowCom ['month'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>

                                                    </div>

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Employee.</label>
                                                        <?php
                                                        $queryCom = "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' order by  employeeId asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                        echo '<select class="form-control" name="emp_id" id="emp_id" required="">';
                                                        echo "<option value='' >Select Employee Name</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['employeeId'] . "'>" . $rowCom ['emp_name'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Employee Name.</label>
                                                        <input name="name" id="name"  class="form-control" placeholder="Enter Employee Name"  type="text">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Designation Name.</label>
                                                        <input name="designation" id="designation"  class="form-control" placeholder="Enter Employee Designation"  type="text">
                                                    </div>

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Company</label><br/>

                                                        <?php
                                                        $queryCom = "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                        echo '<select class="form-control" name="companyId" id="companyId" required="">';
                                                        echo "<option value='' >Select company Name</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['companymasterId'] . "'>" . $rowCom ['companyname'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>

                                                    </div>

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Skill</label><br/>
                                                        <select name="Skill" id="Skill"  class="form-control" required="">
                                                            <option value="">Select Employee Skill</option>
                                                            <option value="Skill">Skill</option>
                                                            <option value="UnSkill">UnSkill</option>
                                                            <option value="SemiSkill">SemiSkill</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Working Hours</label>
                                                        <input name="workinghours" id="workinghours"  class="form-control" placeholder="Enter Working Hours"  type="text">
                                                    </div>

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Working Days</label>
                                                        <input name="workingdays" id="workingdays"  class="form-control" placeholder="Enter Your Working Days"  type="text">
                                                    </div>



                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Over Time Hours.</label><div id="errordiv"></div>
                                                        <input name="othours" id="othours"  class="form-control" placeholder="Enter Your Over Time Hours." type="text"  >
                                                    </div> 
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Work Rate</label>
                                                        <input name="workrate" id="workrate"  class="form-control" placeholder="Enter Your Work Rate" type="text" >
                                                    </div> 

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Over Time Rate.</label>
<!--                                                        <input name="otrate" id="otrate"  class="form-control" placeholder="Enter Over Time Rate." type="text" required="">-->
                                                        <select name="otrate" id="otrate"  class="form-control" >
                                                            <option value="">Select Employee Over Time Rate</option>
                                                            <option value="1">1</option>
                                                            <option value="1.5">1.5</option>
                                                            <option value="2">2</option>
                                                            <option value="2.5">2.5</option>
                                                            <option value="3">3</option>
                                                        </select>
                                                    </div> 


                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Salary Amt.</label>
                                                        <input name="salaryamt" id="salaryamt"  class="form-control" placeholder="Enter Salary Amt." type="text" >
                                                    </div> 

                                                </div>

                                                <div class="form-actions noborder">
                                                    <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">      
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
 <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>

        <script type="text/javascript">

            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/salarydetails.php';
            }
            
            
      
               $(document).ready(function() {
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
           $(document).ready(function() {
    $("#workrate").keydown(function (e) {
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
           $(document).ready(function() {
    $("#othours").keydown(function (e) {
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
           $(document).ready(function() {
    $("#workingdays").keydown(function (e) {
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
 $(document).ready(function() {
    $("#workinghours").keydown(function (e) {
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
            $('#frmparameter').submit(function (e) {

         
                    e.preventDefault();
                    var $form = $(this);
                    $('#loading').css("display", "block");
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $web_url; ?>admin/querydata.php',
                        data: $('#frmparameter').serialize(),
                        success: function (response) {
                            //alert(response);
                            console.log(response);
                            if (response == 1)
                            {
                                $('#loading').css("display", "none");
                                $("#Btnmybtn").attr('disabled', 'disabled');
                                alert('Added Sucessfully.');
                                window.location.href = '<?php echo $web_url; ?>admin/salarydetails.php';

                            }
                        }

                    });
              
            });



        </script>
    </body>
</html>