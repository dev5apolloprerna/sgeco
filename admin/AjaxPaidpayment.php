<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {

    if ($_REQUEST['companysalarymasterId'] != NULL && $_REQUEST['salarymonthId'] != NULL) {
        $where = " and multicompany.companysalarymasterId = " . $_POST['companysalarymasterId'] . " " and "  
        salarymaster.month = " . $_POST['salarymonthId'] . " ";

        if ($_REQUEST['bank'] != NULL) {
            if ($_REQUEST['bank'] == 3) {
                $where .= " and employee.bankid not in (1,2)  and multicompany.pay_cash='0'";
                //$where .= " and employee.bankid not in (2)  and multicompany.pay_cash='0'";
            } else if ($_REQUEST['bank'] == 1 || $_REQUEST['bank'] == 2) {
                $where .= " and employee.bankid = " . $_POST['bank'] . "  and multicompany.pay_cash='0'";
            } else {
                $where .= " and multicompany.pay_cash='1'";
            }
        }
    }
    
    $filterstr = "SELECT multicompany.multicompanyid,multicompany.balance1,companysalarymaster.companysalarymasterId,employee.employeeId,employee.emp_name
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    employee.bankid) as BankName,
    (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    paymentMaster.iBank) as PaidBankName,paymentMaster.strTransactionNo,employee.ifsccode, employee.emp_other_info,employee.accountno 
    ,(select companysalarymaster.month from companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId) as DisplayMonth,
    CASE
        WHEN paymentMaster.iPaymentMode = 1 THEN 'Cash'
        ELSE 'Bank'
    END as iPaymentMode,
    multicompany.strPaymentDate,multicompany.iPaymentId
    FROM `multicompany`,employee,companysalarymaster,paymentMaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and  paymentMaster.iPaymentId=multicompany.iPaymentId and
    multicompany.emp_id = employee.employeeId  " . $where . "  and multicompany.isDelete='0' and multicompany.iPaymentStatus=1 and  multicompany.istatus='1' ORDER BY employee.emp_name asc";
    //exit;
    $countstr = "SELECT count(*) as TotalRow FROM `multicompany`,employee,companysalarymaster where companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and multicompany.emp_id = employee.employeeId  " . $where . " and multicompany.iPaymentStatus=1  and multicompany.isDelete='0'  and  multicompany.istatus='1'";
}

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
        <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
            <thead class="tbg">
                <tr>
                    
                    <th>Sr.No</th>
                    <th class="all">Name</th>
                    <th class="desktop">Balance</th>
                    <?php if ($_REQUEST['bank'] == 3 ) {  ?>
                        <th class="desktop">Bank Name</th>
                    <?php } ?>
                    <th class="desktop">IFSC Code</th>
                    <th class="desktop">Bank A/c No</th>
                    <th class="desktop">Month</th>
                    <th class="desktop">Mode</th>
                    <th class="desktop">Bank</th>
                    <th class="desktop">Transaction No / Cheque No</th>
                    <th class="desktop">Paid Date</th>
                    <th class="desktop">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $Total = array(0, 0, 0, 0);
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
                   where salarymaster.salarymasterId = salarydetails.salaryId
                   and salarymaster.month = '" . $_POST['salarymonthId'] . "'  
                   and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
                   multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_POST['companysalarymasterId'] . ")
                   and salarydetails.emp_id = " . $rowfilter['employeeId'] . "  and salarymaster.isDelete=0 ";
                    $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));
                    $Balance = $rowfilter['balance1'] - $empdata['PaidAmount'];
                    if ($Balance > 0) {
                        ?>
                        <tr>
                            <td><?php echo $i; ?>
                                <input type="hidden" name="multicompanyid[]" id="multicompanyid" value="<?= $rowfilter['multicompanyid']?>">
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];}  ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo number_format($Balance,2);
                                    $Total[0] = $Balance + $Total[0];
                                    ?>
                                    <!--<input type="hidden" id="Balance_<?= $rowfilter['employeeId'] ?>" value="<?= $Balance ?>">-->
                                </div>
                            </td>
                            <?php if ($_REQUEST['bank'] == 3) { ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php 
                                if ($_REQUEST['bank'] == 0){
                                    echo "Cash";
                                }else{
                                    echo $rowfilter['BankName']; 
                                }
                                ?> 
                                </div>
                            </td> 
                            <?php } ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo str_replace('A/C. ','',$rowfilter['accountno']); ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['salarymonth']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['iPaymentMode']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['PaidBankName']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['strTransactionNo']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo date('d-m-Y',strtotime($rowfilter['strPaymentDate'])); ?> 
                                </div>
                            </td> 
                            <td>
                                <a class="btn blue" onclick="javascript: return deletedata('Delete', <?= $rowfilter['iPaymentId'];?>,<?= $rowfilter['multicompanyid']; ?>,'<?= $Balance; ?>');" title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                            </td>
                        </tr>
                        <?php
                        $i++;
                        }
                    }
                    ?>
                
            </tbody>
        </table>
        <h4><strong>Total:</strong> <?php echo number_format($Total[0],2); ?></h4>
        
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
if ($_POST['action'] == 'Delete') {
    //$filterSql = mysqli_query($dbconn,"select * from paymentMaster where iPaymentId='". $_REQUEST['ID']."'");
    mysqli_query($dbconn, 'update multicompany set iPaymentId=0,iPaymentStatus=0,iPaymentBy=0,strPaymentDate="" where multicompanyid="'.$_REQUEST['multicompanyid'].'"');
    $paymentMaster = mysqli_fetch_assoc(mysqli_query($dbconn, 'select iAmount from paymentMaster where iPaymentId="'.$_REQUEST['iPaymentId'].'"'));
    $iAmount = $paymentMaster['iAmount'] - $_REQUEST['Balance'];
    $data = array(
        "iAmount" => $iAmount,
        "strEntryDate" => date('d-m-Y H:i:s')
    );
    $where = ' where iPaymentId=' . $_REQUEST['iPaymentId'];
    $dealer_res = $connect->updaterecord($dbconn,'paymentMaster', $data, $where);    
    
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
   } else {
       $("#bankdetails").hide();
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