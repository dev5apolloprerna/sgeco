<?php
error_reporting(E_ALL);
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;

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

$sheet = $spreadsheet->getActiveSheet();
$pageSetup = $sheet->getPageSetup();
$totalComm = 0;

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
            // if($bank['bankmasterId'] == 1){
            //     $BankName = "BOB";
            // } else {
            //     $BankName = "SBI";
            // }
            $BankName = $bank['bankname'];
        } else {
            $BankName = 'Other';
        }
    } else {
        $BankName = "Cash";
    }
    
    $date = DateTime::createFromFormat('m/Y', $_REQUEST['salarymonthId']);
    $formattedDate = "";
    if ($date === false) {
        $formattedDate = $_REQUEST['salarymonthId'];
    } else {
        $formattedDate = $date->format('F-Y');
    }
    
    $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    // Set document properties
    $spreadsheet->getProperties()->setCreator('Your Name')
        ->setLastModifiedBy('Your Name')
        ->setTitle('Multi Company Bank Report')
        ->setSubject('Multi Company Bank Report')
        ->setDescription('Report generated using PHP classes.')
        ->setKeywords('office php')
        ->setCategory('Report');
    
    if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
         $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('G1', 'Date: ' . date('d-m-Y'));
            $spreadsheet->getActiveSheet()->getStyle('G1:G1')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('G1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('G1:G1')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $sheet->getRowDimension(1)->setRowHeight(21);
            
             $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A3', 'SUB : PAYMENT SHEET')
            ->setCellValue('A4', 'SITE : '. $companymasterId1)
            ->setCellValue('A5', 'Month : '. $formattedDate .' - Balance Payment');
            
            $spreadsheet->getActiveSheet()->getStyle('A3:A3')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A3:A3')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A4:A4')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A4:A4')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A5:A5')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A5:A5')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            
            $sheet->getRowDimension(2)->setRowHeight(21);
            $sheet->getRowDimension(3)->setRowHeight(21);
            $sheet->getRowDimension(4)->setRowHeight(21);
            
            $sheet->getRowDimension(5)->setRowHeight(20);
        // $spreadsheet->setActiveSheetIndex(0)
        //     ->setCellValue('A6', 'Sr. No.')
        //     ->setCellValue('B6', 'Name')
        //     ->setCellValue('C6', 'Balance')
        //     ->setCellValue('D6', 'IFSC Code')
        //     ->setCellValue('E6', 'Account')
        //     ->setCellValue('F6', 'Address')
        //     ->setCellValue('G6', 'Comm.');
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A6', 'Sr. No.')
            ->setCellValue('B6', 'Beneficiary Account Number')
            ->setCellValue('C6', 'Balance')
            ->setCellValue('D6', 'Beneficiary Name')
            ->setCellValue('E6', 'Beneficiary Address')
            ->setCellValue('F6', 'IFSC Code')
            ->setCellValue('G6', 'Comm.');
        $spreadsheet->getActiveSheet()->getRowDimension(6)->setRowHeight(33); // Adjust the height as needed
        // Reduce font size for all cells in the worksheet
        $spreadsheet->getActiveSheet()->getStyle('A6:G6')->getFont()->setSize(10); // Adjust the font size as needed
        
        // Apply style to header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'cccccc',
                ],
            ],
            'alignment' => [
                'wrapText' => true, // Enable text wrapping
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center align text
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Center align vertically
            ],
        ];
        
        $spreadsheet->getActiveSheet()->getStyle('A6:G6')->applyFromArray($headerStyle);
        
        // Example data
        $data = [];
        $rowNumber = 7;
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
                $Comm = 0;
                if($Balance <= 10000){
                    $Comm = "2.36";
                } else {
                    $Comm = "4.72";
                }
                $totalComm += $Comm;
                
                $spreadsheet->getActiveSheet()
                    ->setCellValue('A' .$rowNumber, "  ".$iCounter." ")
                    ->setCellValueExplicit('B' . $rowNumber, str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',trim($row['accountno'])))), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING)
                    // ->setCellValue('C' . $rowNumber, number_format($Balance, 2, '.', ','))
                    ->setCellValue('C' . $rowNumber, $Balance)
                    ->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])))
                    ->setCellValue('E' . $rowNumber, '')
                    ->setCellValue('F' . $rowNumber, trim($row['ifsccode']))
                    ->setCellValue('G' . $rowNumber, $Comm); 
                $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                    
                $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
                $sheet->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('C'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('D'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('E'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('F'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('G'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($rowNumber)->setRowHeight(20);
                $rowNumber++;
                $iCounter++;
            }
        }
        
        $tableRange = 'A6:G' . $rowNumber; // Assuming $rowNumber is the last row of your table
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
            // ->setCellValue('C' . $rowNumber, number_format($Total,2))
            // ->setCellValue('G'.$rowNumber, number_format($totalComm,2));
            ->setCellValue('C' . $rowNumber, $Total)
            ->setCellValue('G'.$rowNumber, $totalComm);
        $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('G'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(10); // Adjust the font size as needed
        
        $headerStyleNew = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'cccccc',
                ],
            ],
        ];
        
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':G'.$rowNumber)->applyFromArray($headerStyleNew);
        
        $sheet->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('C'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('D'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('E'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('F'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('G'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($rowNumber)->setRowHeight(20);
            
        // Set specific width for columns (optional)
        // $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(3);
        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Adjust the width as needed
        // $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(8);
        // $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        // $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(13);
        // $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(6);
        // $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        
        // Set specific width for columns (optional)
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(6);
        
        
        // $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        // $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        // $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(22);
        // $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        // $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        // $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        
    } else {
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
            $sheet->getRowDimension(1)->setRowHeight(21);
            
             $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A3', 'SUB : PAYMENT SHEET')
            ->setCellValue('A4', 'SITE :' . $companymasterId1)
            ->setCellValue('A5', 'Month : '. $formattedDate . ' - ' . $BankName  .' - Balance Payment');
            
            $spreadsheet->getActiveSheet()->getStyle('A3:A3')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A3:A3')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A4:A4')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A4:A4')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A5:A5')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A5:A5')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            
            $sheet->getRowDimension(2)->setRowHeight(21);
            $sheet->getRowDimension(3)->setRowHeight(21);
            $sheet->getRowDimension(4)->setRowHeight(21);
            
            $sheet->getRowDimension(5)->setRowHeight(20);
            
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A6', 'Sr. No.')
            ->setCellValue('B6', 'Beneficiary Account Number')
            ->setCellValue('C6', 'Balance')
            ->setCellValue('D6', 'Beneficiary Name')
            ->setCellValue('E6', 'IFSC Code');
        $spreadsheet->getActiveSheet()->getRowDimension(6)->setRowHeight(33); // Adjust the height as needed
        
        // Reduce font size for all cells in the worksheet
        $spreadsheet->getActiveSheet()->getStyle('A6:E6')->getFont()->setSize(10); // Adjust the font size as needed
        
        // Apply style to header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'cccccc',
                ],
            ],
            'alignment' => [
                'wrapText' => true, // Enable text wrapping
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center align text
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Center align vertically
            ],
        ];
        
        $spreadsheet->getActiveSheet()->getStyle('A6:E6')->applyFromArray($headerStyle);
        
        // Example data
        $data = [];
        $rowNumber = 7;
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
                    ->setCellValue('A' . $rowNumber, "  ".$iCounter." ")
                    ->setCellValueExplicit('B' . $rowNumber, str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',trim($row['accountno'])))), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING)
                    //->setCellValue('C' . $rowNumber, number_format($Balance,2))
                    ->setCellValue('C' . $rowNumber, $Balance)
                    ->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])))
                    ->setCellValue('E' . $rowNumber, trim($row['ifsccode']));
                $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(10);  // Adjust the font size as needed
                $sheet->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('C'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('D'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getStyle('E'.$rowNumber)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($rowNumber)->setRowHeight(20);
                $rowNumber++;
                $iCounter++;
            }
        }
        $tableRange = 'A6:E' . $rowNumber; // Assuming $rowNumber is the last row of your table
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
            // ->setCellValue('C' . $rowNumber, number_format($Total,2));
            ->setCellValue('C' . $rowNumber, $Total);
        $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
        
        $headerStyleNew = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'cccccc',
                ],
            ],
        ];
        
        $sheet->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('C'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('D'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('E'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($rowNumber)->setRowHeight(20);
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->applyFromArray($headerStyleNew);
        // Set specific width for columns (optional)
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15); // Adjust the width as needed
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
    }
   
    // Reduce font size for all cells in the worksheet
    //$spreadsheet->getDefaultStyle()->getFont()->setSize(10); // Adjust the font size as needed
    
}
if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
    
    $rowNumber++;
    $rowNumber++;
    
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Amount.')
        // ->setCellValue('C'.$rowNumber, number_format($Total,2));
        ->setCellValue('C'.$rowNumber, $Total);
    $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':'.'C'.$rowNumber)->applyFromArray(
        [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // You can adjust the color if needed
                ],
            ],
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':C'.$rowNumber)->getFont()->setSize(10);//->setAutoSize(true);
    
    $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$rowNumber, 'For, SHREE GANESH ENGINEERING CO.');
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':'.'G'.$rowNumber)->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G' . $rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(10);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
    $rowNumber++;
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Bank Comm.')
        // ->setCellValue('C'.$rowNumber, number_format($totalComm,2));
        ->setCellValue('C'.$rowNumber, $totalComm);
    $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':'.'C'.$rowNumber)->applyFromArray(
        [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // You can adjust the color if needed
                ],
            ],
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':C'.$rowNumber)->getFont()->setSize(10);//->setAutoSize(true);
    $TotalAmt = $totalComm + $Total;
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
    $rowNumber++;
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Total Amt.')
        // ->setCellValue('C'.$rowNumber, number_format($TotalAmt,2));
        ->setCellValue('C'.$rowNumber, $TotalAmt);
    $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':C'.$rowNumber)->getFont()->setSize(10);//->setAutoSize(true);
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':'.'C'.$rowNumber)->applyFromArray(
        [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // You can adjust the color if needed
                ],
            ],
            'font' => [
                'bold' => true,
            ]
        ]
    );
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
    $rowNumber++;    
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('G'.$rowNumber, 'HITESH.K.SHAH (PARTNER)');
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':'.'G'.$rowNumber)->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G' . $rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(10);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
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
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
} else {
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
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    $spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E' . $rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(10);
    
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
    $spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(10);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
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
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
}

// Set header to appear on every printed page
$sheet->getHeaderFooter()->setOddHeader('');

// Set the header margin (height of the header)
$pageMargins = $sheet->getPageMargins();
$pageMargins->setTop(2.5); // Set the top margin to 1 inch (adjust as needed)


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Create Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'multi_companyBankReport_' . date('Y-m-d_H-i-s') . '.xlsx';
$writer->save('ReportExcel/'.$filename);

// Redirect to the generated Excel file
header('Location: ReportExcel/' . $filename);

exit;
?>
