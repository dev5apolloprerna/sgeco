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
    <title><?php echo $ProjectName; ?> | Employee Payment History</title>
    <?php include_once './include.php'; ?>
    <style>
        .employee-suggestions {
            position: absolute;
            z-index: 10050;
            background: #fff;
            border: 1px solid #ccc;
            left: 15px;
            right: 15px;
            max-height: 260px;
            overflow-y: auto;
            display: none
        }

        .employee-suggestions a {
            display: block;
            padding: 9px 12px;
            color: #333;
            border-bottom: 1px solid #eee
        }

        .employee-suggestions a:hover {
            background: #eef3f8;
            text-decoration: none
        }

        .employee-payment-history th,
        .employee-payment-history td {
            vertical-align: middle !important
        }
    </style>
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include_once './header.php'; ?>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li>Employee Payment History</li>
                    </ul>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <div class="caption font-red-sunglo"><i class="icon-doc font-red-sunglo"></i><span class="caption-subject bold uppercase">Employee Payment History</span></div>
                        </div>
                        <div class="portlet-body">
                            <form id="historySearch" class="row">
                                <div class="form-group col-md-6" style="position:relative"><label>Employee Name</label><input class="form-control" id="employeeSearch" autocomplete="off" placeholder="Search employee name, UAN number or father name"><input type="hidden" id="employeeId">
                                    <div id="employeeSuggestions" class="employee-suggestions"></div>
                                </div>
                                <div class="form-group col-md-6" style="padding-top:25px"><button class="btn blue" type="submit"><i class="fa fa-search"></i> View Report</button> <button class="btn green" type="button" id="excel" disabled><i class="fa fa-file-excel-o"></i> Export Excel</button> <button class="btn red" type="button" id="pdf" disabled><i class="fa fa-file-pdf-o"></i> Export PDF</button></div>
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
            var input = $('#employeeSearch'),
                id = $('#employeeId'),
                suggestions = $('#employeeSuggestions'),
                timer;
            input.on('input', function() {
                var term = input.val();
                id.val('');
                $('#excel,#pdf').prop('disabled', true);
                clearTimeout(timer);
                if (term.length < 2) {
                    suggestions.hide().empty();
                    return;
                }
                timer = setTimeout(function() {
                    $.post('AjaxEmployeePaymentHistory.php', {
                        action: 'suggestions',
                        term: term
                    }, function(items) {
                        suggestions.empty();
                        $.each(items, function(_, item) {
                            $('<a href="#"></a>').text(item.label).data('item', item).appendTo(suggestions);
                        });
                        suggestions.toggle(items.length > 0);
                    }, 'json');
                }, 250);
            });
            suggestions.on('click', 'a', function(event) {
                event.preventDefault();
                var item = $(this).data('item');
                id.val(item.id);
                input.val(item.value);
                suggestions.hide();
            });
            $('#historySearch').on('submit', function(event) {
                event.preventDefault();
                if (!id.val()) {
                    alert('Please select an employee from the search list.');
                    return;
                }
                $.post('AjaxEmployeePaymentHistory.php', {
                    action: 'report',
                    employeeId: id.val()
                }, function(html) {
                    $('#report').html(html);
                    $('#excel,#pdf').prop('disabled', false);
                });
            });
            $('#excel').click(function() {
                window.open('exportEmployeePaymentHistoryExcel.php?employeeId=' + encodeURIComponent(id.val()), '_blank');
            });
            $('#pdf').click(function() {
                window.open('generateEmployeePaymentHistoryPDF.php?employeeId=' + encodeURIComponent(id.val()), '_blank');
            });
        })();
    </script>
</body>

</html>