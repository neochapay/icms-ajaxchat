<?php
function ajaxchat()
{
  $inCore = cmsCore::getInstance();
  $inPage = cmsPage::getInstance();
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
	if(!$model->CheckOnline($inUser->id))
	{
	  $model->addMessage(0,0,"К чату присоединяется ".$inUser->nickname);
	}
	$model->UpdateOnlineList($inUser->id);
	$smarty = $inCore->initSmarty('components', 'com_ajaxchat_view.tpl');
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
      cmsCore::addSessionMessage('Для входа в чат необходимо зайти на сайт', 'error');
      $inCore->redirect("/login");
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
    if($inUser->id)
    {
      if(!$model->isBanned($inUser->id) or $inUser->is_admin)
      {
      	$messages = $model->getMessages($skipsystem,$count);
      	if($messages)
      	{
	  print json_encode($messages);
	}
	else
	{
	  $messages = array();
	  print json_encode($messages);
	}
      }
      else
      {
	$messages['error'] = true;
	$messages['error_message'] = "Ошибка доступа";
	print json_encode($messages);
      }
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
      exit;
    }
    else
    {
      $companion_id = str_replace("open_","",$id);
      if($companion_id and is_numeric($companion_id))
      {
      }
    }
  }
  
  if($do == "load_new")
  {
    $last_id = $inCore->request('last_id', 'int');
    $skipsystem = $inCore->request('skipsystem', 'int');
    if($last_id)
    {
      if(!$model->isBanned($inUser->id) or $inUser->is_admin)
      {
	$messages = $model->getNewMessages($last_id,$inUser->id,$skipsystem);
      }
      else
      {
	$messages['error'] = 1;
	$messages['error_message'] = "Вы забанены";
      }
      print json_encode($messages);
    }
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
    $companion_id = str_replace("dialog_","",$companion);
    if(!$inUser->id)
    {
      exit;
    }
    
    if($companion_id == 0)
    {
      $companion_id = "-1";
    }
    
    $companion = $model->getDialogUser($companion_id);
    $author = $model->getDialogUser($inUser->id);
    $messages = $model->getDialog($inUser->id,$companion_id);
    
    $output = array();
    $output['companion'] = $companion;
    $output['companion']['nickname'] = $companion['nickname'];
    $output['messages'] = $messages;
    
    print json_encode($output);
    
    exit;
  }
  
  if($do == "get_help")
  {
    print $cfg['help'];
    exit;
  }
}
?>