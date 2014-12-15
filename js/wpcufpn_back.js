/** wpcuFPN back-end jQuery script v.0.1 **/

(function($){
	
	$( document ).ready(function() {
		
		$("#colorPicker .colorInner").unbind("mousedown");
		
		
		
		/** Theme preview drop-down **/
		$('select#theme').change( function(e){
			var theme_img = themes[$(this).val()]['theme_url']+'/screenshot.png';
			
			/*
			 * 
			 * If theme is Premium > disable Option
			 * else theme is defautl > enable Option
			 * 
			 */
			
			if ($(this).val().indexOf("masonry") > -1) {
				$("#wpcufpn_config_zone,#wpcufpn_config_animation").addClass("disabled");
				$("#wpcufpn_config_color,#colorPicker .color").removeClass("disabled");
				
				$('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_rows').attr('disabled','disabled');
				$("#amount_pages,#pagination,#amount_rows").closest(".field").addClass("disabled");
				
				$("#amount_cols").closest(".field").removeClass("disabled");
				$('#amount_cols').removeAttr('disabled');
				
				if ($("ul.arrow_col").hasClass("ui-sortable"))				
					$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );
				
			} else if ($(this).val().indexOf("smooth") > -1) { 
				$("#wpcufpn_config_zone,#wpcufpn_config_animation").addClass("disabled");
				$("#wpcufpn_config_color,#colorPicker .color").removeClass("disabled");
				$("#amount_pages,#amount_cols,#pagination,#amount_rows").closest(".field").addClass("disabled");
				
				$('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows').attr('disabled','disabled');
				if ($("ul.arrow_col").hasClass("ui-sortable"))	
					$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );
			} else if ($(this).val().indexOf("timeline") > -1) { 
				$("#wpcufpn_config_zone,#wpcufpn_config_animation").addClass("disabled");
				$("#wpcufpn_config_color,#colorPicker .color").removeClass("disabled");
				$("#amount_pages,#amount_cols,#pagination,#amount_rows").closest(".field").addClass("disabled");
				
				$('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows').attr('disabled','disabled');
				if ($("ul.arrow_col").hasClass("ui-sortable"))	
					$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );
			}
			else {
				
				$("#wpcufpn_config_zone,#wpcufpn_config_animation").removeClass("disabled");
				$("#wpcufpn_config_color,#colorPicker .color").addClass("disabled");
				$("#amount_pages,#amount_cols,#pagination,#amount_rows").closest(".field").removeClass("disabled");
				$('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows').removeAttr('disabled');
				dragandDropinnerBlock();
				$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "enable" );
			}
			
			$('.wpcufpn-theme-preview img').fadeOut(200, function(){
				$(this).attr('src',theme_img).bind('onreadystatechange load', function(){
					if (this.complete) $(this).fadeIn(400);
				});
			});
		});
		
		dragandDropinnerBlock();
		
		function dragandDropinnerBlock(){
					
			$('.wpcu-inner-admin-block:not(.disabled) ul.arrow_col,#wpcufpn_config_zone:not(.disabled) .drop_zone_col .wpcu-inner-admin-block ul').sortable({
				connectWith: 'ul',
				update: function( event, ui ) {
					//console.log( ui.item );
					$(ui.item).animate({opacity: 0.5}, 90).animate({opacity: 1}, 90);
					//console_log( 'sortable was updated: ' + $(this).parent().attr('id') );
					$('#wpcufpn_' + $(this).parent().attr('id')).val( $(this).html() );
				},
				containment: '#wpcufpn_config_zone',
				over: function(event, ui) {
					$(this).parent().addClass('dragover');
				},
				out: function(event, ui) {
					$(this).parent().removeClass('dragover');
				}
			});
			$('.wpcu-inner-admin-block:not(.disabled) ul.arrow_col,#wpcufpn_config_zone:not(.disabled) .drop_zone_col .wpcu-inner-admin-block ul').disableSelection();
		
		}
						
	
		
		
		
		/** Automatically setup default pagination **/
		$('#amount_pages').live('focus', function(){
		      $(this).attr('oldValue',$(this).val());
		});

		$('#amount_pages').live('change', function(){
		      var oldValue = $(this).attr('oldValue');
		      var currentValue = $(this).val();
		      if( oldValue == 1 && currentValue > 1 ) {
		    	  
		    	  if( $('#pagination').val() == 0 ) {		    		  
		    		  $('#pagination').eq(0).prop('selected', false);
		    		  $('#pagination option:eq(0)').prop('selected', false);
		    		  
		    		  $('#pagination option:eq(3)').prop('selected', true);
		    		  $('#pagination').eq(3).prop('selected', true);
		    		  $('#pagination').val(3);
		    		  $('#pagination').change();
		    		  //$('#pagination')[3].selected = true;
		    	  }
		      }
		      
		      if( oldValue > 1 && currentValue == 1 ) {
		    	  
		    	  if( $('#pagination').val() > 0 ) {
		    		  $('#pagination').eq(0).prop('selected', true);
		    		  $('#pagination option:eq(0)').prop('selected', true);
		    		  $('#pagination').change();
		    	  }
		      }
		});
		
	});
	
})( jQuery );