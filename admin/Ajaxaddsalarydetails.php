<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');
include_once 'companyReportAdvance.php';

if ($_POST['action'] == 'ListUser') {
    //$where = "where 1=1";
    $whereA = " 1=1";

    if (isset($_POST['employeeId'])) {
        if ($_POST['employeeId'] != '') {
            $whereA.=" and employee.emp_name like '%" . $_POST['employeeId'] . "%' ";
            //$filterstr = "SELECT * FROM employee where isDelete=0 and istatus=1 and employee.emp_name  like '%" . $_POST['employeeId'] . "%'";
        }
    }
    $filterstr = "SELECT * FROM employee where isDelete=0 and istatus=1 and ".$whereA."";
//      $countstr = "SELECT count(*) as TotalRow,e1.salaryamt as SDisplayAmount from (SELECT * FROM `employee` where   employee.isDelete='0' and employee.istatus='1'
// and employee.employeeId in (select comskill.empid from comskill where comskill.companyid = '" . $_POST['companyId'] . "' )
// UNION
// select * from employee where  employee.isDelete='0' and employee.istatus='1' and employee.employeeId like '%" . $_POST['employeeId'] . "%'
//  and employee.employeeId not in (select comskill.empid from comskill where comskill.companyid = '" . $_POST['companyId'] . "'  ))as e1 left join (select * from salarydetails where " . $whereA . ") as e2 on e1.employeeId=e2.emp_id";
    $countstr = "SELECT count(*) as TotalRow FROM employee where isDelete=0 and istatus=1 and ".$whereA."";
    
    $resrowcount = mysqli_query($dbconn, $countstr);
    $resrowc = mysqli_fetch_array($resrowcount);
    $totalrecord = $resrowc['TotalRow'];
    $per_page = $cateperpaging;
    $total_pages = ceil($totalrecord / $per_page);
    $page = $_REQUEST['Page'] - 1;
    $startpage = $page * $per_page;
    $show_page = $page + 1;

    $filterstr = $filterstr . " LIMIT $startpage, $per_page"; 
    $resultfilter = mysqli_query($dbconn, $filterstr);
    if (mysqli_num_rows($resultfilter) > 0) {
        $i = 1;
        ?>  
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>

        <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
            <input type="hidden" value="AddSalaryDetails" name="action" id="action">
            <table class="table table-bordered table-hover center table-responsive" width="100%" id="tableC">
                <thead class="tbg">
                    <tr>
                        <th class="all">Employee Name</th>
                        <!-- <th class="all">PF No</th> -->
                        <th class="all">Employee <br /> Code</th>
                        <th class="all">UAN</th>
                        <th class="all">Working Days</th>
                        <th class="all">Employee Skill</th>
                        <th class="all">Overtime Hours</th>
                        <th class="all">Overtime Rate</th>
                        <th class="all">Deduction if any</th>
                        <th class="all">DA</th>
                        <th class="all">HRA</th>
                        <th class="all">No of National Holiday</th>
                        <!-- <th class="all">Prof <br /> Tax</th> -->
                        <th class="all">Advance Paid By Bank</th>
                        <th class="all">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $salarymaster = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM salarymaster where salarymasterId='" . $_POST['salarymasterId'] . "' "));
                    $salaryAdvances = getCompanyReportAdvances($dbconn, $salarymaster['companymasterId'], $salarymaster['month']);
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        $advanceAmount = getEmployeeCompanyReportAdvance($salaryAdvances, $rowfilter['employeeId']);
                        if($rowfilter['isExitEmployee']==1){
                        ?>
                        <tr style="background: #ffc107!important;">
                        <?php } else { ?>
                        <tr>
                        <?php } ?>
                    <input type="hidden"  value="<?php echo $salarymaster['companymasterId'] ?>"  name="companyId" id="companyId"/>
                    <input type="hidden"  value="<?php echo $i ?>"  name="inc" id="inc"/>
                    <input type="hidden"  value="<?php echo $salarymaster['salarymasterId'] ?>"  name="salaryId" id="salaryId"/>
                    <input type="hidden"  value="<?php echo $salarymaster['salarypaiddate'] ?>"  name="salarypaiddate" id="salarypaiddate"/>
            <!--<input type="hidden"  value="<?php echo $rowfilter['SDisplayAmount'] ?>"  name="salaryamt[]" id="salaryamt"/>-->
                    <input type="hidden"  value="<?php echo $rowfilter['employeeId'] ?>"  name="emp_id_<?php echo $i ?>" id="emp_id_<?php echo $i ?>"/>
                    <input type="hidden"  class="form-control"  value="<?php echo $rowfilter['emp_name'] ?>"  name="Ename_<?php echo $i ?>" id="Ename_<?php echo $i ?>"/>
                    <td><?php echo ucwords(strtolower($rowfilter['emp_name'])); if(isset($rowfilter['strFatherName'])) { echo " - ".$rowfilter['strFatherName'];} ?> </td>
                    <!-- <td><?php echo $rowfilter['pfcode'] ?> </td> -->
                    <td><?php echo $rowfilter['employeecode'] ?> </td>
                    <td><?php echo $rowfilter['uan'] ?> </td>
                    <td>
                        <input type="text" class="form-control" <?= $rowfilter['isExitEmployee']==1 ? 'readonly' : ""; ?>  onkeypress="return isNumberKey(event)" name="workingdays_<?php echo $i ?>" id="workingdays_<?php echo $i ?>"/>
                    </td>
                    <td>
                        <select name="Skill_<?php echo $i ?>" id="Skill_<?php echo $i ?>" <?= $rowfilter['isExitEmployee']==1 ? 'disabled' : ""; ?> class="form-control" >
                            <?php
                            $companymaster = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM companymaster where companymasterId='" . $salarymaster['companymasterId'] . "' "));
                            ?>
                            <option value="" <?php
                            if ($companymaster['skill'] == '') {
                                echo 'selected';
                            }
                            ?>>Select Employee Skill</option>
                            <option value="HighlySkill" <?php
                            if ($companymaster['skill'] == 'highlyskilled') {
                                echo 'selected';
                            }
                            ?>>HighlySkill-<?php echo $companymaster['highlyskilled'] ?></option>

                            <option value="Skill" <?php
                            if ($companymaster['skill'] == 'Skill') {
                                echo 'selected';
                            }
                            ?>>Skill-<?php echo $companymaster['skil'] ?></option>
                            <option value="SemiSkill" <?php
                            if ($companymaster['skill'] == 'SemiSkill') {
                                echo 'selected';
                            }
                            ?>>SemiSkill -<?php echo $companymaster['semiskill'] ?></option>
                            <option value="UnSkill" <?php
                            if ($companymaster['skill'] == 'UnSkill') {
                                echo 'selected';
                            }
                            ?>>UnSkill -<?php echo $companymaster['unskill'] ?></option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="0" <?= $rowfilter['isExitEmployee']==1 ? 'readonly' : ""; ?>  name="othours_<?php echo $i ?>" id="othours_<?php echo $i ?>" onkeypress="return isNumberKey(event)" />
                    </td>
                    <td>
                        <select name="otrate_<?php echo $i ?>" id="otrate_<?php echo $i ?>" <?= $rowfilter['isExitEmployee']==1 ? 'disabled' : ""; ?> class="form-control" >
                            <option value="0">Select Employee Over Time Rate</option>
                            <option value="1">1</option>
                            <option value="1.5">1.5</option>
                            <option value="2">2</option>                            
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="0"   name="deductionifany_<?php echo $i ?>" <?= $rowfilter['isExitEmployee']==1 ? 'readonly' : ""; ?> id="deductionifany_<?php echo $i ?>" onkeypress="return isNumberKey(event)" />
                    </td>
                    <td>
                        <input class="form-control" name="da_<?php echo $i ?>" id="da_<?php echo $i ?>" <?= $rowfilter['isExitEmployee']==1 ? 'readonly' : ""; ?> onkeypress="return isNumberKey(event)">
                    </td>
                    <td>
                        <input class="form-control" name="hra_<?php echo $i ?>" id="hra_<?php echo $i ?>" <?= $rowfilter['isExitEmployee']==1 ? 'readonly' : ""; ?> onkeypress="return isNumberKey(event)">
                    </td>
                    <td>
                        <input class="form-control" name="national_holiday_payment_<?php echo $i ?>" <?= $rowfilter['isExitEmployee']==1 ? 'readonly' : ""; ?> id="national_holiday_payment_<?php echo $i ?>" onkeypress="return isNumberKey(event)">
                    </td>
                    <td>
                        <!-- <input type="number" min="0" step="0.01" class="form-control" value="<?php echo htmlspecialchars(number_format($advanceAmount, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" name="advance_<?php echo $i ?>" id="advance_<?php echo $i ?>" <?php echo ($advanceAmount > 0 || $rowfilter['isExitEmployee'] == 1) ? 'readonly' : ''; ?>> -->
                        <input type="number" min="0" <?php echo $advanceAmount > 0 ? 'max="' . htmlspecialchars(number_format($advanceAmount, 2, '.', ''), ENT_QUOTES, 'UTF-8') . '"' : ''; ?> step="0.01" class="form-control" value="<?php echo htmlspecialchars(number_format($advanceAmount, 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" name="advance_<?php echo $i ?>" id="advance_<?php echo $i ?>" <?php echo $rowfilter['isExitEmployee'] == 1 ? 'readonly' : ''; ?>>
                    </td>
                    <!-- <td>
                        <input class="form-control" name="pt_<?php echo $i ?>" id="pt_<?php echo $i ?>" onkeypress="return isNumberKey(event)">
                    </td> -->
                    <td>
                        <!--<input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">    -->
                        <?php if($rowfilter['isExitEmployee']==0){ ?>
                            <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">    
                        <?php } 
                        // else { ?>
                            <!--<a class="btn blue" onClick="activeExitEmployee('<?php echo $rowfilter['employeeId']; ?>');"  title="Active Exit Employee"><i class="fa fa-sign-in" aria-hidden="true"></i></a>-->
                        <?php // } ?>
                    </td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
                </tbody>
            </table>
        </form>
        <?php
    } else {
        ?>
        <div class="row">
            <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark">
                <div class="alert alert-info clearfix profile-information padding-all-10 margin-all-0 backgroundDark">
                    <h1 class="font-white text-center"> No Data Found ! </h1>
                </div>   
            </div>
        </div>
        <?php
    }
}

if ($_REQUEST['action'] == 'Delete') {
    $data = array(
        "isDelete" => '1',
        "strEntryDate" => date('d-m-Y H:i:s')
    );
    $where = ' where  	employeeId=' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
}

if($_REQUEST['action'] == "activeExitEmployee"){
    $employee = mysqli_fetch_assoc(mysqli_query($dbconn, 'select * from employee where employeeId="'. $_REQUEST['ID'] . '"'));
    $checkaddhar = mysqli_query($dbconn, 'select * from employee where adharcard="'. $employee['adharcard'] . '" and employeeId!="'. $_REQUEST['ID'] . '" ');
    if(mysqli_num_rows($checkaddhar) > 0){
        echo 1;
        exit;
    } else {
         $data = array(
            "isExitEmployee" => '0',
            "strExitDate" => null,
            "strIP" => $_SERVER['REMOTE_ADDR']
        );
        
        $where = ' where  employeeId =' . $_REQUEST['ID'];
        $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
        
        $activity_log = array(
            "action" => "Acive Employee from Exit Status",
            "iemployeeId" => $_REQUEST['ID'],
            "iEnterBy" => $_SESSION['AdminId'],
            "strEntryDate" => date('d-m-Y H:i:s'),
            "strIP" => $_SERVER['REMOTE_ADDR']
        );
        $dealer_res = $connect->insertrecord($dbconn, 'activity_log', $activity_log);
        echo 0;
        exit;
    }
}


if ($totalrecord > $per_page) {
    ?>
    <div class="row">
        <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark" style="text-align: center;">
            <div class="form-actions noborder">
                <?php
                echo '<div class="pagination">';
                if ($totalrecord > $per_page) {
                    echo paginate($reload = '', $show_page, $total_pages);
                }
                echo "</div>";
                ?>
            </div>
        </div>
    </div>
<?php } ?>								  
<script language=Javascript>

    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 46 || charCode > 57))
            return false;
        return true;
    }

    $('#frmparameter').submit(function (e) {
        var salaryId = $('#salaryId').val();
        e.preventDefault();
        var $form = $(this);
        $('#loading').css("display", "block");
        $.ajax({
            type: 'POST',
            url: '<?php echo $web_url; ?>admin/querydata.php',
            data: $('#frmparameter').serialize(),
            success: function (response) {
                //alert(response);
                //console.log(response);
                if (response == 1)
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Added Sucessfully.');
                    window.location.href = '<?php echo $web_url; ?>admin/AddSalaryDetails.php?token=' + salaryId;

                } else {
                    $('#loading').css("display", "none");
                    // alert(response);                                       
                    window.location.href = '<?php echo $web_url; ?>admin/AddSalaryDetails.php?token=' + salaryId;
                }
            }
        });
    });
    
    function activeExitEmployee(id){
        var errMsg = 'Are you sure to active exited employee?';
        
        if(id>0){
            if (confirm(errMsg)) {
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/Ajaxaddsalarydetails.php",
                    data: {action: "activeExitEmployee", ID: id},
                    success: function (msg) {
                        if(msg == 1){
                            alert('Oops, You have already active employee with same aadhar number');
                            $('#loading').css("display", "none");
                            PageLoadData(1);
                            //window.location.href = '';
                            return false;
                        } else {
                            alert('Active Employee Successfully');
                            $('#loading').css("display", "none");
                            PageLoadData(1);
                            return false;
                        }
                    },
                });
            }
        }
        return false;
    }
</script>



