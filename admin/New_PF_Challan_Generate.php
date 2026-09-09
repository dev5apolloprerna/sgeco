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
    <title><?php echo $ProjectName; ?> | New PF Challan Generate Report</title>
    <?php include_once './include.php'; ?>
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include_once './header.php'; ?>
    <div style="display:none;z-index:10060" id="loading"><img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif"></div>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="<?php echo $web_url; ?>admin/index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li><span>New PF Challan Generate Report</span></li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="col-md-12">
                            <div class="portlet light">
                                <div class="portlet-title">
                                    <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase">List of New PF Challan Generate Report</span></div>
                                </div>
                                <div class="portlet-body form">
                                    <form role="form" id="frmSearch">
                                        <div class="row">
                                            <div class="form-group col-md-2"><select name="month" id="month" required class="form-control">
                                                    <option value="">Select Month</option><?php
                                                                                            for ($month = 1; $month <= 12; $month++) {
                                                                                                echo '<option value="' . sprintf('%02d', $month) . '">' . date('F', mktime(0, 0, 0, $month, 1)) . '</option>';
                                                                                            }
                                                                                            ?>
                                                </select></div>
                                            <div class="form-group col-md-2"><select name="Year" id="Year" required class="form-control"><?php
                                                                                                                                            for ($year = (int) date('Y') - 1; $year <= (int) date('Y') + 4; $year++) {
                                                                                                                                                echo '<option value="' . $year . '"' . ($year === (int) date('Y') ? ' selected' : '') . '>' . $year . '</option>';
                                                                                                                                            }
                                                                                                                                            ?></select></div>
                                            <div class="col-md-6"><button type="submit" class="btn blue margin-bottom-20">Search</button><button type="button" class="btn btn-success margin-bottom-20" style="margin-left:26px" id="exportButton"><i class="fa fa-file-excel-o"></i>&nbsp; Export Excel</button></div>
                                        </div>
                                    </form>
                                    <div id="PlaceUsersDataHere"></div>
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
        (function() {
            function validMonth() {
                if (!$('#month').val()) {
                    alert('Please Select Month');
                    $('#month').focus();
                    return false;
                }
                return true;
            }
            $('#frmSearch').on('submit', function(event) {
                event.preventDefault();
                if (!validMonth()) return;
                $('#loading').show();
                $.post('<?php echo $web_url; ?>admin/AjaxNewPFChallanCompanyReport.php', {
                        month: $('#month').val(),
                        Year: $('#Year').val()
                    })
                    .done(function(html) {
                        $('#PlaceUsersDataHere').html(html);
                    })
                    .fail(function() {
                        alert('Unable to load report.');
                    })
                    .always(function() {
                        $('#loading').hide();
                    });
            });
            $('#exportButton').on('click', function() {
                if (validMonth()) window.open('exportNewPFChallanCompanyReport.php?month=' + encodeURIComponent($('#month').val()) + '&Year=' + encodeURIComponent($('#Year').val()), '_blank');
            });
        }());
    </script>
</body>

</html>