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
$event_thumb_url = $event->data->featured_image['large'];
$start_date = (isset($event->date['start']) and isset($event->date['start']['date'])) ? $event->date['start']['date'] : date('Y-m-d H:i:s');
$end_date = (isset($event->date['end']) and isset($event->date['end']['date'])) ? $event->date['end']['date'] : date('Y-m-d H:i:s');

$event_time = '';
$event_time .= sprintf("%02d", (isset($event->data->meta['mec_date']['start']['hour']) ? $event->data->meta['mec_date']['start']['hour'] : 8)).':';
$event_time .= sprintf("%02d", (isset($event->data->meta['mec_date']['start']['minutes']) ? $event->data->meta['mec_date']['start']['minutes'] : 0));
$event_time .= (isset($event->data->meta['mec_date']['start']['ampm']) ? $event->data->meta['mec_date']['start']['ampm'] : 'AM');

$event_etime = '';
$event_etime .= sprintf("%02d", (isset($event->data->meta['mec_date']['end']['hour']) ? $event->data->meta['mec_date']['end']['hour'] : 6)).':';
$event_etime .= sprintf("%02d", (isset($event->data->meta['mec_date']['end']['minutes']) ? $event->data->meta['mec_date']['end']['minutes'] : 0));
$event_etime .= (isset($event->data->meta['mec_date']['end']['ampm']) ? $event->data->meta['mec_date']['end']['ampm'] : 'PM');

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

$start_time = date('D M j Y G:i:s', strtotime($start_date.' '.date('H:i:s', strtotime($event_time))));
$end_time = date('D M j Y G:i:s', strtotime($end_date.' '.date('H:i:s', strtotime($event_etime))));

$d1 = new DateTime($start_time);
$d2 = new DateTime(current_time("D M j Y G:i:s"));
$d3 = new DateTime($end_time);

$ongoing = (isset($settings['hide_time_method']) and trim($settings['hide_time_method']) == 'end') ? true : false;

// Skip if event is expired
if($ongoing) if($d3 < $d2) $ongoing = false;

if($d1 < $d2 and !$ongoing) return;

$gmt_offset = $this->main->get_gmt_offset();
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') === false) $gmt_offset = ' : '.$gmt_offset;
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') == true) $gmt_offset = '';

// Generating javascript code of countdown module
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_available_spot'.$this->id.'").mecCountDown(
    {
        date: "'.(($ongoing and (isset($event->data->meta['mec_repeat_status']) and $event->data->meta['mec_repeat_status'] == 0)) ? $end_time : $start_time).$gmt_offset.'",
        format: "off"
    },
    function()
    {
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);

$book = $this->getBook();
$availability = $book->get_tickets_availability($event->data->ID, $start_date);
$event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';

$spots = 0;
foreach($availability as $ticket_id=>$count)
{
    if(!is_numeric($ticket_id)) continue;

    if($count != '-1') $spots += $count;
    else
    {
        $spots = -1;
        break;
    }
}

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
do_action('mec_available_spot_skin_head');
?>
<div class="mec-wrap <?php echo $event_colorskin; ?> <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
    <div class="mec-av-spot-wrap">
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
        <?php endif; ?>        
        <div class="mec-av-spot">
            <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>">

                <?php if($event_thumb_url): ?>
                <div class="mec-av-spot-img" style="background: url('<?php echo $event_thumb_url; ?>');"></div>
                <?php endif; ?>

                <div class="mec-av-spot-head clearfix">
                    <div class="mec-av-spot-col6">
                        <div class="mec-av-spot-box"><?php _e('Available Spot(s):', 'modern-events-calendar-lite'); ?> <span class="mec-av-spot-count mec-color"><?php echo ($spots != '-1' ? $spots : __('Unlimited', 'modern-events-calendar-lite')); ?></span></div>
                    </div>
                    <div class="mec-av-spot-col6">
                        <div class="mec-event-countdown" id="mec_skin_available_spot<?php echo $this->id; ?>">
                            <ul class="clockdiv" id="countdown">
                                <li class="days-w block-w">
                                    <span class="mec-days">00</span>
                                    <p class="mec-timeRefDays label-w"><?php _e('days', 'modern-events-calendar-lite'); ?></p>
                                </li>
                                <li class="hours-w block-w">
                                    <span class="mec-hours">00</span>
                                    <p class="mec-timeRefHours label-w"><?php _e('hours', 'modern-events-calendar-lite'); ?></p>
                                </li>
                                <li class="minutes-w block-w">
                                    <span class="mec-minutes">00</span>
                                    <p class="mec-timeRefMinutes label-w"><?php _e('minutes', 'modern-events-calendar-lite'); ?></p>
                                </li>
                                <li class="seconds-w block-w">
                                    <span class="mec-seconds">00</span>
                                    <p class="mec-timeRefSeconds label-w"><?php _e('seconds', 'modern-events-calendar-lite'); ?></p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mec-av-spot-content mec-event-grid-modern">

                    <div class="event-grid-modern-head clearfix">
                        <div class="mec-av-spot-col6">
                            <div class="mec-event-date mec-color"><?php echo date_i18n($this->date_format1, strtotime($event_date)); ?></div>
                            <div class="mec-event-month"><?php echo date_i18n($this->date_format2, strtotime($event_date)); ?></div>
                            <div class="mec-event-detail"><?php echo (isset($event->data->time) and isset($event->data->time['start'])) ? $event->data->time['start'] : ''; ?><?php echo (isset($event->data->time) and isset($event->data->time['end']) and trim($event->data->time['end'])) ? ' - '.$event->data->time['end'] : ''; ?></div>
                        </div>
                        <div class="mec-av-spot-col6">
                            <?php if(isset($event_location['name'])): ?>
                            <div class="mec-event-location">
                                <i class="mec-sl-location-pin mec-color"></i>
                                <div class="mec-event-location-det">
                                    <h6 class="mec-location"><?php echo $event_location['name']; ?></h6>
                                    <?php if(isset($event_location['address']) and trim($event_location['address'])): ?><address class="mec-events-address"><span class="mec-address"><?php echo $event_location['address']; ?></span></address><?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mec-event-content">
                        <h4 class="mec-event-title"><a class="mec-color-hover" href="<?php echo $event_link; ?>"><?php echo $event_title; ?></a><?php echo $event_color; ?></h4>
                        <?php
                            $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';

                            // Safe Excerpt for UTF-8 Strings
                            if(!trim($excerpt))
                            {
                                $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                                $words = array_slice($ex, 0, 30);

                                $excerpt = implode(' ', $words);
                            }
                        ?>
                        <div class="mec-event-description mec-events-content">
                            <p><?php echo $excerpt.(trim($excerpt) ? ' ...' : ''); ?></p>
                        </div>
                    </div>
                    <div class="mec-event-footer">
                        <a class="mec-booking-button" href="<?php echo $event_link; ?>" target="_self"><?php echo $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')); ?></a>
                    </div>
                </div>
            </article>
        </div>

    </div>
</div>