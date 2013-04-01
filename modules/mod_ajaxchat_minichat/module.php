<?php
function mod_ajaxchat_minichat($module_id)
{
  $inCore = cmsCore::getInstance();
  
  $smarty = $inCore->initSmarty('modules', 'mod_ajaxchat_minichat.tpl');
  $smarty->display('mod_ajaxchat_minichat.tpl');
  return true;
}
?>