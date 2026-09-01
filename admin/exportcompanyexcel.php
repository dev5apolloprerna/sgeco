<?php

error_reporting(0);
ob_start();
ob_clean();
//require_once('tcpdf/config/tcpdf_config.php');
//require_once('tcpdf/tcpdf.php');
include('../config.php');
include_once 'companyReportAdvance.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$HeaderCompany = "";
$companymasterId = "";
$month = '';
$comnymasid = array();

$i = 1;
$delimiter = ",";
$filename = "multicompanyreport" . date('Y-m-d H:i:s') . ".csv";
//create a file pointer

$f = fopen('php://memory', 'w');
$comid = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");
$month = "";
$companyname = "";
while ($companymaster = mysqli_fetch_array($comid)) {
    $month = $companymaster['month'];
    $companyname = $companymaster['companyname'];
}
$companyname = rtrim($companyname, ',');

$fields = array(
    'SUB:',
    'PAYMENT SHEET',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    ''
);
fputcsv($f, $fields, $delimiter);
$fields = array(
    'SITE:',
    $companyname,
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    ''
);
fputcsv($f, $fields, $delimiter);

$fields = array(
    'MONTH',
    $month,
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    'Date',
    date('d-m-Y')
);
fputcsv($f, $fields, $delimiter);

$fields = array(
    'Sr. No.',
    'PF.NO.',
    'ESIC No',
    'Name',
    'Rate',
    'Present Days',
    'O.T Hours',
    'Present Amount',
    'O.T Amount',
    'Total Amount',
    'Adv',
    'ADV TWO',
    'Adv. Paid By Bank',
    'PF Amt.',
    'ESIC Amt.',
    'Total',
    'F.A.',
    'T.A.',
    'Balance'
);


$comid1 = mysqli_query($dbconn, "SELECT *,(SELECT companyname FROM companymaster where companymaster.companymasterId = multiycompanysalarymaster.companymasterId) as companyname ,(SELECT companysalarymaster.month FROM companysalarymaster where companysalarymaster.companysalarymasterId = multiycompanysalarymaster.companysalarymasterId) as month FROM multiycompanysalarymaster  where companysalarymasterId='" . $_REQUEST['token'] . "'  order by companyname");

while ($commaster = mysqli_fetch_array($comid1)) {
    $month = $commaster['month'];
    array_push($fields, $commaster['companyname']);
    array_push($comnymasid, $commaster['companymasterId']);
}
$headfield = array(
    'Total Balance',
    'Bank / IFSC',
    'Bank A/c No.',
    'Paid Date'
);
$fields = array_merge($fields, $headfield);

fputcsv($f, $fields, $delimiter);
$array[][] = array();
$array[0][0] = "";
$array[0][1] = "CASH";
$array[0][2] = "SBI";
$array[0][3] = "BOB";
$array[0][4] = "OTHER";
$array[0][5] = "TOTAL";
$array[1][0] = "BANK PAYMENT";
$array[1][1] = 0;
$array[1][2] = 0;
$array[1][3] = 0;
$array[1][4] = 0;
$array[1][5] = 0;
$array[2][0] = "BALANCE PAYMENT";
$array[2][1] = 0;
$array[2][2] = 0;
$array[2][3] = 0;
$array[2][4] = 0;
$array[2][5] = 0;
$array[3][0] = "TOTAL";
$array[3][1] = 0;
$array[3][2] = 0;
$array[3][3] = 0;
$array[3][4] = 0;
$array[3][5] = 0;
$array[4][0] = "ADVANCE PAYMENT";
$array[4][1] = 0;
$array[4][2] = 0;
$array[4][3] = 0;
$array[4][4] = 0;
$array[4][5] = 0;
$array[5][0] = "TOTAL PAYMENT";
$array[5][1] = 0;
$array[5][2] = 0;
$array[5][3] = 0;
$array[5][4] = 0;
$array[5][5] = 0;

$query = "SELECT * FROM `multicompany` where  companysalarymasterId='" . $_REQUEST['token'] . "' order by multicompanyid desc";
// $reportAdvances = getMultiCompanyReportAdvances($dbconn, $_REQUEST['token'], $month);
$reportDeductions = getMultiCompanyReportDeductions($dbconn, $_REQUEST['token']);
$Total = array("", "Total", "", "", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0");



$result = mysqli_query($dbconn, $query);
$PaidcompanyWiseTotal = array();
$balance2 = 0;
$TotalBalance2 = array("0");
$companyWiseTotal = array();
while ($row = mysqli_fetch_assoc($result)) {
    $desg = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `employee`  where isDelete='0' and employeeId='" . $row['emp_id'] . "'"));
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT bankname FROM `bankmaster`  where  bankmasterId='" . $desg['bankid'] . "'"));
    $HeaderCompany = "0";
    //$comnymasid = explode(',', $companymasterId);
    $iCounter = 0;
    $netamt = '0';
    $AllCompanyTotal = 0;
    $EmployeeSalary = array();
    $bal2 = 0;

    //    $Total[0] = $bal2 + $Total[0];
    $CompanysTotal = '';
    // $advPaidByBank = (float) $row['advance_paid_by_bank'];
    // $pfAmount = (float) $row['pf_amount'];
    // $esicAmount = (float) $row['esic_amount'];
    // $advPaidByBank = getEmployeeCompanyReportAdvance($reportAdvances, $row['emp_id']);
    // Use the value saved on this report so Excel matches the reviewed list.
    $advPaidByBank = (float) $row['advance_paid_by_bank'];
    $employeeDeductions = getEmployeeMultiCompanyReportDeductions($reportDeductions, $row['emp_id']);
    $pfAmount = $employeeDeductions['pf'];
    $esicAmount = $employeeDeductions['esic'];
    $calculation = calculateMultiCompanySalary($row['PresentAmount'], $row['otamt'], $row['adv'], $row['adv_two'], $advPaidByBank, $pfAmount, $esicAmount, $row['Fa'], $row['Ta']);
    // Payment-sheet Total and Balance values must always be rounded upward.
    // Use the rounded values for both display and report totals so the footer
    // agrees with the employee rows.
    $rowTotal = ceil($calculation['total']);
    $rowBalance = ceil($calculation['balance1']);
    $lineData = array(
        $i,
        $desg['pfcode'],
        $desg['ecsno'],
        ucwords(strtolower($desg['emp_name'])),
        $row['rate'],
        $row['workingdays'],
        $row['othours'],
        $row['PresentAmount'],
        $row['otamt'],
        $row['totalamt'],
        $row['adv'],
        $row['adv_two'],
        $advPaidByBank,
        $pfAmount,
        $esicAmount,
        $rowTotal,
        $row['Fa'],
        $row['Ta'],
        $rowBalance
    );
    $Total[4] += $row['rate'];
    $Total[5] += $row['workingdays'];
    $Total[6] += $row['othours'];
    $Total[7] += $row['PresentAmount'];
    $Total[8] += $row['otamt'];
    $Total[9] += $row['totalamt'];
    $Total[10] += $row['adv'];
    $Total[11] += $row['adv_two'];
    $Total[12] += $advPaidByBank;
    $Total[13] += $pfAmount;
    $Total[14] += $esicAmount;
    $Total[15] += $rowTotal;
    $Total[16] += $row['Fa'];
    $Total[17] += $row['Ta'];
    $Total[18] += $rowBalance;
    //    $Total=array_merge($Total, $PaidcompanyWiseTotal);

    $strPaymentDate = "";
    for ($iCounter = 0; $iCounter < sizeof($comnymasid); $iCounter++) {

        $Query = "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and month='" . $month . "' and companymasterId='" . $comnymasid[$iCounter] . "'";
        $saleryid = mysqli_fetch_array(mysqli_query($dbconn, $Query));

        $comp = mysqli_query($dbconn, "SELECT salarydetails.netamountpaid,strPaymentDate FROM `salarydetails` WHERE salarydetails.companyId in (" . $comnymasid[$iCounter] . ") and salarydetails.emp_id='" . $row['emp_id'] . "' and  salarydetails.salaryId = '" . $saleryid['salarymasterId'] . "'");
        if (mysqli_num_rows($comp) > 0) {
            while ($rowfiltercom = mysqli_fetch_array($comp)) {
                if ($rowfiltercom['netamountpaid'] != '') {
                    $strPaymentDate = $rowfiltercom['strPaymentDate'];
                    $AllCompanyTotal = $AllCompanyTotal + $rowfiltercom['netamountpaid'];
                    $companyWiseTotal[$iCounter] = $companyWiseTotal[$iCounter] + $rowfiltercom['netamountpaid'];

                    //                    if ($companyWiseTotal[$iCounter] == '') {
                    //                        $companyWiseTotal[$iCounter] = 0;
                    //                        array_sum($companyWiseTotal[$iCounter]);
                    //                    } else {
                    //                        array_sum($companyWiseTotal[$iCounter]);
                    //                    }

                    /*
                    Old Code
                    $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                    $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                    if ($desg['bankid'] == 2) {
                        $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                        $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                    } else if ($desg['bankid'] != 2) {
                        $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                        $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                    } */

                    if ($row['pay_cash'] == 0) {
                        if ($desg['bankid'] == 2) {
                            $array[1][2] = $rowfiltercom['netamountpaid'] + $array[1][2];
                            $array[3][2] = $rowfiltercom['netamountpaid'] + $array[3][2];
                        } else if ($desg['bankid'] == 1) {
                            $array[1][3] = $rowfiltercom['netamountpaid'] + $array[1][3];
                            $array[3][3] = $rowfiltercom['netamountpaid'] + $array[3][3];
                            //}
                        } else if ($desg['bankid'] != 2 || $desg['bankid'] != 1) {
                            $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                            $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                        }
                    } else {
                        $array[1][1] = $rowfiltercom['netamountpaid'] + $array[1][1];
                        $array[3][1] = $rowfiltercom['netamountpaid'] + $array[3][1];
                    }
                    /* else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                        $array[1][4] = $rowfiltercom['netamountpaid'] + $array[1][4];
                        $array[3][4] = $rowfiltercom['netamountpaid'] + $array[3][4];
                    }*/


                    array_push($EmployeeSalary, $rowfiltercom['netamountpaid']);
                } else {
                    $companyWiseTotal[$iCounter] = $companyWiseTotal[$iCounter] + 0;
                    array_push($EmployeeSalary, 0);
                }
            }
        } else {
            array_push($EmployeeSalary, 0);
        }
    }

    $bal2 = $rowBalance - $AllCompanyTotal;
    $bal2 = ceil($bal2);

    if ($bal2 > 0) {

        // $array[2][4] = $bal2 + $array[2][4];
        // $array[3][4] = $bal2 + $array[3][4];
        if ($row['pay_cash'] == 0) {
            if ($desg['bankid'] == 2) {
                $array[2][2] = $bal2 + $array[2][2];
                $array[3][2] = $bal2 + $array[3][2];
            } else if ($desg['bankid'] == 1) {
                $array[2][3] = $bal2 + $array[2][3];
                $array[3][3] = $bal2 + $array[3][3];
            } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                $array[2][4] = $bal2 + $array[2][4];
                $array[3][4] = $bal2 + $array[3][4];
            }
        } else {
            $array[2][1] = $bal2 + $array[2][1];
            $array[3][1] = $bal2 + $array[3][1];
        }
    } else {
        // $array[4][4] = ($bal2 * (-1)) + $array[4][4];
        if ($row['pay_cash'] == 0) {
            if ($desg['bankid'] == 2) {
                $array[4][2] = ($bal2 * (-1)) + $array[4][2];
            } else if ($desg['bankid'] == 1) {
                $array[4][3] = ($bal2 * (-1)) + $array[4][3];
            } else if ($desg['bankid'] != 2 && $desg['bankid'] != 1) {
                $array[4][4] = ($bal2 * (-1)) + $array[4][4];
            }
        } else {
            $array[4][1] = ($bal2 * (-1)) + $array[4][1];
        }
    }

    $array[1][5] = $array[1][1] + $array[1][2] + $array[1][3] + $array[1][4];
    $array[2][5] = $array[2][1] + $array[2][2] + $array[2][3] + $array[2][4];
    $array[3][5] = $array[3][1] + $array[3][2] + $array[3][3] + $array[3][4];
    $array[4][5] = $array[4][1] + $array[4][2] + $array[4][3] + $array[4][4];
    $array[5][5] = $array[5][1] + $array[5][2] + $array[5][3] + $array[5][4];

    $array[5][1] = $array[3][1] + ($array[4][1] * (-1));
    $array[5][2] = $array[3][2] + ($array[4][2] * (-1));
    $array[5][3] = $array[3][3] + ($array[4][3] * (-1));
    $array[5][4] = $array[3][4] + ($array[4][4] * (-1));
    $array[5][5] = $array[3][5] + $array[4][5] * (-1);



    $TotalBalance2[0] += $bal2;

    $lineData = array_merge($lineData, $EmployeeSalary);
    if ($bal2 > 0) {
        $bankDetails = array(
            $bal2,
            $bank['bankname'] . " - " . $desg['ifsccode'],
            $desg['accountno'],
            $row['strPaymentDate']
        );
    } else {
        $bankDetails = array(
            $bal2,
            $bank['bankname'] . " - " . $desg['ifsccode'],
            $desg['accountno'],
            $strPaymentDate
        );
    }

    $lineData = array_merge($lineData, $bankDetails);
    fputcsv($f, $lineData, $delimiter);
    $i++;
}
if (empty($companyWiseTotal)) {
    $companyWiseTotal[0] = 0;
    array_push($PaidcompanyWiseTotal, $companyWiseTotal[0]);
} else {
    for ($i = 0; $i < sizeof($companyWiseTotal); $i++) {
        array_push($PaidcompanyWiseTotal, $companyWiseTotal[$i]);
    }
}
$Total = array_merge($Total, $PaidcompanyWiseTotal);
$Total = array_merge($Total, $TotalBalance2);

fputcsv($f, $Total, $delimiter);

$fields = array(
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
);
fputcsv($f, $fields, $delimiter);
$fields = array(
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
);
fputcsv($f, $fields, $delimiter);
$fields = array(
    '',
    '',
    '',
    'Summary',
    '',
    '',
    '',
    '',
);
fputcsv($f, $fields, $delimiter);

$SummeryHeader = array(
    $array[0][0] => "",
    $array[0][1] => "CASH",
    $array[0][2] => "SBI",
    $array[0][3] => "BOB",
    $array[0][4] => "OTHER",
    $array[0][5] => "TOTAL"
);
fputcsv($f, $SummeryHeader, $delimiter);
$dataTable1 = array(
    $array[1][0] => "BANK PAYMENT",
    $array[1][1],
    $array[1][2],
    $array[1][3],
    $array[1][4],
    $array[1][5]
);
fputcsv($f, $dataTable1, $delimiter);

$dataTable2 = array(
    $array[2][0] => "BALANCE PAYMENT",
    $array[2][1],
    $array[2][2],
    $array[2][3],
    $array[2][4],
    $array[2][5]
);
fputcsv($f, $dataTable2, $delimiter);

$dataTable3 = array(
    $array[3][0] => "TOTAL",
    $array[3][1],
    $array[3][2],
    $array[3][3],
    $array[3][4],
    $array[3][5]
);
fputcsv($f, $dataTable3, $delimiter);

$dataTable4 = array(
    $array[4][0] => "ADVANCE PAYMENT",
    $array[4][1],
    $array[4][2],
    $array[4][3],
    $array[4][4],
    $array[4][5],
);
fputcsv($f, $dataTable4, $delimiter);

$dataTable5 = array(
    $array[5][0] => "TOTAL PAYMENT",
    $array[5][1],
    $array[5][2],
    $array[5][3],
    $array[5][4],
    $array[5][5]
);
fputcsv($f, $dataTable5, $delimiter);

fseek($f, 0);

$reportRows = array();
while (($reportRow = fgetcsv($f, 0, $delimiter)) !== false) {
    $reportRows[] = $reportRow;
}
fclose($f);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Multi Company Report');
$sheet->fromArray($reportRows, null, 'A1', true);
$sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

$headerRow = 4;
$dataStartRow = $headerRow + 1;
$employeeCount = mysqli_num_rows($result);
$totalRow = $dataStartRow + $employeeCount;
$summaryTitleRow = $totalRow + 3;
$summaryHeaderRow = $summaryTitleRow + 1;
$summaryLastRow = $summaryHeaderRow + 5;
$lastColumnNumber = count($reportRows[$headerRow - 1]);
$lastColumn = Coordinate::stringFromColumnIndex($lastColumnNumber);
$totalColumn = Coordinate::stringFromColumnIndex(16);
$balanceColumn = Coordinate::stringFromColumnIndex(19);
// Present the report heading as full-width sections, matching the formatted
// statutory company report rather than the old comma-separated download.
$sheet->mergeCells('B1:' . $lastColumn . '1');
$sheet->mergeCells('B2:' . $lastColumn . '2');
$sheet->mergeCells('B3:J3');
$sheet->mergeCells('K3:' . $lastColumn . '3');
$sheet->getStyle('A1:' . $lastColumn . '3')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A1:' . $lastColumn . '3')->getAlignment()
    ->setVertical(Alignment::VERTICAL_CENTER)
    ->setWrapText(true);
$sheet->getRowDimension(1)->setRowHeight(24);
$sheet->getRowDimension(2)->setRowHeight(28);
$sheet->getRowDimension(3)->setRowHeight(24);

$tableRange = 'A' . $headerRow . ':' . $lastColumn . $totalRow;
$sheet->getStyle($tableRange)->applyFromArray(array(
    'borders' => array(
        'allBorders' => array(
            'borderStyle' => Border::BORDER_THIN,
            'color' => array('rgb' => '000000'),
        ),
    ),
    'alignment' => array('vertical' => Alignment::VERTICAL_CENTER),
));
$sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->applyFromArray(array(
    'font' => array('bold' => true),
    'alignment' => array(
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ),
));
$sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->getFont()->setBold(true);
$sheet->getRowDimension($headerRow)->setRowHeight(48);

$sheet->mergeCells('A' . $summaryTitleRow . ':F' . $summaryTitleRow);
$sheet->setCellValue('A' . $summaryTitleRow, 'SUMMARY');
$sheet->getStyle('A' . $summaryTitleRow . ':F' . $summaryTitleRow)->applyFromArray(array(
    'font' => array('bold' => true, 'size' => 12),
    'alignment' => array('horizontal' => Alignment::HORIZONTAL_CENTER),
));
$sheet->getStyle('A' . $summaryHeaderRow . ':F' . $summaryLastRow)->applyFromArray(array(
    'borders' => array(
        'allBorders' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('rgb' => '000000')),
    ),
));
$sheet->getStyle('A' . $summaryHeaderRow . ':F' . $summaryHeaderRow)->getFont()->setBold(true);

// Keep amounts numeric and consistently readable in Excel.
if ($employeeCount > 0) {
    $sheet->getStyle('E' . $dataStartRow . ':' . Coordinate::stringFromColumnIndex($lastColumnNumber - 3) . $totalRow)
        ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
}
// Total and Balance are rounded upward above, but remain numeric cells. Apply
// an explicit two-decimal format to both highlighted columns, including the
// report totals row (for example, 9160 is displayed as 9160.00).
$sheet->getStyle($totalColumn . $dataStartRow . ':' . $totalColumn . $totalRow)
    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle($balanceColumn . $dataStartRow . ':' . $balanceColumn . $totalRow)
    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle('B' . ($summaryHeaderRow + 1) . ':F' . $summaryLastRow)
    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

$columnWidths = array(10, 14, 24, 10, 12, 10, 14, 12, 14, 10, 11, 14, 11, 11, 12, 10, 10, 12);
for ($column = 1; $column <= $lastColumnNumber; $column++) {
    $width = isset($columnWidths[$column - 1]) ? $columnWidths[$column - 1] : 15;
    if ($column >= $lastColumnNumber - 2) {
        $width = 20;
    }
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth($width);
}

$sheet->freezePane('A' . $dataStartRow);
$sheet->setShowGridlines(false);
$sheet->getSheetView()->setZoomScale(80);
$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
$sheet->getPageMargins()->setTop(0.3)->setRight(0.3)->setBottom(0.3)->setLeft(0.3);
$sheet->getPageSetup()->setHorizontalCentered(true);
$sheet->getPageSetup()->setPrintArea('A1:' . $lastColumn . $summaryLastRow);

$filename = 'multicompanyreport_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
while (ob_get_level() > 0) {
    ob_end_clean();
}
(new Xlsx($spreadsheet))->save('php://output');
$spreadsheet->disconnectWorksheets();
exit;
