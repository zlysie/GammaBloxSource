<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	$user = UserUtils::GetLoggedInUser();
	$isIE = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE') ? true : false;;

	if($user == null) { // if they are not logged in then tell them to login
		die();
	}
?>
// Hello unfortunate snooper
// This is shit code sorry

Object.keys = function(obj) {
	var keys = [];
	for (var i in obj) {
		if (obj.hasOwnProperty(i)) {
			keys.push(i);
		}
	}
	return keys;
};

var currentSelectCategory = 0;
var currentPage = 1;
var pageCount = 0;

function LoadToSelectAssets(category, page) {
	if(page === undefined) {
		page = 1;
	}

	currentPage = page;

	if(category === undefined) {
		category = currentSelectCategory;
	} else {
		currentSelectCategory = category;
	}
	
	

	var datalistbody = $("#SelectFromAssetsTable").find("tbody");
	
	$("a#category").each(function() {
		if($(this).attr('c') != category) {
			$(this).attr('class','');
		} else {
			$(this).attr('class','AttireCategorySelector_Selected');
		}
	});

	datalistbody.children().each(function() {
		$(this).children().each(function() {
			$(this).remove();		
		})
	});

	$.get( "/api/characterstuff?c="+category+"&p="+page, function( data ) {
		$("#SelectPages").html("&nbsp;");
		
		if(Object.keys(data).length != 0) {
			var index = 0;
			var trIndex = 0;
			for (var key in data){
				
				if(key == "page" || key == "totalpages") {
					continue;
				}
				var asset = data[key];
				var tdwrapper = $('<TD class="Asset" valign="top"></TD>');
				var $template = $( $('#asset_template').html());
				$template = $(tdwrapper.append($template)); 
				var namelink = $template.find('.AssetName').find("A");
				namelink.html(asset["Name"]);
				namelink.attr("href", "/Item.aspx?ID="+asset["ID"]);
				var creatornamelink = $template.find('.AssetCreator').find("A");
				creatornamelink.html(asset["CreatorName"]);
				creatornamelink.attr("href","/User.aspx?ID="+asset["CreatorUserID"]);
				
				var imagelink = $template.find('#AssetThumbnailLink');
				imagelink.attr("href", "javascript:Wear("+asset["ID"]+")");
				imagelink.attr("title", asset["Name"]);

				$(imagelink.find("img")[0]).attr("alt", asset["Name"]);
				$(imagelink.find("img")[0]).attr("src", "/thumbs/?id="+asset["ID"]+"&type=120");

				$template.find(".DeleteButtonOverlay").attr("href", "javascript:Wear("+asset["ID"]+")")
				$(datalistbody.find("tr")[trIndex]).append($template);
				
				index += 1;
				if(index % 4 == 0) {
					trIndex += 1;
				}
			}
			
			if(Object.keys(data).length < 4) {
				for(var i = 0; i < 5 - Object.keys(data).length; i++) {
					$(datalistbody.find("tr")[0]).append("<TD></TD>");
				}
			}

			if(data.totalpages > 1) {
				if(data.page == 1) {
					$("#FirstLinker").css("color", "#ccc");
					$("#FirstLinker").attr("disabled", "disabled");
					$("#FirstLinker").attr("onclick", "return false;");
					$("#PreviousLinker").css("color", "#ccc");
					$("#PreviousLinker").attr("disabled", "disabled");
					$("#PreviousLinker").attr("onclick", "return false;");
				} else {
					$("#FirstLinker").css("color", "");
					$("#FirstLinker").attr("disabled", "");
					$("#FirstLinker").attr("onclick", "");
					$("#PreviousLinker").css("color", "");
					$("#PreviousLinker").attr("disabled", "");
					$("#PreviousLinker").attr("onclick", "");
				}

				if(data.page >= data.totalpages) {
					$("#NextLinker").css("color", "#ccc");
					$("#NextLinker").attr("disabled", "disabled");
					$("#NextLinker").attr("onclick", "return false;");
					$("#LastLinker").css("color", "#ccc");
					$("#LastLinker").attr("disabled", "disabled");
					$("#LastLinker").attr("onclick", "return false;");
				} else {
					$("#NextLinker").css("color", "");
					$("#NextLinker").attr("disabled", "");
					$("#NextLinker").attr("onclick", "");
					$("#LastLinker").css("color", "");
					$("#LastLinker").attr("disabled", "");
					$("#LastLinker").attr("onclick", "");
				}

				for (var i = data.totalpages; i > 0; i--) {
					if(i == page) {
						$("#SelectPages").prepend("&nbsp;<a style=\"color:#ccc\" onclick=\"return false;\" href=\"javascript:LoadToSelectAssets(currentSelectCategory, "+(i)+")\">"+(i)+"</a>");
					} else {
						$("#SelectPages").prepend("&nbsp;<a href=\"javascript:LoadToSelectAssets(currentSelectCategory, "+(i)+")\">"+(i)+"</a>");
					}
					
				}

				pageCount = data.totalpages;
			} else {
				
				$("#FirstLinker").css("color", "#ccc");
				$("#FirstLinker").attr("disabled", "disabled");
				$("#FirstLinker").attr("onclick", "return false;");
				$("#PreviousLinker").css("color", "#ccc");
				$("#PreviousLinker").attr("disabled", "disabled");
				$("#PreviousLinker").attr("onclick", "return false;");
				$("#NextLinker").css("color", "#ccc");
				$("#NextLinker").attr("disabled", "disabled");
				$("#NextLinker").attr("onclick", "return false;");
				$("#LastLinker").css("color", "#ccc");
				$("#LastLinker").attr("disabled", "disabled");
				$("#LastLinker").attr("onclick", "return false;");
			}
		}
	});
}



function RefreshWearingItems() {
	var datalistbody = $("#WearingAssetsTable").find("tbody");
	datalistbody.children().each(function() {
		$(this).children().each(function() {
			$(this).remove();		
		})
	});

	$.get( "/api/characterstuff?getwearing", function( data ) {
		if(Object.keys(data).length != 0) {
			$(".NoResults").css("display", "none");
			var index = 0;
			var trIndex = 0;
			for (var key in data){
				
				var asset = data[key];
				var tdwrapper = $('<td class="Asset" valign="top"></td>');
				var $template = $( $('#asset_wearing_template').html());
				$template = $(tdwrapper.append($template)); 
				var namelink = $template.find('.AssetName').find("a");
				namelink.html(asset["Name"]);
				namelink.attr("href", "/Item.aspx?ID="+asset["ID"]);

				var assType = asset["Type"];
				var type = "";

				if(assType == 2) {
					type = "T-Shirt";
				} else if(assType == 8) {
					type = "Hat";
				} else if(assType == 11) {
					type = "Shirt";
				} else if(assType == 12) {
					type = "Pants";
				}
				
				var namelink = $template.find('.AssetType').find("a");
				namelink.html(type);
				namelink.attr("href", "/Catalog.aspx?c="+asset["Type"]);

				var creatornamelink = $template.find('.AssetCreator').find("a");
				creatornamelink.html(asset["CreatorName"]);
				creatornamelink.attr("href","/User.aspx?ID="+asset["CreatorUserID"]);
				
				var imagelink = $template.find('#AssetThumbnailLink');
				imagelink.attr("href", "javascript:TakeOff("+asset["ID"]+")");
				imagelink.attr("title", asset["Name"]);

				$(imagelink.find("img")[0]).attr("alt", asset["Name"]);
				$(imagelink.find("img")[0]).attr("src", "/thumbs/?id="+asset["ID"]+"&type=120");

				$template.find(".DeleteButtonOverlay").attr("href", "javascript:TakeOff("+asset["ID"]+")")

				$(datalistbody.find("tr")[0]).append($template);
				index += 1;
				if(index % 4 == 0) {
					trIndex += 1;
				}
			}
			
			if(Object.keys(data).length < 4) {
				for(var i = 0; i < 5 - Object.keys(data).length; i++) {
					$(datalistbody.find("tr")[0]).append("<td></td>");
				}
			}
		} else {
			$(".NoResults").css("display", "block");
		}
	});
}

function TakeOff(id) {
	$.get( "/api/characterstuff?takeoff="+id, function( data ) {
		$("#Char").attr("src", "/thumbs/player?id=<?= $user->id ?>&t="+Math.random());
		LoadToSelectAssets();
		RefreshWearingItems();
	});
}

function Wear(id) {
	$.get( "/api/characterstuff?wear="+id, function( data ) {
		$("#Char").attr("src", "/thumbs/player?id=<?= $user->id ?>&t="+Math.random());
		LoadToSelectAssets();
		RefreshWearingItems();
	});
}