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
var user_id;
var user_login;
var user_nickname;

$(document).on("click", "a.closedialog", function() {
  $(".dialogLineHolder UL LI.new.from_him").each(function(){
    $(this).removeClass("new");
    var mess_id = $(this).attr("id").replace("mess_","");
    $.ajax({
      url:	'/ajaxchat/read_pmessage',
      type:	'post',
      data:	'id='+mess_id
    })
  })

  $("#chatTopBar UL #"+$(this).attr("id")).remove();
  listTab("chatrum");
});

$(document).on("click","#chatTopBar UL LI SPAN",function(){
  listTab($(this).parent().attr("id"));
})

$("#chatText").focus(function(){
  $(this).find("BR").remove();
})

$(document).on("click","IMG.startdialog",function(){
  var from_id = $(this).closest("li").attr("user-id");
  var from_nickname = $(this).closest("li").text();
  
  $("#chatTopBar UL").append("<li id=\"open_"+from_id+"\" id='open_"+from_id+"' class=\"dialog\"><span>"+from_nickname+"</span><a class='closedialog' id='open_"+from_id+"'\"></a></div>");
  listTab("open_"+$(this).closest("li").attr("user-id"));
})

$(document).on("click","IMG.sendpublic",function(){

  var login = $(this).closest("li").attr("login-id");
  var color = $(this).closest("li").attr("user-color");
  var nickname = $(this).closest("li").find("A").text();
  if($("LI.chatuser#chatuser_"+active_user).attr("login-id") != login)
  {
    var apd = '<b contenteditable="false" style="color:'+color+'" data-login="'+login+'">'+nickname+'</b>';
    if($("#chatText").html().indexOf(apd) === -1)
    {
      $("#chatText").append(apd);
      el = $("#chatText").get(0);
      placeCaretAtEnd(el);
    }
  }
})

$(document).on("click","#chatLineHolder LI B",function(){
  if(user_login != $(this).attr("data-login"))
  {
    var object = this;
    var login = $(this).attr("data-login");
    $.ajax({
      url:	'/ajaxchat/get_color',
      type:	'post',
      data:	'login='+login,
      success:	function(color)
      {
	var apd = '<b contenteditable="false" style="color:'+color+'" data-login="'+$(object).attr("data-login")+'">'+$(object).text()+'</b> ';
	if($("#chatText").html().indexOf(apd) === -1)
	{
	  $("#chatText").append(apd);
	  el = $("#chatText").get(0);
	  placeCaretAtEnd(el);
	}
      }
    })
  }
});

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

$(document).on("click","#images_container IMG",function(){
    $(this).remove();
});

$(document).on("click","#chatText B",function(){
    $(this).remove();
});

$(document).on("mouseover",".dialogLineHolder UL LI.new.from_him",function(){
  $(this).removeClass("new");
  var mess_id = $(this).attr("id").replace("mess_","");
  $.ajax({
      url:	'/ajaxchat/read_pmessage',
      type:	'post',
      data:	'id='+mess_id
  })
});

$(document).on("click","IMG.emo_s",function(){
  var img = '<img class="emo_c" src="'+$(this).attr("src")+'">';
  $('#chatText').append(img);
  if(!$("#smilecollection IMG[src='"+$(this).attr("src")+"']").is("IMG"))
  {
    $('#smilecollection IMG:nth-child(5)').remove();
    $('#smilecollection').prepend('<img class="emo_s" src="'+$(this).attr("src")+'">');
    
    var smstr = "";
    $("#smilecollection IMG").each(function(){
      smstr += $(this).attr("src")+",";
    })
    
    $.ajax({
      url:	'/ajaxchat/savesmile',
      type:	'post',
      data:	'smiles='+smstr
    })
    
  }
})

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
	
	$.ajax({
	  url:	'/ajaxchat/me',
	  type:	'post',
	  success: function(ansver)
	  {
	    var user = jQuery.parseJSON(ansver);
	    user_id = user.id;
	    user_login = user.login;
	    user_nickname = user.nickname;
	    $.each(user.config.smiles,function(){
	      $('#smilecollection').apend('<img class="emo_s" src="'+this+'">');
	    })
	  }
	})
	
	onLineUsers();
	get_messages();
	setInterval(loadNewMessages, 5000);
	setInterval(onLineUsers, 15000);

	$f("player", "/components/ajaxchat/js/fp/flowplayer-3.2.14.swf",{
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
	
	$("#smilebutton").click(function(){
	  $("#smilelist").toggle();
	})
	

  /*UPLOADER CODE*/
  var uploader = new ss.SimpleUpload({
      button: 'insertimage', // HTML element used as upload button
      url: '/ajaxchat/img_upload', // URL of server-side upload handler
      responseType: 'json',
      name: 'uploadfile' ,
      allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
      maxSize: 1024,
      onComplete: function(filename, response) 
      {
	$("#images_container").append('<img src="/upload/userfiles/'+filename+'">');
	if (!response) {
	  alert(filename + 'upload failed');
          return false;            
	}
      }
  });
});

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
  var message = $("#chatText").html();
  message += $("#images_container").html();
  var id = $("#chatTopBar .active").attr("id");

  if(message.length >= 2)
  {
    if(message == "/clean")
    {
      $("#chatLineHolder UL").html("");
    }
    else
    {
      $.ajax({
	url:	'/ajaxchat/send_message',
	type:	'post',
	data:	'message="'+encodeURIComponent(message)+'"&id='+id,
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
  $("#chatText").html("");
  $("#images_container").html("");
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
	  
	  if(this.active)
	  {
	    active_user = this.user_id;
	  }
	  
	  if(!this.imageurl)
	  {
	    this.imageurl = "noprofile.jpg";
	  }

	  if($("#chatuser_"+this.user_id).text().length == 0)
	  {
	    var userstring = '<li class="chatuser" id="chatuser_'+this.user_id+'" user-color="'+this.color+'" user-id="'+this.user_id+'" login-id="'+this.login+'"><a href="/users/'+this.login+'">';
	    if(this.config)
	    {
	      if(this.config.mobile)
	      {
		userstring += '<img class="activestatus" src="/components/ajaxchat/img/onphone.png">';
	      }
	      else if(this.on_chat == 1)
	      {
		userstring += '<img class="activestatus" src="/components/ajaxchat/img/online.png">';
	      }
	      else
	      {
		userstring += '<img class="activestatus" src="/components/ajaxchat/img/offline.png">'; 
	      }
	    }
	    else 
	    {
	      if(this.on_chat == 1)
	      {
		userstring += '<img class="activestatus" src="/components/ajaxchat/img/online.png">';
	      }
	      else
	      {
		userstring += '<img class="activestatus" src="/components/ajaxchat/img/offline.png">';
	      }
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
	    if(this.config)
	    {
	      if(this.config.mobile == true)
	      {
		$("#chatuser_"+this.user_id+" IMG.activestatus").attr("src","/components/ajaxchat/img/onphone.png");
	      }
	    }
	    else if(this.on_chat == 1)
	    {
	      $("#chatuser_"+this.user_id+" IMG.activestatus").attr("src","/components/ajaxchat/img/online.png");
	    }
	    else
	    {
	      $("#chatuser_"+this.user_id+" IMG.activestatus").attr("src","/components/ajaxchat/img/offline.png");
	    }
	    
	    $("#chatuser_"+this.user_id).removeClass("oldOnlineUsers");
	  }
	  //Обновляем цвета в чате а вдруг сменил
	  var usercolor = this.color;
	  var userlogin = this.login;
	  $("#chatLineHolder UL li b[data-login="+this.login+"]").each(function(){ 
	    if($(this).parent().find("b").first().attr("data-login") == userlogin)
	    {
	      $(this).parent().css("color",usercolor);
	    }
	  })
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


  var dialog = $("#chatTopBar UL LI.dialog.active").attr("id");
  if(dialog)
  {
    dialog = dialog.replace("open_","");
  }

  if(upd == 0)
  {
    upd = 1;
    $.ajax({
      url:	"/ajaxchat/load_new",
      type:	"post",
      data:	"last_id="+last_id+"&skipsystem="+skipsystem+"&dialog="+dialog,
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
	    if(this.id > last_id)
	    {
	      if($("#mess_"+this.id).text().length == 0)
	      {
		$("#chatLineHolder UL").append(formatMessage(this));
		$("#chatLineHolder").scrollTop("99999999");
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

	    if($('#open_'+this.from_id).text().length == 0)
	    {
	      loadDialogTab(this);
	    }
	    $('#open_'+this.from_id).addClass('have_new');
	  })
	}
// 	Обновление активного диалога 
	var active_id = $('#chatTopBar LI.dialog.active').attr("id");
	if(active_id)
	{
	  getPrivateDialog(active_id.replace("open_",""));
	}
      }
    });
    upd = 0;
  }
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
  $("#chatTopBar UL LI#"+tab).removeClass("have_new");
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
    mess.imageurl = "noprofile.jpg";
  }
  if(mess.user_id == "0")
  {
    var str = "<li class=\"sysmes\" id=\"mess_"+mess.id+"\"><tt>"+mess.time+"</tt> <i>"+mess.message+"</i></li>";
  }
  else
  {
    if($($.parseHTML(mess.message)).attr("data-user-id") == user_id && $($.parseHTML(mess.message)).attr("data-user-id") !== undefined)
    {
      var cls = ' class="toyou"';
    }
    else
    {
      var cls = "";
    }
    
    var str = "<li"+cls+" id=\"mess_"+mess.id+"\" style=\"color:"+mess.message_color+"\"><tt>"+mess.time+"</tt> <b"; 
    if(mess.login)
    {
      str += " data-login='"+mess.login+"'";
    }
    str += ">"+mess.nickname+"</b>:"+mess.message+"</li>";
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
	    var dialog_string = '<li class="';
	    if(this.is_new == "1")
	    {
	      dialog_string += 'new '
	    }

	    if(this.from_id == active_user)
	    {
	      dialog_string += 'from_you ';
	    }
	    else
	    {
	      dialog_string += 'from_him';
	    }

	    dialog_string +='" id="mess_'+this.id+'">'+this.message+"</li>"
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

function placeCaretAtEnd(el) {
    el.focus();
    if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.collapse(false);
        textRange.select();
    }
}

if (!Array.prototype.jlast){
    Array.prototype.jlast = function(){
        return this[this.length - 1];
    };
};