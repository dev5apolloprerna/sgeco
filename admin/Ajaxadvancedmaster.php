<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include('User_Paging.php');

$rightsResult = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
$rights = mysqli_fetch_assoc($rightsResult);
if ($_SESSION['AdminType'] != 1 && (!isset($rights['isAdvancedEntry']) || $rights['isAdvancedEntry'] != 1)) {
    http_response_code(403);
    header('location:'.$web_url.'admin/login.php');	
    exit;
}

if ($_REQUEST['action'] == 'Delete') {
    $id = (int) $_REQUEST['ID'];
    $data = array('isDelete' => 1, 'iUpdatedBy' => $_SESSION['AdminId'], 'UpdatedDate' => date('Y-m-d'));
    $connect->updaterecord($dbconn, 'advanced_master', $data, ' where iAdvancedMasterId=' . $id);
    exit;
}

if ($_POST['action'] == 'ListUser') {
    $search = mysqli_real_escape_string($dbconn, isset($_POST['Search_Txt']) ? trim($_POST['Search_Txt']) : '');
    $where = " WHERE isDelete=0 AND istatus=1" . ($search === '' ? '' : " AND strMonthYear LIKE '%" . $search . "%'");
    $countResult = mysqli_query($dbconn, 'SELECT COUNT(*) AS TotalRow FROM advanced_master' . $where);
    $totalrecord = (int) mysqli_fetch_assoc($countResult)['TotalRow'];
    $per_page = $cateperpaging;
    $total_pages = max(1, ceil($totalrecord / $per_page));
    $show_page = max(1, (int) $_REQUEST['Page']);
    $startpage = ($show_page - 1) * $per_page;
    $result = mysqli_query($dbconn, 'SELECT * FROM advanced_master' . $where . ' ORDER BY iAdvancedMasterId DESC LIMIT ' . $startpage . ', ' . $per_page);
    if ($totalrecord > 0) { ?>
        <table class="table table-bordered table-hover center table-responsive" width="100%">
            <thead class="tbg">
                <tr>
                    <th>Month / Year</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th class="desktop">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($advanced = mysqli_fetch_assoc($result)) { ?><tr>
                        <td><?php echo htmlspecialchars($advanced['strMonthYear'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($advanced['fromdate'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($advanced['todate'])); ?></td>
                        <td>
                            <a class="btn blue" href="AddAdvancedDetails.php?token=<?php echo (int) $advanced['iAdvancedMasterId']; ?>" title="Add advanced details" aria-label="Add advanced details"><i class="fa fa-plus"></i></a> 
                            <button type="button" class="btn blue" onclick="editAdvanced(<?php echo (int) $advanced['iAdvancedMasterId']; ?>)" title="Edit"><i class="fa fa-edit"></i></button> 
                            <button type="button" class="btn blue" onclick="deleteAdvanced(<?php echo (int) $advanced['iAdvancedMasterId']; ?>)" title="Delete"><i class="fa fa-trash-o"></i></button>
                        </td>
                    </tr><?php } ?>
            </tbody>
        </table>
    <?php } else { ?><div class="alert alert-info clearfix profile-information">
            <h1 class="font-white text-center">No Data Found!</h1>
        </div><?php }
            if ($totalrecord > $per_page) {
                echo '<div class="pagination">' . paginate('', $show_page, $total_pages) . '</div>';
            }
        }
