<?php
require_once($path_correction.'modules/Values/ValueHolder.inc.php');
class ConfInfo
{
    //?? ???? ???????? Kernel ? ????????? ?? ????
        var $Kernel;
        var $dbh;
        var $cAl;
	var $holder;

        function ConfInfo(&$Kernel, &$dbh, &$cAl)
        {
                $this->Kernel = $Kernel;
                $this->dbh = $dbh;
                $this->cAl = $cAl;
		$this->holder = new ValueHolder($Kernel, $dbh);
        }

        function confParams($compname,$conf_id_or_alias)
        {
                //resolving alias
                $conf_id = null;
                if (preg_match("/^[\d]+/", $conf_id_or_alias))
                {
			$conf_id = $conf_id_or_alias;
                }
                else
                {
                        $conf_id = $this->cAl->aliasToConf($conf_id_or_alias, $compname);
                }
                $ret = $this->Kernel->doSql($this->dbh, "SELECT * FROM conf_params WHERE conf_id=?", $conf_id);
		$params = null;
		if(!$ret)
                {
                     error_log("ConfInfo: Something wrong with DB!",0);
                     return false;
                }
                else
                {
                    $r = mysql_fetch_assoc($ret);
                    mysql_free_result($ret);
		    $params = $this->holder->value($r['type_id'], $r['v_id']);
		    if($params)
                    {
                        $params['_system']['KERNEL'] = $this->Kernel;
                	$params['_system']['CONFID'] = $conf_id;
                	$params['_system']['COMPNAME'] = $compname;
                	$params['_system']['DBH'] = $this->dbh;
                	$params['_system']['CONFALIAS'] = $this->cAl;
                	$params['_system']['CONFINFO'] = &$this;
			return $params;
                    }
		}
		return false;
	}

        function setConfParams()
        {
		$args = func_get_args();
		$params = array_shift($args);
		$conf_id = array_shift($args);
		if (!is_null($conf_id))
                {
			$this->deleteConfParams($conf_id);
		}
                else
                {
			$conf_id = $this->Kernel->getSeqNextVal($this->dbh, "seq_confid");
                }
                $val = $this->holder->setValue($params);
                $ret = $this->Kernel->doSql($this->dbh, "INSERT INTO conf_params VALUES (?,?,?)", $conf_id, $val[0], $val[1]);
                return $conf_id;
        }
  
        function deleteConfParams($conf_id)
        {
	      $ret = $this->Kernel->doSql($this->dbh, "SELECT * FROM conf_params WHERE conf_id=?", $conf_id);
              if (!$ret)
              {
                   error_log("ConfInfo: Something wrong with DB! " .mysql_error() ,0);
                   return false;
	      }
	      else
	      {
		      $r = mysql_fetch_assoc($ret);
		      $this->holder->delete($r['type_id'], $r['v_id']);
		      mysql_free_result($ret);
	      }
	      $ret = $this->Kernel->doSql($this->dbh, "DELETE FROM conf_params WHERE conf_id=?", $conf_id);
              if (!$ret)
              {
                   error_log("ConfInfo: Something wrong with DB! " .mysql_error() ,0);
                   return false;
              }
              return true;
        }
}
?>