<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FormXIIReport.php');
require_once('OtherReportWordExport.php');

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;
    $employees = getFormXIIEmployees($dbconn, $companyId, $month, $employeeId);
    $pages = array_map('renderFormXIICard', $employees);
    $html = buildOtherReportWordDocument($pages, 'Form XII - Employment Card', '.form-page');
} catch (Throwable $exception) {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($exception->getMessage());
}

outputOtherReportWordDocument($html, 'Form-XII-Employment-Card.doc');
