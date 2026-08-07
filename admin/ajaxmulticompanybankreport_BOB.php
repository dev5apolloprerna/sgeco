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
                //$where .= " and employee.bankid not in (1,2)  and multicompany.pay_cash='0'";
                $where .= " and employee.bankid not in (2)  and multicompany.pay_cash='0'";
            } 
            if ($_REQUEST['bank'] == 4) {
            //$where = " and employee.bankid not in (1,2)";
            $where .= " and employee.bankid not in (1,2) and multicompany.pay_cash='0'";
        }

            else if ($_REQUEST['bank'] == 1 || $_REQUEST['bank'] == 2) {
                $where .= " and employee.bankid = " . $_POST['bank'] . "  and multicompany.pay_cash='0'";
            } else {
                $where .= " and multicompany.pay_cash='1'";
            }
        }
    }
    $filterstr = "SELECT multicompany.balance1,companysalarymaster.companysalarymasterId,employee.employeeId,employee.emp_name
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    employee.bankid) as BankName ,employee.ifsccode, employee.emp_other_info,employee.accountno 
    ,(select companysalarymaster.month from companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId) as DisplayMonth 
    FROM `multicompany`,employee,companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and  
    multicompany.emp_id = employee.employeeId  " . $where . "  and multicompany.isDelete='0'  and  multicompany.istatus='1' ORDER BY employee.emp_name asc";

    $countstr = "SELECT count(*) as TotalRow FROM `multicompany`,employee,companysalarymaster where companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and multicompany.emp_id = employee.employeeId  " . $where . " and multicompany.isDelete='0'  and  multicompany.istatus='1'";
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
                <th class="desktop">Bank Name</th>
                <th class="desktop">IFSC Code</th>
                <th class="desktop">Bank A/c No</th>
                <th class="desktop">Month</th>
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
               and salarydetails.emp_id = " . $rowfilter['employeeId'] . " ";
                $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));
                $Balance = $rowfilter['balance1'] - $empdata['PaidAmount'];
                if ($Balance > 0) {
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];}  ?>
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php
                                echo $Balance;
                                $Total[0] = $Balance + $Total[0];
                                ?>
                            </div>
                        </td>
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
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?> 
                            </div>
                        </td> 
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['accountno']; ?> 
                            </div>
                        </td> 
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['DisplayMonth']; ?> 
                            </div>
                        </td> 
                    </tr>
                    <?php
                    $i++;
                    }
                    
                }
                ?>
            
        </tbody>
    </table>
    <h4><strong>Total:</strong> <?php echo $Total[0]; ?></h4>
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