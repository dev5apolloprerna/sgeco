  <?php

  error_reporting(0);
  include('../config.php');
  include('IsLogin.php');
  ?>
  <?php




  $result = mysqli_query($dbconn, "select multiycompanysalarymaster.companysalarymasterId,GROUP_CONCAT(companymaster.companyname SEPARATOR ',') as Company from companymaster,multiycompanysalarymaster where companymaster.companymasterId
  	= multiycompanysalarymaster.companymasterId
  	and multiycompanysalarymaster.companysalarymasterId in 
  	(
  	select companysalarymaster.companysalarymasterId from companysalarymaster where companysalarymaster.month ='".$_GET['cId']."' 
  	) and multiycompanysalarymaster.isDelete=0
  	group by multiycompanysalarymaster.companysalarymasterId");


  $data = '<select class="form-control" name="companysalarymasterId" id="companysalarymasterId" required="">';

  $data.='<option value="">Select company</option>';


  while ($row = mysqli_fetch_array($result)) {
  	$data.='<option value=' . $row['companysalarymasterId'] . '>' . $row['Company'] . '</option>';
  }
  $data .='</select>';
  echo $data;
  ?>


