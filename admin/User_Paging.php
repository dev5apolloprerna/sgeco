<?php
function paginate($reload, $page, $tpages) {
    $adjacents = 2;
    $prevlabel = "&lsaquo; Prev";
    $nextlabel = "Next &rsaquo;";
    $out = "";
    // previous
    if ($page == 1) {
		$out.= "<li><a>|< First</a></li>\n";
        $out.= "<li><a>" . $prevlabel . "</a></li>\n";
		
    } 
	else if($page=='')
	{   $out.= "<li><a>|< First</a></li>\n";
		$out.= "<li><a>" . $prevlabel . "</a></li>\n";
	}
	
	elseif ($page == 2) {
		 $out.= "<li ><a  href='javascript:void(0);' onclick='PageLoadData(1)'>|< First</a>\n</li>";
        $out.= "<li><a  href='javascript:void(0);' onclick='PageLoadData(1)'>" . $prevlabel . "</a>\n</li>";
    } else {
		 $out.= "<li ><a  href='javascript:void(0);' onclick='PageLoadData(1)'>|< First</a>\n</li>";
        $out.= "<li><a href='javascript:void(0);' onclick='PageLoadData(".($page - 1).")' >" . $prevlabel . "</a>\n</li>";
    }
  
    $pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
    $pmax = ($page < ($tpages - $adjacents)) ? ($page + $adjacents) : $tpages;
    for ($i = $pmin; $i <= $pmax; $i++) {
        if ($i == $page) {
            $out.= "<li  class=\"active\"><a href='javascript:void(0);'>" . $i . "</a></li>\n";
        } elseif ($i == 1) {
            $out.= "<li><a  href='javascript:void(0);' onclick='PageLoadData(".$i.")'>" . $i . "</a>\n<li>";
        } else {
            $out.= "<li><a  href='javascript:void(0);' onclick='PageLoadData(".$i.")'>". $i . "</a>\n</li>";
        }
    }
    
    if ($page < ($tpages - $adjacents)) {
		
		$out.= "<li><a style='font-size:13px' href='javascript:void(0);'  " . "\">---</a><li>\n";
        $out.= "<li><a style='font-size:13px' href='javascript:void(0);' onclick='PageLoadData(".$tpages.")' ". "\">" . $tpages . "</a><li>\n";
    }
    // next
    if ($page < $tpages) {
        $out.= "<li><a  onclick='PageLoadData(".($page + 1).")' href='javascript:void(0);'>" . $nextlabel . "</a>\n</li>";
		$out.= "<li><a  onclick='PageLoadData(".$tpages.")' href='javascript:void(0);'>Last >|</a>\n</li>";

    } else {
        $out.= "<li><a>" . $nextlabel . "</li></a>\n";
		$out.= "<li><a>Last >| </li></a>\n";

    }
    $out.= "";
    return $out;
}
