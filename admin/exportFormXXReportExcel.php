<?php

ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXXReport.php');
require_once('../vendor/autoload.php');
require_once('OtherReportExcelExport.php');

try {
    $html = getFormXXRequestData($dbconn);
} catch (Throwable $exception) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($exception->getMessage());
}

exportOtherReportExcel($html, 'Form-XX-Register-of-Deduction-for-Damage-or-Loss.xlsx', 'Form XX');
