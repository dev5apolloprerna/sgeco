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
        <title><?php echo $ProjectName; ?> | Add Employee Ledger</title>
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
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <a href="<?php echo $web_url; ?>admin/LocationEmployee.php">List Of Employee Ledger</a>
                                <i class="fa fa-circle"></i>
                            </li>
                            <li>
                                <span>Add Employee Ledger</span>
                            </li>
                        </ul>
                        <?php
                        // SELECT SUM(`credit`) as credit,SUM(`debit`) as debit FROM `ledger` WHERE `emp_id`=1
                        $emp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where  employeeId='" . $_REQUEST['token'] . "' order by  employeeId desc"));
                        ?>
                        <div class="page-content-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase"> <?php echo $emp['emp_name']; ?> Ledger</span>

                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="AddEmployeeLedger" name="action" id="action">
                                                <input type="hidden" name="emp_id" id="emp_id" value="<?php echo $_REQUEST['token'] ?>">
                                                <div class="form-body">
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Amount.</label>
                                                            <input name="Amount" id="Amount"  class="form-control" placeholder="Enter amount"  type="text">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Type</label><br/>
                                                            <select name="Type" id="Type"  class="form-control" required="">
                                                                <option value="">Select Type</option>
                                                                <option value="credit">credit</option>
                                                                <option value="debit">debit</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-md-4">
                                                            <label for="form_control_1">Comment</label>
                                                            <input name="Comment" id="Comment"  class="form-control" placeholder="Enter Comment"  type="text">
                                                        </div>
                                                    </div>
                                                    <div class="form-actions noborder">
                                                        <input class="btn blue " type="submit" id="Btnmybtn"  value="Add" name="submit">      
                                                        <button type="button" class="btn blue" onClick="checkclose();">Cancel</button>
                                                    </div>

                                                </div>


                                            </form>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="hidden" name="token" id="token" value="<?php echo $_REQUEST['token'] ?>">
                                                    <div  id="PlaceUsersDataHere">

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
            </div>
        </div>
        <?php include_once './footer.php'; ?>

        <script type="text/javascript">
            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
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
                        url: "<?php echo $web_url; ?>admin/AjaxEmployeeLedger.php",
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

            $('#frmparameter').submit(function (e) {

                e.preventDefault();
                var $form = $(this);
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: $('#frmparameter').serialize(),
                    success: function (response) {

                        console.log(response);
                        if (response == 1)
                        {
                            $('#loading').css("display", "none");

                            alert('Employee Added Sucessfully.');
                            window.location.href = '<?php echo $web_url; ?>admin/EmployeeLedger.php?token=<?php echo $_REQUEST['token'] ?>';

                                            }
                                        }

                                    });

                                });

                                function PageLoadData(Page) {
                                    var token = $('#token').val();
                                    $('#loading').css("display", "block");
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $web_url; ?>admin/AjaxEmployeeLedger.php",
                                        data: {action: 'ListUser', Page: Page, token: token},
                                        success: function (msg) {
                                            $("#PlaceUsersDataHere").html(msg);
                                            $('#loading').css("display", "none");
                                        },
                                    });
                                }// end of filter
                                PageLoadData(1);


        </script>



    </body>
</html>