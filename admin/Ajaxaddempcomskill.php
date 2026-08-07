<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    // $where = "where 1=1";
//    if (isset($_REQUEST['Search_Txt'])) {
//        if ($_POST['Search_Txt'] != '') {
//
//            $where.=" and  emp_name like '$_POST[Search_Txt]%'";
//        }
//    }

     $filterstr = "SELECT * from (SELECT companymaster.companyname,companymaster.companymasterId FROM `companymaster`   where   isDelete='0'  and  istatus='1')as t1 LEFT JOIN (SELECT comskill.companyid,comskill.skill FROM `comskill` WHERE comskill.empid='".$_REQUEST['employeeId']."')as t2 on t1.`companymasterId` = t2.companyid order by  t1.companymasterId asc";
    $countstr = "SELECT count(*) as TotalRow FROM `companymaster`  where   isDelete='0'  and  istatus='1' order by  companymasterId asc";

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
        <form  role="form"  method="POST"  action="" name="frmparameterrr"  id="frmparameterrr" enctype="multipart/form-data">
            <input type="hidden" value="empcompskill" name="action" id="action">

            <input type="hidden" value="<?php echo $_REQUEST['employeeId']; ?>" name="employeeId" id="employeeId">
            <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
            <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
            <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>

            <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>


            <table class="table table-bordered table-hover center table-responsive" width="100%" id="tableC">
                <thead class="tbg">
                    <tr>
                        <th class="all">Company</th>
                        <th class="all">Skill</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        ?>
                        <tr>
                            <td>
                                <input type="hidden" value="<?php echo $rowfilter['companymasterId']; ?>" name="companymasterId[]" id="companymasterId">
                                <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['companyname'])); ?> 
                                </div>
                            </td> 
                            <td>

                                <?php
                               // $q = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `comskill` WHERE `empid`='" . $_REQUEST['employeeId'] . "' and companyid='" . $rowfilter['companymasterId'] . "'"));
                                ?>
                                <select name="Skill[]" id="Skill"  class="form-control col-md-4" > 

                                    <option value=""<?php
                                    if ($rowfilter['skill'] == '') {
                                        echo 'selected';
                                    }
                                    ?>>Select Employee Skill</option>
                                    <option value="Skill" <?php
                                    if ($rowfilter['skill'] == 'Skill') {
                                        echo 'selected';
                                    }
                                    ?> >Skill</option>
                                    <option value="UnSkill" <?php
                                    if ($rowfilter['skill'] == 'UnSkill') {
                                        echo 'selected';
                                    }
                                    ?>>UnSkill</option>
                                    <option value="SemiSkill" <?php
                                    if ($rowfilter['skill'] == 'SemiSkill') {
                                        echo 'selected';
                                    }
                                    ?>>SemiSkill</option>
                                </select>


                            </td>


                            <?php
                            $bank = "SELECT * FROM `bankmaster`  where  bankmasterId='" . $rowfilter['bankid'] . "' order by  bankmasterId desc";
                            $bankfilter = mysqli_query($dbconn, $bank);
                            $bankrowfilter = mysqli_fetch_array($bankfilter)
                            ?>



                                                            <!--                        <td style="width: 20%">
                                                                                        <div class="form-group form-md-line-input">
                                                                                            <a  class="btn blue" href="<?php echo $web_url; ?>admin/EditEmployee.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Edit"><i class="fa fa-edit iconshowFirst"></i></i></a>
                                                                                            <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['employeeId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                                                                            <a  class="btn blue" href="<?php echo $web_url; ?>admin/EmployeeLedger.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Ledger"><i class="fa fa-user"></i></i></a>
                                                                                            <a  class="btn blue" href="<?php echo $web_url; ?>admin/addempcomskill.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Ledger"><i class="fa fa-user"></i></i></a>
                                                                                        </div>
                                                                                    </td>-->

                            <?php
                        }
                        ?>

                    </tr>
                </tbody>

            </table>
            <div class="form-group ">
                <input class="btn blue " type="submit" id="submit"  value="submit" name="submit">      

            </div>
        </form>
        <script type="text/javascript">


            function checkclose() {
                window.location.href = '<?php echo $web_url; ?>Employee/Employee.php';
            }

            $('#frmparameterrr').submit(function (e) {

                e.preventDefault();
                var $form = $(this);


                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: $('#frmparameterrr').serialize(),
                    success: function (response) {
                        //alert(response);
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Added Sucessfully.');
                        window.location.href = '<?php echo $web_url; ?>admin/Employee.php';
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
    $where = ' where  	employeeId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
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


