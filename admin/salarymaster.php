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
        <title><?php echo $ProjectName; ?> |Salary</title>
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
                                <span>Salary</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase" id="editSalary">Add Salary</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="AddSalary" name="action" id="action">
                                                <div class="form-body">
                                                    <div class="form-group">
                                                        <label for="form_control_1">Company Name</label><br/>
                                                        <?php
                                                        $queryCom = "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                        echo '<select class="form-control" name="companymasterId" id="companymasterId" required="">';
                                                        echo "<option value='' >Select Company Name</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['companymasterId'] . "'>" . $rowCom ['companyname'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>

                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Salary month</label>
                                                        <input type="text"  id="month"  name="month" class="form-control" placeholder="Enter the Salary month" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="form_control_1">Salary Date From</label>
                                                        <input type="text"  id="fromdate"  name="fromdate" class="form-control date-picker" placeholder="Enter the from Date" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">To Salary Date</label>
                                                        <input type="text"  id="todate"  name="todate" class="form-control date-picker" placeholder="Enter the to Date" required>
                                                    </div>
                                                    <div class="form-group ">
                                                        <label for="form_control_1">Salary  Date</label>
                                                        <input type="text" id="salarypaiddate" name="salarypaiddate" class="form-control date-picker" placeholder="Enter Salary Date" required=""/>
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
                                                <span class="caption-subject bold uppercase">List of Salary</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <div class="col-md-6 pull-right">
                                                <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                                    <div class="form-group col-md-9">
                                                        <?php
                                                        $queryCom = "SELECT * FROM `salarymaster` where isDelete='0' and  istatus='1' GROUP BY salarymaster.month order by month asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                        echo '<select class="form-control" name="Search_Txt" id="Search_Txt" >';
                                                        echo "<option value=''>Select salary Month</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['month'] . "'>" . $rowCom ['month'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>
                                                        <!--<input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" placeholder="Search Salary Month " required/>-->
                                                    </div>
                                                    <div class="form-actions  col-md-3">
                                                        <a href="#" class="btn blue pull-right" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                    </div>
                                                </form>
                                            </div>
                                            <div id="PlaceSalaryDataHere">
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
        <?php include_once './footer.php'; ?>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>

        <script type="text/javascript">
                                                            $('#month').datepicker({
                                                                autoclose: true,
                                                                minViewMode: 1,
                                                                format: 'mm/yyyy'
                                                            });

                                                            $(document).ready(function () {

                                                                $("#fromdate").datepicker({
                                                                    format: 'dd-m-yyyy',
                                                                    autoclose: true,
                                                                    todayHighlight: true,
                                                                    defaultDate: "now",
                                                                });

                                                                $("#salarypaiddate").datepicker({
                                                                    format: 'dd-m-yyyy',
                                                                    autoclose: true,
                                                                    todayHighlight: true,
                                                                    defaultDate: "now",
                                                                });

                                                            });
                                                            $(document).ready(function () {

                                                                $("#todate").datepicker({
                                                                    format: 'dd-m-yyyy',
                                                                    autoclose: true,
                                                                    todayHighlight: true,
                                                                    defaultDate: "now",
                                                                });

                                                            });

                                                            function checkclose() {
                                                                window.location.href = '';
                                                            }
                                                            $('#frmparameter').submit(function (e) {
                                                                e.preventDefault();
                                                                var $form = $(this);
                                                                $('#loading').css("display", "block");
                                                                $.ajax({
                                                                    type: 'POST',
                                                                    url: '<?php echo $web_url; ?>admin/querydata.php',
                                                                    data: $('#frmparameter').serialize(),
                                                                    success: function (response) {
                                                                        //  alert(response);
                                                                        // console.log(response);
                                                                        if (response == 1)
                                                                        {
                                                                            $('#loading').css("display", "none");
                                                                            $("#Btnmybtn").attr('disabled', 'disabled');
                                                                            alert('Salary Added Sucessfully.');
                                                                            window.location.href = '';
                                                                        } else if (response == 2)
                                                                        {
                                                                            $('#loading').css("display", "none");
                                                                            $("#Btnmybtn").attr('disabled', 'disabled');
                                                                            alert('Salary Edited Sucessfully.');
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
                                                                    url: '<?php echo $web_url; ?>admin/querydata.php',
                                                                    data: {action: "GetAdminSalary", ID: id},
                                                                    success: function (response) {
                                                                        document.getElementById("editSalary").innerHTML = "EDIT Salary";
                                                                        $('#loading').css("display", "none");
                                                                        var json = JSON.parse(response);
                                                                        $('#month').val(json.month);
                                                                        $('#year').val(json.year);
                                                                        $('#fromdate').val(json.fromdate);
                                                                        $('#todate').val(json.todate);
                                                                        $('#companymasterId').val(json.companymasterId);
                                                                        $('#salarypaiddate').val(json.salarypaiddate);
                                                                        $('#action').val('EditSalaryname');
                                                                        $('<input>').attr('type', 'hidden').attr('name', 'salarymasterId').attr('value', json.salarymasterId).attr('id', 'salarymasterId').appendTo('#frmparameter');
                                                                    }
                                                                });
                                                            }
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
                                                                        url: "<?php echo $web_url; ?>admin/Ajaxsalarymaster.php",
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



                                                            $("#frmSearch").keypress(function (event) {
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
                                                                    url: "<?php echo $web_url; ?>admin/Ajaxsalarymaster.php",
                                                                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt},
                                                                    success: function (msg) {

                                                                        $("#PlaceSalaryDataHere").html(msg);
                                                                        $('#loading').css("display", "none");
                                                                    },
                                                                });
                                                            }// end of filter
                                                            PageLoadData(1);
        </script>
    </body>
</html>