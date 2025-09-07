// Hello unfortunate snooper
// This is shit code sorry

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

function LoadAssetMenu(userid, menuName, page) {
	if(page === undefined) {
		page = 1;
	}
	var datalistbody = $("#ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList").find("tbody");
	
	$("#AssetsMenu").children().each(function() {
		var button = ($($(this).find("a")[0]));
		var id = button.attr("cat");
		
		//window.alert(name);
		
		if(id == menuName) {
			button.removeClass('AssetsMenuButton').addClass('AssetsMenuButton_Selected');
			$(this).removeClass('AssetsMenuItem').addClass('AssetsMenuItem_Selected');
		} else {
			button.removeClass('AssetsMenuButton_Selected').addClass('AssetsMenuButton');
			$(this).removeClass('AssetsMenuItem_Selected').addClass('AssetsMenuItem');
		}
	});

	datalistbody.children().each(function() {
		$(this).children().each(function() {
			$(this).remove();		
		})
	});

	$.get( "/api/stuff?type="+menuName+"&usr_id="+userid+"&page="+page, function( data ) {
		$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").css("color", "");
		if(menuName == 2) {
			$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").attr("href", "/My/ContentBuilder.aspx?ContentType=2");
		} else if(menuName == 11) {
			$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").attr("href", "/My/ContentBuilder.aspx?ContentType=11");
		} else if(menuName == 12) {
			$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").attr("href", "/My/ContentBuilder.aspx?ContentType=12");
		} else if(menuName == 13) {
			$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").attr("href", "/My/ContentBuilder.aspx?ContentType=13");
		} else {
			$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").attr("href", "#");
			$("#ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink").css("color", "gray");
		}

		$("#ctl00_cphRoblox_rbxUserAssetsPane_CatalogHyperLink").attr("href", "/Catalog.aspx?c="+menuName);
		
		if(Object.keys(data).length != 0) {
			var index = 0;
			var trIndex = -1;
			for (var key in data){
				if(index % 5 == 0) {
					trIndex += 1;
				}
				if(key == "page" || key == "totalpages") {
					continue;
				}
				var asset = data[key];
				var tdwrapper = $('<td class="Asset" valign="top"></td>');
				var $template = $( $('#template_clone_asset').html());
				$template = $(tdwrapper.append($template)); 
				var namelink = $template.find('#ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList_ctl00_AssetNameHyperLink');
				namelink.html(asset["Name"]);
				namelink.attr("href", "/Item.aspx?ID="+asset["ID"]);
				var creatornamelink = $template.find('#ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList_ctl00_AssetCreatorHyperLink');
				creatornamelink.html(asset["CreatorName"]);
				creatornamelink.attr("href","/User.aspx?ID="+asset["CreatorUserID"]);
				
				var imagelink = $template.find('#ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList_ctl00_AssetThumbnailHyperLink');
				imagelink.attr("href", "/Item.aspx?id="+asset["ID"]);
				imagelink.attr("title", asset["Name"]);
				$(imagelink.find("img")[0]).attr("alt", asset["Name"]);
				$(imagelink.find("img")[0]).attr("src", "/thumbs/?id="+asset["ID"]+"&type=120");

				if(asset['Cost'] != 0) {
					$template.find(".AssetPrice").html('<span class="PriceInTickets">Tux: '+ asset["Cost"]+'</span>');
				} else {
					$template.find(".AssetPrice").html("");
				}

				$(datalistbody.find("tr")[trIndex]).append($template);
				index += 1;
			}
			
			if(Object.keys(data).length < 5) {
				for(var i = 0; i < 5 - Object.keys(data).length; i++) {
					$(datalistbody.find("tr")[0]).append("<td></td>");
				}
			}
			var footerpager = $("#ctl00_cphRoblox_rbxUserAssetsPane_FooterPagerPanel");
			if(data.totalpages > 1) {
				var nextpage = data.page + 1;
				var backpage = data.page - 1;
				if(backpage <= 0) {
					$(footerpager.find("#FooterPageSelector_Back")).css("display", "none");
				} else {
					$(footerpager.find("#FooterPageSelector_Back")).attr("href", 'javascript:LoadAssetMenu('+userid+','+menuName+','+backpage+')');
					$(footerpager.find("#FooterPageSelector_Back")).css("display", "inline");
				}
				
				$(footerpager.find("#FooterPagerLabel")).html('Page '+data.page+' of '+data.totalpages);
				if(nextpage > data.totalpages) {
					$(footerpager.find("#FooterPageSelector_Next")).css("display", "none");
				} else {
					$(footerpager.find("#FooterPageSelector_Next")).attr("href", 'javascript:LoadAssetMenu('+userid+','+menuName+','+nextpage+')');
					$(footerpager.find("#FooterPageSelector_Next")).css("display", "inline");
				}
				footerpager.css("display", "block");
			} else {
				footerpager.css("display", "none");
			}
		} else {
			$("#ctl00_cphRoblox_rbxUserAssetsPane_FooterPagerPanel").css("display", "none");
		}
	});
}

function unfavouriteAsset(asset_id) {
    $.post( "/api/favourites?removeitem", { id: asset_id }).done(function() {
        $("#asset_"+asset_id).remove();
    });
}

function LoadFavourites(userid, type, page) {
	if(page === undefined) {
		page = 1;
	}
	if(type === undefined) {
		type = $("#ctl00_cphRoblox_rbxFavoritesPane_AssetCategoryDropDownList").val();
	}
	var datalistbody = $("#ctl00_cphRoblox_rbxFavoritesPane_FavoritesDataList").find("tbody");
	
	datalistbody.find("tr").each(function() {
		$(this).children().each(function() {
			$(this).remove();		
		})
	});
	datalistbody.attr("style", "width:412px;display:inline-block");

    var ulog = false;

	$.get( "/api/user?getloggedid", function( data ) {
        if(userid == data) {
			ulog = true;
		}
    });

	$.get( "/api/favourites?getfavlist&id="+userid+"&type="+type+"&page="+page, function( data ) {
		datalistbody.css("display", "block");
        var noresult_div = $("#ctl00_cphRoblox_rbxFavoritesPane_NoResultsPanel");
        noresult_div.css("display", "none");
		if(Object.keys(data).length != 0) {
			var index = 0;
			var trIndex = -1;
			for (var key in data){
				if(index % 3 == 0) {
					trIndex += 1;
				}
				if(key == "page" || key == "totalpages") {
					continue;
				}
				var asset = data[key];
				var tdwrapper = $('<td class="Asset" valign="top"></td>');
				var $template = $( $('#template_clone_asset_fav').html());
				$template = $(tdwrapper.append($template)); 
                $template.attr("id", "asset_"+asset["ID"]);
				var namelink = $($template.find('.AssetName').find("a")[0]);
				namelink.html(asset["Name"]);
				namelink.attr("href", "/Item.aspx?id="+asset["ID"]);
				var creatornamelink = $($template.find('.Detail').find("a")[0]);
				creatornamelink.html(asset["CreatorName"]);
				creatornamelink.attr("href","/User.aspx?ID="+asset["CreatorUserID"]);
				
                var deletebutton = $('<a href="javascript:unfavouriteAsset(' + asset["ID"] + ', '+userid+')" class="DeleteButtonOverlay">[ delete ]</a>');
                if(ulog) {
                    $($template.find('.AssetThumbnail')).append(deletebutton);
                }

				var imagelink = $template.find('#AssetThumbnailLink');
				imagelink.attr("href", "/Item.aspx?id="+asset["ID"]);
				imagelink.attr("title", asset["Name"]);
                $(imagelink.find("img")[0]).attr("alt", asset["Name"]);
				$(imagelink.find("img")[0]).attr("src", "/thumbs/?id="+asset["ID"]+"&type=120");
				$(datalistbody.find("tr")[trIndex]).append($template);
				index += 1;
			}
			if(Object.keys(data).length < 3) {
				for(var i = 0; i < 3 - Object.keys(data).length; i++) {
					$(datalistbody.find("tr")[0]).append("<td></td>");
				}
			}
			
			var headerpager = $("#ctl00_cphRoblox_rbxFavoritesPane_HeaderPagerPanel");
			var footerpager = $("#ctl00_cphRoblox_rbxFavoritesPane_FooterPagerPanel");
			
			if(data.totalpages != 1) {
				var nextpage = data.page + 1;
				if(data.totalpages == data.page) {
					nextpage = 1;
				}
				headerpager.html('<span id="HeaderPagerLabel">Page '+data.page+' of '+data.totalpages+'</span><a id="HeaderPageSelector_Next" href="javascript:LoadFavourites(\''+type+'\','+nextpage+')"> Next <span class="NavigationIndicators">&gt;&gt;</span></a>');
				footerpager.html('<span id="FooterPagerLabel">Page '+data.page+' of '+data.totalpages+'</span><a id="FooterPageSelector_Next" href="javascript:LoadFavourites(\''+type+'\','+nextpage+')"> Next <span class="NavigationIndicators">&gt;&gt;</span></a>');
				
			} else {
				headerpager.html("");
				footerpager.html("");
			}
			
		} else {

            var types = {
                2: "t-shirts",
                11: "shirts",
                12: "pants",
                8: "hats",
                13: "decals",
                10: "models",
                9: "places"
            };

			datalistbody.css("display", "none");
            
            var headerpager = $("#ctl00_cphRoblox_rbxFavoritesPane_HeaderPagerPanel");
			var footerpager = $("#ctl00_cphRoblox_rbxFavoritesPane_FooterPagerPanel");
            headerpager.css("display", "none");
            footerpager.css("display", "none");

			var noresult_div = $("#ctl00_cphRoblox_rbxFavoritesPane_NoResultsPanel");
            var noresult_label = $("#ctl00_cphRoblox_rbxFavoritesPane_NoResultsLabel");


            noresult_div.css("display", "block");
            noresult_label.html(noresult_div.attr("username") + " has not chosen any favorite " + types[type]+".")
		}
	});
}
