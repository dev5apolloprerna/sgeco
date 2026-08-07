<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    // echo $_REQUEST['Company'];
    // echo $_REQUEST['bank'] ;
    // echo $_REQUEST['salaryId'] ;
//    $where = "where 1=1";
//   $whereA = " where 1=1";
//    if ($_REQUEST['Company'] != NULL && isset($_REQUEST['Company']))
//        $whereA.=" and salarydetails.companyId='" . $_POST['Company'] . "'";
// if ($_REQUEST['bank'] != NULL && isset($_REQUEST['bank']))
//        $where.=" and bankid='" . $_POST['bank'] . "'";
//    if ($_REQUEST['salaryId'] != NULL && isset($_REQUEST['salaryId']))
//        $whereA.=" and salarydetails.salaryId='" . $_POST['salaryId'] . "'";
//        
//        SELECT * FROM `employee` where employee.bankid= '" . $_POST['bank'] . "' and employee.employeeId in (SELECT salarydetails.emp_id FROM salarydetails where salarydetails.emp_id=employee.employeeId and salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId='" . $_POST['salaryId'] . "') and employee.isDelete=0 and employee.istatus=1 
//SELECT *
//FROM employee 
//INNER JOIN salarydetails
//ON employee.employeeId=salarydetails.emp_id where employee.bankid= '" . $_POST['bank'] . "' and salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId='" . $_POST['salaryId'] . "' and employee.isDelete=0 and employee.istatus=1
    if ($_REQUEST['Company'] != NULL && $_REQUEST['bank'] != NULL && $_REQUEST['salaryId'] != NULL) {
        $where = " and employee.bankid= '" . $_POST['bank'] . "'";
        if ($_POST['bank'] == 3) {
            //$where = " and employee.bankid not in (1,2)";
            $where = " and employee.bankid not in (2)";
        }
        if ($_POST['bank'] == 4) {
            //$where = " and employee.bankid not in (1,2)";
            $where = " and employee.bankid not in (1,2)";
        }
        //$filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId='" . $_POST['salaryId'] . "' and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
     $filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete = '0' and employee.istatus= '1'  ORDER BY `emp_name` ASC";
        //$countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where  salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId='" . $_POST['salaryId'] . "' " . $where . " and salarydetails.workingdays > 0  and employee.isDelete=0 and employee.istatus=1";
        $countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where  salarydetails.companyId='" . $_POST['Company'] . "' and salarydetails.salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salaryId'] . "' and isDelete='0' and  istatus='1') " . $where . " and salarydetails.workingdays > 0  and employee.isDelete=0 and employee.istatus=1";
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
                    <th class="desktop">IFSC Code</th>
                    <th class="desktop">Bank Name</th>
                    <th class="desktop">Bank A/c No</th>
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
                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['emp_name'])); if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];}?>
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php
                                echo ceil($rowfilter['netamountpaid']);
                                $Total[0] = $rowfilter['netamountpaid'] + $Total[0];
                                ?>
                            </div>
                        </td> 
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?>
                            </div>
                        </td>
                        <?php
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
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['accountno']; ?> 
                            </div>
                        </td> 
                    </tr>
                    <?php
                    $i++;
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