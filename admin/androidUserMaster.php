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
        <title><?php echo $ProjectName; ?> | Android User Master </title>
        <?php include_once './include.php'; ?>
    </head>
    <body class="page-container-bg-solid page-boxed">
        <?php include_once './header.php'; ?>
        <div style="display: none; z-index: 10060;" id="loading">
            <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif">
        </div>
        <div class="page-container">        
            <div class="page-content-wrapper">
                <!--                <div class="page-head">
                                    <div class="container">
                                        <div class="page-title">
                                            <h1>Dashboard
                                                <small>dashboard</small>
                                            </h1>
                                        </div>                    
                                    </div>
                                </div>-->
                <div class="page-content">
                    <div class="container">
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                                <i class="fa fa-circle"></i>
                            </li>

                            <li>
                                <span>Android User Master</span>
                            </li>
                        </ul>

                        <div class="page-content-inner">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase" id="editDocument">Add Android User</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="AddAndroidUser" name="action" id="action">
                                                <div class="form-body">

                                                    <div class="form-group">
                                                        <label for="form_control_1">User Name</label>
                                                        <input type="text"  id="strUserName"  name="strUserName" class="form-control" placeholder="Enter the Android User Name" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Mobile</label>
                                                        <input type="text"  id="iMobile"  name="iMobile" class="form-control" placeholder="Enter the Mobile" required pattern='[7-9]{1}[0-9]{9}' maxlength="10" minlength="10">
                                                    </div>
                                                    <div class="form-group" id="divPassword">
                                                        <label for="form_control_1">Password</label>
                                                        <input type="password"  id="strPassword"  name="strPassword" class="form-control" placeholder="Enter the Password" required>
                                                    </div>
                                                </div>

                                                <div class="form-actions noborder">
                                                    <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">      
                                                    <button type="button" class="btn blue margin-top-20" onClick="checkclose();">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>



                                <div class="col-md-8">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase">List of Android User Master</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <div class="col-md-6 pull-right">
                                                <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                                    <div class="form-group col-md-9">
                                                        <input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" placeholder="Search Android User Name " required/>

                                                    </div>
                                                    <div class="form-actions  col-md-3">
                                                        <a href="#" class="btn blue pull-right" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                    </div>
                                                </form>
                                            </div>
                                            <div id="PlaceDocumentDataHere">

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">X</span>
                </button>
              </div>
              <form  role="form"  method="POST"  action="" name="fromchangepassword"  id="fromchangepassword" enctype="multipart/form-data" class="margin-bottom-40">
                <input type="hidden" value="AndroidUserChangePassword" name="action">
                <input type="hidden" value="" name="iAndroidUser_Id" id="iAndroidUser_Id">
                
                <div class="modal-body">
                    
                    <div class="form-group col-md-12">
                        <label for="form_control_1">New Password</label>
                        <input type="password"  id="password" name="password" class="form-control" placeholder="Enter your New Password" required>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="form_control_1">Confirm Password</label>
                        <input type="password"  id="cpassword" name="cpassword"  class="form-control" placeholder="Enter your Confirm Password" required>
                    </div>
                  </div>
                  
              <br />
              <div class="modal-footer">
                <button type="button"  class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" onClick="change_password();" class="btn btn-primary">Save changes</button>
              </div>
                </form>
            </div>
          </div>
        </div>
        
        <?php include_once './footer.php'; ?>
        <script type="text/javascript">

            function checkclose() {
                window.location.href = '';
            }

            $('#frmparameter').submit(function (e) {

                e.preventDefault();
                var $form = $(this);
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/android_querydata.php',
                    data: $('#frmparameter').serialize(),
                    success: function (response) {
                        console.log(response);
                        //$("#Btnmybtn").attr('disabled', 'disabled');
                        if (response == 1)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Android User Added Sucessfully.');
                            window.location.href = '';
                        } else if (response == 2)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Android User Edited Sucessfully.');
                            window.location.href = '';
                        } else if (response == 3)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Mobile Number Already Exist!');
                            window.location.href = '';
                        } else if (response == 4)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid Mobile Number!');
                            window.location.href = '';
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

            function setEditdata(id)
            {

                $('#errorDIV').css('display', 'none');
                $('#errorDIV').html('');
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/android_querydata.php',
                    data: {action: "GetAdminAndroidUser", ID: id},
                    success: function (response) {
                        document.getElementById("editDocument").innerHTML = "EDIT ANDROID USER";
                        $('#loading').css("display", "none");
                        var json = JSON.parse(response);
                        $('#strUserName').val(json.strUserName);
                        $('#iMobile').val(json.iMobile);
                        $('#divPassword').hide();
                        $('#strPassword').removeAttr('required', false);
                        $('#action').val('EditAndroidUser');
                        $('<input>').attr('type', 'hidden').attr('name', 'iAndroidUserId').attr('value', json.iAndroidUserId).attr('id', 'iAndroidUserId').appendTo('#frmparameter');
                    }
                });
            }
            
            function clearDevicedata(id){
                var task = 'ClearDeviceToken';
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxAndroidUsermaster.php",
                    data: {action: task, ID: id},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        alert('Android User Device Token Clear Sucessfully.');
                        window.location.href = '';
                        return false;
                    },
                });
            }


            function deletedata(task, id)
            {

                var errMsg = '';
                if (task == 'Delete') {
                    errMsg = 'Are you sure to delete?';
                } else if(task == 'InActiveUser'){
                    errMsg = 'Are you sure to Inactive User?';
                } else {
                    errMsg = 'Are you sure to Active?';
                }
                if (confirm(errMsg)) {
                    $('#loading').css("display", "block");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $web_url; ?>admin/AjaxAndroidUsermaster.php",
                        data: {action: task, ID: id},
                        success: function (msg) {

                            $('#loading').css("display", "none");
                            //alert('Android User Deleted Sucessfully.');
                            if (task == 'Delete') {
                                alert('Android User Deleted Sucessfully.');
                            } else if(task == 'InActiveUser'){
                                alert('Android User Inactive Sucessfully.');
                            } else {
                                alert('Android User Active Sucessfully.');
                            }
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
                var Search_Txt = $('#Search_Txt').val();
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxAndroidUsermaster.php",
                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt},
                    success: function (msg) {

                        $("#PlaceDocumentDataHere").html(msg);
                        $('#loading').css("display", "none");
                    },
                });
            }// end of filter
            PageLoadData(1);
            
            function changePassword(id){
                $('#iAndroidUser_Id').val(id);
            }
                
            function change_password()
            {
                var ps = $.trim($("#password").val());
                var cps = $.trim($("#cpassword").val());
                if (ps != "" && cps != "")
                {
                    if (ps != cps)
                    {
                        $("#cpassword").val('');
                        $("#cpassword").attr("placeholder", "Confirm password Doen't match");
                        $("#cpassword").focus();
                    } else {
                        var data = $('#fromchangepassword').serializeArray();
                        $('#loading').css("display", "block");
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $web_url;?>admin/android_querydata.php",
                            data: data,
                            success: function (msg) {
                                $('#loading').css("display", "none");
                                var msg = $.trim(msg);
                                if (msg == 'Sucess') {
                                    alert('Successfully Password Changed.')
                                    window.location.href = "";
                                } else if (msg == 'OldNot') {
                                    alert('Wrong Old Password !')
                                    window.location.href = "";
                                } else {
                                    alert(msg);
                                    window.location.href = "";
                                }
                            },
                        });
                    }

                } else {
                    if (ps == "")
                        $("#password").attr("placeholder", "Enter New Password please");
                    if (cps == "")
                        $("#cpassword").attr("placeholder", "Enter Confirm password");
                }

            }

        </script>
    </body>
</html>