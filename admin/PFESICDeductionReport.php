<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
include 'IsLogin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $ProjectName; ?> | PF &amp; ESIC Deduction Report</title><?php include_once './include.php'; ?>
    <style>
        .pf-esic-report {
            font-family: serif;
            font-size: 15px;
            white-space: nowrap
        }

        .pf-esic-report th,
        .pf-esic-report td {
            text-align: center !important;
            vertical-align: middle !important;
            border-color: #333 !important
        }

        .pf-esic-report .employee-name {
            text-align: left !important
        }

        .pf-esic-report .report-title th {
            background: #c9ffff;
            font-size: 24px
        }

        .pf-esic-report .company-heading {
            background: #d9e2f3
        }

        .pf-esic-report .total-heading {
            background: #9fd8f6
        }

        .pf-esic-report .report-total {
            font-weight: bold
        }

        .pf-esic-section {
            margin-bottom: 26px
        }
    </style>
</head>

<body class="page-container-bg-solid page-boxed"><?php include_once './header.php'; ?>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li>PF &amp; ESIC Deduction Report</li>
                    </ul>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <div class="caption font-red-sunglo"><i class="icon-doc font-red-sunglo"></i><span class="caption-subject bold uppercase">PF &amp; ESIC Deduction Report</span></div>
                        </div>
                        <div class="portlet-body">
                            <form id="deductionSearch" class="row">
                                <div class="form-group col-md-3"><label>Month</label><select class="form-control" id="month" required>
                                        <option value="">Select Month</option><?php for ($m = 1; $m <= 12; $m++) echo '<option value="' . sprintf('%02d', $m) . '">' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>'; ?>
                                    </select></div>
                                <div class="form-group col-md-2"><label>Year</label><select class="form-control" id="year" required><?php for ($y = date('Y') - 5; $y <= date('Y') + 4; $y++) echo '<option value="' . $y . '"' . ($y == date('Y') ? ' selected' : '') . '>' . $y . '</option>'; ?></select></div>
                                <div class="form-group col-md-3"><label>Employee</label><input class="form-control" id="employee" type="search" placeholder="Name / PF / UAN / ESIC / Aadhar"></div>
                                <div class="form-group col-md-4" style="padding-top:25px"><button class="btn blue" type="submit"><i class="fa fa-search"></i> Search</button> <button class="btn green" id="excel" type="button" disabled><i class="fa fa-file-excel-o"></i> Export Excel</button> <button class="btn red" id="pdf" type="button" disabled><i class="fa fa-file-pdf-o"></i> Export PDF</button></div>
                            </form>
                            <div id="report"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><?php include_once './footer.php'; ?>
    <script>
        (function() {
            var form = $('#deductionSearch'),
                button = form.find('button[type=submit]');
            form.submit(function(event) {
                event.preventDefault();
                button.prop('disabled', true);
                $('#report').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
                $.post('AjaxPFESICDeductionReport.php', {
                    month: $('#month').val(),
                    year: $('#year').val(),
                    employee: $('#employee').val()
                }).done(function(html) {
                    $('#report').html(html);
                    $('#excel, #pdf').prop('disabled', !$('#report table').length)
                }).fail(function(xhr) {
                    $('#report').html(xhr.responseText || '<div class="alert alert-danger">Unable to load report.</div>')
                }).always(function() {
                    button.prop('disabled', false)
                })
            });
            $('#excel').click(function() {
                window.open(exportUrl('exportPFESICDeductionReport.php'), '_blank')
            });
            $('#pdf').click(function() {
                window.open(exportUrl('exportPFESICDeductionReportPDF.php'), '_blank')
            });
            function exportUrl(file) {
                return file + '?month=' + encodeURIComponent($('#month').val()) + '&year=' + encodeURIComponent($('#year').val()) + '&employee=' + encodeURIComponent($('#employee').val())
            }
        })();
    </script>
</body>

</html>