<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `salarymaster` WHERE `salarymasterId`='" . $_REQUEST['token'] . "'");
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
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> | Add Salary Details</title>
        <?php include_once 'include.php'; ?>
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
                                <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Salary Details</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <span>Add Salary Details</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase"> Add Salary Details</span>
                                            </div>
                                            <a  class="margin_fr_25 btn blue pull-right"  href="salarymaster.php">Back</a>
                                            <!--<a  class="btn blue pull-right" target="-" href="AddEmployee.php">Add Employee</a>-->
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmserch"  id="frmserch" enctype="multipart/form-data">
                                                <input type="hidden" value="AddSalaryDetails" name="action" id="action">
                                                <input type="hidden" value="<?php echo $_REQUEST['token']; ?>" name="salarymasterId" id="salarymasterId">
                                                <div class="row">

                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1"><strong>Salary Month :-</strong></label>
                                                        <?php
                                                        echo $row['month'];
//                                                        $queryCom = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' order by  month asc";
//                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
//                                                        echo '<select class="form-control" name="salaryId" id="salaryId" required="">';
//                                                        echo "<option value=''>Select salary Month</option>";
//                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
//                                                            echo "<option value='" . $rowCom ['salarymasterId'] . "'>" . $rowCom ['month'] . "</option>";
//                                                        }
//                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1"><strong>Salary Paid Date :-</strong></label>
                                                        <?php
                                                        echo $row['salarypaiddate'];
                                                        ?>
                                                        <!--                                                         <input type="text" id="salarypaiddate" name="salarypaiddate" class="form-control date-picker" placeholder="Enter The Date" required=""/>-->
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1"><strong>Company Name :-</strong></label>
                                                        <?php
                                                        $queryCom = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster`  where companymasterId='" . $row['companymasterId'] . "' and  isDelete='0'  and  istatus='1' order by  companymasterId asc"));
                                                        echo $queryCom['companyname'];

//                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
//                                                        echo '<select class="form-control" name="companyId" id="companyId" required="">';
//                                                        echo "<option value=''>Select company Name</option>";
//                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
//                                                            echo "<option value='" . $rowCom ['companymasterId'] . "'>" . $rowCom ['companyname'] . "</option>";
//                                                        }
//                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="form_control_1">Employee</label><br/>
                                                        <input type="text" id="employeeId" name="employeeId" class="form-control" list="employeeSuggestions" autocomplete="off" placeholder="Search by name, UAN or father name" />
                                                        <datalist id="employeeSuggestions"></datalist>
                                                        <?php
//                                                        $queryCom = "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' order by  emp_name asc";
//                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
//                                                        echo '<select class="form-control" name="employeeId" id="employeeId" required="">';
//                                                        echo "<option value=''>Select Employee Name</option>";
//                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
//                                                            echo "<option value='" . $rowCom ['employeeId'] . "'>" . $rowCom ['emp_name'] . "</option>";
//                                                        }
//                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="permanentStatus">Employee List</label><br/>
                                                        <select id="permanentStatus" name="permanentStatus" class="form-control">
                                                            <option value="1">Permanent Employees</option>
                                                            <option value="0">Add New Permanent Employee</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 margin-top-20">
                                                        <a  class="btn blue pull-left"  id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                    </div>
                                                </div>
                                            </form>
                                            <div id="PlaceUsersDataHere">

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

            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/salarydetails.php';
            }
            $(document).ready(function () {

                $("#salarypaiddate").datepicker({
                    format: 'dd-m-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    defaultDate: "now",
                });

                 $('#permanentStatus').change(function () {
                    loadEmployeeSuggestions();
                    PageLoadData(1);
                });

                var suggestionTimer;
                $('#employeeId').on('input', function () {
                    clearTimeout(suggestionTimer);
                    suggestionTimer = setTimeout(loadEmployeeSuggestions, 250);
                });
            });            

            $("#employeeId").keypress(function (event) {
                if (event.keyCode === 13) {
                    $("#clickbutton").click();
                    return false;
                }
            });

            function PageLoadData(Page) {
                var employeeId = $('#employeeId').val();
                var salarymasterId = $('#salarymasterId').val();
                var permanentStatus = $('#permanentStatus').val();
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxPermanentEmployeeSalaryDetails.php",
                    data: {action: 'ListUser', Page: Page, employeeId: employeeId, salarymasterId: salarymasterId, permanentStatus: permanentStatus},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        $("#PlaceUsersDataHere").html(msg);
                    },
                });
            }

            function loadEmployeeSuggestions() {
                var search = $.trim($('#employeeId').val());
                if (search.length < 2) {
                    $('#employeeSuggestions').empty();
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/AjaxPermanentEmployeeSalaryDetails.php',
                    dataType: 'json',
                    data: {
                        action: 'EmployeeSuggestions',
                        term: search,
                        permanentStatus: $('#permanentStatus').val()
                    },
                    success: function (employees) {
                        var options = '';
                        $.each(employees, function (index, employee) {
                            options += $('<option>').val(employee.value).attr('label', employee.label).prop('outerHTML');
                        });
                        $('#employeeSuggestions').html(options);
                    }
                });
            }

            function updatePermanentEmployee(employeeId, isPermanent) {
                var message = isPermanent === 1
                        ? 'Are you sure you want to add this employee to the permanent list?'
                        : 'Are you sure you want to remove this employee from the permanent list?';

                if (!confirm(message)) {
                    return false;
                }

                $('#loading').css('display', 'block');
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/AjaxPermanentEmployeeSalaryDetails.php',
                    dataType: 'json',
                    data: {action: 'UpdatePermanent', employeeId: employeeId, isPermanent: isPermanent},
                    success: function (response) {
                        if (!response.success) {
                            alert(response.message || 'Unable to update employee.');
                        }
                        PageLoadData(1);
                    },
                    error: function () {
                        $('#loading').css('display', 'none');
                        alert('Unable to update employee. Please try again.');
                    }
                });
                return false;
            }

            function calculatePermanentTotal(rowNumber) {
                var workingDays = parseFloat($('#workingdays_' + rowNumber).val()) || 0;
                var wagesRate = parseFloat($('#wagesrate_' + rowNumber).val()) || 0;
                $('#salary_' + rowNumber).val((workingDays * wagesRate).toFixed(2));
            }

            PageLoadData(1);

        </script>
    </body>
</html>