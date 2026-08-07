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
        <title><?php echo $ProjectName; ?> | Multi Company Report</title>
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
                                <span>Multi Company</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Multi Company</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                            <div class="row">
                                                <!-- <div class="form-group col-md-3">
                                                    <?php
                                                    $queryCat = "SELECT * FROM `companysalarymaster`  where isDelete='0'  and  istatus='1'   GROUP BY month  order by  month asc";
                                                    $resultCat = mysqli_query($dbconn, $queryCat);
                                                    echo '<select class="form-control" name="month" id="month">';
                                                    echo "<option value='' >Select salary Month</option>";
                                                    while ($rowCat = mysqli_fetch_array($resultCat)) {
                                                        echo "<option value='" . $rowCat['month'] . "'>" . $rowCat['month'] . "</option>";
                                                    }
                                                    echo "</select>";
                                                    ?>
                                                </div> -->
                                                <div class="form-group col-md-2">
                                                    <select name="month" id="month" size='1' class="form-control">
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
                                                    <select name="Year" id="Year" class="form-control">
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
                                                <div class="col-md-3">
                                                    <a href="#" class="btn blue margin-bottom-20" id="clickbutton" onclick="PageLoadData(1);">Search</a>
<!--                                                    <a href="#" onclick="checkb4submit();" class="btn red pull-right margin-bottom-20"><i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;Download PDF</a>-->
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
        <script>

            $("#frmSearch").keypress(function (event) {
                if (event.keyCode === 13) {
                    $("#clickbutton").click();
                    return false;
                }
            });

            function PageLoadData(Page) {
                //var month = $('#month').val();
                var months = $('#month').val();
                var Year = $('#Year').val();
                var month = months +'/'+ Year;
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/Ajaxmulticompanyreport.php",
                    data: {action: 'ListUser', Page: Page, month: month},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        $("#PlaceUsersDataHere").html(msg);
                    },
                });
            }// end of filter
//            PageLoadData(1);

//            function checkb4submit()
//            {
//                var Company = $('#Company').val();
//                var salarymasterId = $('#salarymasterId').val();
//                var strURL = "multicompanygeneratePDF.php?Company=" + Company + "&salarymasterId=" + salarymasterId;
//                window.open(strURL, '_blank');
//            }

        </script>
    </body>
</html>