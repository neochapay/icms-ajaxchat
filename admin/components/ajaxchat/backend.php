<?php
if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

cpAddPathway('Чат', '?view=components&do=config&id='.$_REQUEST['id']);

$inCore->loadModel('ajaxchat');
$model = new cms_model_ajaxchat();
echo '<h3>Чат</h3>';

if (isset($_REQUEST['opt'])) { $opt = $_REQUEST['opt']; } else { $opt = 'list'; }

$toolmenu = array();

$toolmenu[0]['icon'] = 'save.gif';
$toolmenu[0]['title'] = 'Сохранить';
$toolmenu[0]['link'] = 'javascript:document.optform.submit();';

$toolmenu[1]['icon'] = 'cancel.gif';
$toolmenu[1]['title'] = 'Отмена';
$toolmenu[1]['link'] = '?view=components';

cpToolMenu($toolmenu);

$GLOBALS['cp_page_head'][] = '<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>';
$GLOBALS['cp_page_head'][] = '<script type="text/javascript" src="/includes/jquery/tabs/jquery.ui.min.js"></script>';
$GLOBALS['cp_page_head'][] = '<link href="/includes/jquery/tabs/tabs.css" rel="stylesheet" type="text/css" />';


//LOAD CURRENT CONFIG
$cfg = $model->config;

//SAVE CONFIG
if($opt=='saveconfig')
{
    $cfg = array();
    $cfg['history_clear'] = $inCore->request('history_clear', 'int');
    $cfg['use_cron'] = $inCore->request('use_cron', 'int');
    $cfg['help'] = $inCore->request('help', 'html');
    
    $inCore->saveComponentConfig('ajaxchat', $cfg);
    $inCore->redirectBack();
}

$msg = cmsUser::sessionGet('ajaxchat_msg');

if ($msg) { echo '<p class="success">'.$msg.'</p>'; cmsUser::sessionDel('ajaxchat_msg'); }
?>

<form action="index.php?view=components&amp;do=config&amp;id=<?php echo $_REQUEST['id'];?>" method="post" name="optform" target="_self" id="optform">
  <div id="config_tabs" style="margin-top:12px;" class="uitabs">
    <ul id="tabs">
      <li><a href="#basic_t"><span>Общие</span></a></li>
      <li><a href="#help_t"><span>Помощь</span></a></li>
    </ul>
    <div id="basic_t">
      <table width="700" border="0" cellpadding="10" cellspacing="0" class="proptable">
	<tr>
	  <td width="250">
	    <strong>История: </strong><br/>
	    <span class="hinttext">
	      Сообщения какой давности будут удаляться из базы
	    </span>
	  </td>
	  <td valign="top">
	    <select name="history_clear" id="history_clear" style="width:350px">
	      <option value="0" <?php if ($cfg['history_clear']=='0'){?>selected="selected"<?php } ?>>Сохранять все сообщения</option>
	      <option value="1" <?php if ($cfg['history_clear']=='1'){?>selected="selected"<?php } ?>>Удалять сообщения старше одного дня</option>
	      <option value="7" <?php if ($cfg['history_clear']=='7'){?>selected="selected"<?php } ?>>Удалять сообщения старше недели</option>
	      <option value="30" <?php if ($cfg['history_clear']=='30'){?>selected="selected"<?php } ?>>Удалять сообщения старше месяца</option>
	      <option value="365" <?php if ($cfg['history_clear']=='365'){?>selected="selected"<?php } ?>>Удалять сообщения старше года</option>
	    </select>
	  </td>
	</tr>
	<tr>
	  <td width="250">
	    <strong>Обновления: </strong><br/>
	    <span class="hinttext">
	      Как будут происходить обновления
	    </span>
	  </td>
	  <td valign="top">
	    <select name="use_cron" id="use_cron" style="width:350px">
	      <option value="0" <?php if ($cfg['use_cron']=='0'){?>selected="selected"<?php } ?>>Автоматически (Большая нагрузка на БД)</option>
	      <option value="1" <?php if ($cfg['use_cron']=='1'){?>selected="selected"<?php } ?>>Через CRON (Необходима настройка)</option>
	    </select>
	  </td>
	</tr>      
      </table>
    </div>
    <div id="help_t">
      <table width="661" border="0" cellpadding="10" cellspacing="0" class="proptable">
	<tr>
	  <td width="250">
	    <strong>Помощь </strong><br/>
	    <span class="hinttext">
	      Сообщение которое будет выводиться когда пользователь введёт /help
	    </span>
	  </td>
	  <td valign="top">
	    <textarea name="help" id="help" style="width:100%;height:150px;"><?php print $cfg['help']?></textarea>
	  </td>
	</tr>
      </table>
    </div>  
  </div>
  <p>
    <input name="opt" type="hidden" value="saveconfig" />
    <input name="save" type="submit" id="save" value="Сохранить" />
    <input name="back" type="button" id="back" value="Отмена" onclick="window.location.href='index.php?view=components';"/>
  </p>
</form>

<!--<script type="text/javascript">
  $('#config_tabs > ul#tabs').tabs();
</script>-->