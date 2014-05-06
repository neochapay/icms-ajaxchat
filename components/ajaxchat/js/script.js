var last_id = 0;
var sound = 1;
var news = 0;
var upd = 0;
var hl = 0;
var on_chat = 1;
var skipsystem = 0;
var newmessages_count = 0;
var click;
var active_user;
var title;
var notify_count = 0;
var notification;

$(document).on("click", "a.closedialog", function() {
  $("#chatTopBar UL #"+$(this).attr("id")).remove();
  listTab("chatrum");
});

$(document).on("click","#chatTopBar UL LI SPAN",function(){
  listTab($(this).parent().attr("id"));
})

$(document).on("click","IMG.startdialog",function(){
  getPrivateDialog($(this).closest("li").attr("user-id"));
})

$(document).on("click","IMG.sendpublic",function(){
  addLogin($(this).closest("li").attr("login-id"));
})

$(document).on("click","#chatLineHolder LI TT",function(){
  var id = $(this).parent().attr("id");
  if($("#"+id).hasClass("fixed"))
  {
    $("#"+id).removeClass("fixed");
  }
  else
  {
    var fcount = $(".fixed").size();
    var top = 50+20*fcount;
    $("#"+id).addClass("fixed");
    $("#"+id).css("top",top+"px");
  }
});

$(document).on("click","#chatLineHolder LI B",function(){
  addLogin($(this).attr("data-login"));
});

$(document).on("mouseover",".dialogLineHolder UL LI.new",function(){
  $(this).removeClass("new");
  var mess_id = $(this).attr("id").replace("mess_","");
  $.ajax({
      url:	'/ajaxchat/read_pmessage',
      type:	'post',
      data:	'id='+mess_id
  })
});

$(document).ready(function(){
	title = $('title').text();
	$(window).blur(function() {
	    $.ajax({
	      url:	'/ajaxchat/userstatus',
	      type:	'post',
	      data:	'status=offline'
	    })
	    on_chat = 0;
	});

	$(window).focus(function() {
	    $.ajax({
	      url:	'/ajaxchat/userstatus',
	      type:	'post',
	      data:	'status=online'
	    })
	    on_chat = 1;
	    newmessages_count = 0;
	    if(notify_count > 0)
	    {
	      notification.close();
	    }
	    $('title').text(title);
	});
	
	$.ajax({
	  url:	'/ajaxchat/userstatus',
	  type:	'post',
	  data:	'status=online'
	}) 
	
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
	$('select[name="colorpicker"]').simplecolorpicker({picker: true, theme: 'glyphicons'});;
});

function get_userlist()
{
  $('#flag').removeClass();
  $('#flag').addClass('yellow');
  $.ajax({
    url:	'/ajaxchat/get_userlist',
    type:   	'post',
    success: function(json)
      {
	$("#chatUsers").html("<ul></ul>");
	var users = jQuery.parseJSON(json);
	if(!users)
	{
	  $('#flag').removeClass();
	  $('#flag').addClass('red');
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
	    var userstring = '<li class="chatuser" id="chatuser_'+this.user_id+'" user-id="'+this.user_id+'" login-id="'+this.login+'"><a href="/users/'+this.login+'">';
	    if(this.on_chat == 1)
	    {
	      userstring += '<img class="activestatus" src="/components/ajaxchat/img/online.png">';
	    }
	    else
	    {
	      userstring += '<img class="activestatus" src="/components/ajaxchat/img/offline.png">';
	    }
	    if(this.user_id != active_user)
	    {
	      userstring += '<img src="/images/users/avatars/small/'+this.imageurl+'">'+this.nickname+'</a><div class="iconsright"><img class="startdialog" src="/components/ajaxchat/img/start-dialog.png"><img class="sendpublic" title="Отправить публичное сообщение" src="/components/ajaxchat/img/send_public.png"></div></li>';
	    }
	    else
	    {
	      userstring += '<img src="/images/users/avatars/small/'+this.imageurl+'">'+this.nickname+'</a></li>';
	    }
	    $("#chatUsers UL").append(userstring);
	  });
	  $('#flag').removeClass();
	  $('#flag').addClass('green');
	}
      }
  });
}

function get_messages()
{
  $('#flag').removeClass();
  $('#flag').addClass('yellow');
  $.ajax({
    url:	'/ajaxchat/get_messages',
    type:	'post',
    data:	'skipsystem='+skipsystem,
    success:	function(json)
    {
      $("#chatLineHolder").html("<ul></ul>");
      var str = jQuery.parseJSON(json);
      if(!str)
      {
	$('#flag').addClass('red');
	alert("Получены неверные данные: отсутствует список сообщений");
      }
      else if(str.error)
      {
	$('#flag').removeClass();
	$('#flag').addClass('red');
	alert(str.error_message);
      }
      else
      {
	$.each(str.messages,function(){
	  $("#chatLineHolder UL").append(formatMessage(this));
	    last_id = this.id;
	});
	
	if(str.dialogs)
	{
	  $.each(str.dialogs,function(){
	    from_id = this.from_id;
	    loadDialogTab(this);
	  })
	}
      }
      var height = $("#chatLineHolder UL").height();
      $("#chatLineHolder").animate({"scrollTop":height},"fast");
      $('#flag').removeClass();
      $('#flag').addClass('green');
    }
  });
}

function sendMessage()
{
  $('#flag').removeClass();
  $('#flag').addClass('yellow');
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
	    
	    if(id)
	    {
	      var act_nickname = $('#chatuser_'+active_user).text();
	      $(".dialogLineHolder UL").append("<li class=\"mess_stub\" style=\"color:black\"><tt>00:00:00</tt> <b>"+act_nickname+"</b>:"+message+"</li>");
	    }
	  }
	  else
	  {
	    $('#flag').removeClass();
	    $('#flag').addClass('red');
	    alert(answer);
	  }
	}
      });
    }
  }
  $('#flag').removeClass();
  $('#flag').addClass('green');
  $("#chatText").val("");
}

function onLineUsers()
{
  $('#flag').removeClass();
  $('#flag').addClass('yellow');
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
	    var userstring = '<li class="chatuser" id="chatuser_'+this.user_id+'" user-id="'+this.user_id+'" login-id="'+this.login+'"><a href="/users/'+this.login+'">';
	    
	    if(this.on_chat == 1)
	    {
	      userstring += '<img class="activestatus" src="/components/ajaxchat/img/online.png">';
	    }
	    else
	    {
	      userstring += '<img class="activestatus" src="/components/ajaxchat/img/offline.png">';
	    }
	    
	    userstring += '<img src="/images/users/avatars/small/'+this.imageurl+'">'+this.nickname+'</a><div class="iconsright"><img class="startdialog"src="/components/ajaxchat/img/start-dialog.png"><img class="sendpublic" title="Отправить публичное сообщение" src="/components/ajaxchat/img/send_public.png"></div></li>';
	    $("#chatUsers UL").append(userstring);
	    if(sound == 1)
	    {
	      $f().play(0);
	    }
	    $("#chatLineHolder").scrollTop("99999999");
	  }
	  else
	  {
	    if(this.on_chat == 1)
	    {
	      $("#chatuser_"+this.user_id+" IMG.activestatus").attr("src","/components/ajaxchat/img/online.png");
	    }
	    else
	    {
	      $("#chatuser_"+this.user_id+" IMG.activestatus").attr("src","/components/ajaxchat/img/offline.png");
	    }
	    
	    $("#chatuser_"+this.user_id).removeClass("oldOnlineUsers");
	  }
	});
	$(".oldOnlineUsers").remove();
      }
      $('#flag').removeClass();
      $('#flag').addClass('green');
    }
  });

}

function loadNewMessages()
{
  $('#flag').removeClass();
  $('#flag').addClass('yellow');
  
  if(upd == 0)
  {
    upd = 1;
    $.ajax({
      url:	"/ajaxchat/load_new",
      type:	"post",
      data:	"last_id="+last_id+"&skipsystem="+skipsystem,
      success: function(json)
      {
	if(json.length < 10)
	{
	  return;
	}
	var str = jQuery.parseJSON(json);
	if(!str)
	{
	  $('#flag').removeClass();
	  $('#flag').addClass('blue');
	  return false;
	}
	
	if(str.messages)
	{
	  $.each(str.messages,function(){
	    if($("#mess_"+this.id).text().length == 0)
	    {
	      $("#chatLineHolder UL").append(formatMessage(this));
	      
	      if(on_chat == 0)
	      {
		newmessages_count++;
		$('title').text("("+newmessages_count+") "+title);
		if(this.to_id == active_user)
		{
		  newNotify(this.message,"ajaxchat_public",this.nickname,this.imageurl)
		}
	      }
	      
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
	  $('#flag').addClass('green');
	  $("#chatLineHolder").scrollTop("99999999");
	}
	else
	{
	  $('#flag').removeClass();
	  $('#flag').addClass('green');
	}
	
	if(str.dialogs)
	{
	  $.each(str.dialogs,function(){
	    from_id = this.from_id;

	    if($('#open_'+from_id).text().length == 0)
	    {
	      loadDialogTab(this);
	      $("#open_"+from_id).click(function(){listTab("open_"+from_id)});
	    }
	    else if($('#open_'+from_id).hasClass('active'))
	    {
// 	      loadDialog(from_id);
	    }
	  })
	}
      }
    });
    upd = 0;
  }
}

function addLogin(login)
{
  $("#chatText").val("/to "+login+" ");
  $("#chatText").focus();
}

function listTab(tab)
{
  if(tab == "open_-1")
  {
    $("#chatBottomBar").hide();
  }
  else
  {
    $("#chatBottomBar").show();
  }
  $("#chatTopBar UL LI").removeClass("active");
  $("#chatTopBar UL LI#"+tab).addClass("active");
  if(tab == "chatrum")
  {
    $(".dialogLineHolder").hide();
    $("#chatLineHolder").show();
    $("#chatBottomBar").show();
    $("#chatUsers").show();
    $("#chatLineHolder").scrollTop("99999999");
    id = 0;
  }
  else
  {
    $("#chatLineHolder").hide();
    $(".dialogLineHolder").show();
    $("#chatBottomBar").show();
    $("#chatUsers").hide();
    if($("#"+tab).hasClass("dialog"))
    {
      id = tab.replace("open_","");
      getPrivateDialog(id);
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
      str += " data-login='"+mess.login+"'";
    }
    str += ">"+mess.nickname+"</b>:"+mess.message+"</li>";
  }
  else
  {
    var cls = "";
    if(mess.to_id == active_user)
    {
      cls = "toyou";
    }
    var str = "<li class=\""+cls+"\" id=\"mess_"+mess.id+"\" style=\"color:"+mess.color+"\"><tt>"+mess.time+"</tt> <b data-login='"+mess.login+"'>"+mess.nickname+"</b> для <b data-login='"+mess.to_login+"'>"+mess.to_nickname+"</b>:"+mess.message+"</li>";
  }
  return str;
}

function getPrivateDialog(id)
{
  if(active_user != id)
  {
    $.ajax({
      url:	"/ajaxchat/get_converstation",
      type:	"post",
      data:	"id="+id,
      success: function(json)
      {
	var dialog = jQuery.parseJSON(json);
	if(!dialog.messages)
	{
	  $(".dialogLineHolder").html('<div class="nomess-private">Ваша переписка пуста<br />Начните общение прямо сейчас</div>');
	}
	else
	{
	  $(".dialogLineHolder").html('<ul class="pdialog"></ul>');
	  $.each(dialog.messages,function(){
	    var dialog_string = "<li ";
	    if(this.is_new == "1")
	    {
	      dialog_string += 'class="new"'
	    }
	    dialog_string +=' id="mess_'+this.id+'">'+"<tt>"+this.senddate+"</tt> :"+this.message+"</li>"
	    $(".dialogLineHolder UL").append(dialog_string);
	  })
	  $(".dialogLineHolder").scrollTop("99999999");
	}
      }
    });
  }
}

function sysMes()
{
  if(skipsystem == 0)
  {
    $("#sysvoice").removeClass("on");
    skipsystem = 1;
  }
  else
  {
    $("#sysvoice").addClass("on");
    skipsystem = 0;
  }
}

function sysSound()
{
  if(sound == 1)
  {
    $("#sound").addClass("off");
    sound = 0;
  }
  else
  {
    $("#sound").removeClass("off");
    sound = 1;
  }
}


function loadDialogTab(object){
  $("#chatTopBar UL").append("<li id=\"open_"+object.from_id+"\" id='open_"+object.from_id+"' class=\"dialog\"><span>"+object.from_nickname+"</span><a class='closedialog' id='open_"+object.from_id+"'\"></a></div>");
}


$(window).on("click", requestPermissionNotification);

 
function requestPermissionNotification() {
  if(Notification.permission.toLowerCase() != "granted")
  {
    Notification.requestPermission( function(result) { "granted" } );
  }
}

function newNotify(body,tag,theme,icon) 
{
  if(Notification.permission.toLowerCase() != "granted")
  {
    return false;
  }
  
  var params = {
    body : (body) ? body : "",
    tag : (tag) ? tag : "",
    icon : (icon) ? icon : ""
  };
  
  notification = new Notification(theme, params);
  notify_count++;
};