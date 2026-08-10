<?php

include('../config.php');
include('IsLogin.php');
require_once('FormCReport.php');

try {
    $html = getFormCRequestData($dbconn);
} catch (Exception $exception) {
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="Form-C.xls"');
header('Cache-Control: max-age=0');
echo $html;
