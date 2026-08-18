<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
include_once 'advancedPaymentReportData.php';
requireAdvancedPaymentReportAccess($dbconn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $ProjectName; ?> | Summary Advance Payment</title>
    <?php include_once './include.php'; ?>
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include_once './header.php'; ?>
    <div style="display:none;z-index:10060" id="loading"><img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif" alt="Loading"></div>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li><a href="advancedPaymentReport.php">Advanced Payment Report</a><i class="fa fa-circle"></i></li>
                        <li><span>Summary Advance Payment</span></li>
                    </ul>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase">Summary Advance Payment</span></div>
                        </div>
                        <div class="portlet-body form">
                            <form id="frmSearch">
                                <div class="row">
                                    <div class="form-group col-md-3"><label for="Company">Company</label><select class="form-control" id="Company">
                                            <option value="">All Companies</option><?php
                                                                                    $companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete=0 AND istatus=1 ORDER BY companyname");
                                                                                    while ($company = mysqli_fetch_assoc($companies)) echo '<option value="' . (int) $company['companymasterId'] . '">' . htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8') . '</option>';
                                                                                    ?>
                                        </select></div>
                                    <div class="form-group col-md-2"><label for="month">Month <span class="required">*</span></label><select class="form-control" id="month" required>
                                            <option value="">Select Month</option><?php for ($m = 1; $m <= 12; $m++) {
                                                                                        $v = str_pad($m, 2, '0', STR_PAD_LEFT);
                                                                                        echo '<option value="' . $v . '"' . ($m == (int)date('m') ? ' selected' : '') . '>' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>';
                                                                                    } ?>
                                        </select></div>
                                    <div class="form-group col-md-2"><label for="Year">Year <span class="required">*</span></label><select class="form-control" id="Year" required><?php for ($y = date('Y') + 1; $y >= date('Y') - 10; $y--) echo '<option value="' . $y . '"' . ($y == date('Y') ? ' selected' : '') . '>' . $y . '</option>'; ?></select></div>
                                    <div class="col-md-5 margin-top-20"><button type="submit" class="btn blue margin-bottom-20"><i class="fa fa-search"></i> Search</button> <button type="button" class="btn btn-success margin-bottom-20 export-button"><i class="fa fa-file-excel-o"></i> Export Excel</button> <button type="button" class="btn red margin-bottom-20 pdf-button"><i class="fa fa-file-pdf-o"></i> Export PDF</button></div>
                                </div>
                            </form>
                            <div id="PlaceUsersDataHere"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once './footer.php'; ?>
    <script>
        function summaryParameters() {
            return $.param({
                Company: $('#Company').val(),
                month: $('#month').val(),
                Year: $('#Year').val()
            });
        }
        $('#frmSearch').on('submit', function(e) {
            e.preventDefault();
            $('#loading').show();
            $.post('AjaxSummaryAdvancePayment.php', summaryParameters(), function(html) {
                $('#PlaceUsersDataHere').html(html);
            }).fail(function(xhr) {
                $('#PlaceUsersDataHere').html('<div class="alert alert-danger">' + (xhr.responseText || 'Unable to load report.') + '</div>');
            }).always(function() {
                $('#loading').hide();
            });
        });
        $('.export-button').on('click', function() {
            if (document.getElementById('frmSearch').checkValidity()) window.open('exportSummaryAdvancePaymentExcel.php?' + summaryParameters(), '_blank');
            else $('#frmSearch').find(':submit').click();
        });
        $('.pdf-button').on('click', function() {
            if (document.getElementById('frmSearch').checkValidity()) window.open('generateSummaryAdvancePaymentPDF.php?' + summaryParameters(), '_blank');
            else $('#frmSearch').find(':submit').click();
        });
    </script>
</body>

</html>