<?php
require_once ("modules/TemplateResource.inc.php");
require_once ("modules/TemplateCreator.inc.php");
require_once ("modules/AuthCreator.inc.php");

class AuthComp
{
      var $out;
      
      function AuthComp($params)
      {
            $K = $params['_system']['KERNEL'];
            $TR = new TemplateResource($params['_system']['DBH']);
            $AC = $params['_system']['AUTHCREATOR'];
            $stat = $AC->status();
            $tmpl = '';
            if($stat == 'INITIALIZED')
            {
                  $source = $TR->getTemplate($params['source'].'_exit'); 
                  $tmpl = new TemplateCreator($source);
                  $tmpl->add('name', $AC->$user);
            }
            else if($stat == 'UNINITIALIZED' || $stat == 'TIMEOUT' || $stat == 'IPCHANGE')
            {
                  $source = $TR->getTemplate($params['source'].'_login');
                  $tmpl = new TemplateCreator($source);
            }
            $this->out = $tmpl;
      }

      function output()
      {
            return $this->out;
      }
}
?>