<?php

class TemplateCreator
{
    var $template;
    var $sections = "";
    var $secArr;

	function TemplateCreator($template)
	{
		if (!is_string($template))
		{
            		error_log("GetTemplate: Source not correct!",0);
		}
		else
		{
			//шаблон парсим на секции
			if(preg_match_all("/<!--([A-Za-z][A-Za-z0-9]+_[A-Za-z\d]+)\/-->/", $template, $sec))
			{
				$this->secArr = array();
				$count = sizeof($sec[1]);
				for ($i=0; $i<$count; $i++)
				{
					$this->sections[$sec[1][$i]] = " ";
					array_push($this->secArr, $sec[1][$i]);
				}
                	}
                	else
                	{
                    		$this->sections = $template;
                	}
			$this->template = $template;
		}	
	}

    //add section to template
    //секция выглядит следующим образом <!--meta_NAMESEC_ConfOrAlias/-->
    function add($nameSec, $val)
    {
        $flag = 0;
        foreach ($this->sections as $key => $value)
        {
            if ($key == $nameSec)
            {
                $flag = 1;
                $this->sections[$key] = $value.$val;
                break;
            }
        }
        if ($flag == 0)
        {
            return false;
        }
        return true;
    }

    function getSectionsNames()
    {
        return $this->secArr;
    }

    function output()
    {
	if (is_array($this->sections))
	{
	    foreach ($this->sections as $key => $val)
	    {
		$this->template = preg_replace("/<!--".$key."\/-->/", $val, $this->template, 1);
	    }
	}
        if (is_string($this->sections))
        {
            $this->template = $this->sections;
        }
	return $this->template;
    }

}

?>