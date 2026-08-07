<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

if ($_REQUEST['action'] == 'ListUser') {

    $result = mysqli_query($dbconn,"SELECT * FROM `studentregistration` WHERE `iStudentId`='" . $_REQUEST['iStudentId'] . "' and isDelete=0");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
    } else {
        echo 'somthig going worng! try again';
        exit();
    }
    ?>
    <div class="row">
        <div class="style-msg  errormsg col-md-12">
            <div class="alert alert-danger" id="editErrorDIV" style="display: none;"></div>
        </div>
    </div>
    <div class="modal-body">
        <div class="form-group m-form__group">
            <label class="col-form-label ">
                Branch *
            </label>
            <?php
            $query = "SELECT * FROM `branchmaster`  where isDelete='0'  and  istatus='1' order by  iBranchId asc";
            $result = mysqli_query($dbconn, $query) or die(mysqli_error());
            echo '<select class="form-control" name="iBranchId" id="iBranchId" required="">';
            echo "<option value='' >Select Branch</option>";
            while ($rowfilter = mysqli_fetch_array($result)) {
                if ($rowfilter['iBranchId'] == $row['ibranchId']) {
                    ?>
                    <option value="<?php echo $rowfilter['iBranchId']; ?>" selected>  <?php echo $rowfilter['strBranchName']; ?>  </option>
                    <?php
                } else {
                    echo "<option value='" . $row['ibranchId'] . "'>" . $rowfilter['strBranchName'] . "</option>";
                }
            }
            echo "</select>";
            ?>
            <label class="col-form-label ">
                Student Name *
            </label>
            <input type="text" required="" class="form-control m-input--solid" value="<?php echo $row['strStudentName'] ?>" name="strStudentName" id="strStudentName" placeholder="Enter Student Name">
            <label class="col-form-label ">
                Father Name *
            </label>
            <input type="text" class="form-control m-input--solid" value="<?php echo $row['strFatherName'] ?>" name="strFatherName" id="strFatherName" placeholder="Enter Father Name">
            <label class="col-form-label ">
                Student Mobile *
            </label>
            <input placeholder="Enter Student Mobile" id="strStudentMobile" value="<?php echo $row['strStudentMobile'] ?>" name="strStudentMobile" pattern="[0-9]{10}" class="form-control m-input--solid">
            <label class="col-form-label ">
                Address *
            </label>
            <textarea type="text" required="" class="form-control m-input--solid"  name="strAddress" id="strAddress" placeholder="Enter Address" rows="3"><?php echo $row['strAddress'] ?></textarea>
            
        </div>
    </div>



    <?php
}
?>