DEBUG = true;
$(document).ready(function() {
		$("div.toggle_menu a.toggle_menu").click(function(){
			div_toggle = $(this).parent().parent().next("div.tohide");
			if (div_toggle.css('display') === 'none') {
				div_toggle.slideDown('slow');
				$(this).find("img").each(function(i) {
					//alert(newimage.substr(0,newimage.length-8) + 'minus.gif');
					$(this).attr('src', IMAGE_PATH + 'minus.gif');
				});				
			} else {
				div_toggle.slideUp('slow');
				$(this).find("img").each(function(i) {
					$(this).attr('src', IMAGE_PATH + 'plus.gif');
				});
			}
		});
		
		$("div.toggle_hide2show a.toggle_menu").toggle(function(){
			$(this).find("img").each(function(i) {
				newimage = $(this).attr('src');
				$(this).attr('src', newimage.substr(0,newimage.length-4) + '_on.png');
			});
			div_toggle = $(this).parent().parent().find("div.tohide");
			//alert(div_toggle.html());
			div_toggle.animate({ height: 'show', opacity: 'show' }, 'slow');
			
		},function(){
			$(this).find("img").each(function(i) {
				newimage = $(this).attr('src');
				$(this).attr('src', newimage.substr(0,newimage.length-7) + '.png');
			});
			div_toggle = $(this).parent().parent().find("div.tohide");
			div_toggle.animate({ height: 'hide', opacity: 'hide' }, 'slow');
		});
		
		$("div.toggle_show2hide a.toggle_menu").toggle(function(){
			$(this).find("img").each(function(i) {
				newimage = $(this).attr('src');
				$(this).attr('src', newimage.substr(0,newimage.length-7) + '.png');
			});
			div_toggle = $(this).parent().find("div.tohide");
			div_toggle.animate({ height: 'hide', opacity: 'hide' }, 'slow');
			
		},function(){			
			$(this).find("img").each(function(i) {
				newimage = $(this).attr('src');
				$(this).attr('src', newimage.substr(0,newimage.length-4) + '_on.png');
			});
			div_toggle = $(this).parent().find("div.tohide");
			div_toggle.animate({ height: 'show', opacity: 'show' }, 'slow');
		});
		
		
		$("div.toggle_show2hide a.hide_help").click(function(){
			div_toggle = $(this).parent().parent().parent().find("div.tohide");
			div_toggle.animate({ height: 'hide', opacity: 'hide' }, 'slow');
			
		});
	}
);
