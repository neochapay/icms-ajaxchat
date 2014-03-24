<link rel="stylesheet" type="text/css" href="/components/ajaxchat/css/page.css" />
<link rel="stylesheet" type="text/css" href="/components/ajaxchat/css/chat.css" />

<div id="chatContainer">

    <div id="chatTopBar" class="rounded">
      <ul>
	<li id="chatrum">Чат</li>
      </ul>
      <div id="flag">
      </div>
    </div>
    <div id="chatLineHolder"></div>
    <div class="dialogLineHolder"></div>
    
    <div id="chatUsers" class="rounded"></div>
    <div id="chatBottomBar" class="rounded">
    	<div class="tip"></div>
        <div id="submitForm" style=" margin-right: 10px;">
	    <div class="usr_msg_bbcodebox">{$bb_toolbar}</div>
	    <div class="color"><a href="#" title="Поменять цвет соощений"><img src="/components/ajaxchat/img/color.png"></a></div>
	    {$smilies}
	    {$autogrow}
            <input id="chatText" name="chatText" class="rounded" style="width: 100%; margin-bottom: 10px;" />
            <input type="submit" class="blueButton" value="Отправить" onClick="sendMessage()"/>
            <div class="sysmesc">
	      <div class="icon" id="sysvoice" onClick="sysMes()" title="Системные сообщения"></div>
	      <div class="icon" id="sound" onClick="sysSound()" title="Звуки в чате"></div>
	      <div class="icon" id="help" onClick="console.log('Помощи ждать неоткуда,а люди настроены враждебно...')" title="Помощь"></div>
	    </div>
        </div>
    </div>
</div>
<div align="center"><a href="/ajaxchat/history.html"><h6>история чата</h6></a></div>
<div id="player" style="display:block;width:0px;height:0px;"></div>
<script src="/components/ajaxchat/js/fp/fp.min.js"></script>
<script src="/components/ajaxchat/js/script.js"></script>