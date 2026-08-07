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
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
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
                                                    <label for="form_control_1">Salary Month</label>
                                                    <?php
                                                    $queryCom = "SELECT * FROM `companysalarymaster`  where isDelete='0'  and  istatus='1' GROUP BY companysalarymaster.month order by  month asc";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                    echo '<select class="form-control" name="salarymonthId" id="salarymonthId" required=""  onchange="findcompany();">';
                                                    echo "<option value=''>Select salary Month</option>";
                                                    while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                        echo "<option value='" . $rowCom ['month'] . "'>" . $rowCom ['month'] . "</option>";
                                                    }
                                                    echo "</select>";
                                                    ?>
                                                </div> -->
                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">Month</label><br/>
                                                    <select name="month" id="month" required size='1' class="form-control" onchange="findcompany();">
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
                                                <div class="form-group col-md-3">
                                                    <label for="form_control_1">Year</label><br/>
                                                    <select name="Year" id="Year" required class="form-control" onchange="findcompany();">
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
                                                <div class="form-group col-md-3">
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
                                                            //$companymasterId = rtrim($companymasterId, ", ");
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 margin-top-20">
                                                    <a href="#" style="padding: 9px 7px;" class="btn blue margin-bottom-20" id="clickbutton" onclick="PageLoadData(1);">Search</a>
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
        <div class="modal fade" id="Edit-company" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- <button type="button" class="close" data-dismiss="modal" style="display: contents;">X</button> -->
                        <h4 style="width: 80%;">Edit Multi Company Report</h4>
                        <div class="close_btn_f">
                            <button type="button" data-dismiss="modal">X</button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="portlet-body form">
                            <form  role="form"  method="POST"  action="" name="frmparameterEditmulticompany"  id="frmparameterEditmulticompany" enctype="multipart/form-data">
                                <input type="hidden" value="Editmulticompany" name="action" id="action">
                                <input type="hidden" name="multicompanyid" id="multicompanyid" value="">
                                <div class="modal-body" id="placeEditbusinessid">
                                </div>
                                <div class="form-actions noborder">
                                    <button class="btn blue margin-top-20" type="button" id="Btnmybtn" onclick="return editbusinessid();"  name="Btnmybtn"> Submit</button>
                                    <button type="button" class="btn blue margin-top-20" onClick="checkclose();">Cancel</button>
                                </div>
                            </form>
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
        <script>

            function findcompany()
            {
                //var q = $('#salarymonthId').val();
                var month = $('#month').val();
                var Year = $('#Year').val();
                var q = month+'/'+Year;
                var urlp = '<?php echo $web_url; ?>admin/findcompany.php?cId=' + q;
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

            $("#frmSearch").keypress(function (event) {
                if (event.keyCode === 13) {
                    $("#clickbutton").click();
                    return false;
                }
            });

            function PageLoadData(Page) {
                var companysalarymasterId = $('#companysalarymasterId').val();
                var month = $('#month').val();
                var Year = $('#Year').val();
                //var salarymonthId = $('#salarymonthId').val();
                var salarymonthId = month+"/"+Year;
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/ajax-edit-multicompanydetails.php",
                    data: {action: 'ListUser', Page: Page, companysalarymasterId: companysalarymasterId, salarymonthId: salarymonthId},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        $("#PlaceUsersDataHere").html(msg);
                    },
                });
            }

            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/viewmulticompanyreport.php';
            }

            function deletedata(task, multicompanyid)
            {
                var errMsg = '';
                if (task == 'Delete') {
                    errMsg = 'Are you sure to delete?';
                }
                if (confirm(errMsg)) {
                    $('#loading').css("display", "block");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $web_url; ?>admin/ajax-edit-multicompanydetails.php",
                        data: {action: task, ID: multicompanyid},
                        success: function (msg) {
                            $('#loading').css("display", "none");
                            PageLoadData(1);
                            return false;
                        },
                    });
                }
                return false;
            }

        </script>
        <script>

            function setEditdata(multicompanyid)
            {
                $('#Edit-company').modal();
                $('#multicompanyid').val(multicompanyid);
                loadEditbusinessidModal(multicompanyid);
                return false;
            }
            function loadEditbusinessidModal(multicompanyid)
            {
                $('#loading').show();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/ajaxeditmulticompanydetails.php",
                    data: {action: 'ListUser', multicompanyid: multicompanyid},
                    success: function (msg)
                    {
                        $('#loading').hide();
                        $("#placeEditbusinessid").html(msg);
                        
                    },
                });
                return false;
            }

            function editbusinessid()
            {
                $('#loading').show();
                var formData = new FormData($('form#frmparameterEditmulticompany')[0]);
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/querydata.php",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (response) {
                        if (response == 2)
                        {
                            $('#loading').css("display", "none");
                            alert('Edited Sucessfully.');
                            $('#Edit-company').modal('toggle');
                            PageLoadData(1);
                        } else {
                            $('#loading').css("display", "none");
                            alert('Invalid Request.');
                        }
                    }
                });
                return false;
            }
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