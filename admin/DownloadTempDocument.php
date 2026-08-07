<?php
ob_start();
include_once '../common.php';
$connect = new connect();

ini_set('max_execution_time', '50000');
ini_set('memory_limit', '3G');

include 'IsLogin.php';

$iTempEmpId=$_REQUEST['token'];
$filterstr = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT emp_name FROM `tempEmpolyeeMaster` where iTempEmpId=" . $_REQUEST['token'] . " "));

$dir = "./TempDocument/".$_REQUEST['token']."/";

$directory = $dir;
//create zip object
$zip = new ZipArchive();
$zip_name = $_REQUEST['token']."_". $filterstr['emp_name']. ".zip";
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