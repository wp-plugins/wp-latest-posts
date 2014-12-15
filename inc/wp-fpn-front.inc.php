<?php
/** WP Frontpage News front display class **/
class wpcuFPN_Front {
	
	const CSS_DEBUG			= false;
	const TITLE_EM_SIZE 	= 1.1;
	const TEXT_EM_SIZE		= 1.1;
	
	public		$widget;
	private	$html		= '';
	private	$posts 		= array();
	private	$prepared	= false;
	private	$boxes		= array();
	
	/**
	 * Sets up widget options
	 * 
	 * @param object $widget
	 */
	public function __construct( $widget ) {
		$this->widget 	= $widget;
		/*
		 * If Premium Theme ! reset box
		 */ 

		
		if (strpos($this->widget->settings["theme"],'masonry'))
			$this->resetsettingsPremium();
		
		if (strpos($this->widget->settings["theme"],'smooth'))
			$this->resetsettingsPremium();
		
		if (strpos($this->widget->settings["theme"],'timeline'))
			$this->resetsettingsPremium();
		
		$this->posts	= $this->queryPosts();
		//var_dump($this->posts);
		$this->prepared = true;
		
		//TODO: boxes setup will depend on theme template + pro filter
		$this->boxes = array( 'top', 'left', 'right', 'bottom' );
	}
	
	
	/**
	 * Reset Box Settings
	 * 
	 */
	private function resetsettingsPremium() {
		
		if (strpos($this->widget->settings["theme"],'masonry-category')) {
			$this->widget->settings["box_top"]=array("ImageFull","Title");
		}
		elseif (strpos($this->widget->settings["theme"],'smooth')) {
			$this->widget->settings["box_top"]=array("Category","Date");
		}
		elseif (strpos($this->widget->settings["theme"],'timeline')) {
			$this->widget->settings["box_top"]=array("ImageFull");
		}
		else {	
			$this->widget->settings["box_top"]=array("ImageFull","Title","Date","Text");
		}
		$this->widget->settings["box_left"]=null;
		$this->widget->settings["box_right"]=null;
		if (strpos($this->widget->settings["theme"],'masonry-category')) {
			$this->widget->settings["box_bottom"]=array("Category");
		}
		elseif (strpos($this->widget->settings["theme"],'smooth')) {		
			$this->widget->settings["box_bottom"]=array("Title","Text","Read more");
		}
		elseif (strpos($this->widget->settings["theme"],'timeline')) {
			$this->widget->settings["box_bottom"]=array("ImageFull","Title","Category","Text","Read more","Date");
		}
		else {
			$this->widget->settings["box_bottom"]=array("Read more");
		}
		
		$this->widget->settings['margin_top']=0;
		$this->widget->settings['margin_right']=0;
		$this->widget->settings['margin_bottom']=0;
		$this->widget->settings['margin_left']=0;
	}
	
	
	/**
	 * Selects posts to display in our widget
	 * 
	 */
	private function queryPosts() {
		
		wp_reset_postdata();
		
		/** for posts and page source_types **/
		if( 
				'src_category' == $this->widget->settings['source_type'] || 
				'src_page' == $this->widget->settings['source_type']
		) {
			
			/** source_types (post_type) **/
			$post_type = 'post';
			if( 'src_category' == $this->widget->settings['source_type'] )
				$post_type = 'post';
			if( 'src_page' == $this->widget->settings['source_type'] )
				$post_type = 'page';
			
			/** source_order (order_by) **/
			$order_by = 'date';
			if( 'src_category' == $this->widget->settings['source_type'] ) {
				if( 'date' == $this->widget->settings['cat_source_order'] )
					$order_by = 'date';
				if( 'title' == $this->widget->settings['cat_source_order'] )
					$order_by = 'title';
				if( 'order' == $this->widget->settings['cat_source_order'] )
					$order_by = 'menu_order';
			}
			if( 'src_page' == $this->widget->settings['source_type'] ) {
				if( 'date' == $this->widget->settings['pg_source_order'] )
					$order_by = 'date';
				if( 'title' == $this->widget->settings['pg_source_order'] )
					$order_by = 'title';
				if( 'order' == $this->widget->settings['pg_source_order'] )
					$order_by = 'menu_order';
			}
			
			/** source_asc (order) **/
			$order = 'DESC';
			if( 'src_category' == $this->widget->settings['source_type'] ) {
				if( 'desc' == $this->widget->settings['cat_source_asc'] )
					$order = 'DESC';
				if( 'asc' == $this->widget->settings['cat_source_asc'] )
					$order = 'ASC';
			}
			if( 'src_page' == $this->widget->settings['source_type'] ) {
				if( 'desc' == $this->widget->settings['pg_source_asc'] )
					$order = 'DESC';
				if( 'asc' == $this->widget->settings['pg_source_asc'] )
					$order = 'ASC';
			}
			
			/** max_elts (limit / posts_per_page) **/
			$limit = 10;
			if( $this->widget->settings['max_elts'] > 0 )
				$limit = $this->widget->settings['max_elts'];
			
			$args = array( 
					'post_type'			=> $post_type,
					'orderby'			=> $order_by,
					'order' 			=> $order,
					'posts_per_page' 	=> $limit
			);
			
			/** filter by category **/
			if( 
				'src_category' == $this->widget->settings['source_type'] &&
				isset( $this->widget->settings['source_category'] ) &&
				'_all' != $this->widget->settings['source_category']
			)
				$args['category__in'] 	= $this->widget->settings['source_category'];
			
			
			//$args = apply_filters( 'wpcufpn_src_category_args', $args );
			
			$posts = get_posts( $args );
		}elseif(
				'src_tags' == $this->widget->settings['source_type'] 
		){
			
			
			$post_type = 'post';
			$order_by = 'date';
			
			$limit = 10;
			if( $this->widget->settings['max_elts'] > 0 )
				$limit = $this->widget->settings['max_elts'];
		
			
			foreach( $this->widget->settings['source_tags'] as $tag ) {
				if ($tag=="_all"){
					$tags = get_tags();		
					foreach ($tags as $tag)
						$source_tag[] = $tag->term_id;
					
				}else {
					$source_tag[] = $tag;
				}
				
				
			}
			
			//var_dump($source_tag);
			
			$args = array( 
						'post_type'			=> $post_type,
						'orderby'			=> $order_by,
						'order' 			=> $order,
						'posts_per_page' 	=> $limit,
						'tax_query' => array(array(
							'taxonomy' => 'post_tag',
							'field' => 'term_id',
							'terms' => $source_tag
						))
				);
				
			$posts = get_posts( $args );
		}
		
		wp_reset_postdata();
		
		return
			$this->posts = apply_filters( 'wpcufpn_front_queryposts', $posts, $this->widget );
	}
	
	/**
	 * Front end display
	 * 
	 */
	public function display( $echo=true, $is_sidebar_widget=false ) {		
		
		if( $this->posts ) {
			$this->container( $is_sidebar_widget );
		} else {
			$this->html .= "\n<!-- wpcuFPN: No Post to show in this widget! -->\n";
		}
		
		/** call pro plugin additional source type display modes **/
		$this->html = apply_filters( 'wpcufpn_front_display', $this->html, $this->widget );
		
		if( $echo )
			echo $this->html;
		
		return $this->html;
	}
	
	/**
	 * This dynamically loads theme styles as inline html styles
	 * 
	 */
	public function loadThemeStyle() {

		$theme = $this->widget->settings['theme'];
		$theme_dir = basename( $theme );
		//$this->html.='<p>Theme: ' . $theme . '</p>';	//Debug
		
		//var_dump( $this->widget );					//Debug
		
		if( file_exists( $theme . '/style.css' ) ) {
			
			/*  @TODO : Not best way to add stylesheet on wordpress...
			$this->html .= '<style>';
			$url_prefix = plugins_url( '/themes/' . $theme_dir, dirname( __FILE__ ) ) . '/';
			if( class_exists(wpcuWPFnProPlugin) ) {
				$url_prefix = preg_replace( '/wp-latest-posts/', 'wp-frontpage-news-pro-addon', $url_prefix );
			}
			
			$styles = @ file_get_contents( $theme . '/style.css' );
			$styles = preg_replace( '/url\(/', 'url(' . $url_prefix, $styles );
			$styles = preg_replace( '/wpcufpn_widget_ID/', 'wpcufpn_widget_' . $this->widget->ID, $styles );
			$this->html .= $styles;
			$this->html .= '</style>';
			*/
			
			$color=$this->widget->settings["colorpicker"];
			$color=$this->hex2rgba($this->widget->settings["colorpicker"],0.7);
			$colorfull=$this->hex2rgba($this->widget->settings["colorpicker"],1);
			
			/*
			 * Number of column
			 */ 
			$nbcol=$this->widget->settings["amount_cols"];
			$theme_classDashicon = ' ' . basename( $this->widget->settings['theme'] );
			
			if($theme_classDashicon != " default")			
				wp_enqueue_style( 'themes-wplp'.$this->widget->ID, plugins_url("wp-latest-posts-addon/themes/").$theme_dir."/style.css.php?id=".$this->widget->ID."&color=".$color."&colorfull=".$colorfull."&nbcol=".$nbcol);
			
			
			
			if ( $theme_classDashicon == " masonry-category" || $theme_classDashicon == " timeline"){
				wp_enqueue_style( 'dashicons' );
			}
			
					
	
		}
		
		if( file_exists( $theme . '/script.js' ) ) {
			/*
			 * @TODO : Again... Not best way to add script on wordpress...
			 *  
			 
			$this->html .= '<script language="javascript" type="text/javascript">';
			$script = @ file_get_contents( $theme . '/script.js' );
			$script = preg_replace( '/wpcufpn_widget_ID/', 'wpcufpn_widget_' . $this->widget->ID, $script );
			$this->html .= $script;
			$this->html .= "wpfpn_nbrows = ( typeof wpfpn_nbrows != 'undefined' && wpfpn_nbrows instanceof Array ) ? wpfpn_nbrows : [];";
			$this->html .= "wpfpn_nbrows[" . $this->widget->ID . "]=" . ($this->widget->settings['amount_rows']?$this->widget->settings['amount_rows']:0) . ';';
			$this->html .= '</script>';
			*/ 
			wp_enqueue_script( 'themes-wplp'.$this->widget->ID, plugins_url("wp-latest-posts-addon/themes/").$theme_dir
			."/script.js.php?id="
			.$this->widget->ID
			."&nbrow="
			.($this->widget->settings['amount_rows']?$this->widget->settings['amount_rows']:0)
			."&autoanimate="
			.$this->widget->settings['autoanimation']
			."&autoanimatetrans="
			.$this->widget->settings['autoanimation_trans']);
			
			
		} else {
			/*$this->html .= '<script language="javascript" type="text/javascript">';
			$this->html .= "wpfpn_nbrows = ( typeof wpfpn_nbrows != 'undefined' && wpfpn_nbrows instanceof Array ) ? wpfpn_nbrows : [];";
			$this->html .= "wpfpn_nbrows[" . $this->widget->ID . "]=" . ($this->widget->settings['amount_rows']?$this->widget->settings['amount_rows']:0) . ';';
			$this->html .= '</script>';
			*/
			
			
			
			wp_enqueue_script( 'scriptdefault-wplp'.$this->widget->ID, plugins_url("wp-latest-posts/js/")
			."/wpcufpn_front.js.php?id="
			.$this->widget->ID
			."&pagination="
			.$this->widget->settings['pagination']
			."&autoanimate="
			.$this->widget->settings['autoanimation']
			."&autoanimatetrans="
			.$this->widget->settings['autoanimation_trans']);
			
		}
	}
	
	/**
	 * This is the main container of our widget
	 * also acts as outside framing container of a slideshow
	 * 
	 */
	private function container( $is_sidebar_widget=false ) {
		
		$style_cont = '';
		$orientation = 'vertical';
		
		/** Container width **/
		if( 
			isset( $this->widget->settings['total_width'] ) && 
			'auto' != strtolower( $this->widget->settings['total_width'] ) &&
			$this->widget->settings['total_width']
		) {
			global $wpcu_wpfn;
			$style_cont .= 'width:' . $this->widget->settings['total_width'] . $wpcu_wpfn->_width_unit_values[$this->widget->settings['total_width_unit']] .';';
		}
		
		/** Slider width **/
		if(
			isset( $this->widget->settings['amount_pages'] ) &&
			$this->widget->settings['amount_pages'] > 1
		) {
			$percent = $this->widget->settings['amount_pages'] * 100;
			$style_slide = 'width: ' . $percent . '%;';
			$orientation = 'horizontal';
			
			/** Test colonnes **/
			$style_slide .= '-webkit-column-count: 1;';
			$style_slide .= '-moz-column-count: 1;';
			$style_slide .= 'column-count: 1;';
			
		} else {
			$style_slide = 'width: 100%;';
		}
				
		if( self::CSS_DEBUG ) {
			$style_cont .= 'border:1px solid #C00;';
			$style_slide .= 'border:1px solid #0C0;';
		}
		
		$this->html .= '<div class="wpcufpn_outside wpcufpn_widget_' . $this->widget->ID . '" style="' . $style_cont . '">';
		
		/** Widget block title **/
		if(
			!$is_sidebar_widget &&
			isset( $this->widget->settings['show_title'] ) &&
			$this->widget->settings['show_title'] == 1
		) {
			$this->html .= '<span class="wpcu_block_title">' . $this->widget->post_title . '</span>';
		}
		
		$theme_class = ' ' . basename( $this->widget->settings['theme'] );
		
		$theme_classpro="";
		$masonry_class="";
		$smooth_class="";
		$slideClass='';
		$timelineClass='';
	
		if ($theme_class == " masonry" || $theme_class == " masonry-category"){
		   $theme_classpro = " pro";
		   $masonry_class = "masonrycontainer_".$this->widget->ID;
		}
		
		if ($theme_class == " smooth-effect"){
			$theme_classpro = " pro";
			$smooth_class="smoothcontainer_".$this->widget->ID;
			$style_cont="";
			$style_slide="";
			$slideClass=" slides";
		}
		
		if ($theme_class == " timeline"){
			$theme_classpro = " pro";
			$timelineClass="timeline_".$this->widget->ID;
		}
		
		$themedefaut="";
		if ($themeclass == ""){
			$style_cont="";
			$themedefaut=" defaultflexslide";			
		}
		
		
			
		$amount_cols_class = ' cols' . $this->widget->settings['amount_cols'];
		
		/** Container div **/
		$this->html .= '<div id="wpcufpn_widget_' . $this->widget->ID . '" class="wpcufpn_container ' . $orientation . $themedefaut . $theme_class . $theme_classpro . $amount_cols_class . '" style="' . $style_cont . '">';
		$this->html .= '<ul class="wpcufpn_listposts'.$slideClass.$themedefaut.'" id="'.$masonry_class.$smooth_class.$timelineClass.'" style="' . $style_slide . '" >';
		$this->loop($theme_class);
		$this->html .= '</ul>';
		$this->html .= '</div>';
		$this->html .= '</div>';
		
		
		/** Navigation / pagination **/
		/*
		if( $this->widget->settings['pagination'] )
			$this->html .= '<div class="wpcufpn_nav">';
		if( 1 == $this->widget->settings['pagination'] || 3 == $this->widget->settings['pagination'] )		// Arrow left
			 $this->html .= '<a href="#" class="slide_left slidebtn">&#171;</a>';
		if( 2 == $this->widget->settings['pagination'] || 3 == $this->widget->settings['pagination'] ) {	// Page numbers
			$first = true;
			for( $p=1; $p<=$this->widget->settings['amount_pages']; $p++ ) {
				//if( !$first ) $this->html .= '&nbsp;';
				$this->html .= '<a href="#" class="pagi_p ppn_' . $p;
				if( $first )
					$this->html .= ' active';
				$this->html .= '">' . $p . '</a>';
				$first = false;
			}
		} elseif( 4 == $this->widget->settings['pagination'] ) {											// Square bullets
			$first = true;
			for( $p=1; $p<=$this->widget->settings['amount_pages']; $p++ ) {
				//if( !$first ) $this->html .= '&nbsp;';
				$this->html .= '<a href="#" class="pagi_p squarebullet ppn_' . $p;
				if( $first )
					$this->html .= ' active';
				$this->html .= '">' . $p . '</a>';
				$first = false;
			}
		}
		//if( 5 == $this->widget->settings['pagination'] ) 													// ???
		//	$this->html .= '&nbsp;';
		if( 1 == $this->widget->settings['pagination'] || 3 == $this->widget->settings['pagination'] )	// Arrow right
			$this->html .= '<a href="#" class="slide_right slidebtn">&#187;</a>';
		if( $this->widget->settings['pagination'] )
			$this->html .= '</div>';
		$this->html .= '</div>';	// /outside
		if( $this->widget->settings['autoanimation'] && $theme_class != " smooth-effect") {
			$this->html .= '<script>var autoanimate = true;';
			if( 0 == $this->widget->settings['autoanimation_trans'] )
				$this->html .= "transition_type = 'fade';";
			$this->html .= '</script>';
		} else {
			$this->html .= '<script>var autoanimate = false;';
			$this->html .= '</script>';
		}*/
	}
	
	/**
	 * This loops through the posts to display in our widget
	 * Each post is like a frame if there is a slider
	 * although the slider may list more than one frame in a page
	 * depending on the theme template
	 * 
	 */
	private function loop($themeclass=NULL) {
		global $post;
		global $more;
		$more = 1;
		
		$style = '';
		if( isset( $this->widget->settings['amount_cols'] ) && ( $this->widget->settings['amount_cols'] > 0 ) ) {
			$percent = 100 / $this->widget->settings['amount_cols'];
			
			if(
					isset( $this->widget->settings['amount_pages'] ) &&
					$this->widget->settings['amount_pages'] > 1
			)
				$percent = $percent / $this->widget->settings['amount_pages'];
			
			if( self::CSS_DEBUG )
				$percent = $percent -1;
			$style .= 'width:' . $percent . '%;';
		}
		if( self::CSS_DEBUG )
			$style .= 'border:1px solid #00C;';
		
		/*
		if( isset( $this->widget->settings['amount_rows'] ) && ( $this->widget->settings['amount_rows'] > 0 ) ) {
			$this->html .= '';
		}
		*/
		
		/*
		 * If themeClass = masonry
		 * 
		 */ 
		
		if ($themeclass == " masonry" || $themeclass ==' masonry-category' || $themeclass ==' smooth-effect' || $themeclass == " timeline")
		$style="";	
		
		
		if ($themeclass == " default")
		$style="";		
		
		$backgroundimageLI=false;
		if ($themeclass ==' smooth-effect')
		$backgroundimageLI=true;
		
		
		//$themeclass
			
		//if( isset( $this->widget->settings['amount_rows'] ) && ( $this->widget->settings['amount_rows'] > 1 ) ) {
	
			
		if ($themeclass == " default") {	
			$i=0;
			$counter=0;
			$countercols=0;
			$correcstyle=$style;
			
			foreach ( $this->posts as $post ) {
				$i++;
				$counter++;
				$countercols++;
				
				
				setup_postdata( $post );	
				
				
				$parentClass="";
				if ($counter!=1){
					$style='width:'.(100/$this->widget->settings['amount_cols']).'%;box-sizing: border-box;-moz-box-sizing: border-box;';
					$parentClass="";
				}
					
				else {
					$style=$correcstyle;
					$parentClass="parent ";
				}
					
				
				
				$this->html .= '<li class="'.$parentClass.'" style="' . $style . '"><div class="insideframe">';
				if ($counter==1){
					$this->html .= '<ul style="' . $style . '">';
					$this->html .= '<li class="" style="width:'.(100/$this->widget->settings['amount_cols']).'%;box-sizing: border-box;-moz-box-sizing: border-box;"><div class="insideframe">';
				}
					$this->frame();
					
				if ($counter==($this->widget->settings['amount_rows']*$this->widget->settings['amount_cols']) || $i == count($this->posts)){
					$this->html .= '</div></li>';	
					$this->html .= "</ul>";
					$counter=0;
				}
					
				$this->html .= '</div></li>';
				
			}
			wp_reset_postdata();
		}
		else {
			$i=0;
			
			foreach ( $this->posts as $post ) {
			$i++;
			setup_postdata( $post );
			if ($backgroundimageLI){
				// Smooth Hover
				if ($this->widget->settings["thumb_img"] == 0){
					//echo "feature image";				
					$imgsrc=wp_get_attachment_image_src( get_post_thumbnail_id($post->ID),"full");				
				}
				else {
					$imgsrc="";
					if ($img = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(get_the_ID()), $matches)){
					$img = $matches[1][0];
					global $wpdb;
					$attachment_id = false;
					$attachment_url = $img;
				    // Get the upload directory paths
					$upload_dir_paths = wp_upload_dir();
				    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
				 		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
				 		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
				 		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
				 	}
					if ($attachment_id)
					   $imgsrc = wp_get_attachment_image_src($attachment_id,"full");
					else
					   $imgsrc[0]=$img;
					}
				        
				}
				
				
				$color=$this->widget->settings["colorpicker"];
				$color=$this->hex2rgba($this->widget->settings["colorpicker"],0.7);
				$colorfull=$this->hex2rgba($this->widget->settings["colorpicker"],1);				
				
				if (!$imgsrc[0])
					$imgsrc[0]=$this->widget->settings['default_img'];
				
			
				
				
				//$style = "background-color:$colorfull;background-image:url('" . $imgsrc[0] . "')";			
				$style = "background-image:url('" . $imgsrc[0] . "')";							
			}
			
			
				$this->html .= '<li id="wpcufpn_li_' . $this->widget->ID . '_' . $post->ID . '" class="postno_' . $i . $themeclass .'" style="' . $style . '"><div class="insideframe">';
				$this->frame();
				$this->html .= '</div></li>';
			
				
			
			
			
		}
			wp_reset_postdata();
		}
	}
	
	/**
	 * One frame displays data about just one post or article
	 * The data is organized geometrically into template boxes or blocks
	 * 
	 */
	private function frame() {
		foreach( $this->boxes as $box ) {
			//$function = 'box_' . $box;	//Maybe later to have full customization of a box
			$function = 'box_misc';
			$this->$function( $box );		//Variable function name
		}
	}
	
	/**
	 * Builds the content of a block of info for a post
	 * inside a frame.
	 * $before and $after are only output if there is actual content for that box
	 * 
	 * @param string $before
	 * @param string $after
	 */
	private function boxContent( $before, $after, $box_name ) {
		$my_html = '';
		
		//TODO: retrieve fields from theme to display inside this box?
		$fields = $this->widget->settings['box_' . $box_name];
		//if( !$fields )
		//	return;
		
		if( is_array( $fields ) ) {
			foreach( $fields as $field ) {
				if( $inner = $this->field( $field ) ) {
					$my_html .= '<span class="' .sanitize_title( $field ) . '">';
					$my_html .= $inner;
					$my_html .= '</span>';
				}
			}
		}
		//if( !$my_html )
		//	return;
		
		$this->html .= $before;
		$this->html .= $my_html;
		$this->html .= $after;
	}
	
	/**
	 * Formats a field for front-end display 
	 * 
	 * @param string $field
	 * @return string : html output
	 */
	private function field( $field ) {
		global $post;
		
		remove_filter( 'excerpt_more', 'twentyeleven_auto_excerpt_more' );
		remove_filter( 'get_the_excerpt', 'twentyeleven_custom_excerpt_more' );
		
		/** Title field **/
		if( 'Title' == $field ) {
			$before = $after = '';
			
			$title = get_the_title();
				
			if( $this->widget->settings['crop_title'] == 0 ) { 	// word cropping
				if( function_exists( 'wp_trim_words' ) )
					$title = wp_trim_words( $title, $this->widget->settings['crop_title_len']);
			}
			if( $this->widget->settings['crop_title'] == 1 ) { 	// char cropping
				$title = strip_tags($title);
				$title = mb_substr($title, 0, $this->widget->settings['crop_title_len']);
				$title = mb_substr($title, 0, mb_strripos($title, " "));
			}
			if( $this->widget->settings['crop_title'] == 2 ) {	// line limitting
				$style = 'height:' . ( $this->widget->settings['crop_title_len'] * self::TITLE_EM_SIZE ) . 'em';
				//$style = 'max-height:' . ( $this->widget->settings['crop_title_len'] * self::TITLE_EM_SIZE ) . 'em';
				
				/*
				if( 1 == $this->widget->settings['crop_title_len'] )
					$style .= ';white-space:nowrap';
				*/
				
				/** Limit lines **/
				if( 1 == $this->widget->settings['crop_title_len'] ) {
					$before = '<span style="' . $style . '" class="line_limit">';
				} else {
					$before = '<span style="' . $style . '" class="line_limit nowrap">';
				}
				$after = '</span>';
			}
			return $before . $title . $after;
		}
		
		/** Text field **/
		if( 'Text' == $field ) {
			$before = $after = '';
		
			$text = get_the_excerpt();
				
			if( $this->widget->settings['crop_text'] == 0 ) { 	// word cropping
				if( function_exists( 'wp_trim_words' ) )
					$text = wp_trim_words( $text, $this->widget->settings['crop_text_len']);
			}
			if( $this->widget->settings['crop_text'] == 1 ) { 	// char cropping
				$text = strip_tags($text);
				$text = mb_substr($text, 0, $this->widget->settings['crop_text_len']);
				$text = mb_substr($text, 0, mb_strripos($text, " "));
			}
			if( $this->widget->settings['crop_text'] == 2 ) { 	// line limitting
				$before = '<span style="max-height:' . ($this->widget->settings['crop_text_len'] * self::TEXT_EM_SIZE ) . 'em" class="line_limit">';
				$after = '</span>';
			}
				
			return $before . $text . $after;
		}
		
		if ("ImageFull" ==  $field ) {
			if ($this->widget->settings["thumb_img"] == 0){
				//echo "feature image";				
				$imgsrc=wp_get_attachment_image_src( get_post_thumbnail_id($post->ID),"full");				
			}
			else {
				if ($img = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(get_the_ID()), $matches)){
					$img = $matches[1][0];
					global $wpdb;
					$attachment_id = false;
					$attachment_url = $img;
				    // Get the upload directory paths
					$upload_dir_paths = wp_upload_dir();
				    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
				 		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
				 		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
				 		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
				 	}
					if ($attachment_id)
					   $imgsrc = wp_get_attachment_image_src($attachment_id,"full");
					else
					   $imgsrc[0]=$img;
				}
			       
			}
				
			//var_dump(get_the_post_thumbnail($post->ID));
			
			if (!$imgsrc[0])
					$imgsrc[0]=$this->widget->settings['default_img'];
			
			
			$img = '<img src="' . $imgsrc[0] . '"  alt="' . get_the_title() . '"  class="wpcufpn_default" />';			
			$before = '<span class="img_cropper '. get_post_format().'">';
			$after = '</span>';
						
			return $before . $img . $after;
		}
		
		/** First image field **/
		/** Thumbnail field **/
		if( 'First image' == $field || 'Thumbnail' == $field ) {
			$sizing = null;
			$style = '';
			if( $this->widget->settings['thumb_width'] > 0 && $this->widget->settings['thumb_height'] > 0 ) {
				$sizing = array(
						$this->widget->settings['thumb_width'],
						$this->widget->settings['thumb_height']
				);
				$style .= 'width:' . $this->widget->settings['thumb_width'] . 'px;';
				
				/** Only enforce image height if cropping is off **/
				if( isset($this->widget->settings['crop_img']) && $this->widget->settings['crop_img'] == 0 ) {
					$style .= 'height:' . $this->widget->settings['thumb_height'] . 'px;';
				} else {
					$style .= 'position: absolute;';
					$style .= 'top: 50%;';
					//$style .= 'left: 50%;';
					$style .= 'margin-top: ' . ( 0 - ( $this->widget->settings['thumb_width'] / 2 ) ) . 'px;';
					//$style .= 'margin-left: ' . $this->widget->settings['width']/2 . ';';
				}
			}
			
			/** Find image **/
			if( 'First image' == $field || (isset($this->widget->settings['thumb_img'])&&$this->widget->settings['thumb_img']==2) ) {
				/** Use first attachment of post **/
				$attachments = get_children( array('post_parent' => get_the_ID(), 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order' ) );
				if ( is_array($attachments) ) {
					$first_attachment = array_shift($attachments);
					$srca = wp_get_attachment_image_src( $first_attachment->ID );
					$src = $srca[0];
				}

			} elseif (isset($this->widget->settings['thumb_img'])&&$this->widget->settings['thumb_img']==1) {
				/** Use first image of post **/
				global $post;
				if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post->post_content, $matches))
					$src = $matches[1];
				
			} else {
				/** Use default WP thumbnail **/
				$srca = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), $sizing );
				$src = $srca[0];
				
			}
			/* DEBUG
			echo 'img_src: ' . $this->widget->settings['thumb_img'] . '<br/>';
			echo 'post_id: ' . get_the_ID() . '<br/>';
			echo 'src: ' . $src . '<br/><br/>';
			*/
			
			/** If no thumb or first image get default image **/
			if( isset($src) && $src ) {
				$img = '<img src="' . $src . '" style="' . $style . '" alt="' . get_the_title() . '" class="wpcufpn_thumb" />';
			} else {
				if( isset($this->widget->settings['default_img']) && $this->widget->settings['default_img'] ) {
					$img = '<img src="' . $this->widget->settings['default_img'] . '" style="' . $style . '" alt="' . get_the_title() . '"  class="wpcufpn_default" />';
				} else {
					$img = '<span class="img_placeholder" style="' . $style . '" class="wpcufpn_placeholder"></span>';
				}
			}
			
			/** Image cropping & margin **/
			$style = '';
			if( isset($this->widget->settings['crop_img']) && $this->widget->settings['crop_img'] == 1 ) {
								
				$style .= 'width:' . $this->widget->settings['thumb_width'] . 'px;';
				$style .= 'height:' . $this->widget->settings['thumb_height'] . 'px;';
			} else {
				//$style .= 'width:100%;';
			}
			
			if( isset($this->widget->settings['margin_top']) && $this->widget->settings['margin_top'] > 0 )
				$style .= 'margin-top:' . $this->widget->settings['margin_top'] . 'px;';
			if( isset($this->widget->settings['margin_right']) && $this->widget->settings['margin_right'] > 0 )
				$style .= 'margin-right:' . $this->widget->settings['margin_right'] . 'px;';
			if( isset($this->widget->settings['margin_bottom']) && $this->widget->settings['margin_bottom'] > 0 )
				$style .= 'margin-bottom:' . $this->widget->settings['margin_bottom'] . 'px;';
			if( isset($this->widget->settings['margin_left']) && $this->widget->settings['margin_left'] > 0 )
				$style .= 'margin-left:' . $this->widget->settings['margin_left'] . 'px;';
			
			$before = '<span class="img_cropper" style="' . $style . '">';
			$after = '</span>';
						
			return $before . $img . $after;
		}
		
		/** Read more field **/
		if( 'Read more' == $field ) {
			if( isset($this->widget->settings['read_more']) && $this->widget->settings['read_more'] ) {
				return __($this->widget->settings['read_more']);
			} else {
				return __('Read more...', 'wpcufpn');
			}
		}
		
		if( 'Category' == $field ) {
			$cats= get_the_category();
			$listcat="";
			
			for ($i=0; $i < count($cats); $i++) {
				if($i>0) 
				$listcat.= " / ";
				$listcat.=$cats[$i]->cat_name;
			}
			return $listcat;
		}
		
		/** Author field **/
		if( 'Author' == $field ) {
			return get_the_author();
		}

		/** Date field **/
		if( 'Date' == $field ) {
			if( isset($this->widget->settings['date_fmt']) && $this->widget->settings['date_fmt'] ) {
				return get_the_date($this->widget->settings['date_fmt']);
			} else {
				return get_the_date();
			}
		}
		
		return "\n<!-- wpcuFPN Unknown field: " .strip_tags( $field ) . " -->\n";
	}
	
	/**
	 * Default template for standard boxes
	 *
	 */
	private function box_misc( $box_name ) {
		global $post;
		
		
		$style = '';
		if( self::CSS_DEBUG )
			$style = 'style="border:1px solid #999"';
	
		$before = '';
		if( 'left' == $box_name )
			$before .= '<table><tr>';
		
		if( 'left' == $box_name || 'right' == $box_name ) {
			$before .= '<td ';
		} else {
			$before .= '<div ';
		}
		$before .= 'id="wpcufpn_box_' . $box_name . '_' . $this->widget->ID . '_' . $post->ID . '" class="wpcu-front-box ' . $box_name . '" ' . $style . '>';
		$before .= '<a href="' . get_permalink() . '">';
		
		$after = '';
		$after .= '</a>';
		if( 'left' == $box_name || 'right' == $box_name ) {
			$after .= '</td>';
		} else {
			$after .= '</div>';
		}
		if( 'right' == $box_name )
			$after .= '</tr></table>';
		
		$this->boxContent( $before, $after, $box_name );
		
	}
	
	
	private function hex2rgba($color, $opacity = false) {

		$default = 'rgb(0,0,0)';
	
		//Return default if no color provided
		if(empty($color))
	          return $default; 
	
		//Sanitize $color if "#" is provided 
	        if ($color[0] == '#' ) {
	        	$color = substr( $color, 1 );
	        }
	
	        //Check if color has 6 or 3 characters and get values
	        if (strlen($color) == 6) {
	                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	        } elseif ( strlen( $color ) == 3 ) {
	                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	        } else {
	                return $default;
	        }
	
	        //Convert hexadec to rgb
	        $rgb =  array_map('hexdec', $hex);
	
	        //Check if opacity is set(rgba or rgb)
	        if($opacity){
	        	if(abs($opacity) > 1)
	        		$opacity = 1.0;
	        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
	        } else {
	        	$output = 'rgb('.implode(",",$rgb).')';
	        }
	
	        //Return rgb(a) color string
	        return $output;
	}
		
	/*Unused...
	/**
	 * Default template for the top box
	 * 
	 *
	private function box_top() {
		//
	}
	
	/**
	 * Default template for the left box
	 *
	 *
	private function box_left() {
		//
	}
	
	/**
	 * Default template for the right box
	 *
	 *
	private function box_right() {
		//
	}
	
	/**
	 * Default template for the bottom box
	 *
	 *
	private function box_bottom() {
		//
	}
	*/
}
?>