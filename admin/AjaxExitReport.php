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
    //and  istatus='1'
    $filterstr = "SELECT * FROM `employee` " . $where . " and isDelete='0' and isExitEmployee=1  order by employeecode desc";
    $countstr = "SELECT count(*) as TotalRow FROM `employee` " . $where . " and isDelete='0' and isExitEmployee=1";

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
        <!--<link href="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.css" rel="stylesheet" type="text/css" />-->
            <!--<form name="frmparameter"  id="frmparameter" >
                <div class="row">
                    <div class="f_delet_btn">
                        <input type="hidden" value="employeedeletedata" name="action" id="action">
                        <input type="submit" name="deletedata" class="btn blue" value="Delete All">
                        
                        <input type="button" name="deletedata" class="btn blue" onclick="employeeaddtoexportlist();" value="Add To Export List">
                        <input type="button" class="btn blue" onclick="ViewExportList();" style="float: right;" value="Export List">
                    </div>
                </div>-->
                <div class="table-responsive table-responsive-new">
                    <table class="table table-striped table-bordered table-hover dt-responsive" width="100%"
                       id="empdata">
                    <thead class="tbg">
                        <tr>

                            <!--<th class="desktop">
                                <div class="md-checkbox">
                                    <input type="checkbox"  onclick="javascript:CheckAll();" id="check_listall" class="md-check" value="">
                                    <label for="check_listall">
                                        <span></span>
                                        <span class="check"></span>
                                        <span class="box"></span>
                                    </label>
                                </div>
                            </th>-->
                            
                            <!--<th class="desktop">Employee Name</th>-->
                            <!--<th class="desktop">Designation</th>-->
                            <!--<th class="desktop">Bank Name</th>-->
                            <!-- <th class="desktop">Mobile No</th> -->
                            <!--<th class="desktop">ESIC No</th>-->
                            <!--<th class="desktop">PF Code</th>-->
                            <!--<th class="desktop">Employee Code</th>-->
                            <!--<th class="desktop">UAN</th>-->
                            <!--<th class="desktop">Account NO</th>-->
                            <!--<th class="desktop">IFSC Code</th>-->
                            <!--<th class="desktop">Date Of Birth</th>-->
                            <!--<th class="desktop">Date Of Joining</th>-->
                            <!--<th class="desktop">Address</th>-->
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
                            <!--<th class="desktop">Other Documents</th>-->
                            <th class="desktop">Bank Name</th>
                            <th class="desktop">Account No.</th>
                            <th class="desktop">IFSC Code</th>
                            <th class="desktop">Mobile No.</th>
                            <th class="desktop">Marital Status</th>
                            <th class="desktop">Nominee Name</th>
                            <th class="desktop">Nominee Relation</th>
                            <th class="desktop">Nominee Adhar No.</th>
                            
                            <th class="desktop">Emergency Contact No</th>
                            <th class="desktop">Qualification</th>
                            <th class="desktop">Experience</th>
                            <th class="desktop">Married Date</th>
                            <th class="desktop">Son </th>
                            <th class="desktop">Doughter </th>
                            
                            <th class="desktop">Family Details</th>
                            <th class="desktop">Relation</th>
                            <th class="desktop">Created By</th>
                            <th class="desktop">Updated By</th>
                            <th class="desktop">Status</th>
                            <th class="desktop">Date of Exit</th>
                            <!--<th class="desktop">Created By</th>-->
                            <!--<th class="desktop">Updated By</th>-->
                            <!--<th class="desktop">Moved By</th>-->
                            <!--<th class="desktop">Action</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($rowfilter = mysqli_fetch_array($resultfilter)) {
                            ?>
                            <tr>

                                <!--<td>
                                    <div class="md-checkbox">
                                        <input type="checkbox" name="check_list[]" id="check_list<?php echo $i; ?>" class="md-check" value="<?php echo $rowfilter['employeeId']; ?> ">
                                        <label for="check_list<?php echo $i; ?>">
                                            <span></span>
                                            <span class="check"></span>
                                            <span class="box"></span></label>
                                    </div>

                                </td> -->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input ">-->
                                <!--        <?php echo $rowfilter['emp_name']; if(isset($rowfilter['emp_other_info'])) { echo " - ".$rowfilter['emp_other_info'];} ?> -->
                                <!--    </div>-->
                                <!--</td> -->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['designation']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <?php
                                /*$bank = "SELECT * FROM `bankmaster`  where  bankmasterId='" . $rowfilter['bankid'] . "' order by  bankmasterId desc";
                                $bankfilter = mysqli_query($dbconn, $bank);
                                $bankrowfilter = mysqli_fetch_array($bankfilter) */
                                ?>
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $bankrowfilter['bankname']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['ecsno']; ?> -->
                                <!--    </div>-->
                                <!--</td>                                                                      -->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['pfcode']; ?> -->
                                <!--    </div>-->
                                <!--</td>                                                -->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['employeecode']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['uan']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['accountno']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['ifsccode']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofbirth']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['dateofjoining']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['address']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
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
                                <!--<td>-->
                                <!--    <div class="form-group form-md-line-input "><?php echo $rowfilter['passport']; ?> -->
                                <!--    </div>-->
                                <!--</td>-->
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
                                
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['strEmergencyContactNo']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['strQualification']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['strExperience']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['strMarriedDate']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['iSon']; ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo $rowfilter['iDoughter']; ?> 
                                    </div>
                                </td>
                                
                                <?php 
                                    $filterFamilyRelation = mysqli_query($dbconn,"SELECT * FROM `EmployeeFamilyDetails` where iEmpId='".$rowfilter['employeeId']."' order by iEmpFamilyDetailsId asc limit 1");
                                    $strFamilyDetails = "";
                                    $strRelation = "";
                                    if(mysqli_num_rows($filterFamilyRelation) == 1){
                                        $rowFamilyRelation = mysqli_fetch_assoc($filterFamilyRelation);
                                        $filterRelationStr = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT * FROM `relation` where isDelete=0 and iRelation='".$rowFamilyRelation['iRelation']."'"));
                                        $strFamilyDetails = $rowFamilyRelation['strFamilyDetails'];
                                        $strRelation = $filterRelationStr['strRelation'];
                                    }
                                ?>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($strFamilyDetails)); ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($strRelation)); ?> 
                                    </div>
                                </td>
                                <td>
                                    <?php $CreatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT strUserName FROM `androidusermaster` where iAndroidUserId='".$rowfilter['iCreatedBy']."' and iStatus=1 and isDelete=0")); ?>
                                    <div class="form-group form-md-line-input "><?php echo $CreatedBy['strUserName']; ?> 
                                    </div>
                                </td>
                                
                                <td>
                                    <?php 
                                    if($rowfilter['iMovedBy'] == ""){
                                        $UpdatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT strUserName FROM `androidusermaster` where iAndroidUserId='".$rowfilter['iUpdatedBy']."' and iStatus=1 and isDelete=0")); 
                                    } else {
                                        $UpdatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT username AS strUserName FROM `admin` where id='".$rowfilter['iMovedBy']."' and istatus=1 and isDelete=0"));
                                    }
                                    ?>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($UpdatedBy['strUserName'])); ?> 
                                    </div>
                                </td>
                                <!--<td>-->
                                    <?php 
                                        //  $MovedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT username AS strUserName FROM `admin` where id='".$rowfilter['iMovedBy']."' and istatus=1 and isDelete=0"));
                                    ?>
                                <!--    <div class="form-group form-md-line-input "><?php //echo $MovedBy['strUserName']; ?> 
                                    </div>-->
                                <!--</td>-->
                                <td>
                                    <div class="form-group form-md-line-input ">
                                        <?php 
                                        if($rowfilter['istatus'] == 0){
                                            echo "Block";
                                        } else if($rowfilter['isExitEmployee'] == 1){
                                            echo "Exit";
                                        } else {
                                            echo "Active";   
                                        }
                                        ?> 
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group form-md-line-input "><?= (isset($rowfilter['strExitDate']) && $rowfilter['strExitDate'] != "") ?  date('d-m-Y',strtotime($rowfilter['strExitDate'])) : ""; ?> 
                                    </div>
                                </td>
                                <!--<td>
                                    <?php $CreatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT strUserName FROM `androidusermaster` where iAndroidUserId='".$rowfilter['iCreatedBy']."' and iStatus=1 and isDelete=0")); ?>
                                    <div class="form-group form-md-line-input "><?php echo $CreatedBy['strUserName']; ?> 
                                    </div>
                                </td>
                                
                                <td>
                                    <?php 
                                    if($rowfilter['iMovedBy'] == ""){
                                        $UpdatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT strUserName FROM `androidusermaster` where iAndroidUserId='".$rowfilter['iUpdatedBy']."' and iStatus=1 and isDelete=0")); 
                                    } else {
                                        $UpdatedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT username AS strUserName FROM `admin` where id='".$rowfilter['iMovedBy']."' and istatus=1 and isDelete=0"));
                                    }
                                    ?>
                                    <div class="form-group form-md-line-input "><?php echo ucwords(strtolower($UpdatedBy['strUserName'])); ?> 
                                    </div>
                                </td>-->
                                <!--<td>-->
                                    <?php 
                                        //  $MovedBy = mysqli_fetch_assoc(mysqli_query($dbconn,"SELECT username AS strUserName FROM `admin` where id='".$rowfilter['iMovedBy']."' and istatus=1 and isDelete=0"));
                                    ?>
                                <!--    <div class="form-group form-md-line-input "><?php //echo $MovedBy['strUserName']; ?> 
                                    </div>-->
                                <!--</td>-->
                                
                                <!-- <td style="width: 20%">
                                    <div class="form-group form-md-line-input">
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/EditEmployee.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Edit"><i class="fa fa-edit iconshowFirst"></i></i></a>
                                        <a  class="btn blue" onClick="javascript: return deletedata('Delete', '<?php echo $rowfilter['employeeId']; ?>');"   title="Delete"><i class="fa fa-trash-o iconshowFirst"></i></a>
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/ViewDocument.php?token=<?php echo $rowfilter['employeeId']; ?>" title="View Document"><i class="fa fa-eye"></i></i></a>
                                        
                                        <?php if($rowfilter['istatus'] == 1){ ?>
                                            <a  class="btn blue" onClick="javascript: return deletedata('Block', '<?php echo $rowfilter['employeeId']; ?>');"   title="Block"><i class="fa fa-ban"></i></i></a>
                                        <?php } else { ?>
                                            <a  class="btn blue" onClick="javascript: return deletedata('Unblock', '<?php echo $rowfilter['employeeId']; ?>');"   title="Unblock"><i class="fa fa-unlock"></i></i></a>
                                        <?php } ?>
                                        <?php  if($rowfilter['isExitEmployee'] == 0) { ?>
                                            <a class="btn blue" data-toggle="modal" data-target="#exampleModal" href="#" onClick="exitModelGet('<?php echo $rowfilter['employeeId']; ?>');"  title="Exit Employee"><i class="fa fa-sign-out" aria-hidden="true"></i></a>
                                        <?php } else { ?>
                                            <a class="btn blue" onClick="activeExitEmployee('<?php echo $rowfilter['employeeId']; ?>');"  title="Active Exit Employee"><i class="fa fa-sign-in" aria-hidden="true"></i></a>
                                        <?php } ?>
                                        
                                        <a class="btn blue" data-toggle="modal" data-target="#updatebankModal" href="#" onClick="updateSetBank('<?php echo $rowfilter['employeeId']; ?>');"  title="Update Bank"><i class="fa fa-university" aria-hidden="true"></i></a>
                                        <a  class="btn blue" href="<?php echo $web_url; ?>admin/EmployeeDetails.php?token=<?php echo $rowfilter['employeeId']; ?>" title="Employee Details"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
                                    </div>
                                </td> -->

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

    $dealer_res = mysqli_query($dbconn, 'delete from employee where employeeId in  ("' . $_REQUEST['ID'] . '")');
}

if ($_REQUEST['action'] == 'Block') {
    $CheckList = $_REQUEST['ID'];
    $data = array(
        "istatus" => '0',
        "strEntryDate" => date('d-m-Y H:i:s'),
        "strIP" => $_SERVER['REMOTE_ADDR']
    );
    $where = ' where  employeeId =' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
}

if ($_REQUEST['action'] == 'Unblock') {
    $CheckList = $_REQUEST['ID'];
    $data = array(
        "istatus" => '1',
        "strEntryDate" => date('d-m-Y H:i:s'),
        "strIP" => $_SERVER['REMOTE_ADDR']
    );
    $where = ' where  employeeId =' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
}

if ($_REQUEST['action'] == 'ExitEmployee') {
    $CheckList = $_REQUEST['ID'];
    $data = array(
        "isExitEmployee" => '1',
        "strExitDate" => $_REQUEST['strExitDate'],
        "strIP" => $_SERVER['REMOTE_ADDR']
    );
    
    $where = ' where  employeeId =' . $_REQUEST['ID'];
    $dealer_res = $connect->updaterecord($dbconn, 'employee', $data, $where);
    
    $activity_log = array(
        "action" => "Employee Exit",
        "iemployeeId" => $_REQUEST['ID'],
        "iEnterBy" => $_SESSION['AdminId'],
        "strEntryDate" => date('d-m-Y H:i:s'),
        "strIP" => $_SERVER['REMOTE_ADDR']
    );
    $dealer_res = $connect->insertrecord($dbconn, 'activity_log', $activity_log);
    
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
<!--<script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/datatables.js" type="text/javascript"></script>-->
<!--<script src="<?php echo $web_url; ?>admin/assets/global/plugins/datatables/table-datatables-responsive.js" type="text/javascript"></script>-->
<script>
                                // $(document).ready(function () {
                                //     $('#tableC').DataTable({
                                //     });
                                // });
</script>




<script>

    $(document).ready(function () {
        //              $('#defaultTextarea').characterCounter({alertclass: 'red'});
        // $('#empdata').DataTable({
        // });
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
        window.location.href = 'ViewExportList.php?token=1';
    }

</script>
<script>
    $(document).ready(function () {
        new DataTable('#empdata', {
            paging: false, // Disable pagination
            dom: 'Bfrtip', // Necessary for the Buttons extension
            buttons: [
                
                'colvis', // Column visibility button
                {
                    extend: 'excel',
                    text: 'Export to visible Excel',
                    exportOptions: {
                        columns: ':visible' // This will only export the visible columns
                    }
                }
            ]
        });
    });
</script>