<?php
function info_module_mod_ajaxchat_minichat()
{
  $_module['title']        = 'Миничат';
  $_module['name']         = 'Показывает миничат в виде модуля';
  $_module['description']  = 'Пока';
  $_module['link']         = 'mod_ajaxchat_minichat';
  $_module['position']     = 'sidebar';
  $_module['author']       = 'NeoChapay';
  $_module['version']      = '1';
  return $_module;
}

function install_module_mod_ajaxchat_minichat()
{
  return true;
}

function upgrade_module_mod_ajaxchat_minichat()
{
  return true;
}
?>
