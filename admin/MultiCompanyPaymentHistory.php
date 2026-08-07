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
        <title><?php echo $ProjectName; ?> | Multicompany Payment History</title>
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
                                <span>Multicompany Payment History List</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Multicompany Payment History</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Month</label><br/>
                                                    <select name="month" id="month" size='1' class="form-control" onchange="findcompany();">
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
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Year</label><br/>
                                                    <select name="Year" id="Year" class="form-control" onchange="findcompany();">
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
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Company</label>
                                                    <div id="districtDiv">
                                                        <?php
                                                        $companymasterId = '';
                                                        $comid = mysqli_query($dbconn, "select multiycompanysalarymaster.companysalarymasterId,GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId
                                                = multiycompanysalarymaster.companymasterId and multiycompanysalarymaster.isDelete=0
                                                group by multiycompanysalarymaster.companysalarymasterId ");
                                                        ?>
                                                        <select class="form-control" name="companysalarymasterId" id="companysalarymasterId" required="">
                                                            <option value='' >Select Company </option>
                                                            <?php
                                                            while ($commaster = mysqli_fetch_array($comid)) {
                                                                echo "<option value='" . $commaster['companysalarymasterId'] . "'>" . $commaster['Company'] . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Bank</label>
                                                    <?php
                                                    //$queryCom = "SELECT * FROM `bankmaster`  where isDelete='0' and bankmasterId!=1 and  istatus='1' order by  bankmasterId asc LIMIT 2";
                                                    $queryCom = "SELECT * FROM `bankmaster`  where isDelete='0' and  istatus='1' order by  bankmasterId asc LIMIT 3";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                                                    echo '<select class="form-control" name="bank" id="bank" required="" >';
                                                    echo "<option value='' >Select Bank </option>";
                                                    while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                        echo "<option value='" . $rowCom['bankmasterId'] . "'>" . $rowCom['bankname'] . "</option>";
                                                    }
                                                    echo "</select>";
                                                    ?>
                                                </div>
                                                <div class="col-md-4 margin-top-20">
                                                    <a href="#" style="padding: 9px 7px;" class="btn blue margin-bottom-20" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                    <a class="m-portlet__nav-link btn btn-success margin-bottom-20" onclick="exportToExcel();"><i class="fa fa-file-excel-o"></i>&nbsp; Export Excel</a>
                                                    <!--<a href="#" style="padding: 9px 6px;" onclick="checkb4submit();" class="btn red pull-right margin-bottom-20"><i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;Download PDF</a>-->
                                                </div>
                                        </form>
                                    </div>
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

        function findcompany()
        {
            //var q = $('#salarymonthId').val();
            var month = $('#month').val();
            var Year = $('#Year').val();
            var q = month +'/'+Year;
            var urlp = '<?php echo $web_url; ?>admin/findPaidpaymentHistorycompany.php?cId=' + q;
            $.ajax({
                type: 'POST',
                url: urlp,
                success: function (data) {
                    $('#districtDiv').html(data);
                }
            }).error(function () {
                alert('An error occured');
            });
        }

        /* $("#frmSearch").keypress(function(event) { 
         if (event.keyCode === 13) { 
         $("#clickbutton").click(); 
     
         return false;
     
         }          
         }); */

        function PageLoadData(Page) {
            var bank = $('#bank').val();
            var companysalarymasterId = $('#companysalarymasterId').val();
            var month = $('#month').val();
            var Year = $('#Year').val();
            //var salarymonthId = $('#salarymonthId').val();
            var salarymonthId = month+'/'+Year;
            $('#loading').css("display", "block");
            $.ajax({
                type: "POST",
                url: "<?php echo $web_url; ?>admin/AjaxPaypaymentHistory.php",
                data: {action: 'ListUser', Page: Page, bank: bank, companysalarymasterId: companysalarymasterId, salarymonthId: salarymonthId},
                success: function (msg) {
                    $('#loading').css("display", "none");
                    $("#PlaceUsersDataHere").html(msg);
                },
            });
        }
        
        function deletedata(task, id)
        {
            var errMsg = '';
            if (task == 'Delete') {
                errMsg = 'Are you sure to delete?';
            }
            if (confirm(errMsg)) {
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxPaypaymentHistory.php",
                    data: {action: task, ID: id},
                    success: function (msg) {

                        $('#loading').css("display", "none");
                        window.location.href = '';

                        return false;
                    },
                });
            }
            return false;
        }
        
        // function checkb4submit()
        // {
        //     var bank = $('#bank').val();
        //     var companysalarymasterId = $('#companysalarymasterId').val();
        //     var month = $('#month').val();
        //     var Year = $('#Year').val();
        //     //var salarymonthId = $('#salarymonthId').val();
        //     var salarymonthId = month+'/'+Year;
        //     var strURL = "generatePaypaymentRepotPDF.php?bank=" + bank + "&companysalarymasterId=" + companysalarymasterId + "&salarymonthId=" + salarymonthId;
        //     window.open(strURL, '_blank');
        // }
        function exportToExcel()
        {
            var bank = $('#bank').val();
            var companysalarymasterId = $('#companysalarymasterId').val();
            var month = $('#month').val();
            var Year = $('#Year').val();
            //var salarymonthId = $('#salarymonthId').val(); 
            var salarymonthId = month+'/'+Year;
            var strURL = "generatemulticompanyPaypaymentRepotExcel.php?bank=" + bank + "&companysalarymasterId=" + companysalarymasterId + "&salarymonthId=" + salarymonthId;
            window.open(strURL, '_blank');
        }

    </script>
</body>
</html>