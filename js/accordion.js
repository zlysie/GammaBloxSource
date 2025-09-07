﻿$(function () {
    $("#accordion").accordion({ autoHeight: false, collapsible: true, animated: "myslide" });
	 $.extend($.ui.accordion.animations, {
        myslide: function (options) {
            $.ui.accordion.animations.slide(options, { duration: 400 });
        }
    });

	/*$(function() {
		$("#accordion").accordion();
	});*/
});