<?php
function mod_ajaxchat($module_id)
{
    $inDB   = cmsDatabase::getInstance();
    function _dec($digit,$expr,$onlyword=false)
    {
        if(!is_array($expr)) $expr = array_filter(explode(' ', $expr));
        if(empty($expr[2])) $expr[2]=$expr[1];
        $i=preg_replace('/[^0-9]+/s','',$digit)%100; //intval не всегда корректно работает
        if($onlyword) $digit='';
        if($i>=5 && $i<=20) $res=$digit.' '.$expr[2];
        else
        {
                $i%=10;
                if($i==1) $res=$expr[0];
                elseif($i>=2 && $i<=4) $res=$expr[1];
                else $res=$expr[2];
        }
        return trim($res);
    }

    $sql = "SELECT cms_ajaxchat_online.*,
    cms_users.login,
    cms_users.nickname
    FROM cms_ajaxchat_online
    INNER JOIN cms_users ON cms_users.id = cms_ajaxchat_online.user_id
    WHERE last_action < NOW() - INTERVAL 15 MINUTES
    ";
    $result = $inDB->query($sql);
    
    $chat_num = $inDB->num_rows($result);
    
    if($chat_num == 0)
    {
      $msg = 'В <a href="/ajaxchat/">чате</a> никого нет... Чату от этого грусно и одиноко...';
    }
    elseif($chat_num == 1)
    {
      $msg = 'В <a href="/ajaxchat/">чате</a> одиноко скучает 1 пользователь. Срочно необходима компания! Заходим в чат!';
    }
    elseif($chat_num == 2)
    {
      $msg = 'В <a href="/ajaxchat/">чате</a> мило болтают 2 человека. Может сообразить на троих? Заходи в чат!';
    }
    elseif($chat_num == 3)
    {
      $msg = 'В <a href="/ajaxchat/">чате</a> кто то соображает на троих... Такое событие и без Вас? Срочно исправлять!';
    }
    else
    {
      $msg = 'В <a href="/ajaxchat/">чате</a> '.$chat_num.' ';
      $msg .= _dec($chat_num,array("пользователь", "пользователя", "пользователей"));
    }
    //print mysql_error();
    print $msg;
    return true;
}
?>