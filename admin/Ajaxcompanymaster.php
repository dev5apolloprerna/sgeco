<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1 ";
    if (isset($_REQUEST['Search_Txt'])) {
        if ($_POST['Search_Txt'] != '') {
            $where.=" and  companyname like '%$_POST[Search_Txt]%'";
        }
    }

    $filterstr = "SELECT * FROM `companymaster`  " . $where . " and isDelete='0'  and  istatus='1' order by companymasterId desc";
    $countstr = "SELECT count(*) as TotalRow FROM `companymaster` " . $where . " and isDelete='0' and  istatus='1' ";

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


        <table class="table table-bordered table-hover center table-responsive" width="100%" id="tableC">
            <thead class="tbg">
                <tr>
                    <th class="all">Company Name</th>
                    <th class="all">Highly Skill Rate</th>
                    <th class="all">Skil Rate</th>
                    <th class="all">UnSkill Rate</th>
                    <th class="all">Semi Skill Rate</th>
                    
                    <th class="all">ESI</th>
                    <th class="all">Professional Tax</th>
                    <th class="all">Provident Fund (PF)</th>
                    <th class="all">Medical Allowance</th>
                    <th class="all">Medical Allowance Per</th>
                    <th class="all">Bonus</th>
                    <th class="all">Leave</th>
                    <th class="all">Daily Working Hours</th>
                    <th class="desktop">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    ?>
                    <tr>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['companyname'])); ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['highlyskilled']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['skil']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['unskill']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['semiskill']; ?> 
                            </div>
                        </td>
                        
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['ESI']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['pf']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['ProvidentFund']; ?>
                            </div>
                        </td>
                         <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['MedicalAllowance']; ?> 
                            </div>
                        </td>
                         <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['MedicalAllowancePer']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['strBonus']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['strLeave']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['iDailyWorkingRate']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input icon_set"> 
                                <a class="btn blue" onClick="javascript: return setEditdata('<?php echo $rowfilter['companymasterId']; ?>');"  title="Edit"><i class="fa fa-edit iconshowFirst"></i></a> 
                                <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['companymasterId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                            </div>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
            </tbody>
        </table>
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
    $where = ' where companymasterId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'companymaster', $data, $where);
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

