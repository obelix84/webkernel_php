<?php
class NumberHolder
{
    var $kernel;
    var $dbh;
    
    function NumberHolder(&$kernel, &$dbh)
    {
	$this->kernel = $kernel;
	$this->dbh = $dbh;
    }
    
    function &value($v_id)
    {
	$val_query = $this->kernel->doSql($this->dbh, "SELECT val FROM vals_number WHERE v_id = ? ", $v_id);
	if (! $val_query)
	{
	    error_log("NumberHolder: Something wrong with DB!!!",0);
	}
	else
	{
	    $val = mysql_fetch_assoc($val_query);
	    if ($val)
	    {
	    	$ret = $val['val'];
		mysql_free_result($val_query);
	    	return $ret;
	    }
	}
	return false;
    }
    
    function setValue($ref_data)
    {
        $v_id = $this->kernel->getSeqNextVal($this->dbh,'seq_vid_number');
        $res = $this->kernel->doSql($this->dbh, "INSERT INTO vals_number( v_id, val ) VALUES (?, ?)", $v_id, $ref_data);
	if (!$res)
	{
	 	error_log("NumberHolder: Something wrong with DB!", 0);
		return -1;  
	}
	return array($this->typeID(), $v_id);
    }
    
    function delete($v_id)
    {
        $res = $this->kernel->doSql($this->dbh, "DELETE FROM vals_number WHERE v_id= ?", $v_id);
	if (!$res)
	{
	 	error_log("StringHolder: Something wrong with DB!", 0);
		return false;  
	}
	return true;
    }
    
    function typeID()
    {
	# please use _helpers/value_type_id_creator/do
	# for create UNIQ ID for your type
	return 4165261607;# CRC32 for "NumberHolder"
    }

}
?>