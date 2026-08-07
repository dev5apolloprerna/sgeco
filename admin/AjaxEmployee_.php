<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";

    if (isset($_REQUEST['Search_Txt'])) {
        if ($_POST['Search_Txt'] != '') {

            $where.=" and  emp_name like '%" . $_POST['Search_Txt'] . "%' ";
        }
    }

    $filterstr = "SELECT * FROM `employee`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
    $countstr = "SELECT count(*) as TotalRow FROM `employee`  " . $where . " and isDelete='0' and  istatus='1' ";

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
        <div class="table-responsive">
            <form name="frmparameter"  id="frmparameter" >
                <div class="row">
                    <div class="f_delet_btn">
                        <input type="hidden" value="employeedeletedata" name="action" id="action">
                        <input type="submit" name="deletedata" class="btn blue" value="Delete All">
                    </div>
                </div>
                <table class="table table-striped table-bordered table-hover dt-responsive" width="100%"
                       id="empdata">
                    <thead class="tbg">
                        <tr>

                            <th class="desktop">
                                <div class="md-checkbox">
                                    <input type="checkbox"  onclick="javascript:CheckAll();" id="check_listall" class="md-check" value="">
                                    <label for="check_listall">
                                        <span></span>
                                        <span class="check"></span>
                                        <span class="box"></span>
                                    </label>
                                </div>
                            </th>
                            <th class="desktop">Employee Name</th>
                            <th class="desktop">Designation</th>
                            <th class="desktop">Bank Name</th>
                            <!-- <th class="desktop">Mobile No</th> -->
                            <th class="desktop">ESIC No</th>
                            <th class="desktop">PF Code</th>
                            <th class="desktop">Employee Code</th>
                            <th class="desktop">UAN</th>
                            <th class="desktop">Account NO</th>
                            <th class="desktop">IFSC Code</th>
                            <th class="desktop">Date Of Birth</th>
                            <th class="desktop">Date Of Joining</th>
                            <th class="desktop">Address</th>
                            <th class="desktop">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                            ?>
                            <tr>

                                <td>
                                    <div class="md-checkbox">
                                        <!-- <input type="hidden" name="smsid[]" id="smsid<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['multicompanyid']; ?> "> -->
                                        <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['employeeId']; ?> ">
                                        <label for="check_list<?php echo $i; ?>">
                                            <span></span>
                                            <span class="check"></span>
                                            <span class="box"></span></label>
                                    </div>

                                </td> 
                                <td>
                                    <div class="form-group form-md-line-input ">
                                        <?php echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];} ?> 
                                    </div>
                                </td> 
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['designation']; ?> 
                                    </div>
                                </td>
                                <?php
                                $bank = "SELECT * FROM `bankmaster`  where  bankmasterId='" . $rowfilter['bankid'] . "' order by  bankmasterId desc";
                                $bankfilter = mysqli_query($dbconn, $bank);
                                $bankrowfilter = mysqli_fetch_array($bankfilter)
                                ?>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $bankrowfilter['bankname']; ?> 
                                    </div>
                                </td>
                                <!-- <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['mno']; ?> 
                                    </div>
                                </td>  -->
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['ecsno']; ?> 
                                    </div>
                                </td>                                                                      
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['pfcode']; ?> 
                                    </div>
                                </td>                                                
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['employeecode']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['uan']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['accountno']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofbirth']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofjoining']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['address']; ?> 
                                    </div>
                                </td>
                                <td style="width: 20%">
                                    <div class="form-group form-md-line-input">
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/EditEmployee.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Edit"><i class="fa fa-edit iconshowFirst"></i></i></a>
                                        <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['employeeId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                        <!--<a  class="btn blue" href="<?php echo $web_url; ?>admin/EmployeeLedger.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Ledger"><i class="fa fa-user"></i></i></a>-->
                                        <!--<a  class="btn blue" href="<?php // echo $web_url;  ?>admin/addempcomskill.php?token=<?php // echo $rowfilter['employeeId'];  ?>" title="skill"><i class="fa fa-bars"></i></i></a>-->
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/ViewDocument.php?token=<?php echo $rowfilter['employeeId']; ?>" title="View Document"><i class="fa fa-eye"></i></i></a>
                                    </div>
                                </td>

                                <?php
                                $i++;
                            }
                            ?>

                        </tr>
                    </tbody>
                </table>
            </form>
        </div>

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

    $dealer_res = mysqli_query($dbconn, 'delete from employee where employeeId in  ("' . $_REQUEST['ID'] . '")');
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
<script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
<script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>
<script>
                                $(document).ready(function () {
                                    $('#tableC').DataTable({
                                    });
                                });
</script>




<script>

    $(document).ready(function () {
        //              $('#defaultTextarea').characterCounter({alertclass: 'red'});
        $('#empdata').DataTable({
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
