<link rel="stylesheet" type="text/css" href="/components/ajaxchat/css/page.css" />
<link rel="stylesheet" type="text/css" href="/components/ajaxchat/css/chat.css" />

<div id="chatContainer" class="colWrap">

    <div id="chatTopBar" class="rounded col20">
      <ul>
	<li id="chatrum">Чат</li>
      </ul>
      <div id="flag">
      </div>
    </div>
    <div id="chatLineHolder"></div>
    <div class="dialogLineHolder"></div>
    
    <div id="chatUsers" class="rounded"><ul></ul></div>
    <div id="chatBottomBar" class="rounded">
    	<div class="tip"></div>
        <div id="submitForm" style=" margin-right: 10px;">
	    <div class="scolor">
	      <select name="colorpicker">
		{foreach  key=id item=color from=$colors}
		  <option value="{$color}" {if $color == $user_color}selected{/if}>{$color}</option>
		{/foreach}
	      </select>
	    </div>
	    <div id="smilecelect">
	      <div id="smilelist">
		{foreach  key=id item=dir from=$smiles}
		  <hr />
		  {foreach  key=id item=img from=$dir}
		    <img src="{$img.file}" class="emo_s" data-name="{$img.name}">
		  {/foreach}
		{/foreach}
	      </div>
	      <div id="smilebutton">
		<img src="/components/ajaxchat/img/smiles/smiles/1f603.png">
	      </div>
	    </div>
	    {$autogrow}
            <div id="chatText" name="chatText" class="rounded" contenteditable></div>
            <input type="submit" class="blueButton" value="Отправить" onClick="sendMessage()"/>
            <div class="sysmesc">
	      <div class="icon on" id="sysvoice" onClick="sysMes()" title="Системные сообщения"></div>
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
<script src="/components/ajaxchat/js/colorpicker.js"></script>