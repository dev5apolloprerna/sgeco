<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";

   

    $filterstr = "SELECT * FROM `ledger`  " . $where . " and isDelete=0 and emp_id='".$_REQUEST['token']."'   and  istatus='1' order by  ledgerid asc";
    $countstr = "SELECT count(*) as TotalRow FROM `ledger`  " . $where . " and emp_id='".$_REQUEST['token']."' and isDelete='0' and  istatus='1' ";

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

   <form  role="form"  method="POST"  action="" name="frmparameterrr"  id="frmparameterrr" enctype="multipart/form-data">
            <input type="hidden" value="daleteall" name="action" id="action">
            <div class="form-group col-md-4">
                            <input class="btn blue " type="submit" id="Btnmybtn"  value="Delete all" name="submit">      
                             
                        </div>
        <table class="table table-bordered table-hover center table-responsive" width="100%" id="tableC">
            <thead class="tbg">
                <tr>
                    <th>
                            <div class="md-checkbox">
                                <input type="checkbox"  onclick="javascript:CheckAll();" id="check_listall" class="md-check" value="">
                                <label for="check_listall">
                                    <span></span>
                                    <span class="check"></span>
                                    <span class="box"></span>
                                </label>

                            </div>
                        </th>
                    <th class="all">Sr.No</th>
                    <th class="desktop">Date</th>
                    <th class="desktop">Comment</th>
                    <th class="all">Opening<br />Balance</th>
                      <th class="all">Debit</th>
                     <th class="all">Credit</th>
                     <th class="desktop">Colsing<br />Balance</th>
                    <th class="desktop">Action</th>
                  
                </tr>
            </thead>
            <tbody>
                <?php
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    ?>
                
                    <tr>
                         <td>
                                <!--                                <div class="form-group form-md-line-input ">-->
                                <!--                                    <div class="form-group form-md-checkboxes">-->
                                <!--                                        <div class="md-checkbox-inline">-->
                                <div class="md-checkbox">
                                    <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check " value="<?php echo $rowfilter['ledgerid']; ?> ">
                                    <label for="check_list<?php echo $i; ?>">
                                        <span></span>
                                        <span class="check"></span>
                                        <span class="box"></span>
                                    </label>
                                </div>
                                <!--                                         </div>-->
                                <!--                                    </div>-->
                                <!--                                </div>-->
                            </td>
                        <?php
                       // SELECT SUM(`credit`) as credit,SUM(`debit`) as debit FROM `ledger` WHERE `emp_id`=1
                         $emp = mysqli_fetch_array(mysqli_query($dbconn,"SELECT * FROM `employee`  where  employeeId='" . $rowfilter['emp_id'] . "' order by  employeeId desc"));
                        
                        ?>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $i; ?> 
                            </div>
                        </td> 
                         <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['strEntryDate']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['comment']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['opbalance']; ?> 
                            </div>
                        </td> 
                         <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['debit']; ?> 
                            </div>
                        </td> 
                         <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['credit']; ?> 
                            </div>
                        </td>
                       
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['balance']; ?> 
                            </div>
                        </td>
                         <td>
                            <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['ledgerid']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                        </td>
                      
                       

                        <?php
                        $i++;
                    }
                    ?>

                </tr>
            </tbody>
        </table>
   </form>
          <script type="text/javascript">


                                function checkclose() {
                                    window.location.href = '<?php echo $web_url; ?>Employee/AjaxEmployeeLedger.php';
                                }

                                $('#frmparameterrr').submit(function (e) {

                                    e.preventDefault();
                                    var $form = $(this);


                                    $.ajax({
                                        type: 'POST',
                                          url: '<?php echo $web_url; ?>admin/querydata.php',
                                        data: $('#frmparameterrr').serialize(),
                                        success: function (response) {
                                            // alert(response);
                                            $("#Btnmybtn").attr('disabled', 'disabled');
                                            alert('Delete Sucessfully.');
                                            window.location.href = '<?php echo $web_url; ?>admin/EmployeeLedger.php?token=<?php echo $_REQUEST['token'] ?>';
                                        }
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

if ($_REQUEST['action'] == 'Delete') {
    $data = array(
        "isDelete" => '1',
        "strEntryDate" => date('d-m-Y H:i:s')
    );
    $where = " where ledgerid='". $_REQUEST['ID'] ."'";
    $dealer_res = $connect->updaterecord($dbconn, 'ledger', $data, $where);
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
        function CheckAll()
        {
            
            if ($('#check_listall').is(":checked"))
            {
                // alert('cheked');
                $('input[type=checkbox]').each(function () {
                    $(this).prop('checked', true);
                });
            } else
            {
                //alert('cheked fail');
                $('input[type=checkbox]').each(function () {
                    $(this).prop('checked', false);
                });
            }
        }
    </script>

