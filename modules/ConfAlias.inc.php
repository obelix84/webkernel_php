<?php
class ConfAlias
{
    var $Kernel;
    var $dbh;
    
    function ConfAlias(&$Kernel, &$dbh)
    {
        $this->Kernel = $Kernel;
        $this->dbh = $dbh;
    }
    
    function aliasToConf($alias, $comp)
    {
        //хранится все в таблице confalias (alias, comp, conf)
        $res = $this->Kernel->doSql($this->dbh,"SELECT * FROM confalias WHERE alias=? AND comp=?", $alias, $comp);
        if (!$res)
        {
            error_log("ConfAlias: Something wrong with DB " . mysql_error()." !",0);
            return false;
        }
        else
        {
            $r = mysql_fetch_assoc($res);
            mysql_free_result($res);    
            if($r)
            {
                return $r['conf_id'];
            }
            else
            {
                return false;
            }
        }
    }
    
    function alias($comp, $conf)
    {
        $res = $this->Kernel->doSql($this->dbh,"SELECT * FROM confalias WHERE conf_id=? AND comp=?", $conf, $comp);
        if (!$res)
        {
            error_log("ConfAlias alias: Something wrong with DB " . mysql_error()." !",0);
            return false;
        }
        else
        {
            $r = mysql_fetch_assoc($res);
            mysql_free_result($res);
            return $r['alias'];
        }
    }
    
    function deleteAlias($comp, $conf)
    {
        $res = $this->Kernel->doSql($this->dbh,"DELETE FROM confalias WHERE  comp=? AND conf_id=?", $comp, $conf);
    }
    
    function addAliasToConf($alias, $comp, $conf)
    {
        $res = $this->Kernel->doSql($this->dbh,"INSERT INTO confalias (alias, comp, conf_id) VALUES (?, ?, ?)", $alias, $comp, $conf);
    }
}
?>