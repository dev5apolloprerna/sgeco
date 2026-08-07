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
        <title> <?php echo $ProjectName ?> | Upload Temp Employee Documents</title>
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
                            <span> Upload Temp Employee Documents </span>

                        </li>
                    </ul>

                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">Upload Temp Employee Documents</span>
                                        </div>
                                        <a href="<?php echo $web_url; ?>admin/TempEmployeeList.php" class="btn blue" style="float: right;">Back</a>
                                    </div>
                                    <div class="portlet-body form">

                                        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                            <input type="hidden" value="UploadTempEmployeeDocuments" name="action" id="action">
                                            <input type="hidden" value="<?php echo $row['iTempEmpId'] ?>" name="iTempEmpId" id="iTempEmpId">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group col-md-3">
                                                            <label for="form_control_1">Aadhar Card Front</label><div id="errordiv"></div>
                                                            <input name="AadharCardFront"  id="AadharCardFront" class="form-control" type="file" accept="image/png, image/gif, image/jpeg">
                                                            <input type="hidden" name="AadharCardFrontDocId" id="AadharCardFrontDocId" value="1">
                                                        </div> 
                                                        <div class="form-group col-md-3">
                                                            <label for="form_control_1">Aadhar Card Back</label>
                                                            <input name="AadharCardBack"  id="AadharCardBack" class="form-control" type="file" accept="image/png, image/gif, image/jpeg">
                                                            <input type="hidden" name="AadharCardBackDocId" id="AadharCardBackDocId" value="2">
                                                        </div> 
                                                        <div class="form-group col-md-3">
                                                            <label for="form_control_1">Pan Card</label>
                                                            <input name="PanCard" id="PanCard" class="form-control" type="file" accept="image/png, image/gif, image/jpeg">
                                                            <input type="hidden" name="PanCardDocId" id="PanCardDocId" value="3">
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="form_control_1">Other Document (Passbook Homepage)</label>
                                                            <input name="OtherDocument" id="OtherDocument" class="form-control" type="file" accept="image/png, image/gif, image/jpeg">
                                                            <input type="hidden" name="OtherDocumentDocId" id="OtherDocumentDocId" value="4">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-actions noborder" style="text-align: end;">
                                                <input class="btn blue margin-top-20" type="submit" id="Btnmybtn" value="Upload" name="submit">
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
            window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
        }

        $('#frmparameter').submit(function (e) {
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
                    //window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                    window.location.href = '';
                    // } else {
                    //     $('#loading').css("display", "none");
                    //     $("#Btnmybtn").attr('disabled', 'disabled');
                    //     alert('Invalid Request.');
                    //     window.location.href = '<?php echo $web_url; ?>admin/TempEmployeeList.php';
                    // }
                }

            });
        });
        
    </script>
    <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {

            $("#dateofbirth").datepicker({
                format: 'dd-m-yyyy',
                autoclose: true,
                todayHighlight: true,
            });
        });
        $(document).ready(function () {

            $("#dateofjoining").datepicker({
                format: 'dd-m-yyyy',
                autoclose: true,
                todayHighlight: true,
                defaultDate: "now",
            });

        });
    </script>

</body>
</html>
