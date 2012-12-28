<?php
function info_module_mod_ajaxchat()
{
  $_module['title']        = 'Сообщение ajaxchat';
  $_module['name']         = 'Сообщение ajaxchat';
  $_module['description']  = 'Сообщение ajaxchat';
  $_module['link']         = 'mod_ajaxchat';
  $_module['position']     = 'sidebar';
  $_module['author']       = 'NeoChapay';
  $_module['version']      = '1';
  return $_module;
}

function install_module_mod_ajaxchat()
{
  return true;
}

function upgrade_module_mod_ajaxchat()
{
  return true;
}
?>
