<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {

    $where = "where 1=1";

//    if ($_REQUEST['Company'] != NULL && isset($_REQUEST['Company']))
//        $where.=" and companyId='" .implode(',',$_POST['Company'])."'";
//
//
//
//    if ($_REQUEST['salarymasterId'] != NULL && isset($_REQUEST['salarymasterId']))
//        $where.=" and salaryId='" . $_POST['salarymasterId'] . "'";


     $filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where salarydetails.companyId in (" . implode(',', $_POST['Company']) . ") and salarydetails.salaryId='" . $_POST['salarymasterId'] . "' and employee.isDelete=0 and employee.istatus=1";
    // echo $filterstr = "SELECT * FROM `salarydetails` " . $where . "   order by salarydetailsId asc";
    $countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id  " . $where . "  order by salarydetailsId asc";

    $resrowcount = mysqli_query($dbconn, $countstr);
    $resrowc = mysqli_fetch_array($resrowcount);
    $totalrecord = $resrowc['TotalRow'];
    $per_page = $cateperpaging;
    $total_pages = ceil($totalrecord / $per_page);
    $page = $_REQUEST['Page'] - 1;
    $startpage = $page * $per_page;
    $show_page = $page + 1;



    $filterstr = $filterstr . " LIMIT $startpage, $per_page";
// echo $filterstr;


    $resultfilter = mysqli_query($dbconn, $filterstr);

    if (mysqli_num_rows($resultfilter) > 0) {
        $i = 1;
        ?>  
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
            <input type="text" disabled="" name="action" id="action" value="AddSalaryDetails" >
            <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
                
                <thead class="tbg">
                    <tr>
                        <th class="all">Sr.No</th>
                        <th class="all">Employee Name</th>
                        <th class="all">RATE</th>
                        <th class="all">Present<br />Days</th>
                        <th class="desktop">O.T<br />Hours</th>
                        <th class="desktop">Present Amount</th>
                        <th class="desktop">O.T<br />Amount</th>
                        <th class="desktop">Total<br />Amount</th>
                        <th class="desktop">Adv.</th>
                        <th class="desktop">Total</th>
                        <th class="desktop">F.A.</th>
                        <th class="desktop">T.A.</th>
                        <th class="desktop">Balance1</th>
                        <?php
//                    
//                      echo "SELECT * FROM `companymaster` where companymasterId='".$_REQUEST['Company']."'"; 
//                        $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster` where companymasterId='".$_REQUEST['Company']."'"));
//                        ?>
<!--                     <th class="desktop"><?php echo $comp['companyname']; ?></th>-->
                        <th class="desktop">GSFC</th>
                        <th class="desktop">GIPCL</th>
                        <th class="desktop">Balance2</th>
                        <th class="desktop">Paid Date</th>
                    </tr>
                </thead>


                <tbody>
                    <?php while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        ?>
                        <tr>
                            <?php
                            $PresentAmount = $rowfilter['salaryamt'] * $rowfilter['workingdays'];
                            $otamt = ($rowfilter['salaryamt'] / 8) * $rowfilter['othours'];
                            $totalamt = $PresentAmount + $PresentAmount;
                            $ledger = mysqli_fetch_array(mysqli_query($dbconn, "SELECT SUM(`credit`) as totalcredit,SUM(`debit`) as totaldebit ,SUM(`balance`) as totalbalance FROM `ledger` where emp_id='" . $rowfilter['employeeId'] . "' "));
                            $cradit = $ledger['totalcredit'];
                            $debit = $ledger['totaldebit'];
                            $adv = $cradit - $debit;
                            $total = $totalamt - $adv;
                            $balance1 = $total + $Fa + $Ta;
                            ?>
                            <td><?php echo $i; ?></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['emp_name']; ?>"/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['salaryamt']; ?>"/></td> 
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['workingdays']; ?> "/></td> 
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['othours']; ?> "/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $PresentAmount; ?> "/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $otamt; ?>"/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $totalamt; ?> "/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $adv; ?> "/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $total; ?> "/></td>
                            <td><input type="text"  name="action" id="action" value="<?php echo $Fa; ?> "/></td>
                            <td><input type="text"  name="action" id="action" value="<?php echo $Ta; ?> "/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $balance1; ?>"/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['workrate']; ?>"/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['netamountpaid']; ?>"/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['netamountpaid']; ?>"/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['netamountpaid']; ?> "/></td>
                            <td><input type="text" disabled="" name="action" id="action" value="<?php echo $rowfilter['netamountpaid']; ?> "/></td>
                        </tr>       
            <?php
            $i++;
        }
        ?>

                </tbody>


            </table>
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
