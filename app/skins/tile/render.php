<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-event-tile-view">
        <?php
        $count = $this->count;

        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        $rcount = 1 ;
        foreach($this->events as $date):
            foreach($date as $event):

                echo ($rcount == 1) ? '<div class="row">' : '';
                echo '<div class="col-md-'.$col.' col-sm-'.$col.'">';
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                $event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
                $background_image = (isset($event->data->featured_image['tileview']) && trim($event->data->featured_image['tileview'])) ? ' url(\''.trim($event->data->featured_image['tileview']).'\')' : '';
                $label_style = '';
                if(!empty($event->data->labels))
                {
                    foreach( $event->data->labels as $label)
                    {
                        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;

                        if($label['style'] == 'mec-label-featured') $label_style = esc_html__('Featured' , 'modern-events-calendar-lite');
                        elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled' , 'modern-events-calendar-lite');
                    }
                }

                $speakers = '""';
                if(!empty($event->data->speakers))
                {
                    $speakers= [];
                    foreach($event->data->speakers as $key => $value)
                    {
                        $speakers[] = array(
                            "@type" 	=> "Person",
                            "name"		=> $value['name'],
                            "image"		=> $value['thumbnail'],
                            "sameAs"	=> $value['facebook'],
                        );
                    }

                    $speakers = json_encode($speakers);
                }

                $schema_settings = isset($settings['schema']) ? $settings['schema'] : '';
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
                <article <?php echo 'style="background:' . $event_color . $background_image. '"'; ?> data-style="<?php echo $label_style; ?>" class="<?php echo ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : ''); ?> mec-event-article mec-tile-item mec-clear <?php echo $this->get_event_classes($event); ?>">
                    <div class="event-tile-view-head clearfix">
                        <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                            <div class="mec-event-date"><?php echo date_i18n($this->date_format_clean_1, strtotime($event->date['start']['date'])); ?></div>
                            <div class="mec-event-month"><?php echo date_i18n($this->date_format_clean_2, strtotime($event->date['start']['date'])); ?></div>
                        <?php else: ?>
                            <div class="mec-event-month"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_clean_1 .' '. $this->date_format_clean_2); ?></div>
                        <?php endif; ?>
                        <div class="mec-event-time"><i class="mec-sl-clock"></i><?php echo $start_time; ?></div>
                    </div>
                    <div class="mec-event-content">
                        <div class="mec-event-detail"><?php echo (isset($location['name']) ? '<i class="mec-sl-location-pin"></i>' . $location['name'] : ''); ?></div>
                        <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $this->main->get_flags($event->data->ID, $event_start_date); ?></h4>
                    </div>
                </article>
                <?php
                echo '</div>';
                if($rcount == $count)
                {
                    echo '</div>';
                    $rcount = 0;
                }

                $rcount++;
                ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>