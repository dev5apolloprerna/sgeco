<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');
include ('phpmailer/PHPMailerAutoload.php');

if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1 ";
    if ($_REQUEST['companysalarymasterId'] != NULL) {
        
        $where .= " and companypaymentMaster.iCompanySalaryMasterId = " . $_POST['companysalarymasterId'] . " ";
    }
    if ($_REQUEST['salarymonthId'] != NULL) {
        $where .= " and companypaymentMaster.salarymonth ='" . $_POST['salarymonthId'] . "'";
    }
    if ($_REQUEST['bank'] != NULL) {
        $where .= " and companypaymentMaster.iBank = " . $_POST['bank'] . "";
    }
    
    
//     $filterstr = "SELECT paymentMaster.iPaymentId,paymentMaster.iCompanySalaryMasterId,paymentMaster.salarymonth,paymentMaster.strPaymentDate,paymentMaster.iPaymentMode,paymentMaster.iBank,paymentMaster.strTransactionNo,paymentMaster.iAmount
//     ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = paymentMaster.iBank) as BankName,
//     (select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId= multiycompanysalarymaster.companymasterId
//   	and multiycompanysalarymaster.companysalarymasterId in (select pm.iCompanySalaryMasterId from paymentMaster as pm where pm.iCompanySalaryMasterId=paymentMaster.iCompanySalaryMasterId) and multiycompanysalarymaster.isDelete=0
//   	group by multiycompanysalarymaster.companysalarymasterId) as companyname FROM `paymentMaster`,companysalarymaster 
//   	where 
//     companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId " . $where . "  and paymentMaster.isDelete='0' and paymentMaster.iStatus='1' ORDER BY paymentMaster.iPaymentId desc";

    $filterstr = "SELECT companypaymentMaster.iCompanyPaymentId,companypaymentMaster.iCompanySalaryMasterId,companypaymentMaster.salarymonth,companypaymentMaster.iBank,companypaymentMaster.strTransactionNo,companypaymentMaster.iPaymentMode,companypaymentMaster.iAmount,
        (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = companypaymentMaster.iBank) as PaidBankName,
        CASE
            WHEN companypaymentMaster.iPaymentMode = 1 THEN 'Cash'
            ELSE 'Bank'
        END as iPaymentMode,companypaymentMaster.strPaymentDate,
        (select companymaster.companyname from companymaster where companymaster.companymasterId = companypaymentMaster.iCompanySalaryMasterId and isDelete=0) as companyname
        FROM companypaymentMaster " . $where . "  and companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1 order by companypaymentMaster.iCompanyPaymentId desc";
    
    $countstr = "SELECT count(*) as TotalRow FROM companypaymentMaster " . $where . " and companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1 ";


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
                    <th class="all">Company Name</th>
                    <th class="desktop">Salary Month</th>
                    <th class="desktop">Paid Date</th>
                    <th class="desktop">Mode</th>
                    <th class="desktop">Bank</th>
                    <th class="desktop">Cheque / Transaction No</th>
                    <th class="desktop">Amount</th>
                    <th class="desktop">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $Total = array(0, 0, 0, 0);
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        ?>
                        <tr>
                            <td><?php echo $i; ?>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['companyname']; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= $rowfilter['salarymonth'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['strPaymentDate']; ?> 
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
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['iAmount']; $Total[0] = $rowfilter['iAmount'] + $Total[0]; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input ">
                                    <a  class="btn blue" href="#" onclick="updatepaymentDate(<?php echo $rowfilter['iCompanyPaymentId']; ?>,'<?php echo $rowfilter['strPaymentDate']; ?>');" data-toggle="modal" data-target="#exampleModal" title="Update Payment Date"><i class="fa fa-edit iconshowFirst"></i></i></a>
                                    <a  class="btn blue" href="<?php echo $web_url; ?>admin/ViewCompanyPaypaymentHistory.php?token=<?php echo $rowfilter['iCompanyPaymentId']; ?>" title="View History"><i class="fa fa-eye"></i></i></a>
                                    <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['iCompanyPaymentId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
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
if ($_POST['action'] == 'Delete') {
    //$filterSql = mysqli_query($dbconn,"select * from paymentMaster where iPaymentId='". $_REQUEST['ID']."'");
    mysqli_query($dbconn, 'update salarydetails set iPaymentId=0,iPaymentStatus=0,iPaymentBy=0,strPaymentDate="" where iPaymentId="'.$_REQUEST['ID'].'"');
    $data = array(
        "isDelete" => '1',
        "strEntryDate" => date('d-m-Y H:i:s')
    );
    $where = ' where iCompanyPaymentId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn,'companypaymentMaster', $data, $where);    
    
    $salaery = mysqli_fetch_assoc(mysqli_query($dbconn,"select *,(select companymaster.companyname from companymaster where companymaster.companymasterId = companypaymentMaster.iCompanySalaryMasterId and isDelete=0) as companyname from companypaymentMaster where iCompanyPaymentId='".$_REQUEST['ID']."'"));
    $msg = "Dear Sir, <br /><br />
    			
    User ". $_SESSION['AdminName'] ." is Removed Salary Month of ".$salaery['salarymonth'].", compmay is ".$salaery['companyname']." of Amount Entry is ".$salaery['iAmount'].". <br />";
    $connect->sendmail($msg, "sgeco@rediffmail.com", $sub = 'Remove Company Salary History', $mailHost, $mailFrom, $mailFromName, $mailSMTPSecure, $mailAddReplyTo, $mailUsername, $mailPassword);
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

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: #658397;color: #fff;">
                <h5 class="modal-title" id="exampleModalLabel">Change Payment Date</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">X</span>
                </button>
            </div>
            <form  role="form"  method="POST"  action="" name="frmchangepwd"  id="frmchangepwd" enctype="multipart/form-data">
                <input type="hidden" value="AddChangeCompanyPaymentDate" name="action" id="action">
                <input type="hidden" value="" name="iCompanyPaymentId" id="iCompanyPaymentId">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="form_control_1">Payment Date</label>
                            <input name="strUpdatePaymentDate" id="strUpdatePaymentDate" class="form-control" required placeholder="Enter Payment Date"   type="text">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" onclick="ChangePaymentDate();" class="btn btn-primary" style="background: #658397;color: #fff;">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script>

$(document).ready(function () {
    $("#strDate").datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
    });
    $("#strUpdatePaymentDate").datepicker({
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

function updatepaymentDate(iCompanyPaymentId,strPaymentDate){
    $("#iCompanyPaymentId").val(iCompanyPaymentId);
    $("#strUpdatePaymentDate").val(strPaymentDate);
}

function ChangePaymentDate(){
    
    $('#loading').css("display", "block");
    $.ajax({
        type: 'POST',
        url: 'paymentquerydata.php',
        data: $('#frmchangepwd').serialize(),
        success: function (response) {
            console.log(response);
            if (response == 1)
            {
                $('#loading').css("display", "none");
                $("#Btnmybtn").attr('disabled', 'disabled');
                alert('Payment Date Update Sucessfully.');
                window.location.href = '';
                // $("div").removeClass("modal-backdrop fade in");
                // PageLoadData(1);
            } else
            {
                $('#loading').css("display", "none");
                $("#Btnmybtn").attr('disabled', 'disabled');
                alert('Invalid Request. Please Try Again.');
                //$("div").removeClass("modal-backdrop fade in");
                window.location.href = '';
                //PageLoadData(1);
            }
        }
    });
}

</script>