<?php
require_once($path_correction."modules/Values/StringHolder.inc.php");
require_once($path_correction."modules/Values/LongStringHolder.inc.php");
require_once($path_correction."modules/Values/NumberHolder.inc.php");
//require_once($path_correction."modules/Values/ArrayHolder.inc.php");
require_once($path_correction."modules/Values/DictionaryHolder.inc.php");

class ValueHolder
{
    var $kernel;
    var $dbh;
    var $holders;

function  ValueHolder(&$kernel, &$dbh)
{
    $this->kernel = $kernel;
    $this->dbh = $dbh;
    
    $this->holders = array(
	'numberHolder' => new NumberHolder($kernel,$dbh),
	'stringHolder' => new StringHolder($kernel,$dbh),
	'longStringHolder' => new LongStringHolder($kernel,$dbh),
	//'arrayHolder' => new ArrayHolder($kernel,$dbh),
	'dictionaryHolder' => new DictionaryHolder($kernel,$dbh)
    );
}

function value($type_id, $v_id)
{
    foreach ($this->holders as $key => $val)
    {
	if ($this->holders[$key]->typeID() == $type_id)
	{
            return $this->holders[$key]->value($v_id);
	}
    }
    return false;
}

function setValue($val)
{
    $type = gettype($val);
    if ($type == "integer")
    {
	return $this->holders['numberHolder']->setValue($val);
    }
    else if ($type == "string")
    {
	if ( strlen($val) < 255 )
	{
	    return $this->holders['stringHolder']->setValue($val);
	}
	else
	{
	    return $this->holders['longStringHolder']->setValue($val);
	}
    }
    else if ($type == "array")
    {
	return $this->holders['dictionaryHolder']->setValue($val);   
    }
    
}

function delete($type_id, $v_id)
{
    foreach ($this->holders as $key => $val)
    {
	if ($this->holders[$key]->typeID() == $type_id)
	{   
	    return $this->holders[$key]->delete($v_id);
	}
    }
}

function typeID()
{
    # please use _helpers/value_type_id_creator/do
    # for create UNIQ ID for your type
    return 570854379;# CRC32 for "VirtualHolder"
}

}


?>