<?php
/*
CREATE TABLE `session` (
  `s_id` varchar(200) NOT NULL,
  `ip` varchar(70) NOT NULL,
  `u_id` int(10) unsigned NOT NULL,
  `pass` varchar(200) NOT NULL,
  `entry` int(11) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY  (`s_id`),
  KEY `u_id` (`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
class SessionCreator
{
	var $status;
	/*
	 UNINITIALIZED - no session
	 INITIALIZED - true session, prolongation
	 TIMEOUT - time out, plaese relogin
	 IPCHANGE - changing ip address, please relogin
	*/
	var $sessionTime;
	var $dbh;
	var $K;
	var $s_id;
	var $u_id;
	var $ip;
	var $pass;
	var $expiration;
	var $entry;

	function SessionCreator(&$dbh, &$K, $stime)
	{
		$this->sessionTime = $stime;
		$this->dbh = $dbh;
		$this->K = $K;
		$this->status = 'UNINITIALIZED';
		$this->clearExpiredSessions();
	}

	function status()
	{
		return $this->status;
	}
	
	function isSession()
	{
		$curtime = time();
		if (array_key_exists('s_id', $_COOKIE))
		{
			$res = $this->K->doSql($this->dbh, 'SELECT * FROM session WHERE s_id = ?', $_COOKIE['s_id']);	
			if ($res)
			{
				while($r = mysql_fetch_assoc($res))
				{
					if (($r['expiration'] > $curtime) && ($r['ip'] == $_SERVER['REMOTE_ADDR']))
					{
						$this->status = 'INITIALIZED';	
						$this->ip = $r['ip'];
						$this->s_id = $r['s_id'];
						$this->entry = $r['entry'];
						$this->expiration = $r['expiration'];
						$this->pass = $r['pass'];
						$this->prolongation();
						return true;
					}
					if (($r['expiration'] <= $curtime))
					{
						$this->status = 'TIMEOUT';
						$this->stop();
						return false;
					}
					if (($r['ip'] != $_SERVER['REMOTE_ADDR']))
					{
						$this->status = 'IPCHANGE';
						$this->stop();
						return false;
					}
				}
			}
			else
			{
				error_log("SessionCreator.isSession: something with DB! " . mysql_error(),0);	
			}
			
		}	
		return false; 
	}

	//устанавливает куки и заносит в базу
	function start($u_id, $pass)
	{
		$res = $this->K->doSql($this->dbh, 'SELECT * FROM session WHERE u_id = ? AND pass = ? ', $u_id, $pass);	
		$ok = 1;
		if ($res)
		{
			$r = mysql_fetch_assoc($res);
			if(!is_null($r) && $r)
			{
				$this->s_id = $r['s_id'];
				$this->stop();
			}
		}
		$curtime = time();
		$s_id = $this->generateRandString();
		$this->K->doSql($this->dbh, 'INSERT INTO session (s_id, ip, u_id, pass, entry, expiration) VALUES (?, ?, ?, ?, ?, ?)', $s_id, $_SERVER['REMOTE_ADDR'], $u_id,  $pass, $curtime, $curtime + $this->sessionTime);
		setcookie('s_id', $s_id);
		$this->status = 'INITIALIZED';	
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->s_id = $s_id;
		$this->entry = $curtime;
		$this->expiration = $curtime + $this->sessionTime;
		$this->pass = $pass;
	}
	
	function prolongation()
	{
		if ($this->status == 'INITIALIZED')
		{
			$this->K->doSql($this->dbh, 'UPDATE session SET expiration = ? WHERE s_id = ?', time() + $this->sessionTime, $this->s_id);
		}
		else
		{
			error_log("SessionCreator.prolongation: Session is not active! ", 0);	
		}
	}

	function stop()
	{
		$this->K->doSql($this->dbh, 'DELETE FROM session WHERE s_id = ?', $this->s_id);
	}

	function generateRandString()
	{
		return md5(uniqid(rand(), true));
	}
	
	function clearExpiredSessions()
	{
		$this->K->doSql($this->dbh, 'DELETE FROM session WHERE expiration <= ?', time());
	}
}
?>