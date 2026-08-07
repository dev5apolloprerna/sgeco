<?php
error_reporting(E_ALL);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";

    if (isset($_REQUEST['month'])) {
        if ($_REQUEST['month'] != '') {
            $where.=" AND (MONTH(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['month']."' or MONTH(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['month']."' or MONTH(str_to_date(dateofjoining,'%d/%m/%Y'))='".$_REQUEST['month']."')";
            //$where.=" or MONTH(str_to_date(dateofjoining,'%d-%m-%y'))='".$_REQUEST['month']."'";
        }
    }
    
    if (isset($_REQUEST['Year'])) {
        if ($_REQUEST['Year'] != '') {
            $where.=" and (YEAR(str_to_date(dateofjoining,'%m/%y'))='".$_REQUEST['Year']."' or YEAR(str_to_date(dateofjoining,'%d-%m-%Y'))='".$_REQUEST['Year']."'  or YEAR(str_to_date(dateofjoining,'%d/%m/%Y'))='".$_REQUEST['Year']."')";
        }
    }
    $salaryMonth = $_REQUEST['month'] . '/'. $_REQUEST['Year'];

    $filterstr = "SELECT * FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0'  and  istatus='1' order by employeecode DESC";
                // UNION All
                // select * from employee where employee.ecsno='' and isDelete='0' and istatus='1'  order by employeecode DESC";
    $countstr = "SELECT count(*) as TotalRow FROM `tempEmpolyeeMaster`  " . $where . " and isDelete='0' and  istatus='1'";
                // UNION All
                // select count(*) as TotalRow from employee where employee.ecsno='' and isDelete='0' and istatus='1'";
    
    $resrowcount = mysqli_query($dbconn, $countstr);
    $resrowc = mysqli_fetch_array($resrowcount);
    $totalrecord = $resrowc['TotalRow'];
    // $TotalRow = 0;
    // while($resrowc = mysqli_fetch_array($resrowcount)){
    //     $TotalRow += $resrowc['TotalRow'];
    // }
    
    // $totalrecord = $TotalRow;
    
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
                            <th class="desktop">Name As Per Aadhar</th>
                            <th class="desktop">MARRIED/ UNMARRIED</th>
                            <th class="desktop">FATHER NAME</th>
                            <th class="desktop">HUSBAND NAME</th>
                            <th class="desktop">DOB</th>
                            <th class="desktop">DOJ</th>
                            <th class="desktop">Present Address</th>
                            <th class="desktop">Permanat Address</th>
                            <!--<th class="desktop">Pan No.</th>-->
                            <th class="desktop">Aadhar No.</th>
                            <!--<th class="desktop">Other Documents</th>-->
                            <th class="desktop">Nominee name.</th>
                            <th class="desktop">Relation</th>
                            <th class="desktop">Nominee Aadhar</th>
                            <th class="desktop">Aadhar No.</th>
                            <th class="desktop">Family Member Name</th>
                            <th class="desktop">Relation</th>
                            <th class="desktop">Mobile No.</th>
                            <th class="desktop">Bank Name</th>
                            <th class="desktop">Account No.</th>
                            <th class="desktop">IFSC Code</th>
                            <th class="desktop">Rate of Wage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $iCounter = 1;
                        while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                            
                            $sql = mysqli_query($dbconn,"SELECT max(skillrate) as skillrate FROM `salarydetails` where  emp_id='" . $rowfilter['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc");
                            $result = mysqli_fetch_assoc($sql);
                            
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
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strMaritalStatus'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strFatherName'])); ?> 
                                    </div>
                                </td>
                                <td>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo date('d-m-Y',strtotime($rowfilter['dateofbirth'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo date('d-m-Y',strtotime($rowfilter['dateofjoining'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['address'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strPermanentAddress'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['adharcard']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strNomineeName'])); ?> 
                                    </div>
                                </td>                                                
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strNomineeRelation'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['strNomineeAdharNo']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['adharcard']; ?> 
                                    </div>
                                </td>
                                <?php 
                                    $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `tempFamilyDetails` where iTempEmpId='".$rowfilter['iTempEmpId']."' order by iTempFamilyDetailsId asc limit 1");
                                    $strFamilyDetails = "";
                                    $strRelation = "";
                                    if(mysqli_num_rows($filterFamilyRelation) == 1){
                                        $rowFamilyRelation = mysqli_fetch_assoc($filterFamilyRelation);
                                        $filterRelationStr = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0 and iRelation='".$rowFamilyRelation['iRelation']."'"));
                                        $strFamilyDetails = $rowFamilyRelation['strFamilyDetails'];
                                        $strRelation = $filterRelationStr['strRelation'];
                                    } ?>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($strFamilyDetails)); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($strRelation)); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['mno']; ?> 
                                    </div>
                                </td>
                                <?php
                                $bank = "SELECT * FROM `bankmaster`  where  bankmasterId='" . $rowfilter['bankid'] . "' order by  bankmasterId desc";
                                $bankfilter = mysqli_query($dbconn, $bank);
                                $bankrowfilter = mysqli_fetch_array($bankfilter)
                                ?>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($bankrowfilter['bankname'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo str_replace('.','',str_replace('A/C','',str_replace('A/C. ','',$rowfilter['accountno']))); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?= isset($result['skillrate']) ? $result['skillrate']  : ""; ?> 
                                    <?php //echo "SELECT max(skillrate) as skillrate FROM `salarydetails` where  emp_id='" . $rowfilter['employeeId'] . "' and salaryId in (select salarymasterId from salarymaster where  isDelete='0' and  istatus='1') and  isDelete='0'  and  istatus='1' and workingdays > 0 order by salarydetailsId asc"; ?>
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
