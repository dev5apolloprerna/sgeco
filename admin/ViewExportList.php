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
        <title><?php echo $ProjectName; ?> | View Employee Export List </title>
        <?php include_once './include.php'; ?>
        <!--<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />-->
        <link href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
        <link href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css" rel="stylesheet" type="text/css" />
        
        
        <style>
            .btn-group > .dropdown-menu {
                opacity: 1!important;
            }
            button.btn.btn-secondary.buttons-excel.buttons-html5 {
                background: #658397;
                border: 1px solid #658397;
                color: #fff;
                margin-bottom: 5px;
                font-size: 16px;
            }
            
            button.btn.btn-secondary.buttons-collection.dropdown-toggle.buttons-colvis {
                background: #ccc;
                color: #000;
                margin-bottom: 5px;
                font-size: 15px;
            }
        </style>
        
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
                                <span>View Employee Export List</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">

                            <div class="col-md-12">
                                <div class="portlet light " style="width: 100%;float: left;">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Employee Export</span>
                                        </div>
                                        
                                        <a href="#" onclick="javascript: history.go(-1);" class="btn blue" style="float: right;" title="Back">Back</a>
                                        <a href="<?php echo $web_url; ?>admin/exportEmployeeData.php?token=<?= $_REQUEST['token']; ?>" class="btn blue" style="margin-left: 10px;" title="Employee Export To Excel"><i class="fa fa-file-excel-o"></i></a>
                                        
                                        <input type="hidden" name="token" id="token" value="<?= $_REQUEST['token']; ?>">
                                    </div>
                                    <!--<div class="portlet-body form">-->

                                    <!--    <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">-->

                                    <!--        <div class="form-group col-md-3">-->
                                    <!--            <input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" onkeyup="PageLoadData(1);" placeholder="Search Employee Name " required/>-->
                                    <!--        </div>-->
                                    <!--        <div class="form-group col-md-3">-->
                                    <!--            <input type="text" value="" name="Search_Aadhar" class="form-control" id="Search_Aadhar" onkeyup="PageLoadData(1);" placeholder="Search Employee Aadhar " required/>-->
                                    <!--        </div>-->
                                    <!--        <div class="form-actions  col-md-3">-->

                                    <!--            <a href="#" class="btn blue pull-left" id="clickbutton" onclick="PageLoadData(1);">Search</a>-->

                                    <!--            <a href="#" onclick="checkb4submit();" class="btn green  margin-bottom-20"><i class="fa fa-file-excel-o"></i></a>-->
                                    <!--        </div>-->
                                    <!--    </form>-->
                                    <!--</div>-->
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
                        url: "<?php echo $web_url; ?>admin/AjaxViewExportList.php",
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

                var Location = $('#Location').val();
                var Type = $('#Type').val();
                var Search_Txt = $('#Search_Txt').val();
                var Search_Aadhar = $('#Search_Aadhar').val();
                var token= $("#token").val();
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxViewExportList.php",
                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt,Search_Aadhar: Search_Aadhar,token: token},
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
    
                var strURL = "employeeReporDownload.php?Search_Txt=" + Search_Txt+'&Search_Aadhar='+Search_Aadhar;

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
    
    $('.checkbox').on('click',function(){
        if($('.checkbox:checked').length == $('.checkbox').length){
            $('#select_all').prop('checked',true);
        }else{
            $('#select_all').prop('checked',false);
        }
    });
});

            
        </script>
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap5.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.min.js"></script>

   
    </body>
</html>