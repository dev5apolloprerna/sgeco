<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
include('IsLogin.php');

$rightsResult = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
$rights = $rightsResult ? mysqli_fetch_assoc($rightsResult) : null;
if ($_SESSION['AdminType'] != 1 && (!isset($rights['isAdvancedEntry']) || $rights['isAdvancedEntry'] != 1)) {
    http_response_code(403);
    header('location:'.$web_url.'admin/login.php');	
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
                                            <div class="form-group col-md-3">
                                                <label for="advancedId">Advanced Period</label>
                                                <select class="form-control" id="advancedId">
                                                    <option value="">All periods</option>
                                                    <?php
                                                    $periods = mysqli_query($dbconn, "SELECT iAdvancedMasterId, strMonthYear FROM advanced_master WHERE isDelete=0 AND istatus=1 ORDER BY fromdate DESC");
                                                    while ($periods && $period = mysqli_fetch_assoc($periods)) {
                                                    ?>
                                                        <option value="<?php echo (int) $period['iAdvancedMasterId']; ?>"><?php echo htmlspecialchars($period['strMonthYear'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3 margin-top-20">
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
    <?php include_once './footer.php'; ?>
    <script>
        function PageLoadData(page) {
            $('#loading').show();
            $.post('AjaxViewAdvancedDetails.php', {
                action: 'ListAdvancedDetails',
                Page: page,
                employeeSearch: $.trim($('#employeeSearch').val()),
                companyId: $('#companyId').val(),
                advancedId: $('#advancedId').val()
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

        // PageLoadData(1);
    </script>
</body>

</html>