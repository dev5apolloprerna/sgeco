<?php
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    $filterstr = "";
    $countstr = "";
    $where = "";
    if ($_POST['employeeId'] == '') {
        $where = " employeeId=" . $_REQUEST['employeeId'] . " ";
    }
    $filterstr = "SELECT * FROM `employee` where employeeId=" . $_REQUEST['employeeId'] . " ";
    $countstr = "SELECT count(*) as TotalRow FROM `employee` where employeeId=" . $_REQUEST['employeeId'] . " ";

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
        while ($rowfilter = mysqli_fetch_array($resultfilter)) {
            $EntrDate = $rowfilter['strEntryDate'];
            $arr = explode(' ', $EntrDate);
            $dateArrar = explode('-', $arr[0]);
//            echo $rowfilter['aadharImage'];
//            echo $rowfilter['voteridImage'];
//            echo $rowfilter['pancardImage'];
//            echo $rowfilter['passportImage'];
            if($rowfilter['aadharImage'] != NULL){
            ?>
            <div class="col-lg-3">
                <div class="m-portlet m-portlet--skin-dark m-portlet--bordered-semi m--bg-brand">
                    <div class="m-portlet__body m-bg">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="m-resp-img-div">
                                    <a target="_blank" href="Document/<?php echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['aadharImage']; ?>"><i style="font-size: 40px;padding: 20px 0 0 161px;" class="fa fa-file-pdf-o"></i></a>
                                </div>
<!--                                <img class="img-responsive m-resp-img" src="Document/<?php // echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['aadharImage']; ?>"alt="">-->
                                <p class="text-center"><strong>Document Name:</strong> Aadhar Card Front</p>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }if($rowfilter['voteridImage'] != NULL){ ?>
                <div class="col-lg-3">
                <div class="m-portlet m-portlet--skin-dark m-portlet--bordered-semi m--bg-brand">
                    <div class="m-portlet__body m-bg">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="m-resp-img-div">
                                    <a target="_blank" href="Document/<?php echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['voteridImage']; ?>"><i style="font-size: 40px;padding: 20px 0 0 161px;" class="fa fa-file-pdf-o"></i></a>
                                    <!--<img class="img-responsive m-resp-img" src="Document/<?php // echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['voteridImage']; ?>"alt="">-->
                                </div>
                                <p class="text-center"><strong>Document Name:</strong> Aadhar Card Back </p>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <?php }
            if($rowfilter['pancardImage'] != NULL){ ?>
                <div class="col-lg-3">
                <div class="m-portlet m-portlet--skin-dark m-portlet--bordered-semi m--bg-brand">
                    <div class="m-portlet__body m-bg">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="m-resp-img-div">
                                    <a target="_blank" href="Document/<?php echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['pancardImage']; ?>"><i style="font-size: 40px;padding: 20px 0 0 161px;" class="fa fa-file-pdf-o"></i></a>
                                    <!--<img class="img-responsive m-resp-img" src="Document/<?php // echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['pancardImage']; ?>"alt="">-->
                                </div>
                                <p class="text-center"><strong>Document Name:</strong> Pan card </p>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <?php } 
             
            if($rowfilter['passportImage'] != NULL){ ?>
                <div class="col-lg-3">
                <div class="m-portlet m-portlet--skin-dark m-portlet--bordered-semi m--bg-brand">
                    <div class="m-portlet__body m-bg">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="m-resp-img-div">
                                    <a target="_blank" href="Document/<?php echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['passportImage']; ?>"><i style="font-size: 40px;padding: 20px 0 0 161px;" class="fa fa-file-pdf-o"></i></a>
                                    <!--<img class="img-responsive m-resp-img" src="Document/<?php // echo $dateArrar[2] . "/" . $dateArrar[1] . "/" . $dateArrar[0] . "/" . $rowfilter['passportImage']; ?>"alt="">-->
                                </div>
                                <p class="text-center"><strong>Document Name:</strong> Other Document </p>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <?php } 
        }
    } else {
        ?>
        <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark">
            <div class="alert alert-info clearfix profile-information padding-all-10 margin-all-0 backgroundDark">
                <h1 class="font-white text-center"> No Data Found ! </h1>
            </div>   
        </div>
        <?php
    }
}
if ($_POST['action'] == 'DeleteExhibitionImage') {

    $getUploadFile = mysqli_query($dbconn, "select * from `exhibitionimage` where id='" . $_POST['ID'] . "' ");
    $tabUploadFile = mysqli_fetch_assoc($getUploadFile);
    unlink('../gallery/' . $tabUploadFile['image']);
    unlink('../gallery/thumb/' . $tabUploadFile['image']);
    ;

    mysqli_query($dbconn, "Delete FROM `exhibitionimage` where id='" . $_POST['ID'] . "'");
}
?>

<div class="col-lg-12 m-pager">
    <?php if ($totalrecord > $per_page) { ?>
        <div class="col-lg-12 col-md-12  col-xs-12 col-sm-12 padding-5 bottom-border-verydark" style="text-align: center;">
            <div class="form-actions noborder">
                <?php
                echo '<ul>';

                if ($totalrecord > $per_page) {
                    echo paginate($reload = '', $show_page, $total_pages);
                }
                echo "</ul>";
                ?>
            </div>
        </div>
    <?php } ?>
</div>