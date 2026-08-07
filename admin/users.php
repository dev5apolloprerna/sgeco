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
        <title><?php echo $ProjectName; ?> | Users </title>
        <?php include_once './include.php'; ?>
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
                                <span>Users</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">

                            <div class="col-md-12">
                                <div class="portlet light " style="width: 100%;float: left;">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Users</span>
                                        </div>
                                        <a href="<?php echo $web_url; ?>admin/AddUser.php" class="btn blue" style="float: right;" title="Add Employee">ADD User</a>
                                    </div>
                                    <div class="portlet-body form">

                                        <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                            <div class="form-group col-md-2">
                                                <input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" placeholder="Search User Name " onkeyup="PageLoadData(1);" required/>
                                            </div>
                                            <div class="form-actions  col-md-2">
                                                <a href="#" class="btn blue pull-left" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                <!--<a href="#" onclick="checkb4submit();" class="btn green  margin-bottom-20"><i class="fa fa-file-excel-o"></i></a>-->
                                            </div>
                                        </form>
                                    </div>
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
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
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
                        url: "<?php echo $web_url; ?>admin/AjaxUsers.php",
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


    $("#frmSearch").keypress(function(event) { 
        if (event.keyCode === 13) { 
            $("#clickbutton").click(); 

            return false;
            
        }          
    }); 
            function PageLoadData(Page) {

                var Location = $('#Location').val();
                var Type = $('#Type').val();
                var Search_Txt = $('#Search_Txt').val();
                var Search_Aadhar = $('#Search_Aadhar').val();
                var Search_From_Date = $("#Search_From_Date").val();
                var Search_To_Date = $("#Search_To_Date").val();
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxUsers.php",
                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt,Search_Aadhar: Search_Aadhar,Search_From_Date: Search_From_Date,Search_To_Date: Search_To_Date},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        $("#PlaceUsersDataHere").html(msg);
                        
                    },
                });
            }// end of filter
            PageLoadData(1);

            function checkb4submit()
            {
                var Search_Txt = $('#Search_Txt').val();
                var Search_Aadhar = $("#Search_Aadhar").val();
                var strURL = "tempEmployeeReporDownload.php?Search_Txt=" + Search_Txt+'&Search_Aadhar='+Search_Aadhar;
                window.open(strURL, '_blank');
            }
        </script>


    <script type="text/javascript">
            
    $(document).ready(function(){
        $('#select_all').on('click',function(){
        if(this.checked){
            $('.checkbox').each(function(){
                this.checked = true;
            });
        }else{
             $('.checkbox').each(function(){
                this.checked = false;
            });
        }
    });
    $(document).ready(function () {
        $("#Search_From_Date").datepicker({
            format: 'dd-m-yyyy',
            autoclose: true,
            todayHighlight: true,
            defaultDate: "now",
        });

        $("#Search_To_Date").datepicker({
            format: 'dd-m-yyyy',
            autoclose: true,
            todayHighlight: true,
            defaultDate: "now",
        });
    });
    $('.checkbox').on('click',function(){
        if($('.checkbox:checked').length == $('.checkbox').length){
            $('#select_all').prop('checked',true);
        }else{
            $('#select_all').prop('checked',false);
        }
    });
});

            
        </script>
    </body>
</html>