<?php
ob_start();

include('../config.php');
include('IsLogin.php');
require_once('FormVIIIReport.php');

try {
    $companyId = isset($_GET['Company']) ? (int) $_GET['Company'] : 0;
    $month = isset($_GET['salarymasterId']) ? trim($_GET['salarymasterId']) : '';
    $employeeId = isset($_GET['employeeId']) ? (int) $_GET['employeeId'] : 0;

    $employees = getFormVIIIEmployees(
        $dbconn,
        $companyId,
        $month,
        $employeeId
    );
} catch (Throwable $exception) {
    ob_clean();
    http_response_code(400);
    exit(htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/*
 * IMPORTANT:
 * Form VIII already has an approved HTML template:
 *
 *     SGECO-forms/Form-VIII-complete.html
 *
 * FormVIIIReport.php uses that template in
 * renderFormVIIICertificate().
 *
 * Therefore, Word export also uses the SAME template instead of
 * rebuilding the form with tables. This keeps the Word output
 * visually consistent with the original printed Form VIII.
 */

function formVIIIWordPage(array $employee, $isLast = false)
{
    /*
     * Word export is intentionally built WITHOUT HTML tables.
     * Tables cause Word to show table borders/grid lines.
     * The form below uses normal paragraphs/spans so the document
     * remains clean and borderless like the original printed Form VIII.
     */
    $d = formVIIIEmployeeData($employee);

    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $pageBreak = $isLast ? 'auto' : 'always';

    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>
@page {
    size: A4 portrait;
    margin: 0;
}

html, body {
    margin: 0;
    padding: 0;
    background: #ffffff;
}

body {
    font-family: "Times New Roman", Times, serif;
    color: #000000;
    font-size: 14pt;
    font-weight: normal;
    font-style: normal;
}

.form-page {
    width: 17.2cm;
    height: 28.8cm;
    margin: 0 auto;
    padding-top: 2.55cm;
    box-sizing: border-box;
    page-break-after: ' . $pageBreak . ';
    break-after: page;
    position: relative;
}

/* Header */
.form-title {
    text-align: center;
    font-size: 21pt;
    font-weight: bold;
    font-style: normal;
    line-height: 1;
    margin: 0 0 0.35cm 0;
}

.rule-text {
    text-align: center;
    font-size: 11.5pt;
    font-weight: normal;
    font-style: normal;
    line-height: 1.05;
    margin: 0;
    white-space: nowrap;
}

.service-title {
    text-align: center;
    font-size: 17pt;
    font-weight: bold;
    font-style: normal;
    line-height: 1;
    margin: 0.28cm 0 1.55cm 0;
}

/* Main form lines */
.row {
    width: 100%;
    margin: 0 0 0.34cm 0;
    padding: 0;
    font-size: 14pt;
    line-height: 1.08;
    font-weight: normal;
    font-style: normal;
    white-space: nowrap;
}

.row .no {
    display: inline-block;
    width: 0.72cm;
    vertical-align: top;
}

.row .label {
    font-weight: normal;
    font-style: normal;
}

.value {
    font-weight: bold;
    font-style: normal;
    text-decoration: underline;
}

/* Nature/location: only the two real blank lines are underlined */
.nature-row {
    margin-bottom: 0;
}

.nature-lines {
    margin-left: 0.72cm;
    margin-top: 0.12cm;
}

.nature-line {
    display: block;
    width: 13.55cm;
    height: 0.72cm;
    border-bottom: 1px solid #000000;
    box-sizing: border-box;
    margin-bottom: 0.04cm;
}

.after-nature {
    margin-top: 0.72cm;
}

/* Period line in the original form */
.period-value {
    display: inline-block;
    min-width: 6.9cm;
}

/* Signature position
 * Keep it fixed near the lower-right of the A4 form,
 * matching the original printed Form VIII.
 */
.signature-area {
    position: static;
    height: 0;
    width: 100%;
}

.signature {
    position: absolute;
    right: 0.10cm;
    bottom: 3.35cm;
    font-size: 13pt;
    line-height: 1;
    font-weight: normal;
    font-style: normal;
    white-space: nowrap;
}
</style>
</head>

<body>
<div class="form-page">

    <div class="form-title">FORM VIII</div>

    <div class="rule-text">
        [Under rule 77 of the Contract Labour (Regulation and Abolition) Central Rules, 1971]
    </div>

    <div class="service-title">Service Certificate</div>

    <div class="row">
        <span class="no">1.</span>
        <span class="label">Name of employer:
            <span class="value">Shree Ganesh Engineering Co.</span>
        </span>
    </div>

    <div class="row">
        <span class="no">2.</span>
        <span class="label">LIN/PAN No. of the employer:
            <span class="value">AAMFS3884N</span>
        </span>
    </div>

    <div class="row">
        <span class="no">3.</span>
        <span class="label">Email Id of the employer:
            <span class="value">hkshah@sgeco.in</span>
        </span>
    </div>

    <div class="row">
        <span class="no">4.</span>
        <span class="label">Mobile No. of the employer:
            <span class="value">7984454082</span>
        </span>
    </div>

    <div class="row nature-row">
        <span class="no">5.</span>
        <span class="label">Nature and location of work:</span>

        <div class="nature-lines">
            <span class="nature-line"></span>
            <span class="nature-line"></span>
        </div>
    </div>

    <div class="row after-nature">
        <span class="no">6.</span>
        <span class="label">Name of the workman:
            <span class="value">' . $e($d['name']) . '</span>
        </span>
    </div>

    <div class="row">
        <span class="no">7.</span>
        <span class="label">UAN / Aadhaar No.: UAN:
            <span class="value">' . $e($d['uan']) . '</span>
            / Aadhaar No.:
            <span class="value">' . $e($d['aadhaar']) . '</span>
        </span>
    </div>

    <div class="row">
        <span class="no">8.</span>
        <span class="label">Mobile No.:
            <span class="value">' . $e($d['mobile']) . '</span>
        </span>
    </div>

    <div class="row">
        <span class="no">9.</span>
        <span class="label">Serial Number in the Register of Workmen:
            <span class="value">' . $e($d['serial']) . '</span>
        </span>
    </div>

    <div class="row">
        <span class="no">10.</span>
        <span class="label">Period of Employment:
            <span class="value period-value">' . $e($d['period']) . '</span>
        </span>
    </div>

    <div class="row">
        <span class="no">11.</span>
        <span class="label">Designation:
            <span class="value">' . $e($d['designation']) . '</span>
        </span>
    </div>

    <div class="signature-area">
        <div class="signature">Seal and Signature of Employer</div>
    </div>

</div>
</body>
</html>';
}

ob_clean();

if (!$employees) {
    $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { size:A4 portrait; margin:0.7in; }
body { font-family:"Times New Roman",serif; text-align:center; }
</style>
</head>
<body>
<h2>No Data Found !</h2>
</body>
</html>';
} else {
    $pages = array();

    foreach ($employees as $index => $employee) {
        $isLast = ($index === count($employees) - 1);
        $pages[] = formVIIIWordPage($employee, $isLast);
    }

    /*
     * Every employee is one complete Form VIII page.
     */
    $html = implode('', $pages);
}

header('Content-Type: application/msword; charset=UTF-8');
header(
    'Content-Disposition: attachment; filename="Form-VIII-Service-Certificate.doc"'
);
header('Cache-Control: max-age=0');
header('Pragma: public');

echo $html;
exit;
?>
