<?php
ob_start();
include('../config.php');
include('IsLogin.php');
require_once('FullFinalSettlementReport.php');

try {
    $settlements = getFullFinalSettlements(
        $dbconn,
        isset($_GET['employeeId']) ? $_GET['employeeId'] : 0,
        isset($_GET['Company']) ? $_GET['Company'] : 0,
        isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : ''
    );
    $html = renderFullFinalSettlements($settlements);

    // Microsoft Word uses its installed Gujarati font and performs the complex
    // character shaping that older PDF renderers do not support reliably.
    $html = str_replace(
        'font-family:freesans',
        'font-family:"Nirmala UI",Shruti,"Noto Sans Gujarati",sans-serif',
        $html
    );
} catch (Throwable $exception) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($exception->getMessage());
}

while (ob_get_level() > 0) {
    ob_end_clean();
}
$fileSuffix = count($settlements) === 1
    ? '-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $settlements[0]['employee_name'])
    : '-All-Employees';
header('Content-Type: application/msword; charset=UTF-8');
header('Content-Disposition: attachment; filename="Full-Final-Settlement' . $fileSuffix . '.doc"');
header('Cache-Control: max-age=0');
echo "\xEF\xBB\xBF" . $html;
exit;
