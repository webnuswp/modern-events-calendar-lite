<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event = $this->events[0];
$settings = $this->main->get_settings();

// Event is not valid!
if(!isset($event->data)) return;

$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$event_location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
$event_organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
$event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);
$event_link = (isset($event->data->permalink) and trim($event->data->permalink)) ? $this->main->get_event_date_permalink($event->data->permalink, $event_date) : get_permalink($event->data->ID);
$event_title = $event->data->title;
$event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
$event_thumb = $event->data->thumbnails['large']; 
$event_thumb_url = $event->data->featured_image['large'];
$label_style = '';
if ( !empty($event->data->labels) ):
foreach( $event->data->labels as $label)
{
    if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
    if ( $label['style']  == 'mec-label-featured' )
    {
        $label_style = esc_html__( 'Featured' , 'modern-events-calendar-lite' );
    } 
    elseif ( $label['style']  == 'mec-label-canceled' )
    {
        $label_style = esc_html__( 'Canceled' , 'modern-events-calendar-lite' );
    }
}
endif;
$speakers = '""';
if ( !empty($event->data->speakers)) 
{
    $speakers= [];
    foreach ($event->data->speakers as $key => $value) {
        $speakers[] = array(
            "@type" 	=> "Person",
            "name"		=> $value['name'],
            "image"		=> $value['thumbnail'],
            "sameAs"	=> $value['facebook'],
        );
    } 
    $speakers = json_encode($speakers);
}
do_action('mec_start_skin' , $this->id);
do_action('mec_cover_skin_head');
?>
<div class="mec-wrap <?php echo $event_colorskin . ' ' . $this->html_class; ?>">
<?php
    $schema_settings = isset( $settings['schema'] ) ? $settings['schema'] : '';
    if($schema_settings == '1' ):
?>
    <script type="application/ld+json">
    {
        "@context" 		: "http://schema.org",
        "@type" 		: "Event",
        "startDate" 	: "<?php echo !empty( $event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : '' ; ?>",
        "endDate" 		: "<?php echo !empty( $event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : '' ; ?>",
        "location" 		:
        {
            "@type" 		: "Place",
            "name" 			: "<?php echo (isset($location['name']) ? $location['name'] : ''); ?>",
            "image"			: "<?php echo (isset($location['thumbnail']) ? esc_url($location['thumbnail'] ) : '');; ?>",
            "address"		: "<?php echo (isset($location['address']) ? $location['address'] : ''); ?>"
        },
        "offers": {
            "url": "<?php echo $event->data->permalink; ?>",
            "price": "<?php echo isset($event->data->meta['mec_cost']) ? $event->data->meta['mec_cost'] : '' ; ?>",
            "priceCurrency" : "<?php echo isset($settings['currency']) ? $settings['currency'] : ''; ?>"
        },
        "performer": <?php echo $speakers; ?>,
        "description" 	: "<?php  echo esc_html(preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', $event->data->post->post_content)); ?>",
        "image" 		: "<?php echo !empty($event->data->featured_image['full']) ? esc_html($event->data->featured_image['full']) : '' ; ?>",
        "name" 			: "<?php esc_html_e($event->data->title); ?>",
        "url"			: "<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"
    }
    </script>
    <?php
    endif;
    if($this->style == 'modern' and $event_thumb_url): ?>
    <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-cover-modern <?php echo $this->get_event_classes($event); ?>" style="background: url('<?php echo $event_thumb_url; ?>'); height: 678px;background-size: cover;">
        <a href="<?php echo $event_link; ?>" class="mec-event-cover-a">
        <div class="mec-event-overlay mec-bg-color"></div>
            <div class="mec-event-detail">
                <?php if ( empty($label_style) ) echo '<div class="mec-event-tag mec-color">' . __('featured event', 'modern-events-calendar-lite') . ' </div>'; else echo '<span class="mec-fc-style">'.$label_style.'</span>';  ?>
                <div class="mec-event-date"><?php echo date_i18n($this->date_format_modern1, strtotime($event_date)).((isset($event->data->time) and trim($event->data->time['start'])) ? ' - '.$event->data->time['start'] : ''); ?></div>
                <h4 class="mec-event-title"><?php echo $event_title . $event_color; ?></h4>
                <div class="mec-event-place"><?php echo (isset($event_location['name']) ? $event_location['name'] : ''); ?></div>
            </div>
        </a>
    </article>
    <?php elseif($event_thumb): ?>
    <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-cover-<?php echo $this->style; ?> <?php echo $this->get_event_classes($event); ?>">
        <div class="mec-event-image"><?php echo $event_thumb; ?></div>
        <div class="mec-event-overlay"></div>
        <?php if($this->style == 'classic'): ?>
        <div class="mec-event-content">
            <i class="mec-event-icon mec-bg-color mec-fa-calendar"></i>
            <div class="mec-event-date">
                <span class="mec-color"><?php echo date_i18n($this->date_format_classic1, strtotime($event_date)); ?></span> <?php echo date_i18n($this->date_format_classic2, strtotime($event_date)); ?>
            </div>
            <h4 class="mec-event-title"><?php echo $event_title . $event_color; ?><?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?></h4>
            <div class="mec-btn-wrapper"><a class="mec-event-button" href="<?php echo $event_link; ?>"><?php echo $this->main->m('event_detail', __('EVENT DETAIL', 'modern-events-calendar-lite')); ?></a></div>
        </div>
        <?php elseif($this->style == 'clean'): ?>
        <div class="mec-event-content">
            <h4 class="mec-event-title"><a href="<?php echo $event_link; ?>"><?php echo $event_title; ?></a><?php echo $event_color; ?><?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?></h4>
            <?php if(isset($event_organizer['name'])): ?><div class="mec-event-place"><?php echo (isset($event_organizer['name']) ? $event_organizer['name'] : ''); ?></div><?php endif; ?>
        </div>
        <div class="mec-event-date mec-bg-color">
            <div class="dday"><?php echo date_i18n($this->date_format_clean1, strtotime($event_date)); ?></div>
            <div class="dmonth"><?php echo date_i18n($this->date_format_clean2, strtotime($event_date)); ?></div>
            <div class="dyear"><?php echo date_i18n($this->date_format_clean3, strtotime($event_date)); ?></div>
        </div>
        <?php endif; ?>
    </article>
    <?php endif; ?>
</div>