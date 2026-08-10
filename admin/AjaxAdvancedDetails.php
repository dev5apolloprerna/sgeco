<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();

function advancedDetailsAccess($dbconn)
{
    if ($_SESSION['AdminType'] == 1) return true;
    $result = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
    $rights = mysqli_fetch_assoc($result);
    return isset($rights['isAdvancedEntry']) && $rights['isAdvancedEntry'] == 1;
}

function advancedPeriod($dbconn, $advancedId)
{
    $result = mysqli_query($dbconn, "SELECT * FROM advanced_master WHERE iAdvancedMasterId=" . (int) $advancedId . " AND isDelete=0 AND istatus=1");
    return $result ? mysqli_fetch_assoc($result) : null;
}

function validDetailDate($date, $advanced)
{
    $value = DateTime::createFromFormat('!Y-m-d', $date);
    return $value && $value->format('Y-m-d') === $date && $date >= $advanced['fromdate'] && $date <= $advanced['todate'];
}

if (!advancedDetailsAccess($dbconn)) {
    http_response_code(403);
    header('location:'.$web_url.'admin/login.php');	
    exit;
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$advancedId = isset($_POST['advancedId']) ? (int) $_POST['advancedId'] : 0;
$advanced = advancedPeriod($dbconn, $advancedId);
if (!$advanced) {
    http_response_code(400);
    exit('Invalid advanced period.');
}

if ($action === 'SearchEmployees') {
    $search = mysqli_real_escape_string($dbconn, isset($_POST['employeeSearch']) ? trim($_POST['employeeSearch']) : '');
    $where = $search === '' ? '' : " AND (emp_name LIKE '%" . $search . "%' OR employeecode LIKE '%" . $search . "%')";
    $employees = mysqli_query($dbconn, "SELECT employeeId, emp_name, employeecode, uan, bankid FROM employee WHERE isDelete=0 AND istatus=1 AND isExitEmployee=0" . $where . " ORDER BY emp_name LIMIT 100");
    if (!$employees || mysqli_num_rows($employees) === 0) {
        echo '<div class="alert alert-info"><h4 class="text-center">No Data Found!</h4></div>';
        exit;
    }


?>
    <table class="table table-bordered table-hover table-responsive">
        <thead class="tbg">
            <tr>
                <th>Employee Name</th>
                <th>Employee Code</th>
                <th>UAN</th>
                <th>Amount</th>
                <th>Remarks</th>
                <!-- <th>Action</th> -->
            </tr>
        </thead>
        <tbody>
            <?php while ($employee = mysqli_fetch_assoc($employees)) { ?>
            <tr class="employee-row" data-employee-id="<?php echo (int) $employee['employeeId']; ?>">

                    <td><?php echo htmlspecialchars(ucwords(strtolower($employee['emp_name'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['employeecode'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['uan'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control advanced-amount" placeholder="0.00">
                        <input type="hidden" class="advanced-bank" value="<?php echo (int) $employee['bankid']; ?>">
                    </td>
                    <td><input type="text" maxlength="1000" class="form-control advanced-remarks" placeholder="Remarks"></td>
                    <!-- <td><button type="button" class="btn blue" onclick="addAdvancedDetail(<?php echo (int) $employee['employeeId']; ?>, this)">Add</button></td> -->
                </tr><?php } ?></tbody>
    </table>
    <button type="button" class="btn blue pull-right" onclick="submitAdvancedDetails(this)"><i class="fa fa-save"></i> Submit</button>
    <div class="clearfix"></div>
<?php exit;
}

header('Content-Type: application/json');
if ($action === 'AddAdvancedDetails') {
    $companyId = (int) $advanced['iCompanyId'];
    $date = isset($_POST['advancedDate']) ? trim($_POST['advancedDate']) : '';
    $details = json_decode(isset($_POST['details']) ? $_POST['details'] : '', true);
    $validCompany = mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE companymasterId=" . $companyId . " AND isDelete=0 AND istatus=1");
    if (!validDetailDate($date, $advanced) || !$validCompany || mysqli_num_rows($validCompany) === 0 || !is_array($details) || count($details) === 0 || count($details) > 100) {
        echo json_encode(array('success' => false, 'message' => 'Select a valid date and enter an amount for at least one employee.'));
        exit;
    }

    $employeeStatement = mysqli_prepare($dbconn, "SELECT e.bankid FROM employee e INNER JOIN bankmaster b ON b.bankmasterId=e.bankid AND b.isDelete=0 AND b.istatus=1 WHERE e.employeeId=? AND e.isDelete=0 AND e.istatus=1 AND e.isExitEmployee=0");
    $insertStatement = mysqli_prepare($dbconn, "INSERT INTO advanced_details (iAdvancedMasterId, iEmployeeId, iCompanyId, iAmount, strDate, strRemarks, iBankId, strEntryDate, iEntryBy, EntryDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$employeeStatement || !$insertStatement) {
        echo json_encode(array('success' => false, 'message' => 'Unable to prepare advanced details.'));
        exit;
    }
    mysqli_begin_transaction($dbconn);
    $employeeIds = array();
    $success = true;
    foreach ($details as $detail) {
        $employeeId = isset($detail['employeeId']) ? (int) $detail['employeeId'] : 0;
        $amountValue = isset($detail['amount']) ? trim((string) $detail['amount']) : '';
        $remarks = isset($detail['remarks']) ? trim((string) $detail['remarks']) : '';
        if ($employeeId <= 0 || isset($employeeIds[$employeeId]) || !preg_match('/^\d+(\.\d{1,2})?$/', $amountValue) || (float) $amountValue <= 0 || strlen($remarks) > 1000) {
            $success = false;
            break;
        }
        $employeeIds[$employeeId] = true;
        mysqli_stmt_bind_param($employeeStatement, 'i', $employeeId);
        if (!mysqli_stmt_execute($employeeStatement)) {
            $success = false;
            break;
        }
        $employeeResult = mysqli_stmt_get_result($employeeStatement);
        $employee = $employeeResult ? mysqli_fetch_assoc($employeeResult) : null;
        if (!$employee) {
            $success = false;
            break;
        }
        $bankId = (int) $employee['bankid'];
        $amount = number_format((float) $amountValue, 2, '.', '');
        $entryDateTime = date('d-m-Y H:i:s');
        $entryBy = (int) $_SESSION['AdminId'];
        $entryDate = date('Y-m-d');
        mysqli_stmt_bind_param($insertStatement, 'iiidssisis', $advancedId, $employeeId, $companyId, $amount, $date, $remarks, $bankId, $entryDateTime, $entryBy, $entryDate);
        if (!mysqli_stmt_execute($insertStatement)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        mysqli_commit($dbconn);
    } else {
        mysqli_rollback($dbconn);
    }
    mysqli_stmt_close($employeeStatement);
    mysqli_stmt_close($insertStatement);
    echo json_encode(array('success' => $success, 'message' => $success ? count($details) . ' advanced detail(s) added successfully.' : 'No details were added. Check each selected employee, bank, amount, and remarks.'));
    exit;
}
echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
?>