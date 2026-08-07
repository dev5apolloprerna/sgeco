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

$where = " and 1=1";
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
        // Format the date floato the desired format: 'F-Y'
        $formattedDate = $date->format('F-Y');
    }
    
    
        $sheet = $spreadsheet->getActiveSheet();
        $pageSetup = $sheet->getPageSetup();
    
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
            $sheet->getRowDimension(1)->setRowHeight(21);
            
            $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A3', 'SUB : PAYMENT SHEET')
            ->setCellValue('A4', 'SITE :' .$comp['companyname'])
            ->setCellValue('A5', 'Month : '.$formattedDate . ' - Bank Payment');
            //date('M-y',strtotime(date('d-m-Y',strtotime($_REQUEST['salaryId']))))
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
            // Merge cells A2 and B2
            // $spreadsheet->getActiveSheet()->mergeCells('A2:B2');
            
            // // Merge cells A3 and B3
            // $spreadsheet->getActiveSheet()->mergeCells('A3:B3');
            
            // // Merge cells A4 and B4
            // $spreadsheet->getActiveSheet()->mergeCells('A4:B4');
        
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
        $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A6', 'Sr. No.')
            ->setCellValue('B6', 'Beneficiary Account Number')
            ->setCellValue('C6', 'Amount')
            ->setCellValue('D6', 'Beneficiary Name')
            ->setCellValue('E6', 'Beneficiary Address')
            ->setCellValue('F6', 'IFSC Code')
            ->setCellValue('G6', 'Comm.');
        // Increase row height to auto for row 5
        // $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension(6)->setRowHeight(33); // Adjust the height as needed
        // Reduce font size for all cells in the worksheet
        //$spreadsheet->getDefaultStyle()->getFont()->setSize(10); 
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
                ->setCellValue('A' . $rowNumber, " ".$iCounter." ")
                ->setCellValueExplicit('B' . $rowNumber, str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',trim($row['accountno'])))), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING) // Ensure the account number is treated as text
                //->setCellValue('C' . $rowNumber, number_format(floatval($row['netamountpaid']),2))
                ->setCellValue('C' . $rowNumber, $row['netamountpaid'])
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
            $Total += $row['netamountpaid'];
            $totalComm += $Comm;
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
                ->setCellValue('B' . $rowNumber, "Total Amt.")
                // ->setCellValue('C' . $rowNumber, number_format(floatval($Total),2))
                // ->setCellValue('G' . $rowNumber, number_format(floatval($totalComm),2));
                ->setCellValue('C' . $rowNumber, $Total)
                ->setCellValue('G' . $rowNumber, $totalComm);
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
        $sheet->getRowDimension($rowNumber)->setRowHeight(30);
            
        // Set specific width for columns (optional)
        // $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        // $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(8);
        // $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        // $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(8);
        // $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        // $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(6);
        
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
            $sheet->getRowDimension(1)->setRowHeight(21);
            
            $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A3', 'SUB :'.'PAYMENT SHEET')
            ->setCellValue('A4', 'SITE :'.$comp['companyname'])
            ->setCellValue('A5', 'Month : '.$formattedDate . ' - '. $bankname . ' - Bank Payment');
            //date('M-y',strtotime(date('d-m-Y',strtotime($_REQUEST['salaryId']))))
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
            // // Merge cells A2 and B2
            // $spreadsheet->getActiveSheet()->mergeCells('A2:B2');
            
            // // Merge cells A3 and B3
            // $spreadsheet->getActiveSheet()->mergeCells('A3:B3');
            
            // // Merge cells A4 and B4
            // $spreadsheet->getActiveSheet()->mergeCells('A4:B4');
        
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
            
        
        $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A6', 'Sr. No.')
            ->setCellValue('B6', 'Beneficiary Account Number')
            ->setCellValue('C6', 'Amount')
            ->setCellValue('D6', 'Beneficiary Name')
            //->setCellValue('E5', 'Beneficiary Address')
            ->setCellValue('E6', 'IFSC Code');
            //->setCellValue('G5', 'Comm.');
        // Increase row height to auto for row 5
        // $spreadsheet->getActiveSheet()->getRowDimension(5)->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension(6)->setRowHeight(33); // Adjust the height as needed
        // Reduce font size for all cells in the worksheet
        //$spreadsheet->getDefaultStyle()->getFont()->setSize(10); 
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
        $totalComm =0;
        while ($row = mysqli_fetch_array($filterstr)) {
            //$bankname = mysqli_fetch_array(mysqli_query($dbconn, "SELECT * FROM `bankmaster`  where isDelete='0'  and  istatus='1' and bankmasterId='" . $row['bankid'] . "'"));
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $rowNumber, " ".$iCounter." ")
                ->setCellValueExplicit('B' . $rowNumber, str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',trim($row['accountno'])))), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING) // Ensure the account number is treated as text
                //->setCellValue('C' . $rowNumber, number_format(floatval($row['netamountpaid']),2))
                ->setCellValue('C' . $rowNumber, $row['netamountpaid'])
                ->setCellValue('D' . $rowNumber, ucwords(strtolower($row['emp_name'])))
                //->setCellValue('E' . $rowNumber, 'Vadodara')
                ->setCellValue('E' . $rowNumber, trim($row['ifsccode']));
                //->setCellValue('G' . $rowNumber, '2.36');
            $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            
            $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(10); // Adjust the font size as needed
            $sheet->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C'.$rowNumber)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D'.$rowNumber)->getAlignment()->setWrapText(true);
            $sheet->getStyle('E'.$rowNumber)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($rowNumber)->setRowHeight(20);
            $rowNumber++;
            $iCounter++;
            $Total += $row['netamountpaid'];
            // $totalComm += 2.36;
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
                ->setCellValue('B' . $rowNumber, "Total Amt.")
                //->setCellValue('C' . $rowNumber, number_format(floatval($Total),2));
                ->setCellValue('C' . $rowNumber, $Total);
                //->setCellValue('G' . $rowNumber, number_format($totalComm,2));
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
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$rowNumber.':E'.$rowNumber)->applyFromArray($headerStyleNew);
        $sheet->getStyle('A'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('B'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('C'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('D'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getStyle('E'.$rowNumber)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($rowNumber)->setRowHeight(30);
        
        // Set specific width for columns (optional)
        // $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        // $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        // $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        // $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        
        // Reduce font size for all cells in the worksheet
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10); // Adjust the font size as needed
    }
    
}


$rowNumber++;
$rowNumber++;
if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Amount.')
        //->setCellValue('C'.$rowNumber, number_format(floatval($Total),2));
        ->setCellValue('C'.$rowNumber,$Total);
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
    $spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(10);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
}

$rowNumber++;    
if ($_REQUEST['bank'] == 3 || $_REQUEST['bank'] == "") {
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Bank Comm.')
        // ->setCellValue('C'.$rowNumber, number_format(floatval($totalComm),2));
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
    $TotalAmt = $totalComm + $Total;
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
    $rowNumber++;    
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B'.$rowNumber, 'Total Amt.')
        // ->setCellValue('C'.$rowNumber, number_format(floatval($TotalAmt),2));
        ->setCellValue('C'.$rowNumber, $TotalAmt);
    $sheet->getStyle('C'.$rowNumber)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
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
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
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
    $spreadsheet->getActiveSheet()->getStyle('E'.$rowNumber.':E'.$rowNumber)->getFont()->setSize(10);
    $sheet->getRowDimension($rowNumber)->setRowHeight(20);
    
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
//$filename = 'companyBankReport_' . date('Y-m-d_H-i-s') . '.xlsx';
$filename = 'companyBankReport.xlsx';
$writer->save('ReportExcel/'.$filename);

// Redirect to the generated Excel file
header('Location: ReportExcel/' . $filename);

exit;
?>
