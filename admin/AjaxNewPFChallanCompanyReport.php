<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
require_once('NewPFChallanReportData.php');

function reportHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

try {
    $report = getNewPFChallanReportData($dbconn, isset($_POST['month']) ? $_POST['month'] : 0, isset($_POST['Year']) ? $_POST['Year'] : 0);
} catch (Exception $exception) {
    http_response_code(400);
    echo '<div class="alert alert-danger">' . reportHtml($exception->getMessage()) . '</div>';
    exit;
}
if (count($report['pfEmployees']) === 0 && count($report['aadharEmployees']) === 0) {
    echo '<div class="alert alert-info"><h4 class="text-center">No Data Found!</h4></div>';
    exit;
}

function renderNewPFSection($report, $employees, $type)
{
    if (count($employees) === 0) {
        return;
    }
    $isAadhar = $type === 'aadhar';
    ?>
    <div class="table-responsive new-pf-section">
        <table class="table table-bordered table-hover new-pf-report">
            <thead>
                <tr>
                    <th class="fixed" rowspan="2">SR. No.</th>
                    <th class="fixed" rowspan="2"><?php echo $isAadhar ? 'Name as per Aadhar' : 'NAME'; ?></th>
                    <?php if ($isAadhar) { ?>
                        <th class="fixed" rowspan="2">Father Name</th>
                        <th class="fixed" rowspan="2">Aadhar no.</th>
                    <?php } else { ?>
                        <th class="fixed" rowspan="2">P.F. A/c No.</th>
                        <th class="fixed" rowspan="2">UAN NO</th>
                    <?php } ?>
                    <th class="fixed" rowspan="2">ESIC NO</th>
                    <th class="fixed" rowspan="2">D.O.B</th>
                    <?php foreach ($report['companies'] as $companyName) { ?><th colspan="4"><?php echo reportHtml($companyName); ?></th><?php } ?>
                    <th class="total" colspan="4">Total</th>
                    <th class="trailing" rowspan="2"><?php echo $isAadhar ? 'Joining Date' : 'OT AMOUNT FOR ESIC'; ?></th>
                </tr>
                <tr>
                    <?php foreach ($report['companies'] as $companyName) { ?>
                        <th>Present Days</th><th>National Holiday</th><th>Wages</th><th>Difference in ESIC</th>
                    <?php } ?>
                    <th class="total">Present Days</th><th class="total">National Holiday</th><th class="total">Wages</th><th class="total">Difference in ESIC</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $index => $employee) {
                    $totals = array(0, 0, 0, 0); ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td class="employee-name"><?php echo reportHtml($employee['name']); ?></td>
                        <?php if ($isAadhar) { ?>
                            <td><?php echo reportHtml($employee['fatherName']); ?></td>
                            <td><?php echo reportHtml($employee['aadharNo']); ?></td>
                        <?php } else { ?>
                            <td><?php echo reportHtml($employee['pfNo']); ?></td>
                            <td><?php echo reportHtml($employee['uan']); ?></td>
                        <?php } ?>
                        <td><?php echo reportHtml($employee['esicNo']); ?></td>
                        <td><?php echo reportHtml($employee['dob']); ?></td>
                        <?php foreach ($report['companies'] as $companyId => $companyName) {
                            $values = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'nationalHoliday' => 0, 'wages' => 0, 'differenceInESIC' => 0);
                            $totals[0] += $values['presentDays'];
                            $totals[1] += $values['nationalHoliday'];
                            $totals[2] += $values['wages'];
                            $totals[3] += $values['differenceInESIC']; ?>
                            <td><?php echo reportHtml(newPFChallanPresentDays($values['presentDays'])); ?></td>
                            <td><?php echo reportHtml(newPFChallanNationalHoliday($values['nationalHoliday'])); ?></td>
                            <td><?php echo reportHtml(newPFChallanWages($values['wages'])); ?></td>
                            <td><?php echo reportHtml(newPFChallanValue($values['differenceInESIC'])); ?></td>
                        <?php } ?>
                        <td><?php echo reportHtml(newPFChallanPresentDays($totals[0])); ?></td>
                        <td><?php echo reportHtml(newPFChallanNationalHoliday($totals[1])); ?></td>
                        <td><?php echo reportHtml(newPFChallanWages($totals[2])); ?></td>
                        <td><?php echo reportHtml(newPFChallanValue($totals[3])); ?></td>
                        <td><?php echo reportHtml($isAadhar ? $employee['joiningDate'] : newPFChallanValue($employee['overtime'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
<style>
    .new-pf-section { margin-bottom: 26px; }
    .new-pf-report th, .new-pf-report td { text-align: center; vertical-align: middle !important; white-space: nowrap; }
    .new-pf-report .employee-name { text-align: left; }
    .new-pf-report thead tr:first-child th { background: #ead0d0; }
    .new-pf-report thead tr:first-child th.fixed, .new-pf-report thead tr:first-child th.trailing { background: #eeeade; }
    .new-pf-report thead th.total { background: #00ed20 !important; }
</style>
<div class="new-pf-report-heading" style="text-align:center;color:#333;margin-bottom:12px">
    <strong>FOR EPF &amp; ESIC ONLY</strong><br><strong>SHREE GANESH ENGINEERING CO.</strong><br>
    P.F.CODE : GJ 16240 &nbsp;&nbsp; GROUP : IV<br>
    Employee List of PF and ESIC For the Month of: <strong><?php echo reportHtml(date('F-Y', strtotime('01-' . str_replace('/', '-', $report['salaryMonth'])))); ?></strong>
</div>
<?php
renderNewPFSection($report, $report['pfEmployees'], 'pf');
renderNewPFSection($report, $report['aadharEmployees'], 'aadhar');