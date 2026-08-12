<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
include('IsLogin.php');

$rightsResult = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
$rights = $rightsResult ? mysqli_fetch_assoc($rightsResult) : null;
if ($_SESSION['AdminType'] != 1 && (!isset($rights['isAdvancedEntry']) || $rights['isAdvancedEntry'] != 1)) {
    http_response_code(403);
    header('location:' . $web_url . 'admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $ProjectName; ?> | Advanced Details</title>
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
                        <li><span>Advanced Details</span></li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="col-md-12">
                            <div class="portlet light">
                                <div class="portlet-title">
                                    <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase">List of Advanced Details</span></div>
                                    <a class="btn blue pull-right" href="advancedmaster.php"><i class="fa fa-plus"></i> Add Advanced</a>
                                </div>
                                <div class="portlet-body form">
                                    <form id="advancedSearchForm" role="form">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label for="employeeSearch">Name / Employee Code</label>
                                                <input type="text" class="form-control" id="employeeSearch" placeholder="Search employee">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="companyId">Company</label>
                                                <select class="form-control" id="companyId">
                                                    <option value="">All companies</option>
                                                    <?php
                                                    $companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete=0 AND istatus=1 ORDER BY companyname");
                                                    while ($companies && $company = mysqli_fetch_assoc($companies)) {
                                                    ?>
                                                        <option value="<?php echo (int) $company['companymasterId']; ?>"><?php echo htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="month">Month</label>
                                                <select class="form-control" id="month">
                                                    <option value="">All Months</option>
                                                    <?php
                                                    for ($month = 1; $month <= 12; $month++) {
                                                        $monthValue = str_pad($month, 2, '0', STR_PAD_LEFT);
                                                    ?>
                                                        <option value="<?php echo $monthValue; ?>"><?php echo date('F', mktime(0, 0, 0, $month, 1)); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="year">Year</label>
                                                <select class="form-control" id="year">
                                                    <option value="">All Years</option>
                                                    <?php for ($year = date('Y') + 1; $year >= date('Y') - 5; $year--) { ?>
                                                        <option value="<?php echo $year; ?>" <?php echo $year == date('Y') ? ' selected' : ''; ?>><?php echo $year; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="fromDate">From Date</label>
                                                <input class="form-control" type="date" id="fromDate" disabled>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="toDate">To Date</label>
                                                <input class="form-control" type="date" id="toDate" disabled>
                                            </div>
                                            <div class="form-group col-md-2 margin-top-20">
                                                <button type="submit" class="btn blue"><i class="fa fa-search"></i> Search</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div id="advancedDetailsList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editAdvancedDetailModal" tabindex="-1" role="dialog" aria-labelledby="editAdvancedDetailTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editAdvancedDetailForm">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="editAdvancedDetailTitle">Edit Advanced Detail</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editDetailId">
                        <input type="hidden" id="editEmployeeId">
                        <div class="form-group">
                            <label for="editEmployeeSearch">Employee</label>
                            <div class="dropdown">
                                <input type="text" class="form-control" id="editEmployeeSearch" placeholder="Type employee name or code" autocomplete="off" required>
                                <ul class="dropdown-menu" id="editEmployeeSuggestions" style="width:100%; max-height:220px; overflow-y:auto;"></ul>
                            </div>
                            <span class="help-block">Type at least two characters, then select an employee from the suggestions.</span>
                        </div>
                        <div class="form-group"><label for="editAdvancedDate">Date <small id="editAdvancedPeriod" class="text-muted"></small></label><input type="date" class="form-control" id="editAdvancedDate" required></div>
                        <div class="form-group"><label for="editAdvancedAmount">Amount</label><input type="number" min="0.01" step="0.01" class="form-control" id="editAdvancedAmount" required></div>
                        <div class="form-group"><label for="editAdvancedRemarks">Remarks</label><textarea class="form-control" id="editAdvancedRemarks" maxlength="1000" rows="3"></textarea></div>
                        <div id="editAdvancedDetailMessage"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn blue"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include_once './footer.php'; ?>
    <script>
        function PageLoadData(page) {
            $('#loading').show();
            $.post('AjaxViewAdvancedDetails.php', {
                action: 'ListAdvancedDetails',
                Page: page,
                employeeSearch: $.trim($('#employeeSearch').val()),
                companyId: $('#companyId').val(),
                month: $('#month').val(),
                year: $('#year').val(),
                fromDate: $('#fromDate').val(),
                toDate: $('#toDate').val()
            }, function(html) {
                $('#advancedDetailsList').html(html);
                $('#loading').hide();
            }).fail(function(xhr) {
                $('#loading').hide();
                $('#advancedDetailsList').html($('<div class="alert alert-danger"></div>').text(xhr.responseText || 'Unable to load advanced details.'));
            });
        }

        $('#advancedSearchForm').on('submit', function(event) {
            event.preventDefault();
            PageLoadData(1);
        });

        function updateDateRange() {
            var month = $('#month').val();
            var year = $('#year').val();
            var fields = $('#fromDate, #toDate');
            if (!month || !year) {
                fields.val('').prop('disabled', true).removeAttr('min max');
                return;
            }
            var lastDay = new Date(Number(year), Number(month), 0).getDate();
            var minimum = year + '-' + month + '-01';
            var maximum = year + '-' + month + '-' + ('0' + lastDay).slice(-2);
            fields.prop('disabled', false).attr({
                min: minimum,
                max: maximum
            });
            if (!$('#fromDate').val() || $('#fromDate').val() < minimum || $('#fromDate').val() > maximum) $('#fromDate').val(minimum);
            if (!$('#toDate').val() || $('#toDate').val() < minimum || $('#toDate').val() > maximum) $('#toDate').val(maximum);
        }

        $('#month, #year').on('change', updateDateRange);
        updateDateRange();

        // PageLoadData(1);
        $('#advancedDetailsList').on('click', '.edit-advanced-detail', function() {
            var button = $(this);
            $('#editDetailId').val(button.data('id'));
            $('#editEmployeeId').val(button.attr('data-employee-id'));
            $('#editEmployeeSearch').val(button.attr('data-employee-name'));
            $('#editEmployeeSuggestions').empty().hide();
            $('#editAdvancedDate')
                .attr('min', button.attr('data-min-date'))
                .attr('max', button.attr('data-max-date'))
                .val(button.attr('data-date'));
            $('#editAdvancedPeriod').text('(select a date within ' + button.attr('data-period') + ')');
            $('#editAdvancedAmount').val(button.attr('data-amount'));
            $('#editAdvancedRemarks').val(button.attr('data-remarks'));
            $('#editAdvancedDetailMessage').empty();
            $('#editAdvancedDetailModal').modal('show');
        });

        var employeeSearchTimer;
        $('#editEmployeeSearch').on('input', function() {
            var searchInput = $(this);
            var search = $.trim(searchInput.val());
            $('#editEmployeeId').val('');
            clearTimeout(employeeSearchTimer);
            if (search.length < 2) {
                $('#editEmployeeSuggestions').empty().hide();
                return;
            }
            employeeSearchTimer = setTimeout(function() {
                $.post('AjaxViewAdvancedDetails.php', {
                    action: 'SearchAdvancedEmployees',
                    employeeSearch: search
                }, function(response) {
                    var suggestions = $('#editEmployeeSuggestions').empty();
                    if (!response.success || response.employees.length === 0) {
                        suggestions.append('<li class="disabled"><a href="#">No employees found</a></li>').show();
                        return;
                    }
                    $.each(response.employees, function(index, employee) {
                        $('<a href="#"></a>')
                            .text(employee.name + ' (' + employee.code + ')')
                            .data('employee', employee)
                            .appendTo($('<li></li>').appendTo(suggestions));
                    });
                    suggestions.show();
                }, 'json');
            }, 250);
        });

        $('#editEmployeeSuggestions').on('click', 'a', function(event) {
            event.preventDefault();
            var employee = $(this).data('employee');
            if (!employee) return;
            $('#editEmployeeId').val(employee.id);
            $('#editEmployeeSearch').val(employee.name + ' (' + employee.code + ')');
            $('#editEmployeeSuggestions').empty().hide();
        });

        $('#editAdvancedDetailForm').on('submit', function(event) {
            event.preventDefault();
            if (!$('#editEmployeeId').val()) {
                $('#editAdvancedDetailMessage').html('<div class="alert alert-danger">Select an employee from the suggestions.</div>');
                return;
            }
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }
            var submitButton = $(this).find('[type="submit"]');
            submitButton.prop('disabled', true);
            $.post('AjaxViewAdvancedDetails.php', {
                action: 'UpdateAdvancedDetail',
                detailId: $('#editDetailId').val(),
                employeeId: $('#editEmployeeId').val(),
                advancedDate: $('#editAdvancedDate').val(),
                amount: $('#editAdvancedAmount').val(),
                remarks: $('#editAdvancedRemarks').val()
            }, function(response) {
                submitButton.prop('disabled', false);
                if (response.success) {
                    $('#editAdvancedDetailModal').modal('hide');
                    PageLoadData(1);
                } else {
                    $('#editAdvancedDetailMessage').html($('<div class="alert alert-danger"></div>').text(response.message));
                }
            }, 'json').fail(function(xhr) {
                submitButton.prop('disabled', false);
                $('#editAdvancedDetailMessage').html($('<div class="alert alert-danger"></div>').text(xhr.responseText || 'Unable to update advanced detail.'));
            });
        });

        $('#advancedDetailsList').on('click', '.delete-advanced-detail', function() {
            if (!confirm('Are you sure you want to delete this entry?')) return;
            var detailId = $(this).data('id');
            $('#loading').show();
            $.post('AjaxViewAdvancedDetails.php', {
                action: 'DeleteAdvancedDetail',
                detailId: detailId
            }, function(response) {
                $('#loading').hide();
                if (response.success) PageLoadData(1);
                else alert(response.message);
            }, 'json').fail(function(xhr) {
                $('#loading').hide();
                alert(xhr.responseText || 'Unable to delete advanced detail.');
            });
        });
    </script>
</body>

</html>