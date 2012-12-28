icms-livechat
=============

Структура БД

Если для 1.9 то надо делать кодировку cp1251 в бд 

CREATE TABLE `cms_ajaxchat_banlist` (
  `user_id` int(11) NOT NULL
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
  
CREATE TABLE `cms_ajaxchat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `to_id` int(11) NOT NULL,
  `message` mediumtext NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `cms_ajaxchat_online` (
  `user_id` int(11) NOT NULL,
  `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
