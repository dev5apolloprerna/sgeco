<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$rightsResult = mysqli_query($dbconn, "SELECT isUserEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
$rights = mysqli_fetch_assoc($rightsResult);
if ($_SESSION['AdminType'] != 1 && (!isset($rights['isUserEntry']) || $rights['isUserEntry'] != 1)) {
    http_response_code(403);
    header('location:'.$web_url.'admin/login.php');	
        exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $ProjectName; ?> | Add User</title>
    <?php include_once 'include.php'; ?>
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include_once './header.php'; ?>
    <div style="display: none; z-index: 10060;" id="loading">
        <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif" alt="Loading">
    </div>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="<?php echo $web_url; ?>admin/index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li><a href="<?php echo $web_url; ?>admin/users.php">List Of Users</a><i class="fa fa-circle"></i></li>
                        <li><span>Add User</span></li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">Add User</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form role="form" method="post" id="frmparameter">
                                            <input type="hidden" value="AddUsers" name="action">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="username">User Name</label>
                                                        <input name="username" id="username" class="form-control" maxlength="100" placeholder="Enter User Name" type="text" required>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="mobileNo">Mobile</label>
                                                        <input name="mobileNo" id="mobileNo" class="form-control" maxlength="10" minlength="10" pattern="[0-9]{10}" placeholder="Enter Mobile Number" type="tel">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="email">Email</label>
                                                        <input name="email" id="email" class="form-control" maxlength="150" placeholder="Enter Email" type="email">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="loginid">Login Id</label>
                                                        <input name="loginid" id="loginid" class="form-control" maxlength="100" placeholder="Enter Login Id" type="text" autocomplete="username" required>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="password">Password</label>
                                                        <input name="password" id="password" class="form-control" minlength="6" placeholder="Enter Password" type="password" autocomplete="new-password" required>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="cpassword">Confirm Password</label>
                                                        <input name="cpassword" id="cpassword" class="form-control" minlength="6" placeholder="Confirm Password" type="password" autocomplete="new-password" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-actions noborder" style="text-align: right;">
                                                <button class="btn blue margin-top-20" type="submit" id="Btnmybtn">Submit</button>
                                                <button type="button" class="btn blue margin-top-20" onclick="checkclose();">Cancel</button>
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
    <script>
        function checkclose() {
            window.location.href = '<?php echo $web_url; ?>admin/users.php';
        }

        $('#frmparameter').submit(function(event) {
            event.preventDefault();
            if ($('#password').val() !== $('#cpassword').val()) {
                alert('Password and Confirm Password must match.');
                return;
            }
            $('#loading').show();
            $('#Btnmybtn').prop('disabled', true);
            $.post('<?php echo $web_url; ?>admin/paymentquerydata.php', $(this).serialize(), function(response) {
                $('#loading').hide();
                if (response == 1) {
                    alert('User Added Successfully.');
                    window.location.href = '<?php echo $web_url; ?>admin/users.php';
                } else {
                    $('#Btnmybtn').prop('disabled', false);
                    if (response == 3) alert('Login Id already exists.');
                    else if (response == 4) alert('Please enter valid user details and matching passwords.');
                    else alert('Invalid Request.');
                }
            });
        });
    </script>
</body>

</html>