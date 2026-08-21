<?php

error_reporting(0);
include('../config.php');
include('IsLogin.php');
require_once('FormCReport.php');
require_once('FormXXIReport.php');
require_once('FormXXReport.php');
require_once('FormXIIIReport.php');
require_once('FormXXIIReport.php');
require_once('FormXXIIIReport.php');
require_once('FormXIXReport.php');
require_once('BonusFormCReport.php');
require_once('FormVIIIReport.php');
require_once('FormXIIReport.php');

header('Content-Type: text/html; charset=UTF-8');

$report = isset($_POST['Report']) ? trim($_POST['Report']) : '';
$companyId = isset($_POST['Company']) ? trim($_POST['Company']) : '';
$salaryMonth = isset($_POST['salarymasterId']) ? trim($_POST['salarymasterId']) : '';

if (
    !in_array($report, array('form-c', 'form-xx', 'form-xxi', 'form-xxii', 'form-xxiii', 'form-xiii', 'form-xix', 'form-bonus-c', 'form-viii', 'form-xii'), true) ||    $companyId === '' ||
    !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $salaryMonth)
) {
    http_response_code(400);
    exit('A valid report, company, month, and year are required.');
}

try {
    if ($report === 'form-xii') {
        echo renderFormXIIList(getFormXIIEmployees($dbconn, $companyId, $salaryMonth), $companyId, $salaryMonth);
    } elseif ($report === 'form-viii') {
        echo renderFormVIIIList(getFormVIIIEmployees($dbconn, $companyId, $salaryMonth), $companyId, $salaryMonth);
    } elseif ($report === 'form-bonus-c') {
        echo renderBonusFormCHtml(
            getBonusFormCListEmployees($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getBonusFormCCompanyName($dbconn, $companyId)
        );
    } elseif ($report === 'form-xix') {
        echo renderFormXIXList(
            getFormXIXEmployees($dbconn, $companyId, $salaryMonth),
            $companyId,
            $salaryMonth
        );
    } elseif ($report === 'form-xiii') {
        echo renderFormXIIIHtml(
            getFormXIIIEmployees($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getFormXIIICompanyName($dbconn, $companyId)
        );
    } elseif ($report === 'form-xx') {
        echo renderFormXXHtml(
            getFormXXEmployees($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getFormXXCompanyName($dbconn, $companyId)
        );
    } elseif ($report === 'form-xxii') {
        echo renderFormXXIIHtml(
            getFormXXIIEmployees($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getFormXXIICompanyName($dbconn, $companyId)
        );
    } elseif ($report === 'form-xxiii') {
        echo renderFormXXIIIHtml(
            getFormXXIIIEntries($dbconn, $companyId, $salaryMonth),
            $salaryMonth,
            getFormXXIIICompanyName($dbconn, $companyId)
        );
    } elseif ($report === 'form-xxi') {
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
