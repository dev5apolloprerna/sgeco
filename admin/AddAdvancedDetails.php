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
    header('location:'.$web_url.'admin/login.php');	
    exit;
}

$advancedId = isset($_GET['token']) ? (int) $_GET['token'] : 0;
$advancedResult = mysqli_query($dbconn, "SELECT am.*, c.companyname FROM advanced_master am INNER JOIN companymaster c ON c.companymasterId=am.iCompanyId WHERE am.iAdvancedMasterId=" . $advancedId . " AND am.isDelete=0 AND am.istatus=1");
if (!$advancedResult || mysqli_num_rows($advancedResult) === 0) {
    http_response_code(404);
    exit('Advanced date range not found.');
}
$advancedPeriod = mysqli_fetch_assoc($advancedResult);
$repayCompanies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete=0 AND istatus=1 ORDER BY companyname");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $ProjectName; ?> | Add Advanced Details</title>
    <?php include_once './include.php'; ?>
    <style>
        .advanced-details-table-wrapper {
            max-height: 65vh;
            overflow: auto;
        }

        .advanced-details-table-wrapper .table {
            margin-bottom: 0;
        }

        .advanced-details-table-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #658397;
        }
    </style>
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
                                        <button type="button" class="btn green pull-right" data-toggle="modal" data-target="#advanceRepayModal"><i class="fa fa-repeat"></i> Repay</button>
                                    </div>
                                    <div class="portlet-body form">
                                        <div class="alert alert-info">
                                            Company: <strong><?php echo htmlspecialchars($advancedPeriod['companyname'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                            Advanced period: <strong><?php echo htmlspecialchars($advancedPeriod['strMonthYear'], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo date('d-m-Y', strtotime($advancedPeriod['fromdate'])); ?> to <?php echo date('d-m-Y', strtotime($advancedPeriod['todate'])); ?>)
                                        </div>
                                        <form id="searchForm" method="post" role="form">
                                            <input type="hidden" id="advancedId" value="<?php echo $advancedId; ?>">
                                            <input type="hidden" id="companyId" value="<?php echo (int) $advancedPeriod['iCompanyId']; ?>">
                                            <hr>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="advancedDate"><strong>Date</strong></label>
                                                    <input class="form-control" type="date" id="advancedDate" min="<?php echo htmlspecialchars($advancedPeriod['fromdate'], ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($advancedPeriod['todate'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="employeeSearch"><strong>Search Employee</strong></label>
                                                    <input class="form-control" type="text" id="employeeSearch" name="employeeSearch" placeholder="Enter employee name, father name or code" autocomplete="off">
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
     <div class="modal fade" id="advanceRepayModal" tabindex="-1" role="dialog" aria-labelledby="advanceRepayTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="advanceRepayForm">
                    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="advanceRepayTitle">Advance Repay</h4></div>
                    <div class="modal-body">
                        <div class="form-group"><label for="repayCompanyId">Company</label><select id="repayCompanyId" class="form-control" required><option value="">Select company</option><?php while ($repayCompanies && $company = mysqli_fetch_assoc($repayCompanies)) { ?><option value="<?php echo (int) $company['companymasterId']; ?>"<?php echo (int) $company['companymasterId'] === (int) $advancedPeriod['iCompanyId'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?></select></div>
                        <div class="form-group"><label for="repaySourceDate">Old Advance Payment Date</label><input type="date" id="repaySourceDate" class="form-control" required></div>
                        <!-- <div class="form-group"><label for="repayDate">Repayment Date</label> -->
                            <input type="hidden" id="repayDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required readonly>
                        <!-- </div> -->
                        <button type="button" class="btn default" id="previewRepay">Preview Employees</button><div id="repayPreview" class="margin-top-20"></div><div id="repayMessage" class="margin-top-20"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Cancel</button><button type="submit" class="btn blue">Repay</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php include_once './footer.php'; ?>
    <script>
        function showMessage(type, text) {
            $('#message').html('<div class="alert alert-' + type + '"></div>').find('.alert').text(text);
        }
        $('#previewRepay').click(function() {
            $('#repayPreview').html('<div class="text-center">Loading...</div>');
            $.post('<?php echo $web_url; ?>admin/AjaxAdvanceRepay.php', {
                action: 'Preview', companyId: $('#repayCompanyId').val(), sourceDate: $('#repaySourceDate').val()
            }, function(html) { $('#repayPreview').html(html); });
        });
        $('#advanceRepayForm').submit(function(event) {
            event.preventDefault();
            $('#loading').show();
            $.post('<?php echo $web_url; ?>admin/AjaxAdvanceRepay.php', {
                action: 'Add', companyId: $('#repayCompanyId').val(), sourceDate: $('#repaySourceDate').val(), repayDate: $('#repayDate').val()
            }, function(response) {
                $('#loading').hide();
                $('#repayMessage').html($('<div>').addClass('alert ' + (response.success ? 'alert-success' : 'alert-danger')).text(response.message));
                if (response.success) setTimeout(function() { window.location.href = 'viewAdvancedDetails.php'; }, 700);
            }, 'json');
        });
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
                employeeSearch: $.trim($('#employeeSearch').val())
            }, function(html) {
                $('#employeeResults').html(html);
                $('#loading').hide();
            }).fail(function(xhr) {
                $('#loading').hide();
                $('#employeeResults').empty();
                showMessage('danger', xhr.responseText || 'Unable to search employees.');
            });
        });
        $('#employeeSearch').on('keypress', function(event) {
            if (event.which === 13) {
                event.preventDefault();
                $('#searchEmployeeButton').click();
            }
        });

        function updateMultiRowSubmitButton() {
            var enteredAmountCount = $('#employeeResults .employee-row')
                .not('.advanced-detail-saved')
                .find('.advanced-amount')
                .filter(function() {
                    return $.trim($(this).val()) !== '';
                }).length;

            $('#employeeResults .multi-row-submit-button').prop('disabled', enteredAmountCount < 2);
        }

        $('#employeeResults').on('input change', '.advanced-amount', updateMultiRowSubmitButton);

        function submitAdvancedDetails(button, rowElement) {
            var companyId = $('#companyId').val();
            var advancedDate = $('#advancedDate').val();
            if (!companyId || !advancedDate) {
                showMessage('danger', 'Select a date.');
                return;
            }
            var details = [];
            var invalidAmount = false;
            var rows = rowElement ? $(rowElement) : $('#employeeResults .employee-row').not('.advanced-detail-saved');
            rows.each(function() {
                var row = $(this);
                var amount = $.trim(row.find('.advanced-amount').val());
                if (amount === '') {
                    if (rowElement) {
                        invalidAmount = true;
                    }
                    return;
                }
                if (!/^\d+(\.\d{1,2})?$/.test(amount) || parseFloat(amount) <= 0) {
                    invalidAmount = true;
                    return false;
                }
                details.push({
                    employeeId: row.data('employee-id'),
                    amount: amount,
                    remarks: row.find('.advanced-remarks').val()
                });
            });
            if (invalidAmount) {
                showMessage('danger', 'Enter an amount greater than zero with no more than two decimal places.');
                return;
            }
            if (details.length === 0) {
                showMessage('danger', 'Enter an amount for at least one employee.');
                return;
            }
            $(button).prop('disabled', true);
            $('#loading').show();
            $.post('AjaxAdvancedDetails.php', {
                action: 'AddAdvancedDetails',
                advancedId: $('#advancedId').val(),
                companyId: companyId,
                advancedDate: advancedDate,
                details: JSON.stringify(details)
            }, function(response) {
                $('#loading').hide();
                if (response.success) {
                    showMessage('success', response.message);
                    details.forEach(function(detail) {
                        var savedRow = $('#employeeResults .employee-row[data-employee-id="' + detail.employeeId + '"]');
                        savedRow.addClass('advanced-detail-saved');
                        savedRow.find('input, .row-submit-button').prop('disabled', true);
                    });
                    updateMultiRowSubmitButton();
                    if (!rowElement) {
                        $(button).prop('disabled', true);
                    }
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