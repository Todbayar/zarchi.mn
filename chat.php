<?php
include "mysql_config.php";
include "info.php";
?>
<script>
$(document).ready(function(){
	Notification.requestPermission().then((permission) => {
		if (permission === 'granted') {
		  console.log('Notification permission granted.');
		}
	});
	
	$(".chat .right .bottom #chatMessage").keydown( function( event ) {
		if(event.which === 13){
			event.preventDefault();
			$(".chat .right .bottom .button_yellow").trigger("click");
		}
	});
	
//	$(".chat .right .top").scroll(function(){
//		console.log("<scroll>:"+$(".chat .right .top").scrollTop()+", "+$(".chat .right .top").height()+", "+$(".chat .right .top").innerHeight());
//	});
	
	if(sessionStorage.getItem("startChatToID")!=null){
		chat_select(sessionStorage.getItem("startChatToID"));
		sessionStorage.removeItem("startChatToID");
	}
});
	
function chat_send(toID){
	var chatSubmitData = new FormData();
	chatSubmitData.append("fromID", <?php echo $_COOKIE["userID"]; ?>);
	chatSubmitData.append("toID", toID);
	chatSubmitData.append("type", 0);
	chatSubmitData.append("message", $("#chatMessage").val());
	
	const reqChatSubmit = new XMLHttpRequest();
	reqChatSubmit.onload = function() {
		$("#chatMessage").val("");
		chat_select(toID);
	};
	reqChatSubmit.onerror = function(){
		console.log("<chat_send>:" + reqChatSubmit.status);
	};

	reqChatSubmit.open("POST", "chat_process.php", true);
	reqChatSubmit.send(chatSubmitData);
}
	
function chat_select(toID){
	$(".left .user").css("background-color", "transparent");
	$(".left #chatSelect"+toID).css("background-color", "#d7edf7");
	
	$(".chat .right .bottom").show();
	$(".chat .right .bottom .button_yellow").attr("onclick", "chat_send("+toID+")");
	
	const reqChatFetchSubmit = new XMLHttpRequest();
	reqChatFetchSubmit.onload = function() {
		if(this.responseText != ""){
			$(".chat .right .top").empty();
			const responseChatFetch = JSON.parse(this.responseText);
			responseChatFetch.forEach(function(chatRow){
				if(parseInt(chatRow.type) == 1){
					chat_list_category(chatRow.sender, chatRow.body, chatRow.datetime, chatRow.isEdit);
				}
				else if(parseInt(chatRow.type) == 2){
					chat_list_item(chatRow.sender, chatRow.body, chatRow.datetime, chatRow.isEdit);
				}
				else if(parseInt(chatRow.type) == 3){
					chat_list_role(chatRow.sender, chatRow.body, chatRow.datetime, chatRow.isEdit);
				}
				else {
					chat_list_message(chatRow.sender, chatRow.body, chatRow.datetime, chatRow.isEdit);
				}
				$(".chat .right .top").scrollTop($(".chat .right .top").innerHeight()*2);
			});
			$("div.message:last-child").css("margin-bottom","35px");
		}
	};
	reqChatFetchSubmit.onerror = function(){
		console.log("<chat_select>:" + reqChatFetchSubmit.status);
	};
	
	reqChatFetchSubmit.open("GET", "chat_fetch.php?toID="+toID, true);
	reqChatFetchSubmit.send();
}

function chat_list_role(sender, body, datetime, isEdit){
	if(sender.id == <?php echo $_COOKIE["userID"]; ?>){
	   	var htmlRole = "<div class=\"message me\"><div class=\"container\"><div class=\"text\"><i class=\"fa-solid fa-user\" style=\"position:absolute; top:0; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
	   	htmlRole += body.message;
		htmlRole += "</div><div class=\"datetime\">"+datetime+"</div>";
	    if(isEdit) htmlRole += chat_list_role_action_show(body.isActive, body.id, true);
		htmlRole += "</div>"+chat_profile_image_show(sender.image)+"</div>";
		$(".chat .right .top").append(htmlRole);
    }
	else {
	   	var htmlRole = "<div class=\"message\">"+chat_profile_image_show(sender.image)+"<div class=\"container\"><div class=\"text\"><i class=\"fa-solid fa-user\" style=\"position:absolute; top:0; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		htmlRole += body.message;
		htmlRole += "</div><div class=\"datetime\">"+datetime+"</div>";
		if(isEdit) htmlRole += chat_list_role_action_show(body.isActive, body.id, false);
		htmlRole += "</div></div>";
		$(".chat .right .top").append(htmlRole);
    }
}

function chat_list_role_action_show(isactive, id, isMe){
	if(isactive == 0){	//review
		if(!isMe){
			return "<div id=\"2"+id+"\" class=\"action\"><div onClick=\"chat_action(0,2,"+id+")\" class=\"button_yellow\" style=\"float: left\"><i class=\"fa-solid fa-check\"></i></div></div>";
	   	}
		else {
			return "<div id=\"2"+id+"\" class=\"action\" style=\"display:flex; justify-content: flex-end\"><div onClick=\"chat_action(0,2,"+id+")\" class=\"button_yellow\" style=\"float: left\"><i class=\"fa-solid fa-check\"></i></div></div>";
		}
	}
	else {
		return "";
	}
}

function chat_list_category(sender, body, datetime, isEdit){
	if(sender.id == <?php echo $_COOKIE["userID"]; ?>){
		var htmlCategory = "<div class=\"message me\"><div class=\"container\"><div class=\"text\"><i class=\"fa-solid fa-sitemap\" style=\"position:absolute; top:0; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
	   	if(body.isActive == 1){
			htmlCategory += "<i class=\"fa-solid fa-arrow-rotate-left\" style=\"position:absolute; top:20px; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
		}
		else if(body.isActive == 2){
			htmlCategory += "<i class=\"fa-solid fa-check\" style=\"position:absolute; top:20px; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
		}
	   	htmlCategory += body.title;
	   	htmlCategory += chat_list_category_show(body.category, body.title);
		htmlCategory += chat_list_category_words_show(body.words);
		htmlCategory += "</div><div class=\"datetime\">"+datetime+"</div>";
		if(isEdit) htmlCategory += chat_list_category_action_show(body.isActive, body.category.length+"_"+body.id, true);
		htmlCategory += "</div>"+chat_profile_image_show(sender.image)+"</div>";
		$(".chat .right .top").append(htmlCategory);
	}
	else {
		var htmlCategory = "<div class=\"message\">"+chat_profile_image_show(sender.image)+"<div class=\"container\"><div class=\"text\"><i class=\"fa-solid fa-sitemap\" style=\"position:absolute; top:0; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		if(body.isActive == 1){
			htmlCategory += "<i class=\"fa-solid fa-arrow-rotate-left\" style=\"position:absolute; top:20px; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		}
		else if(body.isActive == 2){
			htmlCategory += "<i class=\"fa-solid fa-check\" style=\"position:absolute; top:20px; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		}
		htmlCategory += body.title;
		htmlCategory += chat_list_category_show(body.category, body.title);
		htmlCategory += chat_list_category_words_show(body.words);
		htmlCategory += "</div><div class=\"datetime\">"+datetime+"</div>";
		if(isEdit) htmlCategory += chat_list_category_action_show(body.isActive, body.category.length+"_"+body.id);
		htmlCategory += "</div></div>";
		$(".chat .right .top").append(htmlCategory);
	}
}

function chat_list_item(sender, body, datetime, isEdit){
	if(sender.id == <?php echo $_COOKIE["userID"]; ?>){
	   	var htmlItem = "<div class=\"message me\"><div class=\"container\"><div class=\"text\"><i class=\"fa-solid fa-cart-shopping\" style=\"position:absolute; top:0; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
	   	if(body.isActive == 3){
			htmlItem += "<i class=\"fa-solid fa-arrow-rotate-left\" style=\"position:absolute; top:20px; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
		}
		else if(body.isActive == 4){
			htmlItem += "<i class=\"fa-solid fa-check\" style=\"position:absolute; top:20px; left:-15px; padding:5px; border-radius:10px; background: #fff4d0; color:#878787\"></i>";
		}
	   	htmlItem += body.title;
	   	htmlItem += chat_list_category_show(body.category, "");
		htmlItem += "</div><div class=\"datetime\">"+datetime+"</div>";
		if(isEdit) htmlItem += chat_list_item_action_show(body.isActive, body.id, true);
		htmlItem += "</div>"+chat_profile_image_show(sender.image)+"</div>";
		$(".chat .right .top").append(htmlItem);
	}
	else {
		var htmlItem = "<div class=\"message\">"+chat_profile_image_show(sender.image)+"<div class=\"container\"><div class=\"text\"><i class=\"fa-solid fa-cart-shopping\" style=\"position:absolute; top:0; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		if(body.isActive == 3){
			htmlItem += "<i class=\"fa-solid fa-arrow-rotate-left\" style=\"position:absolute; top:20px; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		}
		else if(body.isActive == 4){
			htmlItem += "<i class=\"fa-solid fa-check\" style=\"position:absolute; top:20px; right:-15px; padding:5px; border-radius:10px; background: #e7e7e7; color:#878787\"></i>";
		}
		htmlItem += body.title;
		htmlItem += chat_list_category_show(body.category, "");
		htmlItem += "</div><div class=\"datetime\">"+datetime+"</div>";
		if(isEdit) htmlItem += chat_list_item_action_show(body.isActive, body.id);
		htmlItem += "</div></div>";
		$(".chat .right .top").append(htmlItem);
	}
}

function chat_list_message(sender, body, datetime){
	if(sender.id == <?php echo $_COOKIE["userID"]; ?>){
		$(".chat .right .top").append("<div class=\"message me\"><div class=\"container\"><div class=\"text\">"+body+"</div><div class=\"datetime\">"+datetime+"</div></div><img src=\"<?php echo $path; ?>/"+sender.image+"\"></div>");
	}
	else {
		$(".chat .right .top").append("<div class=\"message\"><img src=\"<?php echo $path; ?>/"+sender.image+"\"><div class=\"container\"><div class=\"text\">"+body+"</div><div class=\"datetime\">"+datetime+"</div></div></div>");
	}
}

function chat_list_category_show(categories, title){
	var vCategory = "<div style=\"font-size:12px; color:gray; margin-top:5px\">";
	for(let i=0; i<categories.length; i++){
		if(i+1<categories.length){
			vCategory += categories[i]+"<i class=\"fas fa-angle-right\" style=\"font-size:10px; margin-left:2px; margin-right:2px\"></i>";   
		}
		else {
			if(categories[i] != title) vCategory += categories[i];
		}
	}
	return "<br/>"+vCategory+"</div>";
}
	
function chat_list_category_words_show(words){
	return "<div style=\"font-size:12px; color:gray; margin-top:5px\">"+words+"</div>";
}

function chat_list_category_action_show(isactive, id, isMe = false){
	if(isactive == 0){	//review
		if(!isMe){
	   		return "<div id=\"0"+id+"\" class=\"action\"><div onClick=\"chat_action(0,0,'"+id+"')\" class=\"button_yellow\" style=\"float: left\"><i class=\"fa-solid fa-check\"></i></div><div onClick=\"chat_action(1,0,'"+id+"')\" class=\"button_yellow\" style=\"float: left; margin-left: 5px\"><i class=\"fa-solid fa-arrow-rotate-left\"></i></div></div>";
		}
		else {
			return "<div id=\"0"+id+"\" class=\"action\" style=\"display:flex; justify-content: flex-end\"><div onClick=\"chat_action(0,0,'"+id+"')\" class=\"button_yellow\" style=\"float: left\"><i class=\"fa-solid fa-check\"></i></div><div onClick=\"chat_action(1,0,'"+id+"')\" class=\"button_yellow\" style=\"float: left; margin-left: 5px\"><i class=\"fa-solid fa-arrow-rotate-left\"></i></div></div>";
		}
	}
	else {
		return "";
	}
}
	
function chat_list_item_action_show(isactive, id, isMe = false){
	if(isactive == 1){	//review
		if(!isMe){
			return "<div id=\"1"+id+"\" class=\"action\"><div onClick=\"chat_action(0,1,"+id+")\" class=\"button_yellow\" style=\"float: left\"><i class=\"fa-solid fa-check\"></i></div><div onClick=\"chat_action(1,1,"+id+")\" class=\"button_yellow\" style=\"float: left; margin-left: 5px\"><i class=\"fa-solid fa-arrow-rotate-left\"></i></div></div>";   
	   	}
		else {
			return "<div id=\"1"+id+"\" class=\"action\" style=\"display:flex; justify-content: flex-end\"><div onClick=\"chat_action(0,1,"+id+")\" class=\"button_yellow\" style=\"float: left\"><i class=\"fa-solid fa-check\"></i></div><div onClick=\"chat_action(1,1,"+id+")\" class=\"button_yellow\" style=\"float: left; margin-left: 5px\"><i class=\"fa-solid fa-arrow-rotate-left\"></i></div></div>";
		}
	}
	else {
		return "";
	}
}

function chat_profile_image_show(image){
	if(image != ""){
		return "<img src=\"<?php echo $path; ?>/"+image+"\">";
	}
	else {
		return "<img src=\"user.png\">";
	}
}

function chat_action(action, type, id){
	//action 0=accept, 1=dismiss
	//type 0=category, 1=item, 2=role
	//id rowid
	
	var chatActionSubmitData = new FormData();
	chatActionSubmitData.append("action", action);
	chatActionSubmitData.append("type", type);
	chatActionSubmitData.append("id", id);

	const reqChatActionSubmit = new XMLHttpRequest();
	reqChatActionSubmit.onload = function() {
		if(this.responseText == "OK"){
			$("div#"+type+""+id).hide();
		}
	};
	reqChatActionSubmit.onerror = function(){
		console.log("<chat_send>:" + reqChatActionSubmit.status);
	};

	reqChatActionSubmit.open("POST", "chat_action.php", true);
	reqChatActionSubmit.send(chatActionSubmitData);
}

//function chat_input_enter(evt){
//	if(evt.keyCode == 13){
//		$(".chat .right .bottom .button_yellow").trigger("click");
//    }
//}
</script>

<div class="chat">
	<div class="left">
<!--
		<div id="chatSelect0" class="user" onClick="chat_select(0)">
			<div class="container">
				<div class="box">
					<div class="profile">
						<img src="user.png">
					</div>
					<div>
						<div class="name"><?php echo $title; ?></div>
						<div class="role">Сүпер админ</div>
					</div>
				</div>
			</div>
		</div>
-->
		<?php
		$queryFetchSender = "SELECT *, (SELECT name FROM user WHERE id=chat.fromID) AS name, (SELECT image FROM user WHERE id=chat.fromID) AS image, (SELECT role FROM user WHERE id=chat.fromID) AS role FROM chat WHERE toID=".$_COOKIE["userID"];
//		if($_COOKIE["role"]>0){
//			$queryFetchSender .= " OR toID=0";
//		}
		$queryFetchSender .= " GROUP BY fromID ORDER BY datetime DESC";
		$resultFetchSender = $conn->query($queryFetchSender);
		while($rowFetchSender = mysqli_fetch_array($resultFetchSender)){
		?>
		<div id="chatSelect<?php echo $rowFetchSender["fromID"]; ?>" class="user" onClick="chat_select(<?php echo $rowFetchSender["fromID"]; ?>)">
			<div class="container">
				<div class="box">
					<div class="profile">
						<?php
						if($rowFetchSender["image"] != ""){
						?>
						<img src="<?php echo $path.DIRECTORY_SEPARATOR.$rowFetchSender["image"]; ?>">
						<?php
						}
						else {
						?>
						<img src="user.png">
						<?php
						}
						?>
					</div>
					<div>
						<div class="name"><?php echo $rowFetchSender["name"]; ?></div>
						<div class="role">
							<?php
							switch($rowFetchSender["role"]){
								case 0:
									echo "Хэрэглэгч";
									break;
								case 1:
									echo "Нийтлэгч";
									break;
								case 2:
									echo "Менежер";
									break;
								case 3:
									echo "Админ";
									break;
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		}
		?>
	</div>
	<div class="right">
		<div class="top"></div>
		<div class="bottom" style="display: none">
			<input id="chatMessage" type="text" placeholder="Энд бичнэ үү" />
			<div onClick="chat_send(0)" class="button_yellow" style="float: right; height: 16px">
				<i class="fa-solid fa-paper-plane"></i>
			</div>
		</div>
	</div>
</div>