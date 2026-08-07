<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    $filterstr = "SELECT * FROM `multicompany` where isDelete='0'  and  istatus='1' and companysalarymasterId='" . $_POST['companysalarymasterId'] . "' order by multicompanyid desc";
    $countstr = "SELECT count(*) as TotalRow FROM  `multicompany` where   isDelete='0'  and  istatus='1' and companysalarymasterId ='" . $_POST['companysalarymasterId'] . "' ";

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
            
            <form name="frmparameter"  id="frmparameter" >
                <div class="row">
                    <div class="f_delet_btn" style="padding: 0;">
                        <input type="hidden" value="multicompanydeletedata" name="action" id="action">
                        <input type="submit" name="deletedata" class="btn blue" value="Delete All">
                    </div>
                </div>
                <div class="table-responsive table-responsive-new">
                    <table class="table table-striped table-bordered table-hover dt-responsive" width="100%"  id="tableC">
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
                                <th class="desktop">Sr.No</th>
                                <th class="desktop">Name</th>
                                <th class="desktop">Rate</th>                    
                                <th class="desktop">No of<br />Days<br />Worked</th>
                                <th class="desktop">O.T.<br />HOURS</th>
                                <th class="desktop">O.T.<br />Amount</th>
                                <th class="desktop">ADV</th>
                                <th class="desktop">ADV PAY</th>
                                <th class="desktop">ADV TWO</th>
                                <th class="desktop">ADV TWO PAY</th>
                                <th class="desktop">F.A</th>
                                <th class="desktop">T.A</th>
                                <th class="desktop">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Total = array(0, 0, 0, 0);
                            while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                                ?>
                                <tr>
                                    <?php
                                    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0'  and  istatus='1' and employeeId='" . $rowfilter['emp_id'] . "'"));
                                    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));
                                    ?>
                                    <td>
                                        <div class="md-checkbox">
                                            <!-- <input type="hidden" name="smsid[]" id="smsid<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['multicompanyid']; ?> "> -->
                                            <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['multicompanyid']; ?> ">
                                            <label for="check_list<?php echo $i; ?>">
                                                <span></span>
                                                <span class="check"></span>
                                                <span class="box"></span></label>
                                        </div>
                                    </td> 
                                    <td><?php echo $i; ?></td>
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
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['otamt']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['adv']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['adv_one_paid']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['adv_two']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['adv_two_paid']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php
                                            echo $rowfilter['Fa'];
                                            ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php
                                            echo $rowfilter['Ta'];
                                            ?> 
                                        </div>
                                    </td>
                                    <td style="width: 10%">
                                        <div class="form-group form-md-line-input">
                                            <a onClick="javascript: return setEditdata('<?php echo $rowfilter['multicompanyid']; ?>');" type="button" class="btn blue"  id="clickbutton"><i class="fa fa-edit iconshowFirst"></i></a>
                                            <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['multicompanyid']; ?>');" type="button" id="clickbutton"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>
        

        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>
        <script>

                                            $(document).ready(function () {
                                                //              $('#defaultTextarea').characterCounter({alertclass: 'red'});
                                                $('#tableC').DataTable({
                                                });

                                                $('#frmparameter').submit(function (e) {
                                                    e.preventDefault();
                                                    var $form = $(this);
                                                    $('#loading').css("display", "block");
                                                    $.ajax({
                                                        type: 'POST',
                                                        url: 'querydata.php',
                                                        data: $('#frmparameter').serialize(),
                                                        success: function (response) {
                                                            if (response == 1)
                                                            {
                                                                $('#loading').css("display", "none");
                                                                $("#Btnmybtn").attr('disabled', 'disabled');
                                                                alert('Deleted Sucessfully.');
                                                                window.location.href = '';
                                                            } else
                                                            {
                                                                $('#loading').css("display", "none");
                                                                $("#Btnmybtn").attr('disabled', 'disabled');
                                                                alert('Not Deleted  Please Try Again.');
                                                                window.location.href = '';
                                                            }
                                                        }

                                                    });
                                                });
                                            });

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
    $CheckList = $_REQUEST['ID'];
    //echo 'delete from employee where employeeId in ('.implode("," ,  $_POST['check_list']).')';
    $dealer_res = mysqli_query($dbconn, 'delete from multicompany where multicompanyid in  ("' . $_REQUEST['ID'] . '")');
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