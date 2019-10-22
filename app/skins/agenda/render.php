<?php
/** no direct access **/
defined('MECEXEC') or die();

$current_month_divider = $this->request->getVar('current_month_divider', 0);
$settings = $this->main->get_settings();
?>
<?php foreach($this->events as $date=>$events): ?>

    <?php $month_id = date('Ym', strtotime($date)); if($this->month_divider and $month_id != $current_month_divider): $current_month_divider = $month_id; ?>
        <div class="mec-month-divider" data-toggle-divider="mec-toggle-<?php echo date_i18n('Ym', strtotime($date)); ?>-<?php echo $this->id; ?>"><span><?php echo date_i18n('F Y', strtotime($date)); ?></span><i class="mec-sl-arrow-down"></i></div>
    <?php endif; ?>
    <div class="mec-events-agenda">

        <div class="mec-agenda-date-wrap">
            <i class="mec-sl-calendar"></i>
            <span class="mec-agenda-day"><?php echo date_i18n($this->date_format_clean_1, strtotime($date)); ?></span>
            <span class="mec-agenda-date"><?php echo date_i18n($this->date_format_clean_2, strtotime($date)); ?></span>
        </div>

        <div class="mec-agenda-events-wrap">
            <?php
                foreach($events as $event)
                {
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
                    <?php
                    endif;
                    if($this->style == 'clean'): ?>
                    <div class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-agenda-event <?php echo $this->get_event_classes($event); ?>">
                        <i class="mec-sl-clock "></i>
                        <span class="mec-agenda-time">
                            <?php
                                if(trim($start_time))
                                {
                                    echo '<span class="mec-start-time">'.$start_time.'</span>';
                                    if(trim($end_time)) echo ' - <span class="mec-end-time">'.$end_time.'</span>';
                                }
                            ?>
                        </span>
                        <span class="mec-agenda-event-title">
                            <a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a>
                            <?php echo $event_color; ?>
                            <?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                        </span>
                        <?php do_action( 'mec_agenda_skin_attribute', $organizer, $location ); ?>
                    </div>
                <?php endif; ?>
            <?php } ?>
        </div>
    </div>
<?php endforeach;