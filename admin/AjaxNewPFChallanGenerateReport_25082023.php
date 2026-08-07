<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";

    $salaryMonth = $_REQUEST['month'] . '/'. $_REQUEST['Year'];
    $filterstr = "SELECT employee.employeeId,max(skillrate) as skillrate,(max(skillrate)- min(skillrate)) as Diff,employee.employeecode,
        case when (max(skillrate)- min(skillrate)) > 0 then max(CONVERT(workingdays,UNSIGNED)) + 2 else max(CONVERT(workingdays,UNSIGNED)) end as 
        workingdays,employee.emp_name,employee.pfcode,employee.uan,employee.ecsno,employee.dateofbirth,max(salarydetails.basicwages) as 'basicAmount',max(salarydetails.total) as 'grossAmount',employee.employeeId, salarydetails.totalovertime,employee.dateofjoining
        ,strFatherName,adharcard FROM `salarydetails` inner join employee on salarydetails.emp_id=employee.employeeId where  salaryId in (select salarymasterId from salarymaster where   month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  
        salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0 and employee.employeecode=0 GROUP by employee.employeeId order by employeeId";
                                
    // $filterstr = "SELECT * FROM `employee`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode desc";
    $countstr = "SELECT count(*) as TotalRow FROM `salarydetails` inner join employee on salarydetails.emp_id=employee.employeeId where  salaryId in (select salarymasterId from salarymaster where  month='".$salaryMonth."' and isDelete='0' and  istatus='1') and  salarydetails.isDelete='0'  and salarydetails.istatus='1' and salarydetails.workingdays > 0  and employee.employeecode=0";

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
                                <th class="desktop">Name as per Aadhar</th>
                                <th class="desktop">Father Name</th>
                                <th class="desktop">Aadhar no.</th>
                                <th class="desktop">ESIC No.</th>
                                <th class="desktop">DOB</th>
                                <th class="desktop">PRESENT DAYS</th>
                                <th class="desktop">WAGES</th>
                                <th class="desktop">Difference  in ESIC</th>
                                <th class="desktop">Joining Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $iCounter = 1;
                            while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                                $dateofbirth = $rowfilter['dateofbirth']; //isset($row['dateofbirth']) && $row['dateofbirth'] != "" ? date('d-m-Y',strtotime(trim($row['dateofbirth']))) :"";
                                $dateofjoining =  $rowfilter['dateofjoining']; //isset($rowfilter['dateofjoining']) && $rowfilter['dateofjoining'] != "" ? date('d-m-Y',strtotime(trim($rowfilter['dateofjoining']))) :"";
                                // $DifferenceInESIC=0;
                                // if($rowfilter['Diff'] == 0){
                                //     $grossAmount = $rowfilter['grossAmount'];
                                //     $basicAmount = $rowfilter['basicAmount'];
                                //     $DifferenceInESIC = $grossAmount - $basicAmount;
                                //     //echo round($DifferenceInESIC);
                                // } else {
                                //     //echo $DifferenceInESIC;
                                // }
        
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
                                        <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strFatherName'])); ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['adharcard']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $rowfilter['ecsno']; ?> 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input "><?php echo $dateofbirth; //echo isset($rowfilter['dateofbirth']) && $rowfilter['dateofbirth'] != "" ? date('d-m-Y',strtotime($rowfilter['dateofbirth'])) : ""; ?> 
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
                                                // $DifferenceInESIC=0;
                                                // if($rowfilter['Diff'] == 0){
                                                //     $grossAmount = $rowfilter['grossAmount'];
                                                //     $basicAmount = $rowfilter['basicAmount'];
                                                //     $DifferenceInESIC = $grossAmount - $basicAmount;
                                                //     echo round($DifferenceInESIC);
                                                // } else {
                                                //     echo $DifferenceInESIC;
                                                // }
                                                $DifferenceInESIC=0;
                                                if($rowfilter['Diff'] == 0){
                                                    $grossAmount = $rowfilter['grossAmount'];
                                                    $basicAmount = $rowfilter['basicAmount'];
                                                    $DifferenceInESIC = $grossAmount - $basicAmount;
                                                    echo round($DifferenceInESIC);
                                                } else {
                                                    $workingdays = $workingdays - 2;
                                                    $sql = mysqli_query($dbconn,"SELECT max(CONVERT(salarydetails.basicwages,UNSIGNED)) as 'basicAmount',max(CONVERT(salarydetails.total,UNSIGNED)) as 'grossAmount' FROM `salarydetails` where  workingdays='".$workingdays."' and skillrate='".$rowfilter['skillrate']."' and emp_id='" . $rowfilter['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  month='" . $salaryMonth . "' and isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
                                                    if(mysqli_num_rows($sql) == 1){
                                                        $rowDays= mysqli_fetch_assoc($sql);
                                                        $grossAmount = $rowDays['grossAmount'];
                                                        $basicAmount = $rowDays['basicAmount'];
                                                        $DifferenceInESIC = $grossAmount - $basicAmount;
                                                        echo round($DifferenceInESIC);
                                                    } else {
                                                        echo $DifferenceInESIC;
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group form-md-line-input ">
                                            <?= $dateofjoining ?>
                                        </div>
                                    </td>
                                    <?php
                                    $iCounter++;
                                }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

    $CheckList = $_REQUEST['ID'];

    $dealer_res = mysqli_query($dbconn, 'delete from employee where employeeId in  ("' . $_REQUEST['ID'] . '")');
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
