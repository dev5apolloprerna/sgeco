<?php

function requireAdvancedPaymentReportAccess($dbconn)
{
    if ($_SESSION['AdminType'] == 1) {
        return;
    }

    $result = mysqli_query($dbconn, "SELECT isAdvancedEntry FROM user_rights WHERE iUserId='" . (int) $_SESSION['AdminId'] . "'");
    $rights = $result ? mysqli_fetch_assoc($result) : array();
    if (!isset($rights['isAdvancedEntry']) || $rights['isAdvancedEntry'] != 1) {
        http_response_code(403);
        header('location:'.$web_url.'admin/login.php');	
        exit;
    }
}

function advancedPaymentReportFilters($dbconn, $source)
{
    $companyId = isset($source['Company']) ? (int) $source['Company'] : 0;
    $bankId = isset($source['bank']) ? (int) $source['bank'] : 0;
    $month = isset($source['month']) ? trim($source['month']) : '';
    $year = isset($source['Year']) ? trim($source['Year']) : '';
    $fromDate = isset($source['fromDate']) ? trim($source['fromDate']) : '';
    $toDate = isset($source['toDate']) ? trim($source['toDate']) : '';

    if ($month !== '' && !preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
        $month = '';
    }
    if ($year !== '' && !preg_match('/^[0-9]{4}$/', $year)) {
        $year = '';
    }

    $monthStart = ($month !== '' && $year !== '') ? $year . '-' . $month . '-01' : '';
    $monthEnd = $monthStart !== '' ? date('Y-m-t', strtotime($monthStart)) : '';
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $fromDate) || ($monthStart !== '' && ($fromDate < $monthStart || $fromDate > $monthEnd))) {
        $fromDate = '';
    }
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $toDate) || ($monthStart !== '' && ($toDate < $monthStart || $toDate > $monthEnd))) {
        $toDate = '';
    }
    if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
        $temporaryDate = $fromDate;
        $fromDate = $toDate;
        $toDate = $temporaryDate;
    }

    return array('companyId' => $companyId, 'bankId' => $bankId, 'month' => $month, 'year' => $year, 'fromDate' => $fromDate, 'toDate' => $toDate);
}

function advancedPaymentReportWhere($dbconn, $filters)
{
    $where = array('am.isDelete=0', 'am.istatus=1');
    if ($filters['companyId'] > 0) {
        $where[] = 'ad.iCompanyId=' . $filters['companyId'];
    }
    if ($filters['bankId'] > 0) {
        $where[] = $filters['bankId'] === 3 ? 'ad.iBankId NOT IN (1,2)' : 'ad.iBankId=' . $filters['bankId'];
    }
    if ($filters['month'] !== '') {
        $where[] = "DATE_FORMAT(ad.strDate, '%m')='" . mysqli_real_escape_string($dbconn, $filters['month']) . "'";
    }
    if ($filters['year'] !== '') {
        $where[] = "DATE_FORMAT(ad.strDate, '%Y')='" . mysqli_real_escape_string($dbconn, $filters['year']) . "'";
    }
    if ($filters['fromDate'] !== '') {
        $where[] = "DATE(ad.strDate)>='" . mysqli_real_escape_string($dbconn, $filters['fromDate']) . "'";
    }
    if ($filters['toDate'] !== '') {
        $where[] = "DATE(ad.strDate)<='" . mysqli_real_escape_string($dbconn, $filters['toDate']) . "'";
    }
    return implode(' AND ', $where);
}

function advancedPaymentReportUsesBankFormat($filters)
{
    return $filters['bankId'] === 1 || $filters['bankId'] === 2;
}

function advancedPaymentReportShowsTransferNote($filters)
{
    return $filters['bankId'] === 3;
}

function advancedPaymentReportDateLabel($filters)
{
    if ($filters['fromDate'] !== '' || $filters['toDate'] !== '') {
        $from = $filters['fromDate'] !== '' ? date('d-m-Y', strtotime($filters['fromDate'])) : 'Beginning';
        $to = $filters['toDate'] !== '' ? date('d-m-Y', strtotime($filters['toDate'])) : 'End';
        return $from . ' to ' . $to;
    }
    if ($filters['month'] !== '' && $filters['year'] !== '') {
        return DateTime::createFromFormat('!m/Y', $filters['month'] . '/' . $filters['year'])->format('F-Y');
    }
    if ($filters['month'] !== '') {
        return DateTime::createFromFormat('!m', $filters['month'])->format('F') . ' (All Years)';
    }
    return $filters['year'] !== '' ? $filters['year'] : 'All Dates';
}

function advancedPaymentReportMonthLabel($filters)
{
    if ($filters['month'] !== '' && $filters['year'] !== '') {
        return DateTime::createFromFormat('!m/Y', $filters['month'] . '/' . $filters['year'])->format('M-Y');
    }
    if ($filters['month'] !== '') {
        return DateTime::createFromFormat('!m', $filters['month'])->format('M') . ' (All Years)';
    }
    return $filters['year'] !== '' ? $filters['year'] : 'All Months';
}

function advancedPaymentReportQuery($where)
{
    return "SELECT ad.iAmount, ad.strDate, ad.strRemarks, e.emp_name, e.employeecode, e.accountno, e.ifsccode, " .
        "c.companyname, b.bankname FROM advanced_details ad " .
        "INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId " .
        "INNER JOIN employee e ON e.employeeId=ad.iEmployeeId " .
        "INNER JOIN companymaster c ON c.companymasterId=ad.iCompanyId " .
        "INNER JOIN bankmaster b ON b.bankmasterId=ad.iBankId " .
        "WHERE " . $where . " ORDER BY ad.strDate DESC, e.emp_name ASC";
}