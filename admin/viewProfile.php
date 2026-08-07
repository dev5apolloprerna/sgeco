<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
$filterstr = "SELECT * FROM admin where isDelete=0 and istatus=1";
$filterQuery=mysqli_query($dbconn,$filterstr);
$row = mysqli_fetch_array($filterQuery);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> | View Profile </title>
        <?php include_once 'include.php'; ?>
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
                            <!-- <li>
                                <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Salary Details</a>
                                <i class="fa fa-circle"></i>
                            </li> -->
                            <li>
                                <span>View Profile</span>
                            </li>
                        </ul>
                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase">View Profile</span>
                                            </div>
                                            <a  class="margin_fr_25 btn blue pull-right"   onclick="window.history.go(-1); return false;">Back</a>
                                            <a  class="btn blue pull-right" href="EditProfile.php?token=<?= $row['id'];?>">Edit Profile</a>
                                        </div>
                                        <div class="portlet-body form">
                                                <div class="row">
                                                    <h3 style="text-align: center;">Company Profile</h3>
                                                    <hr>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Company Profile:-</strong></label>
                                                        <?= $row['strCompanyName'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Address :-</strong></label>
                                                        <?= $row['strAddress'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Established Year :-</strong></label>
                                                        <?= $row['strEstablishedYear'];?>
                                                    </div>
                                                    
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Firm Registration No:-</strong></label>
                                                        <?= $row['strFirmRegistrationNo'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Business Partner :-</strong></label>
                                                        <?= $row['strBusinessPartner'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Contact No :-</strong></label>
                                                        <?= $row['PhoneNumber'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Mobile :-</strong></label>
                                                        <?= $row['MobileNumber'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Email ID :-</strong></label>
                                                        <?= $row['strEmail'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Type of Business:-</strong></label>
                                                        <?= $row['strTypeOfBusiness'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>GSTIN:-</strong></label>
                                                        <?= $row['iGSTIN'];?>
                                                    </div>

                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>PAN No:-</strong></label>
                                                        <?= $row['strPINno'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>PF Code No:-</strong></label>
                                                        <?= $row['strPFCodeNo'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>ESIC No:-</strong></label>
                                                        <?= $row['strESICNo'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>MSME Udyog Aadhar No:-</strong></label>
                                                        <?= $row['MSMEUdyogAadharNo'];?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <h3 style="text-align: center;">Bank Details</h3>
                                                    <hr>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Bank Name:-</strong></label>
                                                        <?= $row['strBankName'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Branch Name and Address:-</strong></label>
                                                        <?= $row['strBranchNameandAddress'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>IFSC Code:-</strong></label>
                                                        <?= $row['IFSCCode'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Type of Account:-</strong></label>
                                                        <?= $row['TypeOfAccount'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Account Number:-</strong></label>
                                                        <?= $row['AccountNumber'];?>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="form_control_1"><strong>Micr Code:-</strong></label>
                                                        <?= $row['MicrCode'];?>
                                                    </div>
                                                </div>                                            
                                            <!-- <div id="PlaceUsersDataHere">

                                            </div> -->
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
        <script type="text/javascript">
           /* function PageLoadData(Page) {
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/AjaxViewProfile.php",
                    data: {action: 'ListUser', Page: Page},
                    success: function (msg) {
                        $('#loading').css("display", "none");
                        $("#PlaceUsersDataHere").html(msg);
                    },
                });
            }
            PageLoadData(1);*/
        </script>
    </body>
</html>