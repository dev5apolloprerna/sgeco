<?php

include_once 'advancedPaymentReportData.php';

/** Build the single matrix used by the page, Excel export, and PDF export. */
function summaryAdvancePaymentData($dbconn, $source)
{
    $filters = advancedPaymentReportFilters($dbconn, $source);
    if ($filters['month'] === '' || $filters['year'] === '') {
        return array('filters' => $filters, 'dates' => array(), 'columns' => array(), 'groups' => array(), 'sites' => array(), 'columnTotals' => array(), 'grandTotal' => 0);
    }

    $where = advancedPaymentReportWhere($dbconn, $filters);
    $sql = "SELECT DATE(ad.strDate) advanceDate, ad.iCompanyId, c.companyname, ad.iEmployeeId, " .
        "e.emp_name, e.employeecode, e.isPermanent, SUM(ad.iAmount) amount " .
        "FROM advanced_details ad " .
        "INNER JOIN advanced_master am ON am.iAdvancedMasterId=ad.iAdvancedMasterId " .
        "INNER JOIN employee e ON e.employeeId=ad.iEmployeeId " .
        "INNER JOIN companymaster c ON c.companymasterId=ad.iCompanyId " .
        "WHERE " . $where . " GROUP BY DATE(ad.strDate), ad.iCompanyId, c.companyname, " .
        "ad.iEmployeeId, e.emp_name, e.employeecode, e.isPermanent " .
        "ORDER BY advanceDate, c.companyname, e.isPermanent DESC, e.emp_name";
    $result = mysqli_query($dbconn, $sql);
    $dates = array();
    $companies = array();
    $rows = array();
    $columns = array();
    $employees = array();
    $sites = array();
    $columnTotals = array();
    $grandTotal = 0;

    while ($result && $row = mysqli_fetch_assoc($result)) {
        $date = $row['advanceDate'];
        //$key = $date . ':' . (int) $row['iCompanyId'];
        if (!isset($dates[$date])) {
            $dates[$date] = array();
        }
        // if (!isset($columns[$key])) {
        //     $columns[$key] = array('key' => $key, 'date' => $date, 'companyId' => (int) $row['iCompanyId'], 'company' => $row['companyname']);
        $companyId = (int) $row['iCompanyId'];
        $companies[$companyId] = $row['companyname'];
        $rows[] = $row;
    }

    // Every date must repeat the complete company list.  Creating columns only
    // from rows containing a payment made the report contract on sparse data.
    foreach ($dates as $date => $unused) {
        foreach ($companies as $companyId => $companyName) {
            $key = $date . ':' . $companyId;
            $columns[$key] = array('key' => $key, 'date' => $date, 'companyId' => $companyId, 'company' => $companyName);
            $dates[$date][] = $key;
            $columnTotals[$key] = 0;
        }
        // $sites[(int) $row['iCompanyId']] = $row['companyname'];
    }
    $sites = $companies;

    foreach ($rows as $row) {
        $key = $row['advanceDate'] . ':' . (int) $row['iCompanyId'];
        $employeeId = (int) $row['iEmployeeId'];
        if (!isset($employees[$employeeId])) {
            $employees[$employeeId] = array(
                'id' => $employeeId,
                'name' => ucwords(strtolower($row['emp_name'])),
                'code' => $row['employeecode'],
                'group' => ((int) $row['isPermanent'] === 1 ? 'OFFICE STAFF' : 'EMPLOYEES'),
                'amounts' => array(),
                'total' => 0,
            );
        }
        $amount = (float) $row['amount'];
        $employees[$employeeId]['amounts'][$key] = $amount;
        $employees[$employeeId]['total'] += $amount;
        $columnTotals[$key] += $amount;
        $grandTotal += $amount;
    }

    $groups = array();
    foreach ($employees as $employee) {
        $groups[$employee['group']][] = $employee;
    }
    return array(
        'filters' => $filters,
        'dates' => $dates,
        'columns' => $columns,
        'groups' => $groups,
        'sites' => array_values($sites),
        'columnTotals' => $columnTotals,
        'grandTotal' => $grandTotal,
    );
}

function summaryAdvancePaymentMonth($filters)
{
    $date = DateTime::createFromFormat('!m/Y', $filters['month'] . '/' . $filters['year']);
    // return $date ? $date->format('F - Y') : '';
    return $date ? $date->format('M-y') : '';
}

function summaryAdvancePaymentNumber($amount)
{
    return number_format((float) $amount, 2, '.', '');
}
