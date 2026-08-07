<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `admin` WHERE `id`='" . $_REQUEST['token'] . "'");
if (mysqli_num_rows($result) == 1) {
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
        <title><?php echo $ProjectName; ?> | Edit User </title>
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
                                <a href="<?php echo $web_url; ?>admin/users.php">List Of Users</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <span>Edit User</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase"> Edit User</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="EditUsers" name="action" id="action">
                                                <input type="hidden" value="<?= $_REQUEST['token']; ?>" name="id" id="id">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">User Name</label>
                                                            <input name="username" id="username" value="<?= $row['username'] ?>" class="form-control" required placeholder="Enter User Name" type="text" required="">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Mobile</label>
                                                            <input name="mobileNo" id="mobileNo" value="<?= $row['mobileNo'] ?>" class="form-control" maxlength="10" minlength="10" placeholder="Enter Your Mobile Number" type="text">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Email</label>
                                                            <input name="email" id="email" value="<?= $row['email'] ?>" class="form-control" placeholder="Enter Email" type="email" >
                                                        </div> 
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Login Id</label>
                                                            <input name="loginid" id="loginid" value="<?= $row['loginid'] ?>" class="form-control" required placeholder="Enter Login Id" type="text" >
                                                        </div> 
                                                    </div>
                                                    
                                                </div>
                                                <div class="form-actions noborder" style="text-align: right;">
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script type="text/javascript">
            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
            }

            $(document).ready(function () {
                $("#mobileNo").keydown(function (e) {
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
            
            $('#frmparameter').submit(function (e) {
                e.preventDefault();
                var formData = new FormData($('form#frmparameter')[0]);
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: 'paymentquerydata.php',
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
                            alert('User Updated Sucessfully.');
                            window.location.href = '<?php echo $web_url; ?>admin/users.php';
                        } else if (response == 3) {
                            $('#loading').css("display", "none");
                           // $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Login Id is already exists!');
                            // window.location.href = '<?php echo $web_url; ?>admin/users.php';
                        } else {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid Request.');
                            window.location.href = '<?php echo $web_url; ?>admin/users.php';
                        }
                    }

                });
            });

        </script>
    </body>
</html>