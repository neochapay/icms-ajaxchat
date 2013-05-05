var last_id = 0;
var sound = 1;
var news = 0;
var upd = 0;
var hl = 0;
var active_user;
var enable_dev =0;
$(document).ready(function(){
	get_userlist();
	get_messages();
	setInterval(loadNewMessages, 5000);
	setInterval(onLineUsers, 15000);

	$f("player", "http://releases.flowplayer.org/swf/flowplayer-3.2.14.swf",{
	  clip:{
	    autoPlay: false,
	  },
	  playlist:
	  [
	    {url: "/components/ajaxchat/sounds/Im-User-Auth.mp3"},
	    {url: "/components/ajaxchat/sounds/Im-Message-In.mp3"},
	    {url: "/components/ajaxchat/sounds/Im-Sms.mp3"}
	  ]
	});

	var code = null;

	$("#chatText").keypress(function(e)
        {
            code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13)
	    {
	      sendMessage();
	    };
        });

	$("#chatrum").addClass("active");

	$("#chatTopBar UL LI").click(function(){listTab($(this).attr("id"))});
});

function get_userlist()
{
  $.ajax({
    url:	'/ajaxchat/get_userlist',
    type:   	'post',
    success: function(json)
      {
	$("#chatUsers").html("<ul></ul>");
	var users = jQuery.parseJSON(json);
	if(!users)
	{
	  alert("Получены неверные данные: отсутствует список пользователей");
	}
	else
	{
	  $.each(users, function(){
	    if(!this.imageurl)
	    {
	      this.imageurl = "nopic.jpg";
	    }
	    if(this.active)
	    {
	      active_user = this.user_id;
	    }
	    $("#chatUsers UL").append("<li class=\"chatuser\" id=\"chatuser_"+this.user_id+"\"><div onClick=\"loadUser("+this.user_id+")\"><img src=\"/images/users/avatars/small/"+this.imageurl+"\">"+this.nickname+"</div></li>");
	  });
	}
      }
  });
}

function get_messages()
{
  $.ajax({
    url:	'/ajaxchat/get_messages',
    type:	'post',
    success:	function(json)
    {
      $("#chatLineHolder").html("<ul></ul>");
      var str = jQuery.parseJSON(json);
      if(!str)
      {
	alert("Получены неверные данные: отсутствует список сообщений");
      }
      else if(str.error)
      {
	alert(str.error_message);
      }
      else
      {
	$.each(str.messages,function(){
	  $("#chatLineHolder UL").append(formatMessage(this));
	    last_id = this.id;
	});
	
	$.each(str.dialogs,function(){
	  from_id = this.from_id;
	  $("#chatTopBar UL").append("<li id=\"open_"+this.from_id+"\" class=\"dialog\">"+this.from_nickname+"</div>");
	  $("#open_"+from_id).click(function(){listTab("open_"+from_id)});
	})
      }
      var height = $("#chatLineHolder UL").height();
      $("#chatLineHolder").animate({"scrollTop":height},"fast");
    }
  });
}

function sendMessage()
{
  var message = $("#chatText").val();
  var id = $(".active").attr("id");

  if(message.length >= 2)
  {
    if(message == "/clean")
    {
      $("#chatLineHolder UL").html("");
    }
    else if(message == "/sound on")
    {
      sound = 1;
    }
    else if(message == "/sound off")
    {
      sound = 0;
    }
    else if(message == "/help")
    {
      $.ajax({
	url:	"/ajaxchat/get_help",
	type:	"post",
	success: function(help)
	{
	  $("#chatLineHolder UL").append("<li>"+help+"</li>");
	  $("#chatLineHolder").scrollTop("99999999");
	}
      });
    }
    else
    {
      $.ajax({
	url:	'/ajaxchat/send_message',
	type:	'post',
	data:	'message='+message+'&id='+id,
	success: function(answer)
	{
	  if(answer == "pass")
	  {
	    if(upd == 0)
	    {
	      loadNewMessages();
	    }
	  }
	  else
	  {
	    alert(answer);
	  }
	}
      });
    }
  }
  $("#chatText").val("");
}

function onLineUsers()
{
  $.ajax({
    url:	"/ajaxchat/get_userlist",
    type:	"post",
    success: function(json)
    {
      var users = jQuery.parseJSON(json);
      if(users)
      {
	$("#chatUsers UL").children().addClass("oldOnlineUsers");
	$.each(users,function(){
	  if(!this.imageurl)
	  {
	    this.imageurl = "nopic.jpg";
	  }

	  if($("#chatuser_"+this.user_id).text().length == 0)
	  {
	    $("#chatUsers UL").append("<li class=\"chatuser\" id=\"chatuser_"+this.user_id+"\"><a href=\"/users/"+this.login+"\"><img src=\"/images/users/avatars/small/"+this.imageurl+"\">"+this.nickname+"</a></li>");
	    if(sound == 1)
	    {
	      $f().play(0);
	    }
	    $("#chatLineHolder").scrollTop("99999999");
	  }
	  else
	  {
	    $("#chatuser_"+this.user_id).removeClass("oldOnlineUsers");
	  }
	});
	$(".oldOnlineUsers").remove();
      }
    }
  });

}

function loadNewMessages()
{
  if(upd == 0)
  {
    upd = 1;
    $.ajax({
      url:	"/ajaxchat/load_new",
      type:	"post",
      data:	"last_id="+last_id,
      success: function(json)
      {
	var messages = jQuery.parseJSON(json);
	if(messages)
	{
	  $.each(messages,function(){
	    if($("#mess_"+this.id).text().length == 0)
	    {
	      $("#chatLineHolder UL").append(formatMessage(this));
	      if(last_id < this.id)
	      {
		last_id = this.id;
	      }
	      if(this.hl)
	      {
		hl = 1;
	      }
	      news = 1;
	    }
	  });

	  if(news == 1)
	  {
	    if(sound == 1)
	    {
	      if(hl == 1)
	      {
		$f().play(2);
	      }
	      else
	      {
		$f().play(1);
	      }
	    }
	    news = 0;
	    hl = 0;
	  }
	  $("#chatLineHolder").scrollTop("99999999");
	}
      }
    });
    upd = 0;
  }
}

function addLogin(login)
{
  $("#chatText").val("/to "+login+":");
  $("#chatText").focus();
}

function listTab(tab)
{
  $("#chatTopBar UL LI").removeClass("active");
  $("#"+tab).addClass("active");
  if(tab == "chatrum")
  {
    $("#chatLineHolder").show();
    $("#chatBottomBar").show();
    $("#dialogLineHolder").hide();
    $("#chatUsers").show();
    $("#chatLineHolder").scrollTop("99999999");
    id = "";
  }
  else
  {
    $("#chatLineHolder").hide();
    $("#dialogLineHolder").show();
    $("#chatBottomBar").show();
    $("#chatUsers").hide();
    if($("#"+tab).hasClass("dialog"))
    {
      id = tab.replace("open_","");
      loadDialog(tab);
    }
  }
}

function formatMessage(mess)
{
  if(!mess.imageurl)
  {
    mess.imageurl = "nopic.jpg";
  }
  if(mess.user_id == "0")
  {
    var str = "<li class=\"sysmes\" id=\"mess_"+mess.id+"\"><tt>"+mess.time+"</tt> <i>"+mess.message+"</i></li>";
  }
  else if(mess.to_id == "0")
  {
    var str = "<li id=\"mess_"+mess.id+"\" style=\"color:"+mess.color+"\"><tt>"+mess.time+"</tt> <b"; 
    if(mess.login)
    {
      str += "onClick=addLogin('"+mess.login+"')";
    }
    str += ">"+mess.nickname+"</b>:"+mess.message+"</li>";
  }
  else
  {
    var str = "<li id=\"mess_"+mess.id+"\" style=\"color:"+mess.color+"\"><tt>"+mess.time+"</tt> <b onClick=addLogin('"+mess.login+"')>"+mess.nickname+"</b> для <b onClick=addLogin('"+mess.to_login+"')>"+mess.to_nickname+"</b>:"+mess.message+"</li>";
  }
  return str;
}

function loadDialog(id)
{
  $.ajax({
    url:	"/ajaxchat/get_converstation",
    type:	"post",
    data:	"id="+id,
    success: function(json)
    {
      var dialog = jQuery.parseJSON(json);
      if(dialog)
      {
	$("#dialogLineHolder").html("<ul></ul>");
	$.each(dialog.messages, function(){
	    $("#dialogLineHolder UL").append(formatMessage(this));
	});
	var height = $("#dialogLineHolder UL").height();
	$("#dialogLineHolder").animate({"scrollTop":height},"fast");
      }
    }
  });
}

function loadUser(id)
{
  if(enable_dev == 1)
  {
    if(active_user == id)
    {
      $(".userinfo").remove();
      $("#chatuser_"+id).append("<div class=\"userinfo\"><div onClick=\"loadDialog("+id+")\">написать личное сообщение</div></div>")
    }
  }
}

function sysMes()
{
  if($("#sysmes").attr("checked"))
  {
    $(".sysmes").hide();
  }
  else
  {
    $(".sysmes").show();
  }
}