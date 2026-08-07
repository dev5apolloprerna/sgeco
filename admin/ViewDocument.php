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
        <title><?php echo $ProjectName; ?> | View Employee Document </title>
        <?php include_once './include.php'; ?>
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
                                <span>Employee</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="col-md-12">
                                <div class="portlet light " style="width: 100%;float: left;">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of View Employee Document</span>
                                        </div>
                                        <a href="<?php echo $web_url; ?>admin/Employee.php" class="btn blue" style="float: right;">Back</a>
                                        <!--<a href="<?php echo $web_url; ?>admin/ZipEmployeeDocument.php" class="btn blue" style="float: right;" title="Add Employee">ADD Employee</a>-->
                                    </div>
                                    <input type="hidden" name="employeeId" id="employeeId" value="<?php echo $_REQUEST['token']; ?>">
                                    <div id="PlaceUsersDataHere" style="width: 100%;float: left;">
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

    <style>

        .multiselect
        {
            display: block;
            height: 35px;
            padding: 6px;
            text-align: left !important;
            line-height: 1.42857;
            /* color: #DFDFDF; */
            background-color: #fff;
            background-image: none;
            border: 1px solid #51c6dd !important;
            border-radius: 4px;
            color: #51c6dd;
            font-size: 15px;
            font-weight: normal !important;
            text-transform: lowercase;
        }
    </style>
    <script type="text/javascript">
        function deletedata(task, id)
        {
            var errMsg = '';
            if (task == 'Delete') {
                errMsg = 'Are you sure to delete?';
            }
            if (confirm(errMsg)) {
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxEmployeeDocument.php",
                    data: {action: task, ID: id},
                    success: function (msg) {

                        $('#loading').css("display", "none");
                        window.location.href = '';

                        return false;
                    },
                });
            }
            return false;
        }



        function PageLoadData(Page) {

            var employeeId = $('#employeeId').val();
//            var Type = $('#Type').val();
//            var Search_Txt = $('#Search_Txt').val();
            $('#loading').css("display", "block");
            $.ajax({
                type: "POST",
                url: "<?php echo $web_url; ?>admin/AjaxEmployeeDocument.php",
                data: {action: 'ListUser', Page: Page, employeeId: employeeId},
                success: function (msg) {
                    $('#loading').css("display", "none");
                    $("#PlaceUsersDataHere").html(msg);
                },
            });
        }// end of filter
        PageLoadData(1);

    </script>
</body>
</html>