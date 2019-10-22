<?php
/** no direct access **/
defined('MECEXEC') or die();

$has_events = array();
$settings = $this->main->get_settings();
?>
<?php if($this->style == 'modern'): ?>
<div class="mec-timetable-day-events mec-clear mec-weekly-view-dates-events">
    <?php foreach($this->events as $date=>$events): $week = $this->week_of_days[$date]; ?>
    <?php
        if(!isset($has_events[$week]))
        {
            foreach($this->weeks[$week] as $weekday) if(isset($this->events[$weekday]) and count($this->events[$weekday])) $has_events[$week] = true;
        }
    ?>
    <?php if(count($events)): ?>
    <div class="mec-timetable-events-list <?php echo ($date == $this->active_date ? '' : 'mec-util-hidden'); ?> mec-weekly-view-date-events mec-calendar-day-events mec-clear mec-weekly-view-week-<?php echo $this->id; ?>-<?php echo date('Ym', strtotime($date)).$week; ?>" id="mec_weekly_view_date_events<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>" data-week-number="<?php echo $week; ?>">
        <?php foreach($events as $event): ?>
            <?php
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
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
            <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-timetable-event mec-timetable-day-<?php echo $this->id; ?>-<?php echo date('Ymd', strtotime($date)); ?> <?php echo $this->get_event_classes($event); ?>">
                <span class="mec-timetable-event-span mec-timetable-event-time">
                    <i class="mec-sl-clock"></i>
                    <?php if(trim($start_time)): ?>
                    <span><?php echo $start_time.(trim($end_time) ? ' - '.$end_time : ''); ?></span>
                    <?php endif; ?>
                </span>
                <span class="mec-timetable-event-span mec-timetable-event-title">
                    <a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?>
                    <?php if (!empty($label_style)) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                </span>
                
                <span class="mec-timetable-event-span mec-timetable-event-location">
                    <i class="mec-sl-location-pin"></i>
                    <?php if(isset($location['name']) and trim($location['name'])): ?>
                    <span><?php echo (isset($location['name']) ? $location['name'] : ''); ?></span>
                    <?php endif; ?>
                </span>
                <span class="mec-timetable-event-span mec-timetable-event-organizer">
                    <i class="mec-sl-user"></i>
                    <?php if(isset($organizer['name']) and trim($organizer['name'])): ?>
                    <span><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></span>
                    <?php endif; ?>
                </span>
            </article>
        <?php endforeach; ?>
    </div>
    <?php elseif(!isset($has_events[$week])): $has_events[$week] = 'printed'; ?>
    <div class="mec-timetable-events-list mec-weekly-view-date-events mec-util-hidden mec-calendar-day-events mec-clear mec-weekly-view-week-<?php echo $this->id; ?>-<?php echo date('Ym', strtotime($date)).$week; ?>" id="mec_weekly_view_date_events<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>" data-week-number="<?php echo $week; ?>">
        <article class="mec-event-article"><h4 class="mec-event-title"><?php _e('No Events', 'modern-events-calendar-lite'); ?></h4><div class="mec-event-detail"></div></article>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>
<div class="mec-event-footer"></div>
<?php elseif($this->style == 'clean'): ?>
<div class="mec-timetable-t2-wrap">
    <?php foreach($this->events as $date=>$events): ?>
    <div class="mec-timetable-t2-col mec-timetable-col-<?php echo $this->number_of_days; ?>">
        <div class="mec-ttt2-title"> <?php echo date_i18n('l', strtotime($date)); ?> </div>
        <?php foreach($events as $event): ?>
        <?php
            $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
            $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
            $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
            $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
            $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';

            $label_style = '';
            if(!empty($event->data->labels))
            {
                foreach($event->data->labels as $label)
                {
                    if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;

                    if($label['style'] == 'mec-label-featured') $label_style = esc_html__( 'Featured' , 'modern-events-calendar-lite' );
                    elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__( 'Canceled' , 'modern-events-calendar-lite' );
                }
            }
        ?>
        <article class="mec-event-article <?php echo $this->get_event_classes($event); ?>">
            <?php echo $event_color; ?>
            <div class="mec-timetable-t2-content">
                <h4 class="mec-event-title">
                    <a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a>
                    <?php if(!empty($label_style)) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                </h4>
                <div class="mec-event-time">
                    <i class="mec-sl-clock-o"></i>
                    <?php if(trim($start_time)): ?>
                    <span><?php echo $start_time.(trim($end_time) ? ' - '.$end_time : ''); ?></span>
                    <?php endif; ?>
                </div>
                <div class="mec-event-loction">
                    <i class="mec-sl-location-pin"></i>
                    <?php if(isset($location['name']) and trim($location['name'])): ?>
                        <span><?php echo (isset($location['name']) ? $location['name'] : ''); ?></span>
                    <?php endif; ?>
                </div>
                <div class="mec-event-organizer">
                    <i class="mec-sl-user"></i>
                    <?php if(isset($organizer['name']) and trim($organizer['name'])): ?>
                        <span><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>