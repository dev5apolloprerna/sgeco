<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    //$where = "where 1=1";
    $whereA = " 1=1";

    if (isset($_POST['employeeId'])) {
        if ($_POST['employeeId'] != '') {
            $whereA.=" and employee.emp_name like '%" . $_POST['employeeId'] . "%' ";
            //$filterstr = "SELECT * FROM employee where isDelete=0 and istatus=1 and employee.emp_name  like '%" . $_POST['employeeId'] . "%'";
        }
    }
    $filterstr = "SELECT * FROM employee  where isDelete=0 and istatus=1 and ".$whereA." and isPermanent=1";
//      $countstr = "SELECT count(*) as TotalRow,e1.salaryamt as SDisplayAmount from (SELECT * FROM `employee` where   employee.isDelete='0' and employee.istatus='1'
// and employee.employeeId in (select comskill.empid from comskill where comskill.companyid = '" . $_POST['companyId'] . "' )
// UNION
// select * from employee where  employee.isDelete='0' and employee.istatus='1' and employee.employeeId like '%" . $_POST['employeeId'] . "%'
//  and employee.employeeId not in (select comskill.empid from comskill where comskill.companyid = '" . $_POST['companyId'] . "'  ))as e1 left join (select * from salarydetails where " . $whereA . ") as e2 on e1.employeeId=e2.emp_id";
    $countstr = "SELECT count(*) as TotalRow FROM employee where isDelete=0 and istatus=1 and ".$whereA." and isPermanent=1";

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
            <input type="hidden" value="AddPermanentSalaryDetails" name="action" id="action">
            <table class="table table-bordered table-hover center table-responsive" width="100%" id="tableC">
                <thead class="tbg">
                    <tr>
                        <th class="all">Employee Name</th>
                        <th class="all">Employee <br /> Code</th>
                        <th class="all">UAN</th>
                        <th class="all">Working Days</th>
                        <th class="all">Salary</th> 
                        <th class="all">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $salarymaster = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT * FROM salarymaster where salarymasterId='" . $_POST['salarymasterId'] . "' "));
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        $salary = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT max(workingdays) as workingdays,max(netamountpaid) as netamountpaid FROM permanentemployeesalarydetails where salaryId='" . $_POST['salarymasterId'] . "' and emp_id='".$rowfilter['employeeId']."' group by emp_id"));
                        ?>
                        <tr>
                    <input type="hidden"  value="<?php echo $salarymaster['companymasterId'] ?>"  name="companyId" id="companyId"/>
                    <input type="hidden"  value="<?php echo $i ?>"  name="inc" id="inc"/>
                    <input type="hidden"  value="<?php echo $salarymaster['salarymasterId'] ?>"  name="salaryId" id="salaryId"/>
                    <input type="hidden"  value="<?php echo $salarymaster['salarypaiddate'] ?>"  name="salarypaiddate" id="salarypaiddate"/>
                    <!--<input type="hidden"  value="<?php echo $rowfilter['SDisplayAmount'] ?>"  name="salaryamt[]" id="salaryamt"/>-->
                    <input type="hidden"  value="<?php echo $rowfilter['employeeId'] ?>"  name="emp_id_<?php echo $i ?>" id="emp_id_<?php echo $i ?>"/>
                    <input type="hidden"  class="form-control"  value="<?php echo $rowfilter['emp_name'] ?>"  name="Ename_<?php echo $i ?>" id="Ename_<?php echo $i ?>"/>
                    <td><?php echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];} ?> </td>
                    <!-- <td><?php echo $rowfilter['pfcode'] ?> </td> -->
                    <td><?php echo $rowfilter['employeecode'] ?> </td>
                    <td><?php echo $rowfilter['uan'] ?> </td>
                    <td>
                        <input type="text" class="form-control"  onkeypress="return isNumberKey(event)" value="<?= $salary['workingdays'] ? $salary['workingdays'] : ""; ?>" name="workingdays_<?php echo $i ?>" id="workingdays_<?php echo $i ?>"/>
                    </td>
                    <td>
                        <input class="form-control" name="salary_<?php echo $i ?>" id="salary_<?php echo $i ?>" value="<?= $salary['netamountpaid'] ? $salary['netamountpaid'] : ""; ?>" onkeypress="return isNumberKey(event)">
                    </td>
                    <td>
                        <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">    
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
<SCRIPT language=Javascript>

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
                console.log(response);
                if (response == 1)
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Added Sucessfully.');
                    //window.location.href = '<?php echo $web_url; ?>admin/AddPermanentEmployeeSalaryDetails.php?token=' + salaryId;

                } else {
                    $('#loading').css("display", "none");
                    // alert(response);                                       
                    //window.location.href = '<?php echo $web_url; ?>admin/AddPermanentEmployeeSalaryDetails.php?token=' + salaryId;
                }
            }
        });
    });
</SCRIPT>



