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
    <title><?php echo $ProjectName; ?> | Other Report</title>
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
                            <span>Other Report</span>
                        </li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="col-md-12">
                            <div class="portlet light ">
                                <div class="portlet-title">
                                    <div class="caption font-red-sunglo">
                                        <i class="icon-settings font-red-sunglo"></i>
                                        <span class="caption-subject bold uppercase">Other Reports</span>
                                    </div>
                                </div>
                                <div class="portlet-body form">
                                    <form role="form" method="POST" action="" name="frmSearch" id="frmSearch" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <select class="form-control" name="Report" id="Report" required>
                                                    <option value="">Select Report</option>
                                                    <option value="form-c">Form C (Register of Loan / Recoveries / Damage / Loss / Fine / Advance / Absence)</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <?php
                                                $queryCom = "SELECT * FROM companymaster  where isDelete='0'  and  istatus='1' ";
                                                $resultCom = mysqli_query($dbconn, $queryCom);
                                                echo '<select class="form-control" name="Company" id="Company" >';
                                                echo '<option value=" " >Select Company</option>';
                                                while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                    echo "<option value='" . $rowCom['companymasterId'] . "'>" . $rowCom['companyname'] . "</option>";
                                                }
                                                echo "</select>";
                                                ?>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <select name="month" id="month" required size='1' class="form-control">
                                                    <option value="">Select Month</option>
                                                    <option value="01">January</option>
                                                    <option value="02">February</option>
                                                    <option value="03">March</option>
                                                    <option value="04">April</option>
                                                    <option value="05">May</option>
                                                    <option value="06">June</option>
                                                    <option value="07">July</option>
                                                    <option value="08">August</option>
                                                    <option value="09">September</option>
                                                    <option value="10">October</option>
                                                    <option value="11">November</option>
                                                    <option value="12">December</option>
                                                    <?php
                                                    /*for ($i = 0; $i < 12; $i++) {
                                                            $time = strtotime(sprintf('%d months', $i));
                                                            $label = date('F', $time);
                                                            $value = date('m', $time);
                                                            echo "<option value='$value'>$label</option>";
                                                        }*/
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <select name="Year" id="Year" required class="form-control">
                                                    <?php
                                                    $starting_year = date('Y', strtotime('-1 year'));
                                                    $ending_year = date('Y', strtotime('+4 year'));
                                                    $current_year = date('Y');
                                                    for ($starting_year; $starting_year <= $ending_year; $starting_year++) {
                                                        echo '<option value="' . $starting_year . '"';
                                                        if ($starting_year == $current_year) {
                                                            echo ' selected="selected"';
                                                        }
                                                        echo ' >' . $starting_year . '</option>';
                                                    }
                                                    //$starting_year = date('Y', strtotime('-5 year'));
                                                    /*$ending_year = date('Y', strtotime('+5 year'));
                                                        $current_year = date('Y');
                                                        for ($current_year; $current_year <= $ending_year; $current_year++) {
                                                            echo '<option value="' . $current_year . '"';
                                                            if ($current_year == date('Y')) {
                                                                echo ' selected="selected"';
                                                            }
                                                            echo ' >' . $current_year . '</option>';
                                                        }*/
                                                    ?>
                                                </select>
                                            </div>
                                            <!-- <div class="form-group col-md-3">
                                                    <div id="districtDiv">
                                                        <?php
                                                        $queryCat = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' GROUP BY salarymaster.month order by  month asc";
                                                        $resultCat = mysqli_query($dbconn, $queryCat) or die(mysql_error());
                                                        echo '<select class="form-control" name="salarymasterId" id="salarymasterId" required>';
                                                        echo '<option value="">Select salary Month</option>';
                                                        while ($rowCat = mysqli_fetch_array($resultCat)) {
                                                            echo "<option value='" . $rowCat['salarymasterId'] . "'>" . $rowCat['month'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                </div> -->
                                            <div class="col-md-6">
                                                <a href="#" class="btn blue margin-bottom-20" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                <a style="margin: 0 0 0 26px;" class="m-portlet__nav-link btn btn-success margin-bottom-20" onclick="exporttoexcel();"><i class="fa fa-file-excel-o"></i>&nbsp; Export Excel</a>
                                                <a href="#" onclick="checkb4submit();" class="btn red pull-right margin-bottom-20"><i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;Download PDF</a>
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

    <?php include_once './footer.php'; ?>
    <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
    <script>
        /*function findsalarymasterId()
                                                        {
                                                            var q = $('#Company').val();
                                                            var urlp = '<?php echo $web_url; ?>admin/findsalarymasterId.php?cId=' + q;
                                                            $.ajax({
                                                                type: 'POST',
                                                                url: urlp,
                                                                success: function (data) {
                                                                    $('#districtDiv').html(data);
                                                                }
                                                            }).error(function () {
                                                                alert('An error occured');
                                                            });
                                                        }*/

        $("#frmSearch").keypress(function(event) {
            if (event.keyCode === 13) {
                $("#clickbutton").click();
                return false;
            }
        });

        function PageLoadData(Page) {
            var Company = $('#Company').val();
            var month = $('#month').val();
            var Year = $('#Year').val();
            //var salarymasterId = $('#salarymasterId').val();
            var salarymasterId = month + '/' + Year;

            $('#loading').css("display", "block");
            $.ajax({
                type: "POST",
                url: "<?php echo $web_url; ?>admin/Ajaxreport.php",
                data: {
                    action: 'ListUser',
                    Page: Page,
                    Company: Company,
                    salarymasterId: salarymasterId
                },
                success: function(msg) {
                    $('#loading').css("display", "none");
                    $("#PlaceUsersDataHere").html(msg);
                },
            });
        } // end of filter
        PageLoadData(1);

        function checkb4submit() {
            var FormDate = $('#FormDate').val();
            var ToDate = $('#ToDate').val();
            var Company = $('#Company').val();
            var month = $('#month').val();
            var Year = $('#Year').val();
            if (!$('#Report').val() || !$.trim(Company) || !month || !Year) {
                alert('Please select Report, Company, Month and Year.');
                return;
            }
            //var salarymasterId = $('#salarymasterId').val();
            var salarymasterId = month + '/' + Year;
            window.open("generateFormCReportPDF.php?Company=" + encodeURIComponent(Company) + "&salarymasterId=" + encodeURIComponent(salarymasterId), '_blank');
        }
        //foreach($attr_array[1] as $id => $name) {

        function exporttoexcel() {
            var Company = $('#Company').val();
            var month = $('#month').val();
            var Year = $('#Year').val();
            if (!$('#Report').val() || !$.trim(Company) || !month || !Year) {
                alert('Please select Report, Company, Month and Year.');
                return;
            }
            var salarymasterId = month + '/' + Year;
            window.open("exportFormCReportExcel.php?Company=" + encodeURIComponent(Company) + "&salarymasterId=" + encodeURIComponent(salarymasterId), '_blank');
        }
    </script>
</body>

</html>