<?php

class SetTemplate
{
    var $dbh;

    function setTemplate($db)
    {
        $this->dbh = $db;
    }

    function set($source, $template_name, $new_template)
    {
	    
	//засадим все в буфер
	$template = "";    
	if (preg_match("/^(\w+)\s*:\s*(\w+)$/", $source,$matches))
        {
	    if ($matches[1] == 'FILE')
            {
                $fp = fopen("templates/".$matches[2].".tmpl","r");
                if (!$fp)
                {
                    error_log("SetTemplate: Template file not found!",0);
                }
                else
                {
                    while (!feof ($fp))
                    {
                        $buffer = fgets($fp, 4096);
                        $template = $template.$buffer;
                    }
			fclose($fp);
		}
            }
            if ($matches[1] == 'DBH')
            {


            }
	    //теперь заменим старый шаблон новым... просто replace
	    $new_template = '<!--template_'.$template_name.'-->'.$new_template.'<!--\/template_'.$template_name.'-->';
	    $new = preg_replace('/(?s)<!--template_'.$template_name.'-->(.*)<!--\/template_'.$template_name.'-->/',$new_template, $template);
	    if($new != $template)
	    {
		//ну ежели заменили давайте теперь засунем обратно!
            	if ($matches[1] == 'FILE')
            	{
			$ft = fopen("templates/".$matches[2].".tmpl","w");
			if(!$ft)
			{
                    		error_log("SetTemplate: Template file not found!",0);
			}
			else
			{
				if (fwrite($ft, $new) === false)
				{
                    			error_log("SetTemplate: Cannot write in template file!", 0);
				}
				fclose($ft);
			}
		}	
	    }
	    else
	    {
            	error_log('SetTemplate: Nothing to replace or template is absence',0);
	    }
	}
        else
        {
            error_log("SetTemplate: Source not correct!",0);
        }
        return $template;
    }
}

?>
