<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	$user = UserUtils::GetLoggedInUser();
	if($user == null) {
		die();
	}

	$ie6 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 6.0') ? true : false;
	$ie7 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 7.0') ? true : false;
	$ie8 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 8.0') ? true : false;
	$isIE = $ie6 || $ie7 || $ie8;
	$domain = $_SERVER['SERVER_NAME'];

	header("Content-Type: application/javascript");
?>
if(typeof(Gamma) == "undefined") {
	Gamma = {};
}

if(typeof(Gamma.PlaceLauncher) == "undefined") {
	Gamma.PlaceLauncher = {}
}

Gamma.PlaceLauncher.JoinGame = function(id) {
<?php if($isIE): ?>
	$("#ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1").modal({showClose: false, clickClose: false});
	$("#Requesting").css("display", "inline");

	$.post( "/api/gameserverutils", { creategameserver: id }).done(function( data ) {
		if(data != "null") {
			window.setTimeout(function() {

				if ("ActiveXObject" in window) {
					try {
						var app = new ActiveXObject("Roblox.App");
						var workspace = app.CreateGame(2);	// Window

						workspace.ExecUrlScript("http://<?= $domain ?>/game/join.ashx?serverPort="+data);
							
						workspace = app.NullDispatch;
						app = app.NullDispatch;

						Gamma.PlaceLauncher.LoadServers(id);
					} catch (err) {
						window.alert(err.message);
					}
				}

				if($.modal() !== undefined) {
					$.modal().close();
				}
				
			}, 750); 
		}
	});
<?php else: ?>
	window.location.href = "/Install/LaunchGame.aspx";
<?php endif ?>
}

Gamma.PlaceLauncher.JoinServer = function(id) {
<?php if($isIE): ?>
	$("#ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1").modal({showClose: false, clickClose: false});
	$("#Requesting").css("display", "inline");
	window.setTimeout(function() {

		if ("ActiveXObject" in window) {
			try {
				var app = new ActiveXObject("Roblox.App");
				var workspace = app.CreateGame(2);	// Window
				
				workspace.ExecUrlScript("http://<?= $domain ?>/game/join.ashx?serverPort="+id);
					
				workspace = app.NullDispatch;
				app = app.NullDispatch;

				Gamma.PlaceLauncher.LoadServers(id);
			} catch (err) {
				window.alert(err.message);
			}
		}

		if($.modal() !== undefined) {
			$.modal().close();
		}
	}, 750); 
<?php else: ?>
	window.location.href = "/Install/LaunchGame.aspx";
<?php endif ?>
}

Gamma.PlaceLauncher.Cancel = function() {
	if($.modal() !== undefined) {
		$.modal().close();
	}
}
Gamma.PlaceLauncher.VisitPlace = function(place_id) {
	<?php if($isIE): ?>
	$("#ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1").modal({showClose: false, clickClose: false});
	$("#Requesting").css("display", "inline");
	window.setTimeout(function() {

		if ("ActiveXObject" in window) {
			try {
				var app = new ActiveXObject("Roblox.App");
				var workspace = app.CreateGame(2);	// Window

				workspace.ExecUrlScript("http://<?= $domain ?>/game/visit.ashx?placeID="+place_id);
					
				workspace = app.NullDispatch;
				app = app.NullDispatch;
			} catch (err) {
				window.alert(err.message);
			}
		}

		if($.modal() !== undefined) {
			$.modal().close();
		}
	}, 750); 
<?php else: ?>
	window.location.href = "/Install/LaunchGame.aspx";
<?php endif ?>
}

Gamma.PlaceLauncher.EditPlace = function(place_id) {
	<?php if($isIE): ?>
	$("#ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1").modal({showClose: false, clickClose: false});
	$("#Requesting").css("display", "inline");
	window.setTimeout(function() {

		if ("ActiveXObject" in window) {
			try {
				var app = new ActiveXObject("Roblox.App");
				var workspace = app.CreateGame(2);	// Window

				workspace.ExecUrlScript("http://<?= $domain ?>/game/edit.ashx?placeID="+place_id);
					
				workspace = app.NullDispatch;
				app = app.NullDispatch;
			} catch (err) {
				window.alert(err.message);
			}
		}

		if($.modal() !== undefined) {
			$.modal().close();
		}
	}, 750); 
<?php else: ?>
	window.location.href = "/Install/LaunchGame.aspx";
<?php endif ?>
}

if (!Object.keys) {
	Object.keys = function(obj) {
		var keys = [];
		for (var i in obj) {
			if (obj.hasOwnProperty(i)) {
				keys.push(i);
			}
		}
		return keys;
	};
}

function escapeRegExp(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}

function replaceAll(str, find, replace) {
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

Gamma.PlaceLauncher.LoadServers = function(placeid, page) {
	if(page === undefined) {
		page = 1;
	}
	var datalistbody = $("#ctl00_cphRoblox_TabbedInfo_GamesTab_RunningGamesUpdatePanel").find("table").find("tbody");

	datalistbody.children().each(function() {
		$(this).remove();	
	});

	var time = new Date().getTime();
	// time thing is here so that IE doesnt fucking cache the request and use that permanently
	$.get( "/api/gameserverutils?runningservers="+placeid+"&t="+time, function( data ) {
		if(data != "[]") {
			var count = 0;
			for (var key in data){
				if(key == "page" || key == "totalpages") {
					continue;
				}
				var place = data[key];

				if(place['player_count'] == 0) {
					//continue;
				}

				var templatestring = $('#server_template').html();
				templatestring = replaceAll(templatestring, "<div id=\"joinpanel\">", "<td valign=\"top\" width=\"150px\">");
				templatestring = replaceAll(templatestring, "<div id=\"playerspanel\">", "<td valign=\"top\">");
				templatestring = replaceAll(templatestring, "</div>", "</td>");
				
				// ie compatibility
				templatestring = replaceAll(templatestring, "<DIV id=joinpanel>", "<TD valign=\"top\" width=\"150px\">");
				templatestring = replaceAll(templatestring, "<DIV id=playerspanel>", "<TD valign=\"top\">");
				templatestring = replaceAll(templatestring, "</DIV>", "</TD>");
				<?php if($isIE): ?>
				var $template = $(templatestring);
				$template.attr("id", "id_"+place['game_port']);
				<?php else: ?>
				var $template = $("<tr id=\"id_"+place['game_port']+"\">"+templatestring+"</tr>");
				<?php endif ?>

				var joinpanel = $($template.find("td")[0]);
				var playerspanel = $($template.find("td")[1]);
				
				datalistbody.append($template);

				$(joinpanel.find("p")[0]).html(place['player_count']+" of "+place['max_player_count']+" players max");
				players = place['players'].replace("[", "").replace("]", "").replace(" ", "").split(",");
				if(place['player_count'] != 0) {
					var arrayLength = players.length;
					for (var i = 0; i < arrayLength; i++) {
						var player = players[i];
						
						// ugly fucking hack for pre-ES6 js browsers (COUGH COUGH PRE-IE10!!)
						try {
							throw new XMLHttpRequest();
						} catch(xhr) {
							
							function handler() {
								if(this.readyState == 4) {
									if(this.status == 200) {

										// success!
										<?php if($isIE): ?>
										var playerdata = JSON.parse(this.responseText);
										<?php else: ?>
										var playerdata = this.response;
										<?php endif ?>
										$($("#id_"+xhr.url.split("p=")[1]).find("td")[1]).prepend($("<a disabled=\"disabled\" title=\""+playerdata['username']+"\" href=\"/User.aspx?ID="+playerdata['id']+"\" style=\"display:inline-block;\"><img src=\"/thumbs/player?id="+playerdata['id']+"&type=48\" width=\"48\" height=\"48\" border=\"0\" alt=\""+playerdata['username']+"\"></a>"));
									
										//return;
									}
									// something went wrong
								}
							}

							xhr.onreadystatechange = handler;
							xhr.responseType = "json";
							xhr.open("GET", "/api/user?getplayerinfo="+player+"&p="+place['game_port'], true);
							xhr.url = "/api/user?getplayerinfo="+player+"&p="+place['game_port'];
							
							xhr.send();
						}
					}
				}
				
				if(place['player_count'] == place['max_player_count']) {
					joinpanel.find(".Button").attr("disabled", "true");
				}

				<?php if($isIE): ?>
				joinpanel.find(".Button").click(function () {
					Gamma.PlaceLauncher.JoinServer(place['game_port']);
				});
				<?php else: ?>
				joinpanel.find(".Button").click(function () {
					window.location.href = "/Install/LaunchGame.aspx";
				});
				<?php endif ?>
				
				count += 1;
				
			}
			if(count == 0) {
				datalistbody.append("<tr><td><b>No running games!</b></td></tr>");
			}
		} else {
			datalistbody.append("<tr><td><b>No running games!</b></td></tr>");
		}
	});

	
}