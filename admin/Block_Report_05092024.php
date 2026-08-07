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
        <title><?php echo $ProjectName; ?> | BLocked Employee </title>
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
                                <span>BLocked Employee</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">

                            <div class="col-md-12">
                                <div class="portlet light " style="width: 100%;float: left;">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Employee BLocked</span>
                                        </div>
                                        <a href="#" onclick="checkb4submit();" class="btn green  margin-bottom-20" style="float: right;"><i class="fa fa-file-excel-o"></i></a>
                                        <!--<a href="<?php echo $web_url; ?>admin/AddEmployee.php" class="btn blue" style="float: right;" title="Add Employee">ADD Employee</a>-->
                                    </div>
                                    <div class="portlet-body form">

                                        <!--<form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">

                                            <div class="form-group col-md-3">
                                                <input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" onkeyup="PageLoadData(1);" placeholder="Search Employee Name " required/>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <input type="text" value="" name="Search_Aadhar" class="form-control" id="Search_Aadhar" onkeyup="PageLoadData(1);" placeholder="Search Employee Aadhar " required/>
                                            </div>
                                            <div class="form-actions  col-md-3">

                                                <a href="#" class="btn blue pull-left" id="clickbutton" onclick="PageLoadData(1);">Search</a>

                                                <a href="#" onclick="checkb4submit();" class="btn green  margin-bottom-20"><i class="fa fa-file-excel-o"></i></a>
                                            </div>
                                        </form>-->
                                        
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

       
        <script type="text/javascript">
            function deletedata(task, id)
            {
                var errMsg = '';
                if (task == 'Delete') {
                    errMsg = 'Are you sure to delete?';
                } else if(task == 'Block'){
                    errMsg = 'Are you sure to block?';
                } else {
                    errMsg = 'Are you sure to unblock?';
                }
                if (confirm(errMsg)) {
                    $('#loading').css("display", "block");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $web_url; ?>admin/AjaxEmployeeBlockedReport.php",
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
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxEmployeeBlockedReport.php",
                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt,Search_Aadhar: Search_Aadhar},
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
    
                var strURL = "employeeBlockedReporDownload.php?Search_Txt=" + Search_Txt+'&Search_Aadhar='+Search_Aadhar;

                window.open(strURL, '_blank');
            }
            
            function exitModelGet(id){
                $("#iEmployeeId").val(id);
            }
            
            function exitEmployeeSet(task){
                var errMsg = '';
                var id = $("#iEmployeeId").val();
                if (task == 'ExitEmployee') {
                    errMsg = 'Are you sure to exit employee?';
                } 
                if(id>0){
                    var strExitDate = $("#strExitDate").val();
                    if (confirm(errMsg)) {
                        $('#loading').css("display", "block");
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $web_url; ?>admin/AjaxExitReport.php",
                            data: {action: task, ID: id, strExitDate: strExitDate },
                            success: function (msg) {
                                
                                $('#loading').css("display", "none");
                                window.location.href = '';
                                return false;
                            },
                        });
                    }
                }
                return false;
            }
            
            function activeExitEmployee(id){
                var errMsg = 'Are you sure to active exited employee?';
                
                if(id>0){
                    if (confirm(errMsg)) {
                        $('#loading').css("display", "block");
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $web_url; ?>admin/AjaxExitReport.php",
                            data: {action: "activeExitEmployee", ID: id},
                            success: function (msg) {
                                if(msg == 1){
                                    alert('Oops, You have already active employee with same aadhar number');
                                    $('#loading').css("display", "none");
                                    window.location.href = '';
                                    return false;
                                } else {
                                    alert('Active Employee Successfully');
                                    $('#loading').css("display", "none");
                                    window.location.href = '';
                                    return false;
                                }
                            },
                        });
                    }
                }
                return false;
            }
            
            
            function updateSetBank(Id){
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/querydata.php",
                    data: {action: 'employeeBankDetails', Id: Id},
                    success: function (response) {
                        console.log(response);
                        $('#loading').css("display", "none");
                        var json = JSON.parse(response);
                        $('#bankid').val(json.bankid);
                        $('#iEmpId').val(Id);
                        $('#accountno').val(json.accountno);
                        $('#ifsccode').val(json.ifsccode);
                    },
                });
            }
            
            $('#frmeditempbankdetail').submit(function (e) {

                e.preventDefault();
                var $form = $(this);
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: $('#frmeditempbankdetail').serialize(),
                    success: function (response) {
                        console.log(response);
                        //$("#Btnmybtn").attr('disabled', 'disabled');
                        if (response == 1)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Bank Details Edited Sucessfully.');
                            window.location.href = '';
                        } else if (response == 2)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid OTP.');
                            window.location.href = '';
                        }else if (response == 3)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('OTP Timeout.');
                            //window.location.href = '';
                        } else
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid Request.');

                            window.location.href = '';
                        }
                    }

                });
            });
            
            function resendOTP(){
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: {action: 'resendOTP'},
                    success: function (response) {
                        console.log(response);
                        //$("#Btnmybtn").attr('disabled', 'disabled');
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('OPT resend Sucessfully.');
                    
                    }

                });
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
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $("#strExitDate").datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                });
            });
        </script>
    </body>
</html>