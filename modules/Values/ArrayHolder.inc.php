<?php
require_once("modules/Values/ValueHolder.inc.php");

class ArrayHolder
{
    var $kernel;
    var $dbh;
    
    function ArrayHolder(&$kernel, &$dbh)
    {
	$this->kernel = $kernel;
	$this->dbh = $dbh;
    }

    function &value($v_id)
    {
	$holder = new ValueHolder($this->kernel, $this->dbh);    
	$val_query = $this->kernel->doSql($this->dbh, "SELECT type_id, v_id FROM vals_array WHERE a_id = ? ", $v_id);
	if (! $val_query)
	{
		error_log("ArrayHolder: Something wrong with DB!!!",0);
	}
	else
	{
		$ret = array();
		while($val = mysql_fetch_assoc($val_query))
		{
			$value = $holder->value($val['type_id'], $val['v_id']);
			array_push($ret, $value);	
		}
    		return $ret;
	}
	return undef;
    }
    
    function setValue($ref_data)
    {
	$holder = new ValueHolder($this->kernel, $this->dbh);
	$a_id = $this->kernel->getSeqNextVal($this->dbh, 'seq_vid_array');
	$ord = 0;
	for ($i = 0; $i<count($ref_data); $i++)
	{
	    $id = $this->kernel->getSeqNextVal($this->dbh, 'seq_id_array');
	    $arr = $holder->setValue($ref_data[$i]);
	    $res = $this->kernel->doSql($this->dbh, "INSERT INTO vals_array(id, a_id, ord, type_id, v_id) VALUES (?, ?, ?, ?, ?)", $id, $a_id, $ord, $arr[0], $arr[1]);
	    $ord++;
	    if (!$res)
	    {
	 	error_log("ArrayHolder: Something wrong with DB!", 0);
		return -1;  
	    } 
	}
	return array($this->typeID(), $a_id);
    }
    
    function delete($v_id)
    {
        $holder = new ValueHolder($this->kernel, $this->dbh);
	$res = $this->kernel->doSql($this->dbh, "SELECT * FROM vals_array WHERE a_id = ? ORDER BY ord", $v_id);
	if (!$res)
	{
	    error_log("ArrayHolder: Something wrong with DB!", 0);
	    return false;  
	}
	else
	{
	    while ($val = mysql_fetch_assoc($res))
	    {
		if ($holder->delete($val['type_id'], $val['v_id']))
		{
		    $r = $this->kernel->doSql($this->dbh, "DELETE FROM vals_array WHERE a_id=? AND type_id=? AND v_id = ?", $v_id, $val['type_id'], $val['v_id']);
		}
		else
		{
		    error_log('ArrayHolder: Something wrong with Holder, because cannot delete!', 0);
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
	return 1440690368;# CRC32 for "NumberHolder"
    }

}
?>