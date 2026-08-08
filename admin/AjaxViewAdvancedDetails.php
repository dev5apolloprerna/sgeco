<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
include('User_Paging.php');

function canViewAdvancedDetails($dbconn)
{
    if ($_SESSION['AdminType'] == 1) {
        return true;
    }
    $result = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
    $rights = $result ? mysqli_fetch_assoc($result) : null;
    return isset($rights['isAdvancedEntry']) && $rights['isAdvancedEntry'] == 1;
}

if (!canViewAdvancedDetails($dbconn)) {
    http_response_code(403);
    exit('Access denied.');
}
if (!isset($_POST['action']) || $_POST['action'] !== 'ListAdvancedDetails') {
    http_response_code(400);
    exit('Invalid request.');
}

$page = isset($_POST['Page']) ? max(1, (int) $_POST['Page']) : 1;
$perPage = isset($cateperpaging) ? max(1, (int) $cateperpaging) : 15;
$companyId = isset($_POST['companyId']) ? (int) $_POST['companyId'] : 0;
$advancedId = isset($_POST['advancedId']) ? (int) $_POST['advancedId'] : 0;
$employeeSearch = mysqli_real_escape_string($dbconn, isset($_POST['employeeSearch']) ? trim($_POST['employeeSearch']) : '');
$where = array('1=1');
if ($companyId > 0) {
    $where[] = 'ad.iCompanyId=' . $companyId;
}
if ($advancedId > 0) {
    $where[] = 'ad.iAdvancedMasterId=' . $advancedId;
}
if ($employeeSearch !== '') {
    $where[] = "(e.emp_name LIKE '%" . $employeeSearch . "%' OR e.employeecode LIKE '%" . $employeeSearch . "%')";
}
$whereSql = implode(' AND ', $where);
$fromSql = " FROM advanced_details ad INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId INNER JOIN employee e ON e.employeeId=ad.iEmployeeId INNER JOIN companymaster c ON c.companymasterId=ad.iCompanyId LEFT JOIN bankmaster b ON b.bankmasterId=ad.iBankId WHERE " . $whereSql;
$countResult = mysqli_query($dbconn, 'SELECT COUNT(*) AS TotalRow' . $fromSql);
$countRow = $countResult ? mysqli_fetch_assoc($countResult) : array('TotalRow' => 0);
$totalRecords = (int) $countRow['TotalRow'];
$totalPages = (int) ceil($totalRecords / $perPage);
$offset = ($page - 1) * $perPage;
$details = mysqli_query($dbconn, "SELECT ad.*, am.strMonthYear, e.emp_name, e.employeecode, c.companyname, b.bankname" . $fromSql . " ORDER BY ad.strDate DESC, e.emp_name ASC LIMIT " . $offset . ', ' . $perPage);

if (!$details || mysqli_num_rows($details) === 0) {
    echo '<div class="alert alert-info"><h4 class="text-center">No Data Found!</h4></div>';
    exit;
}
?>
<div class="table-responsive table-responsive-new">
    <table class="table table-striped table-bordered table-hover">
        <thead class="tbg">
            <tr>
                <th>Advanced Period</th>
                <th>Employee Name</th>
                <th>Employee Code</th>
                <th>Company</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Bank</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($detail = mysqli_fetch_assoc($details)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($detail['strMonthYear'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucwords(strtolower($detail['emp_name'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($detail['employeecode'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($detail['companyname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($detail['strDate'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(number_format((float) $detail['iAmount'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(isset($detail['bankname']) ? $detail['bankname'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($detail['strRemarks'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php if ($totalPages > 1) { ?>
    <div class="pagination-panel">
        <ul class="pagination pull-right"><?php echo paginate('', $page, $totalPages); ?></ul>
        <div class="clearfix"></div>
    </div>
<?php } ?>