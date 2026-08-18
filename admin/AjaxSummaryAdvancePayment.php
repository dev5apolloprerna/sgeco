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
        margin-bottom: 0;
        white-space: nowrap;
        background: #fff;
        color: #34495e
    }

    .summary-advance th {
        padding: 10px 8px !important;
        text-align: center;
        vertical-align: middle !important;
        border-color: #b8c4ce !important
    }

    .summary-advance td {
        padding: 8px !important;
        vertical-align: middle !important;
        border-color: #d7dfe6 !important
    }

    .summary-advance thead tr:first-child th:first-child,
    .summary-advance thead tr:first-child th:nth-child(2) {
        background: #658397;
        color: #ffffff
    }

    .summary-advance .date-header {
        background: #658397;
        color: #ffffff
    }

    .summary-advance .company-header {
        /* background: #a8dc70;
        color: #294416 */
        background: #658397;
        color: #ffffff;
    }

    .summary-advance .total-header {
        /* background: #19c63b;
        color: #fff */
        background: #658397;
        color: #ffffff;
    }

    .summary-advance .group-row {
        background: #658397 !important;
        color: #ffffff !important;
        font-style: italic;
        font-weight: bold
    }

    .summary-advance tbody tr:not(.group-row):nth-child(odd) {
        background: #f8fafc
    }

    .summary-advance tbody tr:not(.group-row):hover {
        background: #eaf6ff
    }

    .summary-advance tbody td:last-child {
        background: #658397;
        color: #ffffff
    }

    .summary-advance tfoot th {
        font-size: 12px
    }

    .summary-report-heading {
        padding: 14px 18px;
        border-left: 4px solid #3598dc;
        border-radius: 3px;
        background: #f4f8fb;
        color: #2c3e50;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.7
    }

    .summary-scroll {
        margin-top: 15px;
        overflow-x: auto;
        border: 1px solid #d7dfe6;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(44, 62, 80, .08)
    }
</style>
<div class="summary-report-heading">SUB: Summary Advance Payment<br>SITE: <?php echo summaryHtml(implode(', ', $report['sites'])); ?><br>MONTH: <?php echo summaryHtml(summaryAdvancePaymentMonth($report['filters'])); ?></div>
<div class="summary-scroll">
    <table class="table table-striped table-bordered table-hover summary-advance">
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