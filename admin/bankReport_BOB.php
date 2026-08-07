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
        <title><?php echo $ProjectName; ?> | Bank Report BOB</title>
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
                                <span>Bank Report BOB</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Bank Report BOB</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Company</label>
                                                    <?php
                                                    $queryCom = "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                                                    ?>
                                                    <select class="form-control" name="Company" id="Company" onchange="findsalarymasterId();" required="">;
                                                        <option value='' >Select Company </option>
                                                        <?php
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom['companymasterId'] . "'>" . $rowCom['companyname'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Month</label><br/>
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
                                                    <label for="form_control_1">Year</label><br/>
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
                                                        //$starting_year = date('Y', strtotime('-5 year'));
                                                       /* $ending_year = date('Y', strtotime('+5 year'));
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
                                                <!-- <div class="form-group col-md-3">                                                        <label for="form_control_1">Salary Month</label>
<!--                                                        <input type="text"  id="salaryId"  name="salaryId" class="form-control date-picker" placeholder="Enter the Company UnSkil" required>-->
                                                    <!--<div id="districtDiv">
                                                        <?php
                                                        $queryCom = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' GROUP BY salarymaster.month order by  month asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error());
                                                        echo '<select class="form-control" name="salaryId" id="salaryId" required="" >';
                                                        echo "<option value=''>Select salary Month</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['salarymasterId'] . "'>" . $rowCom ['month'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                </div> -->
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Bank</label>
                                                    <select name="bank" id="bank" size='1' class="form-control">
                                                        <option value="">Select Bank</option>
                                                        <option value="2">SBI</option>
                                                        <option value="1">BOB</option>
                                                        <option value="3">Other(Incl BOB)</option>
                                                        <option value="4">Other(Excl BOB, SBI)</option>
                                                       
                                                    </select>
                                                    <?php
                                                    // $queryCom = "SELECT * FROM `bankmaster`  where  bankmasterId < 4 and isDelete='0'  and  istatus='1' order by  bankmasterId asc LIMIT 3";
                                                    // $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                                                    // echo '<select class="form-control" name="bank" id="bank" required="" >';
                                                    // echo "<option value='' >Select Bank </option>";
                                                    // while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                    //     echo "<option value='" . $rowCom['bankmasterId'] . "'>" . $rowCom['bankname'] . "</option>";
                                                    // }
                                                    // echo "</select>";
                                                    ?>
                                                </div>
                                                <div class="col-md-4 margin-top-20">
                                                    <a href="#" class="btn blue margin-bottom-20" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                    <a style="margin: 0 0 0 26px;"   class="m-portlet__nav-link btn btn-success margin-bottom-20" onclick="exportToExcel();"><i class="fa fa-file-excel-o"></i>&nbsp; Export Excel</a>
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

                                                        $("#frmSearch").keypress(function (event) {
                                                            if (event.keyCode === 13) {
                                                                $("#clickbutton").click();
                                                                return false;
                                                            }
                                                        });

                                                        function PageLoadData(Page) {
                                                            var bank = $('#bank').val();
                                                            var Company = $('#Company').val();
                                                            var month = $('#month').val();
                                                            var Year = $('#Year').val();
                                                            //var salaryId = $('#salarymasterId').val();
                                                            var salaryId = month+'/'+Year;
                                                            $('#loading').css("display", "block");
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "<?php echo $web_url; ?>admin/AjaxBankReport_BOB.php",
                                                                data: {action: 'ListUser', Page: Page, bank: bank, Company: Company, salaryId: salaryId},
                                                                success: function (msg) {
                                                                    $('#loading').css("display", "none");
                                                                    $("#PlaceUsersDataHere").html(msg);
                                                                },
                                                            });
                                                        }// end of filter
//                                                        if($('#Skill').val() != '' || $('#companyId').val() != '' || $('#salaryId').val() != '')
//                                                            {
                                                        PageLoadData(1);
//                                                            }

                                                        function checkb4submit()
                                                        {
                                                            var bank = $('#bank').val();
                                                            var Company = $('#Company').val();
                                                            var month = $('#month').val();
                                                            var Year = $('#Year').val();
                                                            //var salaryId = $('#salarymasterId').val();
                                                            var salaryId = month+'/'+Year;
                                                            var strURL = "generateBankRepot_BOB_PDF.php?bank=" + bank + "&Company=" + Company + "&salaryId=" + salaryId;
                                                            window.open(strURL, '_blank');
                                                        }
                                                        function exportToExcel()
                                                        {
                                                            var bank = $('#bank').val();
                                                            var Company = $('#Company').val();
                                                            var month = $('#month').val();
                                                            var Year = $('#Year').val();
                                                            //var salaryId = $('#salarymasterId').val();
                                                            var salaryId = month+'/'+Year;
                                                            //alert(salaryId);
                                                            var strURL = "generateBankRepot_BOB_Excel.php?bank=" + bank + "&Company=" + Company + "&salaryId=" + salaryId;
                                                            window.open(strURL, '_blank');
                                                        }
        </script>
    </body>
</html>