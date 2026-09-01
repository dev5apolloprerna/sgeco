<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');

if ($_REQUEST['action'] == 'ListUser') {
    $result = mysqli_query($dbconn, "SELECT * FROM `multicompany` where  multicompanyid='" . $_REQUEST['multicompanyid'] . "' order by companysalarymasterId desc");

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $savedOvertimeRate = '0';
        if ((float) $row['rate'] > 0 && (float) $row['othours'] > 0) {
            foreach (array('1|8', '1|12', '1.5|8', '1.5|12', '2|8', '2|12') as $option) {
                list($multiplier, $hours) = explode('|', $option);
                $calculatedAmount = (((float) $row['rate'] / (float) $hours) * (float) $multiplier) * (float) $row['othours'];
                if (abs((float) $row['otamt'] - $calculatedAmount) < 0.01) {
                    $savedOvertimeRate = $option;
                    break;
                }
            }
        }
    } else {
        echo 'somthig going worng! try again';
        exit();
    }
    ?>
    <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css" />
    <div class="row">
        <div class="style-msg  errormsg col-md-12">
            <div class="alert alert-danger" id="editErrorDIV" style="display: none;"></div>
        </div>
    </div>
    <div class="modal-body">
        <div class="form-group m-form__group">

            <label for="form_control_1"> Name</label>
            <input type="text"  id="name" readonly="" name="name" class="form-control"  value="<?php echo ucwords(strtolower($row['name'])) ?>" placeholder="Enter the  Name" required>

            <label for="form_control_1">Rate</label>
            <input type="text"  id="rate"  name="rate" class="form-control"  value="<?php echo $row['rate'] ?>" placeholder="Enter the Rate" required>

            <label for="form_control_1"> Workin Days</label>
            <input type="text"  id="workingdays"  name="workingdays" class="form-control"  value="<?php echo $row['workingdays'] ?>" placeholder="Enter the Workin Days" required>

            <label for="form_control_1"> OT Hours</label>
            <input type="text"  id="othours"  name="othours" class="form-control"  value="<?php echo $row['othours'] ?>" placeholder="Enter the OT Hours" required>

            <label for="form_control_1"> Over Time Rate</label>
            <select name="otrate" id="otrate" class="form-control" required>
                <option value="0">Select Employee Over Time Rate</option>
                <?php foreach (array('1|8' => '1 for 8 hours', '1|12' => '1 for 12 hours', '1.5|8' => '1.5 for 8 hours', '1.5|12' => '1.5 for 12 hours', '2|8' => '2 for 8 hours', '2|12' => '2 for 12 hours') as $value => $label) { ?>
                    <option value="<?php echo $value; ?>" <?php echo $savedOvertimeRate === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php } ?>                       
            </select>

            <label for="form_control_1">ADV ONE</label>
            <input type="text"  id="adv"  name="adv" class="form-control"  value="<?php echo $row['adv'] ?>" placeholder="Enter the ADV" required>

            <label for="form_control_1">ADV ONE PAY</label>
            <input type="text"  id="adv_one_paid"  name="adv_one_paid" class="form-control"  value="<?php echo $row['adv_one_paid'] ?>" placeholder="Enter the ADV ONE" required>

            <label for="form_control_1">ADV TWO</label>
            <input type="text"  id="adv_two"  name="adv_two" class="form-control"  value="<?php echo $row['adv_two'] ?>" placeholder="Enter the ADV TWO" required>

            <label for="form_control_1">ADV TWO PAY</label>
            <input type="text"  id="adv_two_paid"  name="adv_two_paid" class="form-control"  value="<?php echo $row['adv_two_paid'] ?>" placeholder="Enter the ADV TWO" required>

            <label for="advance_paid_by_bank">ADVANCE PAID BY BANK</label>
            <input type="number" min="0" step="0.01" id="advance_paid_by_bank" name="advance_paid_by_bank" class="form-control" value="<?php echo htmlspecialchars($row['advance_paid_by_bank'], ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="pf_amount">PF AMOUNT</label>
            <input type="number" min="0" step="0.01" id="pf_amount" name="pf_amount" class="form-control" value="<?php echo htmlspecialchars($row['pf_amount'], ENT_QUOTES, 'UTF-8'); ?>" readonly required>

            <label for="esic_amount">ESIC AMOUNT</label>
            <input type="number" min="0" step="0.01" id="esic_amount" name="esic_amount" class="form-control" value="<?php echo htmlspecialchars($row['esic_amount'], ENT_QUOTES, 'UTF-8'); ?>" readonly required>
            
            <label for="form_control_1">F.A</label>
            <input type="text"  id="Fa"  name="Fa" class="form-control"  value="<?php echo $row['Fa'] ?>" placeholder="Enter the F.A" required>

            <label for="form_control_1">T.A</label>
            <input type="text"  id="Ta"  name="Ta" class="form-control"  value="<?php echo $row['Ta'] ?>" placeholder="Enter the T.A" required>

            <label for="form_control_1">Date</label>
            <input type="text"  id="date"  name="date" class="form-control"  value="<?php echo $row['date'] ?>" placeholder="Enter the Date" required>

            <label for="form_control_1">PAY IN CASH</label>
            <select class="form-control" name="pay_cash" id="pay_cash">
                <option value="0" <?php
                if ($row['pay_cash'] == 0) {
                    echo "selected";
                } else {
                    echo "";
                }
                ?> >NO</option>
                <option value="1" <?php
                if ($row['pay_cash'] == 1) {
                    echo "selected";
                } else {
                    echo "";
                }
                ?> >YES</option>
            </select>
            <!--<input type="text"  id="pay_cash"  name="Ta" class="form-control"  value="<?php echo $row['Ta'] ?>" placeholder="Enter the T.A" required>-->
        </div>
    </div>
    <?php
}
?>
<script src="<?php echo $web_url; ?>admin/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#date").datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            defaultDate: "now"
        });
    });
</script>