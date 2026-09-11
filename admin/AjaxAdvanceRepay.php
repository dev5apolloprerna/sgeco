<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');

function canAddAdvanceRepay($dbconn)
{
    if ($_SESSION['AdminType'] == 1) return true;
    $result = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
    $rights = $result ? mysqli_fetch_assoc($result) : array();
    return isset($rights['isAdvancedEntry']) && $rights['isAdvancedEntry'] == 1;
}

function isValidAdvanceRepayDate($date)
{
    $value = DateTime::createFromFormat('!Y-m-d', $date);
    return $value && $value->format('Y-m-d') === $date;
}

if (!canAddAdvanceRepay($dbconn)) {
    http_response_code(403);
    exit('Access denied.');
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$companyId = isset($_POST['companyId']) ? (int) $_POST['companyId'] : 0;
$sourceDate = isset($_POST['sourceDate']) ? trim($_POST['sourceDate']) : '';

if ($action === 'Preview') {
    $statement = mysqli_prepare($dbconn, "SELECT e.emp_name, e.employeecode, SUM(ad.iAmount) iAmount FROM advanced_details ad INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId INNER JOIN employee e ON e.employeeId=ad.iEmployeeId WHERE ad.iCompanyId=? AND DATE(ad.strDate)=? AND am.isDelete=0 AND am.istatus=1 GROUP BY ad.iEmployeeId, e.emp_name, e.employeecode ORDER BY e.emp_name");
    mysqli_stmt_bind_param($statement, 'is', $companyId, $sourceDate);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<div class="alert alert-info">No advance payments found for this company and date.</div>';
        exit;
    }
    $total = 0;
    echo '<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Employee</th><th>Code</th><th class="text-right">Amount</th></tr></thead><tbody>';
    while ($row = mysqli_fetch_assoc($result)) {
        $total += (float) $row['iAmount'];
        echo '<tr><td>' . htmlspecialchars(ucwords(strtolower($row['emp_name'])), ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($row['employeecode'], ENT_QUOTES, 'UTF-8') . '</td><td class="text-right">' . number_format((float) $row['iAmount'], 2) . '</td></tr>';
    }
    echo '<tr><th colspan="2">Total</th><th class="text-right">' . number_format($total, 2) . '</th></tr></tbody></table></div>';
    exit;
}

header('Content-Type: application/json');
$repayDate = isset($_POST['repayDate']) ? trim($_POST['repayDate']) : '';
if ($action !== 'Add' || $companyId < 1 || !isValidAdvanceRepayDate($sourceDate) || !isValidAdvanceRepayDate($repayDate) || $sourceDate >= $repayDate) {
    echo json_encode(array('success' => false, 'message' => 'Select a valid company, old payment date and repayment date.'));
    exit;
}

$company = mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE companymasterId=" . $companyId . " AND isDelete=0 AND istatus=1");
if (!$company || mysqli_num_rows($company) === 0) {
    echo json_encode(array('success' => false, 'message' => 'Selected company is not available.'));
    exit;
}

$monthYear = date('m/Y', strtotime($repayDate));

$escapedMonthYear = mysqli_real_escape_string($dbconn, $monthYear);
$masterResult = mysqli_query($dbconn, "SELECT iAdvancedMasterId FROM advanced_master WHERE iCompanyId=" . $companyId . " AND strMonthYear='" . $escapedMonthYear . "' AND isDelete=0 AND istatus=1 LIMIT 1");
$master = $masterResult ? mysqli_fetch_assoc($masterResult) : null;
if (!$master) {
    echo json_encode(array('success' => false, 'message' => 'Create an advanced master entry for the repayment month before submitting.'));
    exit;
}
$advancedMasterId = (int) $master['iAdvancedMasterId'];

mysqli_begin_transaction($dbconn);
$entryDateTime = date('d-m-Y H:i:s');
$entryBy = (int) $_SESSION['AdminId'];
$entryDate = date('Y-m-d');
$sql = "INSERT INTO advanced_details (iAdvancedMasterId, iEmployeeId, iCompanyId, iAmount, strDate, strRemarks, iBankId, strEntryDate, iEntryBy, EntryDate) SELECT ?, ad.iEmployeeId, ?, SUM(ad.iAmount), ?, CONCAT('Repay of advance dated ', ?), MAX(ad.iBankId), ?, ?, ? FROM advanced_details ad INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId WHERE ad.iCompanyId=? AND DATE(ad.strDate)=? AND am.isDelete=0 AND am.istatus=1 GROUP BY ad.iEmployeeId";
$statement = mysqli_prepare($dbconn, $sql);
mysqli_stmt_bind_param($statement, 'iisssisis', $advancedMasterId, $companyId, $repayDate, $sourceDate, $entryDateTime, $entryBy, $entryDate, $companyId, $sourceDate);
$success = mysqli_stmt_execute($statement);
$count = $success ? mysqli_stmt_affected_rows($statement) : 0;
mysqli_stmt_close($statement);

if ($success && $count > 0) mysqli_commit($dbconn);
else mysqli_rollback($dbconn);
echo json_encode(array('success' => $success && $count > 0, 'message' => $count > 0 ? $count . ' advance payment(s) repaid successfully.' : 'No advance payments were found.'));
