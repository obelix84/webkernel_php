<?php
require_once ($path_correction."modules/SessionCreator.inc.php");

class AuthCreator
{
    var $Kernel;
    var $dbh;
    var $session;
    var $status;
    var $user;
    
    function AuthCreator(&$Kernel, &$dbh)
    {
        $this->Kernel = $Kernel;
        $this->dbh = $dbh;
        $this->session = new SessionCreator($this->dbh, $this->Kernel, 600);
        $pass = $this->Kernel->args('pass');
        $login = $this->Kernel->args('login');
        $exit = $this->Kernel->args('exit');
        //если пришел логин и пароль
        if (!is_null($login) && !is_null($pass) && $login && $pass)
        {
            //echo "1 <br>";
            $u_id = $this->testUser($login, $pass);
            if(!is_null($u_id))
            {//есть такой, значит надо делать сессию
                $this->session->start($u_id, $pass);
                $this->status = $this->session->status();
                $this->user = $this->getUser($u_id);
            }
        }
        else if (!is_null($exit) && !$exit)
        {//кто-то нажал кнопку выхода
            $this->status = $this->session->status();
            if ($this->session->isSession())
            {
                $this->session->stop();
            }
        }
        else if ($this->session->isSession())
        {
            $this->status = $this->session->status();    
            $this->user = $this->getUser($u_id);    
        }
        else if (!$this->session->isSession())
        {
            $this->status = $this->session->status();
            if ($this->session->status() == 'IPCHANGE' || $this->session->status() == 'TIMEOUT')
            {
                $this->user = null;  
            }
            else if($this->session->status() == 'UNINITIALIZED')
            {
                $this->user = null; 
            }
        }
    }
    
    function status()
    {
        return $this->status;
    }
    
    //Возвращаем пользователя залогиненного сейчас
    function getUser($u_id)
    {
        return 'root';
    }
    //Временное решение проблемы авторизации пользователей
    function testUser($login, $pass)
    {
        if($login == 'root')
        {
            if ($pass == 'qwerty')
            {
                return 0;
            }
        }
        return null;
    }
    
    function isSession()
    {
        return $this->user;
    }
}
?>
