<?php

//$dbh = mysql_connect("localhost:8888","root","root");
$dbh = mysql_connect("sonicx.mysql:3306","sonicx_mysql","e89sohab");
mysql_select_db('sonicx_valensia', $dbh);

//default admin name
$cvDefaultAdminName = 'admin';
//admin Password
$cvDefaultAdminPass = 'CQKFiMYzKz93M';
//conf page default
$cvDefaultPageConf = 1;
//alias conf default
$cvDefaultPageAlias = 'main';
//error_reporting(2);
?>
