<?php
class ComponentCreator
{
      var $component;

      function ComponentCreator($params, $path_correction)
      {
            $comp = $params['_system']['COMPNAME'];
            require_once("modules/".$comp.".inc.php");
            $c = '';
            if($comp == 'PageCreator')
            {
                  eval("\$c = new ${comp}(\$params, \$path_correction);");
            }
            else
            {
                  eval("\$c = new ${comp}(\$params);");
            }
            $r = $c->output();
            $this->component = $c;
      }

      function output()
      {
            return $this->component->output();
      }
      
}
?>