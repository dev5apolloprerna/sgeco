<?php

error_reporting(0);
include('../config.php');
include('IsLogin.php');
?>
<?php

if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";
    if (isset($_REQUEST['Search_Aadhar'])) {
        if ($_POST['Search_Aadhar'] != '') {
            $where.=" and  adharcard like '%" . $_POST['Search_Aadhar'] . "%' ";
            $result = mysqli_query($dbconn, "SELECT * FROM `employee` " . $where . " and isDelete='0'  order by employeecode desc");
            $row = mysqli_fetch_assoc($result);
            print_r(json_encode($row));
        }
    }
}
?>


