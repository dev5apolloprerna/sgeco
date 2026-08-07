<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";

    // if (isset($_REQUEST['month'])) {
    //     if ($_REQUEST['month'] != '') {
    //         $where.=" AND (MONTH(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['month']."' or MONTH(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['month']."' or MONTH(str_to_date(dateofjoining,'%d/%m/%Y'))='".$_REQUEST['month']."')";
    //         //$where.=" or MONTH(str_to_date(dateofjoining,'%d-%m-%y'))='".$_REQUEST['month']."'";
    //     }
    // }
    
    // if (isset($_REQUEST['Year'])) {
    //     if ($_REQUEST['Year'] != '') {
    //         $where.=" and (YEAR(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['Year']."' or YEAR(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['Year']."'  or YEAR(str_to_date(dateofjoining,'%d/%m/%Y'))='".$_REQUEST['Year']."')";
    //     }
    // }
    $salaryMonth = $_REQUEST['month'] . '/'. $_REQUEST['Year'];
    $filterstr = "SELECT employee.employeeId,max(skillrate) as skillrate,(max(skillrate)- min(skillrate)) as Diff,employee.employeecode,
                    case when (max(skillrate)- min(skillrate)) > 0 then max(CONVERT(workingdays,UNSIGNED)) + 2 else max(CONVERT(workingdays,UNSIGNED)) end as 
                    workingdays,employee.emp_name,employee.pfcode,employee.uan,employee.ecsno,employee.dateofbirth,max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount',employee.employeeId, salarydetails.totalovertime
                    FROM `salarydetails` inner join employee on salarydetails.emp_id=employee.employeeId where  salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0 and employee.employeecode !=0 
                    and employeecode not in (829,257,815,1063,256,84,2060,259,1131,1444,229,306,1834,1275,1967,1606,2305) and isPermanent=0
                    GROUP by employee.employeeId order by employee.employeecode asc";
                                
    // $filterstr = "SELECT * FROM `employee`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
    $countstr = "SELECT count(*) as TotalRow FROM `salarydetails` inner join employee on salarydetails.emp_id=employee.employeeId where  salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0  and employee.employeecode !=0 and employeecode not in (829,257,815,1063,256,84,2060,259,1131,1444,229,306,1834,1275,1967,1606,2305)";

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
        $i = 1;
        ?>  
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
            <form name="frmparameter"  id="frmparameter" >
                <div class="row">
                    <div class="f_delet_btn">
                    </div>
                </div>
                <div class="table-responsive table-responsive-new">
                    <table class="table table-striped table-bordered table-hover dt-responsive" width="100%"
                       id="empdata">
                        <thead class="tbg">
                            <tr>
                                <th class="desktop">SR. No.</th>
                                <th class="desktop">NAME</th>
                                <th class="desktop">P.F. A/c No.</th>
                                <th class="desktop">UAN NO</th>
                                <th class="desktop">ESIC NO</th>
                                <th class="desktop">D.O.B</th>
                                <th class="desktop">PRESENT DAYS</th>
                                <th class="desktop">WAGES</th>
                                <th class="desktop">Difference  in ESIC</th>
                                <th class="desktop">OT AMOUNT FOR ESIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $iCounter = 1;
                            $resfilter = mysqli_query($dbconn,"SELECT employee.employeeId,employee.employeecode,employee.emp_name,employee.pfcode,employee.uan,employee.ecsno,employee.dateofbirth,employee.employeeId FROM `employee` where employeecode in (829,257,815,1063,256,84,2060,259,1131,1444,229,306,1834,1275,1967,1606,2305) and isPermanent=1 and isDelete=0 and istatus=1 order by employeecode asc");
                            if (mysqli_num_rows($resfilter) > 0) {
                                while ($row = mysqli_fetch_array($resfilter)) { 
                                    $filterdetails = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate,(max(skillrate)- min(skillrate)) as Diff,
                                        case when (max(skillrate)- min(skillrate)) > 0 then max(CONVERT(workingdays,UNSIGNED)) + 2 else max(CONVERT(workingdays,UNSIGNED)) end as 
                                        workingdays,max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount',salarydetails.totalovertime
                                        FROM `salarydetails` where  salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0 and salarydetails.emp_id='".$row['employeeId']."' GROUP by salarydetails.emp_id");
                                    $workingdays = 0;
                                    $skillrate = 0;
                                    $DifferenceInESIC=0;
                                    $totalovertime = 0;
                                    if(mysqli_num_rows($filterdetails) == 1){
                                        $rowDetails = mysqli_fetch_array($filterdetails);
                                        $workingdays = $rowDetails['workingdays'];
                                        $skillrate = $rowDetails['skillrate'];
                                        
                                        if($rowDetails['Diff'] == 0){
                                            $grossAmount = $rowDetails['grossAmount'];
                                            $basicAmount = $rowDetails['basicAmount'];
                                            $DifferenceInESIC = $grossAmount - $basicAmount;
                                            //echo round($DifferenceInESIC);
                                        } else {
                                            $workingdays = $workingdays - 2;
                                            $sql = mysqli_query($dbconn,"SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$rowDetails['skillrate']."' and emp_id='" . $row['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
                                            if(mysqli_num_rows($sql) == 1){
                                                $rowDays= mysqli_fetch_assoc($sql);
                                                $grossAmount = $rowDays['grossAmount'];
                                                $basicAmount = $rowDays['basicAmount'];
                                                $DifferenceInESIC = $grossAmount - $basicAmount;
                                                //echo round($DifferenceInESIC);
                                            } else {
                                                //echo $DifferenceInESIC;
                                            }
                                        }
                                        $totalovertime = $rowDetails['totalovertime'];
                                    }  
                                    $salary = mysqli_fetch_assoc(mysqli_query($dbconn, "SELECT max(workingdays) as workingdays,max(netamountpaid) as netamountpaid FROM permanentemployeesalarydetails where salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and emp_id='".$row['employeeId']."' group by emp_id"));
                                    if(isset($skillrate) && $skillrate ==0){
                                        if(isset($salary['netamountpaid']) && $salary['netamountpaid'] != 0){
                                            $skillrate = $salary['netamountpaid'];
                                        }
                                    }
                                    if(isset($workingdays) && $workingdays == 0){
                                        if(isset($salary['workingdays']) && $salary['workingdays'] != ""){
                                            $workingdays=$salary['workingdays'];
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?php echo $iCounter; ?> 
                                            </div>
                                        </td> 
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($row['emp_name'])); ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $row['employeecode']; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $row['uan']; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $row['ecsno']; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $row['dateofbirth']; //echo isset($rowfilter['dateofbirth']) && $rowfilter['dateofbirth'] != "" ? date('d-m-Y',strtotime($rowfilter['dateofbirth'])) : ""; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?= isset($workingdays) ? $workingdays: 0; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?= isset($skillrate) && $skillrate != 0 ? $skillrate : ""; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?php
                                                    echo round($DifferenceInESIC);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?= $totalovertime; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    $iCounter++;
                                }
                            }
                            if (mysqli_num_rows($resultfilter) > 0) {
                                while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                                    // $sql = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate,workingdays FROM `salarydetails` where  emp_id='" . $rowfilter['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
                                    // $result = mysqli_fetch_assoc($sql);
                                    // $sql = mysqli_query($dbconn,"SELECT max(rate) as skillrate,workingdays FROM `multicompany` where  companysalarymasterId in ( SELECT companysalarymasterId FROM `companysalarymaster` where month='".$salaryMonth."' and istatus=1 and isDelete=0) and isDelete=0 and emp_id='".$rowfilter['']."' order by name asc ");
                                    // $result = mysqli_fetch_assoc($sql);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?php echo $iCounter; ?> 
                                            </div>
                                        </td> 
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['emp_name'])); ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $rowfilter['employeecode']; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $rowfilter['uan']; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $rowfilter['ecsno']; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofbirth']."<br />"; //echo isset($rowfilter['dateofbirth']) && $rowfilter['dateofbirth'] != "" ? date('d-m-Y',strtotime($rowfilter['dateofbirth'])) : ""; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?php
                                                    $workingdays = isset($rowfilter['workingdays']) ? $rowfilter['workingdays'] : "0"; ?>
                                                <?=  $workingdays ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?= isset($rowfilter['skillrate']) ? $rowfilter['skillrate'] : ""; ?> 
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?php
                                                    $DifferenceInESIC=0;
                                                    if($rowfilter['Diff'] == 0){
                                                        $grossAmount = $rowfilter['grossAmount'];
                                                        $basicAmount = $rowfilter['basicAmount'];
                                                        $DifferenceInESIC = $grossAmount - $basicAmount;
                                                        echo round($DifferenceInESIC);
                                                    } else {
                                                        //$workingdays = $workingdays - 2;
                                                        // echo "SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$rowfilter['skillrate']."' and emp_id='" . $rowfilter['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc";
                                                        $sql = mysqli_query($dbconn,"SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$rowfilter['skillrate']."' and emp_id='" . $rowfilter['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
                                                        if(mysqli_num_rows($sql) == 1){
                                                            $rowDays= mysqli_fetch_assoc($sql);
                                                            $grossAmount = $rowDays['grossAmount'];
                                                            $basicAmount = $rowDays['basicAmount'];
                                                            $DifferenceInESIC = $grossAmount - $basicAmount;
                                                            echo round($DifferenceInESIC);
                                                        } else {
                                                            echo 0;
                                                        }
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group form-md-line-input ">
                                                <?= isset($rowfilter['totalovertime']) ? $rowfilter['totalovertime'] : 0; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                    $iCounter++;
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </form>
            
            
        <?php
    // } else {
        ?>
        <!--<div class="row">-->
        <!--    <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark">-->
        <!--        <div class="alert alert-info clearfix profile-information padding-all-10 margin-all-0 backgroundDark">-->
        <!--            <h1 class="font-white text-center"> No Data Found ! </h1>-->
        <!--        </div>   -->
        <!--    </div>-->
        <!--</div>-->
        <?php
    // }
}

if ($_REQUEST['action'] == 'Delete') {

    $CheckList = $_REQUEST['ID'];

    $dealer_res = mysqli_query($dbconn, 'delete from employee where employeeId in  ("' . $_REQUEST['ID'] . '")');
}
?>
<?php if ($totalrecord > $per_page) { ?>
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

<?php

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
?>								  
<script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>
<script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>
<script>
                                $(document).ready(function () {
                                    $('#tableC').DataTable({
                                    });
                                });
</script>




<script>

    $(document).ready(function () {
        //              $('#defaultTextarea').characterCounter({alertclass: 'red'});
        $('#empdata').DataTable({
        });
        $('#frmparameter').submit(function (e) {

            e.preventDefault();
            var $form = $(this);
            $('#loading').css("display", "block");
            $.ajax({
                type: 'POST',
                url: 'querydata.php',
                data: $('#frmparameter').serialize(),
                success: function (response) {


                    if (response == 1)
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Deleted Sucessfully.');
                        window.location.href = '';
                    } else
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Not Deleted  Please Try Again.');
                        window.location.href = '';
                    }
                }

            });
        });
    });


    function CheckAll()
    {

        if ($('#check_listall').is(":checked"))
        {
            // alert('cheked');
            $('input[type=checkbox]').each(function () {
                $(this).prop('checked', true);
            });
        } else
        {
            //alert('cheked fail');
            $('input[type=checkbox]').each(function () {
                $(this).prop('checked', false);
            });
        }
    }


</script>
