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

    // Word does not reliably import data-URI images from an HTML .doc file.
    // Package the HTML and signature as related MIME parts so it is embedded in
    // the downloaded document rather than fetched from a relative web address.
    $signaturePath = __DIR__ . '/SGECO-forms/image1.png';
    $signatureData = is_readable($signaturePath) ? file_get_contents($signaturePath) : '';
    if ($signatureData !== '') {
        $html = preg_replace(
            '/src="data:image\/png;base64,[^"]+"/',
            'src="cid:full-final-witness-signature"',
            $html
        );
    }
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

header('Content-Disposition: attachment; filename="Full-Final-Settlement' . $fileSuffix . '.doc"');
header('Cache-Control: max-age=0');
$boundary = '----=_FullFinalSettlement_' . md5(uniqid('', true));
header('MIME-Version: 1.0');
header('Content-Type: multipart/related; boundary="' . $boundary . '"');
echo 'MIME-Version: 1.0' . "\r\n";
echo 'Content-Type: multipart/related; boundary="' . $boundary . '"' . "\r\n\r\n";
echo '--' . $boundary . "\r\n";
echo 'Content-Type: text/html; charset=UTF-8' . "\r\n";
echo 'Content-Transfer-Encoding: quoted-printable' . "\r\n";
echo 'Content-Location: full-final-settlement.html' . "\r\n\r\n";
echo quoted_printable_encode("\xEF\xBB\xBF" . $html) . "\r\n";
if ($signatureData !== '') {
    echo '--' . $boundary . "\r\n";
    echo 'Content-Type: image/png' . "\r\n";
    echo 'Content-Transfer-Encoding: base64' . "\r\n";
    echo 'Content-Location: image1.png' . "\r\n";
    echo 'Content-ID: <full-final-witness-signature>' . "\r\n\r\n";
    echo chunk_split(base64_encode($signatureData), 76, "\r\n");
}
echo '--' . $boundary . '--' . "\r\n";
exit;
