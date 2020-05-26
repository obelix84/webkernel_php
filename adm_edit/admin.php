<?php
$path_correction = '../';
$href = 'admin.php';
require_once ($path_correction.'modules/ConfInfo.inc.php');
require_once ($path_correction.'modules/ComponentCreator.inc.php');
require_once ($path_correction.'modules/ConfAlias.inc.php');
require_once ($path_correction.'modules/TemplateCreator.inc.php');
require_once ($path_correction.'modules/TemplateResource.inc.php');
require_once ($path_correction.'modules/Kernel.inc.php');
require_once ($path_correction.'modules/PageCreator.inc.php');
require_once ($path_correction.'modules/AuthCreator.inc.php');
require ($path_correction.'res_conf.inc.php');

$K = new Kernel();

$SC = new SessionCreator($dbh, $K, 600);
$SC->isSession();
$status = $SC->status();
$CA = new ConfAlias($K, $dbh);
$CI = new ConfInfo($K, $dbh, $CA);

$out = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><head><script src="http://code.jquery.com/jquery-latest.js"></script><script language="JavaScript">//$(document).ready(function () { $("#d0").click(function () {alert("privet")})}); </script></head><body>';
if ($status == 'UNINITIALIZED')
{
    $login = $K->args('login');
    $pass = $K->args('pass');
    $exit = $K->args('exit');
    //���� ������, � �� ������� �� � ��� ���-��?
    if(!is_null($login) && !is_null($pass) && $login && $pass)
    {
        if($login == 'root' && crypt($pass, $cvDefaultAdminPass) == $cvDefaultAdminPass)
        {
            $SC->start(1, $pass);   
        }
        else
        {
            $TR = new TemplateResource($K,$dbh, $path_correction);
            $source = $TR->getTemplate('FILE:auth_login');
            $tmpl = new TemplateCreator($source);
            echo $tmpl->output();            
        }
    }
    else if(!is_null($exit) && $exit)
    {
        $SC->stop();  
    }
    else
    {
        $TR = new TemplateResource($K,$dbh, $path_correction);
        $source = $TR->getTemplate('FILE:auth_login');
        $tmpl = new TemplateCreator($source);
        echo $tmpl->output();    
    }
}
else if ($status == 'IPCHANGE' || $status == 'TIMEOUT')
{
    $out .= '<h1>����� �������!</h1>';
}
if ($SC->status() == 'INITIALIZED')
{
    $mode = $K->args('mode');
    $aliaserr = $K->args('aliaserr');
    $srcerr = $K->args('srcerr');
    $filenameerr = $K->args('filenameerr');
    //<-- PageCreator
    if($mode == 1)
    {
       //�������� ����� ��������, ���������� PageCreator
        $out = $out. '<div><h3>�����������������</h3></div>';
        if ($srcerr)
        {
            $out .= '<h4>�� ����� ������ ���������� �������!</h4>';
        }
        if ($filenameerr)
        {
            $out .= '<h4>�� ���������� ��� �����!</h4>';
        }
        if ($aliaserr)
        {
            $out .= '<h4>�������� �������� ������ ��������� ������ ������� A-Z, a-z, 0-9 � ���������� � �����!</h4>';
        }
        $out.="
        <script language='JavaScript'>
            function updateHTML()
            {
                alert(document.template.html.value);
                document.template.html.value = document.template.html.value.replace(/\'/gs,'&-1;');
                document.template.html.value = document.template.html.value.replace(/\"/gs,'&-2;');
                document.template.html.value = document.template.html.value.replace(/\\\/gs,'&-3;')
                alert(document.template.html.value);
            }
        </script>
        <form name='template' method='post' action='".$href."?mode=2' onSubmit='updateHTML();'>
        �������� �������� (������ ��������� �������): <input type='text' name='alias' value='' size=40><br>
        <input type='radio' name='group1' value='file' onClick ='document.getElementById(\"fn\").style.display=\"\"' >��������� �������� � �����<br/>
        <input type='radio' name='group1' value='dbh' onClick ='document.getElementById(\"fn\").style.display=\"none\"'>��������� �������� � ��<br/>
        <span style='display:none;' id='fn'>��� ����� � ������� (������ ��������� �������) <input type='text' name='filename' value='' size=50><br></span>
        <textarea cols='60' rows='15' name='html'></textarea><br>
        <span>�����������(�������������):</span></br>
        <textarea cols='60' rows='5' name='comment'></textarea><br>
        <input type='submit' name='save' value='���������'>
        </form>";    
    }
    else if($mode == 2)
    {
        $alias = $K->args('alias');
        $comment = $K->args('comment');
        $html = $K->args('html');
        $place = $K->args('place');
        $where = $K->args('group1');
        $filename = $K->args('filename');
        echo $K->decodeHTML($html);
        $html = $K->decodeHTML($html);
        $err = array('aliaserr' => '0', 'filenameerr' => '0', 'srcerr' => '0');
        //��������� ����� ��������������
        //���� ������ ������?
        if($where == 'file')
        {
            $TR = new TemplateResource($K,$dbh, $path_correction);
            if(!$filename)
            {
                $count = 0;
                $filename = md5(uniqid(rand(), true));
                while($TR->isFileTemplateExist($filename))
                {
                    $count++;
                    $filename = md5(uniqid(rand(), true));
                    if ($count> 65536)
                    {
                        echo '�� ���� ������� ����� ������! ���������� � ���������� ��������������!';
                        error_log("Admin-PageCreator: Cant create new template!",0);
                        break;
                    }
                }
            }
            //<--�������� ������������ �������� ������: ����� � ������ � �����
            $correct = 1;
            if(!preg_match('/^\w+$/', $filename))
            {
                $correct = 0;
                $err['filenameerr'] = 1;
            }
            if ($alias != "")
            {
                if(!preg_match('/^[A-Za-z][A-Za-z0-9\_]*$/', $alias))
                {
                    $correct = 0;
                    $err['aliaserr'] = 1;
                }
            }
            //���� ���� ������ - �� ��� ���������� �����
            if(!$correct)
            {
                $err['mode'] = 1;
                $K->redirect('admin.php', $err);
            }
            //-->
            //<-- � ������ ���� �� ������������ ������ ��� ���, � ������ ���� ��� �������� ���� ����
            $TR->setTemplate($html, 'FILE:'.$filename);
            $page_params = array('source'=>'FILE:'.$filename, 'compname' => 'PageCreator');
            if($comment)
                $page_params['comment'] = $comment;
            $conf_id = $CI->setConfParams($page_params);
            if($alias)
            {
                $CA->addAliasToConf( $alias,'PageCreator', $conf_id);
            }
            $K->redirect('admin.php');
            //-->
        }
        else if($where == 'dbh')
        {
            $TR = new TemplateResource($K,$dbh, $path_correction);
            //<--�������� ������������ �������� ������: ������ � ������������
            $correct = 1;
            if ($alias != "")
            {
                if(!preg_match('/^[A-Za-z][A-Za-z0-9\_]*$/', $alias))
                {
                    $correct = 0;
                    $err['aliaserr'] = 1;
                }
            }
            //���� ���� ������ - �� ��� ���������� �����
            if(!$correct)
            {
                $err['mode'] = 1;
                $K->redirect('admin.php', $err);
            }
            //���� ��� ������, �� ����� ��������� ������
            //<-- � ������ ���� �� ������������ ������ ��� ���, � ������ ���� ��� �������� ���� ����
            $type_val = $TR->setTemplate($html, 'DBH: 0000_00');
            $type_val = $type_val[0].'_'.$type_val[1];
            $page_params = array('source'=>'DBH:'.$type_val, 'compname' => 'PageCreator');
            if($comment)
                $page_params['comment'] = $comment;
            $conf_id = $CI->setConfParams($page_params);
            if($alias)
            {
                $CA->addAliasToConf( $alias,'PageCreator', $conf_id);
            }
            $K->redirect('admin.php');
            //-->
        }
        else
        {
            $err['srcerr'] = 1;
            $err['mode'] = 1;
            $K->redirect('admin.php', $err);
        }
    }
    else if($mode == 3)
    {
        //���� ������ ����������� ��� �������������� PageCreatora
        $counter = 0;
        //�������� ��� ������������
        //$ret = $K->doSql($dbh,"SELECT conf_id FROM conf_params WHERE v_id IN (SELECT vd.d_id FROM vals_dictionary AS vd WHERE v_id IN (SELECT VS.v_id FROM vals_string as VS WHERE val='PageCreator'))");
        $ret = $K->doSql($dbh, 'SELECT conf_id, cp.v_id, word, vd.v_id, vs.v_id, vs.val FROM conf_params AS cp, vals_dictionary AS vd, vals_string AS vs  WHERE vs.val = "PageCreator"  AND  vd.word = "compname" AND vs.v_id = vd.v_id AND cp.v_id =  vd.d_id');
        if($ret)
        {
            while($r = mysql_fetch_assoc($ret))
            {
                $params = $CI->confParams('PageCreator',$r['conf_id']);    
                //$out .=  '<script> $("#d'.$counter.'").click(function () {alert("privet")}); </script>';
                $out .= "<div id='d".$counter."' style='padding: 3px; background-color: #eeeeee; width: 300px; border:1px dotted black;'>������������ �����: <strong>".$r['conf_id']."</strong>";
                $alias = $CA->alias('PageCreator', $r['conf_id']);
                if (is_null($alias))
                    $alias = $r['conf_id'];
                $out .= "<br/>���������: <strong>${alias}</strong><br/>";
                $out .= ($params['comment']? "�����������:<p style='margin: 5px; padding-left: 30px; font-style: italic;'>".$params['comment']."</p>" : '<br/>');
                $out .= "<a href='admin.php?mode=6&conf=".$r['conf_id']."'>��������</a> <a href='admin.php?mode=4&conf=".$r['conf_id']."'>�������</a> <a href='../index.php?page=".$alias."' target='_blank'>��������</a></div><br/></div>";
                $counter++;
            }
        }
        else
        {
            error_log("Admin mode 3: something with DB!", 0); 
        }
        mysql_free_result($ret);
        //SELECT conf_id FROM conf_params WHERE v_id IN (SELECT vd.d_id FROM vals_dictionary AS vd WHERE v_id IN (SELECT VS.v_id FROM vals_string as VS WHERE val='PageCreator'));

    }
    else if($mode == 4)
    {
        //�������� ������������ PageCreator'a
        $conf = $K->args('conf');
        $out .= "<h2>�� �������, ��� ������ ������� ��� ��������?</h2>";
        $params = $CI->confParams('PageCreator',$conf);    
        $out .= "<div style='border:1px dotted black;'>������������ �����: <strong>".$conf."</strong>";
        $alias = $CA->alias('PageCreator', $conf);
        $out .= "<br/>���������: <strong>${alias}</strong><br>";
        $out .= "�����������:<br><i>".$params['comment']? $params['comment']."</i><br>" : '</i>';
        $out .= "<a href='../index.php?page=".$alias."' target='_blank'>��������</a></div><br/></div>";
        $out .= "<form method='post' action='admin.php' name='delform'><input type='hidden' value='5' name='mode'><input type='hidden' value='".$conf."' name='conf'><input type='submit' value='�������' name='delete'></form>";
    }
    else if($mode == 5)
    {
        //��������
        $conf = $K->args('conf');
        $params = $CI->confParams('PageCreator',$conf);
        $TR = new TemplateResource($K,$dbh, $path_correction);
        $TR->deleteTemplate($params['source']);
        $CI->deleteConfParams($conf);
        $CA->deleteAlias('PageCreator', $conf);
        $K->redirect('admin.php');

    }
    else if($mode == 6)
    {
        $conf = $K->args('conf');
        $alias = $CA->alias('PageCreator', $conf);
        $params = $CI->confParams('PageCreator',$conf);       
        if ($srcerr)
        {
            $out .= '<h4>�� ����� ������ ���������� �������!</h4>';
        }
        if ($filenameerr)
        {
            $out .= '<h4>�� ���������� ��� �����!</h4>';
        }
        if ($aliaserr)
        {
            $out .= '<h4>�������� �������� ������ ��������� ������ ������� A-Z, a-z, 0-9 � ���������� � �����!</h4>';
        }
        $out.="
        <script language='JavaScript'>
        function updateHTML()
        {
            alert(document.template.html.value);
            document.template.html.value = document.template.html.value.replace(/\'/gs,'&-1;');
            document.template.html.value = document.template.html.value.replace(/\"/gs,'&-2;');
            document.template.html.value = document.template.html.value.replace(/\\\/gs,'&-3;')
            alert(document.template.html.value);
        }
        </script>
        <form name='template' method='post' action='".$href."?mode=7' onSubmit='updateHTML();'>
        �������� �������� (������ ��������� �������): <input type='text' name='alias' value='".$alias."' size=40><br>";
        if (preg_match("/^(\w+)\s*:\s*(\w+)$/", $params['source'], $matches))
	{	
	    if ($matches[1] == 'FILE')
            {
                $out .="<input type='radio' name='group1' value='file' onClick ='document.getElementById(\"fn\").style.display=\"\"' checked='true'>��������� �������� � �����<br/>
                <input type='radio' name='group1' value='dbh' onClick ='document.getElementById(\"fn\").style.display=\"none\"'>��������� �������� � ��<br/>
                <span style='display:\"\";' id='fn'>��� ����� � ������� (������ ��������� �������) <input type='text' name='filename' value='".$matches[2]."' size=50><br></span>";
	    }
	    if ($matches[1] == 'DBH')
	    {
		    //DBH:typeid_vid
                $out .="<input type='radio' name='group1' value='file' onClick ='document.getElementById(\"fn\").style.display=\"\"' >��������� �������� � �����<br/>
                <input type='radio' name='group1' value='dbh' onClick ='document.getElementById(\"fn\").style.display=\"none\"' checked='true'>��������� �������� � ��<br/>
                <span style='display:none;' id='fn'>��� ����� � ������� (������ ��������� �������) <input type='text' name='filename' value='' size=50><br></span>";
                
	    }
        }
	else
	{
	    error_log("TemplateResource - deleteTemplate: Source not correct!",0);	
	    return false;
	}
        $TR = new TemplateResource($K, $dbh, $path_correction);
        $html = $TR->getTemplate($params['source']);
        $out .= "<textarea cols='60' rows='15' name='html'>".$html."</textarea><br>
        <span>�����������(�������������):</span></br>
        <textarea cols='60' rows='5' name='comment'>".$params['comment']."</textarea><br>
        <input type='hidden' name='conf' value='".$conf."'>
        <input type='submit' name='save' value='���������'>
        </form>";  
    }
    else if($mode == 7)
    {
        $alias = $K->args('alias');
        $comment = $K->args('comment');
        $html = $K->args('html');
        $html = $K->decodeHTML($html);
        $place = $K->args('place');
        $where = $K->args('group1');
        $filename = $K->args('filename');
        $conf =$K->args('conf');
        $err = array('aliaserr' => '0', 'filenameerr' => '0', 'srcerr' => '0');
        //��������� ����� ��������������
        //���� ������ ������?
        if($where == 'file')
        {
            $TR = new TemplateResource($K,$dbh, $path_correction);
            if(!$filename)
            {
                $count = 0;
                $filename = md5(uniqid(rand(), true));
                while($TR->isFileTemplateExist($filename))
                {
                    $count++;
                    $filename = md5(uniqid(rand(), true));
                    if ($count> 65536)
                    {
                        echo '�� ���� ������� ����� ������! ���������� � ���������� ��������������!';
                        error_log("Admin-PageCreator: Cant create new template!",0);
                        break;
                    }
                }
            }
            //<--�������� ������������ �������� ������: ����� � ������ � �����
            $correct = 1;
            if(!preg_match('/^\w+$/', $filename))
            {
                $correct = 0;
                $err['filenameerr'] = 1;
            }
            if ($alias != "")
            {
                if(!preg_match('/^[A-Za-z][A-Za-z0-9\_]*$/', $alias))
                {
                    $correct = 0;
                    $err['aliaserr'] = 1;
                }
            }
            //���� ���� ������ - �� ��� ���������� �����
            if(!$correct)
            {
                $err['mode'] = 6;
                $K->redirect('admin.php', $err);
            }
            //-->
            //<-- ������� ���� ������� ������, � ����� ��������  �����
            $params = $CI->confParams('PageCreator',$conf);
            $TR = new TemplateResource($K,$dbh, $path_correction);
            $TR->deleteTemplate($params['source']);
            $CI->deleteConfParams($conf);
            $CA->deleteAlias('PageCreator', $conf);
            //-->
            //<-- � ������ ���� �� ������������ ������ ��� ���, � ������ ���� ��� �������� ���� ����
            $TR->setTemplate($html, 'FILE:'.$filename);
            $page_params = array('source'=>'FILE:'.$filename, 'compname' => 'PageCreator');
            if($comment)
                $page_params['comment'] = $comment;
            $conf_id = $CI->setConfParams($page_params, $conf);
            if($alias)
            {
                $CA->addAliasToConf( $alias,'PageCreator',$conf_id);
            }
            $K->redirect('admin.php');
        }    //-->
        else if($where == 'dbh')
        {
            //<--�������� ������������ �������� ������: ����� � ������ � �����
            $correct = 1;
            if ($alias != "")
            {
                if(!preg_match('/^[A-Za-z][A-Za-z0-9\_]*$/', $alias))
                {
                    $correct = 0;
                    $err['aliaserr'] = 1;
                }
            }
            //���� ���� ������ - �� ��� ���������� �����
            if(!$correct)
            {
                $err['mode'] = 6;
                $K->redirect('admin.php', $err);
            }
            //-->
            //<-- ������� ���� ������� ������, � ����� ��������  �����
            $params = $CI->confParams('PageCreator',$conf);
            $TR = new TemplateResource($K,$dbh, $path_correction);
            $TR->deleteTemplate($params['source']);
            $CI->deleteConfParams($conf);
            $CA->deleteAlias('PageCreator', $conf);
            //-->
            //<-- � ������ ���� �� ������������ ������ ��� ���, � ������ ���� ��� �������� ���� ����
            $type_val = $TR->setTemplate($html, 'DBH: 0000_00');
            $type_val = $type_val[0].'_'.$type_val[1];
            $page_params = array('source'=>'DBH:'.$type_val, 'compname' => 'PageCreator');
            if($comment)
                $page_params['comment'] = $comment;
            $conf_id = $CI->setConfParams($page_params, $conf);
            if($alias)
            {
                $CA->addAliasToConf( $alias,'PageCreator',$conf_id);
            }
            $K->redirect('admin.php');
        }
    }
    else
    {
        //���� �����������������
        $out .= "<div><strong>PageCreator</strong> <a href='".$href."?mode=1'>������� ����� ��������</a> <a href='".$href."?mode=3'>�������������</a></div>";
    }
    //--> PageCreator!!!
}   
$status = $SC->status();
echo $out."</body>"."<hr>${status}<hr>";

?>