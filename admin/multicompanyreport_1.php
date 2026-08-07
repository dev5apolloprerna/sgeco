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
                                              
                                                <div class="form-group col-md-3">
                                                    <?php
                                                    $queryCom = "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysql_error());
                                                    echo '<select class="form-control" name="Company[]" id="Company" multiple="multiple">';
                                                   
                                                    while ($rowCom = mysqli_fetch_array($resultCom)) {
                                                        echo "<option value='" . $rowCom['companymasterId'] . "'>" . $rowCom['companyname'] . "</option>";
                                                    }
                                                    echo "</select>";
                                                    ?>
                                                </div>


                                                <div class="form-group col-md-3">
                                                    <?php
                                                    $queryCat = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1'   GROUP BY salarymaster.month   order by  month asc";
                                                    $resultCat = mysqli_query($dbconn, $queryCat) or die(mysql_error());
                                                    echo '<select class="form-control" name="salarymasterId" id="salarymasterId">';
                                                    echo "<option value='' >Select salary Month</option>";
                                                    while ($rowCat = mysqli_fetch_array($resultCat)) {
                                                        echo "<option value='" . $rowCat['salarymasterId'] . "'>" . $rowCat['month'] . "</option>";
                                                    }
                                                    echo "</select>";
                                                    ?>
                                                </div>

                                                <div class="col-md-12">
                                                    <a href="#" class="btn blue margin-bottom-20" onclick="PageLoadData(1);">Search</a>
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
                border: 1px solid #91d8f7 !important;
                border-radius: 4px;
               /*color: #e31e24;*/
                font-size: 15px;
                font-weight: normal !important;
                text-transform: lowercase;

            }
        </style>
        <link href="assets/bootstrap-multiselect.css" rel="stylesheet" type="text/css"/>
        <script src="assets/bootstrap-multiselect.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>

        <script>
            
                                                        $(document).ready(function () {
                                                             $('#Company').multiselect({
                                                                        nonSelectedText: 'Select Any Company',
                                                                        includeSelectAllOption: true,
                                                                        buttonWidth: '100%',
                                                                    });

                                                            $("#FormDate").datepicker({
                                                                format: 'dd-m-yyyy',
                                                                autoclose: true,
                                                                todayHighlight: true,
                                                                defaultDate: "now",
                                                            });
                                                        });
                                                        $(document).ready(function () {

                                                            $("#ToDate").datepicker({
                                                                format: 'dd-m-yyyy',
                                                                autoclose: true,
                                                                todayHighlight: true,
                                                                defaultDate: "now",
                                                            });

                                                        });
                                                        function PageLoadData(Page) {

                                                            var FormDate = $('#FormDate').val();
                                                            var ToDate = $('#ToDate').val();
                                                            var Company = $('#Company').val();
                                                            var salarymasterId = $('#salarymasterId').val();
                                                            $('#loading').css("display", "block");
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "<?php echo $web_url; ?>admin/Ajaxmulticompanyreport_2.php",
                                                                data: {action: 'ListUser', Page: Page, FormDate: FormDate, ToDate: ToDate, Company: Company, salarymasterId: salarymasterId},
                                                                success: function (msg) {
                                                                    $('#loading').css("display", "none");
                                                                    $("#PlaceUsersDataHere").html(msg);

                                                                },
                                                            });
                                                        }// end of filter
                                                        PageLoadData(1);



                                                        function checkb4submit()
                                                        {
                                                            
                                                            var Company = $('#Company').val();
                                                            var salarymasterId = $('#salarymasterId').val();
                                                            var strURL = "multicompanygeneratePDF.php?Company=" + Company + "&salarymasterId=" + salarymasterId;
                                                            window.open(strURL, '_blank');
                                                        }

        </script>
    </body>
</html>