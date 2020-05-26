<?php

class GetTemplate
{
    var $dbh;

    function GetTemplate($db)
    {
        $this->dbh = $db;
    }

    function template($source)
    {
        $template = "";
        if (preg_match("/^(\w+)\s*:\s*(\w+)$/", $source,$matches))
        {
            if ($matches[1] == 'FILE')
            {
                $fp = fopen("templates/".$matches[2].".tmpl","r");
                if (!$fp)
                {
                    error_log("GetTemplate: Template file not found!",0);
                }
                else
                {
                    while (!feof ($fp))
                    {
                        $buffer = fgets($fp, 4096);
                        $template = $template.$buffer;
                    }
                }
            }
            if ($matches[1] == 'DBH')
            {


            }

        }
        else
        {
            error_log("GetTemplate: Source not correct!",0);
        }
        return $template;
    }
}

?>
