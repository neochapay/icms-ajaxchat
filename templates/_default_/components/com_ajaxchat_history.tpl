<div id="chatLineHolder">
  <ul>
    {foreach  key=id item=message from=$messages}
      {if $message.to_id == 0}
	<li id="mess_{$message.id}" style="color:{$message.color}">
	  {if $is_admin}
	    <a href="/ajaxchat/delete{$message.id}.html">[X]</a>
	  {/if}
	  <tt>{$message.time}</tt> 
	  <b>{$message.nickname}</b>:
	  {$message.message}
	</li>
      {else}
	<li id="mess_{$message.id}" style="color:{$message.color}">
	  {if $is_admin}
	    <a href="/ajaxchat/delete{$message.id}.html">[X]</a>
	  {/if}
	  <tt>{$message.time}</tt> 
	  <b>{$message.nickname}</b> для 
	  <b>{$message.to_nickname}</b> 
	  {$message.message}
	</li>
      {/if}
    {/foreach}
  </ul>
</div>
{$pagination}