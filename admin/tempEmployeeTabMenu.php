<style>
    .active{
        background: #f0f2f8 !important;
        border-bottom: white !important;
        border-right: #78756f 1px solid !important;
        border-left: #78756f 1px solid !important;
    }
    
</style>
<?php //echo basename($_SERVER['REQUEST_URI']); 
    $url = (explode("?",basename($_SERVER['REQUEST_URI'])));
?>
<ul class="nav nav-tabs" id="ex1" role="tablist" style="margin-bottom: 0px !important;">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php
                            if ($url[0] == "moveTempEmployee.php") {
                                echo 'active';
                            }
                            ?>" href="<?php echo $web_url; ?>admin/moveTempEmployee.php?<?php echo $url[1] ?>" role="tab">Edit / Move Employee</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?php if ($url[0] == 'uploadTempEmployeeDocuments.php') {
                                echo 'active';
                            } ?>" href="<?php echo $web_url; ?>admin/uploadTempEmployeeDocuments.php?<?php echo $url[1] ?>" role="tab">Upload Doucuments</a>
    </li>
</ul>