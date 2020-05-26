<?php
require_once ($path_correction."modules/TemplateResource.inc.php");
require_once ($path_correction."modules/TemplateCreator.inc.php");

class PageCreator
{
      var $out;

      function PageCreator($params, $path_correction)
      {
            $TR = new TemplateResource($params['_system']['KERNEL'], $params['_system']['DBH'], $path_correction);
            $source = $TR->getTemplate($params['source']);
            $tmpl = new TemplateCreator($source);
            $components = $tmpl->getSectionsNames();
            if ($components)
            {
                  foreach ($components as $comp)
                  {
                        $submasks = array();
                        if (preg_match("/([A-Za-z][A-Za-z0-9]*)_([A-Za-z][A-Za-z\d]*|[\d]+)/", $comp, $submasks))
                        {
                              $comp_par = $params['_system']['CONFINFO']->confParams($submasks[1], $submasks[2]); 
                              if($comp_par)
                              {
                                $CCreator = new ComponentCreator($comp_par, $path_correction);
                                $tmpl->add($comp, $CCreator->output());
                              }
                              else
                              {
                                error_log("PageCreator constructor: Configuration params not found- ".$submasks[2]." component - ".$submasks[1], 0); 
                              }
                        }
                        else
                        {
                              error_log("PageCreator: something with regexp!", 0); 
                        }
                  }
            }
            $this->out = $tmpl->output();
            
      }

      function output()
      {
            return $this->out;
      }
}
?>