<?php
/*
 * Name: Modern Events Calendar
 * Section: content
 * Description: Include MEC events into your newsletter
 */

/* @var $options array */

$default_options = array(
    'event_id' => NULL,
    'layout' => 'full',

    // block_ prefixed options are reserved for Newsletter and the ones below managed directly by Newsletter
    'block_padding_left' => 15,
    'block_padding_right' => 15,
    'block_padding_top' => 15,
    'block_padding_bottom' => 15,
    'block_background' => '#ffffff',
);

$options = array_merge($default_options, $options);

/** @var MEC_main $main */
$main = MEC_base::getInstance('app.libraries.main');

$event_id = $options['event_id'];

$layout = $options['layout'];
if(!$event_id) $layout = 'empty';

$td_width = round((600 - $options['block_padding_left'] - $options['block_padding_right'] - 20)/2);

$featured_image_id = $event_id ? get_post_thumbnail_id($event_id) : NULL;
if($event_id and $featured_image_id)
{
    if($layout === 'full')
    {
        $image_width = 600 - $options['block_padding_left'] - $options['block_padding_right'];
        $media = tnp_resize_2x($featured_image_id, [$image_width, 0]);
    }
    else
    {
        $media = tnp_resize_2x($featured_image_id, [$td_width, 0]);
    }
}
else $media = false;

$title_style = TNP_Composer::get_title_style($options, 'title', $composer);
$text_style = TNP_Composer::get_text_style($options, '', $composer);

$event = $event_id ? get_post($event_id) : NULL;

$button_options = $options;
$button_options['button_url'] = $event_id ? get_permalink($event) : NULL;
$button_options['button_label'] = esc_html__('Click Here', 'modern-events-calendar-lite');

switch ($layout) {
    case 'left':
        include __DIR__ . '/block-left.php';
        return;
    case 'right':
        include __DIR__ . '/block-right.php';
        return;
    case 'empty':
        include __DIR__ . '/block-empty.php';
        return;
    default:
        include __DIR__ . '/block-full.php';
        return;
}
