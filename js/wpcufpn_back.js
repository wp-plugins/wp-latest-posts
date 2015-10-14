/** wpcuFPN back-end jQuery script v.0.1 **/

(function($){

    $( document ).ready(function() {

        $("#colorPicker .colorInner").unbind("mousedown");

        ThemeChange($('select#theme'),true);

        function ThemeChange(object,first){

            var theme_img = themes[object.val()]['theme_url']+'/screenshot.png';

            console.log(theme_img);
            /*
             *
             * If theme is Premium > disable Option
             * else theme is defautl > enable Option
             *
             */
            if (object.val().indexOf("masonry") > -1) {

                $("option#oldDefaultThemeOption").attr('disabled','disabled');
                $("#dfAuthor, #dfDate, #dfReadMore, #dfText, #dfThumbnail, #dfTitle").attr('disabled','disabled');
                $("#wpcufpn_config_zone,#wpcufpn_config_zone_new, #wpcufpn_config_animation").addClass("disabled");
                $("#wpcufpn_config_color,#colorPicker .color,#wpcufpn_config_cropText").removeClass("disabled");


                $('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_rows,#defaultColorTheme').attr('disabled','disabled');
                $("#amount_pages,#pagination,#amount_rows,#defaultColorTheme").closest(".field").addClass("disabled");

                $("#amount_cols, #crop_text1, #crop_text_len").closest(".field").removeClass("disabled");
                $('#amount_cols, #crop_text1, #crop_text2, #crop_text3, #crop_text_len').removeAttr('disabled');

                //if ($("ul.arrow_col").hasClass("ui-sortable"))
                //$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );

            }
            else if (object.val().indexOf("smooth") > -1) {

                $("option#oldDefaultThemeOption").attr('disabled','disabled');
                $("#dfAuthor, #dfDate, #dfReadMore, #dfText, #dfThumbnail, #dfTitle").attr('disabled','disabled');
                $("#wpcufpn_config_animation, #wpcufpn_config_cropText").removeClass("disabled");
                $("#wpcufpn_config_zone, #wpcufpn_config_zone_new").addClass("disabled");

                $("#wpcufpn_config_color,#colorPicker .color").removeClass("disabled");
                $("#amount_pages,#amount_cols,#amount_rows,#autoanimation_trans,#autoanimation_slidedir,#defaultColorTheme").closest(".field").addClass("disabled");

                $('#amount_pages,#amount_cols,#amount_rows,#autoanimation_trans,#autoanimation_slidedir,#defaultColorTheme').attr('disabled','disabled');

                $("#pagination, #crop_text1, #crop_text_len ").closest(".field").removeClass("disabled");
                $("#pagination").removeAttr('disabled');

                $('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows, #crop_text1, #crop_text2, #crop_text3, #crop_text_len').removeAttr("disabled");
                $("#autoanimation_trans,#autoanimation_slidedir").attr("disabled", "disabled");

                //if ($("ul.arrow_col").hasClass("ui-sortable"))
                //$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );

            }
            else if (object.val().indexOf("timeline") > -1) {

                $("option#oldDefaultThemeOption").attr('disabled','disabled');
                $("#dfAuthor, #dfDate, #dfReadMore, #dfText, #dfThumbnail, #dfTitle").attr('disabled','disabled');
                $("#wpcufpn_config_zone,#wpcufpn_config_zone_new,#wpcufpn_config_animation").addClass("disabled");
                $("#wpcufpn_config_color,#colorPicker .color, #wpcufpn_config_cropText").removeClass("disabled");
                $("#amount_pages,#amount_cols,#pagination,#amount_rows,#autoanimation_trans,#autoanimation_slidedir,#defaultColorTheme").closest(".field").addClass("disabled");

                $("#crop_text1, #crop_text2, #crop_text3, #crop_text_len").removeAttr("disabled");
                $("#crop_text1, #crop_text_len").closest(".field").removeClass("disabled");

                $('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows,#defaultColorTheme').attr('disabled','disabled');
                //if ($("ul.arrow_col").hasClass("ui-sortable"))
                //$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );

            }
            <!-- theme portfolio -->
            else if(object.val().indexOf("portfolio") > -1) {

                $("option#oldDefaultThemeOption").attr('disabled','disabled');
                $("#dfAuthor, #dfDate, #dfReadMore, #dfText, #dfThumbnail, #dfTitle").attr('disabled','disabled');

                $("#wpcufpn_config_cropText").addClass("disabled");

                $("#wpcufpn_config_zone,#wpcufpn_config_zone_new,#wpcufpn_config_animation").addClass("disabled");
                $("#wpcufpn_config_color,#colorPicker .color").removeClass("disabled");

                $('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_rows, #crop_text1, #crop_text2, #crop_text3, #crop_text_len').attr('disabled', 'disabled');
                $("#amount_pages,#pagination,#amount_rows, #crop_text_len, #crop_text1").closest(".field").addClass("disabled");

                $("#defaultColorTheme").closest(".field").removeClass("disabled");
                $("#defaultColorTheme").removeAttr('disabled');

                $("#amount_cols").closest(".field").removeClass("disabled");
                $('#amount_cols').removeAttr('disabled');

                //if ($("ul.arrow_col").hasClass("ui-sortable"))
                //$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable("disable");

            }
            else if(object.val().indexOf("oldDefault") > -1) {
                $("option#oldDefaultThemeOption").removeAttr('disabled','disabled');
                $("#dfAuthor, #dfDate, #dfReadMore, #dfText, #dfThumbnail, #dfTitle").attr('disabled','disabled');
                $("#wpcufpn_config_animation, #wpcufpn_config_cropText").removeClass("disabled");
                $("#wpcufpn_config_zone_new, #wpcufpn_config_color,#colorPicker .color").addClass("disabled");
                $("#amount_pages,#amount_cols,#pagination,#amount_rows,#autoanimation_trans,#autoanimation_slidedir").closest(".field").removeClass("disabled");
                $('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows,#autoanimation_trans,#autoanimation_slidedir').removeAttr('disabled');

            } else {
                if ($("div#wpcufpn_config_zone").length)
                {
                    $("div#wpcufpn_config_zone").replaceWith(
                        '<div id="wpcufpn_config_zone_new" class="wpcu-inner-admin-block with-title with-border ">' +
                        '<h4>A new item</h4>' +
                        '<div class="wpcufpn-drag-config"></div>' +
                        '<div class="arrow_col_wrapper"><ul class="arrow_col">' +

                        '<input type="hidden" name="wpcufpn_dfThumbnail" value="">' +
                        '<input checked id="dfThumbnail" type="checkbox" name="wpcufpn_dfThumbnail" value="Thumbnail">Thumbnail<br>' +

                        '<input type="hidden" name="wpcufpn_dfTitle" value="">' +
                        '<input checked id="dfTitle" type="checkbox" name="wpcufpn_dfTitle" value="Title">Title<br>' +


                        '<input type="hidden" name="wpcufpn_dfAuthor" value="">' +
                        '<input id="dfAuthor"  type="checkbox" name="wpcufpn_dfAuthor" value="Author">Author<br>' +

                        '<input type="hidden" name="wpcufpn_dfDate" value="">' +
                        '<input checked id="dfDate" type="checkbox" name="wpcufpn_dfDate" value="Date">Date<br>' +

                        '<input type="hidden" name="wpcufpn_dfText" value="">' +
                        '<input checked id="dfText"  type="checkbox" name="wpcufpn_dfText" value="Text">Text<br>' +


                        '<input type="hidden" name="wpcufpn_dfReadMore" value="">' +
                        '<input id="dfReadMore" type="checkbox" name="wpcufpn_dfReadMore" value="Read more">Read more<br>' +

                        '</ul></div>' +
                        '</div>' +
                        '</div>')
                };

                $("option#oldDefaultThemeOption").attr('disabled','disabled');
                $("#dfAuthor, #dfDate, #dfReadMore, #dfText, #dfThumbnail, #dfTitle").removeAttr('disabled');
                $("#wpcufpn_config_zone_new,#wpcufpn_config_animation, #wpcufpn_config_cropText").removeClass("disabled");
                $("#wpcufpn_config_color,#colorPicker .color").addClass("disabled");
                $("#amount_pages,#amount_cols,#pagination,#amount_rows,#autoanimation_trans,#autoanimation_slidedir").closest(".field").removeClass("disabled");
                $('#wpcufpn_config_animation input,#wpcufpn_config_animation select,#amount_pages,#pagination,#amount_cols,#amount_rows,#autoanimation_trans,#autoanimation_slidedir').removeAttr('disabled');
                //dragandDropinnerBlock();

                // if ($("ul.arrow_col").hasClass("ui-sortable"))
                //$('ul.arrow_col, .drop_zone_col .wpcu-inner-admin-block ul').sortable( "disable" );
            }
            if (!first){
                $('.wpcufpn-theme-preview img').fadeOut(200, function(){
                    $(this).attr('src',theme_img).bind('onreadystatechange load', function(){
                        if (this.complete) $(this).fadeIn(400);
                    });
                });
            }

        }



        /** Theme preview drop-down **/
        $('select#theme').change( function(e){
            ThemeChange($(this));
        });










        //dragandDropinnerBlock();
        //
        //function dragandDropinnerBlock(){
        //
        //	$('.wpcu-inner-admin-block:not(.disabled) ul.arrow_col,#wpcufpn_config_zone:not(.disabled) .drop_zone_col .wpcu-inner-admin-block ul').sortable({
        //		connectWith: 'ul',
        //		update: function( event, ui ) {
        //			//console.log( ui.item );
        //			$(ui.item).animate({opacity: 0.5}, 90).animate({opacity: 1}, 90);
        //			//console_log( 'sortable was updated: ' + $(this).parent().attr('id') );
        //			$('#wpcufpn_' + $(this).parent().attr('id')).val( $(this).html() );
        //		},
        //		containment: '#wpcufpn_config_zone',
        //		over: function(event, ui) {
        //			$(this).parent().addClass('dragover');
        //		},
        //		out: function(event, ui) {
        //			$(this).parent().removeClass('dragover');
        //		}
        //	});
        //	$('.wpcu-inner-admin-block:not(.disabled) ul.arrow_col,#wpcufpn_config_zone:not(.disabled) .drop_zone_col .wpcu-inner-admin-block ul').disableSelection();

        //}





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