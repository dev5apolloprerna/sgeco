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
            $where.=" and  username like '%" . $_POST['Search_Txt'] . "%' ";
        }
    }

    $filterstr = "SELECT * FROM `admin` " . $where . " and isDelete='0' and id!=1 and  istatus='1' order by id desc";
    $countstr = "SELECT count(*) as TotalRow FROM `admin` " . $where . " and id!=1 and isDelete='0' and  istatus='1' ";

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
        
            <!--<form name="frmparameter"  id="frmparameter" >-->
            <!--    <div class="row">-->
            <!--        <div class="f_delet_btn">-->
                        <!--<input type="hidden" value="employeedeletedata" name="action" id="action">-->
            <!--            <input type="button" name="deletedata" onclick="DeleteTempEmployee();" class="btn blue" value="Delete">-->
                        
            <!--            <input type="hidden" value="tempemployeemovedata" name="action" id="action">-->
            <!--            <input type="submit" name="deletedata" class="btn blue" value="Move">-->
                        
            <!--            <input type="button" name="deletedata" class="btn blue" onclick="employeeaddtoexportlist();" value="Add To Export List">-->
            <!--            <input type="button" class="btn blue" onclick="ViewExportList();" style="float: right;" value="Export List">-->
            <!--        </div>-->
            <!--    </div>-->
                <div class="table-responsive table-responsive-new">
                    <table class="table table-striped table-bordered table-hover dt-responsive " width="100%"
                       id="empdata">
                    <thead class="tbg">
                        <tr>
                            <!--<th class="desktop">-->
                            <!--    <div class="md-checkbox">-->
                            <!--        <input type="checkbox"  onclick="javascript:CheckAll();" id="check_listall" class="md-check" value="">-->
                            <!--        <label for="check_listall">-->
                            <!--            <span></span>-->
                            <!--            <span class="check"></span>-->
                            <!--            <span class="box"></span>-->
                            <!--        </label>-->
                            <!--    </div>-->
                            <!--</th>-->
                            <th class="desktop">User Name</th>
                            <th class="desktop">Login id</th>
                            <th class="desktop">Email</th>
                            <th class="desktop">Mobile</th>
                            <th class="desktop">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                            ?>
                            <tr>
                                <!--<td>-->
                                <!--    <div class="md-checkbox">-->
                                <!--        <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['iTempEmpId']; ?> ">-->
                                <!--        <label for="check_list<?php echo $i; ?>">-->
                                <!--            <span></span>-->
                                <!--            <span class="check"></span>-->
                                <!--            <span class="box"></span></label>-->
                                <!--    </div>-->

                                <!--</td> -->
                                <td>
                                    <div class="form-group form-md-line-input ">
                                        <?php echo $rowfilter['username']; ?> 
                                    </div>
                                </td> 
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['loginid']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['email']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['mobileNo']; ?> 
                                    </div>
                                </td>
                                <td style="width: 20%">
                                    <div class="form-group form-md-line-input">
                                        <a  class="btn blue" href="#" onclick="changepassword(<?php echo $rowfilter['id']; ?>);" data-toggle="modal" data-target="#exampleModal" title="Change Password"><i class="fa fa-key iconshowFirst"></i></i></a>
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/EditUser.php?token=<?php echo $rowfilter['id']; ?>" title="Edit"><i class="fa fa-edit iconshowFirst"></i></i></a>
                                        <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['id']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/userRights.php?token=<?php echo $rowfilter['id']; ?>" title="Users Rights"><i class="fa fa-plus iconshowFirst"></i></i></a>
                                    </div>
                                </td>

                                <?php
                                $i++;
                            }
                            ?>

                        </tr>
                    </tbody>
                </table>
                </div>
            <!--</form>-->
        

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
    mysqli_query($dbconn, 'delete from admin where id="' . $_REQUEST['ID'] . '"');
}
?>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #658397;color: #fff;">
                <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">X</span>
                </button>
            </div>
            <form  role="form"  method="POST"  action="" name="frmchangepwd"  id="frmchangepwd" enctype="multipart/form-data">
                <input type="hidden" value="AddChangePassword" name="action" id="action">
                <input type="hidden" value="" name="iUserId" id="iUserId">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="form_control_1">Password</label>
                            <input name="password" id="password" class="form-control" required placeholder="Enter Password"   type="password">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="form_control_1">Confirm Password</label>
                            <input name="cpassword" id="cpassword" class="form-control" required placeholder="Enter Password"   type="password">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" onclick="updatepassword();" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    function changepassword(id){
        $("#iUserId").val(id);
    }
    
    function updatepassword(){
        var password = $("#password").val();
        var cpassword = $("#cpassword").val();
        if(cpassword === password){
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: 'paymentquerydata.php',
                data: $('#frmchangepwd').serialize(),
                success: function (response) {
                    console.log(response);
                    if (response == 'Sucess')
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Password Change Sucessfully.');
                        window.location.href = '';
                    } else
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Invalid Request. Please Try Again.');
                        window.location.href = '';
                    }
                }
    
            });
        }else{
            alert('Password and Confirm Password Not Match.');
            return false;
        }
    }
</script>


