<?php
require_once($path_correction."modules/Values/ValueHolder.inc.php");

class DictionaryHolder
{
    var $kernel;
    var $dbh;
    
    function DictionaryHolder(&$kernel, &$dbh)
    {
	$this->kernel = $kernel;
	$this->dbh = $dbh;
    }

    function &value($v_id)
    {
	$holder = new ValueHolder($this->kernel, $this->dbh);    
	$val_query = $this->kernel->doSql($this->dbh, "SELECT word, type_id, v_id FROM vals_dictionary WHERE d_id = ? ", $v_id);
	if (! $val_query)
	{
		error_log("DictionaryHolder: Something wrong with DB!!!",0);
	}
	else
	{
		$ret = array();
		while($val = mysql_fetch_assoc($val_query))
		{
			$value = $holder->value($val['type_id'], $val['v_id']);
			$ret[$val['word']] = $value;	
		}
    		return $ret;
	}
	return false;
    }
    
    function setValue($ref_data)
    {
	$holder = new ValueHolder($this->kernel, $this->dbh);
	$a_id = $this->kernel->getSeqNextVal($this->dbh, 'seq_vid_dictionary');
	foreach ($ref_data as $key => $val)
	{
	    $id = $this->kernel->getSeqNextVal($this->dbh, 'seq_id_dictionary');
	    $arr = $holder->setValue($val);
	    $res = $this->kernel->doSql($this->dbh, "INSERT INTO vals_dictionary(id, d_id, word, type_id, v_id) VALUES (?, ?, ?, ?, ?)", $id, $a_id, $key, $arr[0], $arr[1]);
	    if (!$res)
	    {
	 	error_log("DictionaryHolder: Something wrong with DB!", 0);
		return -1;  
	    } 
	}
	return array($this->typeID(), $a_id);
    }
    
    function delete($v_id)
    {
        $holder = new ValueHolder($this->kernel, $this->dbh);
	$res = $this->kernel->doSql($this->dbh, "SELECT * FROM vals_dictionary WHERE d_id = ?", $v_id);
	if (!$res)
	{
	    error_log("DictionaryHolder: Something wrong with DB!", 0);
	    return false;  
	}
	else
	{
	    while ($val = mysql_fetch_assoc($res))
	    {
		if ($holder->delete($val['type_id'], $val['v_id']))
		{
		    $r = $this->kernel->doSql($this->dbh, "DELETE FROM vals_dictionary WHERE d_id=? AND type_id=? AND v_id = ?", $v_id, $val['type_id'], $val['v_id']);
		}
		else
		{
		    error_log('DictionaryHolder: Something wrong with Holder, because cannot delete!', 0);
		}
	    }
	    mysql_free_result($res);
	}
	return true;
    }
    
    function typeID()
    {
	# please use _helpers/value_type_id_creator/do
	# for create UNIQ ID for your type
	return 2917877050;# CRC32 for "NumberHolder"
    }

}
?>