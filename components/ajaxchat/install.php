<?php
    function info_component_ajaxchat(){
        $_component['title']        = 'Чат';
        $_component['description']  = 'чат';
        $_component['link']         = 'ajaxchat';
        $_component['author']       = 'Сергей Игоревич (NeoChapay)';
        $_component['internal']     = '0';
        $_component['version']      = '0.1';

        return $_component;
    }

    function install_component_ajaxchat()
    {
      return true;
    }

    function upgrade_component_ajaxchat()
    {
      return true;
    }

?>
