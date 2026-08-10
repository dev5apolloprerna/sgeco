<?php
$MasterEntry = array("State.php", "City.php", "Location.php", "ExcelFormMaster.php", "Category.php");
$salary = array("salarydetails.php", "salarymaster.php");
$report = array("paypayment.php", "paidpayment.php");
$reportHistory = array("MultiCompanyPaymentHistory.php", "CompanyPaymentHistory.php");
$advanced = array("advancedmaster.php", "AddAdvancedDetails.php",'viewAdvancedDetails.php');
?>
<div class="page-header">
    <div class="page-header-top">
        <div class="container">
            <div class="page-logo">
                <a href="<?php echo $web_url; ?>admin/index.php">
                    <img src="<?php echo $web_url; ?>admin/assets/images/logo.png" width="60px" alt="logo" class="logo-default">
                </a>
            </div>
            <a href="javascript:;" class="menu-toggler"></a>
            <div class="top-menu">
                <ul class="nav navbar-nav pull-right">

                    <li class="dropdown dropdown-user dropdown-dark">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <i class="fa fa-user fa-2x"></i>
                            <span class="username username-hide-mobile"><?php echo $_SESSION['AdminName']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-default">
                            <?php
                                if (isset($_SESSION['AdminName'])) { 
                                    if($_SESSION['AdminId'] == 2) { ?>
                                        <!--<li>-->
                                        <!--    <a href="<?php echo $web_url; ?>admin/UpdateSecretCode.php">-->
                                        <!--        <i class="fa fa-user"></i>Update Secret Code-->
                                        <!--    </a>-->
                                        <!--</li>-->
                                    <?php } 
                                } ?>
                            <?php
                                if (isset($_SESSION['AdminName'])) { 
                                    if($_SESSION['AdminId'] == 1) { ?>
                                <li>
                                    <a href="<?php echo $web_url; ?>admin/viewProfile.php">
                                        <!-- <i class="icon-lock"></i> -->
                                        <i class="fa fa-user"></i>View Profile 
                                    </a>
                                </li>
                            <?php } 
                                } ?>
                            <li>
                                <a href="<?php echo $web_url; ?>admin/ChangePassword.php">
                                    <i class="fa fa-lock"></i>Change Password 
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo $web_url; ?>admin/Logout.php">
                                    <i class="fa fa-sign-out"></i>Log Out 
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="page-header-menu">
        <div class="container">
            <div class="hor-menu">
                <ul class="nav navbar-nav">
                    <?php
                    $result = mysqli_query($dbconn, "SELECT * FROM `user_rights` WHERE `iUserId`='" . $_SESSION['AdminId'] . "'");
                    if (mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_assoc($result);
                    } 

                    if (isset($_SESSION['AdminName'])) {
                        if((isset($row['isMasterMenu']) && $row['isMasterMenu'] == 1) || $_SESSION['AdminType'] == 1){
                            ?>
                            <li class="menu-dropdown classic-menu-dropdown <?php if (in_array(basename($_SERVER['REQUEST_URI']), $MasterEntry)) { echo 'active'; } ?>">
                                <a href="#">Master Entry</a>
                                <ul class="dropdown-menu pull-left">
                                    <?php
                                    if((isset($row['isBankEntry']) && $row['isBankEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/bankmaster.php" class="nav-link">
                                            Bank
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isCompanyEntry']) && $row['isCompanyEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/companymaster.php" class="nav-link">
                                            Company
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isDesignationEntry']) && $row['isDesignationEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/designationmaster.php" class="nav-link">
                                            Designation
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                    <!-- <li>
                                        <a href="<?php echo $web_url; ?>admin/salarymaster.php" class="nav-link">
                                            Salary
                                        </a>
                                    </li>-->
                                    <!--<li>-->
                                    <!--    <a href="<?php echo $web_url; ?>admin/documentMaster.php" class="nav-link">-->
                                    <!--        Document Master-->
                                    <!--    </a>-->
                                    <!--</li>-->
                                    <?php 
                                    if((isset($row['isAndroidUserEntry']) && $row['isAndroidUserEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/androidUserMaster.php" class="nav-link">
                                            Android User Master
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isTempEmployeeEntry']) && $row['isTempEmployeeEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/TempEmployeeList.php" class="nav-link">
                                            Temp Employee List
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isUserEntry']) && $row['isUserEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/users.php" class="nav-link">
                                            Create User
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                </ul>   
                            </li>

                        <?php }
                        if((isset($row['isEmployeeEntry']) && $row['isEmployeeEntry'] == 1) || $_SESSION['AdminType'] == 1){
                        ?>
                            <li class="menu-dropdown classic-menu-dropdown  <?php if (basename($_SERVER['REQUEST_URI']) == 'Employee.php') { echo 'active'; } ?>">
                                <a href="<?php echo $web_url; ?>admin/Employee.php">Employee</a>
                            </li>
                        <?php }
                            if((isset($row['isAdvancedEntry']) && $row['isAdvancedEntry'] == 1) || $_SESSION['AdminType'] == 1){
                        ?>
                            <li class="menu-dropdown classic-menu-dropdown <?php if (in_array(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), $advanced)) { echo 'active'; } ?>">
                                <a href="#">Advanced</a>
                                <ul class="dropdown-menu pull-left">
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/viewAdvancedDetails.php">View Advanced</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/advancedmaster.php">Create Advanced</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/advancedPaymentReport.php">Advanced Payment Report</a>
                                    </li>
                                </ul>
                            </li>
                        <?php }
                            if((isset($row['isSalaryMenu']) && $row['isSalaryMenu'] == 1) || $_SESSION['AdminType'] == 1){
                        ?>
                            <li class="menu-dropdown classic-menu-dropdown <?php if (in_array(basename($_SERVER['REQUEST_URI']), $salary)) { echo 'active'; } ?>">
                                <a href="#">Salary</a>
                                <ul class="dropdown-menu pull-left">
                                    <?php 
                                    if((isset($row['isViewSalary']) && $row['isViewSalary'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/salarydetails.php">View Salary</a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isSalaryCreateEntry']) && $row['isSalaryCreateEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                     <li>
                                        <a href="<?php echo $web_url; ?>admin/salarymaster.php" class="nav-link">
                                            Create Salary
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isComplanySalaryEntry']) && $row['isComplanySalaryEntry'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/companysalarymaster.php" class="nav-link">
                                            Create Company Salary
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewCompanySalary']) && $row['isViewCompanySalary'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                     <li>
                                        <a href="<?php echo $web_url; ?>admin/viewmulticompanyreport.php" class="nav-link">
                                            View  Company Salary
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>

                                </ul>
                            </li>

                        <?php } 
                            if((isset($row['isReportMenu']) && $row['isReportMenu'] == 1) || $_SESSION['AdminType'] == 1){
                        ?>
                            <li class="menu-dropdown classic-menu-dropdown  <?php if (in_array(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), array('Report.php', 'OtherReport.php'))) { echo 'active'; } ?>">
                                <a href="#"> Report</a>
                                 <ul class="dropdown-menu pull-left">
                                    <!-- <li>
                                        <a href="<?php echo $web_url; ?>admin/Report.php">Company Report</a>
                                    </li> -->
                                    <!-- Newly Created Report -->
                                    <?php 
                                    if((isset($row['isViewCompanyReport']) && $row['isViewCompanyReport'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/newReport.php">Company Report</a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewMultiCompanyReport']) && $row['isViewMultiCompanyReport'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/multicompanyreport.php" class="nav-link">
                                            Multi Company Report
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                      <!-- <li>
                                        <a href="<?php echo $web_url; ?>admin/companysalarymaster.php" class="nav-link">
                                            Create Company Salary
                                        </a>
                                    </li> -->
                                    <?php 
                                    if((isset($row['isViewESICReport']) && $row['isViewESICReport'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/New_ESIC_Generate.php" class="nav-link">
                                            New ESIC Generate
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewPFChallanReport']) && $row['isViewPFChallanReport'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/PF_Challan_Generate.php" class="nav-link">
                                            PF Challan Generate
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/Exit_Report.php" class="nav-link">
                                            Exit Report
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/Block_Report.php" class="nav-link">
                                            Block Report
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/OtherReport.php" class="nav-link">
                                            Other Report
                                        </a>
                                    </li>
                                </ul>
                            </li>

                        <?php } 
                            if((isset($row['isBankReportMenu']) && $row['isBankReportMenu'] == 1) || $_SESSION['AdminType'] == 1){
                        ?>
                            <li class="menu-dropdown classic-menu-dropdown  <?php if (basename($_SERVER['REQUEST_URI']) == 'bankReport.php' || basename($_SERVER['REQUEST_URI']) == 'multicompanybankreport.php' || basename($_SERVER['REQUEST_URI']) == 'HistoryReport.php') { echo 'active'; } ?>">
                                <a href="#">Bank Report</a>
                                 <ul class="dropdown-menu pull-left">
                                    <?php 
                                    if((isset($row['isViewCompanyBankReport']) && $row['isViewCompanyBankReport'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/bankReport.php">Company Bank Report</a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewMultiCompanyBankReport']) && $row['isViewCompanyBankReport'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/multicompanybankreport.php" class="nav-link">
                                            Multi Company Bank Report
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/HistoryReport.php" class="nav-link">
                                            Summary Report
                                        </a>
                                    </li>
                                     <!-- <li>
                                        <a href="<?php echo $web_url; ?>admin/bankReport_BOB.php" class="nav-link">
                                            BOB Bank Report
                                        </a>
                                    </li>
                                     <li>
                                        <a href="<?php echo $web_url; ?>admin/multicompanybankreport_BOB.php" class="nav-link">
                                            Multi Company BOB Bank Report
                                        </a>
                                    </li> -->
                                      <!-- <li>
                                        <a href="<?php echo $web_url; ?>admin/companysalarymaster.php" class="nav-link">
                                            Create Company Salary
                                        </a>
                                    </li> -->
                                </ul>
                           </li>  
                        <?php } 
                            if((isset($row['isPaymentMenu']) && $row['isPaymentMenu'] == 1) || $_SESSION['AdminType'] == 1){
                        ?>
                            <li class="menu-dropdown classic-menu-dropdown  <?php if (in_array(basename($_SERVER['REQUEST_URI']), $report)) { echo 'active'; } ?>">
                                <a href="#">Payment</a>
                                 
                                 <ul class="dropdown-menu pull-left">
                                     <?php 
                                    if((isset($row['isViewPayPayment']) && $row['isViewPayPayment'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/companypaypayment.php" class="nav-link">
                                            Company Pay Payment
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewPaidPayment']) && $row['isViewPaidPayment'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/companypaidpayment.php" class="nav-link">
                                            Company Paid Payment
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewMultiCompanyPayPayment']) && $row['isViewMultiCompanyPayPayment'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/paypayment.php">MultiCompany Pay Payment</a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewMultiCompanyPaidPayment']) && $row['isViewMultiCompanyPaidPayment'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/paidpayment.php" class="nav-link">
                                            MultiCompany Paid Payment
                                        </a>
                                    </li>
                                    <?php 
                                    } ?>
                                </ul>
                           </li>
                           
                            <?php
                            } 
                            
                            if((isset($row['isPaymentHistoryMenu']) && $row['isPaymentHistoryMenu'] == 1) || $_SESSION['AdminType'] == 1){ ?> 
                                <li class="menu-dropdown classic-menu-dropdown  <?php if (in_array(basename($_SERVER['REQUEST_URI']), $reportHistory)) { echo 'active'; } ?>">
                                <a href="#">Payment History</a>
                                 <ul class="dropdown-menu pull-left">
                                    <?php if((isset($row['isViewCompanyPaymentHistory']) && $row['isViewCompanyPaymentHistory'] == 1) || $_SESSION['AdminType'] == 1){ ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/CompanyPaymentHistory.php" class="nav-link">
                                            Company Payment History
                                        </a>
                                    </li>
                                    <?php 
                                    }
                                    if((isset($row['isViewMultiCompanyPaymentHistory']) && $row['isViewMultiCompanyPaymentHistory'] == 1) || $_SESSION['AdminType'] == 1){
                                    ?>
                                    <li>
                                        <a href="<?php echo $web_url; ?>admin/MultiCompanyPaymentHistory.php">Multi Company Payment History</a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                </ul>
                           </li>
                            <?php 
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

