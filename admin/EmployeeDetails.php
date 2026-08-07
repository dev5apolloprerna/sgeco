<?php

ob_start();
ob_clean();
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
//include('common.php');
//$connect = new connect();
include('../config.php');
$where = "where 1=1";

$filterstr = "SELECT * FROM `employee` " . $where . " and employeeId='".$_REQUEST['token']."' and isDelete='0'  order by employeecode desc";

$rowfilter = mysqli_fetch_assoc(mysqli_query($dbconn, $filterstr));

$Total = array(0, 0);
$bank = "SELECT * FROM `bankmaster`  where  bankmasterId='" . $rowfilter['bankid'] . "' order by  bankmasterId desc";
$bankfilter = mysqli_query($dbconn, $bank);
$bankrowfilter = mysqli_fetch_array($bankfilter);

$mailFormat_main = file_get_contents("employee-detail.html");

$mailFormat_main = str_replace("#employeecode#", $rowfilter['employeecode'], $mailFormat_main);
$mailFormat_main = str_replace("#pfno#", $rowfilter['pfcode'], $mailFormat_main);
$mailFormat_main = str_replace("#ecsno#", $rowfilter['ecsno'], $mailFormat_main);
$mailFormat_main = str_replace("#emp_name#", ucwords(strtolower($rowfilter['emp_name'])), $mailFormat_main);

$mailFormat_main = str_replace("#strFatherName#", ucwords(strtolower($rowfilter['strFatherName'])), $mailFormat_main);
$mailFormat_main = str_replace("#dateofbirth#", $rowfilter['dateofbirth'], $mailFormat_main);
$mailFormat_main = str_replace("#dateofjoining#", $rowfilter['dateofjoining'], $mailFormat_main);
$mailFormat_main = str_replace("#Designation#", $rowfilter['designation'], $mailFormat_main);

$address = $rowfilter['address'];
// Split the address into parts
$part1 = "";
$part2 = "";
if(isset($address) && $address != ""){
    $parts = explode(",", $address);
    $part1 = $parts[0]; // Ugamanapura
    //$part2 = $parts[1] . ', ' . $parts[2]; // Dhuvaran Power Station, Dhuvaran
    if(!empty($parts[1])){
        $part1 .= ', ' . $parts[1];
    }
    //$part2 = $parts[1]; 
    if(!empty($parts[2])){
        $part1 .= ', ' . $parts[2];
    }
    if(!empty($parts[3])){
        $part2 =  $parts[3];
    }
    if(!empty($parts[4])){
        $part2 .= ', ' .$parts[4]; // Anand, Gujarat - 388610
    }
    if(!empty($parts[5])){
        $part2 .= ', ' . $parts[5];
    }
}


$mailFormat_main = str_replace("#LocalAddressOne#", $part1, $mailFormat_main);
$mailFormat_main = str_replace("#LocalAddressTwo#", $part2, $mailFormat_main);
//$mailFormat_main = str_replace("#LocalAddressThree#", $part3, $mailFormat_main);

$PermanentAddress = $rowfilter['strPermanentAddress'];
// Split the address into parts
$part_1 = ""; // Ugamanapura
$part_2 = "";
if(isset($PermanentAddress) && $PermanentAddress != ""){
    $newparts = explode(", ", $PermanentAddress);
    $part_1 = $newparts[0]; // Ugamanapura
    //$part2 = $parts[1] . ', ' . $parts[2]; // Dhuvaran Power Station, Dhuvaran
    //$part_2 = $newparts[1]; 
    if(!empty($newparts[1])){
        $part_1 .= ', ' . $newparts[1];
    }
    
    if(!empty($newparts[2])){
        $part_1 .= ', ' . $newparts[2];
    }
    if(!empty($newparts[3])){
        $part_2 = $newparts[3];
    }
    if(!empty($newparts[4])){
        $part_2 .= ', ' .$newparts[4]; // Anand, Gujarat - 388610
    }
    if(!empty($newparts[5])){
        $part_2 .= ', ' . $newparts[5];
    }
}



// Assign the parts to different variables
/*$part_1 = $newparts[0]; // Ugamanapura
$part_2 = $newparts[1] . ', ' . $newparts[2]; // Dhuvaran Power Station, Dhuvaran
$part_3 = $newparts[3];
if(!empty($newparts[4])){
    $part_3 .=   ', ' . $newparts[4]; // Anand, Gujarat - 388610
}
if(!empty($newparts[5])){
    $part_3 .= ', ' . $newparts[5];
}*/
$mailFormat_main = str_replace("#PermanentAddressOne#", $part_1, $mailFormat_main);
$mailFormat_main = str_replace("#PermanentAddressTwo#", $part_2, $mailFormat_main);
//$mailFormat_main = str_replace("#PermanentAddressThree#", $part_3, $mailFormat_main);

$mailFormat_main = str_replace("#strNomineeName#", ucwords(strtolower($rowfilter['strNomineeName'])), $mailFormat_main);
$mailFormat_main = str_replace("#strNomineeRelation#", ucwords(strtolower($rowfilter['strNomineeRelation'])), $mailFormat_main);

$mailFormat_main = str_replace("#pancard#", $rowfilter['pancard'], $mailFormat_main);
$mailFormat_main = str_replace("#electioncard#", $rowfilter['electioncard'], $mailFormat_main);
$mailFormat_main = str_replace("#DrivingLicence#", $rowfilter['drivinglicense'], $mailFormat_main);
$mailFormat_main = str_replace("#Aadharcard#", $rowfilter['adharcard'], $mailFormat_main);
$mailFormat_main = str_replace("#Passport#", $rowfilter['passport'], $mailFormat_main);
$mailFormat_main = str_replace("#MobileNo#", $rowfilter['mno'], $mailFormat_main);
$mailFormat_main = str_replace("#BankAcNo#", $rowfilter['accountno'], $mailFormat_main);
$mailFormat_main = str_replace("#BankBranch#", ucwords(strtolower($bankrowfilter['bankname'])), $mailFormat_main);

$mailFormat_main = str_replace("#IFSCCode#", $rowfilter['ifsccode'], $mailFormat_main);

$mailFormat_main = str_replace("#UANNo#", $rowfilter['uan'], $mailFormat_main);

$mailFormat_main = str_replace("#EducationalQualification#", $rowfilter['strQualification'], $mailFormat_main);

$mailFormat_main = str_replace("#EmergencyContactNo#", $rowfilter['strEmergencyContactNo'], $mailFormat_main);
$mailFormat_main = str_replace("#Experience#", $rowfilter['strExperience'], $mailFormat_main);
$mailFormat_main = str_replace("#MarriedDate#", $rowfilter['strMarriedDate'], $mailFormat_main);
$mailFormat_main = str_replace("#Son#", $rowfilter['iSon'], $mailFormat_main);
$mailFormat_main = str_replace("#Doughter#", $rowfilter['iDoughter'], $mailFormat_main);

$tick = '<img src="https://sgeco.in/admin/images/icon_tick.png" />';
$Married = "";
$Unmarried = "";
if($rowfilter['strMaritalStatus'] == "Married")
{
    $Married = $tick;    
}
if ($rowfilter['strMaritalStatus'] == "Unmarried"){
    $Unmarried= $tick; 
}

$mailFormat_main = str_replace("#Married#", $Married, $mailFormat_main);
$mailFormat_main = str_replace("#Unmarried#", $Unmarried, $mailFormat_main);

$mailFormat_main = str_replace("#EmailId#", '', $mailFormat_main);
$mailFormat_main = str_replace("#ExitDate#", $rowfilter['strExitDate'], $mailFormat_main);

$mailFormat_main = str_replace("#strMaritalStatus#", $rowfilter['strMaritalStatus'], $mailFormat_main);
$mailFormat_main = str_replace("#banktr#", ucfirst(urldecode($mailFormat_rows)), $mailFormat_main);
// create new PDF document

$querystr = mysqli_query($dbconn, "SELECT * FROM `EmployeeFamilyDetails` where 1=1 and iEmpId='".$_REQUEST['token']."' ");
$html="";
$iCounter = 1;
if(mysqli_num_rows($querystr) > 0){
    while($res = mysqli_fetch_assoc($querystr)){
        $strRelation = "";
        if($res['iRelation'] > 0){
            $relationQry = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM `relation` where 1=1 and iRelation='".$res['iRelation']."' and isDelete=0"));
            $strRelation = $relationQry['strRelation'];
        }
        $html.='<tr>
                    <td>'.$iCounter.'</td>
                    <td>'.$res['strFamilyDetails'].'</td>
                    <td></td>
                    <td>'.$strRelation.'</td>
                </tr>';
        $iCounter++;
    }    
}
// $html="";
if($iCounter <= 6){
    $jCounter = $iCounter;
    for($i=6; $i>=$iCounter; $i--){
        
        $html.='<tr>
                    <td>'. $jCounter .'
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                </tr>';
            $jCounter++;
    }
}

$mailFormat_main = str_replace("#tableDetails#", $html, $mailFormat_main);

$pdf = new TCPDF(P, PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);

// set default font subsetting mode
//$pdf->setFontSubsetting(true);
// set margins
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set default header data
//$pdf->SetHeaderData('logo.png', '40', '', '', array(0, 64, 255), array(0, 64, 128));
//$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, 105, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 0, '', true);
//     $pdf->SetFont('helvetica', '', 8);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

$pdf->writeHTML($mailFormat_main, true, false, false, false, '');

//$pdf->writeHTML($html, true, 0);
//$pdf->writeHTML($html, true, 0);
ob_end_clean();

$pdf->Output('BankPayment.pdf', 'I');

?>