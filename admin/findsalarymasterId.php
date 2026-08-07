  <?php

error_reporting(0);
include('../config.php');
include('IsLogin.php');
?>
<?php

$cId = intval($_GET['cId']);

$result = mysqli_query($dbconn, "select * from salarymaster  where  istatus='1' and isDelete='0' and companymasterId=" . $cId . " ");
$data = '<select name="salarymasterId" id="salarymasterId" class="form-control" ">
<option value="">Select salary Month</option>';
while ($row = mysqli_fetch_array($result)) {
    $data.='<option value=' . $row['salarymasterId'] . '>' . $row['month'] . '</option>';
}
$data .='</select>';
echo $data;
?>


