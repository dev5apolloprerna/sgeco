<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$rightsResult = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
$rights = mysqli_fetch_assoc($rightsResult);
if ($_SESSION['AdminType'] != 1 && (!isset($rights['isAdvancedEntry']) || $rights['isAdvancedEntry'] != 1)) {
    http_response_code(403);
    exit('Access denied.');
}

$advancedId = isset($_GET['token']) ? (int) $_GET['token'] : 0;
$advancedResult = mysqli_query($dbconn, "SELECT * FROM advanced_master WHERE iAdvancedMasterId=" . $advancedId . " AND isDelete=0 AND istatus=1");
if (!$advancedResult || mysqli_num_rows($advancedResult) === 0) {
    http_response_code(404);
    exit('Advanced date range not found.');
}
$advancedPeriod = mysqli_fetch_assoc($advancedResult);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $ProjectName; ?> | Add Advanced Details</title>
    <?php include_once './include.php'; ?>
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include_once './header.php'; ?>
    <div style="display:none; z-index:10060" id="loading"><img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif" alt="Loading"></div>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="<?php echo $web_url; ?>admin/index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li><a href="advancedmaster.php">Advanced</a><i class="fa fa-circle"></i></li>
                        <li><span>Add Advanced Details</span></li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase"> Add Advanced Details</span></div>
                                        <a class="btn blue pull-right" href="advancedmaster.php">Back</a>
                                    </div>
                                    <div class="portlet-body form">
                                        <div class="alert alert-info">Advanced period: <strong><?php echo htmlspecialchars($advancedPeriod['strMonthYear'], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo date('d-m-Y', strtotime($advancedPeriod['fromdate'])); ?> to <?php echo date('d-m-Y', strtotime($advancedPeriod['todate'])); ?>)</div>
                                        <form id="searchForm" method="post" role="form">
                                            <input type="hidden" id="advancedId" value="<?php echo $advancedId; ?>">
                                            <hr>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="employeeSearch"><strong>Search Employee</strong></label>
                                                    <input class="form-control" type="text" id="employeeSearch" name="employeeSearch" placeholder="Enter employee name or code" autocomplete="off">
                                                </div>
                                                <div class="form-group col-md-4 margin-top-20">
                                                    <button class="btn blue" type="submit" id="searchEmployeeButton"><i class="fa fa-search"></i> Search Employee</button>
                                                </div>
                                            </div>
                                        </form>
                                        <div id="message"></div>
                                        <div id="employeeResults"></div>
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
    <script>
        function showMessage(type, text) {
            $('#message').html('<div class="alert alert-' + type + '"></div>').find('.alert').text(text);
        }
        $('#searchForm').on('submit', function(event) {
            event.preventDefault();
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }
            $('#loading').show();
            $('#message').empty();
            $.post('AjaxAdvancedDetails.php', {
                action: 'SearchEmployees',
                advancedId: $('#advancedId').val(),
                employeeSearch: $('#employeeSearch').val()
            }, function(html) {
                $('#employeeResults').html(html);
                $('#loading').hide();
            }).fail(function(xhr) {
                $('#loading').hide();
                showMessage('danger', xhr.responseText || 'Unable to search employees.');
            });
        });
        $('#employeeSearch').on('keypress', function(event) {
            if (event.which === 13) {
                event.preventDefault();
                $('#searchEmployeeButton').click();
            }
        });

        function addAdvancedDetail(employeeId, button) {
            var row = $(button).closest('tr');
            var amount = row.find('.advanced-amount').val();
            var bankId = row.find('.advanced-bank').val();
            if (!amount || parseFloat(amount) <= 0) {
                showMessage('danger', 'Enter a valid amount.');
                return;
            }
            if (!bankId) {
                showMessage('danger', 'Bank is not configured in the employee master.');
                return;
            }
            $(button).prop('disabled', true);
            $('#loading').show();
            $.post('AjaxAdvancedDetails.php', {
                action: 'AddAdvancedDetail',
                advancedId: $('#advancedId').val(),
                companyId: $('#companyId').val(),
                advancedDate: $('#advancedDate').val(),
                employeeId: employeeId,
                amount: amount,
                bankId: bankId,
                remarks: row.find('.advanced-remarks').val()
            }, function(response) {
                $('#loading').hide();
                if (response.success) {
                    showMessage('success', response.message);
                    row.find('input, select, button').prop('disabled', true);
                } else {
                    $(button).prop('disabled', false);
                    showMessage('danger', response.message);
                }
            }, 'json').fail(function(xhr) {
                $('#loading').hide();
                $(button).prop('disabled', false);
                showMessage('danger', xhr.responseText || 'Unable to add advanced details.');
            });
        }
    </script>
</body>

</html>