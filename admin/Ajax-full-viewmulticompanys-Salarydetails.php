<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $filterstr = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_POST['companysalarymasterId'] . "' and isDelete=0 order by name asc ";
    $countstr = "SELECT count(*) as TotalRow FROM  `multicompany` where  companysalarymasterId ='" . $_POST['companysalarymasterId'] . "'  and isDelete=0 ";

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
        $array[][] = array();
        $array[0][0] = "";
        $array[0][1] = "CASH";
        $array[0][2] = "SBI";
        $array[0][3] = "BOB";
        $array[0][4] = "OTHER";
        $array[0][5] = "TOTAL";
        $array[1][0] = "BANK PAYMENT";
        $array[1][1] = 0;
        $array[1][2] = 0;
        $array[1][3] = 0;
        $array[1][4] = 0;
        $array[1][5] = 0;
        $array[2][0] = "BALANCE PAYMENT";
        $array[2][1] = 0;
        $array[2][2] = 0;
        $array[2][3] = 0;
        $array[2][4] = 0;
        $array[2][5] = 0;
        $array[3][0] = "TOTAL";
        $array[3][1] = 0;
        $array[3][2] = 0;
        $array[3][3] = 0;
        $array[3][4] = 0;
        $array[3][5] = 0;
        $array[4][0] = "ADVANCE PAYMENT";
        $array[4][1] = 0;
        $array[4][2] = 0;
        $array[4][3] = 0;
        $array[4][4] = 0;
        $array[4][5] = 0;
        $array[5][0] = "TOTAL PAYMENT";
        $array[5][1] = 0;
        $array[5][2] = 0;
        $array[5][3] = 0;
        $array[5][4] = 0;
        $array[5][5] = 0;
        ?>  
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
                <thead class="tbg">
                    <tr>
                        <th class="none">Sr.<br/>No</th>
                        <th class="none">PF.NO.</th>
                        <th class="none">ESIC No</th>
                        <th class="all">Name</th>
                        <th class="none">Rate</th>                    
                        <th class="desktop">No of<br />Days<br />Worked</th>
                        <th class="desktop">O.T.<br />HRS</th>
                        <th class="all">Present Amount</th>
                        <th class="desktop">O.T.<br />Amount</th>
                        <th class="desktop">Total<br /> Amt</th>
                        <th class="none">ADV</th>
                        <th class="none">ADV TWO</th>
                        <th class="none">Adv. Paid By Bank</th>
                        <th class="none">PF Amt.</th>
                        <th class="none">ESIC Amt.</th>
                        <th class="desktop">Total</th>
                        <th class="none">F.A</th>
                        <th class="none">T.A</th>
                        <th class="all">Balance</th>
                        <?php
                        $companymasterId = '0';
                        $month = '';
                        $jCounter = 0;
                        $comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_POST['companysalarymasterId'] . "'  order by companyname");
                        while ($commaster = mysqli_fetch_array($comid)) {
                            $month = $commaster['month'];
                            $companymasterId = $companymasterId . ',' . $commaster['companymasterId'];
                            ?>
                            <th class="all"><?php echo ucwords(strtolower($commaster['companyname'])); ?></th>  

                            <?php
                            $jCounter++; 
                        }
                        $companymasterId = rtrim($companymasterId, ", ");
                        ?>
                        <th class="all">Total Balance</th>
                        <th class="none">Bank / IFSC </th>
                        <th class="none">Bank A/C No</th>
                        <th class="none">Paid Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $Total = array(0, 0, 0, 0);
                    $TotalAdvPaidByBank = 0;
                    $TotalPfAmount = 0;
                    $TotalEsicAmount = 0;
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        // SELECT salarydetails.netamountpaid FROM `salarydetails` WHERE salarydetails.companyId in (" . $_REQUEST['Company'][$iCounter] . ") and salarydetails.emp_id='" . $rowfilter['employeeId'] . "' and  salarydetails.salaryId = '" . $_POST['salarymasterId'] . "'
                        ?>
                        <tr>
                            <?php
                            $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0' and employeeId='" . $rowfilter['emp_id'] . "'"));
                            $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));
                            ?>
                            <td><?php echo $i; ?></td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $desg['pfcode']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $desg['ecsno']; ?> 
                                </div>
                            </td> 
                            <td>
                                <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($desg['emp_name'])); if(isset($desg['emp_other_info'])) { echo " - ".$desg['emp_other_info'];} ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['rate']; ?> 
                                </div>
                            </td>                    
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['workingdays']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['othours']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['PresentAmount']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['otamt']; ?> 
                                </div>
                            </td>

                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['totalamt']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['adv']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['adv_two']; ?> 
                                </div>
                            </td>
                            <?php
                            $advPaidByBank = (float) $rowfilter['adv_one_paid'] + (float) $rowfilter['adv_two_paid'];
                            $statutoryAmounts = mysqli_fetch_array(mysqli_query($dbconn, "SELECT COALESCE(SUM(pf), 0) AS pfAmount, COALESCE(SUM(esi), 0) AS esicAmount FROM salarydetails WHERE isDelete=0 AND emp_id='" . $rowfilter['emp_id'] . "' AND salaryId IN (SELECT salarymasterId FROM salarymaster WHERE isDelete='0' AND istatus='1' AND month='" . $month . "' AND companymasterId IN (" . $companymasterId . "))"));
                            $pfAmount = (float) $statutoryAmounts['pfAmount'];
                            $esicAmount = (float) $statutoryAmounts['esicAmount'];
                            $TotalAdvPaidByBank += $advPaidByBank;
                            $TotalPfAmount += $pfAmount;
                            $TotalEsicAmount += $esicAmount;
                            ?>
                            <td><div class="form-group form-md-line-input "><?php echo $advPaidByBank; ?></div></td>
                            <td><div class="form-group form-md-line-input "><?php echo $pfAmount; ?></div></td>
                            <td><div class="form-group form-md-line-input "><?php echo $esicAmount; ?></div></td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo $rowfilter['total'];
                                    $Total[0] = $rowfilter['total'] + $Total[0];
                                    ?>                                 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo $rowfilter['Fa'];
                                    $Total[1] = $rowfilter['Fa'] + $Total[1];
                                    ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo $rowfilter['Ta'];
                                    $Total[2] = $rowfilter['Ta'] + $Total[2];
                                    ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo $rowfilter['balance1'];
                                    $Total[3] = $rowfilter['balance1'] + $Total[3];
                                    ?> 
                                </div>
                            </td>

                            <?php
                            $strPaymentDate = "";
                            $comnymasid = explode(',', $companymasterId);
                            $iCounter = 0;
                            $netamt = '0';
                            $AllCompanyTotal = 0;
                            for ($iCounter = 1; $iCounter < count($comnymasid); $iCounter++) {

                                $saleryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'"));
                                $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid,strPaymentDate FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $rowfilter['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "' AND salarydetails.isDelete=0");
                                if (mysqli_num_rows($comp) > 0) {
                                    while ($rowfiltercom = mysqli_fetch_array($comp)) {

                                        $netamt = $rowfiltercom['netamountpaid'];

                                        if ($netamt == '') {
                                            $netamt = '0';
                                        }
                                        if ($rowfiltercom['netamountpaid'] != '') {
                                            $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                                            $Total[5][$iCounter] = $rowfiltercom['netamountpaid'] + $Total[5][$iCounter];
                                        }
                                        $strPaymentDate = $rowfiltercom['strPaymentDate']; 
                                        $bal2 = $rowfilter['balance1'] - $AllCompanyTotal;
//                                        if ($bal2 > 0) {
                                        // $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                                        // $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                                       if ($rowfilter['pay_cash'] == 0) {
                                            if ($desg['bankid'] == 2) {
                                                $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                                                $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                                            } else if ($desg['bankid'] == 1) {
                                                $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                                                $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                                            //}
                                            } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                                                $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                                                $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                                            }
                                       } else {
                                           $array[1][1] = $rowfiltercom['netamountpaid'] + $array[1][1];
                                           $array[3][1] = $rowfiltercom['netamountpaid'] + $array[3][1];
                                       }
                                    //    $array[1][5] = $array[1][1] + $array[1][2] + $array[1][3] + $array[1][4];
                                    //    $array[2][5] = $array[2][1] + $array[2][2] + $array[2][3] + $array[2][4];
                                    //    $array[3][5] = $array[3][1] + $array[3][2] + $array[3][3] + $array[3][4];
                                    //    $array[4][5] = $array[4][1] + $array[4][2] + $array[4][3] + $array[4][4];
                                    //    $array[5][5] = $array[5][1] + $array[5][2] + $array[5][3] + $array[5][4];
//                                        }
                                        ?>
                                        <td><div class="form-group form-md-line-input "><?php echo $netamt ?></div></td>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <td>0</td>
                                    <?php
                                }
                            }
                            $netamts = $netamt;

                            $bal2 = $rowfilter['balance1'] - $AllCompanyTotal;

                            if ($bal2 > 0) {

                                // $array[2][4] = $bal2 + $array[2][4];
                                // $array[3][4] = $bal2 + $array[3][4];
                                if ($rowfilter['pay_cash'] == 0) {
                                    if ($desg['bankid'] == 2) {
                                        $array[2][2] = $bal2 + $array[2][2];
                                        $array[3][2] = $bal2 + $array[3][2];
                                    } else if ($desg['bankid'] == 1) {
                                        $array[2][3] = $bal2 + $array[2][3];
                                        $array[3][3] = $bal2 + $array[3][3];
                                    } 
                                    else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                                        $array[2][4] = $bal2 + $array[2][4];
                                        $array[3][4] = $bal2 + $array[3][4];
                                    }
                                } else {
                                    $array[2][1] = $bal2 + $array[2][1];
                                    $array[3][1] = $bal2 + $array[3][1];
                                }
                            } else {
                                
                                // $array[4][4] = ($bal2 * (-1)) + $array[4][4];
                                if ($rowfilter['pay_cash'] == 0) {
                                    if ($desg['bankid'] == 2) {
                                        $array[4][2] = ($bal2 * (-1)) + $array[4][2];
                                    } else if ($desg['bankid'] == 1) {
                                        $array[4][3] = ($bal2 * (-1)) + $array[4][3];
                                    } 
                                    else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                                        $array[4][4] = ($bal2 * (-1)) + $array[4][4];
                                    }
                                }else{
                                    $array[4][1] = ($bal2 * (-1)) + $array[4][1];
                                }
                            }
                            $array[1][5] = $array[1][1] + $array[1][2] + $array[1][3] + $array[1][4];
                            $array[2][5] = $array[2][1] + $array[2][2] + $array[2][3] + $array[2][4];
                            $array[3][5] = $array[3][1] + $array[3][2] + $array[3][3] + $array[3][4];
                            $array[4][5] = $array[4][1] + $array[4][2] + $array[4][3] + $array[4][4];
                            $array[5][5] = $array[5][1] + $array[5][2] + $array[5][3] + $array[5][4];

                            $array[5][1] = $array[3][1] + ($array[4][1] * (-1));
                            $array[5][2] = $array[3][2] + ($array[4][2] * (-1));
                            $array[5][3] = $array[3][3] + ($array[4][3] * (-1));
                            $array[5][4] = $array[3][4] + ($array[4][4] * (-1));
                            $array[5][5] = $array[3][5] + $array[4][5] * (-1);
                            ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo $bal2;
                                    $Total[4] = $bal2 + $Total[4];
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                if ($rowfilter['pay_cash'] == 0) {
                                    echo $bank['bankname'] ." - ". $desg['ifsccode'];
                                }else{
                                    echo "Cash";
                                }
                                    ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    echo $desg['accountno'];
                                    ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php
                                    if($bal2 > 0){
                                            echo $rowfilter['strPaymentDate'];
                                    } else{
                                        echo $strPaymentDate;
                                    }
                                    ?> 
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
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $TotalAdvPaidByBank; ?></td>
                        <td><?php echo $TotalPfAmount; ?></td>
                        <td><?php echo $TotalEsicAmount; ?></td>
                        <td><?php echo $Total[0]; ?></td>
                        <td><?php echo $Total[1]; ?></td>
                        <td><?php echo $Total[2]; ?></td>           
                        <td><?php echo $Total[3]; ?></td>
                        <?php for ($iCounter = 1; $iCounter < count($comnymasid); $iCounter++) { ?>
                            <td><?php echo $Total[5][$iCounter]; ?></td>
                        <?php } ?>
                        <td><?php echo $Total[4]; ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>


                </tbody>
            </table>

        </div>
        <!--        <div class="row">-->

        <table class="table table-striped table-bordered table-hover dt-responsive" style="width: 60%;margin: 25px 0 0 0;" id="tableC">
            <?php
            $iCounter = 0;
            while ($iCounter < sizeof($array)) {
                if ($iCounter == 0) {
                    echo "<thead class='tbg'><tr>
                        <th  style='width: 100%;text-align: center; border: 1px solid #fff;' colspan='6'>SUMMARY</th>
                    </tr><tr class='none'>";
                } else if ($iCounter == 0) {
                    echo "<tbody><tr>";
                } else {
                    echo "<tr>";
                }

                for ($jCounter = 0; $jCounter < sizeof($array[$iCounter]); $jCounter++) {
                    if ($iCounter == 0) {
                        echo "<th class='none'>" . $array[$iCounter][$jCounter] . "</th>";
                    } else {
                        echo "<td>" . $array[$iCounter][$jCounter] . "</td>";
                    }
                }
                if ($iCounter == 0) {
                    echo "</tr></thead>";
                } else {
                    echo "</tr>";
                }
                $iCounter++;
            }
            ?>
        </table>
        <!--</div>-->

        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>
        <!--  <script>
            $(document).ready(function () {
                $('#tableC').DataTable({
                });
            });
        </script> -->
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


