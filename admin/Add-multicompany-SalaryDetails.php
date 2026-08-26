<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `companysalarymaster` WHERE `companysalarymasterId`='" . $_REQUEST['token'] . "'");
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
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> | Add Multicompany Salary Details</title>
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
                <div class="page-content">
                    <div class="container">
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Multicompany Salary Details</a>
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
                                                <span class="caption-subject bold uppercase"> Add Multicompany Salary Details</span>
                                            </div>                                          
                                            <div class="f_right">
                                                <a  class="btn blue pull-right"  href="companysalarymaster.php">Back</a>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmserch"  id="frmserch" enctype="multipart/form-data">
                                                <input type="hidden" value="AddSalaryDetails" name="action" id="action">
                                                <input type="hidden" value="<?php echo $_REQUEST['token']; ?>" name="companysalarymasterId" id="companysalarymasterId">
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1"><strong>Salary Month :-</strong></label>
                                                        <?php
                                                        echo $row['month'];
                                                        ?>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1"><strong>Salary Paid Date :-</strong></label>
                                                        <?php
                                                        echo $row['salarypaiddate'];
                                                        ?>
                                                        <!--                                                         <input type="text" id="salarypaiddate" name="salarypaiddate" class="form-control date-picker" placeholder="Enter The Date" required=""/>-->
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1"><strong>Company Name :-</strong></label>
                                                        <?php
                                                        $companymasterId = '';
                                                        $comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname FROM multiycompanysalarymaster  where companysalarymasterId='" . $row['companysalarymasterId'] . "' ");
                                                        while ($commaster = mysqli_fetch_array($comid)) {
                                                            $companymasterId = $commaster['companyname'] . ',' . $companymasterId;
                                                        }
                                                        $companymasterId = rtrim($companymasterId, ", ");
                                                        ?>
                                                        <?php echo $companymasterId; ?> 
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Employee</label><br/>
                                                        <input type="text" id="employeeId" name="employeeId" class="form-control " />
                                                    </div>
                                                    <div class="col-md-4 margin-top-20">
                                                        <a  class="btn blue pull-left" id="clickbutton"  onclick="PageLoadData(1);">Search</a>
                                                    </div>
                                            </form>
                                        </div>
                                        <div id="PlaceUsersDataHere">
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
                                                                window.location.href = '<?php echo $web_url; ?>admin/Add-multicompany-SalaryDetails.php';
                                                            }

                                                            $("#employeeId").keypress(function (event) {
                                                                if (event.keyCode === 13) {
                                                                    $("#clickbutton").click();
                                                                    return false;
                                                                }
                                                            });
                                                            
                                                            function PageLoadData(Page) {
                                                                var employeeId = $('#employeeId').val();
                                                                var companysalarymasterId = $('#companysalarymasterId').val();
                                                                $('#loading').css("display", "block");
                                                                $.ajax({
                                                                    type: "POST",
                                                                    url: "<?php echo $web_url; ?>admin/AjaxAdd-multicompany-SalaryDetails.php",
                                                                    data: {action: 'ListUser', Page: Page, employeeId: employeeId, companysalarymasterId: companysalarymasterId},
                                                                    success: function (msg) {
                                                                        $('#loading').css("display", "none");
                                                                        $("#PlaceUsersDataHere").html(msg);
                                                                    },
                                                                });
                                                            }
                                                            //  PageLoadData(1);
                                                            
                                                            function checkSubmit(empId) {
                                                                var companysalarymasterId = $('#companysalarymasterId').val();
                                                                var workingdays = $('#workingdays_' + empId).val();
                                                                var rate = $('#rate_' + empId).val();
                                                                var othours = $('#othours_' + empId).val();
                                                                var otrate = $('#otrate_' + empId).val();
                                                                var adv = $('#adv_' + empId).val();
                                                                var adv_one = $('#adv_one_paid_' + empId).val();
                                                                var adv_two = $('#adv_two_' + empId).val();
                                                                var adv_two_paid = $('#adv_two_paid_' + empId).val();
                                                                var advance_paid_by_bank = $('#advance_paid_by_bank_' + empId).val();
                                                                var pf_amount = $('#pf_amount_' + empId).val();
                                                                var esic_amount = $('#esic_amount_' + empId).val();
                                                                var pay_cash = $("#pay_cash_" + empId).val();
                                                                var fa = $('#fa_' + empId).val();
                                                                var ta = $('#ta_' + empId).val();
                                                                var date = $('#date_' + empId).val();

                                                                /*var da = $('#da_' + empId).val();
                                                                var hra = $('#hra_' + empId).val();
                                                                var national_holiday_payment = $('#national_holiday_payment_' + empId).val();*/
                                                                var form_data = $('#frmempSalary' + empId).serialize();
                                                                var extraData = "&workingdays_" + empId + "=" + workingdays;
                                                                extraData += "&rate_" + empId + "=" + rate;
                                                                extraData += "&othours_" + empId + "=" + othours;
                                                                extraData += "&otrate_" + empId + "=" + otrate;
                                                                extraData += "&adv_" + empId + "=" + adv;
                                                                extraData += "&pay_cash_" + empId + "=" + pay_cash;
                                                                extraData += "&adv_two_paid_" + empId + "=" + adv_two_paid;
                                                                extraData += "&adv_one_paid_" + empId + "=" + adv_one;
                                                                extraData += "&adv_two_" + empId + "=" + adv_two;
                                                                extraData += "&advance_paid_by_bank_" + empId + "=" + advance_paid_by_bank;
                                                                extraData += "&pf_amount_" + empId + "=" + pf_amount;
                                                                extraData += "&esic_amount_" + empId + "=" + esic_amount;
                                                                
                                                                extraData += "&fa_" + empId + "=" + fa;
                                                                extraData += "&ta_" + empId + "=" + ta;
                                                                extraData += "&date_" + empId + "=" + date;

                                                                /*extraData += "&da_" + empId + "=" + da;
                                                                extraData += "&hra_" + empId + "=" + hra;
                                                                extraData += "&national_holiday_payment_" + empId + "=" + national_holiday_payment;*/
                                                                
                                                                $('#loading').css("display", "block");
                                                                $.ajax({
                                                                    type: 'POST',
                                                                    url: '<?php echo $web_url; ?>admin/querydata.php',
                                                                    data: form_data + extraData,
                                                                    success: function (response) {
                                                                        console.log(response);
//                                                                        alert(response);
                                                                        if (response == 1)
                                                                        {
                                                                            $('#loading').css("display", "none");
                                                                            $("#Btnmybtn").attr('disabled', 'disabled');
                                                                            alert('Added Sucessfully.');
                                                                            window.location.href = '<?php echo $web_url; ?>admin/Add-multicompany-SalaryDetails.php?token=' + companysalarymasterId;
                                                                        } else {
                                                                            $('#loading').css("display", "none");
                                                                            window.location.href = '<?php echo $web_url; ?>admin/Add-multicompany-SalaryDetails.php?token=' + companysalarymasterId;
                                                                        }
                                                                        return false;
                                                                    },
                                                                });
                                                                return false;
                                                            }
        </script>
    </body>
</html>