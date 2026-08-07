<?php
ob_start();
error_reporting(E_ALL);
include_once '../common.php';
$connect = new connect();
include('IsLogin.php');
?>
<!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $ProjectName; ?> |Company</title>
        <?php include_once './include.php'; ?>
    </head>
    <body class="page-container-bg-solid page-boxed">
        <?php include_once './header.php'; ?>
        <div style="display: none; z-index: 10060;" id="loading">
            <img id="loading-image" src="<?php echo $web_url; ?>admin/images/loader1.gif">
        </div>
        <div class="page-container">        
            <div class="page-content-wrapper">

                <div class="page-content">
                    <div class="container">
                        <ul class="page-breadcrumb breadcrumb">
                            <li>
                                <a href="<?php echo $web_url; ?>admin/index.php">Home</a>
                                <i class="fa fa-circle"></i>
                            </li>

                            <li>
                                <span>Company</span>
                            </li>
                        </ul>

                        <div class="page-content-inner">

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase" id="editCompany">Add Company</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <form  role="form"  method="POST"  action="" name="frmparameter"  id="frmparameter" enctype="multipart/form-data">
                                                <input type="hidden" value="AddCompany" name="action" id="action">
                                                <div class="form-body">

                                                    <div class="form-group">
                                                        <label for="form_control_1">Company Name</label>
                                                        <input type="text"  id="Company"  name="Company" class="form-control" placeholder="Enter the Company Name" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Company Highly Skill Rate</label>
                                                        <input type="text"  id="highlyskilled"  name="highlyskilled" class="form-control" placeholder="Enter the Company Highly skill Rate" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Company Skill Rate</label>
                                                        <input type="text"  id="Skil"  name="Skil" class="form-control" placeholder="Enter the Company Skil Rate" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Company UnSkill Rate</label>
                                                        <input type="text"  id="unSkil"  name="unSkil" class="form-control" placeholder="Enter the Company UnSkil Rate" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Company SemiSkill Rate</label>
                                                        <input type="text"  id="SemiSkil"  name="SemiSkil" class="form-control" placeholder="Enter the Company SemiSkil Rate" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="form_control_1">ESI(YES/NO)</label>
                                                        <select name="ESI" id="ESI" class="form-control" required="" >
                                                            <option value="">Select ESI</option>
                                                            <option value="YES">YES</option>
                                                            <option value="NO">NO</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Professional Tax(YES/NO)</label>
                                                        <select name="pf" id="pf" class="form-control" required="" >
                                                            <option value="">Select Professional Tax</option>
                                                            <option value="YES">YES</option>
                                                            <option value="NO">NO</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Medical Allowance</label>
                                                        <select name="MedicalAllowance" id="MedicalAllowance" class="form-control" required="" onchange='checkvalue(this.value)'>
                                                            <option value="">Select Medical Allowance</option>
                                                            <option value="YES">YES</option>
                                                            <option value="NO">NO</option>
                                                        </select>
                                                    </div>
                                                    <input name="MedicalAllowancePer" id="MedicalAllowancePer"  placeholder="Enter Medical Allowance Per" class="form-control"  type="text" style='display:none'>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Bonus (YES/NO)</label>
                                                        <select name="strBonus" id="strBonus" class="form-control" required="" >
                                                            <option value="">Select Bonus </option>
                                                            <option value="YES">YES</option>
                                                            <option value="NO">NO</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="form_control_1">Leave (YES/NO)</label>
                                                        <select name="strLeave" id="strLeave" class="form-control" required="" >
                                                            <option value="">Select Leave </option>
                                                            <option value="YES">YES</option>
                                                            <option value="NO">NO</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="form_control_1">Daily Working Hours</label>
                                                        <input type="number" step="0.01" id="iDailyWorkingRate"  name="iDailyWorkingRate" class="form-control" placeholder="Enter the Company Daily Working Hours" required>
                                                    </div>
                                                </div>
                
                                                <div class="form-actions noborder">
                                                    <input class="btn blue margin-top-20" type="submit" id="Btnmybtn"  value="Submit" name="submit">      
                                                    <button type="button" class="btn blue margin-top-20" onClick="checkclose();">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="portlet light ">
                                        <div class="portlet-title">
                                            <div class="caption font-red-sunglo">
                                                <i class="icon-settings font-red-sunglo"></i>
                                                <span class="caption-subject bold uppercase">List of Company</span>
                                            </div>
                                        </div>
                                        <div class="portlet-body form">
                                            <div class="row">
                                                <div class="col-md-6 pull-right">
                                                    <form  role="form"  method="POST"  action="" name="frmSearch"  id="frmSearch" enctype="multipart/form-data">
                                                        <div class="form-group col-md-9">
                                                            <input type="text" value="" name="Search_Txt" class="form-control" id="Search_Txt" placeholder="Search Company Name " required/>
                                                        </div>
                                                        <div class="form-actions  col-md-3">
                                                            <a href="#" class="btn blue pull-right" id="clickbutton" onclick="PageLoadData(1);">Search</a>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div id="PlaceCompanyDataHere" class="table-responsive">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once './footer.php'; ?>
        <script type="text/javascript">
            function checkclose() {
                window.location.href = '';
            }
            function checkvalue(val)
            {
                if (val === "YES")
                    document.getElementById('MedicalAllowancePer').style.display = 'block';
                else
                    document.getElementById('MedicalAllowancePer').style.display = 'none';
            }
            $('#frmparameter').submit(function (e) {
                e.preventDefault();
                var $form = $(this);
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: $('#frmparameter').serialize(),
                    success: function (response) {
                      
                        console.log(response);
                        if (response == 1)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Company Added Sucessfully.');
                            window.location.href = '';
                        } else if (response == 2)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Company Edited Sucessfully.');
                            window.location.href = '';
                        }else if (response == 3)
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Company Already Exiest!');
                            window.location.href = '';
                        }
                        else
                        {
                            $('#loading').css("display", "none");
                            $("#Btnmybtn").attr('disabled', 'disabled');
                            alert('Invalid Request.');
                            window.location.href = '';
                        }
                    }
                });
            });

            function setEditdata(id)
            {
                $('#errorDIV').css('display', 'none');
                $('#errorDIV').html('');
                $('#loading').css("display", "block");
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $web_url; ?>admin/querydata.php',
                    data: {action: "GetAdminCompany", ID: id},
                    success: function (response) {
                        document.getElementById("editCompany").innerHTML = "EDIT Company";
                        $('#loading').css("display", "none");
                        var json = JSON.parse(response);
                        $('#Company').val(json.companyname);
                        $('#Skil').val(json.skil);
                        $('#unSkil').val(json.unskill);
                        $('#SemiSkil').val(json.semiskill);
                        $('#highlyskilled').val(json.highlyskilled);
                        $('#ESI').val(json.ESI);
                        $('#pf').val(json.pf);
                        $('#MedicalAllowance').val(json.MedicalAllowance);
                        $('#MedicalAllowancePer').val(json.MedicalAllowancePer);
                        
                        $('#strBonus').val(json.strBonus);
                        $('#strLeave').val(json.strLeave);
                        $('#iDailyWorkingRate').val(json.iDailyWorkingRate);
                        $('#action').val('EditCompanyname');
                        $('<input>').attr('type', 'hidden').attr('name', 'companymasterId').attr('value', json.companymasterId).attr('id', 'companymasterId').appendTo('#frmparameter');
                    }
                });
            }

            function deletedata(task, id)
            {
                var errMsg = '';
                if (task == 'Delete') {
                    errMsg = 'Are you sure to delete?';
                }
                if (confirm(errMsg)) {
                    $('#loading').css("display", "block");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $web_url; ?>admin/Ajaxcompanymaster.php",
                        data: {action: task, ID: id},
                        success: function (msg) {

                            $('#loading').css("display", "none");
                            window.location.href = '';

                            return false;
                        },
                    });
                }
                return false;
            }


    $("#frmSearch").keypress(function(event) { 
        if (event.keyCode === 13) { 
            $("#clickbutton").click(); 

            return false;
            
        }          
    }); 

            function PageLoadData(Page) {
                var Search_Txt = $('#Search_Txt').val();
                $('#loading').css("display", "block");
                $.ajax({
                    type: "POST",
                    url: "<?php echo $web_url; ?>admin/Ajaxcompanymaster.php",
                    data: {action: 'ListUser', Page: Page, Search_Txt: Search_Txt},
                    success: function (msg) {
                        $("#PlaceCompanyDataHere").html(msg);
                        $('#loading').css("display", "none");
                    },
                });
            }// end of filter
            PageLoadData(1);
        </script>
    </body>
</html>