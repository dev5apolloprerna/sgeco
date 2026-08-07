<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
$result = mysqli_query($dbconn, "SELECT * FROM `admin` WHERE `id`='" . $_REQUEST['token'] . "'");
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
} else {
    echo 'somthig going worng! try again';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> |  Edit Profile </title>
        <?php include_once 'include.php'; ?>
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
                        

                        <div class="page-content-inner">
                            <div class="row">
                            <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption grey-gallery">
                                                <i class="icon-settings grey-gallery"></i>
                                                <span class="caption-subject bold uppercase"> Edit Profile</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="editProfile" name="action" id="action">
                                                <input type="hidden" value="<?= $row['id']?>" name="id" id="id">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <h4 class="bold text-center">Company Details</h4>
                                                        </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Company Name</label>
                                                        
                                                        <input name="strCompanyName" id="strCompanyName" value="<?= $row['strCompanyName']?>" class="form-control" required="" placeholder="Enter Company Name">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Address</label>
                                                        <textarea name="strAddress" id="strAddress"  class="form-control" placeholder="Enter Address" type="text" required="" rows=3><?= $row['strAddress']?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Established Year</label>
                                                        <input name="strEstablishedYear" id="strEstablishedYear"  class="form-control" placeholder="Enter Established Year" value="<?= $row['strEstablishedYear']?>" type="text" >
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Firm Registration No</label>
                                                        <input name="strFirmRegistrationNo" id="strFirmRegistrationNo"  class="form-control" placeholder="Enter Firm Registration No" value="<?= $row['strFirmRegistrationNo']?>" type="text" >
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Business Partner</label>
                                                        <textarea name="strBusinessPartner" id="strBusinessPartner" class="form-control" placeholder="Enter Business Partner" type="text" rows=3><?= $row['strBusinessPartner']?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Phone No.</label><div id="errordiv"></div>
                                                        <input name="PhoneNumber" id="PhoneNumber"  class="form-control" value="<?= $row['PhoneNumber']?>" placeholder="Enter The Phone No." pattern="[0-9]{10}" type="text">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Mobile No.</label><div id="errordiv"></div>
                                                        <input name="MobileNumber" id="MobileNumber"  value="<?= $row['MobileNumber']?>" class="form-control" placeholder="Enter The Mobile No." pattern="[0-9]{10}" type="text">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Email</label>
                                                        <input name="Email" id="Email" value="<?= $row['strEmail']?>" class="form-control" placeholder="Enter The Email Address"  type="email">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Type of Business</label>
                                                        <textarea name="strTypeOfBusiness" id="strTypeOfBusiness"  class="form-control" placeholder="Enter Type Of Business" type="text" rows=3><?= $row['strTypeOfBusiness']?></textarea>
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">GSTIN</label>
                                                        <input type="tyext" class="form-control" name="iGSTIN" id="iGSTIN" required="" value="<?= $row['iGSTIN']?>">
                                                    </div>


                                                    <div class="form-group col-md-3" >
                                                        <label for="form_control_1">PAN No</label>
                                                        <input type="tyext" class="form-control" name="strPINno" id="strPINno" required="" value="<?= $row['strPINno']?>">
                                                    </div>

                                                    <div class="form-group col-md-3" >
                                                        <label for="form_control_1">PF Code No</label>
                                                        <input type="tyext" class="form-control" name="strPFCodeNo" id="strPFCodeNo" required="" value="<?= $row['strPFCodeNo']?>">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-3" >
                                                        <label for="form_control_1">ESIC No</label>
                                                        <input type="tyext" class="form-control" name="strESICNo" id="strESICNo" required="" value="<?= $row['strESICNo']?>">
                                                    </div>
                                                    <div class="form-group col-md-3" >
                                                        <label for="form_control_1">MSME Udyog Aadhar No</label>
                                                            <input type="tyext" class="form-control" name="MSMEUdyogAadharNo" id="MSMEUdyogAadharNo" required="" value="<?= $row['MSMEUdyogAadharNo']?>">
                                                    </div>

                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <h4 class="bold text-center">Bank Details</h4>
                                                        </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Bank Name</label>
                                                        <input type="text" class="form-control" name="strBankName" id="strBankName" value="<?= $row['strBankName']?>" required>
                                                    </div> 
                                                      
                                                      <div class="form-group col-md-3">
                                                        <label for="form_control_1">Branch Name and Address</label>
                                                        <textarea type="text" class="form-control" name="strBranchNameandAddress" id="strBranchNameandAddress" rows=3 required>
                                                        <?= $row['strBranchNameandAddress']?></textarea>
                                                    </div> 
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">IFSC Code</label>
                                                        <input type="text" class="form-control" name="IFSCCode" id="IFSCCode" value="<?= $row['IFSCCode']?>" required>
                                                    </div> 
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Type of Account</label>
                                                        <input type="text" class="form-control" name="TypeOfAccount" id="TypeOfAccount" value="<?= $row['TypeOfAccount']?>" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Account Number</label>
                                                        <input type="text" class="form-control" name="AccountNumber" id="AccountNumber" value="<?= $row['AccountNumber']?>" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="form_control_1">Micr Code</label>
                                                        <input type="text" class="form-control" name="MicrCode" id="MicrCode" value="<?= $row['MicrCode']?>" required>
                                                    </div>  
                                                </div>
                                                </div>
                                                <div class="form-actions noborder">
                                                    <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">      
                                                    <button type="button" class="btn blue margin-top-20" onClick="checkclose();">Cancel</button>
                                                </div>
                                            </form>
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

            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/viewProfile.php';
            }

            $('#frmparameter').submit(function (e) {

                e.preventDefault();
                var $form = $(this);
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: $('#frmparameter').serialize(),
                    success: function (response) {
                       
                        if (response != 0)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Update Sucessfully.');
                            window.location.href = '<?php echo $web_url; ?>admin/viewProfile.php';
                        }else{
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid Request.');
                            window.location.href = '<?php echo $web_url; ?>admin/viewProfile.php';
                        }
                    }

                });
            });



        </script>
    </body>
</html>