<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {

    $where = "where 1=1";
    print_r($_REQUEST['Company']) ;
    if ($_REQUEST['Company'] != NULL && isset($_REQUEST['Company']))
        $where.=" and companyId='" . implode(',', $_POST['Company']) . "'";
    if ($_REQUEST['salarymasterId'] != NULL && isset($_REQUEST['salarymasterId']))
        $where.=" and salaryId='" . $_POST['salarymasterId'] . "'";
    //$filterstr = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where salarydetails.companyId in (" . implode(',', $_POST['Company']) . ") and salarydetails.salaryId='" . $_POST['salarymasterId'] . "' and employee.isDelete=0 and employee.istatus=1";
    $filterstr = "SELECT employee.employeeId,employee.emp_name,employee.salaryamt,employee.bankid,employee.ecsno,employee.pfcode,employee.accountno
,sum(salarydetails.workingdays) as workingdays
,sum(salarydetails.othours) as othours
,sum(salarydetails.netamountpaid) as netamountpaid
,(select s1.salaryamt from salarydetails s1 where s1.salaryId = '" . $_POST['salarymasterId'] . "' and s1.salarydetailsId = salarydetails.salarydetailsId limit 1) as SalaryAmount
FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where salarydetails.companyId in (" . implode(',', $_POST['Company']) . ") and salarydetails.salaryId='" . $_POST['salarymasterId'] . "' and employee.isDelete=0 and employee.istatus=1 GROUP BY 
employee.employeeId,employee.emp_name order by employee.emp_name asc ";
    // echo $filterstr = "SELECT * FROM `salarydetails` " . $where . "   order by salarydetailsId asc";
    $countstr = "SELECT count(*) as TotalRow FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where salarydetails.companyId in (" . implode(',', $_POST['Company']) . ") and salarydetails.salaryId='" . $_POST['salarymasterId'] . "' and employee.isDelete=0 and employee.istatus=1 GROUP BY 
employee.employeeId,employee.emp_name";

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
            <input type="hidden" value="multicompany" name="action" id="action">
            <div class="table-responsive">
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
                            $comp = mysqli_query($dbconn, "SELECT * FROM `companymaster` where companymasterId in (" . implode(',', $_POST['Company']) . ")");
                            while ($rowfiltercom = mysqli_fetch_array($comp)) {
                                ?>
                                <th class="desktop"><?php echo $rowfiltercom['companyname']; ?></th>
                            <?php } ?>
                            <th class="desktop">Balance2</th>
                        </tr>
                    </thead>


                    <tbody>
                        <?php while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                            ?>
                            <tr>
                                <?php
                                $PresentAmount = $rowfilter['SalaryAmount'] * $rowfilter['workingdays'];
                                $otamt = ($rowfilter['SalaryAmount'] / 8) * $rowfilter['othours'];
                                $totalamt = $PresentAmount + $otamt;
                                $ledger = mysqli_fetch_array(mysqli_query($dbconn, "SELECT SUM(`credit`) as totalcredit,SUM(`debit`) as totaldebit ,SUM(`balance`) as totalbalance FROM `ledger` where emp_id='" . $rowfilter['employeeId'] . "' and isDelete='0' "));
                                $cradit = $ledger['totalcredit'];
                                $debit = $ledger['totaldebit'];
                                $adv = $cradit - $debit;
                                $total = $totalamt - $adv;
                                //$balance1 = $total + $Fa + $Ta;
                                $Fa = 0;
                                $Ta = 0;
                                $fata = mysqli_query($dbconn, "SELECT * FROM `multicompany` where emp_id='" . $rowfilter['employeeId'] . "' ");
                                if (mysqli_num_rows($fata) > 0) {
                                    $fatas = mysqli_fetch_array($fata);
                                    $Fa = $fatas['Fa'];
                                    $Ta = $fatas['Ta'];
                                }
                               
                                
                                $balance1 = $total + $Fa + $Ta;
                                ?>
                                <td><?php echo $i; ?></td>
                        <input type="hidden" name="emp_id[]" id="emp_id"  value="<?php echo $rowfilter['employeeId']; ?>">
                        <td><input type="hidden" name="name[]" id="name"  value="<?php echo $rowfilter['emp_name']; ?>"><?php echo $rowfilter['emp_name']; ?></td>
                        <td><input type="hidden" name="salaryamt[]" id="salaryamt"  value="<?php echo $rowfilter['SalaryAmount']; ?>"><?php echo $rowfilter['SalaryAmount']; ?></td> 
                        <td><input type="hidden" name="workingdays[]" id="workingdays"  value="<?php echo $rowfilter['workingdays']; ?>"><?php echo $rowfilter['workingdays']; ?></td> 
                        <td><input type="hidden" name="othours[]" id="othours"  value="<?php echo $rowfilter['othours']; ?>"><?php echo $rowfilter['othours']; ?></td>
                        <td><input type="hidden" name="PresentAmount[]" id="PresentAmount"  value="<?php echo $PresentAmount; ?>"><?php echo $PresentAmount; ?></td>
                        <td><input type="hidden" name="otamt[]" id="otamt"  value="<?php echo $otamt; ?>"><?php echo $otamt; ?></td>
                        <td><input type="hidden" name="totalamt[]" id="totalamt"  value="<?php echo round($totalamt); ?>"><?php echo round($totalamt); ?></td>
                        <td><input type="hidden" name="adv[]" id="adv"  value="<?php echo $adv; ?>"><?php echo $adv; ?></td>
                        <td><input type="hidden" name="total[]" id="total"  value="<?php echo round($total); ?>"><?php echo round($total); ?></td>
                        <td><input type="text" name="Fa[]" id="Fa"  value="<?php echo $Fa; ?>" ></td>
                        <td><input type="text" name="Ta[]" id="Ta"  value="<?php echo $Ta; ?>" ></td>
                        <td><input type="hidden" name="balance1[]" id="balance1"  value="<?php echo round($balance1); ?>"><?php echo round($balance1); ?></td>

                        <?php
                        $iCounter = 0;
                         $netamt = '0';
                         $AllCompanyTotal =0;
                        for ($iCounter = 0; $iCounter < count($_REQUEST['Company']); $iCounter++) {

                            $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $_REQUEST['Company'][$iCounter] . ") and salarydetails.emp_id='" . $rowfilter['employeeId'] . "' and  salarydetails.salaryId = '" . $_POST['salarymasterId'] . "'");
                            if (mysqli_num_rows($comp) > 0) {
                                while ($rowfiltercom = mysqli_fetch_array($comp)) {
                                   
                                     $netamt = $rowfiltercom['netamountpaid'];
                                     
                                     if($netamt == ''){
                                        $netamt = '0';
                                    }
                                    if($rowfiltercom['netamountpaid'] != '')
                                    {
                                        $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                                    }
                                    ?>

                                    <td class="desktop"><input type="hidden" name="netamountpaid[]" id="netamountpaid"  value="<?php echo $rowfiltercom['netamountpaid']; ?>"><?php echo $netamt ?></td>
                                    <?php
                                }
                            } else {
                                ?>
                                <td>0</td>
                                <?php
                            }
                        }
                        $netamts = $netamt;

                        $bal2 = $balance1 - $AllCompanyTotal;
                        ?>

                        <td><input type="hidden" name="balance2[]" id="balance2"  value="<?php echo round($bal2); ?>"><?php echo round($bal2); ?></td>
                        <input type="hidden" name="date[]" id="date"  value="<?php echo date('d-m-y'); ?>">
                        <input type="hidden" name="bankid[]" id="bankid"  value="<?php echo $rowfilter['bankid']; ?>">
                        <input type="hidden" name="ecsno[]" id="ecsno"  value="<?php echo $rowfilter['ecsno']; ?>">
                        <input type="hidden" name="pfcode[]" id="pfcode"  value="<?php echo $rowfilter['pfcode']; ?>">
                        <input type="hidden" name="accountno[]" id="accountno"  value="<?php echo $rowfilter['accountno']; ?>">
            <!--                    <input type="hidden" name="Company[]" id="Company"  value="<?php echo $_REQUEST['Company']; ?>">
                        <input type="hidden" name="salarymasterId" id="salarymasterId"  value="<?php echo $_REQUEST['salarymasterId']; ?>">-->
                        </tr>       
                        <?php
                        $i++;
                    }
                    ?>

                    </tbody>


                </table>
            </div>
            <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">      


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
<script>
    $('#frmparameter').submit(function (e) {

        e.preventDefault();
        var $form = $(this);
        $('#loading').css("display", "block");
        $.ajax({
            type: 'POST',
            url: '<?php echo $web_url; ?>admin/querydata.php',
            data: $('#frmparameter').serialize(),
            success: function (response) {
                //  alert(response);
                console.log(response);
                if (response == 1)
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Employee Added Sucessfully.');
                    window.location.href = '<?php echo $web_url; ?>admin/multicompanyreport.php';

                }
            }

        });
        //}
        //return false;
    });

</script>