<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
$employees = mysqli_query($dbconn, "SELECT employeeId, employeecode, emp_name FROM employee WHERE isDelete='0' AND istatus='1' ORDER BY emp_name");
$companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete='0' AND istatus='1' ORDER BY companyname");
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
                                    <div class="form-group col-md-4"><label>Search Employee</label><input type="text" id="employeeSearch" class="form-control" placeholder="Type employee name or code"><select id="employeeId" class="form-control" size="6" required style="margin-top:5px">
                                            <option value="">Select Employee</option><?php while ($employee = mysqli_fetch_assoc($employees)) { ?><option value="<?php echo (int) $employee['employeeId']; ?>"><?php echo htmlspecialchars($employee['emp_name'] . ' (' . $employee['employeecode'] . ')', ENT_QUOTES, 'UTF-8'); ?></option><?php } ?>
                                        </select></div>
                                    <div class="form-group col-md-3"><label>Company</label><select id="Company" class="form-control" required>
                                            <option value="">Select Company</option><?php while ($company = mysqli_fetch_assoc($companies)) { ?><option value="<?php echo (int) $company['companymasterId']; ?>"><?php echo htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?>
                                        </select></div>
                                    <div class="form-group col-md-2"><label>Month</label><select id="month" class="form-control" required>
                                            <option value="">Month</option><?php for ($month = 1; $month <= 12; $month++) { ?><option value="<?php echo sprintf('%02d', $month); ?>" <?php echo $month === (int) date('m') ? ' selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $month, 1)); ?></option><?php } ?>
                                        </select></div>
                                    <div class="form-group col-md-2"><label>Year</label><select id="Year" class="form-control" required><?php for ($year = (int) date('Y') - 2; $year <= (int) date('Y') + 1; $year++) { ?><option value="<?php echo $year; ?>" <?php echo $year === (int) date('Y') ? ' selected' : ''; ?>><?php echo $year; ?></option><?php } ?></select></div>
                                </div>
                                <div class="settlement-actions"><button class="btn blue" type="submit"><i class="fa fa-search"></i> Preview Report</button><button class="btn red" type="button" id="pdfButton"><i class="fa fa-file-pdf-o"></i> Download PDF</button><button class="btn green" type="button" id="excelButton"><i class="fa fa-file-excel-o"></i> Download Excel</button></div>
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
            var allOptions = $('#employeeId option').clone();
            $('#employeeSearch').on('input', function() {
                var term = this.value.toLowerCase();
                $('#employeeId').empty().append(allOptions.filter(function() {
                    return !this.value || $(this).text().toLowerCase().indexOf(term) !== -1;
                }).clone());
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
                if (!p.employeeId || !p.Company) {
                    alert('Please select an employee and company.');
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
        })();
    </script>
</body>

</html>