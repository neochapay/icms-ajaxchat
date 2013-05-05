{literal}
<style>
  #minichat UL{
    list-style: none; 
    padding: 0px;
  }
  
  .widget-container #minichat li{
    background: none;
    padding: 0;
  }
  
  A#minichatlink{
    font-size: 18px;
    font-weight: bold;
  }
  
  #minichat UL LI IMG{
    max-width: 100px;
    max-height: 50px;
  }
  
  #minichat UL LI{
    max-height: 20px;
    overflow: hidden;
    margin-left: 0px;
  }
</style>
<script>
var upd = 0;
var last_id = 0;
$.ajax({
    url:	'/ajaxchat/get_messages',
    type:	'post',
    data:	'skipsystem=1&count=7',
    success:	function(json)
    {
      $("#minichat").html("<ul></ul>");
      var str = jQuery.parseJSON(json);
      if(str.messages)
      {
	$.each(str.messages,function(){
	  $("#minichat UL").append(formatMessage(this));
	    last_id = this.id;
	});
      }
    }
  });
setInterval(loadNewMessages, 5000);

function formatMessage(mess)
{
  if(!mess.imageurl)
  {
    mess.imageurl = "nopic.jpg";
  }
  if(mess.to_id == "0")
  {
    var str = "<li id=\"mess_"+mess.id+"\"><b>"+mess.nickname+"</b>:"+mess.message+"</li>";
  }
  else
  {
    var str = "<li id=\"mess_"+mess.id+"\"><b>"+mess.nickname+"</b> для <b>"+mess.to_nickname+"</b>:"+mess.message+"</li>";
  }
  return str;
}

function loadNewMessages()
{
  if(upd == 0)
  {
    upd = 1;
    $.ajax({
      url:	"/ajaxchat/load_new",
      type:	"post",
      data:	"last_id="+last_id+"&skipsystem=1",
      success: function(json)
      {
	var messages = jQuery.parseJSON(json);
	if(messages)
	{
	  $.each(messages,function(){
	    if($("#mess_"+this.id).text().length == 0)
	    {
	      $("#minichat UL").append(formatMessage(this));
	      if(last_id < this.id)
	      {
		last_id = this.id;
	      }
	      $("#minichat UL LI").first().remove();
	    }
	  });
	}
      }
    });
    upd = 0;
  }
}
</script>
{/literal}
<div id="minichat"></div>
<div align="center">
<a href="/ajaxchat" id="minichatlink">Присоединиться</a>
</div>