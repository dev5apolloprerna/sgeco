<?php 
if(!isset($_SESSION['AdminId']) && !isset($_SESSION['AdminName']) && !isset($_SESSION['AdminType']))
{
	header('location:'.$web_url.'admin/login.php');	
}

?>