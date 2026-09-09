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
if (count($report['employees']) === 0) {
    echo '<div class="alert alert-info"><h4 class="text-center">No Data Found!</h4></div>';
    exit;
}
?>
<style>
    .new-pf-report th,
    .new-pf-report td {
        text-align: center;
        vertical-align: middle !important;
        white-space: nowrap
    }

    .new-pf-report .employee-name {
        text-align: left
    }

    .new-pf-report thead tr:first-child th {
        background: #ead0d0
    }

    .new-pf-report thead tr:first-child th.fixed,
    .new-pf-report thead tr:first-child th.trailing {
        background: #eeeade
    }

    .new-pf-report thead tr:first-child th.total {
        background: #00ed20
    }
</style>
<div class="table-responsive">
    <table class="table table-bordered table-hover new-pf-report">
        <caption style="color:#333"><strong>FOR EPF &amp; ESIC ONLY</strong><br><strong>SHREE GANESH ENGINEERING CO.</strong><br>P.F.CODE : GJ 16240 &nbsp;&nbsp; GROUP : IV<br>Employee List of PF and ESIC For the Month of: <strong><?php echo reportHtml(date('F-Y', strtotime('01-' . str_replace('/', '-', $report['salaryMonth'])))); ?></strong></caption>
        <thead>
            <tr>
                <th class="fixed" rowspan="2">Sr.<br>No.</th>
                <th class="fixed" rowspan="2">Name as per Aadhar</th>
                <th class="fixed" rowspan="2">PF. No.</th>
                <th class="fixed" rowspan="2">UAN No.</th>
                <th class="fixed" rowspan="2">ESIC No.</th>
                <th class="fixed" rowspan="2">D.O.B</th>
                <?php foreach ($report['companies'] as $companyName) { ?><th colspan="4"><?php echo reportHtml($companyName); ?></th><?php } ?>
                <th class="total" colspan="4">Total</th>
                <th class="trailing" rowspan="2">OT AMOUNT<br>FOR ESIC</th>
                <th class="trailing" rowspan="2">Joining<br>Date</th>
                <th class="trailing" rowspan="2">Profess.<br>Tax Amt.</th>
            </tr>
            <tr>
                <?php foreach ($report['companies'] as $companyName) { ?><th>Present<br>Days</th>
                    <th>National<br>Holiday</th>
                    <th>Wages<br>Rate</th>
                    <th>Difference in ESIC<br>(Bonus + Leave)</th><?php } ?>
                <th class="total">Present<br>Days</th>
                <th class="total">National<br>Holiday</th>
                <th class="total">Wages</th>
                <th class="total">Difference in ESIC<br>(Bonus + Leave)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['employees'] as $index => $employee) {
                $totals = array(0, 0, 0, 0); ?><tr>
                    <td><?php echo $index + 1; ?></td>
                    <td class="employee-name"><?php echo reportHtml($employee['name']); ?></td>
                    <td><?php echo reportHtml($employee['pfNo']); ?></td>
                    <td><?php echo reportHtml($employee['uan']); ?></td>
                    <td><?php echo reportHtml($employee['esicNo']); ?></td>
                    <td><?php echo reportHtml($employee['dob']); ?></td>
                    <?php foreach ($report['companies'] as $companyId => $companyName) {
                        $values = isset($employee['companies'][$companyId]) ? $employee['companies'][$companyId] : array('presentDays' => 0, 'nationalHoliday' => 0, 'wages' => 0, 'differenceInESIC' => 0);
                        $totals[0] += $values['presentDays'];
                        $totals[1] += $values['nationalHoliday'];
                        $totals[2] += $values['wages'];
                        $totals[3] += $values['differenceInESIC'];
                        ?><td><?php echo reportHtml(newPFChallanPresentDays($values['presentDays'])); ?></td>
                        <td><?php echo reportHtml(newPFChallanNationalHoliday($values['nationalHoliday'])); ?></td>
                        <td><?php echo reportHtml(newPFChallanWages($values['wages'])); ?></td>
                        <td><?php echo reportHtml(newPFChallanValue($values['differenceInESIC'])); ?></td><?php
                        } ?>
                    <td><?php echo reportHtml(newPFChallanPresentDays($totals[0])); ?></td>
                    <td><?php echo reportHtml(newPFChallanNationalHoliday($totals[1])); ?></td>
                    <td><?php echo reportHtml(newPFChallanWages($totals[2])); ?></td>
                    <td><?php echo reportHtml(newPFChallanValue($totals[3])); ?></td>
                    <td><?php echo reportHtml(newPFChallanValue($employee['overtime'])); ?></td>
                    
                    <td><?php echo reportHtml($employee['joiningDate']); ?></td>
                    <td><?php echo reportHtml(newPFChallanValue($employee['professionalTax'])); ?></td>
                </tr><?php } ?>
        </tbody>
    </table>
</div>