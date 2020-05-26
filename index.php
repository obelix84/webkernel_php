<?php
$path_correction = "";
require_once ('modules/ConfInfo.inc.php');
require_once ('modules/ComponentCreator.inc.php');
require_once ('modules/ConfAlias.inc.php');
require_once ('modules/TemplateCreator.inc.php');
require_once ('modules/TemplateResource.inc.php');
require_once ('modules/Kernel.inc.php');
require_once ('modules/PageCreator.inc.php');
require ('res_conf.inc.php');

//инициализируем все необходимое
$K = new Kernel();
$CA = new ConfAlias($K, $dbh);
$CI = new ConfInfo($K, $dbh, $CA);
//проверяем входящие параметры
$page_id = $K->args('page');
$params = 0;

if(is_null($page_id))
{
    $page_id = $cvDefaultPageConf;
    $params = $CI->confParams('PageCreator', $page_id);
}
else 
{
    if (!preg_match("/^[A-Za-z][A-Za-z0-9\_]*$|^\d+$/i",$page_id))
    {
	echo "<h1>Эту страницу не видишь ты!</h1>";
        $params = false;
    }
    else
    {
        $params = $CI->confParams('PageCreator', $page_id);
    }
}  
if($params)
{    
    $PC = new PageCreator($params, $path_correction);
    echo $PC->output();
}
else
{
    echo "<h3>Что-то не то!</h3>";
}
?>
