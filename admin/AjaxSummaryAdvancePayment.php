<?php
include('../common.php');
include('IsLogin.php');
include_once 'summaryAdvancePaymentData.php';
requireAdvancedPaymentReportAccess($dbconn);
$report = summaryAdvancePaymentData($dbconn, $_POST);
if (!$report['columns']) {
    echo '<div class="alert alert-info"><h4 class="text-center">No Data Found!</h4></div>';
    exit;
}
function summaryHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<style>
    .summary-advance {
        white-space: nowrap
    }

    .summary-advance th {
        text-align: center;
        vertical-align: middle !important
    }

    .summary-advance .date-header {
        background: #70d5ed
    }

    .summary-advance .company-header {
        background: #a9df73
    }

    .summary-advance .total-header {
        background: #00ed18
    }

    .summary-advance .group-row {
        background: #d9e2f3;
        color: #9c2525;
        font-style: italic;
        font-weight: bold
    }

    .summary-report-heading {
        font-family: serif;
        font-size: 16px;
        font-weight: bold
    }

    .summary-scroll {
        overflow-x: auto
    }
</style>
<div class="summary-report-heading">SUB: Summary Advance Payment<br>SITE: <?php echo summaryHtml(implode(', ', $report['sites'])); ?><br>MONTH: <?php echo summaryHtml(summaryAdvancePaymentMonth($report['filters'])); ?></div><br>
<div class="summary-scroll">
    <table class="table table-bordered summary-advance">
        <thead>
            <tr>
                <th rowspan="2">Sr.<br>No.</th>
                <th rowspan="2">Name</th><?php foreach ($report['dates'] as $date => $keys) { ?><th class="date-header" colspan="<?php echo count($keys); ?>"><?php echo date('d-m-Y', strtotime($date)); ?></th><?php } ?><th rowspan="2" class="total-header">Total<br>Advance</th>
            </tr>
            <tr><?php foreach ($report['columns'] as $column) { ?><th class="company-header"><?php echo summaryHtml($column['company']); ?></th><?php } ?></tr>
        </thead>
        <tbody>
            <?php $serial = 1;
            foreach ($report['groups'] as $group => $employees) { ?><tr class="group-row">
                    <td></td>
                    <td><?php echo summaryHtml($group); ?></td>
                    <td colspan="<?php echo count($report['columns']) + 1; ?>"></td>
                </tr><?php foreach ($employees as $employee) { ?><tr>
                        <td class="text-center"><?php echo $serial++; ?></td>
                        <td><?php echo summaryHtml($employee['name']); ?></td><?php foreach ($report['columns'] as $key => $column) { ?><td class="text-right"><?php echo isset($employee['amounts'][$key]) ? summaryAdvancePaymentNumber($employee['amounts'][$key]) : ''; ?></td><?php } ?><td class="text-right"><strong><?php echo summaryAdvancePaymentNumber($employee['total']); ?></strong></td>
                    </tr><?php }
                    } ?>
        </tbody>
        <tfoot>
            <tr class="total-header">
                <th colspan="2">Total</th><?php foreach ($report['columns'] as $key => $column) { ?><th class="text-right"><?php echo summaryAdvancePaymentNumber($report['columnTotals'][$key]); ?></th><?php } ?><th class="text-right"><?php echo summaryAdvancePaymentNumber($report['grandTotal']); ?></th>
            </tr>
        </tfoot>
    </table>
</div>