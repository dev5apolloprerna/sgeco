<?php
ob_start();
include_once '../common.php';
$connect = new connect();

//ini_set('max_execution_time', '50000');
ini_set('memory_limit', '3G');

include 'IsLogin.php';

 $date1=$_REQUEST['date'];
//echo $date1;
//exit;
$date = explode('-', $date1);
$month = $date[1];
$day   = $date[0];
$years  = $date[2];
$yearWithTime = explode(' ', $years);
$year =$yearWithTime[0];
//$year  = '18';


$dir = "../Document/".$year."/".$month."/".$day."/".$_REQUEST['token']."/";

//$loanmore_data = mysql_fetch_assoc(mysql_query("select * from fieldexecutiveimage where applicationId=" . $_REQUEST['appID']));
$sql="SELECT firstName FROM `customerentry` where customerEntryId ='".$_REQUEST['token']."'";
$res=  mysqli_query($dbconn, $sql);
$row=  mysqli_fetch_array($res);
$name=$row['firstName'];
    //$dir = "../BankingDocument/". $_REQUEST['appID'] .'/Inputfile/' ;
//$dir = "../Document/" . $_REQUEST['appID'] . '/FEImages/';
$directory = $dir;
//create zip object

$zip = new ZipArchive();
$zip_name = $name. ".zip";
$zip->open($zip_name, ZipArchive::CREATE);
$files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($files as $file) {
    $path = $file->getRealPath();
    //check file permission
    if (fileperms($path) != "16895") 
    {
        if (!is_dir($path))
            $zip->addFromString(basename($path), file_get_contents($path));
    } else {
        
    }
}
$zip->close();
ob_end_clean();
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=\"" . $zip_name . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($zip_name));
readfile($zip_name);
unlink($zip_name);
?>