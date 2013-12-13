<?php
    function info_component_ajaxchat(){
        $_component['title']        = 'Чат';
        $_component['description']  = 'чат';
        $_component['link']         = 'ajaxchat';
        $_component['author']       = 'Сергей Игоревич (NeoChapay)';
        $_component['internal']     = '0';
        $_component['version']      = '0.4-beta1';
        return $_component;
    }

    function install_component_ajaxchat()
    {
      $inDB = cmsDatabase::getInstance();

      $sql = "CREATE TABLE IF NOT EXISTS `cms_ajaxchat_banlist` (
	      `user_id` int(11) NOT NULL
	      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
      $inDB->query($sql);

      $sql = "CREATE TABLE IF NOT EXISTS `cms_ajaxchat_messages` (
	      `id` int(11) NOT NULL AUTO_INCREMENT,
	      `user_id` int(11) NOT NULL,
	      `to_id` int(11) NOT NULL,
	      `message` mediumtext NOT NULL,
	      `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      PRIMARY KEY (`id`)
	      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
      $inDB->query($sql);
      
      $sql = "CREATE TABLE IF NOT EXISTS `cms_ajaxchat_users` (
	      `user_id` int(11) NOT NULL,
	      `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      `color` text NOT NULL,
	      `online` tinyint(4) NOT NULL,
	      `on_chat` int(11) NOT NULL
	      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
      $inDB->query($sql);
      return true;
    }

    function upgrade_component_ajaxchat()
    {
      return true;
    }

?>
