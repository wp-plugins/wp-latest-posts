<?php
echo '<div id="wpcufpn_config_zone" class="wpcu-inner-admin-block with-title with-border disabled">';
    echo '<h4>A news item</h4>';
    echo '<div class="wpcufpn-drag-config"></div>';
    echo '<div class="arrow_col_wrapper">
            <ul class="arrow_col">';
                echo '<li>Title</li>';
                echo '<li>Text</li>';
                //echo '<li>First image</li>';	<- Unused
                echo '<li>Thumbnail</li>';
                echo '<li>Read more</li>';
                echo '<li>Author</li>';
                echo '<li>Date</li>';
            echo '</ul>
    </div>';	//arrow_col

    echo '<div class="drop_zone_col">';

        echo '<div id="box_top" class="wpcu-inner-admin-block with-title with-border top">';
            echo '<h5>Top</h5><ul class="sortable">';
                if( isset($settings['box_top']) && !empty($settings['box_top']) && $settings['box_top'] )
                echo $box_top = '<li>' . join( '</li><li>', $settings['box_top'] ) . '</li>';
                echo '</ul>';
        echo '</div>';

        echo '<div id="box_left" class="wpcu-inner-admin-block with-title with-border left">';
            echo '<h5>Left</h5><ul class="sortable">';
                if( isset($settings['box_left']) && !empty($settings['box_left']) && $settings['box_left'] )
                echo $box_left = '<li>' . join( '</li><li>', $settings['box_left'] ) . '</li>';
                echo '</ul>';
        echo '</div>';

        echo '<div id="box_right" class="wpcu-inner-admin-block with-title with-border right">';
            echo '<h5>Right</h5><ul class="sortable">';
                if( isset($settings['box_right']) && !empty($settings['box_right']) && $settings['box_right'] )
                echo $box_right = '<li>' . join( '</li><li>', $settings['box_right'] ) . '</li>';
                echo '</ul>';
        echo '</div>';

        echo '<div id="box_bottom" class="wpcu-inner-admin-block with-title with-border bottom">';
            echo '<h5>Bottom</h5><ul class="sortable">';
                if( isset($settings['box_bottom']) && !empty($settings['box_bottom']) && $settings['box_bottom'] )
                echo $box_bottom = '<li>' . join( '</li><li>', $settings['box_bottom'] ) . '</li>';
                echo '</ul>';
        echo '</div>';

        //echo '<div id="trash_cont"><ul id="trashbin" class="sortable"></ul></div>';

    echo '</div>';	//drop_zone_col
    echo '</div>';
    echo '</div>';
?>



