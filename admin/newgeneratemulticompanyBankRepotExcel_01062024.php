<?php
error_reporting(E_ALL);
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;


// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

include('../config.php');

if ($_REQUEST['companysalarymasterId'] != NULL && $_REQUEST['salarymonthId'] != NULL) {
    $where = " and multicompany.companysalarymasterId = " . $_REQUEST['companysalarymasterId'] . " " and "  
        salarymaster.month = " . $_REQUEST['salarymonthId'] . " ";
}
$where1 = "";
if ($_REQUEST['bank'] != NULL) {
    if ($_REQUEST['bank'] == 3) {
        $where1 .= " and employee.bankid not in (1,2) and multicompany.pay_cash='0'";
        //$where1 .= " and employee.bankid not in (2) and multicompany.pay_cash='0'";
    } else if ($_REQUEST['bank'] == 1 || $_REQUEST['bank'] == 2) {
        $where1 .= " and employee.bankid = " . $_REQUEST['bank'] . " and multicompany.pay_cash='0'";
    } else {
        $where1 .= " and multicompany.pay_cash='1'";
    }
}
$filterstr = "SELECT multicompany.balance1,companysalarymaster.companysalarymasterId,employee.employeeId,employee.emp_name
    ,(select bankmaster.bankname from bankmaster where bankmaster.bankmasterId = 
    employee.bankid) as BankName ,employee.ifsccode ,employee.accountno 
    ,(select companysalarymaster.month from companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId) as DisplayMonth 
    FROM `multicompany`,employee,companysalarymaster where 
    companysalarymaster.companysalarymasterId = multicompany.companysalarymasterId and 
    multicompany.emp_id = employee.employeeId  " . $where . "  " . $where1 . "   ORDER BY employee.emp_name asc ";

$result = mysqli_query($dbconn, $filterstr);

if (mysqli_num_rows($result) > 0) {
    $comp = mysqli_query($dbconn, "SELECT * FROM `companymaster`  where isDelete='0'  and  istatus='1' and companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")");
    $companymasterId1 = '';
    while ($commaster1 = mysqli_fetch_array($comp)) {
        $companymasterId1 = $commaster1['companyname'] . ',' . $companymasterId1;
    }
    $companymasterId1 = rtrim($companymasterId1, ", ");
//    echo "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'";
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'"));
    if ($_REQUEST['bank'] != 0) {
        if ($bank['bankmasterId'] == '1' || $bank['bankmasterId'] == '2') {
            if($bank['bankmasterId'] == 1){
                $BankName = "BOB";
            } else {
                $BankName = "SBI";
            }
            //$BankName = $bank['bankname'];
        } else {
            $BankName = 'Other';
        }
    } else {
        $BankName = "Cash";
    }
    
    $spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('E1', 'Date: ' . date('d-m-Y'));
    $spreadsheet->getActiveSheet()->getStyle('E1:E1')->getFont()->setSize(10);
    $spreadsheet->getActiveSheet()->getStyle('E1:E1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $spreadsheet->getActiveSheet()->getStyle('E1:E1')->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
     $spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('A2', 'SUB :')
    ->setCellValue('B2', 'PAYMENT SHEET')
    ->setCellValue('A3', 'SITE :')
    ->setCellValue('B3', $companymasterId1)
    ->setCellValue('A4', $BankName ." :")
    ->setCellValue('B4', $_REQUEST['salarymonthId'].'- Bank Payment');
    $spreadsheet->getActiveSheet()->getStyle('A2:B2')->getFont()->setSize(10);
    $spreadsheet->getActiveSheet()->getStyle('A2:B2')->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('A3:B3')->getFont()->setSize(10);
    $spreadsheet->getActiveSheet()->getStyle('A3:B3')->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('A4:B4')->getFont()->setSize(10);
    $spreadsheet->getActiveSheet()->getStyle('A4:B4')->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    // $spreadsheet->getActiveSheet()->getColumnDimension('A2')->setAutoSize(true);
    // $spreadsheet->getActiveSheet()->getColumnDimension('B2')->setAutoSize(true);
    // $spreadsheet->getActiveSheet()->getColumnDimension('A3')->setAutoSize(true);
    // $spreadsheet->getActiveSheet()->getColumnDimension('B3')->setAutoSize(true);
    // $spreadsheet->getActiveSheet()->getColumnDimension('A4')->setAutoSize(true);
    // $spreadsheet->getActiveSheet()->getColumnDimension('B4')->setAutoSize(true);
    // Set document properties
    $spreadsheet->getProperties()->setCreator('Your Name')
        ->setLastModifiedBy('Your Name')
        ->setTitle('Multi Company Bank Report')
        ->setSubject('Multi Company Bank Report')
        ->setDescription('Report generated using PHP classes.')
        ->setKeywords('office php')
        ->setCategory('Report');
        
    // Add some data
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('A5', 'Sr. No.')
        ->setCellValue('B5', 'Name')
        ->setCellValue('C5', 'Balance')
        ->setCellValue('D5', 'IFSC Code')
        ->setCellValue('E5', 'Bank A/C No');
    // Increase row height to auto for row 5
    // $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(15);
    $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(33); // Adjust the height as needed
    // Reduce font size for all cells in the worksheet
    //$spreadsheet->getDefaultStyle()->getFont()->setSize(10); 
    $spreadsheet->getActiveSheet()->getStyle('A5:E5')->getFont()->setSize(10); // Adjust the font size as needed
    
    // Apply style to header
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => '000000',
            ],
        ],
        'alignment' => [
            'wrapText' => true, // Enable text wrapping
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center align text
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Center align vertically
        ],
    ];
    
    $spreadsheet->getActiveSheet()->getStyle('A5:E5')->applyFromArray($headerStyle);
    
    
    // Example data
    $data = [];
    $rowNumber = 6;
    $iCounter = 1;
    $Total = 0;
    
    while ($row = mysqli_fetch_array($result)) {
        $employee = "select sum(salarydetails.netamountpaid) as PaidAmount from salarymaster,salarydetails
               where salarymaster.salarymasterId = salarydetails.salaryId
               and salarymaster.month = '" . $_REQUEST['salarymonthId'] . "'  
               and salarymaster.companymasterId in (select multiycompanysalarymaster.companymasterId from 
               multiycompanysalarymaster where multiycompanysalarymaster.companysalarymasterId =  " . $_REQUEST['companysalarymasterId'] . ")
               and salarydetails.emp_id = " . $row['employeeId'] . "  and salarymaster.isDelete=0 ";

        $empdata = mysqli_fetch_array(mysqli_query($dbconn, $employee));
        if ($_REQUEST['bank'] != 0) {
            if ($row['BankName'] == 'BOB' || $row['BankName'] == 'SBI') {
                $BankName = $row['BankName'];
            } else if(isset($row['BankName']) || $row['BankName'] != ""){ 
                $BankName = $row['BankName'];
            } else {
                $BankName = 'Other';
            }
        } else {
            $BankName = "Cash";
        }
        $Balance = $row['balance1'] - $empdata['PaidAmount'];
        
        if ($Balance > 0) {
            $Total += $Balance;
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $rowNumber, $iCounter)
                ->setCellValue('B' . $rowNumber, ucwords(strtolower($row['emp_name'])))
                ->setCellValue('C' . $rowNumber, number_format($Balance,2))
                ->setCellValue('D' . $rowNumber, $row['ifsccode'])
                ->setCellValueExplicit('E' . $rowNumber, str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$row['accountno']))), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Ensure the account number is treated as text
                
            $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
        
            $rowNumber++;
            $iCounter++;
        }
    }
    $tableRange = 'A5:E' . $rowNumber; // Assuming $rowNumber is the last row of your table
    $spreadsheet->getActiveSheet()->getStyle($tableRange)->applyFromArray(
        [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // You can adjust the color if needed
                ],
            ],
        ]
    );
    $spreadsheet->getActiveSheet()
        ->setCellValue('B' . $rowNumber, "Total")
        ->setCellValue('C' . $rowNumber, number_format($Total,2));
    $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
    
    $headerStyleNew = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => '000000',
            ],
        ],
    ];
    
    $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->applyFromArray($headerStyleNew);
    
    // Set specific width for columns (optional)
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30); // Adjust the width as needed
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(10);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(13);
    
    // Reduce font size for all cells in the worksheet
    //$spreadsheet->getDefaultStyle()->getFont()->setSize(10); // Adjust the font size as needed
    
}

$rowNumber++;
$rowNumber++;
$spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.$rowNumber, 'For, SHREE GANESH ENGINEERING CO.');
$spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':'.'E'.$rowNumber)->applyFromArray(
    [
     'font' => [
            'bold' => true,
        ]
    ]
);
$spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E' . $rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(8);

$rowNumber++;
$rowNumber++;

$rowNumber++;    
$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('E'.$rowNumber, 'HITESH.K.SHAH (PARTNER)');
$spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':'.'E'.$rowNumber)->applyFromArray(
    [
     'font' => [
            'bold' => true,
        ]
    ]
);
$spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E' . $rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
$spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(8);

$rowNumber++;    
$spreadsheet->setActiveSheetIndex(0)
    ->setCellValue('B'.$rowNumber, 'Cheque No.');
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':B'.$rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':B'.$rowNumber)->getFont()->setSize(8);
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':B'.$rowNumber)->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    
    $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.$rowNumber, '');
    $spreadsheet->getActiveSheet()->getStyle('C'.$rowNumber.':'.'C'.$rowNumber)->applyFromArray(
    [
         'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000'], // You can adjust the color if needed
            ],
        ],
        
    ]
);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Create Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'multi_companyBankReport_' . date('Y-m-d_H-i-s') . '.xlsx';
$writer->save("ReportExcel/".$filename);

// Redirect to the generated Excel file
header('Location: ReportExcel/' . $filename);

exit;
?>
