<?php
error_reporting(0);
include('../config.php');
include('IsLogin.php');
?>
<?php



$salarymonthYear=($_GET['salarymonthYear']);
$result=mysqli_fetch_array(mysqli_query($dbconn,"SELECT * FROM `salarymaster`  where isDelete='0' and month='".$salarymonthYear."' and companymasterId='".$companyId."' and  istatus='1'  GROUP BY salarymaster.month order by  month asc"));
/*$data='<select name="City" id="City" class="form-control"  required>
<option value="">Select City</option>';
 while($row=mysqli_fetch_array($result)) { 
	$data.='<option value='.$row['cityid'].'>'.$row['name'].'</option>';
}*/
$data =result['salarymasterId'];
echo $data;
?>
