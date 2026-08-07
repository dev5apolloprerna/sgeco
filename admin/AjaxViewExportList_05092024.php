<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1";

    if (isset($_REQUEST['Search_Txt'])) {
        if ($_POST['Search_Txt'] != '') {

            $where.=" and  emp_name like '%" . $_POST['Search_Txt'] . "%' ";
        }
    }
    
    if (isset($_REQUEST['Search_Aadhar'])) {
        if ($_POST['Search_Aadhar'] != '') {
            $where.=" and  adharcard like '%" . $_POST['Search_Aadhar'] . "%' ";
        }
    }
    
    // if (isset($_REQUEST['token'])) {
    //     if ($_POST['token'] != '') {
    //         $where.=" and iType= '".$_POST['token']."' ";
    //     }
    // }

    $filterstr = "SELECT iExportEmpId,pfcode,employeeId,emp_name,employeecode,ecsno,uan,strFatherName,dateofbirth,dateofjoining,strPermanentAddress,address,pancard,adharcard,bankid,accountno,ifsccode,mno,strMaritalStatus,strNomineeName,strNomineeRelation,strNomineeAdharNo,strFamilyDetails,strRelation,strExitDate FROM `employee` inner join exportemployeelist on exportemployeelist.iEmpoyeeId=employee.employeeId  " . $where . " and iType= '1'  and employee.isDelete='0' and  employee.istatus='1'
                  UNION ALL
                  SELECT iExportEmpId,iTempEmpId as employeeId,pfcode,emp_name,employeecode,ecsno,uan,strFatherName,dateofbirth,dateofjoining,strPermanentAddress,address,pancard,adharcard,bankid,accountno,ifsccode,mno,strMaritalStatus,strNomineeName,strNomineeRelation,strNomineeAdharNo,strFamilyDetails,strRelation,'' as strExitDate FROM `tempEmpolyeeMaster` inner join exportemployeelist on tempEmpolyeeMaster.iTempEmpId=exportemployeelist.iEmpoyeeId  " . $where . " and iType= '2' and tempEmpolyeeMaster.isDelete='0' and  tempEmpolyeeMaster.istatus='1'";
                  
    $countstr = "SELECT count(*) as TotalRow FROM `employee` inner join exportemployeelist on exportemployeelist.iEmpoyeeId=employee.employeeId " . $where . " and iType= '1' and employee.isDelete='0' and  employee.istatus='1'
                 UNION ALL
                 SELECT count(*) as TotalRow FROM `tempEmpolyeeMaster` inner join exportemployeelist on tempEmpolyeeMaster.iTempEmpId=exportemployeelist.iEmpoyeeId " . $where . " and iType= '2' and tempEmpolyeeMaster.isDelete='0' and  tempEmpolyeeMaster.istatus='1'";

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
                        <input type="hidden" value="clearemployeeexportdata" name="action" id="action">
                        <input type="submit" name="deletedata" class="btn blue" value="Clear All">
                        <input type="hidden" value="<?= $_POST['token']; ?>" name="iType" id="iType">
                    </div>
                    <!--<div class="f_delet_btn">-->
                        
                    <!--</div>-->
                </div>
                <div class="table-responsive table-responsive-new">
                    <table class="table table-striped table-bordered table-hover dt-responsive" width="100%"
                       id="empdata">
                    <thead class="tbg">
                        <tr>
                            <th class="desktop">PF No.</th>
                            <th class="desktop">Employee Code</th>
                            <th class="desktop">ESIC No.</th>
                            <th class="desktop">UAN No.</th>
                            <th class="desktop">Name As per Aadhar</th>
                            <th class="desktop">Father Name</th>
                            <th class="desktop">DOB</th>
                            <th class="desktop">DOJ</th>
                            <th class="desktop">Permanent Address</th>
                            <th class="desktop">Present Address</th>
                            <th class="desktop">Pan No.</th>
                            <th class="desktop">Aadhar No.</th>
                            <th class="desktop">Bank Name</th>
                            <th class="desktop">Account No.</th>
                            <th class="desktop">IFSC Code</th>
                            <th class="desktop">Mobile No.</th>
                            <th class="desktop">Marital Status</th>
                            <th class="desktop">Nominee Name</th>
                            <th class="desktop">Nominee Relation</th>
                            <th class="desktop">Nominee Adhar No.</th>
                            <th class="desktop">Family Details</th>
                            <th class="desktop">Relation</th>
                            <th class="desktop">Date of Exit</th>
                            <th class="desktop">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                            ?>
                            <tr>

                                <!--<td>-->
                                <!--    <div class="md-checkbox">-->
                                <!--        <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['employeeId']; ?> ">-->
                                <!--        <label for="check_list<?php echo $i; ?>">-->
                                <!--            <span></span>-->
                                <!--            <span class="check"></span>-->
                                <!--            <span class="box"></span></label>-->
                                <!--    </div>-->
                                <!--</td> -->
                                <td>
                                    <div class="form-group form-md-line-input ">
                                        <?php //echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];} ?> 
                                        <?php echo $rowfilter['pfcode']; ?> 
                                    </div>
                                </td> 
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['employeecode']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['ecsno']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['uan']; ?> 
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
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofbirth']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofjoining']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strPermanentAddress'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['address'])); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['pancard']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['adharcard']; ?> 
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
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['accountno']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['mno']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($rowfilter['strMaritalStatus'])); ?> 
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
                                    <div class="form-group form-md-line-input "><?php echo $strFamilyDetails; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $strRelation; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?= (isset($rowfilter['strExitDate']) && $rowfilter['strExitDate'] != "") ?  date('d-m-Y',strtotime($rowfilter['strExitDate'])) : ""; ?> 
                                    </div>
                                </td>
                                <td style="width: 20%">
                                    <div class="form-group form-md-line-input">
                                        <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['iExportEmpId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                    </div>
                                </td>

                                <?php
                                $i++;
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

    $dealer_res = mysqli_query($dbconn, 'delete from exportemployeelist where iExportEmpId in  ("' . $_REQUEST['ID'] . '")');
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
                url: 'android_querydata.php',
                data: $('#frmparameter').serialize(),
                success: function (response) {


                    if (response == 1)
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Clear All Data Sucessfully.');
                        window.location.href = '';
                    } else
                    {
                        $('#loading').css("display", "none");
                        $("#Btnmybtn").attr('disabled', 'disabled');
                        alert('Not Cleared  Please Try Again.');
                        window.location.href = '';
                    }
                }

            });
        });
    });
    
    function employeeaddtoexportlist(){
        $('#loading').css("display", "block");
        
        var check_list = new Array();
        //Reference the CheckBoxes and insert the checked CheckBox value in Array.
        $("#frmparameter input[type=checkbox]:checked").each(function () {
            check_list.push(this.value);
        });
        
        $.ajax({
            type: 'POST',
            url: 'android_querydata.php',
            data: {
                check_list : check_list,
                action : "employeeaddtoexportlist",
                strType : "EmployeeMaster",
                iType : 1
            },
            success: function (response) {
                if (response == 1)
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Added Sucessfully.');
                    window.location.href = '';
                } else
                {
                    $('#loading').css("display", "none");
                    $("#Btnmybtn").attr('disabled', 'disabled');
                    alert('Not Added  Please Try Again.');
                    window.location.href = '';
                }
            }

        });
    }

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

    function ViewExportList(){
        window.location.href = 'ViewExportList.php';
    }

</script>
