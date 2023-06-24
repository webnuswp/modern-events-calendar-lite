<?php
/** no direct access **/
defined('MECEXEC') or die();

// Get MEC Style Options
$styling = $this->main->get_styling();

// colorskin
$color = '';

function mec_dyn_hex2rgb($cc)
{
	if($cc[0] == '#') $cc = substr($cc, 1);

	if(strlen($cc) == 6) list($r, $g, $b) = array($cc[0] . $cc[1], $cc[2] . $cc[3], $cc[4] . $cc[5]);
	elseif(strlen($cc) == 3) list($r, $g, $b) = array($cc[0] . $cc[0], $cc[1] . $cc[1], $cc[2] . $cc[2]);
	else return false;

	$r = ((!function_exists('ctype_xdigit') or (function_exists('ctype_xdigit') and ctype_xdigit($r))) ? hexdec($r) : NULL);
	$g = ((!function_exists('ctype_xdigit') or (function_exists('ctype_xdigit') and ctype_xdigit($g))) ? hexdec($g) : NULL);
	$b = ((!function_exists('ctype_xdigit') or (function_exists('ctype_xdigit') and ctype_xdigit($b))) ? hexdec($b) : NULL);

	if(is_null($r) or is_null($g) or is_null($b)) return false;
	else return array('red' => $r, 'green' => $g, 'blue' => $b);
}

if(isset($styling['color']) && $styling['color']) $color = $styling['color'];
elseif(isset($styling['mec_colorskin'])) $color = $styling['mec_colorskin'];

$rgb_color = '64,217,241';
if(!empty($color)) $rgb_color = mec_dyn_hex2rgb($color);

// Typography
$mec_h_fontfamily_arr = $mec_p_fontfamily_arr = $fonts_url = $mec_container_normal_width = $mec_container_large_width = '';

if(isset($styling['mec_h_fontfamily']) && $styling['mec_h_fontfamily'])
{
	$mec_h_fontfamily_arr = $styling['mec_h_fontfamily'];
	$mec_h_fontfamily_arr = str_replace("[", "", $mec_h_fontfamily_arr);
	$mec_h_fontfamily_arr = str_replace("]", "", $mec_h_fontfamily_arr);
	$mec_h_fontfamily_arr = explode(",", $mec_h_fontfamily_arr);
}

if(isset($styling['mec_p_fontfamily']) && $styling['mec_p_fontfamily'])
{
	$mec_p_fontfamily_arr = $styling['mec_p_fontfamily'];
	$mec_p_fontfamily_arr = str_replace("[", "", $mec_p_fontfamily_arr);
	$mec_p_fontfamily_arr = str_replace("]", "", $mec_p_fontfamily_arr);
	$mec_p_fontfamily_arr = explode(",", $mec_p_fontfamily_arr);
}

if((is_array($mec_h_fontfamily_arr) && $mec_h_fontfamily_arr) || (is_array($mec_p_fontfamily_arr) && $mec_p_fontfamily_arr))
{
	//Google font
	$font_families  = array();
	$subsets    	= 'latin,latin-ext';
	$variant_h		= '';
	$variant_p		= '';
	$mec_h_fontfamily_array = '';
	if ( is_array($mec_h_fontfamily_arr) && $mec_h_fontfamily_arr ) :
		foreach($mec_h_fontfamily_arr as $key=>$mec_h_fontfamily_array) {
			if($key != '0') $variant_h .= $mec_h_fontfamily_array .', ';
		}
    endif;

	if ( is_array($mec_p_fontfamily_arr) && $mec_p_fontfamily_arr ) :
		foreach($mec_p_fontfamily_arr as $key=>$mec_p_fontfamily_array) {
			if($key != '0') $variant_p .= $mec_h_fontfamily_array .', ';
		}
	endif;

	$font_families[] = !empty($mec_h_fontfamily_arr[0]) ? $mec_h_fontfamily_arr[0] . ':' . $variant_h : '';
	$font_families[] = !empty($mec_p_fontfamily_arr[0]) ? $mec_p_fontfamily_arr[0] . ':' . $variant_p : '';

	if($font_families)
    {
		$fonts_url = add_query_arg(array(
            'family'=>urlencode(implode('|', $font_families)),
            'subset'=>urlencode($subsets),
		), 'https://fonts.googleapis.com/css');
    }
}

if(isset($styling['container_normal_width']) && $styling['container_normal_width'])
{
	$mec_container_normal_width = trim( $styling['container_normal_width'] );
	if($mec_container_normal_width ) {
		if (is_numeric($mec_container_normal_width)) {
			$mec_container_normal_width .= 'px';
		}
	}
}

if(isset($styling['container_large_width']) && $styling['container_large_width'])
{
	$mec_container_large_width = trim( $styling['container_large_width'] );
	if($mec_container_large_width ) {
		if (is_numeric($mec_container_large_width)) {
			$mec_container_large_width .= 'px';
		}
	}
}
$title_color = $title_color_hover = $content_color = '';
if(isset($styling['title_color']) && $styling['title_color'])
{
	$title_color = $styling['title_color'];
}

if(isset($styling['title_color_hover']) && $styling['title_color_hover'])
{
	$title_color_hover = $styling['title_color_hover'];
}

if(isset($styling['content_color']) && $styling['content_color'])
{
	$content_color = $styling['content_color'];
}

ob_start();

echo ':root,::before,::after{';

// render headings font familty
if($mec_h_fontfamily_arr): ?>
	/* == Custom Fonts For H Tag
		---------------- */
	--mec-heading-font-family: '<?php echo esc_html($mec_h_fontfamily_arr[0]); ?>';

<?php endif;

// render paragraph font familty
if($mec_p_fontfamily_arr): ?>
	/* == Custom Fonts For P Tag
		---------------- */
	--mec-paragraph-font-family: '<?php echo esc_html($mec_p_fontfamily_arr[0]); ?>';
<?php endif;

// render colorskin
if($color && $color != '#40d9f1'): ?>
	/* == TextColors
		---------------- */
	--mec-color-skin: <?php echo esc_html($color); ?>;
	--mec-color-skin-rgba-1: rgba(<?php echo esc_html($rgb_color['red']); ?>,<?php echo esc_html($rgb_color['green']); ?>,<?php echo esc_html($rgb_color['blue']); ?>,.25);
	--mec-color-skin-rgba-2: rgba(<?php echo esc_html($rgb_color['red']); ?>,<?php echo esc_html($rgb_color['green']); ?>,<?php echo esc_html($rgb_color['blue']); ?>,.5);
	--mec-color-skin-rgba-3: rgba(<?php echo esc_html($rgb_color['red']); ?>,<?php echo esc_html($rgb_color['green']); ?>,<?php echo esc_html($rgb_color['blue']); ?>,.75);
	--mec-color-skin-rgba-4: rgba(<?php echo esc_html($rgb_color['red']); ?>,<?php echo esc_html($rgb_color['green']); ?>,<?php echo esc_html($rgb_color['blue']); ?>,.11);
<?php else: ?>
	--mec-color-skin: #40d9f1;
	--mec-color-skin-rgba-1: rgba(64, 217, 241, .25);
	--mec-color-skin-rgba-2: rgba(64, 217, 241, .5);
	--mec-color-skin-rgba-3: rgba(64, 217, 241, .75);
	--mec-color-skin-rgba-4: rgba(64, 217, 241, .11);
<?php endif;

// Render Container Width
if($mec_container_normal_width): ?>
	--mec-container-normal-width: <?php echo esc_html($mec_container_normal_width); ?>;
<?php else: ?>
	--mec-container-normal-width: 1196px;
<?php endif;

if($mec_container_large_width): ?>
	--mec-container-large-width: <?php echo esc_html($mec_container_large_width); ?>;
<?php else: ?>
	--mec-container-large-width: 1690px;
<?php endif;

// Render Advanced Color Options (shortcodes)
if($title_color): ?>
	--mec-title-color: <?php echo esc_html($title_color); ?>;
<?php endif;

if($title_color_hover): ?>
	--mec-title-color-hover: <?php echo esc_html($title_color_hover); ?>;
<?php endif;

if($content_color): ?>
	--mec-content-color: <?php echo esc_html($content_color); ?>;
<?php endif;

/**
 *
 * Frontend Event Submission Layout Color Styles
 *
 */
// Main Color
$fes_main_color = '#40d9f1';
if (isset($styling['fes_color']) && $styling['fes_color']) {
	$fes_main_color = $styling['fes_color'];
	list($fes_main_color_r, $fes_main_color_g, $fes_main_color_b) = sscanf($fes_main_color, "#%02x%02x%02x");
	?>
	--mec-fes-main-color: <?php echo esc_html($fes_main_color); ?>;
	--mec-fes-main-color-rgba-1: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.12)'; ?>;
	--mec-fes-main-color-rgba-2: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.23)'; ?>;
	--mec-fes-main-color-rgba-3: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.03)'; ?>;
	--mec-fes-main-color-rgba-4: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.3)'; ?>;
	--mec-fes-main-color-rgba-5: rgb<?php echo '(' . $fes_main_color_r . ' ' . $fes_main_color_g . ' ' . $fes_main_color_b . ' / ' . '7%)'; ?>;
	--mec-fes-main-color-rgba-6: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.2)'; ?>;
	<?php
} else {
	list($fes_main_color_r, $fes_main_color_g, $fes_main_color_b) = sscanf($fes_main_color, "#%02x%02x%02x");
	?>
	--mec-fes-main-color: <?php echo esc_html($fes_main_color); ?>;
	--mec-fes-main-color-rgba-1: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.12)'; ?>;
	--mec-fes-main-color-rgba-2: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.23)'; ?>;
	--mec-fes-main-color-rgba-3: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.03)'; ?>;
	--mec-fes-main-color-rgba-4: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.3)'; ?>;
	--mec-fes-main-color-rgba-5: rgb<?php echo '(' . $fes_main_color_r . ' ' . $fes_main_color_g . ' ' . $fes_main_color_b . ' / ' . '7%)'; ?>;
	--mec-fes-main-color-rgba-6: rgba<?php echo '(' . $fes_main_color_r . ', ' . $fes_main_color_g . ', ' . $fes_main_color_b . ', ' . '0.2)'; ?>;
	<?php
}

/**
 *
 * Fluent-view Layout Color Styles
 *
 */
// Main Color
$fluent_main_color = '#ade7ff';
if (isset($styling['fluent_main_color']) && $styling['fluent_main_color']) {
	$fluent_main_color = $styling['fluent_main_color'];
	list($fluent_main_color_r, $fluent_main_color_g, $fluent_main_color_b) = sscanf($fluent_main_color, "#%02x%02x%02x");
	?>
	--mec-fluent-main-color: <?php echo esc_html($fluent_main_color); ?>;
	--mec-fluent-main-color-rgba-1: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.3)'; ?>;
	--mec-fluent-main-color-rgba-2: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.8)'; ?>;
	--mec-fluent-main-color-rgba-3: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.1)'; ?>;
	--mec-fluent-main-color-rgba-4: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.2)'; ?>;
	--mec-fluent-main-color-rgba-5: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.7)'; ?>;
	--mec-fluent-main-color-rgba-6: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.7)'; ?>;
	<?php
} else {
	list($fluent_main_color_r, $fluent_main_color_g, $fluent_main_color_b) = sscanf($fluent_main_color, "#%02x%02x%02x");
	?>
	--mec-fluent-main-color: <?php echo esc_html($fluent_main_color); ?>;
	--mec-fluent-main-color-rgba-1: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.3)'; ?>;
	--mec-fluent-main-color-rgba-2: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.8)'; ?>;
	--mec-fluent-main-color-rgba-3: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.1)'; ?>;
	--mec-fluent-main-color-rgba-4: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.2)'; ?>;
	--mec-fluent-main-color-rgba-5: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.7)'; ?>;
	--mec-fluent-main-color-rgba-6: rgba<?php echo '(' . $fluent_main_color_r . ', ' . $fluent_main_color_g . ', ' . $fluent_main_color_b . ', ' . '0.7)'; ?>;
	<?php
}

// Bold Color - Second
$fluent_bold_color = '#00acf8';
if (isset($styling['fluent_bold_color']) && $styling['fluent_bold_color']) {
	$fluent_bold_color = $styling['fluent_bold_color'];
	?>
	--mec-fluent-bold-color: <?php echo esc_html($fluent_bold_color); ?>;
	<?php
} else {
	?>
	--mec-fluent-bold-color: <?php echo esc_html($fluent_bold_color); ?>;
	<?php
}

// Background Hover Color
$fluent_bg_hover_color = '#ebf9ff';
if (isset($styling['fluent_bg_hover_color']) && $styling['fluent_bg_hover_color']) {
	$fluent_bg_hover_color = $styling['fluent_bg_hover_color'];
	?>
	--mec-fluent-bg-hover-color: <?php echo esc_html($fluent_bg_hover_color); ?>;
	<?php
} else {
	?>
	--mec-fluent-bg-hover-color: <?php echo esc_html($fluent_bg_hover_color); ?>;
	<?php
}

// Background Color
$fluent_bg_color = '#f5f7f8';
if (isset($styling['fluent_bg_color']) && $styling['fluent_bg_color']) {
	$fluent_bg_color = $styling['fluent_bg_color'];
	?>
	--mec-fluent-bg-color: <?php echo esc_html($fluent_bg_color); ?>;
	<?php
} else {
	?>
	--mec-fluent-bg-color: <?php echo esc_html($fluent_bg_color); ?>;
	<?php
}

// Second Background Color
$fluent_second_bg_color = '#d6eef9';
if (isset($styling['fluent_second_bg_color']) && $styling['fluent_second_bg_color']) {
	$fluent_second_bg_color = $styling['fluent_second_bg_color'];
	?>
	--mec-fluent-second-bg-color: <?php echo esc_html($fluent_second_bg_color); ?>;
	<?php
} else {
	?>
	--mec-fluent-second-bg-color: <?php echo esc_html($fluent_second_bg_color); ?>;
	<?php
}

echo '}';

if( isset($styling['disable_gfonts']) && !$styling['disable_gfonts']) {
	echo '
	.mec-wrap, .mec-wrap div:not([class^="elementor-"]), .lity-container, .mec-wrap h1, .mec-wrap h2, .mec-wrap h3, .mec-wrap h4, .mec-wrap h5, .mec-wrap h6, .entry-content .mec-wrap h1, .entry-content .mec-wrap h2, .entry-content .mec-wrap h3, .entry-content .mec-wrap h4, .entry-content .mec-wrap h5, .entry-content .mec-wrap h6, .mec-wrap .mec-totalcal-box input[type="submit"], .mec-wrap .mec-totalcal-box .mec-totalcal-view span, .mec-agenda-event-title a, .lity-content .mec-events-meta-group-booking select, .lity-content .mec-book-ticket-variation h5, .lity-content .mec-events-meta-group-booking input[type="number"], .lity-content .mec-events-meta-group-booking input[type="text"], .lity-content .mec-events-meta-group-booking input[type="email"],.mec-organizer-item a, .mec-single-event .mec-events-meta-group-booking ul.mec-book-tickets-container li.mec-book-ticket-container label
	{ font-family: "Montserrat", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;}';

	echo '
	.mec-event-content p, .mec-search-bar-result .mec-event-detail
	{ font-family: Roboto, sans-serif;}';

	echo ' .mec-wrap .mec-totalcal-box input, .mec-wrap .mec-totalcal-box select, .mec-checkboxes-search .mec-searchbar-category-wrap, .mec-wrap .mec-totalcal-box .mec-totalcal-view span
	{ font-family: "Roboto", Helvetica, Arial, sans-serif; }';

	echo '
	.mec-event-grid-modern .event-grid-modern-head .mec-event-day, .mec-event-list-minimal .mec-time-details, .mec-event-list-minimal .mec-event-detail, .mec-event-list-modern .mec-event-detail, .mec-event-grid-minimal .mec-time-details, .mec-event-grid-minimal .mec-event-detail, .mec-event-grid-simple .mec-event-detail, .mec-event-cover-modern .mec-event-place, .mec-event-cover-clean .mec-event-place, .mec-calendar .mec-event-article .mec-localtime-details div, .mec-calendar .mec-event-article .mec-event-detail, .mec-calendar.mec-calendar-daily .mec-calendar-d-top h2, .mec-calendar.mec-calendar-daily .mec-calendar-d-top h3, .mec-toggle-item-col .mec-event-day, .mec-weather-summary-temp
	{ font-family: "Roboto", sans-serif; }';

	echo ' .mec-fes-form, .mec-fes-list, .mec-fes-form input, .mec-event-date .mec-tooltip .box, .mec-event-status .mec-tooltip .box, .ui-datepicker.ui-widget, .mec-fes-form button[type="submit"].mec-fes-sub-button, .mec-wrap .mec-timeline-events-container p, .mec-wrap .mec-timeline-events-container h4, .mec-wrap .mec-timeline-events-container div, .mec-wrap .mec-timeline-events-container a, .mec-wrap .mec-timeline-events-container span
	{ font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }';

}

if (isset($styling['disable_fluent_height_limitation']) && $styling['disable_fluent_height_limitation']) {
	?>
	.mec-fluent-wrap.mec-skin-list-wrap .mec-calendar,
	.mec-fluent-wrap .mec-skin-weekly-view-events-container,
	.mec-fluent-wrap .mec-daily-view-events-left-side,
	.mec-fluent-wrap .mec-daily-view-events-right-side,
	.mec-fluent-wrap .mec-yearly-view-wrap .mec-yearly-calendar-sec,
	.mec-fluent-wrap .mec-yearly-view-wrap .mec-yearly-agenda-sec,
	.mec-fluent-wrap.mec-skin-grid-wrap .mec-calendar,
	.mec-fluent-wrap.mec-skin-tile-container .mec-calendar,
	.mec-fluent-wrap.mec-events-agenda-container .mec-events-agenda-wrap {
		max-height: unset !important;
	}
	<?php
}

if (isset($styling['fes_color']) && $styling['fes_color']) {
	?>
	.mec-fes-form #mec_reg_form_field_types .button.red:before, .mec-fes-form #mec_reg_form_field_types .button.red {
		border-color: #ffd2dd;
		color: #ea6485;
	}

	.mec-fes-form #mec_reg_form_field_types .button.red:hover, .mec-fes-form #mec_reg_form_field_types .button.red:before, .mec-fes-form #mec_reg_form_field_types .button:hover, .mec-fes-form #mec_bfixed_form_field_types .button:hover:before, .mec-fes-form #mec_reg_form_field_types .button:hover:before, .mec-fes-form #mec_bfixed_form_field_types .button:hover, .mec-fes-form .mec-form-row .button:not(.wp-color-result):hover {
		color: #fff;
	}

	.mec-fes-form .mec-meta-box-fields h4 label {
		background: transparent;
	}
	<?php
}

if (isset($styling['fluent_main_color']) && $styling['fluent_main_color']) {
	?>
	.mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type2 .mec-event-sharing-wrap:hover li a, .mec-single-fluent-wrap .mec-booking-button, .mec-single-fluent-wrap .mec-booking-button, .mec-single-fluent-wrap .mec-booking-button, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type1 .mec-booking-button, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type4 .mec-booking-button, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type3 .mec-booking-button {
		color: #fff !important;
	}

	.mec-fluent-wrap .mec-yearly-view-wrap .mec-agenda-events-wrap {
		background-color: transparent !important;
	}
	.mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type1 .mec-date-wrap i, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type4 .mec-date-wrap i, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type2 .mec-date-wrap i {
		background-color: #fff !important;
	}
	.mec-single-fluent-body .lity-content .mec-events-meta-group-booking button:hover, .mec-fluent-wrap.mec-single-fluent-wrap .mec-events-meta-group-booking button:hover {
		background-color: #000 !important;
	}
	<?php
}

if (isset($styling['fluent_bold_color']) && $styling['fluent_bold_color']) {
	?>
	.mec-fluent-wrap.mec-skin-cover-container .mec-event-sharing-wrap>li:first-of-type i, .mec-single-fluent-wrap .mec-single-event-bar .mec-booking-button, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type2 span.mec-event-day-num, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type2 .mec-event-sharing-wrap:hover li:first-child a {
		color: #fff;
	}

	.mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type1 .mec-booking-button:hover, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type4 .mec-booking-button:hover, .mec-fluent-wrap.mec-skin-cover-container .mec-event-cover-fluent-type3 .mec-booking-button:hover {
		background-color: #fff;
	}
	<?php
}

// get render content
$out = '';
$out = ob_get_clean();

// minify css
$out = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $out);
$out = str_replace(array("\r\n", "\r", "\n", "\t", '    '), '', $out);

update_option('mec_gfont', $fonts_url);
update_option('mec_dyncss', $out);