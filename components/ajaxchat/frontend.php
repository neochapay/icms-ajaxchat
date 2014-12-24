<?php
function ajaxchat()
{
  $inCore = cmsCore::getInstance();
  $inPage = cmsPage::getInstance();
  $inPage->addPathway("Чат", "/ajaxchat"); 
  $inUser = cmsUser::getInstance();
  $inDB = cmsDatabase::getInstance();
  $inCore->loadModel('ajaxchat');
  $inCore->loadModel('users');

  $model = new cms_model_ajaxchat();
  $usersModel = new cms_model_users();

  $do = $inCore->request('do', 'str', 'view');

  $cfg = $inCore->loadComponentConfig('ajaxchat');
  
  if($do == "view")
  {
    if($inUser->id)
    {
      if(!$model->isBanned($inUser->id) or $inUser->is_admin)
      {
	$inPage->setTitle("Чат");
	if(!$model->CheckUser($inUser->id))
	{
	  $model->addMessage(0,0,"К чату присоединяется ".$inUser->nickname);
	}
	$model->updateActive($inUser->id,1);

	$model->UpdateOnlineList($inUser->id);
	$smarty = cmsPage::initTemplate('components', 'com_ajaxchat_view.tpl');
	$smarty->assign('colors', $model->allColors());
	$smarty->assign('smiles', $model->getSmiles());
	$smarty->assign('user_color', $model->getUserColor($inUser->id));
	$smarty->display('com_ajaxchat_view.tpl');
      }
      else
      {
	cmsCore::addSessionMessage('Вы забанены в чате', 'error');
	$inCore->redirect("/users/".$inUser->login);
      }
      return;
    }
    else
    {
      cmsCore::addSessionMessage('Для того, чтобы воспользоваться чатом, Вам необходимо войти на сайт под своим аккаунтом', 'error');
      $inCore->redirect("/login");
    }
  }
  
  if($do == "history")
  {
    $page = $inCore->request('page', 'int', 0);
    if($page < 1)
    {
      $page = 1;
    }
    $inPage->addPathway("Чат", "/ajaxchat");
    $inPage->addPathway("История");
    
    $total = $model->totalMessages(TRUE);
    
    $pagination = cmsPage::getPagebar($total, $page, 50, '/ajaxchat/history%page%.html', array());
    
    $messages = $model->getMessages(TRUE,50,($page-1)*50);
    $messages = array_reverse($messages);
    $smarty = cmsPage::initTemplate('components', 'com_ajaxchat_history.tpl');
    $smarty->assign('messages', $messages);
    $smarty->assign('is_admin',$inUser->is_admin);
    $smarty->assign('pagination',$pagination);
    $smarty->display('com_ajaxchat_history.tpl');
  }
  
  if($do == "delete_mess")
  {
    $id = $inCore->request('id', 'int', 0);
    if(!$inUser->is_admin or !$id)
    {
      $inCore->redirectBack();
    }
    else
    {
      $model->deleteMessage($id);
      $inCore->redirectBack();
    }
  }
  
  if($do == "get_userlist")
  {
    $model->UpdateOnlineList($inUser->id);
    $online = $model->getOnline();
    print json_encode($online);
    exit;
  }
  
  if($do == "me")
  {
    $output['id'] = $inUser->id;
    $output['login'] = $inUser->login;
    $output['nickname'] = $inUser->nickname;
    print json_encode($output);
    exit;
  }
  
  if($do == "get_messages")
  {
    $skipsystem = $inCore->request('skipsystem', 'int');
    $count = $inCore->request('count', 'int');
    if($model->isBanned($inUser->id))
    {
      $messages['error'] = true;
      $messages['error_message'] = "Ошибка доступа";
      print json_encode($messages);
    }
    else
    {
      $output = array();
      $output['skipsystem'] = $skipsystem;
      $output['messages'] = $model->getMessages($skipsystem,$count);
      $output['dialogs'] = $model->getDialogs($inUser->id);
      print json_encode($output);
    }
    exit;
  }
  
  if($do == "send_message")
  {
    if($inUser->id)
    {
      $message = $inCore->request('message', 'html','');
      
      $message = urldecode($message);
      preg_match_all("#<b(.*)</b>#Uis", $message, $string);
      $i = 0;

      foreach($string[0] as $rstring)
      {
	preg_match_all("#login=\"(.*)\"#Uis", $rstring, $ostring);
	$message = str_replace($string[0][$i],"@".$ostring[1][0]." ",$message);
	$i++;
      }
      $message = str_replace("&nbsp;"," ",$message);
      $message = mb_substr($message, 1, -1);
      
      $id = $inCore->request('id', 'str');
      if($id == "chatrum")
      {
	if(strlen($message) >= 2)
	{
	  if(!$model->isBanned($inUser->id) or $inUser->is_admin)
	  {
	    $model->addMessage($inUser->id,$to_id,$message);
	    print "pass";
	  }
	  else
	  {
	    echo "ACCESS ERROR";
	  }
	}
	else
	{
	  echo "pass";
	}
      }
      else
      {
	$companion_id = str_replace("open_","",$id);
	if($companion_id and is_numeric($companion_id))
	{
	  $inUser->sendMessage($inUser->id,$companion_id,$message);
	  echo "pass";
	}
      }
      exit;
    }
    echo "ACCESS ERROR";
    exit;
  }
  
  if($do == "load_new")
  {
    $last_id = $inCore->request('last_id', 'int');
    $dialog = $inCore->request('dialog', 'str');
    
    if($inUser->id)
    {
      $model->updateActive($inUser->id,1);
    }
    
    if(!$last_id)
    {
      $output['error'] = 1;
      $output['error_message'] = "Неверные данные";
    }
    $skipsystem = $inCore->request('skipsystem', 'int');
    if(!$model->isBanned($inUser->id) or $inUser->is_admin)
    {
      $output['messages'] = $model->getNewMessages($last_id,$inUser->id,$skipsystem);
      $output['dialogs'] = $model->getDialogs($inUser->id);
      $output['converstation'] = $model->getDialog($inUser->id,$dialog);
    }
    else
    {
      $output['error'] = 1;
      $output['error_message'] = "Вы забанены";
    }
    print json_encode($output);
    exit;
  }
  
  if($do == "get_dialogs")
  {
    $messages = $model->getMessageList($inUser->id);
    
    function cmp($a, $b)
    {
      if ($a['senddate'] == $b['senddate']) {
        return 0;
      }
      return ($a['senddate'] > $b['senddate']) ? -1 : 1;
    } 
 
    usort($messages, "cmp"); 
    
    $messages_ = array();
    foreach($messages as $row)
    {
      if($row['count'] != 0)
      {
	$row['senddate'] = date("H:i d-m-Y",$row['senddate']);
	$messages_[] = $row;
      }
    }
    
    print json_encode($messages_);
    exit;
  }
  
  if($do == "get_converstation")
  {
    $companion = $inCore->request('id', 'str');
    $companion_id = str_replace("open_","",$companion);
    if(!$inUser->id)
    {
      exit;
    }
    
    if($companion_id == 0)
    {
      $companion_id = "-1";
    }
    
    $companion = $inUser->loadUser($companion_id);

    $output = array();
    $output['user'] = $inUser->loadUser($companion_id);
    $output['messages'] = $model->getDialog($inUser->id,$companion_id);
    print json_encode($output);
    //$model->readDialog($inUser->id,$companion_id);
    exit;
  }
  
  if($do == "read_pmessage")
  {
    $id = $inCore->request('id', 'int');
    $model->readPMessage($id,$inUser->id);
  }

  if($do == "userstatus")
  {
    $status = $inCore->request('status', 'str');
    if($status == "online")
    {
      $on_chat = 1;
    }
    else
    {
      $on_chat = 0;
    }
    
    $model->updateActive($inUser->id,$on_chat);
    exit;
  }
  
  if($do == "set_color")
  {
    if(!$inUser->id)
    {
      exit;
    }
    
    $color = $inCore->request('color', 'str');
    if(array_search($color,$model->allColors()))
    {
      $model->setUserColor($inUser->id,$color);
    }
    print $inUser->login;
  }
  
  if($do == "get_color")
  {
    $login = $inCore->request('login', 'str');
    $user = $usersModel->getUser($login);
    print $model->getUserColor($user['id']);
  }
  
  if($do == "clear")
  {
    $model->clearOld($cfg['history_clear']);
    exit;
  }
  
  if($do == "cron")
  {
    if($cfg['use_cron'])
    {
      $model->ClearOnline();
    }
    exit;
  }
}
?>
