<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

$rightsResult = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
$rights = mysqli_fetch_assoc($rightsResult);
if ($_SESSION['AdminType'] != 1 && (!isset($rights['isAdvancedEntry']) || $rights['isAdvancedEntry'] != 1)) {
    http_response_code(403);
    exit('Access denied.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $ProjectName; ?> | Advanced</title>
    <?php include_once './include.php'; ?>
    <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include_once './header.php'; ?>
    <div style="display:none; z-index:10060" id="loading"><img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif" alt="Loading"></div>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="<?php echo $web_url; ?>admin/index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li><span>Advanced</span></li>
                    </ul>
                    <div class="page-content-inner">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase" id="formTitle">Add Advanced</span></div>
                                    </div>
                                    <div class="portlet-body form">
                                        <form method="post" id="advancedForm"><input type="hidden" name="action" id="action" value="AddAdvanced">
                                            <div class="form-group"><label for="strMonthYear">Month / Year</label><input type="text" name="strMonthYear" id="strMonthYear" class="form-control" placeholder="Select month and year" autocomplete="off" required></div>
                                            <div class="form-group"><label for="fromdate">From Date</label><input type="date" name="fromdate" id="fromdate" class="form-control" required></div>
                                            <div class="form-group"><label for="todate">To Date</label><input type="date" name="todate" id="todate" class="form-control" required></div>
                                            <div class="form-actions noborder">
                                                <!-- <button class="btn blue margin-top-20" type="submit" id="submitButton">Submit</button> -->
                                                <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">      
                                                <button type="button" class="btn blue margin-top-20" onclick="resetForm()">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption font-red-sunglo"><i class="icon-settings font-red-sunglo"></i><span class="caption-subject bold uppercase">List of Advanced</span></div>
                                    </div>
                                    <div class="portlet-body form">
                                        <div class="col-md-6 pull-right">
                                            <div class="form-group col-md-9"><input type="text" id="Search_Txt" class="form-control" placeholder="Search month / year"></div>
                                            <div class="form-actions col-md-3"><button type="button" class="btn blue pull-right" onclick="loadAdvanced(1)">Search</button></div>
                                        </div>
                                        <div id="advancedList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once './footer.php'; ?>
    <script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
    <script>
        $('#strMonthYear').datepicker({
            autoclose: true,
            minViewMode: 1,
            format: 'mm/yyyy'
        }).on('changeDate', function() {
            setDateRange(true);
        });

        function setDateRange(resetDates) {
            var parts = $('#strMonthYear').val().split('/');
            if (parts.length !== 2) return;
            var month = parseInt(parts[0], 10);
            var year = parseInt(parts[1], 10);
            if (!month || !year) return;
            var prefix = year + '-' + String(month).padStart(2, '0') + '-';
            var firstDate = prefix + '01';
            var lastDay = new Date(year, month, 0).getDate();
            var lastDate = prefix + String(lastDay).padStart(2, '0');
            $('#fromdate, #todate').attr({min: firstDate, max: lastDate});
            if (resetDates) {
                $('#fromdate').val(firstDate);
                $('#todate').val(lastDate);
            }
        }

        function resetForm() {
            window.location.href = 'advancedmaster.php';
        }
        $('#advancedForm').submit(function(event) {
            event.preventDefault();
            $('#loading').show();
            $.post('<?php echo $web_url; ?>admin/querydata.php', $(this).serialize(), function(response) {
                $('#loading').hide();
                if (response == 1) alert('Advanced added successfully.');
                else if (response == 2) alert('Advanced edited successfully.');
                else if (response == 3) alert('This month / year already exists.');
                else if (response == 4) alert('From Date and To Date must be valid dates in the selected month / year.');
                else alert('Invalid request.');
                if (response == 1 || response == 2) resetForm();
            });
        });

        function editAdvanced(id) {
            $('#loading').show();
            $.post('<?php echo $web_url; ?>admin/querydata.php', {
                action: 'GetAdvanced',
                ID: id
            }, function(response) {
                $('#loading').hide();
                var data = JSON.parse(response);
                $('#formTitle').text('Edit Advanced');
                $('#strMonthYear').val(data.strMonthYear);
                $('#fromdate').val(data.fromdate);
                $('#todate').val(data.todate);
                setDateRange(false);
                $('#action').val('EditAdvanced');
                $('#advancedForm input[name=iAdvancedMasterId]').remove();
                $('<input>', {
                    type: 'hidden',
                    name: 'iAdvancedMasterId',
                    value: data.iAdvancedMasterId
                }).appendTo('#advancedForm');
            });
        }

        function deleteAdvanced(id) {
            if (!confirm('Are you sure you want to delete this entry?')) return;
            $('#loading').show();
            $.post('<?php echo $web_url; ?>admin/Ajaxadvancedmaster.php', {
                action: 'Delete',
                ID: id
            }, function() {
                $('#loading').hide();
                loadAdvanced(1);
            });
        }

        function loadAdvanced(page) {
            $('#loading').show();
            $.post('<?php echo $web_url; ?>admin/Ajaxadvancedmaster.php', {
                action: 'ListUser',
                Page: page,
                Search_Txt: $('#Search_Txt').val()
            }, function(html) {
                $('#advancedList').html(html);
                $('#loading').hide();
            });
        }
        $('#Search_Txt').keypress(function(event) {
            if (event.which === 13) {
                event.preventDefault();
                loadAdvanced(1);
            }
        });
        loadAdvanced(1);
    </script>
</body>

</html>