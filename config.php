<?php

ob_start();
session_start();
date_default_timezone_set("Asia/Calcutta");
$website_name = "Employee Management";
$ProjectName = "Employee Management";


if ($_SERVER['SERVER_NAME'] == 'localhost') {

    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "sgeco";
    $web_url = 'http://localhost/sgeco/';

    $dbconn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Could not connect: ' . mysqli_connect_error());
    $cateperpaging = 15;

    $mailHost = "mail.getdemo.in";
    $mailUsername = "info@getdemo.in";
    $mailPassword = "info@123";
    $mailSMTPSecure = 'tls';
    $mailFrom = "no-replay@getdemo.in";
    $mailFromName = "Consulting Implantologist";
    $mailAddReplyTo = "no-replay@getdemo.in";
}  else if ($_SERVER['SERVER_NAME'] == 'http://sgeco.in' || $_SERVER['SERVER_NAME'] == 'sgeco.in') {

    $dbhost = "localhost";
    $dbuser = "sgeco";
    $dbpass = "LOmN?s1=pX6l";
    $dbname = "sgeco_sgeco";
    $web_url = 'http://sgeco.in/';
    $dbconn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Could not connect: ' . mysqli_connect_error());

    $cateperpaging = 50;

    $mailHost = "mail.getdemo.in";
    $mailUsername = "info@getdemo.in";
    $mailPassword = "info@123@1@";
    $mailSMTPSecure = 'tls';
    $mailFrom = "no-replay@getdemo.in";
    $mailFromName = "employee management";
    $mailAddReplyTo = "no-replay@getdemo.in";
    
} 
?>
 