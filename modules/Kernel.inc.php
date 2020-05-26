<?php
require_once("Database/Placeholder.php");
class Kernel
{
    //на вход пока ничего не надо
    function Kernel()
    {

    }
    
    //декодирует переданный HTML, от спец символов
    function decodeHTML($html)
    {
        $html = preg_replace("/&-1;/", "'", $html);
        $html = preg_replace('/&-2;/', '"', $html);
        $html = preg_replace('/&-3;/', "\\", $html);
        return $html;
    }
    
    function redirect($url, $params, $permanent = false)  
    {  
        if($permanent)  
        {  
            header('HTTP/1.1 301 Moved Permanently');  
        }  
        //<--формируем параметры, если они есть
        $getquery = '?';
        foreach ($params as $key => $value)
        {
            if($getquery == '?')
                $getquery .= $key.'='.rawurlencode($value);
            else
                $getquery .= "&${key}=".rawurlencode($value);
        }
        //-->
        header('Location: '.$url.$getquery);  
        exit();  
    }  
    
    function args($parname)
    {
        
	if(!is_null($_GET[$parname]))
        {
            return $_GET[$parname];
        }
	if(!is_null($_POST[$parname]))
        {
            return $_POST[$parname];
        }
        return null;
    }
    
    /*
        В качестве параметров на вход идет указатель на базу,
        запрос со знаками вопроса, массив с параметрами
    */
    function doSql()
    {
        $args = func_get_args();
        $dbh = array_shift($args);
        $query = array_shift($args);
        $sql = sql_placeholder_ex($query, $args, $error);
        if ($sql == false)
        {
            error_log("doSql: Bad query!",0);
        }
        else
        {
            $res = mysql_query($sql, $dbh);
            if (!$res)
            {
                error_log("doSql: something with DB! " . mysql_error(),0);
            }
            else
            {
                return $res;
            }
        }
        return false;
    }

    function getSeqNextVal($dbh, $seqName)
    {
	$sth = $this->doSql($dbh,"insert into $seqName values (NULL)");
        if ($sth)
        {
            
            $val = mysql_insert_id($dbh);
            return $val;
        }
        else
        {
            error_log("Kernel.getSeqNextVal: something with DB! " . mysql_error(),0);
        }
    }
    /*function doSql($dbh, $query, $params)
    {
        $size = sizeof($params);
        //сначала фильтруем переменные от тегов и тп
         for ($i=0; $i<$size; $i++ )
         {
            $params[$i] = addslashes($params[$i]);
            $params[$i] = htmlspecialchars($params[$i]);
            //убиваем команды запросов JOIN, UNION, SELECT
            $params[$i] = preg_replace("/(UNION|SELECT|JOIN|UPDATE|ALTER|=|OR)/i", " ", $params[$i]);
         }
        //промываем все ? и замещаем сответсвующими параметрами
        $count = 0;
        $pos = 1;
        while ($pos !== false)
        {
            $pos = strpos($query, '?',$pos+1);
            $count++;
        }
        $count--;
        print $count."<BR>";
        //проверяем на совпдение и делаем подстановку
        if ($count != $size)
        {
            error_log("doSql: Parametrs count not equal!",0);
        }
        else
        {
            for ($i=0; $i<$size; $i++ )
            {
                $query = preg_replace("/(\?)/i", "'".$params[$i]."'", $query, 1);
            }
            print $query;
        }
    }*/

}
?>