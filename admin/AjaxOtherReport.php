<?php

error_reporting(0);
include('../config.php');
include('IsLogin.php');
require_once('FormCReport.php');
require_once('FormXXIReport.php');

header('Content-Type: text/html; charset=UTF-8');

$report = isset($_POST['Report']) ? trim($_POST['Report']) : '';
$companyId = isset($_POST['Company']) ? trim($_POST['Company']) : '';
$salaryMonth = isset($_POST['salarymasterId']) ? trim($_POST['salarymasterId']) : '';

if (($report !== 'form-c' && $report !== 'form-xxi') ||
    $companyId === '' ||
    !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)
) {
    http_response_code(400);
    exit('A valid report, company, month, and year are required.');
}

try {
    if ($report === 'form-xxi') {
        echo renderFormXXIHtml(
            getFormXXIEmployees($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getFormXXICompanyName($dbconn, $companyId)
        );
    } else {
        echo renderFormCHtml(
            getFormCEmployees($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getFormCCompanyName($dbconn, $companyId)
        );
    }
} catch (Throwable $exception) {
    http_response_code(500);
    exit('Unable to load the selected report.');
}