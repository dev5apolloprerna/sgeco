<?php
error_reporting(0);
include '../common.php';
include 'IsLogin.php';
require_once 'employeePaymentHistoryData.php';

if (isset($_POST['action']) && $_POST['action'] === 'suggestions') {
    header('Content-Type: application/json');
    $term = trim(isset($_POST['term']) ? $_POST['term'] : '');
    $items = array();
    if (strlen($term) >= 2) {
        $term = mysqli_real_escape_string($dbconn, $term);
        $result = mysqli_query($dbconn, "SELECT employeeId,emp_name,uan,strFatherName FROM employee WHERE isDelete=0 AND (emp_name LIKE '%$term%' OR uan LIKE '%$term%' OR strFatherName LIKE '%$term%') ORDER BY emp_name LIMIT 20");
        while ($result && $row = mysqli_fetch_assoc($result)) {
            $items[] = array('id' => (int) $row['employeeId'], 'value' => $row['emp_name'], 'label' => $row['emp_name'] . ' | UAN: ' . ($row['uan'] ?: '-') . ' | Father: ' . ($row['strFatherName'] ?: '-'));
        }
    }
    echo json_encode($items);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'report') {
    echo employeePaymentHistoryHtml(employeePaymentHistoryFind($dbconn, isset($_POST['employeeId']) ? $_POST['employeeId'] : 0));
}
