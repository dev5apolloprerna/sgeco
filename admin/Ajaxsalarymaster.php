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

            $where.=" and  month like '%$_POST[Search_Txt]%'";
        }
    }
    $filterstr = "SELECT * FROM `salarymaster`  " . $where . " and isDelete='0'  and  istatus='1' order by salarymasterId desc";
    $countstr = "SELECT count(*) as TotalRow FROM `salarymaster` " . $where . " and isDelete='0' and  istatus='1' ";

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
                    <th class="all">Month</th>                    
                    <th class="all">From Date</th>
                    <th class="all">To Date</th>
                    <th class="all">Company Name</th>
                    <th class="all">Salary  Date</th>
                    <th class="desktop">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                    ?>
                    <tr>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['month']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['fromdate']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['todate']; ?> 
                            </div>
                        </td>
                         <?php
                       
                        $comp = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT * FROM companymaster where companymasterId='".$rowfilter['companymasterId']."' "));
                        
                                ?>
                         <td>
                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($comp['companyname'])); ?> 
                            </div>
                        </td>
                         <td>
                            <div class="form-group form-md-line-input "><?php echo $rowfilter['salarypaiddate']; ?> 
                            </div>
                        </td>
                        <td>
                            <div class="form-group form-md-line-input ">                                  
                                <a class="btn blue" onClick="javascript: return setEditdata('<?php echo $rowfilter['salarymasterId']; ?>');"  title="Edit"><i class="fa fa-edit iconshowFirst"></i></a> 
                                <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['salarymasterId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                <a  class="btn blue" href="<?php echo $web_url; ?>admin/AddSalaryDetails.php?token=<?php echo $rowfilter['salarymasterId']; ?>" title="ADD Salary Details"><i class="fa fa-plus iconshowFirst"></i></i></a>
                                
                                <a  class="btn blue" href="<?php echo $web_url; ?>admin/AddPermanentEmployeeSalaryDetails.php?token=<?php echo $rowfilter['salarymasterId']; ?>" title="ADD Permanent Employee Salary Details"><i class="fa fa-user-plus iconshowFirst"></i></i></a>
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
    $where = ' where salarymasterId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'salarymaster', $data, $where);
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

