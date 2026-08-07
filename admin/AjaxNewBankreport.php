<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where = "";
    if ($_REQUEST['Company'] != NULL && $_REQUEST['bank'] != NULL && $_REQUEST['salaryId'] != NULL) {
        $where = " and employee.bankid= '" . $_POST['bank'] . "'";
        if ($_POST['bank'] == 3) {
            $where = " and employee.bankid not in (1,2)";
        }
    }

    $filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
    $countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where  salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') " . $where . " and salarydetails.workingdays > 0  and employee.isDelete=0 and employee.istatus=1";
    
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
                    <th>Sr. No.</th>
                    <th class="desktop">Beneficiary Account Number</th>
                    <th class="desktop">Amount</th>
                    <th class="desktop">Beneficiary Name</th>
                    <?php if ($_POST['bank'] == 3 || $_POST['bank'] == "") { ?>
                        <th class="desktop">Beneficiary Address</th>
                    <?php } ?>
                    <th class="desktop">IFSC Code</th>
                    <?php if ($_POST['bank'] == 3 || $_POST['bank'] == "") { ?>
                        <!--<th class="desktop">Bank Name</th> -->
                        <th class="desktop">Comm.</th>
                    <?php } ?>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                $Total = array(0, 0, 0, 0);
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo str_replace('A/C. ','',$rowfilter['accountno']); ?> 
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
                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['emp_name'])); if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];}?>
                            </div>
                        </td>
                        <?php if ($_POST['bank'] == 3|| $_POST['bank'] == "") {?>
                            <td>
                                <div class="form-group form-md-line-input "></div>
                            </td>
                        <?php } ?>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?>
                            </div>
                        </td>
                        <?php
                        if ($_POST['bank'] == 3|| $_POST['bank'] == "") {
                        //$bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $rowfilter['bankid'] . "'"));
                        ?>
                        <td>
                            <div class="form-group form-md-line-input "><?php
                                // if ($bank['bankname'] == 'Other') {
                                //     echo $rowfilter['otherbankname'];
                                // } else {
                                //     echo $bank['bankname'];
                                // }
                                $Comm = 0;
                                if($rowfilter['netamountpaid'] <= 10000){
                                    $Comm = "2.36";
                                } else {
                                    $Comm = "4.72";
                                }
                                echo $Comm;
                                $Total[1] = $Comm + $Total[1];
                                ?> 
                            </div>
                        </td> 
                        <?php } ?>
                        
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </tbody>
            <thead class="tbg">
                <tr>
                    <th></th>
                    <th class="desktop">Total</th>
                    <th class="desktop"><?= number_format($Total[0],2) ?></th>
                    <th class="desktop"></th>
                    <?php if ($_POST['bank'] == 3 || $_POST['bank'] == "") { ?>
                        <th class="desktop"></th>
                    <?php } ?>
                    <th class="desktop"></th>
                    <?php if ($_POST['bank'] == 3 || $_POST['bank'] == "") { ?>
                        <th class="desktop"><?= number_format($Total[1],2) ?></th>
                    <?php } ?>
                    
                </tr>
            </thead>
        </table>
        <br />
        <?php if ($_POST['bank'] == 3 || $_POST['bank'] == "") { ?>
            <table class="table-striped table-bordered table-hover dt-responsive" width="40%" id="tableC">
                <tr>
                    <td>Amount.</td>
                    <td><?= number_format($Total[0],2) ?></td>
                </tr>
                <tr>
                    <td>Bank Comm.</td>
                    <td><?= number_format($Total[1],2) ?></td>
                </tr>
                <tr>
                    <td><strong>Total Amt.</strong></td>
                    <td><strong><?= number_format($Total[0] + $Total[1],2) ?></strong></td>
                </tr>
            </table>
        <?php } ?>
        <!--<h4><strong>Total:</strong> <?php echo number_format($Total[0],2); ?></h4>-->
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