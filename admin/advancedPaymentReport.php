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
    <title><?php echo $ProjectName; ?> | Advanced Payment Report</title>
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
                        <li><span>Advanced Payment Report</span></li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="col-md-12">
                            <div class="portlet light">
                                <div class="portlet-title">
                                    <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase">Advanced Payment Report</span></div>
                                </div>
                                <div class="portlet-body form">
                                    <form id="frmSearch" role="form">
                                        <div class="row">
                                            <div class="form-group col-md-3"><label for="Company">Company</label><select class="form-control" id="Company">
                                                    <option value="">All Companies</option><?php
                                                                                            $companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete=0 AND istatus=1 ORDER BY companyname");
                                                                                            while ($company = mysqli_fetch_assoc($companies)) {
                                                                                                echo '<option value="' . (int) $company['companymasterId'] . '">' . htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8') . '</option>';
                                                                                            }
                                                                                            ?>
                                                </select></div>
                                            <div class="form-group col-md-2"><label for="month">Month</label><select class="form-control" id="month">
                                                    <option value="">All Months</option><?php
                                                                                        for ($m = 1; $m <= 12; $m++) {
                                                                                            $value = str_pad($m, 2, '0', STR_PAD_LEFT);
                                                                                            echo '<option value="' . $value . '">' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>';
                                                                                        }
                                                                                        ?>
                                                </select></div>
                                            <div class="form-group col-md-2"><label for="Year">Year</label><select class="form-control" id="Year">
                                                    <option value="">All Years</option><?php
                                                                                        for ($year = date('Y') + 1; $year >= date('Y') - 5; $year--) {
                                                                                            echo '<option value="' . $year . '"' . ($year == date('Y') ? ' selected' : '') . '>' . $year . '</option>';
                                                                                        }
                                                                                        ?>
                                                </select></div>
                                            <div class="form-group col-md-2"><label for="bank">Bank</label>
                                                <?php
                                                //$queryCom = "SELECT * FROM `bankmaster`  where  bankmasterId > 1 and isDelete='0'  and  istatus='1' order by  bankmasterId asc LIMIT 2";
                                                $queryCom = "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' order by  bankmasterId asc LIMIT 3";
                                                $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                echo '<select class="form-control" name="bank" id="bank" required="" >';
                                                echo "<option value='' >Select Bank </option>";
                                                while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                    echo "<option value='" . $rowCom['bankmasterId'] . "'>" . $rowCom['bankname'] . "</option>";
                                                }
                                                echo "</select>";
                                                ?>
                                            </div>
                                            <div class="col-md-3 margin-top-20">
                                                <button type="submit" class="btn blue margin-bottom-20"><i class="fa fa-search"></i> Search</button>
                                                <button type="button" class="btn btn-success margin-bottom-20" onclick="exportReport('exportAdvancedPaymentReportExcel.php')"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                                                <button type="button" class="btn red margin-bottom-20" onclick="exportReport('generateAdvancedPaymentReportPDF.php')"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
                                            </div>
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
        function reportParameters() {
            return $.param({
                Company: $('#Company').val(),
                month: $('#month').val(),
                Year: $('#Year').val(),
                bank: $('#bank').val()
            });
        }

        function PageLoadData(page) {
            $('#loading').show();
            $.post('AjaxAdvancedPaymentReport.php', reportParameters() + '&action=ListUser&Page=' + page, function(html) {
                $('#PlaceUsersDataHere').html(html);
                $('#loading').hide();
            }).fail(function(xhr) {
                $('#PlaceUsersDataHere').html('<div class="alert alert-danger">' + (xhr.responseText || 'Unable to load report.') + '</div>');
                $('#loading').hide();
            });
        }

        function exportReport(url) {
            window.open(url + '?' + reportParameters(), '_blank');
        }
        $('#frmSearch').on('submit', function(event) {
            event.preventDefault();
            PageLoadData(1);
        });
        // PageLoadData(1);
    </script>
</body>

</html>