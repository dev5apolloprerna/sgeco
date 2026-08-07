 <?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php')
?>
<!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> | View Multicompany Salary Details</title>
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
                                <span>View Multicompany Salary Details</span>
                            </li>
                        </ul>

                        <div class="page-content-inner">




                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of View Multicompany Salary Details</span>
                                        </div>
                                        <a href="multicompany_pdf1.php?token=<?php echo $_REQUEST['token'];?>" target="_"  class="btn red pull-right margin-bottom-20"><i class="fa fa-file-pdf-o"></i> Download PDF</a>
<!--                                         <a href="#" onclick="checkb4submit();" class="btn red pull-right margin-bottom-20"><i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;Download PDF</a>-->
                                    </div>
                                    <input type="hidden" name="companysalarymasterId" id="companysalarymasterId" value="<?php echo $_REQUEST['token'];?>" >
                                    <div class="portlet-body form">                                  
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
                                                     
                                                        function PageLoadData(Page) {


                                                            var Company = $('#Company').val();

                                                            var companysalarymasterId = $('#companysalarymasterId').val();
                                                            $('#loading').css("display", "block");
                                                            $.ajax({
                                                                type: "POST",
                                                                url: "<?php echo $web_url; ?>admin/Ajax-viewmulticompanys-Salarydetails.php",
                                                                data: {action: 'ListUser', Page: Page,companysalarymasterId: companysalarymasterId},
                                                                success: function (msg) {
                                                                    $('#loading').css("display", "none");
                                                                    $("#PlaceUsersDataHere").html(msg);

                                                                },
                                                            });
                                                        }// end of filter
                                                        PageLoadData(1);



                                                        function checkb4submit()
                                                        {
                                                            var FormDate = $('#FormDate').val();
                                                            var ToDate = $('#ToDate').val();
                                                            var Company = $('#Company').val();
                                                            var salarymasterId = $('#salarymasterId').val();
                                                            var strURL = "generatePDF.php?Company=" + Company + "&salarymasterId=" + salarymasterId;
                                                            window.open(strURL, '_blank');
                                                        }

        </script>
    </body>
</html>