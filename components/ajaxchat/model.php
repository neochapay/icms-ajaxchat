<?php
if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

class cms_model_ajaxchat
{
  function __construct()
  {
    $this->inDB = cmsDatabase::getInstance();
    $this->inUser = cmsUser::getInstance();
  }
  
  public function getDefaultConfig() 
  {
    $cfg = array();
    return $cfg;
  }

  public function getColor()
  {
        //Определяем цвета сообщений чата
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
    
    $color_count = count($colors);
    $color_rnd = rand(0,$color_count-1);
    $color = $colors[$color_rnd];
    return $color;
  }
  
  public function changeColor($user_id)
  {
    $color = $this->getColor();
    $sql = "UPDATE cms_ajaxchat_users SET `color` = '$color' WHERE `user_id` = '$user_id'";
    $result = $this->inDB->query($sql);
  }
  
  public function CheckUser($user_id)
  {
    $sql = "SELECT * FROM cms_ajaxchat_users WHERE user_id = $user_id";
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
  
  public function ClearOnline()
  {
    $sql = "UPDATE Fcms_ajaxchat_users SET `online` = '0' WHERE last_action < NOW() - INTERVAL 1 MINUTE";
    $result = $this->inDB->query($sql);
      
    if($this->inDB->error())
    {
      return FALSE;
    }
    return TRUE;    
  }
  
  public function UpdateOnlineList($user_id)
  {
    $this->ClearOnline();
    if($this->CheckUser($user_id))
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
    $this->ClearOnline();
    $sql = "SELECT cms_ajaxchat_users.last_action,
    cms_ajaxchat_users.user_id,
    cms_users.login,
    cms_users.nickname,
    cms_user_profiles.imageurl
    FROM cms_ajaxchat_users
    INNER JOIN cms_users ON cms_ajaxchat_users.user_id = cms_users.id
    INNER JOIN cms_user_profiles ON cms_ajaxchat_users.user_id = cms_user_profiles.user_id
    WHERE cms_ajaxchat_users.online = 1
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
  
  public function getMessages($skipsystem, $limit)
  {      
    if(!$limit or $limit > 25)
    {
      $limit = 25;
    }
    
    if($skipsystem)
    {
      $apx = " WHERE cms_ajaxchat_messages.user_id <> 0 ";
    }
    
    $offset_sql = "SELECT * FROM cms_ajaxchat_messages $apx";
    $offset_result = $this->inDB->query($offset_sql);
    $offset = $this->inDB->num_rows($offset_result)-$limit;
    
    if($offset < 0)
    {
      $offset = 0;
    }

    $sql = "SELECT cms_ajaxchat_messages.id,
    cms_ajaxchat_messages.message,
    cms_ajaxchat_messages.time,
    cms_ajaxchat_messages.to_id,
    cms_ajaxchat_messages.user_id,
    cms_users.login,
    cms_users.nickname,
    cms_user_profiles.imageurl,
    cms_ajaxchat_users.color as color
    FROM cms_ajaxchat_messages
    LEFT JOIN cms_users ON cms_ajaxchat_messages.user_id = cms_users.id
    LEFT JOIN cms_user_profiles ON cms_ajaxchat_messages.user_id = cms_user_profiles.user_id  
    LEFT JOIN cms_ajaxchat_users ON cms_ajaxchat_messages.user_id = cms_ajaxchat_users.user_id
    $apx
    ORDER BY cms_ajaxchat_messages.id ASC LIMIT $offset,$limit";
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
      
      if($row['to_id'])
      {
	$to = $this->getUserByID($row['to_id']);
	$row['to_nickname'] = $to['nickname'];
	$row['to_login'] = $to['login'];
	$row['message'] = str_replace("/to ".$row['to_login'],"",$row['message']);
      }      
      $output[] = $row;
    }
    return $output;     
  }
  
  public function getNewMessages($last_id,$user_id,$skipsystem)
  {
    if($this->inDB->get_last_id('cms_ajaxchat_messages') >= $last_id)
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
    cms_ajaxchat_messages.user_id,
    cms_users.login,
    cms_users.nickname,
    cms_user_profiles.imageurl,
    cms_ajaxchat_users.color as color
    FROM cms_ajaxchat_messages
    LEFT JOIN cms_users ON cms_ajaxchat_messages.user_id = cms_users.id
    LEFT JOIN cms_user_profiles ON cms_ajaxchat_messages.user_id = cms_user_profiles.user_id    
    LEFT JOIN cms_ajaxchat_users ON cms_ajaxchat_messages.user_id = cms_ajaxchat_users.user_id
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
	$to = $this->getUserByID($row['to_id']);
	$row['to_nickname'] = $to['nickname'];
	$row['to_login'] = $to['login'];
	$row['message'] = str_replace("/to ".$to['login'],"", $row['message']);
      }
      $output[] = $row;
    }
    return $output;     
  }  
  
  public function getUser($login)
  {
    $sql = "SELECT * FROM cms_users WHERE login = '".$login."'";
    $result = $this->inDB->query($sql);
    return $this->inDB->fetch_assoc($result);
  }  
  
  public function getUserByID($id)
  {
    $sql = "SELECT * FROM cms_users WHERE id = '".$id."'";
    $result = $this->inDB->query($sql);
    return $this->inDB->fetch_assoc($result);
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
  
  public function getDialog($user_id,$companion_id)
  {
    $sql = "SELECT * FROM cms_user_msg 
      WHERE 
      to_id = $user_id AND from_id= $companion_id
      OR to_id = $companion_id AND from_id = $user_id
      ORDER BY senddate ASC";
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
      $output[] = $row;
    }
    return $output;
  }  
}
?>