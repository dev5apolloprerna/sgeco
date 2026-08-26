<?php
ob_start();
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include('User_Paging.php');

if ($_POST['action'] == 'ListUser') {

    if (isset($_POST['employeeId'])) {
        if ($_POST['employeeId'] != '') {
            $filterstr = "SELECT * FROM employee where isDelete=0 and istatus=1 and employee.emp_name  like '%" . $_POST['employeeId'] . "%'";
        }
    }
    $countstr = "SELECT count(*) as `TotalRow` FROM employee where employee.emp_name like '%" . $_POST['employeeId'] . "%'";

    $resrowcount = mysqli_query($dbconn, $countstr);
    $resrowc = mysqli_fetch_array($resrowcount);
    $totalrecord = $resrowc['TotalRow'];
    $per_page = $cateperpaging;
    $total_pages = ceil($totalrecord / $per_page);
    $page = $_REQUEST['Page'] - 1;
    $startpage = $page * $per_page;
    $show_page = $page + 1;

    $filterstr = $filterstr . " LIMIT $startpage, $per_page";

    $resultfilter = mysqli_query($dbconn, $filterstr);
    if (mysqli_num_rows($resultfilter) > 0) {
        $i = 1;
?>
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>

        <table class="table table-bordered table-hover center table-responsive" width="100%" id="tableC">
            <thead class="tbg">
                <tr>
                    <th class="all">Employee Name</th>
                    <!-- <th class="all">PF <br /> No</th> -->
                    <th class="all">Employee <br /> Code</th>
                    <th class="all">Working Days</th>
                    <th class="all">Rate</th>
                    <th class="all">Overtime Hours</th>
                    <th class="all">Overtime Rate</th>
                    <th class="all">ADV ONE</th>
                    <th class="all">ADV ONE PAY</th>
                    <th class="all">ADV TWO</th>
                    <th class="all">ADV TWO PAY</th>
                    <th class="all">ADVANCE PAID BY BANK</th>
                    <th class="all">PF AMOUNT</th>
                    <th class="all">ESIC AMOUNT</th>
                    <th class="all">F.A</th>
                    <th class="all">T.A</th>
                    <th class="all">PAY IN CASH</th>
                    <th class="all">PAY DATE</th>
                    <!-- <th class="all">DA</th>
                    <th class="all">HRA</th>
                    <th class="all">National Holiday Payment</th> -->
                    <th class="all">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $salarymaster = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM companysalarymaster where companysalarymasterId='" . $_POST['companysalarymasterId'] . "' "));
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    if ($rowfilter['isExitEmployee'] == 1) {
                ?>
                        <tr style="background: #ffc107!important;">
                        <?php } else { ?>
                        <tr>
                        <?php } ?>
                        <td>
                            <?php echo ucwords(strtolower($rowfilter['emp_name']));
                            if (isset($rowfilter['strFatherName'])) {
                                echo " - " . $rowfilter['strFatherName'];
                            }  //$rowfilter['employeeId'] 
                            ?>
                        </td>
                        <!-- <td>
                            <?php echo $rowfilter['pfcode']; ?> 
                        </td> -->
                        <td><?php echo $rowfilter['employeecode'] ?> </td>
                        <td>
                            <input type="text" class="form-control" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> onkeypress="return isNumberKey(event)" name="workingdays_<?php echo $rowfilter['employeeId'] ?>" id="workingdays_<?php echo $rowfilter['employeeId'] ?>" />
                        </td>
                        <td>
                            <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="rate_<?php echo $rowfilter['employeeId'] ?>" id="rate_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" />
                        </td>
                        <td>
                            <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="othours_<?php echo $rowfilter['employeeId'] ?>" id="othours_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" />
                        </td>
                        <td>
                            <select name="otrate_<?php echo $rowfilter['employeeId'] ?>" id="otrate_<?php echo $rowfilter['employeeId'] ?>" <?= $rowfilter['isExitEmployee'] == 1 ? 'disabled' : ""; ?> class="form-control">
                                <option value="0">Select Employee Over Time Rate</option>
                                <!-- <option value="1">1</option>
                                <option value="1.5">1.5</option>
                                <option value="2">2</option> -->
                                <option value="1|8">1 for 8 hours</option>
                                <option value="1|12">1 for 12 hours</option>
                                <option value="1.5|8">1.5 for 8 hours</option>
                                <option value="1.5|12">1.5 for 12 hours</option>
                                <option value="2|8">2 for 8 hours</option>
                                <option value="2|12">2 for 12 hours</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="adv_<?php echo $rowfilter['employeeId'] ?>" id="adv_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" />
                        </td>
                        <td>
                            <input type="text" class="form-control" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="adv_one_paid_<?php echo $rowfilter['employeeId'] ?>" id="adv_one_paid_<?php echo $rowfilter['employeeId'] ?>" />
                        </td>
                        <td>
                            <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="adv_two_<?php echo $rowfilter['employeeId'] ?>" id="adv_two_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" />
                        </td>
                        <td>
                            <input type="text" class="form-control" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="adv_two_paid_<?php echo $rowfilter['employeeId'] ?>" id="adv_two_paid_<?php echo $rowfilter['employeeId'] ?>" />
                        </td>
                        <td><input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="advance_paid_by_bank_<?php echo $rowfilter['employeeId'] ?>" id="advance_paid_by_bank_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" /></td>
                        <td><input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="pf_amount_<?php echo $rowfilter['employeeId'] ?>" id="pf_amount_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" /></td>
                        <td><input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="esic_amount_<?php echo $rowfilter['employeeId'] ?>" id="esic_amount_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" /></td>
                        <td>
                            <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="fa_<?php echo $rowfilter['employeeId'] ?>" id="fa_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" />
                        </td>
                        <td>
                            <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> name="ta_<?php echo $rowfilter['employeeId'] ?>" id="ta_<?php echo $rowfilter['employeeId'] ?>" onkeypress="return isNumberKey(event)" />
                        </td>
                        <td>
                            <select class="form-control" style="width:60px;" name="pay_cash_<?php echo $rowfilter['employeeId'] ?>" <?= $rowfilter['isExitEmployee'] == 1 ? 'disabled' : ""; ?> id="pay_cash_<?php echo $rowfilter['employeeId'] ?>">
                                <option value="0">NO</option>
                                <option value="1">YES</option>
                            </select>
                        </td>
                        <td>
                            <input class="form-control" name="date_<?php echo $rowfilter['employeeId'] ?>" <?= $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ""; ?> id="date_<?php echo $rowfilter['employeeId'] ?>" onclick="getDateData('<?php echo $rowfilter['employeeId'] ?>');">

                        </td>
                        <!-- <td>
                            <input class="form-control" name="da_<?php echo $rowfilter['employeeId'] ?>" id="da_<?php echo $rowfilter['employeeId'] ?>" >

                        </td>
                        <td>
                            <input class="form-control" name="hra_<?php echo $rowfilter['employeeId'] ?>" id="hra_<?php echo $rowfilter['employeeId'] ?>" >

                        </td>
                        <td>
                            <input class="form-control" name="national_holiday_payment_<?php echo $rowfilter['employeeId'] ?>" id="national_holiday_payment_<?php echo $rowfilter['employeeId'] ?>">

                        </td> -->
                        <td>
                            <form role="form" method="POST" name="frmparameter" id="frmempSalary<?php echo $rowfilter['employeeId']; ?>">
                                <input type="hidden" value="AddmultycompanySalaryDetails" name="action" id="action">
                                <input type="hidden" value="<?php echo $salarymaster['companysalarymasterId'] ?>" name="companysalarymasterId" id="companysalarymasterId" />
                                <input type="hidden" value="<?php echo $salarymaster['salarypaiddate'] ?>" name="salarypaiddate" id="salarypaiddate" />

                                <input type="hidden" value="<?php echo $rowfilter['employeeId'] ?>" name="emp_id" id="emp_id" />
                                <input type="hidden" class="form-control" value="<?php echo $rowfilter['emp_name'] ?>" name="Ename_<?php echo $rowfilter['employeeId'] ?>" id="Ename_<?php echo $rowfilter['employeeId'] ?>" />
                                <?php if ($rowfilter['isExitEmployee'] == 0) { ?>
                                    <input class="btn blue margin-top-20" type="submit" id="Btnmybtn<?php echo $rowfilter['employeeId']; ?>" value="Submit" name="submit" onclick="javascript: return checkSubmit('<?php echo $rowfilter['employeeId']; ?>');">
                                <?php } ?>
                        </td>
                        </tr>
                        </form>
                    <?php
                    $i++;
                }
                    ?>
            </tbody>
        </table>

        <?php if ($totalrecord > $per_page) { ?>
            <div class="row">
                <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark" style="text-align: center;">
                    <div class="form-actions noborder">
                        <?php
                        echo '<div class="pagination">';

                        if ($totalrecord > $per_page) {
                            echo paginate($reload = '', $show_page, $total_pages);
                        }
                        echo "</div>";
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>


    <?php
    } else {
    ?>
        <div class="row">
            <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark">
                <div class="alert alert-info clearfix profile-information padding-all-10 margin-all-0 backgroundDark">

                    <h1 class="font-white text-center"> No Data Found ! </h1>
                </div>
            </div>
        </div>
<?php
    }
}


if ($_REQUEST['action'] == 'Delete') {
    $data = array(
        "isDelete" => '1',
        "strEntryDate" => date('d-m-Y H:i:s')
    );
    $where = ' where  	employeeId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
}
?>



<SCRIPT language=Javascript>
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode

        if (charCode > 31 && (charCode < 46 || charCode > 57))
            return false;

        return true;
    }

    function getDateData(empId) {
        $("#date_" + empId).datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            defaultDate: "now",
        });
    }
</SCRIPT>