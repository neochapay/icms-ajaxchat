<?php
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

class cms_model_ajaxchat
{
  function __construct()
  {
    $this->inDB = cmsDatabase::getInstance();
    $this->inUser = cmsUser::getInstance();
    $this->config = cmsCore::getInstance()->loadComponentConfig('ajaxchat');
  }
  
  public function getDefaultConfig() 
  {
    $cfg = array();
    $cfg['history_clear'] = 0;
    $cfg['use_cron'] = 0;
    $cfg['help'] = "Доступные команды:\n/me - Сообщение в третьем лице\n";
    return $cfg;
  }

  public function allColors()
  {
    $colors = array();
    $colors[] = '#996600';
    $colors[] = '#cc9900';
    $colors[] = '#ff3300';
    $colors[] = '#990000';
    $colors[] = '#003399';
    $colors[] = '#0066cc';
    $colors[] = '#0083d7';
    $colors[] = '#3e9ade';
    $colors[] = '#6666cc';
    $colors[] = '#666699';
    $colors[] = '#006600';
    $colors[] = '#009900';
    $colors[] = '#66cc33';  
    $colors[] = '#000080';
    $colors[] = '#00008B';
    $colors[] = '#4B0082';
    $colors[] = '#191970';
    $colors[] = '#800000';
    $colors[] = '#800080';
    $colors[] = '#8B0000';
    $colors[] = '#0000CD';
    $colors[] = '#8B008B';
    $colors[] = '#006400';
    $colors[] = '#2F4F4F';
    $colors[] = '#8B4513';
    $colors[] = '#0000FF';
    $colors[] = '#008000';
    $colors[] = '#9400D3';
    $colors[] = '#008080';
    $colors[] = '#483D8B';
    $colors[] = '#556B2F';
    $colors[] = '#808000';
    $colors[] = '#A52A2A';
    $colors[] = '#008B8B';
    $colors[] = '#B22222';
    $colors[] = '#A0522D';
    $colors[] = '#228B22';
    $colors[] = '#C71585';
    $colors[] = '#696969';
    $colors[] = '#6B8E23';
    $colors[] = '#DC143C';
    $colors[] = '#2E8B57';
    $colors[] = '#B8860B';
    $colors[] = '#9932CC';
    $colors[] = '#FF0000';
    $colors[] = '#D2691E';
    $colors[] = '#8A2BE2';
    $colors[] = '#FF00FF';
    $colors[] = '#FF00FF';
    $colors[] = '#FF4500';
    $colors[] = '#6A5ACD';
    $colors[] = '#FF1493';
    $colors[] = '#4169E1';
    $colors[] = '#CD5C5C';
    $colors[] = '#808080';
    $colors[] = '#708090';
    $colors[] = '#4682B4';
    $colors[] = '#FF8C00';
    $colors[] = '#20B2AA';
    $colors[] = '#CD853F';
    $colors[] = '#BA55D3';
    $colors[] = '#1E90FF';
    $colors[] = '#00CED1';
    $colors[] = '#778899';
    $colors[] = '#DAA520';
    $colors[] = '#FFA500';
    $colors[] = '#3CB371';
    $colors[] = '#5F9EA0';
    $colors[] = '#00BFFF';
    $colors[] = '#FF6347';
    $colors[] = '#DB7093';
    $colors[] = '#7B68EE';
    $colors[] = '#9370DB';
    $colors[] = '#32CD32';
    $colors[] = '#FF7F50';
    $colors[] = '#DA70D6';
    $colors[] = '#FF0000'; 
    $colors[] = '#00FF00'; 
    return $colors;
  }
  
  public function getColor()
  {
    //Определяем цвета сообщений чата
    
    $colors = $this->allColors();
    
    $color_count = count($colors);
    $color_rnd = rand(0,$color_count-1);
    $color = $colors[$color_rnd];
    return $color;
  }
  
  public function getUser($login)
  {
    $sql = "SELECT * FROM cms_users WHERE login = '".$login."'";
    $result = $this->inDB->query($sql);
    if($this->inDB->error())
    {
      return FALSE;
    }
    
    if(!$this->inDB->num_rows($result))
    {
      return FALSE;
    }    
    return $this->inDB->fetch_assoc($result);
    
  }
  
  public function changeColor($user_id)
  {
    $color = $this->getColor();
    $sql = "UPDATE cms_ajaxchat_users SET `color` = '$color' WHERE `user_id` = '$user_id'";
    $result = $this->inDB->query($sql);
    return TRUE;
  }
  
  public function CheckOnline($user_id)
  {
    $sql = "SELECT * FROM cms_ajaxchat_users WHERE user_id = $user_id AND online = 1";
    $result = $this->inDB->query($sql);
    
    if($this->inDB->error())
    {
      return FALSE;
    }
    
    if(!$this->inDB->num_rows($result))
    {
      return FALSE;
    }
    return TRUE;    
  }
  
  public function CheckUser($user_id)
  {
    $sql = "SELECT * FROM cms_ajaxchat_users WHERE user_id = $user_id AND online = 1";
    $result = $this->inDB->query($sql);
    
    if($this->inDB->error())
    {
      return FALSE;
    }
    
    if(!$this->inDB->num_rows($result))
    {
      return FALSE;
    }
    return TRUE;
  }
  
  public function totalMessages($skipsystem)
  {
    if(!$skipsystem)
    {
      $sql = "SELECT * FROM cms_ajaxchat_messages";
    }
    else
    {
      $sql = "SELECT * FROM cms_ajaxchat_messages WHERE user_id <> 0";
    }
    $result = $this->inDB->query($sql);
    
    if($this->inDB->error())
    {
      return FALSE;
    }
    
    return $this->inDB->num_rows($result);    
  }
  
  public function deleteMessage($id)
  {
    $sql ="DELETE FROM cms_ajaxchat_messages WHERE id = $id LIMIT 1";
    $result = $this->inDB->query($sql);
    
    if($this->inDB->error())
    {
      return FALSE;
    }
    return TRUE;
  }
  
  public function ClearOnline()
  {
    $sql = "UPDATE cms_ajaxchat_users SET `online` = '0', `on_chat` = '0' WHERE last_action < NOW() - INTERVAL 1 MINUTE";
    $result = $this->inDB->query($sql);
      
    if($this->inDB->error())
    {
      return FALSE;
    }
    return TRUE;    
  }
  
  public function updateActive($user_id,$on_chat)
  {
    $sql = "UPDATE cms_ajaxchat_users SET `on_chat` = '$on_chat' WHERE `user_id` = '$user_id'";
    $result = $this->inDB->query($sql);
      
    if($this->inDB->error())
    {
      return FALSE;
    }
    return TRUE;    
  }  
  
  public function UpdateOnlineList($user_id)
  {
    if(!$user_id)
    {
      return FALSE;
    }
    
    if(!$this->config['use_cron'])
    {
      $this->ClearOnline();
    }
    
    $sql = "SELECT user_id FROM cms_ajaxchat_users WHERE `user_id` = $user_id";
    $result = $this->inDB->query($sql);
    
    if($this->inDB->num_rows($result))
    {
      $sql = "UPDATE cms_ajaxchat_users SET last_action = NOW() , `online` = '1' WHERE user_id = $user_id";
    }
    else
    {
      $sql = "INSERT INTO `cms_ajaxchat_users` (`user_id`, `last_action`, `color`, `online`) VALUES ('$user_id', NOW(), '".$this->getColor()."', '1')";
    }
    
    $result = $this->inDB->query($sql);
    if($this->inDB->error())
    {
      return FALSE;
    }
    
    if(!$this->inDB->num_rows($result))
    {
      return FALSE;
    }
    return TRUE;
  }
  
  public function getOnline()
  {
    $sql = "SELECT cms_ajaxchat_users.last_action,
    cms_ajaxchat_users.user_id,
    cms_ajaxchat_users.on_chat,
    cms_users.login,
    cms_users.nickname,
    cms_user_profiles.imageurl
    FROM cms_ajaxchat_users
    INNER JOIN cms_users ON cms_ajaxchat_users.user_id = cms_users.id
    INNER JOIN cms_user_profiles ON cms_ajaxchat_users.user_id = cms_user_profiles.user_id
    WHERE cms_ajaxchat_users.online = 1
    GROUP BY cms_ajaxchat_users.user_id
    ";
    $result = $this->inDB->query($sql);

    if ($this->inDB->error())
    {
      return false;
    }

    if (!$this->inDB->num_rows($result))
    {
      return false;
    }

    $output = array();
    while ($row = $this->inDB->fetch_assoc($result))
    {
      if($row['user_id'] == $this->inUser->id)
      {
	$row['active'] = TRUE;
      }
      else
      {
	$row['active'] = FALSE;
      }
      $output[] = $row;
    }
    return $output;     
  }
  
  public function addMessage($user_id,$to_id,$message)
  {
    $sql = "INSERT INTO cms_ajaxchat_messages (user_id, to_id, message, time) VALUES ('$user_id', '$to_id', '$message', NOW())";
    $result = $this->inDB->query($sql);
      
    if($this->inDB->error())
    {
      return FALSE;
    }
    return TRUE;     
  }
  
  public function getMessages($skipsystem, $limit, $offset = 0)
  {      
    if(!$limit or $limit > 25)
    {
      $limit = "$offset,25";
    }
    else
    {
      $limit = "$offset,$limit";
    }
    
    if($skipsystem)
    {
      $apx = " WHERE cms_ajaxchat_messages.user_id <> 0 ";
    }
    
    $sql = "SELECT cms_ajaxchat_messages.id,
    cms_ajaxchat_messages.message,
    cms_ajaxchat_messages.time,
    cms_ajaxchat_messages.to_id,
    cms_ajaxchat_messages.user_id
    FROM cms_ajaxchat_messages
    $apx
    GROUP BY cms_ajaxchat_messages.id
    ORDER BY cms_ajaxchat_messages.id DESC
    LIMIT $limit";
    $result = $this->inDB->query($sql);
    
    if ($this->inDB->error())
    {
      return false;
    }

    if (!$this->inDB->num_rows($result))
    {
      return false;
    }

    $output = array();
    while ($row = $this->inDB->fetch_assoc($result))
    {
      $row['time'] = substr($row['time'],10);
      if(!isset($user[$row['user_id']]))
      {
	$user[$row['user_id']] = $this->inUser->loadUser($row['user_id']);
      }
      $row['color'] = $this->getUserColor($row['user_id']);
      $row['login'] = $user[$row['user_id']]['login'];
      $row['nickname'] = $user[$row['user_id']]['nickname'];
      $row['imageurl'] = $user[$row['user_id']]['imageurl'];      

      if($row['to_id'])
      {
	$to = $this->inUser->loadUser($row['to_id']);
	$row['to_nickname'] = $to['nickname'];
	$row['to_login'] = $to['login'];
	$row['message'] = str_replace("/to ".$row['to_login'],"",$row['message']);
      }
      $row['message'] = str_replace('src="/','src="http://'.$_SERVER['HTTP_HOST']."/", $row['message']);
      $row['message_color'] = $row['color'];
      if(!$row['imageurl'] or !file($_SERVER['DOCUMENT_ROOT']."/images/users/avatars/small/".$row['imageurl']))
      {
	$row['imageurl'] = "nopic.jpg";
      }
      $row['imageurl'] = "http://".$_SERVER['HTTP_HOST']."/images/users/avatars/small/".$row['imageurl'];
      $output[] = $row;
    }
    return array_reverse($output);     
  }
  
  public function getNewMessages($last_id,$user_id,$skipsystem)
  {
    if($this->inDB->get_last_id('cms_ajaxchat_messages') >= $last_id and $last_id != 0)
    {
      return false;
    }
  
    if($skipsystem)
    {
      $apx = "AND cms_ajaxchat_messages.user_id <> 0";
    }
  
    $this->UpdateOnlineList($user_id);
    $sql = "SELECT cms_ajaxchat_messages.id,
    cms_ajaxchat_messages.message,
    cms_ajaxchat_messages.time,
    cms_ajaxchat_messages.to_id,
    cms_ajaxchat_messages.user_id
    FROM cms_ajaxchat_messages
    WHERE cms_ajaxchat_messages.id > $last_id
    $apx
    ORDER BY cms_ajaxchat_messages.id ASC";
    $result = $this->inDB->query($sql);

    if ($this->inDB->error())
    {
      return false;
    }

    if (!$this->inDB->num_rows($result))
    {
      return false;
    }

    $output = array();
    while ($row = $this->inDB->fetch_assoc($result))
    {
      $row['time'] = substr($row['time'],10);
      if(!isset($user[$row['user_id']]))
      {
	$user[$row['user_id']] = $this->inUser->loadUser($row['user_id']);
      }
      $row['login'] = $user[$row['user_id']]['login'];
      $row['nickname'] = $user[$row['user_id']]['nickname'];
      $row['imageurl'] = $user[$row['user_id']]['imageurl'];  
      $row['color'] = $this->getUserColor($row['user_id']);
      
      if($row['to_id'] == $this->inUser->id)
      {
	$row["hl"] = true;
      }
      else
      {
	$row['hl'] = false;
      }
      if($row['to_id'])
      {
	$to = $this->inUser->loadUser($row['to_id']);
	$row['to_nickname'] = $to['nickname'];
	$row['to_login'] = $to['login'];
	$row['message'] = str_replace("/to ".$to['login'],"", $row['message']);
      }
      $row['message'] = str_replace('src="/','src="http://'.$_SERVER['HTTP_HOST']."/", $row['message']);
      if(!$row['imageurl'] or !file($_SERVER['DOCUMENT_ROOT']."/images/users/avatars/small/".$row['imageurl']))
      {
	$row['imageurl'] = "nopic.jpg";
      }      
      $row['imageurl'] = "http://".$_SERVER['HTTP_HOST']."/images/users/avatars/small/".$row['imageurl'];
      $output[] = $row;
    }
    return $output;     
  }  
  
  public function addToBan($user_id)
  {
    $sql = "INSERT INTO cms_ajaxchat_banlist (user_id) VALUES ($user_id)";
    $result = $this->inDB->query($sql);

    if ($this->inDB->error())
    {
      return false;
    }
    return true;
  }
  
  public function removeFromBan($user_id)
  {
    $sql = "DELETE FROM cms_ajaxchat_banlist  WHERE user_id = $user_id";
    $result = $this->inDB->query($sql);

    if ($this->inDB->error())
    {
      return false;
    }
    return true;
  }  
  
  public function isBanned($user_id)
  {
    $sql = "SELECT * FROM cms_ajaxchat_banlist WHERE user_id = $user_id";
    $result = $this->inDB->query($sql);

    if ($this->inDB->error())
    {
      return false;
    }

    if (!$this->inDB->num_rows($result))
    {
      return false;
    }
    else
    {
      return true;
    }
  }
  
  public function getMessageList($user_id)
  {
    $sql = "SELECT 
    cms_user_msg.*
    FROM cms_user_msg 
    WHERE cms_user_msg.to_id = $user_id 
    OR cms_user_msg.from_id = $user_id
    GROUP BY cms_user_msg.from_id
    ORDER BY cms_user_msg.id DESC";
    
    $result = $this->inDB->query($sql);
    
    if ($this->inDB->error()) 
    {
      return false; 
    }
    
    if (!$this->inDB->num_rows($result)) 
    { 
      return false; 
    }
    
    $output = array();
    
    while ($row = $this->inDB->fetch_assoc($result))
    {
      if($from_id == $user_id)
      {
	$companion_id = $row['to_id'];
      }
      else
      {
	$companion_id = $row['from_id'];
      }
      $author_id = $user_id;
      
      $lastmessage_sql = "SELECT * FROM cms_user_msg 
      WHERE 
      to_id = $author_id AND from_id= $companion_id
      OR to_id = $companion_id AND from_id = $author_id
      ORDER BY ID DESC";
      
      $lastmessage_result = $this->inDB->query($lastmessage_sql);
      $lastmessage = $this->inDB->fetch_assoc($lastmessage_result);
      
      $row['count'] = $this->inDB->num_rows($lastmessage_result);
      $row['author_id'] = $author_id;
      $row['author'] = $this->getDialogUser($author_id);
      $row['companion_id'] = $companion_id;
      $row['companion'] = $this->getDialogUser($companion_id);
      $row['message'] = $lastmessage['message'];
      $row['lastmessage_author_id'] = $lastmessage['from_id'];
      if($row['lastmessage_author_id'] != $user_id and $lastmessage['is_new'])
      {
	$row['is_new'] = 1;
      }
      else
      {
	$row['is_new'] = 0;
      }
      $row['senddate'] = $lastmessage['senddate'];
      
      $row['senddate'] = strtotime($row['senddate']);
      $output[] = $row;
    }
    return $output;
  }
  
  public function getDialogUser($id)
  {
    if($id == "-1")
    {
      $output = array();
      $output['id'] = "0";
      $output['login'] = false;
      $output['nickname'] = "Служба обновлений";
      $output['imageurl'] = "/images/messages/update.jpg";
      $output['is_online'] = "1";
      return $output;
    }
    
    $sql = "SELECT cms_users.id,
    cms_users.login,
    cms_users.nickname,
    cms_user_profiles.imageurl ,
    cms_online.id as is_online
    FROM cms_users 
    INNER JOIN cms_user_profiles ON cms_user_profiles.user_id = cms_users.id 
    LEFT JOIN cms_online ON cms_user_profiles.user_id = cms_online.user_id
    WHERE cms_users.id = $id";
    $result = $this->inDB->query($sql);
    $output = $this->inDB->fetch_assoc($result);
    if($output['imageurl'] == "")
    {
      $output['imageurl'] = "/images/users/avatars/small/nopic.jpg";
    }
    else
    {
      $output['imageurl'] = "/images/users/avatars/small/".$output['imageurl'];
    }
    
    if(!$output['is_online'])
    {
      $output['is_online'] = false;
    }
    else
    {
      $output['is_online'] = true;
    }
    return $output;
  }
  
  public function getDialog($user_id,$companion_id,$limit,$onlynew)
  {
    if(!$limit)
    {
      $limit = 15;
    }
    
    if($onlynew)
    {
      $where = " AND is_new = 1 ";
    }
    
    $sql = "SELECT * FROM cms_user_msg 
      WHERE 
      (to_id = $user_id AND from_id= $companion_id)
      OR 
      (to_id = $companion_id AND from_id = $user_id)
      $where
      ORDER BY senddate ASC
      LIMIT $limit";
      
    $result = $this->inDB->query($sql);
    
    if ($this->inDB->error()) 
    {
      return false; 
    }
    
    if (!$this->inDB->num_rows($result)) 
    { 
      return false; 
    }
    
    while ($row = $this->inDB->fetch_assoc($result))
    {
      $row['user_id'] = $row['from_id'];
      $row['color'] = "#000000";
      $row['time'] = substr($row['senddate'],10);

      $send_user = $this->inUser->loadUser($row["from_id"]);
      $row['nickname'] = $send_user['nickname'];

      $output[] = $row;
    }
    return $output;
  }

  public function getDialogs($user_id)
  {
  //Загружаем активные диалоги - тоесть сообщения личные которые пользователь не прочёл
    $sql = "SELECT cms_user_msg.* ,
		   cms_users.nickname AS from_nickname ,
		   cms_users.login AS from_login
		   FROM cms_user_msg
		   LEFT JOIN cms_users ON cms_users.id = cms_user_msg.from_id
		   WHERE `cms_user_msg`.`to_id` = '$user_id'
                   AND `cms_user_msg`.`is_new` = '1'";
    
    $result = $this->inDB->query($sql);
    
    if ($this->inDB->error()) 
    {
      return FALSE; 
    }
    
    if (!$this->inDB->num_rows($result)) 
    { 
      return FALSE; 
    }
    
    while ($row = $this->inDB->fetch_assoc($result))
    {
      if($row['from_id'] == "-1" or $row['from_id'] == "")
      {
	$row['from_nickname'] = "Служба поддержки";
	$row['from_id'] == "-1";
	$row['senddate'] = date("H:i d-m-Y",$row['senddate']);
      }
      $output[] = $row;
    }
    return $output;
  }
  
  public function clearOld($days)
  {
    $sql = "DELETE FROM  `cms_ajaxchat_messages` WHERE  `time` < DATE_SUB( NOW( ) , INTERVAL $days DAY )";
    $result = $this->inDB->query($sql);
  }
  
  public function getUserColor($user_id)
  {
    $sql = "SELECT color FROM cms_ajaxchat_users WHERE user_id = $user_id";
    $result = $this->inDB->query($sql);
    $user = $this->inDB->fetch_assoc($result);
    if(!$user)
    {
      return "#000000";
    }
    return $user['color'];
  }
  
  public function setUserColor($user_id,$color)
  {
    $sql = "UPDATE cms_ajaxchat_users SET `color` = '$color' WHERE `user_id` = $user_id";
    $result = $this->inDB->query($sql);
    return TRUE;
  }
  
  public function readPMessage($id,$user_id)
  {
    $sql = "UPDATE cms_user_msg SET `is_new` = '0' WHERE `to_id` = $user_id AND `id` = $id";
    $result = $this->inDB->query($sql);
    return TRUE;
  }
}
?>