<?php
include('../common.php');
include('IsLogin.php');
$connect = new connect();
include ('User_Paging.php');

if ($_POST['action'] == 'ListUser') {
    $filterstr = "";
    $countstr = "";
    $where = "";
    if ($_POST['iTempEmpId'] == '') {
        $where = " iTempEmpId=" . $_REQUEST['iTempEmpId'] . " ";
    }
    $filterstr = "SELECT * FROM `temempdocument` where iTempEmpId=" . $_REQUEST['iTempEmpId'] . " ";
    $countstr = "SELECT count(*) as TotalRow FROM `temempdocument` where iTempEmpId=" . $_REQUEST['iTempEmpId'] . " ";

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
            $filterStrSql = mysqli_query($dbconn,"SELECT * FROM `document` where iDocumentId=" . $rowfilter['iDocumentId'] . "");
            $rowDoc = mysqli_fetch_array($filterStrSql)
            ?>
            <div class="col-lg-3">
                <div class="m-portlet m-portlet--skin-dark m-portlet--bordered-semi m--bg-brand">
                    <div class="m-portlet__body m-bg">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="m-resp-img-div">
                                    <a target="_blank" href="./TempDocument/<?= $rowfilter['iTempEmpId'] ."/".$rowfilter['strDocumentImage']; ?>">
                                        <!--<i style="font-size: 40px;padding: 20px 0 0 161px;" class="fa fa-file-pdf-o"></i>-->
                                        <img src="./TempDocument/<?= $rowfilter['iTempEmpId'] ."/".$rowfilter['strDocumentImage']; ?>" style="height: 250px; width: 250px;" alt="">
                                    </a>
                                </div>
                                <p class="text-center"><strong>Document Name:</strong> <?= ucwords(strtolower($rowDoc['strDocumentName'])) ?></p>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        <?php }
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