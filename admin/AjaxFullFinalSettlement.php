<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FullFinalSettlementReport.php');

try {
    $settlements = getFullFinalSettlements(
        $dbconn,
        isset($_POST['employeeId']) ? $_POST['employeeId'] : 0,
        isset($_POST['Company']) ? $_POST['Company'] : 0,
        isset($_POST['salarymasterId']) ? trim($_POST['salarymasterId']) : ''
    );
    echo renderFullFinalSettlements($settlements);
} catch (Throwable $exception) {
    http_response_code(400);
    echo htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
}
