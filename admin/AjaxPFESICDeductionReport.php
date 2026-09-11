<?php
include '../common.php';
include 'IsLogin.php';
require_once 'PFESICDeductionReportData.php';

try {
    echo pfEsicReportHtml(pfEsicReportData(
        $dbconn,
        isset($_POST['month']) ? $_POST['month'] : '',
        isset($_POST['year']) ? $_POST['year'] : '',
        isset($_POST['employee']) ? $_POST['employee'] : ''
    ));
} catch (RuntimeException $exception) {
    http_response_code(500);
    echo '<div class="alert alert-danger">' . pfEsicReportEscape($exception->getMessage()) . '</div>';
}
