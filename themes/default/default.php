    <?php
    $titleChecked    = "";
    $textChecked     = "";
    $dateChecked     = "";
    $authorChecked   = "";
    $readMoreChecked = "";
    $imageChecked    = "";

    if (isset($settings['dfTitle']) && ( ! empty($settings['dfTitle']))) {
    $titleChecked = "checked";
    }
    if (isset($settings['dfText']) && ( ! empty($settings['dfText']))) {
    $textChecked = "checked";

    }
    if (isset($settings['dfDate']) && ( ! empty($settings['dfDate']))) {
    $dateChecked = "checked";

    }
    if (isset($settings['dfAuthor']) && ( ! empty($settings['dfAuthor']))) {
    $authorChecked = "checked";

    }
    if (isset($settings['dfReadMore']) && ( ! empty($settings['dfReadMore']))) {
    $readMoreChecked = "checked";

    }
    if (isset($settings['dfThumbnail']) && ( ! empty($settings['dfThumbnail']))) {
    $imageChecked = "checked";

    }
    /**
    *  new setting theme default
    */
    echo '<div id="wpcufpn_config_zone_new" class="wpcu-inner-admin-block with-title with-border '.$classdisabled.$classdisabledsmooth.'">';
    echo '<h4>'. __( 'A new item', 'wpcufpn' ) .'</h4>';
    echo '<div class="wpcufpn-drag-config"></div>';
    echo '<div class="arrow_col_wrapper"><ul class="arrow_col">';

    /**
    * display image field
    */
    echo '<input type="hidden" name="wpcufpn_dfThumbnail" value="">';
    echo '<input id="dfThumbnail" '.$imageChecked.' type="checkbox" name="wpcufpn_dfThumbnail" value="'.htmlspecialchars("Thumbnail").'">' . __( 'Thumbnail', 'wpcufpn' ) .'<br>';

    /**
    * display title field
    */
    echo '<input type="hidden" name="wpcufpn_dfTitle" value="">';
    echo '<input id="dfTitle" '.$titleChecked.' type="checkbox" name="wpcufpn_dfTitle" value="'.htmlspecialchars("Title").'">'. __( 'Title', 'wpcufpn' ) .'<br>';

    /**
    * display author field
    */
    echo '<input type="hidden" name="wpcufpn_dfAuthor" value="">';
    echo '<input id="dfAuthor"'.$authorChecked.' type="checkbox" name="wpcufpn_dfAuthor" value="'.htmlspecialchars("Author").'">'. __( 'Author', 'wpcufpn' ) .'<br>';

    /**
    * display date field
    */
    echo '<input type="hidden" name="wpcufpn_dfDate" value="">';
    echo '<input id="dfDate"'.$dateChecked.' type="checkbox" name="wpcufpn_dfDate" value="'.htmlspecialchars("Date").'">'. __( 'Date', 'wpcufpn' ) .'<br>';

    /**
    * display text field
    */
    echo '<input type="hidden" name="wpcufpn_dfText" value="">';
    echo '<input id="dfText"'.$textChecked.' type="checkbox" name="wpcufpn_dfText" value="'.htmlspecialchars("Text").'">'. __( 'Text', 'wpcufpn' ) .'<br>';

    /**
    * display read more field
    */
    echo '<input type="hidden" name="wpcufpn_dfReadMore" value="">';
    echo '<input id="dfReadMore"'.$readMoreChecked.' type="checkbox" name="wpcufpn_dfReadMore" value="'.htmlspecialchars("Read more").'">'. __( 'Read more', 'wpcufpn' ) .'<br>';

    echo '</ul></div>';	//arrow_col
    echo '</div>';
    echo '</div>';
