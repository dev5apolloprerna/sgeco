<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include('User_Paging.php');
include_once 'companyReportAdvance.php';

if ($_POST['action'] == 'ListUser') {

    //$filterstr = "SELECT * FROM `salarydetails` where  companyId='" . $_POST['Company'] . "' and salaryId='" . $_POST['salarymasterId'] . "' and   isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc ";
    $filterstr = "SELECT * FROM `salarydetails` where  companyId='" . $_POST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and   isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc ";
    //$countstr = "SELECT count(*) as TotalRow FROM  `salarydetails` where   companyId='" . $_POST['Company'] . "' and salaryId='" . $_POST['salarymasterId'] . "' and  isDelete='0'  and  istatus='1'  and workingdays > 0 order by salarydetailsId asc";
    $countstr = "SELECT count(*) as TotalRow FROM  `salarydetails` where   companyId='" . $_POST['Company'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $_POST['salarymasterId'] . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1'  and workingdays > 0 order by salarydetailsId asc";

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
    $companyReportAdvances = getCompanyReportAdvances($dbconn, $_POST['Company'], $_POST['salarymasterId']);
    if (mysqli_num_rows($resultfilter) > 0) {
        $i = 1;
?>

        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
        <div class="table-responsive table-responsive-new">
            <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
                <thead class="tbg">
                    <tr>
                        <th>Sr.No</th>
                        <th class="all">Name of<br />workman</th>
                        <th class="all">PF<br />No.</th>
                        <th class="none">Serial of<br />in the<br />register of<br />workman</th>
                        <th class="desktop">Designation/<br />Nature of<br />work done</th>
                        <th class="desktop">No of<br />Days<br />Worked</th>
                        <th class="desktop">Rate of Daily<br />work done</th>
                        <th class="desktop">OT Hours</th>
                        <th class="none">Price<br />Rate</th>
                        <th class="desktop">Amount of Wages</th>
                        <th class="desktop">OT Amount</th>
                        <th class="desktop">Medical Allowance</th>
                        <th class="desktop">DA</th>
                        <th class="desktop">HRA</th>
                        <th class="desktop">National Holiday Payment</th>
                        <th class="desktop">Bonus</th>
                        <th class="desktop">Leave</th>
                        <th class="desktop">Total</th>
                        <th class="desktop">P.F.</th>
                        <th class="desktop">E.S.I.</th>
                        <th class="desktop">Advance</th>
                        <th class="desktop">P.T.</th>
                        <th class="none">Deduction<br />if any</th>
                        <th class="desktop">Net<br />Amount<br />Paid</th>
                        <th class="none">Signature/<br />Thumb<br />impression of<br />Workman</th>
                        <th class="none">Initials of<br />Contractor<br />of his<br />Representive</th>


                    </tr>
                </thead>
                <tbody>
                    <?php
                    $Total = array_fill(0, 13, 0);
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        $advanceAmount = getEmployeeCompanyReportAdvance($companyReportAdvances, $rowfilter['emp_id']);
                        $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0' and employeeId='" . $rowfilter['emp_id'] . "'"));

                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($desg['emp_name']));
                                                                            if (isset($desg['emp_other_info'])) {
                                                                                echo " - " . $desg['emp_other_info'];
                                                                            } ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $desg['pfcode']; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input ">-
                                </div>
                            </td>

                            <td>
                                <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($desg['designation'])); ?>
                                </div>
                            </td>

                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['workingdays']; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['skillrate']; ?>
                                </div>
                            </td>

                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['othours']; ?>
                                </div>
                            </td>

                            <td>
                                <div class="form-group form-md-line-input ">-
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['basicwages'];
                                                                            $Total[5] = $rowfilter['basicwages'] + $Total[5];
                                                                            ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo round($rowfilter['totalovertime']);
                                                                            $Total[6] = $rowfilter['totalovertime'] + $Total[6];
                                                                            ?>

                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['MedicalAllowanceamt']; ?>
                                </div>
                            </td>

                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['da'] != '0.00') ? $rowfilter['da'] : ''; ?>
                                    <?php $Total[7] = $rowfilter['da'] + $Total[7]; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['hra'] != '0.00') ? $rowfilter['hra'] : ''; ?>
                                    <?php $Total[8] = $rowfilter['hra'] + $Total[8]; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['national_holiday_payment'] != '0.00') ? $rowfilter['national_holiday_payment'] : ''; ?>
                                    <?php $Total[9] = $rowfilter['national_holiday_payment'] + $Total[9]; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['iBonusAmt'] != '0.00') ? $rowfilter['iBonusAmt'] : ''; ?>
                                    <?php $Total[10] = $rowfilter['iBonusAmt'] + $Total[10]; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['iLeaveAmt'] != '0.00') ? $rowfilter['iLeaveAmt'] : ''; ?>
                                    <?php $Total[11] = $rowfilter['iLeaveAmt'] + $Total[11]; ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo number_format(round($rowfilter['total']), 2);
                                                                            $Total[0] = $rowfilter['total'] + $Total[0];
                                                                            ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['pf'] != '0.00') ? $rowfilter['pf'] : ''; ?>
                                    <?php $Total[2] = $rowfilter['pf'] + $Total[2];
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['esi'];
                                                                            $Total[1] = $rowfilter['esi'] + $Total[1];
                                                                            ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo number_format($advanceAmount, 2, '.', '');
                                                                            $Total[12] = $advanceAmount + $Total[12];
                                                                            ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?= ($rowfilter['pt'] != '0.00') ? $rowfilter['pt'] : ''; ?>
                                    <?php $Total[4] = $rowfilter['pt'] + $Total[4];
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input ">-
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo ceil($rowfilter['netamountpaid'] - $advanceAmount);
                                                                            $Total[3] = ceil($rowfilter['netamountpaid'] - $advanceAmount) + $Total[3];
                                                                            ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input ">-
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input ">-
                                </div>
                            </td>

                        <?php
                        $i++;
                    }
                        ?>

                        </tr>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo $Total[5]; ?></td>
                            <td><?php echo round($Total[6]); ?></td>
                            <td></td>
                            <td><?php echo $Total[7]; ?></td>
                            <td><?php echo $Total[8]; ?></td>
                            <td><?php echo $Total[9]; ?></td>
                            <td><?php echo $Total[10]; ?></td>
                            <td><?php echo $Total[11]; ?></td>
                            <td><?php echo number_format(round($Total[0]), 2); ?></td>
                            <td><?php echo $Total[2]; ?></td>
                            <td><?php echo $Total[1]; ?></td>
                            <td><?php echo number_format($Total[12], 2, '.', ''); ?></td>
                            <td><?php echo $Total[4]; ?></td>
                            <td></td>
                            <td><?php echo $Total[3]; ?></td>
                            <td></td>
                            <td></td>
                        </tr>


                </tbody>
            </table>
        </div>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>
        <script>
            $(document).ready(function() {
                $('#tableC').DataTable({});
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

function rrmdir($dir)
{
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