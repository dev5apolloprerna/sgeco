<?php
ob_start();
include ('../config.php');
include ('password_hash.php');
$message = "";
if (isset($_POST['btnsubmit']) && isset($_POST['loginId']) && !empty($_POST['loginId'])) {
    $strlogin = "select * from admin where  loginid='" . trim($_POST['loginId']) . "' and  istatus=1 ";

    $result = mysqli_query($dbconn, $strlogin);
    if (mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_array($result);
        $_SESSION['LastLoginAdmin'] = $row['LastLogin'];
        $updatelast = "UPDATE `admin` SET `LastLogin` = '" . date('d-m-y H:i:s') . "' WHERE  `loginid` ='" . trim($_POST['loginId']) . "'";
        mysqli_query($dbconn, $updatelast);
        $good_hash = PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $row['salt'] . ":" . $row['password'];
        if (validate_password($_REQUEST['password'], $good_hash)) {
            $_SESSION['AdminId'] = $row['id'];
            $_SESSION['AdminName'] = $row['username'];
            $_SESSION['AdminType'] = $row['type'];
       
              echo "<script>window.location.href='".$web_url."admin/index.php';</script>";
        } else {
            header('location:'.$web_url.'admin/login.php?flg=1');
            // $message = "UserName or password does't match.";
        }
    } else {
        header('location:'.$web_url.'admin/login.php?flg=2');
        // $message = "Email not Registered";
    }
}
?>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>Login</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />

        <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
        <link href="<?php echo $web_url;?>admin/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        
        <link href="<?php echo $web_url;?>admin/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

        <link href="<?php echo $web_url;?>admin/assets/pages/css/login.css" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" href="favicon.ico" /> 
    </head>


    <body class=" login" style="background: url(assets/images/n1.jpg);background-repeat: no-repeat;background-size: 100%;">
        <div class="menu-toggler sidebar-toggler"></div>
        <!-- END SIDEBAR TOGGLER BUTTON -->
        <!-- BEGIN LOGO -->
        <div class="logo">
            <a href="<?php echo $web_url;?>admin/index.php">
                <img src="<?php echo $web_url;?>admin/assets/images/logo.png" alt="" style="width:400px" /> </a>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN LOGIN -->
        <div class="content">
            <!-- BEGIN LOGIN FORM -->
            <form class="login-form" action="" method="post"  >
                <h3 class="form-title font-blue uppercase">Sign In</h3>
                <!--                <div class="alert alert-danger display-hide">-->
                <!--                    <button class="close" data-close="alert"></button>-->
                <?php
                if (isset($_GET['flg'])) {

                    if ($_GET['flg'] == 1)
                        echo "<div class='alert alert-danger display-hide'><span>Login ID  or password does't match.</span> </div>";
                    else if ($_GET['flg'] == 2)
                        echo '<div class="alert alert-danger display-hide"><span><label>Login ID not Registered.</span> </div>';
                }
                ?> 
                <div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9">Login Id</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="Enter The Login Id." name="loginId" id="loginId" value="" required=""/>
                </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Enter The Password" name="password" id="password" value="" required=""/>
                </div>
                <div class="form-actions">

                    <input type="submit"  name="btnsubmit" id="btnsubmit" class="btn btn-success uppercase col-md-12" value="Login">

                </div>

            </form>
            <!-- END LOGIN FORM -->

        </div>
        <div class="copyright">  </div>
        <!--[if lt IE 9]>
<script src="assets/global/plugins/respond.min.js"></script>
<script src="assets/global/plugins/excanvas.min.js"></script> 
<![endif]-->
        <!-- BEGIN CORE PLUGINS -->

        <script>

            $('.input').keypress(function (e) {
                if (e.which == 13) {
                    $('.login-form').submit();
                    return false;
                } else
                {
                    alert('in else');
                }
            });
        </script>
          <script src="<?php echo $web_url;?>admin/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo $web_url;?>admin/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        
        <script src="<?php echo $web_url;?>admin/assets/global/scripts/app.js" type="text/javascript"></script>
        <script src="<?php echo $web_url;?>admin/assets/pages/scripts/login.js" type="text/javascript"></script>


    </body>

</html>