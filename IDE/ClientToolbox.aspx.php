<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Toolbox</title>
		<link href="/CSS/Toolbox.css" type="text/css" rel="stylesheet">
		<script src="/js/jquery.js"></script>
		<script id="Functions" type="text/jscript">
			var tb_page = 1;

			function insertContent(id) {
				try {
					window.external.Insert("http://<?= $_SERVER['SERVER_NAME'];?>/asset/?id=" + id);
				} catch(x) {
					alert("Could not insert the requested item");
				}			    
			}

			function clickButton(e, buttonid) {
				var bt = document.getElementById(buttonid);
				if (typeof bt == 'object') {
					if(navigator.appName.indexOf("Netscape")>(-1)) {
						if (e.keyCode == 13) {
							bt.click();
							return false;
						}
					}
					if (navigator.appName.indexOf("Microsoft Internet Explorer")>(-1)) {
						if (event.keyCode == 13) {
							bt.click();
							return false;
						}
					}
				}
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

			function AdvancePage() {
				LoadCategory($('#ddlToolboxes').find(":selected").val(),$('.Search').val(), tb_page+1);
			}

			function DeadvancePage() {
				LoadCategory($('#ddlToolboxes').find(":selected").val(),$('.Search').val(), tb_page-1);
			}

			function LoadCategory(category, search, page) {
				if(page === undefined && search === undefined) {
					$('.Search').val("");
					tb_page = 1;
				}

				if(page === undefined) {
					page = 1;
				}

				if(search === undefined) {
					search = "";
				}

				tb_page = page;

				var datalistbody = $("#dlToolboxItems");
				datalistbody.children().each(function() {
					$(this).remove();		
				});
				var time = new Date().getTime();
				$.get( "/api/toolbox?c="+category+"&q="+encodeURIComponent(search)+"&p="+page+"&t="+time, function( data ) {
					if(Object.keys(data).length != 0) {
						for (var key in data){
							if(key == "page" || key == "totalpages" || key == "assetcount" || key == "totalassets") {
								continue;
							}
							var asset = data[key];
							var $template = $($('#template').html());
							var namelink = $($template.find('a')[0]);
							namelink.attr("title", asset["Name"]);
							namelink.attr("href", "javascript:insertContent("+asset["ID"]+")");
							
							$(namelink.find("img")[0]).attr("alt", asset["Name"]);
							$(namelink.find("img")[0]).attr("src", "/thumbs/?id="+asset["ID"]+"&type=120");
							
							$template.css("display", "");
							datalistbody.append($template);
						}

						if(data['totalpages'] !== undefined) {
							if(data['totalpages'] != 1) {
								$(".Navigation").css("display", "block");

								var startpage = data['page']-1;
								startpage *= 20;
								var startasset = startpage+1;
								var endasset = startpage+data['assetcount'];

								if(data['page'] == 1) {
									$("#Previous").html("");
								} else {
									$("#Previous").html("&lt;&lt; Prev");
								}

								if(data['page'] >= data['totalpages']) {
									$("#Next").html("");
								} else {
									$("#Next").html("Next &gt;&gt;");
								}

								$("#Location").html(startasset+"-"+endasset+" of " + data['totalassets']);
							} else {
								$(".Navigation").css("display", "none");
							}
						} else {
							$(".Navigation").css("display", "none");
						}
					}

					if(category == 13 || category == 11) {
						$("#ToolboxSearch").css("display", "block");
					} else {
						$("#ToolboxSearch").css("display", "none");
					}
				});
			}

			function selectCategory() {
				LoadCategory($('#ddlToolboxes').find(":selected").val());
			}

			function Search() {
				LoadCategory($('#ddlToolboxes').find(":selected").val(),$('.Search').val());
			}

			$(function() {
				LoadCategory(1);
			});
		</script>
		<style>
			.ToolboxItem {
				margin: 6px;
			}
		</style>
	</head>
	<body class="Page" bottommargin="0" leftmargin="0" rightmargin="0">
		<form name="fToolbox" method="post" action="/IDE/ClientToolbox.aspx" id="fToolbox">
			<div id="ToolboxContainer">
				<div id="ToolboxControls">
					<div id="ToolboxSelector">
						<select name="ddlToolboxes" id="ddlToolboxes" class="Toolboxes" onchange="selectCategory()">
							<option selected="selected" value="1">Bricks</option>
							<option value="2">Robots</option>
							<option value="3">Chassis</option>
							<option value="4">Tools</option>
							<option value="5">Furniture</option>
							<option value="6">Roads</option>
							<option value="7">Skyboxes</option>
							<option value="8">Billboards</option>
							<option value="9">Game Objects</option>
							<option value="10">My Decals</option>
							<option value="11">All Decals</option>
							<option value="12">My Models</option>
							<option value="13">All Models</option>
						</select>
					</div>
					<div id="ToolboxSearch" style="display: none">
						<input type="text" class="Search">
						<input type="submit" id="Button" value="Search" onclick="Search(); return false;">
						<br>
					</div>
					
				</div>
				<div id="template">
					<span class="ToolboxItem" onmouseover="this.style.borderStyle='outset'" onmouseout="this.style.borderStyle='solid'" style="display:none;border-style: solid;">
						<a id="dlToolboxItems_ctl00_ciToolboxItem" title="" href="" style="display:inline-block;height:62px;width:60px;cursor:pointer;">
							<img style="height:60px;width:60px;" src="" border="0" alt="" blankurl="http://t0ak.roblox.com/p1-blank-60x62.gif">
						</a>
					</span>
				</div>
				<div id="ToolboxItems">
					<span id="dlToolboxItems" style="display:inline-block;width:100%;">
						
					</span>
				</div>
				<div class="Navigation">
					<a href="javascript:DeadvancePage()" id="Previous"></a>
					<span id="Location"></span>
					<a href="javascript:AdvancePage()" id="Next"></a>
				</div>
			</div>
		</form>
	</body>
</html>