<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
$employees = mysqli_query($dbconn, "SELECT employeeId, employeecode, emp_name FROM employee WHERE isDelete='0' AND istatus='1' ORDER BY emp_name");
$companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete='0' AND istatus='1' ORDER BY companyname");
$defaultCompany = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT companymasterId FROM companymaster WHERE isDelete='0' AND istatus='1' AND LOWER(companyname) LIKE '%tata%chemical%' ORDER BY companymasterId LIMIT 1"));
$defaultCompanyId = $defaultCompany ? (int) $defaultCompany['companymasterId'] : 0;
$defaultSalaryMonth = '';
if ($defaultCompanyId > 0) {
    $lastSalary = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT sm.month FROM salarydetails sd INNER JOIN salarymaster sm ON sm.salarymasterId=sd.salaryId AND sm.isDelete='0' AND sm.istatus='1' WHERE sd.companyId=" . $defaultCompanyId . " AND sd.isDelete='0' AND sd.istatus='1' AND sm.month REGEXP '^(0[1-9]|1[0-2])/[0-9]{4}$' ORDER BY STR_TO_DATE(CONCAT('01/', sm.month), '%d/%m/%Y') DESC LIMIT 1"));
    $defaultSalaryMonth = $lastSalary ? $lastSalary['month'] : '';
}
$defaultMonth = $defaultSalaryMonth !== '' ? (int) substr($defaultSalaryMonth, 0, 2) : (int) date('m');
$defaultYear = $defaultSalaryMonth !== '' ? (int) substr($defaultSalaryMonth, 3, 4) : (int) date('Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $ProjectName; ?> | Full &amp; Final Settlement</title><?php include_once './include.php'; ?>
    <style>
        .settlement-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .preview-frame {
            width: 100%;
            height: 760px;
            border: 1px solid #ddd;
            background: #fff
        }

        .help-block.demo-note {
            margin-top: 8px;
            color: #777
        }

        .employee-search-wrap {
            position: relative
        }

        .employee-search-results {
            position: absolute;
            z-index: 1000;
            top: 100%;
            right: 0;
            left: 0;
            display: none;
            max-height: 260px;
            margin: 2px 0 0;
            padding: 5px 0;
            overflow-y: auto;
            list-style: none;
            border: 1px solid #c2cad8;
            border-radius: 3px;
            background: #fff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, .175)
        }

        .employee-search-results.open {
            display: block
        }

        .employee-search-results button {
            display: block;
            width: 100%;
            padding: 8px 12px;
            text-align: left;
            color: #333;
            border: 0;
            background: transparent
        }

        .employee-search-results button:hover,
        .employee-search-results button.active {
            color: #fff;
            background: #337ab7
        }

        .employee-search-results .employee-code {
            float: right;
            margin-left: 8px;
            color: #777
        }

        .employee-search-results button:hover .employee-code,
        .employee-search-results button.active .employee-code {
            color: #fff
        }

        .employee-search-message {
            padding: 8px 12px;
            color: #777
        }

        .selected-employee {
            min-height: 20px;
            margin: 5px 0 0;
            color: #337ab7
        }
    </style>
</head>

<body class="page-container-bg-solid page-boxed"><?php include_once './header.php'; ?>
    <div style="display:none;z-index:10060" id="loading"><img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif"></div>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="<?php echo $web_url; ?>admin/index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li><span>Full &amp; Final Settlement</span></li>
                    </ul>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <div class="caption font-red-sunglo"><i class="fa fa-file-text-o"></i> <span class="caption-subject bold uppercase">Full &amp; Final Settlement Report</span></div>
                        </div>
                        <div class="portlet-body form">
                            <form id="settlementSearch">
                                <div class="row">
                                    <div class="form-group col-md-4"><label for="employeeSearch">Search Employee <span class="text-muted">(Optional)</span></label>
                                        <div class="employee-search-wrap"><input type="text" id="employeeSearch" class="form-control" placeholder="Type a name or employee code" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="employeeResults" aria-expanded="false"><input type="hidden" id="employeeId" value="">
                                            <ul id="employeeResults" class="employee-search-results" role="listbox"></ul>
                                        </div>
                                        <p id="selectedEmployee" class="selected-employee" aria-live="polite">No employee selected — all matching employees will be included</p>
                                        <script type="application/json" id="employeeData">
                                            <?php
                                            $employeeData = array();
                                            while ($employee = mysqli_fetch_assoc($employees)) {
                                                $employeeData[] = array('id' => (int) $employee['employeeId'], 'name' => $employee['emp_name'], 'code' => $employee['employeecode']);
                                            }
                                            echo json_encode($employeeData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                                            ?>
                                        </script>
                                    </div>
                                    <div class="form-group col-md-3"><label>Company</label><select id="Company" class="form-control" required>
                                            <option value="">Select Company</option><?php while ($company = mysqli_fetch_assoc($companies)) { ?><option value="<?php echo (int) $company['companymasterId']; ?>" <?php echo (int) $company['companymasterId'] === $defaultCompanyId ? 'selected' : ''; ?>><?php echo htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?>
                                        </select></div>
                                    <div class="form-group col-md-2"><label>Month</label><select id="month" class="form-control" required>
                                            <option value="">Month</option><?php for ($month = 1; $month <= 12; $month++) { ?><option value="<?php echo sprintf('%02d', $month); ?>" <?php echo $month === $defaultMonth ? ' selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $month, 1)); ?></option><?php } ?>
                                        </select></div>
                                    <div class="form-group col-md-2"><label>Year</label><select id="Year" class="form-control" required><?php for ($year = min((int) date('Y') - 2, $defaultYear); $year <= max((int) date('Y') + 1, $defaultYear); $year++) { ?><option value="<?php echo $year; ?>" <?php echo $year === $defaultYear ? ' selected' : ''; ?>><?php echo $year; ?></option><?php } ?></select></div>
                                </div>
                                <div class="settlement-actions"><button class="btn blue" type="submit"><i class="fa fa-search"></i> Preview Report</button><button class="btn red" type="button" id="pdfButton"><i class="fa fa-file-pdf-o"></i> Download PDF</button><button class="btn green" type="button" id="excelButton"><i class="fa fa-file-excel-o"></i> Download Excel</button><button class="btn blue" type="button" id="wordButton"><i class="fa fa-file-word-o"></i> Download Word</button></div>
                                <p class="help-block demo-note">Demo: select an employee with payroll data for the chosen company and salary month.</p>
                            </form>
                            <hr>
                            <div id="preview">
                                <div class="alert alert-info">Choose an employee and period, then click Preview Report.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><?php include_once './footer.php'; ?>
    <script>
        (function() {
            var employees = JSON.parse($('#employeeData').text() || '[]');
            var $search = $('#employeeSearch');
            var $results = $('#employeeResults');
            var activeResult = -1;

            function normalise(value) {
                return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
            }

            function closeResults() {
                $results.removeClass('open').empty();
                $search.attr('aria-expanded', 'false');
                activeResult = -1;
            }

            function selectEmployee(employee) {
                $('#employeeId').val(employee.id);
                $search.val(employee.name + ' (' + employee.code + ')');
                $('#selectedEmployee').html('<i class="fa fa-check-circle"></i> Selected: ').append(document.createTextNode(employee.name + ' (' + employee.code + ')'));
                closeResults();
            }

            function renderResults() {
                var term = normalise($search.val());
                var terms = term.split(' ').filter(Boolean);
                var matches = employees.filter(function(employee) {
                    var searchable = normalise(employee.name + ' ' + employee.code);
                    return terms.every(function(part) {
                        return searchable.indexOf(part) !== -1;
                    });
                }).slice(0, 10);

                $results.empty();
                activeResult = -1;
                if (!term) {
                    $results.append($('<li class="employee-search-message">').text('Start typing an employee name or code.'));
                } else if (!matches.length) {
                    $results.append($('<li class="employee-search-message">').text('No matching employees found.'));
                } else {
                    $.each(matches, function(index, employee) {
                        var $button = $('<button type="button" role="option">')
                            .append($('<span>').text(employee.name))
                            .append($('<span class="employee-code">').text(employee.code))
                            .on('mousedown', function(event) {
                                event.preventDefault();
                                selectEmployee(employee);
                            });
                        $results.append($('<li>').append($button));
                    });
                }
                $results.addClass('open');
                $search.attr('aria-expanded', 'true');
            }

            $search.on('focus input', function(event) {
                if (event.type === 'input') {
                    $('#employeeId').val('');
                    $('#selectedEmployee').text('No employee selected — all matching employees will be included');
                }
                renderResults();
            }).on('keydown', function(event) {
                var $options = $results.find('button');
                if (event.key === 'Escape') return closeResults();
                if (!$options.length || ['ArrowDown', 'ArrowUp', 'Enter'].indexOf(event.key) === -1) return;
                event.preventDefault();
                if (event.key === 'Enter' && activeResult >= 0) return $options.eq(activeResult).trigger('mousedown');
                activeResult = event.key === 'ArrowDown' ? Math.min(activeResult + 1, $options.length - 1) : Math.max(activeResult - 1, 0);
                $options.removeClass('active').attr('aria-selected', 'false').eq(activeResult).addClass('active').attr('aria-selected', 'true')[0].scrollIntoView({
                    block: 'nearest'
                });
            }).on('blur', function() {
                window.setTimeout(closeResults, 150);
            });

            function params() {
                return {
                    employeeId: $('#employeeId').val(),
                    Company: $('#Company').val(),
                    salarymasterId: $('#month').val() + '/' + $('#Year').val()
                };
            }

            function valid() {
                var p = params();
                if (!p.Company) {
                    alert('Please select a company.');
                    return false;
                }
                return true;
            }
            $('#settlementSearch').on('submit', function(event) {
                event.preventDefault();
                if (!valid()) return;
                $('#loading').show();
                $.post('AjaxFullFinalSettlement.php', params()).done(function(html) {
                    var frame = $('<iframe class="preview-frame" title="Full and final settlement preview">');
                    $('#preview').empty().append(frame);
                    var doc = frame[0].contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                }).fail(function(xhr) {
                    $('#preview').html('<div class="alert alert-danger"></div>').find('.alert').text(xhr.responseText || 'Unable to load report.');
                }).always(function() {
                    $('#loading').hide();
                });
            });

            function download(url) {
                if (!valid()) return;
                var p = params();
                window.open(url + '?' + $.param(p), '_blank');
            }
            $('#pdfButton').click(function() {
                download('generateFullFinalSettlementPDF.php');
            });
            $('#excelButton').click(function() {
                download('exportFullFinalSettlementExcel.php');
            });
            $('#wordButton').click(function() {
                download('exportFullFinalSettlementWord.php');
            });
        })();
    </script>
</body>

</html>