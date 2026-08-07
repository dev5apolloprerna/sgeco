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
    exit('Access denied.');
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$advancedId = isset($_POST['advancedId']) ? (int) $_POST['advancedId'] : 0;
$advanced = advancedPeriod($dbconn, $advancedId);
if (!$advanced) {
    http_response_code(400);
    exit('Invalid advanced period.');
}

if ($action === 'SearchEmployees') {
    $companyId = isset($_POST['companyId']) ? (int) $_POST['companyId'] : 0;
    $date = isset($_POST['advancedDate']) ? trim($_POST['advancedDate']) : '';
    $company = mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE companymasterId=" . $companyId . " AND isDelete=0 AND istatus=1");
    if (!$company || mysqli_num_rows($company) === 0 || !validDetailDate($date, $advanced)) {
        http_response_code(400);
        exit('Select a valid company and a date within the advanced period.');
    }
    $search = mysqli_real_escape_string($dbconn, isset($_POST['employeeSearch']) ? trim($_POST['employeeSearch']) : '');
    $where = $search === '' ? '' : " AND (emp_name LIKE '%" . $search . "%' OR employeecode LIKE '%" . $search . "%')";
    $employees = mysqli_query($dbconn, "SELECT employeeId, emp_name, employeecode, uan, bankid FROM employee WHERE isDelete=0 AND istatus=1 AND isExitEmployee=0" . $where . " ORDER BY emp_name LIMIT 100");
    if (!$employees || mysqli_num_rows($employees) === 0) {
        echo '<div class="alert alert-info"><h4 class="text-center">No Data Found!</h4></div>';
        exit;
    }

    $advancedId = isset($_GET['token']) ? (int) $_GET['token'] : 0;
    $advancedResult = mysqli_query($dbconn, "SELECT * FROM advanced_master WHERE iAdvancedMasterId=" . $advancedId . " AND isDelete=0 AND istatus=1");
    if (!$advancedResult || mysqli_num_rows($advancedResult) === 0) {
        http_response_code(404);
        exit('Advanced date range not found.');
    }
    $advancedPeriod = mysqli_fetch_assoc($advancedResult);
?>
    <table class="table table-bordered table-hover table-responsive">
        <thead class="tbg">
            <tr>
                <th>Employee Name</th>
                <th>Employee Code</th>
                <th>UAN</th>
                <th>Company</th>
                <th>Date</th>
                <th>Amount</th>

                <th>Remarks</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($employee = mysqli_fetch_assoc($employees)) { ?><tr>
                    <td><?php echo htmlspecialchars(ucwords(strtolower($employee['emp_name'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['employeecode'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <select class="form-control" id="companyId" required>
                            <option value="">Select company</option>
                            <?php $companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete=0 AND istatus=1 ORDER BY companyname");
                            while ($company = mysqli_fetch_assoc($companies)) { ?>
                                <option value="<?php echo (int) $company['companymasterId']; ?>"><?php echo htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>

                    </td>
                    <td>
                        <input class="form-control" type="date" id="advancedDate" min="<?php echo $advancedPeriod['fromdate']; ?>" max="<?php echo $advancedPeriod['todate']; ?>" required><span class="help-block">Date must be between <?php echo date('d-m-Y', strtotime($advancedPeriod['fromdate'])); ?> and <?php echo date('d-m-Y', strtotime($advancedPeriod['todate'])); ?>
                    </td>
                    <td><?php echo htmlspecialchars($employee['uan'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control advanced-amount" placeholder="0.00">
                        <input type="hidden" class="advanced-bank" value="<?php echo (int) $employee['bankid']; ?>">
                    </td>
                    <td><input type="text" maxlength="1000" class="form-control advanced-remarks" placeholder="Remarks"></td>
                    <td><button type="button" class="btn blue" onclick="addAdvancedDetail(<?php echo (int) $employee['employeeId']; ?>, this)">Add</button></td>
                </tr><?php } ?></tbody>
    </table>
<?php exit;
}

header('Content-Type: application/json');
if ($action === 'AddAdvancedDetail') {
    $companyId = (int) $_POST['companyId'];
    $employeeId = (int) $_POST['employeeId'];
    $bankId = (int) $_POST['bankId'];
    $date = trim($_POST['advancedDate']);
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    $validCompany = mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE companymasterId=" . $companyId . " AND isDelete=0 AND istatus=1");
    $validEmployee = mysqli_query($dbconn, "SELECT employeeId, bankid FROM employee WHERE employeeId=" . $employeeId . " AND isDelete=0 AND istatus=1 AND isExitEmployee=0");
    $employee = $validEmployee ? mysqli_fetch_assoc($validEmployee) : null;
    $bankId = $employee ? (int) $employee['bankid'] : 0;
    $validBank = mysqli_query($dbconn, "SELECT bankmasterId FROM bankmaster WHERE bankmasterId=" . $bankId . " AND isDelete=0 AND istatus=1");
    if (!validDetailDate($date, $advanced) || $amount <= 0 || !$validCompany || mysqli_num_rows($validCompany) === 0 || !$employee || !$validBank || mysqli_num_rows($validBank) === 0) {
        echo json_encode(array('success' => false, 'message' => 'Invalid details. Check the company, employee, bank, amount, and date.'));
        exit;
    }
    $data = array('iAdvancedMasterId' => $advancedId, 'iEmployeeId' => $employeeId, 'iCompanyId' => $companyId, 'iAmount' => number_format($amount, 2, '.', ''), 'strDate' => $date, 'strRemarks' => $remarks, 'iBankId' => $bankId, 'strEntryDate' => date('d-m-Y H:i:s'), 'iEntryBy' => $_SESSION['AdminId'], 'EntryDate' => date('Y-m-d'));
    $result = $connect->insertrecord($dbconn, 'advanced_details', $data);
    echo json_encode(array('success' => (bool) $result, 'message' => $result ? 'Advanced details added successfully.' : 'Unable to add advanced details.'));
    exit;
}
echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
?>