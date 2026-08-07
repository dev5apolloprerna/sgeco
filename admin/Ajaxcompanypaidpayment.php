<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where ="";
    if($_POST['bank'] != ""){
        $where = " and employee.bankid= '" . $_POST['bank'] . "'";
        if ($_POST['bank'] == 3) {
            $where = " and employee.bankid not in (1,2)";
        }
    }
    $filterstr = "SELECT salarydetails.salarydetailsId,salarydetails.salaryId,emp_name,accountno,companypaymentMaster.salarymonth,companypaymentMaster.iBank,companypaymentMaster.strTransactionNo,companypaymentMaster.iPaymentMode,companypaymentMaster.iAmount,
        (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
        employee.bankid) as BankName,
        (select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
        companypaymentMaster.iBank) as PaidBankName,
        CASE
            WHEN companypaymentMaster.iPaymentMode = 1 THEN 'Cash'
            ELSE 'Bank'
        END as iPaymentMode,netamountpaid,emp_other_info,ifsccode,companypaymentMaster.strPaymentDate,iPaymentId FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id inner JOIN companypaymentMaster on companypaymentMaster.iCompanyPaymentId=salarydetails.iPaymentId where salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and iPaymentStatus=1 and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
    $countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id inner JOIN companypaymentMaster on companypaymentMaster.iCompanyPaymentId=salarydetails.iPaymentId where  salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') " . $where . " and iPaymentStatus=1 and salarydetails.workingdays > 0  and employee.isDelete=0 and employee.istatus=1";

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
                        <th class="desktop">IFSC Code</th>
                        <?php if ($_POST['bank'] == 3) { ?>
                        <th class="desktop">Bank Name</th>
                        <?php } ?>
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
                        ?>
                        <tr>
                            <td>
                                <?php echo $i; ?>
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
                            //$bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $rowfilter['bankid'] . "'"));
                            ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    //if ($bank['bankname'] == 'Other') {
                                    //     echo $rowfilter['otherbankname'];
                                    // } else {
                                        echo $rowfilter['BankName'];
                                    // }
                                    ?> 
                                </div>
                            </td> 
                            
                            <?php } ?>
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
                                <a class="btn blue" onclick="javascript: return deletedata('Delete', <?= $rowfilter['iPaymentId'];?>,<?= $rowfilter['salarydetailsId']; ?>,'<?= $rowfilter['netamountpaid']; ?>');" title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                            </td>
                        </tr>
                        <?php
                        $i++;
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
}

if ($_POST['action'] == 'Delete') {
    //$filterSql = mysqli_query($dbconn,"select * from paymentMaster where iPaymentId='". $_REQUEST['ID']."'");
    mysqli_query($dbconn, 'update salarydetails set iPaymentId=0,iPaymentStatus=0,iPaymentBy=0,strPaymentDate="" where salarydetailsId="'.$_REQUEST['salarydetailsId'].'"');
    $companypaymentMaster = mysqli_fetch_assoc(mysqli_query($dbconn, 'select iAmount from companypaymentMaster where iCompanyPaymentId="'.$_REQUEST['iPaymentId'].'"'));
    $iAmount = $companypaymentMaster['iAmount'] - $_REQUEST['netamountpaid'];
    $data = array(
        "iAmount" => $iAmount,
        "strEntryDate" => date('d-m-Y H:i:s')
    );
    $where = ' where iCompanyPaymentId=' . $_REQUEST['iPaymentId'];
    $dealer_res = $connect->updaterecord($dbconn,'companypaymentMaster', $data, $where);    
    
    // $salaery = mysqli_fetch_assoc(mysqli_query($dbconn,"select *,(select companymaster.companyname from companymaster where companymaster.companymasterId = companypaymentMaster.iCompanySalaryMasterId and isDelete=0) as companyname from companypaymentMaster where iCompanyPaymentId='".$_REQUEST['ID']."'"));
    // $msg = "Dear Sir, <br /><br />
    			
    // User ". $_SESSION['AdminName'] ." is Removed Salary Month of ".$salaery['salarymonth'].", compmay is ".$salaery['companyname']." of Amount Entry is ".$salaery['iAmount'].". <br />";
    // $connect->sendmail($msg, "sgeco@rediffmail.com", $sub = 'Remove Company Salary History', $mailHost, $mailFrom, $mailFromName, $mailSMTPSecure, $mailAddReplyTo, $mailUsername, $mailPassword);
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