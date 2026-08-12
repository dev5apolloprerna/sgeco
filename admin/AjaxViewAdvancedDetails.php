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
    header('location:'.$web_url.'admin/login.php');	
    exit;
}
function advancedDetailsPrimaryKey($dbconn)
{
    $result = mysqli_query($dbconn, "SHOW KEYS FROM advanced_details WHERE Key_name='PRIMARY'");
    $key = $result ? mysqli_fetch_assoc($result) : null;
    if (!$key || !preg_match('/^[A-Za-z0-9_]+$/', $key['Column_name'])) {
        return null;
    }
    return $key['Column_name'];
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
if (!in_array($action, array('ListAdvancedDetails', 'SearchAdvancedEmployees', 'UpdateAdvancedDetail', 'DeleteAdvancedDetail'), true)) {
    http_response_code(400);
    exit('Invalid request.');
}

$primaryKey = advancedDetailsPrimaryKey($dbconn);
if ($primaryKey === null) {
    http_response_code(500);
    exit('Advanced details primary key is not configured.');
}

if ($action === 'SearchAdvancedEmployees') {
    header('Content-Type: application/json');
    $search = isset($_POST['employeeSearch']) ? trim($_POST['employeeSearch']) : '';
    if (strlen($search) < 2) {
        echo json_encode(array('success' => true, 'employees' => array()));
        exit;
    }
    $likeSearch = '%' . $search . '%';
    $statement = mysqli_prepare($dbconn, "SELECT employeeId, emp_name, employeecode FROM employee WHERE isDelete=0 AND istatus=1 AND isExitEmployee=0 AND (emp_name LIKE ? OR employeecode LIKE ?) ORDER BY emp_name LIMIT 20");
    mysqli_stmt_bind_param($statement, 'ss', $likeSearch, $likeSearch);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $employees = array();
    while ($result && $employee = mysqli_fetch_assoc($result)) {
        $employees[] = array(
            'id' => (int) $employee['employeeId'],
            'name' => ucwords(strtolower($employee['emp_name'])),
            'code' => $employee['employeecode']
        );
    }
    mysqli_stmt_close($statement);
    echo json_encode(array('success' => true, 'employees' => $employees));
    exit;
}

if ($action === 'UpdateAdvancedDetail' || $action === 'DeleteAdvancedDetail') {
    header('Content-Type: application/json');
    $detailId = isset($_POST['detailId']) ? (int) $_POST['detailId'] : 0;
    $detailStatement = mysqli_prepare($dbconn, "SELECT am.fromdate, am.todate FROM advanced_details ad INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId WHERE ad.`" . $primaryKey . "`=?");
    mysqli_stmt_bind_param($detailStatement, 'i', $detailId);
    mysqli_stmt_execute($detailStatement);
    $detailResult = mysqli_stmt_get_result($detailStatement);
    $existingDetail = $detailResult ? mysqli_fetch_assoc($detailResult) : null;
    mysqli_stmt_close($detailStatement);
    if ($detailId <= 0 || !$existingDetail) {
        http_response_code(404);
        echo json_encode(array('success' => false, 'message' => 'Advanced detail not found.'));
        exit;
    }

    if ($action === 'DeleteAdvancedDetail') {
        $statement = mysqli_prepare($dbconn, "DELETE FROM advanced_details WHERE `" . $primaryKey . "`=?");
        mysqli_stmt_bind_param($statement, 'i', $detailId);
        $success = mysqli_stmt_execute($statement) && mysqli_stmt_affected_rows($statement) === 1;
        mysqli_stmt_close($statement);
        echo json_encode(array('success' => $success, 'message' => $success ? 'Advanced detail deleted successfully.' : 'Unable to delete advanced detail.'));
        exit;
    }

    $date = isset($_POST['advancedDate']) ? trim($_POST['advancedDate']) : '';
    $employeeId = isset($_POST['employeeId']) ? (int) $_POST['employeeId'] : 0;
    $amountValue = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    $dateValue = DateTime::createFromFormat('!Y-m-d', $date);
    $validDate = $dateValue && $dateValue->format('Y-m-d') === $date && $date >= $existingDetail['fromdate'] && $date <= $existingDetail['todate'];
    $employeeStatement = mysqli_prepare($dbconn, "SELECT e.bankid FROM employee e INNER JOIN bankmaster b ON b.bankmasterId=e.bankid AND b.isDelete=0 AND b.istatus=1 WHERE e.employeeId=? AND e.isDelete=0 AND e.istatus=1 AND e.isExitEmployee=0");
    mysqli_stmt_bind_param($employeeStatement, 'i', $employeeId);
    mysqli_stmt_execute($employeeStatement);
    $employeeResult = mysqli_stmt_get_result($employeeStatement);
    $employee = $employeeResult ? mysqli_fetch_assoc($employeeResult) : null;
    mysqli_stmt_close($employeeStatement);
    if (!$employee || !$validDate || !preg_match('/^\d+(\.\d{1,2})?$/', $amountValue) || (float) $amountValue <= 0 || strlen($remarks) > 1000) {
        http_response_code(422);
        echo json_encode(array('success' => false, 'message' => 'Select a valid employee and date, enter a positive amount (up to two decimal places), and keep remarks within 1000 characters.'));
        exit;
    }
    $bankId = (int) $employee['bankid'];
    $amount = number_format((float) $amountValue, 2, '.', '');
    $statement = mysqli_prepare($dbconn, "UPDATE advanced_details SET iEmployeeId=?, iBankId=?, iAmount=?, strDate=?, strRemarks=? WHERE `" . $primaryKey . "`=?");
    mysqli_stmt_bind_param($statement, 'iidssi', $employeeId, $bankId, $amount, $date, $remarks, $detailId);
    $success = mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
    echo json_encode(array('success' => $success, 'message' => $success ? 'Advanced detail updated successfully.' : 'Unable to update advanced detail.'));
    exit;
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
$details = mysqli_query($dbconn, "SELECT ad.*, ad.`" . $primaryKey . "` AS detailId, am.strMonthYear, am.fromdate AS periodFromDate, am.todate AS periodToDate, e.emp_name, e.employeecode, c.companyname, b.bankname" . $fromSql . " ORDER BY ad.strDate DESC, e.emp_name ASC LIMIT " . $offset . ', ' . $perPage);

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
                <th>Action</th>
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
                    <td class="text-nowrap">
                        <button type="button" class="btn blue edit-advanced-detail" title="Edit" data-id="<?php echo (int) $detail['detailId']; ?>" data-employee-id="<?php echo (int) $detail['iEmployeeId']; ?>" data-employee-name="<?php echo htmlspecialchars(ucwords(strtolower($detail['emp_name'])) . ' (' . $detail['employeecode'] . ')', ENT_QUOTES, 'UTF-8'); ?>" data-date="<?php echo htmlspecialchars($detail['strDate'], ENT_QUOTES, 'UTF-8'); ?>" data-min-date="<?php echo htmlspecialchars($detail['periodFromDate'], ENT_QUOTES, 'UTF-8'); ?>" data-max-date="<?php echo htmlspecialchars($detail['periodToDate'], ENT_QUOTES, 'UTF-8'); ?>" data-period="<?php echo htmlspecialchars($detail['strMonthYear'], ENT_QUOTES, 'UTF-8'); ?>" data-amount="<?php echo htmlspecialchars(number_format((float) $detail['iAmount'], 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" data-remarks="<?php echo htmlspecialchars($detail['strRemarks'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-edit"></i></button>
                        <button type="button" class="btn blue delete-advanced-detail" title="Delete" data-id="<?php echo (int) $detail['detailId']; ?>"><i class="fa fa-trash-o"></i></button>
                    </td>
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