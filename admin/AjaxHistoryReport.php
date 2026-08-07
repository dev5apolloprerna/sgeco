<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    
    /*SELECT paymentMaster.iPaymentId,paymentMaster.iCompanySalaryMasterId,paymentMaster.salarymonth,paymentMaster.strPaymentDate,paymentMaster.iPaymentMode,paymentMaster.iBank,paymentMaster.strTransactionNo,paymentMaster.iAmount
,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = paymentMaster.iBank) as BankName,
(select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId= multiycompanysalarymaster.companymasterId
  and multiycompanysalarymaster.companysalarymasterId in (select pm.iCompanySalaryMasterId from paymentMaster as pm where pm.iCompanySalaryMasterId=paymentMaster.iCompanySalaryMasterId) and multiycompanysalarymaster.isDelete=0
  group by multiycompanysalarymaster.companysalarymasterId) as companyname FROM `paymentMaster`,companysalarymaster where 
companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId 
and month='" . $_POST['month'] . "' and paymentMaster.isDelete='0' and paymentMaster.iStatus='1' */
    $filterstr =  "SELECT paymentMaster.iAmount,paymentMaster.iBank,paymentMaster.strPaymentDate,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = paymentMaster.iBank) as BankName,
(select GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId= multiycompanysalarymaster.companymasterId
  and multiycompanysalarymaster.companysalarymasterId in (select pm.iCompanySalaryMasterId from paymentMaster as pm where pm.iCompanySalaryMasterId=paymentMaster.iCompanySalaryMasterId) and multiycompanysalarymaster.isDelete=0
  group by multiycompanysalarymaster.companysalarymasterId) as companyname FROM `paymentMaster`,companysalarymaster where 
companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId 
and month='" . $_POST['month'] . "' and paymentMaster.isDelete='0' and paymentMaster.iStatus='1' 
UNION All
SELECT companypaymentMaster.iAmount,companypaymentMaster.iBank,companypaymentMaster.strPaymentDate,
(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = companypaymentMaster.iBank) as BankName,
(select companymaster.companyname from companymaster where companymaster.companymasterId = companypaymentMaster.iCompanySalaryMasterId and isDelete=0) as companyname FROM companypaymentMaster 
where 1=1 and salarymonth='" . $_POST['month'] . "' and companypaymentMaster.isDelete=0 and companypaymentMaster.iStatus=1";
//and companysalarymasterId ='" . $_REQUEST['token'] . "' 
    //$filterstr = "SELECT * FROM `companysalarymaster`  where  month like '%" . $_POST['month'] . "%' and  isDelete='0'  and  istatus='1' order by companysalarymasterId desc";
    $countstr = "SELECT count(*) as TotalRow FROM `paymentMaster`,companysalarymaster where companysalarymaster.companysalarymasterId = paymentMaster.iCompanySalaryMasterId and month='" . $_POST['month'] . "' and paymentMaster.isDelete='0' and paymentMaster.iStatus='1 ";

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
                <tr><th colspan="5" style="text-align: center;border-bottom: white solid 1px !important;">Summary</th></tr>
                <tr>
                    <th class="all"></th>                    
                    <th class="all">SBI</th>
                    <th class="all">BOB</th>
                    <th class="all">Other</th>
                    <th class="all">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $SBITotal = 0;
                $BOBTotal = 0;
                $OtherTotal = 0;
                $Total = 0;
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    ?>
                    <tr>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['companyname'] ." - ". $rowfilter['strPaymentDate']; ?> 
                            </div>
                        </td>
                        
                        <td>
                            <div class="form-group form-md-line-input ">
                            <?php 
                            $iAmount = 0;
                            if($rowfilter['iBank'] == "2"){
                                echo $rowfilter['iAmount'];
                                $iAmount += $rowfilter['iAmount'];
                                $SBITotal += $rowfilter['iAmount'];
                            } else {
                                echo "0";
                            }
                            ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php 
                            if($rowfilter['iBank'] == "1"){
                                echo $rowfilter['iAmount'];
                                $iAmount += $rowfilter['iAmount'];
                                $BOBTotal += $rowfilter['iAmount'];
                            } else {
                                echo "0";
                            } ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php 
                            if($rowfilter['iBank'] == "0" || $rowfilter['iBank'] > 2 || $rowfilter['iBank'] == ""){
                                echo $rowfilter['iAmount'];
                                $iAmount += $rowfilter['iAmount'];
                                $OtherTotal += $rowfilter['iAmount'];
                            } else {
                                echo "0";
                            }
                            ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $iAmount;
                                $Total+=$iAmount;
                            ?> 
                            </div>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
            </tbody>
            <thead class="tbg">
                <tr>
                    <th class="all">Total</th>                    
                    <th class="all"><?= $SBITotal; ?></th>
                    <th class="all"><?= $BOBTotal; ?></th>
                    <th class="all"><?= $OtherTotal; ?></th>
                    <th class="all"><?= $Total; ?></th>
                </tr>
            </thead>
        </table>

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
    $where = ' where companysalarymasterId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'companysalarymaster', $data, $where);
}
?>
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

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
?>								  

