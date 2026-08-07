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


if ($_REQUEST['Company'] != NULL && $_REQUEST['bank'] != NULL && $_REQUEST['salaryId'] != NULL) {
    $where = " and employee.bankid= '" . $_REQUEST['bank'] . "'";
    if ($_REQUEST['bank'] == 3) {
        $where = " and employee.bankid not in (1,2)";
        //$where = " and employee.bankid not in (2)";
    }
}
$query = "SELECT * FROM employee INNER JOIN salarydetails ON employee.employeeId=salarydetails.emp_id where   salarydetails.companyId='" . $_REQUEST['Company'] . "' and salarydetails.salaryId  in (select salarymasterId from salarymaster where  month='" . $_REQUEST['salaryId'] . "' and isDelete='0' and  istatus='1') and salarydetails.workingdays > 0  " . $where . " and  employee.isDelete=0 and employee.istatus=1";
$filterstr = mysqli_query($dbconn, $query);
if (mysqli_num_rows($filterstr) > 0) {
    $comp = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `companymaster` where isDelete='0' and istatus='1' and companymasterId='" . $_REQUEST['Company'] . "'"));
    $bank = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster` where isDelete='0' and istatus='1' and bankmasterId='" . $_REQUEST['bank'] . "'"));
    $salaryid = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `salarymaster`  where isDelete='0'  and  istatus='1' and salarymasterId='" . $_REQUEST['salaryId'] . "'"));
    
    $date = DateTime::createFromFormat('m/Y', $_REQUEST['salaryId']);
    // Check if the date was parsed successfully
    $formattedDate = "";
    if ($date === false) {
        // Handle error
        $formattedDate = $_REQUEST['salaryId'];
    } else {
        // Format the date into the desired format: 'F-Y'
        $formattedDate = $date->format('F-Y');
    }
    
    if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
        $bankname = 'Other';
        
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
            
            
            $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A2', 'SUB :')
            ->setCellValue('C2', 'PAYMENT SHEET')
            ->setCellValue('A3', 'SITE :')
            ->setCellValue('C3', $comp['companyname'])
            ->setCellValue('A4', 'Month : ')
            ->setCellValue('C4', $formattedDate . '- Bank Payment');
            //date('M-y',strtotime(date('d-m-Y',strtotime($_REQUEST['salaryId']))))
            $spreadsheet->getActiveSheet()->getStyle('A2:C2')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A2:C2')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A3:C3')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A3:C3')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A4:C4')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A4:C4')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            // Merge cells A2 and B2
            $spreadsheet->getActiveSheet()->mergeCells('A2:B2');
            
            // Merge cells A3 and B3
            $spreadsheet->getActiveSheet()->mergeCells('A3:B3');
            
            // Merge cells A4 and B4
            $spreadsheet->getActiveSheet()->mergeCells('A4:B4');
        
            // $spreadsheet->getActiveSheet()->getColumnDimension('A2')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('B2')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('A3')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('B3')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('A4')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('B4')->setAutoSize(true);
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Your Name')
            ->setLastModifiedBy('Your Name')
            ->setTitle('Company Bank Report')
            ->setSubject('Company Bank Report')
            ->setDescription('Report generated using PHP classes.')
            ->setKeywords('office php')
            ->setCategory('Report');
        
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A5', 'Sr. No.')
            ->setCellValue('B5', 'Beneficiary Account Number')
            ->setCellValue('C5', 'Amount')
            ->setCellValue('D5', 'Beneficiary Name')
            ->setCellValue('E5', 'Beneficiary Address')
            ->setCellValue('F5', 'IFSC Code')
            ->setCellValue('G5', 'Comm.');
        // Increase row height to auto for row 5
        // $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(33); // Adjust the height as needed
        // Reduce font size for all cells in the worksheet
        //$spreadsheet->getDefaultStyle()->getFont()->setSize(10); 
        $spreadsheet->getActiveSheet()->getStyle('A5:G5')->getFont()->setSize(10); // Adjust the font size as needed
        
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
        
        $spreadsheet->getActiveSheet()->getStyle('A5:G5')->applyFromArray($headerStyle);
        
        // Example data
        $data = [];
        $rowNumber = 6;
        $iCounter = 1;
        $Total = 0;
        $totalComm =0;
        while ($row = mysqli_fetch_array($filterstr)) {
            //$bankname = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $row['bankid'] . "'"));
            $Comm = 0;
            if($row['netamountpaid'] <= 10000){
                $Comm = "2.36";
            } else {
                $Comm = "4.72";
            }
            
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $rowNumber, $iCounter)
                ->setCellValueExplicit('B' . $rowNumber, str_replace('A/C. ','',$row['accountno']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING) // Ensure the account number is treated as text
                ->setCellValue('C' . $rowNumber, number_format($row['netamountpaid'],2))
                ->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])))
                ->setCellValue('E' . $rowNumber, '')
                ->setCellValue('F' . $rowNumber, $row['ifsccode'])
                ->setCellValue('G' . $rowNumber, $Comm);
            $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
        
            $rowNumber++;
            $iCounter++;
            $Total += $row['netamountpaid'];
            $totalComm += $Comm;
        }
        
        $tableRange = 'A5:G' . $rowNumber; // Assuming $rowNumber is the last row of your table
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
                ->setCellValue('B' . $rowNumber, "Total Amt.")
                ->setCellValue('C' . $rowNumber, number_format($Total,2))
                ->setCellValue('G' . $rowNumber, number_format($totalComm,2));
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
        
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
        
        // Set specific width for columns (optional)
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(3);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15); // Adjust the width as needed
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(8);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        
        // Reduce font size for all cells in the worksheet
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10); // Adjust the font size as needed
    } else {
        $bankname = $bank['bankname'];
        
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
            ->setCellValue('C2', 'PAYMENT SHEET')
            ->setCellValue('A3', 'SITE :')
            ->setCellValue('C3', $comp['companyname'])
            ->setCellValue('A4', 'Month : ')
            ->setCellValue('C4', $formattedDate . '- '. $bankname . '- Bank Payment');
            //date('M-y',strtotime(date('d-m-Y',strtotime($_REQUEST['salaryId']))))
            $spreadsheet->getActiveSheet()->getStyle('A2:C2')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A2:C2')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A3:C3')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A3:C3')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            $spreadsheet->getActiveSheet()->getStyle('A4:C4')->getFont()->setSize(10);
            $spreadsheet->getActiveSheet()->getStyle('A4:C4')->applyFromArray(
                [
                 'font' => [
                        'bold' => true,
                    ]
                ]
            );
            // Merge cells A2 and B2
            $spreadsheet->getActiveSheet()->mergeCells('A2:B2');
            
            // Merge cells A3 and B3
            $spreadsheet->getActiveSheet()->mergeCells('A3:B3');
            
            // Merge cells A4 and B4
            $spreadsheet->getActiveSheet()->mergeCells('A4:B4');
        
            // $spreadsheet->getActiveSheet()->getColumnDimension('A2')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('B2')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('A3')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('B3')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('A4')->setAutoSize(true);
            // $spreadsheet->getActiveSheet()->getColumnDimension('B4')->setAutoSize(true);
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Your Name')
            ->setLastModifiedBy('Your Name')
            ->setTitle('Company Bank Report')
            ->setSubject('Company Bank Report')
            ->setDescription('Report generated using PHP classes.')
            ->setKeywords('office php')
            ->setCategory('Report');
        
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A5', 'Sr. No.')
            ->setCellValue('B5', 'Beneficiary Account Number')
            ->setCellValue('C5', 'Amount')
            ->setCellValue('D5', 'Beneficiary Name')
            //->setCellValue('E5', 'Beneficiary Address')
            ->setCellValue('E5', 'IFSC Code');
            //->setCellValue('G5', 'Comm.');
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
        
        $spreadsheet->getActiveSheet()->getStyle('A5:E5')->applyFromArray($headerStyle);
        
        // Example data
        $data = [];
        $rowNumber = 6;
        $iCounter = 1;
        $Total = 0;
        $totalComm =0;
        while ($row = mysqli_fetch_array($filterstr)) {
            //$bankname = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $row['bankid'] . "'"));
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $rowNumber, $iCounter)
                ->setCellValueExplicit('B' . $rowNumber, str_replace('A/C. ','',$row['accountno']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING) // Ensure the account number is treated as text
                ->setCellValue('C' . $rowNumber, number_format($row['netamountpaid'],2))
                ->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])))
                //->setCellValue('E' . $rowNumber, 'Vadodara')
                ->setCellValue('E' . $rowNumber, $row['ifsccode']);
                //->setCellValue('G' . $rowNumber, '2.36');
            $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(8); // Adjust the font size as needed
        
            $rowNumber++;
            $iCounter++;
            $Total += $row['netamountpaid'];
            // $totalComm += 2.36;
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
                ->setCellValue('B' . $rowNumber, "Total Amt.")
                ->setCellValue('C' . $rowNumber, number_format($Total,2));
                //->setCellValue('G' . $rowNumber, number_format($totalComm,2));
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
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->applyFromArray($headerStyleNew);
        
        // Set specific width for columns (optional)
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15); // Adjust the width as needed
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        //$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        //$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        
        // Reduce font size for all cells in the worksheet
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10); // Adjust the font size as needed
    }
    
}


$rowNumber++;
$rowNumber++;
if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Amount.')
        ->setCellValue('C'.$rowNumber, number_format($Total,2));
    
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
    
    $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$rowNumber, 'For, SHREE GANESH ENGINEERING CO.');
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':'.'G'.$rowNumber)->applyFromArray(
        [
         'font' => [
                'bold' => true,
            ]
        ]
    );
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G' . $rowNumber)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(8);

} else {
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

}

$rowNumber++;    
if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Bank Comm.')
        ->setCellValue('C'.$rowNumber, number_format($totalComm,2));
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
    $TotalAmt = $totalComm + $Total;

    $rowNumber++;    
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Total Amt.')
        ->setCellValue('C'.$rowNumber, number_format($TotalAmt,2));
    $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':C'.$rowNumber)->getFont()->setSize(10);//->setAutoSize(true);
    // Set auto-size for the columns B and C
    // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    // $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
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
    $spreadsheet->getActiveSheet()->getStyle('G'.$rowNumber.':G'.$rowNumber)->getFont()->setSize(8);
    
    $rowNumber++;    
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Cheque No');
        $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':'.'B'.$rowNumber)->applyFromArray(
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
} else {
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
        ->setCellValue('B'.$rowNumber, 'Cheque No');
        $spreadsheet->getActiveSheet()->getStyle('B'.$rowNumber.':'.'B'.$rowNumber)->applyFromArray(
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
}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Create Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'companyBankReport_' . date('Y-m-d_H-i-s') . '.xlsx';
$writer->save('ReportExcel/'.$filename);

// Redirect to the generated Excel file
header('Location: ReportExcel/' . $filename);

exit;
?>
