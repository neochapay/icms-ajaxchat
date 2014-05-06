<?php
function routes_ajaxchat()
{
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/view.html$/i',
    'do'    => 'view'
  );

  $routes[] = array(
    '_uri'  => '/^ajaxchat\/history.html$/i',
    'do'    => 'history'
  );  
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/history([0-9]+).html$/i',
    'do'    => 'history',
     1      => 'page'
  );  
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/delete([0-9]+).html$/i',
    'do'    => 'delete_mess',
     1      => 'id'
  );    
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/get_userlist$/i',
    'do'    => 'get_userlist'
  ); 
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/send_message$/i',
    'do'    => 'send_message'
  ); 
  
   $routes[] = array(
    '_uri'  => '/^ajaxchat\/get_messages$/i',
    'do'    => 'get_messages'
  );  
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/load_new$/i',
    'do'    => 'load_new'
  );   
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/get_help$/i',
    'do'    => 'get_help'
  );     

  $routes[] = array(
    '_uri'  => '/^ajaxchat\/get_dialogs$/i',
    'do'    => 'get_dialogs'
  );   

  $routes[] = array(
    '_uri'  => '/^ajaxchat\/userstatus$/i',
    'do'    => 'userstatus'
  );  
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/get_converstation$/i',
    'do'    => 'get_converstation'
  ); 
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/clear$/i',
    'do'    => 'clear'
  );
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/cron$/i',
    'do'    => 'cron'
  );  
  
  $routes[] = array(
    '_uri'  => '/^ajaxchat\/set_color$/i',
    'do'    => 'set_color'
  );

  $routes[] = array(
    '_uri'  => '/^ajaxchat\/read_pmessage$/i',
    'do'    => 'read_pmessage'
  );  
  return $routes;
}
?>