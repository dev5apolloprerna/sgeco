<?php
ob_start();
include_once '../common.php';
include 'IsLogin.php';
if (!isset($_SESSION['AdminId'])) {
    exit;
}
$selectedYear = (int) date('Y') - ((int) date('n') < 4 ? 1 : 0);
$years = array($selectedYear => true);
$result = mysqli_query($dbconn, "SELECT DISTINCT month FROM salarymaster WHERE isDelete='0' AND istatus='1'");
if ($result) {
    while ($salary = mysqli_fetch_assoc($result)) {
        if (preg_match('/^(0[1-9]|1[0-2])\/(\d{4})$/D', $salary['month'], $parts)) {
            $years[(int) $parts[2] - ((int) $parts[1] < 4 ? 1 : 0)] = true;
        }
    }
}
krsort($years);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($ProjectName, ENT_QUOTES, 'UTF-8'); ?> | Anual Complaince Report</title>
    <?php include './include.php'; ?>
</head>

<body class="page-container-bg-solid page-boxed">
    <?php include './header.php'; ?>
    <div class="page-container">
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="container">
                    <ul class="page-breadcrumb breadcrumb">
                        <li><a href="index.php">Home</a><i class="fa fa-circle"></i></li>
                        <li>Anual Complaince Report</li>
                    </ul>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <div class="caption font-red-sunglo"><span class="caption-subject bold uppercase">Anual Complaince Report</span></div>
                        </div>
                        <div class="portlet-body">
                            <form action="exportAnnualComplianceReport.php" method="get" target="annualReportPreview" id="annualReportForm">
                                <div class="row">
                                    <div class="form-group col-md-3"><label for="annualReport">Report</label><select id="annualReport" class="form-control">
                                            <option>Form C — Register of Bonus</option>
                                        </select></div>
                                    <div class="form-group col-md-3"><label for="Company">Company</label><select name="Company" id="Company" class="form-control" required>
                                            <option value="">Select Company</option>
                                            <?php
                                            $companies = mysqli_query($dbconn, "SELECT companymasterId, companyname FROM companymaster WHERE isDelete='0' AND istatus='1' ORDER BY companyname");
                                            if ($companies) {
                                                while ($company = mysqli_fetch_assoc($companies)) {
                                                    echo '<option value="' . (int) $company['companymasterId'] . '">' . htmlspecialchars($company['companyname'], ENT_QUOTES, 'UTF-8') . '</option>';
                                                }
                                            }
                                            ?>
                                        </select></div>
                                    <div class="form-group col-md-3"><label for="Year">Financial Year (April–March)</label><select name="Year" id="Year" class="form-control" required>
                                            <?php foreach ($years as $year => $unused) {
                                                echo '<option value="' . $year . '"' . ($year === $selectedYear ? ' selected' : '') . '>April ' . $year . ' – March ' . ($year + 1) . '</option>';
                                            } ?>
                                        </select></div>
                                </div>
                                <button type="submit" name="format" value="preview" class="btn blue">Search</button>
                                <button type="submit" name="format" value="excel" formtarget="_blank" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                                <button type="submit" name="format" value="pdf" formtarget="_blank" class="btn red"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
                            </form>
                            <p style="margin-top:15px">Annual basic salary and working days are totalled from April to March. Payable bonus is 8.33% of annual basic salary; amount actually paid is the total bonus recorded in salary.</p>
                            <iframe name="annualReportPreview" title="Annual compliance report preview" style="width:100%;height:65vh;border:1px solid #ddd"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>

</html>