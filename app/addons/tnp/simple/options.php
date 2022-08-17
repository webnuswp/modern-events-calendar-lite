<?php
/* @var $options array containing all the options of the current block */
/* @var $fields NewsletterFields */

/** @var MEC_main $main */
$main = MEC_base::getInstance('app.libraries.main');
?>

<?php
$posts = get_posts(array(
    'post_type' => $main->get_main_post_type(),
    'posts_per_page' => -1
));

$options = array('' => '-----');
foreach($posts as $post) $options['' . $post->ID] = $post->post_title;

$fields->select('event_id', esc_html__('Event', 'modern-events-calendar-lite'), $options); ?>

<?php $fields->select('layout', esc_html__('Layout', 'modern-events-calendar-lite'), array(
    'full' => 'Full',
    'left' => 'Image left',
    'right' => 'Image right'
)); ?>

<?php $fields->block_commons();