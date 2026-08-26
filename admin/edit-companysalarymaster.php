<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$result = mysqli_query($dbconn, "SELECT * FROM `companysalarymaster`  where  isDelete='0'  and  istatus='1' and  companysalarymasterId=" . $_REQUEST['token'] . "");
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
        <meta charset="utf-8">

        <link rel="shortcut icon" href="images/favicon.png">
        <title> <?php echo $ProjectName ?> |Edit Company Salary</title>
        <?php include_once './include.php'; ?>   
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
    </head>

    <body class="page-container-bg-solid page-boxed">
        <?php
        include('header.php');
        ?>
        <div style="display: none; z-index: 10060;" id="loading">
            <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif">
        </div>
        <div class="page-container">        

            <div class="page-content">
                <div class="container">                    
                    <ul class="page-breadcrumb breadcrumb">
                        <li>
                            <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span> Edit Company Salary</span>

                        </li>
                    </ul>

                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo">
                                            <i class="icon-settings font-red-sunglo"></i>
                                            <span class="caption-subject bold uppercase">Edit Company Salary</span>
                                        </div>
                                    </div>
                                    <div class="portlet-body form">

                                        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                            <input type="hidden" value="EditCompanySalary" name="action" id="action">
                                            <input type="hidden" value="<?php echo $row['companysalarymasterId'] ?>" name="companysalarymasterId" id="companysalarymasterId">
                                            <div class="form-body">

                                                <div class="form-group">
                                                    <?php
                                                    $sql_menu = "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' order by  companymasterId asc";
                                                    $result_menu = mysqli_query($dbconn, $sql_menu);
                                                    $i = 1;
                                                    while ($row_menu = mysqli_fetch_array($result_menu)) {
                                                        $Client = "SELECT * FROM `multiycompanysalarymaster`  where isDelete='0'  and  istatus='1' and companymasterId='" . $row_menu['companymasterId'] . "' and companysalarymasterId='" . $_REQUEST['token'] . "'";
                                                        $resultC = mysqli_query($dbconn, $Client);
                                                        ?>
                                                        <input type='checkbox' name='Company<?php echo $row_menu['companymasterId']; ?>' value="<?php echo $row_menu['companymasterId']; ?>"<?php
                                                        if (mysqli_num_rows($resultC) > 0) {
                                                            echo "checked";
                                                        }
                                                        ?> id='Category<?php echo $row_menu['companymasterId']; ?>' />&nbsp <?php echo $row_menu['companyname']; ?>
                                                               <?php
                                                               $i++;
                                                               echo "<br />";
                                                           }
                                                           ?>  

                                                </div>                                                    
                                                <div class="form-group">
                                                    <label for="form_control_1">Salary month</label>
                                                    <input type="text"  id="month" value="<?php echo $row['month'] ?>" name="month" class="form-control" placeholder="Enter the Salary month" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="form_control_1">Salary Date From</label>
                                                    <input type="text"  id="fromdate" value="<?php echo $row['fromdate'] ?>"  name="fromdate" class="form-control date-picker" placeholder="Enter the from Date" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="form_control_1">To Salary Date</label>
                                                    <input type="text"  id="todate" value="<?php echo $row['todate'] ?>"  name="todate" class="form-control date-picker" placeholder="Enter the to Date" required>
                                                </div>
                                                <div class="form-group ">
                                                    <label for="form_control_1">Salary  Date</label>
                                                    <input type="text" id="salarypaiddate" value="<?php echo $row['salarypaiddate'] ?>" name="salarypaiddate" class="form-control date-picker" placeholder="Enter Salary Date" required=""/>
                                                </div>
                                                <div class="form-group">
                                                    <label for="DeductESIC">Deduct ESIC</label>
                                                    <select name="DeductESIC" id="DeductESIC" class="form-control" required>
                                                        <option value="YES" <?php echo $row['DeductESIC'] == 'YES' ? 'selected' : ''; ?>>Yes</option>
                                                        <option value="NO" <?php echo $row['DeductESIC'] == 'NO' ? 'selected' : ''; ?>>No</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="DeductPF">Deduct Provident Fund (PF)</label>
                                                    <select name="DeductPF" id="DeductPF" class="form-control" required>
                                                        <option value="YES" <?php echo $row['DeductPF'] == 'YES' ? 'selected' : ''; ?>>Yes</option>
                                                        <option value="NO" <?php echo $row['DeductPF'] == 'NO' ? 'selected' : ''; ?>>No</option>
                                                    </select>
                                                </div>
                                            </div>




                                            <div class="form-actions noborder">
                                                <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Save" name="submit">      
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
            window.location.href = '<?php echo $web_url; ?>admin/companysalarymaster.php';
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
                   // alert(response);
                    console.log(response);
                    if (response == 2)
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Employee Edited Sucessfully.');
                        window.location.href = '<?php echo $web_url; ?>admin/companysalarymaster.php';

                    } else if (response == 3) {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Already Exiest!');
                        window.location.href = '<?php echo $web_url; ?>admin/companysalarymaster.php';
                    } else {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Invalid Request.');
                        window.location.href = '<?php echo $web_url; ?>admin/companysalarymaster.php';
                    }
                }

            });
        });
    </script>
    <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {
            $('#month').datepicker({
                autoclose: true,
                minViewMode: 1,
                format: 'mm/yyyy'
            });
        });
        $(document).ready(function () {

            $("#fromdate").datepicker({
                format: 'dd-m-yyyy',
                autoclose: true,
                todayHighlight: true,
                defaultDate: "now",
            });

            $("#salarypaiddate").datepicker({
                format: 'dd-m-yyyy',
                autoclose: true,
                todayHighlight: true,
                defaultDate: "now",
            });

        });
        $(document).ready(function () {

            $("#todate").datepicker({
                format: 'dd-m-yyyy',
                autoclose: true,
                todayHighlight: true,
                defaultDate: "now",
            });

        });
    </script>

</body>
</html>
