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
        <title><?php echo $ProjectName; ?> | View Company Paypayment Employee List </title>
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
                                <span>View Company Paypayment Employee List</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">

                            <div class="col-md-12">
                                <div class="portlet light " style="width: 100%;float: left;">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">List of Company Paypayment Employee</span>
                                        </div>
                                        <a style="margin-left: 10px;" class="m-portlet__nav-link btn btn-success margin-bottom-20" onclick="exportToExcel();"><i class="fa fa-file-excel-o"></i>&nbsp; Export Excel</a>
                                        <a href="#" style="padding: 9px 6px;" onclick="checkb4submit();" class="btn red  margin-bottom-20"><i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;Download PDF</a>
                                                    
                                        <a href="#" onclick="javascript: history.go(-1);" class="btn blue" style="float: right;" title="Back">Back</a>
                                        <input type="hidden" name="token" id="token" value="<?= $_REQUEST['token']; ?>">
                                    </div>
                                    <?php 
                                        $data = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT iCompanyPaymentId,iCompanySalaryMasterId,companypaymentMaster.salarymonth,strPaymentDate,iPaymentMode,
                                        iBank,strTransactionNo,iAmount,companymaster.companyname,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId=companypaymentMaster.iBank) as bankName FROM `companypaymentMaster` inner join companymaster on companypaymentMaster.iCompanySalaryMasterId=companymaster.companymasterId where companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1 and iCompanyPaymentId='".$_REQUEST['token']."'"));
                                    ?>
                                    <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
                                        <thead class="tbg">
                                            <tr>
                                                <th class="all">Company Name</th>
                                                <th class="desktop">Salary Month</th>
                                                <th class="desktop">Amount</th>
                                                <th class="desktop">Paid Date</th>
                                                <th class="desktop">Mode</th>
                                                <th class="desktop">Bank</th>
                                                <th class="desktop">Cheque / Transaction No</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?= $data['companyname'] ?></td>
                                                <td><?= $data['salarymonth'] ?></td>
                                                <td><?= $data['iAmount'] ?></td>
                                                
                                                <td><?= $data['strPaymentDate'] ?></td>
                                                <td><?= $data['iPaymentMode'] ? 'Cash' : 'Bank' ?></td>
                                                <td><?= $data['bankName'] ?></td>
                                                <td><?= $data['strTransactionNo'] ?></td>
                                            </tr>
                                        </tbody>    
                                    </table>
                                    <div id="PlaceUsersDataHere" style="width: 100%;float: left;">
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

        <script type="text/javascript">

            function PageLoadData(Page) {
                var token= $("#token").val();
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxViewCompanyPaypaymentHistory.php",
                    data: {action: 'ListUser', Page: Page, token: token},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        $("#PlaceUsersDataHere").html(msg);
                        
                    },
                });
            }// end of filter
            PageLoadData(1);
            
        function checkb4submit()
        {
            var token = $('#token').val();
            var strURL = "generateViewCompanyPaypaymentHistoryPDF.php?token=" + token;
            window.open(strURL, '_blank');
        }
        
        function exportToExcel()
        {
            var token = $('#token').val();
            var strURL = "generateViewCompanyPaypaymentHistoryExcel.php?token=" + token;
            window.open(strURL, '_blank');
        }
        
        </script>

    </body>
</html>