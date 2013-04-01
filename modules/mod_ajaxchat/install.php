<?php
function info_module_mod_ajaxchat()
{
  $_module['title']        = 'Сообщение ajaxchat';
  $_module['name']         = 'Сообщение о количестве пользователей в ajaxchat';
  $_module['description']  = 'Показывает в ироничной форме количество пользователей в чате';
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
