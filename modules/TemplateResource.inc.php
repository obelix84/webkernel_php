<?php
require_once($path_correction.'modules/Values/ValueHolder.inc.php');

class TemplateResource
{
	var $dbh;
	var $path_correction;
	var $holder;
	
	function TemplateResource(&$Kernel, &$dbh, &$path_correction)
	{
		$this->dbh = $dbh;
		$this->path_correction = $path_correction;
		$this->holder = new ValueHolder($Kernel, $dbh);

	}
	
	//никаких секций по одному шаблону на файл
	function getTemplate($source)
	{
		$template = "";
        	if (preg_match("/^(\w+)\s*:\s*(\w+)$/", $source,$matches))
        	{
        		if ($matches[1] == 'FILE')
            		{
                		$fp = fopen($this->path_correction."templates/".$matches[2].".tmpl","r");
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
				//DBH:typeid_vid
				if (preg_match("/^(\d+)_(\d+)$/", $matches[2],$new_match))
				{
					$template = $this->holder->value($new_match[1], $new_match[2]);
				}
            		}
		
        	}
        	else
        	{
            		error_log("GetTemplate: Source not correct!",0);
        	}
		return $template;	
	}
	
	//проверяет существования шаблона, только для файла!!!
        function isFileTemplateExist($source)
        {
                return file_exists($this->path_correction.'templates/'.$source.'.tmpl');        
        }
        
        //изменяет существующий файл или создает новый.. пока работает только с файлами
	function setTemplate($new, $source)
	{
		//засовываем все куда надо..
                if (preg_match("/^(\w+)\s*:\s*(\w+)$/", $source,$matches))
		{	
			if ($matches[1] == 'FILE')
                        {
				$ft = fopen($this->path_correction."templates/".$matches[2].".tmpl","w");
				if(!$ft)
				{
                    			error_log("SetTemplate: Template file not found!",0);
                    			return false;
				}
				else
				{
					if (fwrite($ft, $new) === false)
					{
                                                error_log("SetTemplate: Cannot write in template file!", 0);
						return false;
					}
					fclose($ft);
				}
				return $source;
			}
			if ($matches[1] == 'DBH')
			{
				//DBH:typeid_vid
                                return $this->holder->setValue($new);			
			}		
		}
		else
		{
			error_log("TemplateResource - setTemplate: Source not correct!",0);	
			return false;
		}
		return true;	
	}
	//удаляет существующий файл или создает новый.. пока работает только с файлами
	function deleteTemplate($source)
	{
		if (preg_match("/^(\w+)\s*:\s*(\w+)$/", $source,$matches))
		{	
			if ($matches[1] == 'FILE')
            		{
				return unlink($this->path_correction.'templates/'.$matches[2].'.tmpl');
			}
			if ($matches[1] == 'DBH')
			{
				//DBH:typeid_vid
                                if (preg_match("/^(\d+)_(\d+)$/", $matches[2],$new_match))
				{
                                        return $this->holder->delete($new_match[1], $new_match[2]);	
                                }
                        }
		}
		else
		{
			error_log("TemplateResource - deleteTemplate: Source not correct!",0);	
			return false;
		}
	}
}

?>