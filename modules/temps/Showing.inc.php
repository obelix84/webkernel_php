<?php
require_once ("modules/GetTemplate.inc.php");
require_once ("modules/TemplatesCreator.inc.php");

class Showing
{
      var $out;

      function Showing($params)
      {
            //достаем шаблон..
            $getTempl = new GetTemplate($params['_system']['DBH']);
            $source = $getTempl->template($params['source']);
            $tmpl = "";
            //полную надо генерить или нет?
            if(is_null($params['_system']['kernel']->args('full')))
            {
                  $tmpl = new TemplateCreator($source, 'thumb');
                  $tmpl->add('sex',$params['sex']);
                  $tmpl->add('color',$params['color']);
                  $tmpl->add('age',$params['age']);
                  //разделим их на кусочки по ';'
                  $arr = split(';', $params['fotos']);
                  $tmpl->add('imghref', $arr[0]);
                  //ссылку на полный
            }
            else
            {
                  $tmpl = new TemplateCreator($source, 'full');
            }
            $this->out = $tmpl->output();

      }

      function output()
      {
            return $this->out;
      }
}
?>


