<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `salarydetails` WHERE `salarydetailsId`='" . $_REQUEST['token'] . "'");
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
        <title> <?php echo $ProjectName ?> |Edit Salary Details</title>
        <?php include_once './include.php'; ?>       
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
                            <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Salary Details</a>
                            <i class="fa fa-circle"></i>
                        </li>

                        <li>
                            <span> Edit Salary Details</span>

                        </li>
                    </ul>

                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">Edit Salary Details</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">

                                        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                            <input type="hidden" value="editSalaryDetails" name="action" id="action">
                                            <input type="hidden" value="<?php echo $row['salarydetailsId'] ?>" name="salarydetailsId" id="salarydetailsId">
                                            <input type="hidden" value="<?php echo $row['emp_id'] ?>" name="emp_id" id="emp_id">
                                            <input name="companyId" id="companyId" value="<?php echo $row['companyId']; ?>" type="hidden">
                                            <input name="salaryId" id="salaryId" value="<?php echo $row['salaryId']; ?>"   type="hidden">
                                            <div class="form-body">

                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Employee Name.</label><br/>
                                                    <?php echo $row['name']; ?>
                                                    <input name="name" id="name" value="<?php echo $row['name']; ?>" readonly="" class="form-control"value="<?php echo $row['name']; ?>" placeholder="Enter Employee Name"  type="hidden">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Salary Month</label>                                                    
                                                    <?php
                                                    $queryCom = "SELECT * FROM `salarymaster`  where salarymasterId='".$row['salaryId']."' and isDelete='0'  and  istatus='1' order by  salarymasterId asc";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                    $rowCom = mysqli_fetch_array($resultCom)
                                                    ?>
                                                    <br/>
                                                    <?php echo $rowCom['month']; ?>
                                                </div>

                                                

                                                <div class="form-group col-md-4">
                                                    <label for="form_control_1">Company</label>
                                                    <?php
                                                    $queryCom = "SELECT * FROM `companymaster`  where companymasterId='".$row['companyId']."' and isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                    $rowCom = mysqli_fetch_array($resultCom);                                                   
                                                    ?>
                                                    <br/> 
                                                     <?php echo $rowCom['companyname']; ?>                                                   
                                                </div>

                                               
                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">Working Days</label>
                                                    <input name="workingdays" id="workingdays" value="<?php echo $row['workingdays']; ?>" onkeypress="return isNumberKey(event)" class="form-control" placeholder="Enter Your Working Days"  type="text">
                                                </div>



                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">Over Time Hours.</label><div id="errordiv"></div>
                                                    <input name="othours" id="othours" value="<?php echo $row['othours']; ?>"  class="form-control" onkeypress="return isNumberKey(event)" placeholder="Enter Your Over Time Hours." type="text" >
                                                </div> 
                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">Over Time Rate.</label>
<!--                                                    <input name="otrate" id="otrate" value="<?php echo $row['otrate']; ?>"  class="form-control" placeholder="Enter Over Time Rate." type="text" >-->
                                                    <select name="otrate" id="otrate"  class="form-control" >
                                                        <option value="0"<?php
                                                        if ($row['otrate'] == '') {
                                                            echo 'selected';
                                                        }
                                                        ?>>Select Employee Over Time Rate</option>
                                                        <option value="1"<?php
                                                        if ($row['otrate'] == '1') {
                                                            echo 'selected';
                                                        }
                                                        ?>>1</option>
                                                        <option value="1.5"<?php
                                                        if ($row['otrate'] == '1.5') {
                                                            echo 'selected';
                                                        }
                                                        ?>>1.5</option>
                                                        <option value="2"<?php
                                                        if ($row['otrate'] == '2') {
                                                            echo 'selected';
                                                        }
                                                        ?>>2</option>
                                                        <!--                        <option value="2.5"<?php
                                                        if ($row['otrate'] == '2.5') {
                                                            echo 'selected';
                                                        }
                                                        ?>>2.5</option>
                                                                                <option value="3"<?php
                                                        if ($row['otrate'] == '3') {
                                                            echo 'selected';
                                                        }
                                                        ?>>3</option>-->
                                                    </select>
                                                </div> 
                                                 <div class="form-group col-md-3">
                                                     
<!--                                                       <input type="hidden" value="<?php //echo $row['skillrate'] ?>" name="skillrate" id="skillrate">-->
                                                    <label for="form_control_1">skill Rate.</label>
                                                <select name="skillrate" id="skillrate"  class="form-control" >
                                                    <?php
                                                    $companymaster = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM companymaster where companymasterId='" . $row['companyId'] . "' "));
                                                    ?>
                                                    <option value="" <?php
                                                    if ( $row['skillrate'] == '') {
                                                        echo 'selected';
                                                    }
                                                    ?>>Select Employee Skill</option>
                                                    <option value="<?php echo $companymaster['highlyskilled'] ?>" <?php
                                                    if ($companymaster['highlyskilled'] == $row['skillrate']) {
                                                        echo 'selected';
                                                    }
                                                    ?>>HighlySkill-<?php echo $companymaster['highlyskilled'] ?></option>

                                                    <option value="<?php echo $companymaster['skil'] ?>" <?php
                                                    if ( $row['skillrate'] == $companymaster['skil']) {
                                                        echo 'selected';
                                                    }
                                                    ?>>Skill-<?php echo $companymaster['skil'] ?></option>
                                                    
                                                    <option value="<?php echo $companymaster['semiskill'] ?>" <?php
                                                    if ( $row['skillrate'] == $companymaster['semiskill']) {
                                                        echo 'selected';
                                                    }
                                                    ?>>SemiSkill -<?php echo $companymaster['semiskill'] ?></option>
                                                    <option value="<?php echo $companymaster['unskill'] ?>" <?php
                                                    if ( $row['skillrate'] == $companymaster['unskill']) {
                                                        echo 'selected';
                                                    }
                                                    ?>>UnSkill -<?php echo $companymaster['unskill'] ?></option>

                                                </select>
                                                 </div>

                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">Deduction if any</label><div id="errordiv"></div>
                                                    <input name="deductionifany" id="deductionifany" value="<?php echo $row['deductionifany']; ?>"  class="form-control" onkeypress="return isNumberKey(event)" placeholder="Enter Your Deduction If Any" type="text" >
                                                </div>


                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">DA</label><div id="errordiv"></div>
                                                    <input name="da" id="da" value="<?= ($row['da']) ? $row['da'] : '0' ?>"  class="form-control" onkeypress="return isNumberKey(event)" placeholder="Enter DA" type="text" >
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">HRA</label><div id="errordiv"></div>
                                                    <input name="hra" id="hra" value="<?= ($row['hra']) ? $row['hra'] : '0' ?>"  class="form-control" onkeypress="return isNumberKey(event)" placeholder="Enter HRA" type="text" >
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">No of National Holiday</label><div id="errordiv"></div>
                                                    <input name="national_holiday_payment" id="national_holiday_payment" value="<?= ($row['iNoOfNatioanHoliday']) ? $row['iNoOfNatioanHoliday'] : '0' ?>"  class="form-control" onkeypress="return isNumberKey(event)" placeholder="Enter National Holiday Payment" type="text" >
                                                </div>
                                                <!-- <div class="form-group col-md-3">
                                                    <label for="form_control_1">Prof  Tax</label><div id="errordiv"></div>
                                                    <input name="pt" id="pt" value="<?php echo $row['pt']; ?>"  class="form-control" onkeypress="return isNumberKey(event)" placeholder="Enter Professional Tax" type="text" >
                                                </div> -->


                                            </div>

                                            <div class="form-actions noborder">
                                                <input class="btn blue margin-top-20 pull-right" style="margin: 0 0 0 15px;" type="submit" id="Btnmybtn"  value="Submit" name="submit">                                                     
                                                <button type="button" class="btn blue margin-top-20  pull-right" onClick="checkclose();">Cancel</button>
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
            window.close();
//            window.location.href = '<?php echo $web_url; ?>admin/salarydetails.php';
        }

        $('#frmparameter').submit(function (e) {

            e.preventDefault();
            var $form = $(this);
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: '<?php echo $web_url; ?>admin/querydata.php',
                data: $('#frmparameter').serialize(),
                success: function (response) {
                  //  alert(response);
                    console.log(response);
                    if (response == 2)
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Employee Edited Sucessfully.');
                        window.close();
//                        window.location.href = '<?php // echo $web_url; ?>admin/salarydetails.php';

                    }
                }

            });
        });
    </script>

</body>
</html>
<SCRIPT language=Javascript>
    <!--
      function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 46 || charCode > 57))
            return false;

        return true;
    }
    //-->
</SCRIPT>