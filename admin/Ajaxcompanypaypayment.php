<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    if($_POST['bank'] != ""){
        $where = " and employee.bankid= '" . $_POST['bank'] . "'";
        if ($_POST['bank'] == 3) {
            $where = " and employee.bankid not in (1,2)";
        }
    }
    $filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and iPaymentStatus=0 and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
    $countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where  salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') " . $where . " and iPaymentStatus=0 and salarydetails.workingdays > 0  and employee.isDelete=0 and employee.istatus=1";

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
        <form name="frmparameter"  id="frmparameter" >
            <hr />
            <div class="row">
            <input type="hidden" value="companypaidpayement" name="action" id="action">
            <input type="hidden" name="Companyid" id="Companyid" value="<?= $_POST['Company']; ?>">
            <input type="hidden" name="salaryId" id="salaryId" value="<?= $_POST['salaryId']; ?>">
            <div class="form-group col-md-2">
                <label for="form_control_1">Date</label>
                <input class="form-control" name="strDate" id="strDate" placeholder="Payment Date" required="">
            </div>
            <div class="form-group col-md-2">
                <label for="form_control_1">Mode</label>
                <select class="form-control" name="iMode" id="iMode" required="">
                    <option value="">Select Mode</option>
                    <option value="1">Cash</option>
                    <option value="2">Bank</option>
                </select>
            </div>
            <div id="bankdetails" style="display:none">
                <div class="form-group col-md-2">
                    <label for="form_control_1">Bank</label>
                    <?php $queryCom = "SELECT * FROM `bankmaster`  where isDelete='0' and  istatus='1' order by  bankmasterId asc";
                    $resultCom = mysqli_query($dbconn, $queryCom) or die(mysqli_error($dbconn));
                    echo '<select class="form-control" name="bank" id="bank">';
                    echo "<option value='' >Select Bank </option>";
                    while ($rowCom = mysqli_fetch_array($resultCom)) {
                        echo "<option value='" . $rowCom['bankmasterId'] . "'>" . $rowCom['bankname'] . "</option>";
                    }
                    echo "</select>";
                ?>
                </div>
                <div class="form-group col-md-2">
                    <label for="form_control_1">Transaction No / Cheque No</label>
                    <input class="form-control" name="strTransactionNo" id="strstrTransactionNo" placeholder="Transaction Number">
                </div>
            </div>
            <div class="form-group col-md-2">
                <label for="form_control_1" id="txtAmt">Amount</label>
                <input class="form-control" name="iAmount" id="iAmount" required="" placeholder="Pay Amount">
            </div>
            <div class="form-group col-md-2">
                <label for="form_control_1">Total </label>
                <input class="form-control" name="iTotal" id="iTotal" value="0" readonly="">
            </div>
        </div>
            <div class="row">
                <div class="form-group col-md-2">
                    <input type="submit" name="deletedata" class="btn blue" value="Submit">
                </div>
            </div>
            <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
    
            <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
    
                <thead class="tbg">
                    <tr>
                        <th class="desktop">
                            <div class="md-checkbox">
                                <input type="checkbox"  onclick="javascript:CheckAll();" id="check_listall" class="md-check" value="">
                                <label for="check_listall">
                                    <span></span>
                                    <span class="check"></span>
                                    <span class="box"></span>
                                </label>
                            </div>
                        </th>
                        <th>Sr.No</th>
                        <th class="all">Name</th>
                        <th class="desktop">Balance</th>
                        <th class="desktop">IFSC Code</th>
                        <?php if ($_POST['bank'] == 3) { ?>
                        <th class="desktop">Bank Name</th>
                        <?php } ?>
                        <th class="desktop">Bank A/c No</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $Total = array(0, 0, 0, 0);
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        ?>
                        <tr>
                            <td>
                                <div class="md-checkbox">
                                    <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" onclick="getBalance(<?= $rowfilter['netamountpaid'] ?>,<?= $i ?>);" class="md-check" value="<?php echo $rowfilter['employeeId']; ?> ">
                                    <label for="check_list<?php echo $i; ?>">
                                        <span></span>
                                        <span class="check"></span>
                                        <span class="box"></span></label>
                                </div>
                            </td> 
                            <td>
                                <?php echo $i; ?>
                                <input type="hidden" name="salarydetailsId_<?php echo trim($rowfilter['employeeId']); ?>" id="salarydetailsId" value="<?= $rowfilter['salarydetailsId']?>">
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];}?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo number_format(ceil($rowfilter['netamountpaid']),2);
                                    $Total[0] = $rowfilter['netamountpaid'] + $Total[0];
                                    ?>
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?>
                                </div>
                            </td>
                            <?php
                            if ($_POST['bank'] == 3) {
                            $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $rowfilter['bankid'] . "'"));
                            ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    if ($bank['bankname'] == 'Other') {
                                        echo $rowfilter['otherbankname'];
                                    } else {
                                        echo $bank['bankname'];
                                    }
                                    ?> 
                                </div>
                            </td> 
                            <?php } ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo str_replace('A/C. ','',$rowfilter['accountno']); ?> 
                                </div>
                            </td> 
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
            <h4><strong>Total:</strong> <?php echo number_format($Total[0],2); ?></h4>
            <input type="hidden" id="Balance" value="<?= $Total[0] ?>">
        </form>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $('#tableC').DataTable({
                });
            });
        </script>
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

if ($totalrecord > $per_page) { ?>
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

<script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script>

$(document).ready(function () {
    $("#strDate").datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
    });
});

$("#iMode").change(function (){
   var iMode = $(this).val();
   if(iMode == 2){
       $("#bankdetails").show();
       $("#txtAmt").html('Cheque Amount');
   } else {
       $("#bankdetails").hide();
       $("#txtAmt").html('Amount');
   }
});
function getBalance(Bal,counter){
    var iTotal = $("#iTotal").val();
    if(iTotal == ""){
        iTotal = 0;
    }
    if ($('#check_list'+counter).is(":checked")){
        iTotal = (iTotal * 1) + (Bal * 1);
    } else {
        iTotal = (iTotal * 1) - (Bal * 1);
    }
    $("#iTotal").val(iTotal);
}

function CheckAll()
{
    var iTotal = $("#iTotal").val();
    if(iTotal == ""){
        iTotal = 0;
    }
    var Balance = $("#Balance").val();
    if ($('#check_listall').is(":checked"))
    {
        // alert('cheked');
        $('input[type=checkbox]').each(function () {
            $(this).prop('checked', true);
        });
        iTotal = Balance;
        $("#iTotal").val(iTotal);
    } else
    {
        //alert('cheked fail');
        $('input[type=checkbox]').each(function () {
            $(this).prop('checked', false);
        });
        iTotal = 0;
        $("#iTotal").val(iTotal);
    }
}

function ViewExportList(){
    window.location.href = 'ViewExportList.php?token=1';
}
$('#frmparameter').submit(function (e) {
    var iTotal = $("#iTotal").val();
    var iAmount = $("#iAmount").val();
    if((iTotal * 1) == (iAmount * 1)){
        e.preventDefault();
        var $form = $(this);
        $('#loading').css("display", "block");
        $.ajax({
            type: 'POST',
            url: 'paymentquerydata.php',
            data: $('#frmparameter').serialize(),
            success: function (response) {
                console.log(response);
                if (response == 1)
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Payment Paid Sucessfully.');
                    window.location.href = '';
                } else
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Somthing want wrong, Please Try Again.');
                    window.location.href = '';
                }
            }
    
        });
    } else {
        alert("Amount is not match with total");
        return false;
    }
});
</script>