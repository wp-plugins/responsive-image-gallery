var images, setting;
jQuery(document).ready(function($){
	GalleryImage();
	CollageSetting();
	FancyBoxSetting();
	
	function CollageSetting(){
		setting = {
	            'fadeSpeed'       : 500,
	            'effect'          : 'default',
	            'direction'       : 'vertical',
	            'allowPartialLastRow'       : false
	        };
		
		$(".settingCollage").change(function(){
			var fadeSpeed = $("#fadeSpeed").val();
			var effect = $("#effect").val();
			var direction = $("#direction").val();
			var allow = $("#last-image").val();
			
			var object = {'fadeSpeed':fadeSpeed, 'effect':effect, 'direction':direction, 'allowPartialLastRow':allow};
			
			$("#responsive_image_collage").val(JSON.stringify(object));
		});
	}
	
	function FancyBoxSetting(){
		setting = {
				'arrows' : true, //can be false
				'closeBtn' : true, //can be false
				'autoPlay' : false,
				'playSpeed' : 3000,
	            'helpers' : {
					'title' : {'type' : 'float'}
				}
	      };
		
		$(".settingFancybox").change(function(){
			var navigation_button = $("#navigation_button").val();
			var close_button = $("#close_button").val();
			var autoplay = $("#autoplay").val();
			var slideshow_speed = $("#slideshow_speed").val();
			var title_placing = $("#title_placing").val();
			var thumbnails = parseInt($("#thumbnails").val());
			var thumbnail_dimension_x = $("#thumbnail_dimension_x").val();
			var thumbnail_dimension_y = $("#thumbnail_dimension_y").val();
			
			var object;
			
			if(thumbnails){
				object = {
					'arrows' : navigation_button, //can be false
					'closeBtn' : close_button, //can be false
					'autoPlay' : autoplay,
					'playSpeed' : slideshow_speed,
		            'helpers' : {
						'title' : {'type' : title_placing},
						'thumbs' : {'width' : thumbnail_dimension_x, 'height' : thumbnail_dimension_y}
					}
		      };
			}else{
				object = {
					'arrows' : navigation_button, //can be false
					'closeBtn' : close_button, //can be false
					'autoPlay' : autoplay,
					'playSpeed' : slideshow_speed,
		            'helpers' : {
						'title' : {'type' : title_placing}
					}
		      };
			}
			
			$("#responsive_image_fancybox").val(JSON.stringify(object));
		});
	}
	
	function GalleryImage(){
		var json_string = $("#responsive_image_gallery_images").val();
		if(json_string == ''){
			images = {};
		}else{
			images = JSON.parse(json_string);
		}
		
		function createUUID() {
		    var s = [];
		    var hexDigits = "0123456789abcdef";
		    for (var i = 0; i < 36; i++) {
		        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
		    }
		    s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
		    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
		    s[8] = s[13] = s[18] = s[23] = "-";
		
		    var uuid = s.join("");
		    return uuid;
		}
	
		$('#upload-btn').click(function(e) {
			e.preventDefault();
				var image = wp.media({
				title: 'Upload Image',
				// mutiple: true if you want to upload multiple files at once
				multiple: true
				}).open()
			.on('select', function(e){
				var temp_images = {};
				var selection = image.state().get('selection');
			    selection.map( function( attachment ) {
				    attachment = attachment.toJSON();
				    console.log(attachment);
				    var uuid = createUUID();
				    images[ parseInt(Object.keys(images).length) + 1 ] = {image:attachment.id, id:uuid};
				    temp_images[ parseInt(Object.keys(images).length) + 1 ] = {image:attachment.url, id:uuid};
			    });
			    
			    $("#responsive_image_gallery_images").val(JSON.stringify(images));
			    
			    for(var value in temp_images) {
			    	$(".images- ul").prepend('<li class="gallery-item" id="'+temp_images[value].id+'"><span class="update"></span><span class="delete"></span><img src="'+temp_images[value].image+'"></li>');
			    }
			    
			});
		});
		
		var dialog, form;
		
		form = $( "#dialog-form" );
		form.on( "submit", function( event ) {
			event.preventDefault();
		});
		
		dialog = $( "#dialog-form" ).dialog({
			autoOpen: false,
			height: 300,
			width: 350,
			modal: true,
			buttons: {
				Ok: function() {
					dialog.dialog( "close" );
				}
			},
			close : function() {
				var id = form.find("#image-id").val();
				var description = form.find("#image-description").val();
				var flag = false;
				
				for(var value in images) {
					if(images[value].id == id){
						images[value].description = description;
						flag = true;
						break;
					}
				}
				
				if(flag){
					$("#responsive_image_gallery_images").val(JSON.stringify(images));
				}
				
				form.find("#image-description").val("");
			}
		});
		
		$("body").on("click", ".gallery-item .update", function(){
			var obj = $(this).parent(".gallery-item");
			var id = obj.attr("id");
			
			form.find("#image-id").val(id);
			
			for(var value in images) {
				if(images[value].id == id){
					if(images[value].hasOwnProperty('description')){
						form.find("#image-description").val(images[value].description);
					}
					break;
				}
			}
			
			dialog.dialog( "open" );
		});
		
		$("body").on("click", ".gallery-item .delete", function(){
			var obj = $(this).parent(".gallery-item");
			var id = obj.attr("id");
			var flag = false;
			
			for(var value in images) {
				if(images[value].id == id){
					delete images[value];
					$(obj).remove();
					flag = true;
					break;
				}
			}
			
			if(flag){
				$("#responsive_image_gallery_images").val(JSON.stringify(images));
			}
	});
	}
});