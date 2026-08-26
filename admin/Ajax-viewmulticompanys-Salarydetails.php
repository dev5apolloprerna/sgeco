<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {


   
    $filterstr = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_POST['companysalarymasterId'] . "' order by multicompanyid desc";
    
    $countstr = "SELECT count(*) as TotalRow FROM  `multicompany` where   companysalarymasterId ='" . $_POST['companysalarymasterId'] . "' ";

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
        
        <table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="tableC">
            
            <thead class="tbg">
                <tr>
                    <th class="none">Sr.No</th>
                     <th class="none">PF.NO.</th>
                      <th class="none">ESIC No</th>
                    <th class="all">Name</th>
                    <th class="none">Rate</th>                    
                    <th class="desktop">No of<br />Days<br />Worked</th>
                    <th class="desktop">O.T.HOURS</th>
                    <th class="all">Present Amount</th>
                    <th class="desktop">O.T.Amount</th>
                    <th class="desktop">Total Amt</th>
                    <th class="desktop">ADV</th>
                    <th class="desktop">Total</th>
                    <th class="desktop">Advance Paid By Bank</th>
                    <th class="desktop">PF Amount</th>
                    <th class="desktop">ESIC Amount</th>
                    <th class="desktop">F.A</th>
                    <th class="desktop">T.A</th>
                    <th class="all">Balance</th>                                 
                </tr>
            </thead>
            <tbody>
                <?php
                     $Total=array(0,0,0,0,0,0,0);
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    ?>
                    <tr>
                         <?php
                        $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $rowfilter['emp_id'] . "'"));
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
                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['name'])); ?>
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
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['total'];
                              $Total[0] = $rowfilter['total'] + $Total[0];
                            ?>                                 
                            </div>
                        </td>
                        <td><div class="form-group form-md-line-input "><?php echo $rowfilter['advance_paid_by_bank']; $Total[4] = $rowfilter['advance_paid_by_bank'] + $Total[4]; ?></div></td>
                        <td><div class="form-group form-md-line-input "><?php echo $rowfilter['pf_amount']; $Total[5] = $rowfilter['pf_amount'] + $Total[5]; ?></div></td>
                        <td><div class="form-group form-md-line-input "><?php echo $rowfilter['esic_amount']; $Total[6] = $rowfilter['esic_amount'] + $Total[6];?></div></td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['Fa']; 
                              $Total[1] = $rowfilter['Fa'] + $Total[1];
                            ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['Ta']; 
                              $Total[2] = $rowfilter['Ta'] + $Total[2];
                            ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['balance1']; 
                             $Total[3] = $rowfilter['balance1'] + $Total[3];?> 
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
            <td><?php echo $Total[0]; ?></td>
            <td><?php echo $Total[4]; ?></td>
            <td><?php echo $Total[5]; ?></td>
            <td><?php echo $Total[6]; ?></td>
            <td><?php echo $Total[1]; ?></td>
            <td><?php echo $Total[2]; ?></td>           
            <td><?php echo $Total[3]; ?></td>
          
        </tr>
         
        
        </tbody>
        </table>
      
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
