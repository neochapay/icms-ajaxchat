<?php
function ajaxchat()
{
  $inCore = cmsCore::getInstance();
  $inPage = cmsPage::getInstance();
  $inPage->addPathway("Чат", "/ajaxchat"); 
  $inUser = cmsUser::getInstance();
  $inDB = cmsDatabase::getInstance();
  $inCore->loadModel('ajaxchat');

  $model = new cms_model_ajaxchat();

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
	
	$bb_toolbar = cmsPage::getBBCodeToolbar('chatText', TRUE, 'forum', 'post');
        $smilies    = cmsPage::getSmilesPanel('chatText');
	
	$model->updateActive($inUser->id,1);
	
	$model->UpdateOnlineList($inUser->id);
	$smarty = $inCore->initSmarty('components', 'com_ajaxchat_view.tpl');
	$smarty->assign('bb_toolbar', $bb_toolbar);
	$smarty->assign('smilies', $smilies);
	$smarty->assign('colors', $model->allColors());
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
    $smarty = $inCore->initSmarty('components', 'com_ajaxchat_history.tpl');
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
    if($inUser->id)
    {
      if(!$model->isBanned($inUser->id) or $inUser->is_admin)
      {
	$model->UpdateOnlineList($inUser->id);
	$online = $model->getOnline();
	print json_encode($online);
      }
      else
      {
	echo "ACCESS ERROR";
      }
    }
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
      print json_encode($output);
    }
    exit;
  }
  
  if($do == "send_message")
  {
    if($inUser->id)
    {
      $message = $inCore->request('message', 'html','');
      $id = $inCore->request('id', 'str');
      if($id == "chatrum")
      {
	if(preg_match_all('((?:\\/[\\w\\.\\-]+)+)',$message, $matches))
	{
	  $command = $matches[0][0];
	  $command_raw = explode(" ",$message);
	  $target = $command_raw[1];

	  if($command == "/to")
	  {
	    str_replace(":","",$target);
	    $user = $model->getUser(trim($target));
	    if($user and $user['id'] != $inUser->id)
	    {
	      $to_id = $user['id'];
	    }
	    else
	    {
	      $to_id = 0;
	    }
	  }
	  elseif($command == "/bann")
	  {
	    if($inUser->is_admin)
	    {
	      $user = $model->getUser($target);
	      if($user and $user['id'] != $inUser->id)
	      {
		$model->addToBan($user['id']);
		$model->addMessage(0,0,"Администратор забанил ".$user['nickname']);
	      }
	    }
	    unset($message);
	    print "user is banned.";
	    exit;
	  }
	  elseif($command == "/unbann")
	  {
	    if($inUser->is_admin)
	    {
	      $user = $model->getUser($target);
	      if($user and $user['id'] != $inUser->id)
	      {
		$model->removeFromBan($user['id']);
		$model->addMessage(0,0,"Администратор разбанил ".$user['nickname']);
	      }
	    }
	    unset($message);
	    print "pass";
	    exit;	  
	  }
	  elseif($command == "/me")
	  {
	    $string = trim(str_replace($command,"",$message));
	    $model->addMessage(0,0,$inUser->nickname." ".$string);
	    unset($message);
	    print "pass";
	    exit;
	  }
	  elseif($command == "/color")
	  {
	    $model->changeColor($inUser->id);
	    unset($message);
	  }
	}
	else
	{
	  $message = $inCore->request('message', 'html', '');
	}
      
      
	if(strlen($message) >= 2)
	{
	  if(!$model->isBanned($inUser->id) or $inUser->is_admin)
	  {
	    $message = $inCore->parseSmiles($message, true);
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
    $skipsystem = $inCore->request('skipsystem', 'int');
    if(!$model->isBanned($inUser->id) or $inUser->is_admin)
    {
      $output['messages'] = $model->getNewMessages($last_id,$inUser->id,$skipsystem);
      $output['dialogs'] = $model->getDialogs($inUser->id);
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
    
    $companion = $model->getUserByID($companion_id);

    $output = array();
    $output['user'] = $model->getUserByID($companion_id);
    $output['messages'] = $model->getDialog($inUser->id,$companion_id);
    print json_encode($output);
    $model->readDialog($inUser->id,$companion_id);
    exit;
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
  
  if($do == "get_help")
  {
    print str_replace("\n","<br />",$cfg['help']);
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
