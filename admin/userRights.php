<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `user_rights` WHERE `iUserId`='" . $_REQUEST['token'] . "'");
if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $ProjectName; ?> | User Rights </title>
    <?php include_once 'include.php'; ?>
    <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
</head>

<body class="page-container-bg-solid page-boxed">
    <a href="AddEmployee.php"></a>
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
                            <a href="<?php echo $web_url; ?>admin/users.php">List Of Users</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Edit User Rights</span>
                        </li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase"> User Rights </span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form role="form" method="POST" action="" name="frmparameter" id="frmparameter" enctype="multipart/form-data">
                                            <input type="hidden" value="addUserRights" name="action" id="action">
                                            <input type="hidden" value="<?= $_REQUEST['token']; ?>" name="id" id="id">
                                            <div class="form-body">
                                                <div class="row">
                                                    <h4 style="text-align: center;">Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Master Menu</label>
                                                        <select class="form-control" name="isMasterMenu" id="isMasterMenu">
                                                            <option value="0" <?= isset($row['isMasterMenu']) && $row['isMasterMenu'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row['isMasterMenu']) && $row['isMasterMenu'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Employee</label>
                                                        <select class="form-control" name="isEmployeeEntry" id="isEmployeeEntry">
                                                            <option value="0" <?= isset($row) && $row['isEmployeeEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isEmployeeEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Salary</label>
                                                        <select class="form-control" name="isSalaryMenu" id="isSalaryMenu">
                                                            <option value="0" <?= isset($row) && $row['isSalaryMenu'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isSalaryMenu'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Report</label>
                                                        <select class="form-control" name="isReportMenu" id="isReportMenu">
                                                            <option value="0" <?= isset($row) && $row['isReportMenu'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isReportMenu'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Bank Report</label>
                                                        <select class="form-control" name="isBankReportMenu" id="isBankReportMenu">
                                                            <option value="0" <?= isset($row) && $row['isBankReportMenu'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isBankReportMenu'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Payment</label>
                                                        <select class="form-control" name="isPaymentMenu" id="isPaymentMenu">
                                                            <option value="0" <?= isset($row) && $row['isPaymentMenu'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isPaymentMenu'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Payment History</label>
                                                        <select class="form-control" name="isPaymentHistoryMenu" id="isPaymentHistoryMenu">
                                                            <option value="0" <?= isset($row) && $row['isPaymentHistoryMenu'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isPaymentHistoryMenu'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Master Entry Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Bank Entry</label>
                                                        <select class="form-control" name="isBankEntry" id="isBankEntry">
                                                            <option value="0" <?= isset($row) && $row['isBankEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isBankEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Company Entry</label>
                                                        <select class="form-control" name="isCompanyEntry" id="isCompanyEntry">
                                                            <option value="0" <?= isset($row) && $row['isCompanyEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isCompanyEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Desgination Entry</label>
                                                        <select class="form-control" name="isDesignationEntry" id="isDesignationEntry">
                                                            <option value="0" <?= isset($row) && $row['isDesignationEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isDesignationEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Android User Entry</label>
                                                        <select class="form-control" name="isAndroidUserEntry" id="isAndroidUserEntry">
                                                            <option value="0" <?= isset($row) && $row['isAndroidUserEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isAndroidUserEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Temp Employee Entry</label>
                                                        <select class="form-control" name="isTempEmployeeEntry" id="isTempEmployeeEntry">
                                                            <option value="0" <?= isset($row) && $row['isTempEmployeeEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isTempEmployeeEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">User Create</label>
                                                        <select class="form-control" name="isUserEntry" id="isUserEntry">
                                                            <option value="0" <?= isset($row) && $row['isUserEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isUserEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Advanced Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="isAdvancedEntry">Advanced Entry</label>
                                                        <select class="form-control" name="isAdvancedEntry" id="isAdvancedEntry">
                                                            <option value="0" <?= isset($row) && $row['isAdvancedEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isAdvancedEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Salary Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">View Salary Entry</label>
                                                        <select class="form-control" name="isViewSalary" id="isViewSalary">
                                                            <option value="0" <?= isset($row) && $row['isViewSalary'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewSalary'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Create Salary Entry</label>
                                                        <select class="form-control" name="isSalaryCreateEntry" id="isSalaryCreateEntry">
                                                            <option value="0" <?= isset($row) && $row['isSalaryCreateEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isSalaryCreateEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Create Company Salary Entry</label>
                                                        <select class="form-control" name="isComplanySalaryEntry" id="isComplanySalaryEntry">
                                                            <option value="0" <?= isset($row) && $row['isComplanySalaryEntry'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isComplanySalaryEntry'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">View Company Salary Entry</label>
                                                        <select class="form-control" name="isViewCompanySalary" id="isViewCompanySalary">
                                                            <option value="0" <?= isset($row) && $row['isViewCompanySalary'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewCompanySalary'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Bank Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Conpamy Report</label>
                                                        <select class="form-control" name="isViewCompanyReport" id="isViewCompanyReport">
                                                            <option value="0" <?= isset($row) && $row['isViewCompanyReport'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewCompanyReport'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Multi Company Report</label>
                                                        <select class="form-control" name="isViewMultiCompanyReport" id="isViewMultiCompanyReport">
                                                            <option value="0" <?= isset($row) && $row['isViewMultiCompanyReport'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewMultiCompanyReport'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">New ESIC Generate</label>
                                                        <select class="form-control" name="isViewESICReport" id="isViewESICReport">
                                                            <option value="0" <?= isset($row) && $row['isViewESICReport'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewESICReport'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">PF Challan Generate</label>
                                                        <select class="form-control" name="isViewPFChallanReport" id="isViewPFChallanReport">
                                                            <option value="0" <?= isset($row) && $row['isViewPFChallanReport'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewPFChallanReport'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Bank Report Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Company Bank Report</label>
                                                        <select class="form-control" name="isViewCompanyBankReport" id="isViewCompanyBankReport">
                                                            <option value="0" <?= isset($row) && $row['isViewCompanyBankReport'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewCompanyBankReport'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Multi Company Bank Report</label>
                                                        <select class="form-control" name="isViewMultiCompanyBankReport" id="isViewMultiCompanyBankReport">
                                                            <option value="0" <?= isset($row) && $row['isViewMultiCompanyBankReport'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewMultiCompanyBankReport'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Payment Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Pay Payment</label>
                                                        <select class="form-control" name="isViewPayPayment" id="isViewPayPayment">
                                                            <option value="0" <?= isset($row) && $row['isViewPayPayment'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewPayPayment'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Paid Payment</label>
                                                        <select class="form-control" name="isViewPaidPayment" id="isViewPaidPayment">
                                                            <option value="0" <?= isset($row) && $row['isViewPaidPayment'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewPaidPayment'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">MultiCompany Pay Payment</label>
                                                        <select class="form-control" name="isViewMultiCompanyPayPayment" id="isViewMultiCompanyPayPayment">
                                                            <option value="0" <?= isset($row) && $row['isViewMultiCompanyPayPayment'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewMultiCompanyPayPayment'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">MultiCompany Paid Payment</label>
                                                        <select class="form-control" name="isViewMultiCompanyPaidPayment" id="isViewMultiCompanyPaidPayment">
                                                            <option value="0" <?= isset($row) && $row['isViewMultiCompanyPaidPayment'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewMultiCompanyPaidPayment'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <hr />
                                                    <h4 style="text-align: center;">Payment History Menu Rights</h4>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">Company Payment History</label>
                                                        <select class="form-control" name="isViewCompanyPaymentHistory" id="isViewCompanyPaymentHistory">
                                                            <option value="0" <?= isset($row) && $row['isViewCompanyPaymentHistory'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewCompanyPaymentHistory'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="form_control_1">MultiCompany Payment History</label>
                                                        <select class="form-control" name="isViewMultiCompanyPaymentHistory" id="isViewMultiCompanyPaymentHistory">
                                                            <option value="0" <?= isset($row) && $row['isViewMultiCompanyPaymentHistory'] == 0 ? 'selected' : '' ?>>No</option>
                                                            <option value="1" <?= isset($row) && $row['isViewMultiCompanyPaymentHistory'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-actions noborder" style="text-align: right;">
                                                <input class="btn blue margin-top-20" type="submit" id="Btnmybtn" value="Submit" name="submit">
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript">
        function checkclose() {
            window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
        }

        $(document).ready(function() {
            $("#mobileNo").keydown(function(e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        });

        $('#frmparameter').submit(function(e) {
            e.preventDefault();
            var formData = new FormData($('form#frmparameter')[0]);
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: 'paymentquerydata.php',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (response == 1) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('User Rights Added Sucessfully.');
                        window.location.href = '<?php echo $web_url; ?>admin/users.php';
                    } else {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Invalid Request.');
                        window.location.href = '<?php echo $web_url; ?>admin/users.php';
                    }
                }

            });
        });
    </script>
</body>

</html>