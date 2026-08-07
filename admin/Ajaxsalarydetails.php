<?php
error_reporting(0);
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');


if ($_POST['action'] == 'ListUser') {
    $where = "where 1=1 ";

    if (isset($_REQUEST['Search_Txt'])) {
        if ($_POST['Search_Txt'] != '') {

            //$where.=" and  name like '$_POST[Search_Txt]%'";
            $where .= " and emp_id in (SELECT employeeId FROM `employee` where emp_name like '".$_POST['Search_Txt']."%' and istatus=1 and isDelete=0)";
        }
    }
    
    if (isset($_REQUEST['companyId'])) {
        if ($_POST['companyId'] != '') {

            $where.=" and  companyId = '".$_POST['companyId']."' ";
        }
    }
    if (isset($_REQUEST['salaryId'])) {
        if ($_POST['salaryId'] != '') {

            $where.=" and  salaryId in (select salarymasterId from salarymaster where companymasterId='".$_POST['companyId']."' and month='".$_POST['salaryId']."' and isDelete='0' and  istatus='1')";
        }
    }

    $filterstr = "SELECT * FROM `salarydetails`  " . $where . "  and  companyId = '".$_POST['companyId']."' and isDelete='0'  and  istatus='1' order by  name ASC";
    $countstr = "SELECT count(*) as TotalRow FROM `salarydetails`  " . $where . " and  companyId = '".$_POST['companyId']."' and isDelete='0'  and  istatus='1' order by  name ASC";

    $resrowcount = mysqli_query($dbconn, $countstr);
    $resrowc = mysqli_fetch_array($resrowcount);
    $totalrecord = $resrowc['TotalRow'];
    $per_page = $cateperpaging;
    $total_pages = ceil($totalrecord / $per_page);
    $page = $_REQUEST['Page'] - 1;
    $startpage = $page * $per_page;
    $show_page = $page + 1;



    $filterstr = $filterstr . " LIMIT $startpage, $per_page";
// echo $filterstr;


    $resultfilter = mysqli_query($dbconn, $filterstr);
    if (mysqli_num_rows($resultfilter) > 0) {
        $i = 1;
        ?>  
        
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>

        <script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>



         <form name="frmparameter"  id="frmparameter" >
            <div class="row">
            <div class="f_delet_btn">
                    <input type="hidden" value="companydeletedata" name="action" id="action">
                    <input type="submit" name="deletedata" class="btn blue" value="Delete All">
            </div>
        </div>

        <div class="table-responsive table-responsive-new">
                    <table class="table table-striped table-bordered table-hover dt-responsive " width="100%"
                       id="empdata">
                <thead class="tbg">
                    <tr>
     
                             <th>
                                <div class="md-checkbox">
                                    <input type="checkbox"  onclick="javascript:CheckAll();" id="check_listall" class="md-check" value="">
                                    <label for="check_listall">
                                        <span></span>
                                        <span class="check"></span>
                                        <span class="box"></span>
                                    </label>
                                </div>
                            </th>
                        
                        <th class="desktop">Salary Month</th>
                        <th class="desktop">Employee Name</th>
            <!--                    <th class="all">Name</th>-->
                        <th class="desktop">Company Name</th>
            <!--                    desktop<th class="all">Working Hours</th>-->
                        <th class="desktop">Working Days</th>
                        <th class="desktop">skill Rate</th> 
                        <th class="desktop">Over Time Hours</th>
    
                        <th class="desktop">Over Time Rate</th>
                        <th class="desktop">Total</th>
                        <th class="desktop">E.S.I.</th>
                        <th class="desktop">P.F.</th>
                        <th class="desktop">P.T.</th>
                        <th class="desktop">DA</th>
                        <th class="desktop">HRA</th>
                        <th class="desktop">National Holiday Payment</th>
                        <th class="desktop">Net<br />Amount<br />Paid</th>
            <!--                    <th class="all">Salary Amt</th>-->
    
                        <th class="desktop">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                        ?>
                        <tr>
                             <td>
                                    <div class="md-checkbox">
                                        <!-- <input type="hidden" name="smsid[]" id="smsid<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['multicompanyid']; ?> "> -->
                                        <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['salarydetailsId']; ?> ">
                                        <label for="check_list<?php echo $i; ?>">
                                            <span></span>
                                            <span class="check"></span>
                                            <span class="box"></span></label>
                                    </div>
    
                                </td> 
    
                            <?php
                            $salary = "SELECT * FROM `salarymaster`  where  salarymasterId='" . $rowfilter['salaryId'] . "' order by  salarymasterId desc";
                            $bankfilter = mysqli_query($dbconn, $salary);
                            $salaryrowfilter = mysqli_fetch_array($bankfilter)
                            ?>
                            
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $salaryrowfilter['month']; ?> 
                                </div>
                            </td> 
                            <?php
                            $emp = "SELECT * FROM `employee`  where  employeeId='" . $rowfilter['emp_id'] . "' order by  employeeId desc";
                            $bankfilter = mysqli_query($dbconn, $emp);
                            $emprowfilter = mysqli_fetch_array($bankfilter)
                            ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($emprowfilter['emp_name']));  if(isset($emprowfilter['strFatherName'])) { echo " - ".$emprowfilter['strFatherName'];} ?> 
                                </div>
                            </td> 
                            <?php
                            $company = "SELECT * FROM `companymaster`  where  companymasterId='" . $rowfilter['companyId'] . "' order by  companymasterId desc";
                            $bankfilter = mysqli_query($dbconn, $company);
                            $companyrowfilter = mysqli_fetch_array($bankfilter)
                            ?>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($companyrowfilter['companyname'])); ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['workingdays']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['skillrate']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['othours']; ?> 
                                </div>
                            </td>
    
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['otrate']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['total']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['esi']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['pf']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['pt']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['da']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['hra']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo $rowfilter['national_holiday_payment']; ?> 
                                </div>
                            </td>
                            <td>
                                <div class="form-group form-md-line-input "><?php echo ceil($rowfilter['netamountpaid']); ?>
                                </div>
                            </td>
                            <td style="width: 20%">
                                <div class="form-group form-md-line-input">
                                    <a class="btn blue"   title="EDIT SALARY" onclick="window.open('<?php echo $web_url; ?>admin/Editsalarydetails.php?token=<?php echo $rowfilter['salarydetailsId']; ?>' , 'popUpWindow', 'height=500,width=1250,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');"><i class="fa fa-edit iconshowFirst"></i></i></a>
                                    <!--<a  class="btn blue" href="<?php // echo $web_url; ?>admin/Editsalarydetails.php?token=<?php // echo $rowfilter['salarydetailsId']; ?>" title="Edit"><i class="fa fa-edit iconshowFirst"></i></i></a>-->
                                    <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['salarydetailsId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
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

         <script>                       

                                         $(document).ready(function () {
        //              $('#defaultTextarea').characterCounter({alertclass: 'red'});
                                        $('#tableC').DataTable({
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
      

        <?php
    } else {
        ?>
        <div class="row">
            <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark">
                <div class="alert alert-info clearfix profile-information padding-all-10 margin-all-0 backgroundDark" style="width: 97%;margin: 0 auto;">
                    <h1 class="font-white text-center"> No Data Found ! </h1>
                </div>   
            </div>
        </div>
        <?php
    }
}

if ($_REQUEST['action'] == 'Delete') {
   
$CheckList =$_REQUEST['ID'];

        //echo 'delete from employee where employeeId in ('.implode("," ,  $_POST['check_list']).')';
$dealer_res =mysqli_query($dbconn,'delete from salarydetails where salarydetailsId in  ("'.$_REQUEST['ID'].'")');
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


