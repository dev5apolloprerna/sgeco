<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
include('User_Paging.php');
include_once 'advancedPaymentReportData.php';
requireAdvancedPaymentReportAccess($dbconn);

if (!isset($_POST['action']) || $_POST['action'] !== 'ListUser') {
    exit;
}
$filters = advancedPaymentReportFilters($dbconn, $_POST);
$bankFormat = advancedPaymentReportUsesBankFormat($filters);
$where = advancedPaymentReportWhere($dbconn, $filters);
$countQuery = "SELECT COUNT(*) AS TotalRow FROM advanced_details ad " .
    "INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId " .
    "INNER JOIN employee e ON e.employeeId=ad.iEmployeeId " .
    "INNER JOIN companymaster c ON c.companymasterId=ad.iCompanyId " .
    "INNER JOIN bankmaster b ON b.bankmasterId=ad.iBankId " .
    "WHERE " . $where;
$countResult = mysqli_query($dbconn, $countQuery);
$totalrecord = $countResult ? (int) mysqli_fetch_assoc($countResult)['TotalRow'] : 0;
$per_page = $cateperpaging;
$total_pages = max(1, (int) ceil($totalrecord / $per_page));
$show_page = max(1, min($total_pages, isset($_POST['Page']) ? (int) $_POST['Page'] : 1));
$result = mysqli_query($dbconn, advancedPaymentReportQuery($where) . ' LIMIT ' . (($show_page - 1) * $per_page) . ', ' . $per_page);
if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div class="alert alert-info"><h1 class="font-white text-center">No Data Found!</h1></div>';
    exit;
}
?>
<table class="table table-striped table-bordered table-hover dt-responsive" width="100%">
    <thead class="tbg">
        <tr>
            <th>Sr. No.</th>
            <th>Date</th>
            <th>Employee Code</th>
            <th>Beneficiary Name</th>
            <th>Beneficiary Account Number</th>
            <th>Company</th>
            <th>Bank</th>
            <?php if (!$bankFormat) { ?><th>Beneficiary Address</th><?php } ?>
            <th>IFSC Code</th>
            <th>Amount</th>
            <th>Remarks</th>
            <?php if (!$bankFormat) { ?><th>Comm.</th><?php } ?>
        </tr>
    </thead>
    <tbody><?php $serial = (($show_page - 1) * $per_page) + 1;
            $total = 0;
            $totalCommission = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $amount = (float) $row['iAmount'];
                $commission = $amount <= 10000 ? 2.36 : 4.72;
                $total += $amount;
                $totalCommission += $commission; ?>
            <tr>
                <td><?php echo $serial++; ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['strDate'])); ?></td>
                <td><?php echo htmlspecialchars($row['employeecode'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(ucwords(strtolower($row['emp_name'])), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(str_replace('A/C. ', '', $row['accountno']), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['companyname'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['bankname'], ENT_QUOTES, 'UTF-8'); ?></td>
                <?php if (!$bankFormat) { ?><td></td><?php } ?>
                <td><?php echo htmlspecialchars($row['ifsccode'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-right"><?php echo number_format($row['iAmount'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['strRemarks'], ENT_QUOTES, 'UTF-8'); ?></td>
                <?php if (!$bankFormat) { ?><td class="text-right"><?php echo number_format($commission, 2); ?></td><?php } ?>
            </tr><?php } ?>
    </tbody>
    <tfoot class="tbg">
        <tr>
            <th colspan="<?php echo $bankFormat ? 8 : 9; ?>" class="text-right">Page Total</th>
            <th class="text-right"><?php echo number_format($total, 2); ?></th>
            <th></th>
            <?php if (!$bankFormat) { ?><th class="text-right"><?php echo number_format($totalCommission, 2); ?></th><?php } ?>
        </tr>
    </tfoot>
</table>
<?php if ($totalrecord > $per_page) {
    echo '<div class="pagination">' . paginate('', $show_page, $total_pages) . '</div>';
} ?>