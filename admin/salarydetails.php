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
        <title><?php echo $ProjectName; ?> | Salary Details </title>
        <?php include_once './include.php'; ?>
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
                                <span>Salary Details</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Salary Details</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Name</label>
                                                    <input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" placeholder="Search Employee Name " />
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="form_control_1">Company</label><br/>
                                                    <?php
                                                    $queryCom = "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                    echo '<select class="form-control" name="companyId" id="companyId"  onchange="findsalarymasterId();" >';
                                                    echo "<option value=''>Select company Name</option>";
                                                    while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                        echo "<option value='" . $rowCom ['companymasterId'] . "'>" . $rowCom ['companyname'] . "</option>";
                                                    }
                                                    echo "</select>";
                                                    ?>
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
                                                        $starting_year = date('Y', strtotime('-5 year'));
                                                        //$ending_year = date('Y', strtotime('+5 year'));
                                                        $current_year = date('Y');
                                                        for ($starting_year; $starting_year <= $current_year; $starting_year++) {
                                                            echo '<option value="' . $starting_year . '"';
                                                            if ($starting_year == $current_year) {
                                                                echo ' selected="selected"';
                                                            }
                                                            echo ' >' . $starting_year . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <!-- <div class="form-group col-md-3">
                                                    <label for="form_control_1">Salary Month</label>
                                                    <div id="districtDiv">
                                                        <?php
                                                        $queryCom = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' GROUP BY salarymaster.month order by  month asc";
                                                        $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                        echo '<select class="form-control" name="salarymasterId" id="salarymasterId" >';
                                                        echo "<option value=''>Select salary Month</option>";
                                                        while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                            echo "<option value='" . $rowCom ['salarymasterId'] . "'>" . $rowCom ['month'] . "</option>";
                                                        }
                                                        echo "</select>";
                                                        ?>
                                                    </div>
                                                </div> -->
                                                <div class="form-actions  col-md-3">
                                                    <a href="#" class="btn blue pull-left margin-top-20" id="clickbutton" onclick="PageLoadData(1);">Search</a>
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
        </div>
        <?php include_once './footer.php'; ?>

        <style>

            .multiselect
            {
                display: block;
                height: 35px;
                padding: 6px;

                text-align: left !important;
                line-height: 1.42857;
                /* color: #DFDFDF; */
                background-color: #fff;
                background-image: none;
                border: 1px solid #51c6dd !important;
                border-radius: 4px;
                color: #51c6dd;
                font-size: 15px;
                font-weight: normal !important;
                text-transform: lowercase;

            }
        </style>

        <script type="text/javascript">

            function findsalarymasterId()
            {
                var q = $('#companyId').val();

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
                        url: "<?php echo $web_url; ?>admin/Ajaxsalarydetails.php",
                        data: {action: task, ID: id},
                        success: function (msg) {

                            $('#loading').css("display", "none");
                            PageLoadData(1);

                            return false;
                        },
                    });
                }
                return false;
            }


            $("#frmSearch").keypress(function (event) {
                if (event.keyCode === 13) {
                    $("#clickbutton").click();

                    return false;

                }
            });
            function PageLoadData(Page) {
                var Search_Txt = $('#Search_Txt').val();
                var companyId = $('#companyId').val();
                var month = $('#month').val();
                var Year = $('#Year').val();
                //var salaryId = $('#salarymasterId').val();
                var salaryId = month +'/'+Year;
                /*var salaryId = getsalaryID(salarymonthYear);
                alert(salaryId);*/
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/Ajaxsalarydetails.php",
                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt, companyId: companyId, salaryId: salaryId},
                    success: function (msg) {
                        $("#PlaceUsersDataHere").html(msg);
                        $('#loading').css("display", "none");
                    },
                });
            }
            PageLoadData(1);

            /*function getsalaryID(salarymonthYear){
                var companyId = $('#companyId').val();
                //var urlp = '<?php echo $web_url; ?>Employee/findCity.php?sId=' + q;
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/findsalarymonth.php?salarymonthYear="+salarymonthYear+"&companyId="+companyId,
                    success: function (msg) {
                        return msg;
                    },
                });
            }*/
        </script>

        <script type="text/javascript">

            $(document).ready(function () {
                $('#select_all').on('click', function () {
                    if (this.checked) {
                        $('.checkbox').each(function () {
                            this.checked = true;
                        });
                    } else {
                        $('.checkbox').each(function () {
                            this.checked = false;
                        });
                    }
                });

                $('.checkbox').on('click', function () {
                    if ($('.checkbox:checked').length == $('.checkbox').length) {
                        $('#select_all').prop('checked', true);
                    } else {
                        $('#select_all').prop('checked', false);
                    }
                });
            });
        </script>

    </body>
</html>

