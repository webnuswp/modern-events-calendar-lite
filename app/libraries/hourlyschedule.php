<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Hourly Schedule class.
 * @author Webnus <info@webnus.net>
 */
class MEC_hourlyschedule extends MEC_base
{
    /**
     * @var MEC_main
     */
    public $main;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Main
        $this->main = $this->getMain();
    }

    public function form($args)
    {
        $hourly_schedules = $args['hourly_schedules'];
        $speakers_status = $args['speakers_status'];
        $speakers = $args['speakers'];
        $wrapper_class = (isset($args['wrapper_class']) ? $args['wrapper_class'] : 'mec-meta-box-fields mec-event-tab-content mec-fes-hourly-schedule');
        $prefix = (isset($args['prefix']) ? $args['prefix'] : '');
        $name_prefix = ((isset($args['name_prefix']) and trim($args['name_prefix'])) ? $args['name_prefix'] : 'mec');
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>" id="<?php echo esc_attr($prefix); ?>mec-hourly-schedule">
            <h4><?php esc_html_e('Hourly Schedule', 'modern-events-calendar-lite'); ?></h4>
            <div id="<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_day_form">
                <div class="mec-form-row">
                    <button class="button mec-add-hourly-schedule-day-button" type="button" data-prefix="<?php echo esc_attr($prefix); ?>" data-append="#<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_days" data-key="#<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_day_key" data-raw="#<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_day_raw"><?php esc_html_e('Add Day', 'modern-events-calendar-lite'); ?></button>
                    <span class="description"><?php esc_attr_e('Add new days for schedule. For example if your event is multiple days, you can add a different schedule for each day!', 'modern-events-calendar-lite'); ?></span>
                </div>
            </div>
            <div id="<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_days">
                <?php $d = 0; foreach($hourly_schedules as $day): ?>
                    <div id="<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_day_<?php echo esc_attr($d); ?>">
                        <h4><?php echo isset($day['title']) ? esc_html($day['title']) : sprintf(esc_html__('Day %s', 'modern-events-calendar-lite'), $d + 1); ?></h4>
                        <div id="<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_form<?php echo esc_attr($d); ?>">
                            <div class="mec-form-row">
                                <div class="mec-col-1"><label for="<?php echo esc_attr($prefix); ?>mec_add_hourly_schedule_day<?php echo esc_attr($d); ?>_title"><?php echo esc_html__('Title', 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <div class="mec-col-10"><input type="text" id="<?php echo esc_attr($prefix); ?>mec_add_hourly_schedule_day<?php echo esc_attr($d); ?>_title" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][title]" value="<?php echo isset($day['title']) ? esc_attr($day['title']) : ''; ?>" class="widefat"></div>
                                <div class="mec-col-1">
                                    <button class="button mec-hourly-schedule-remove-day-button" type="button" onclick="mec_hourly_schedule_day_remove(<?php echo esc_attr($d); ?>, '<?php echo esc_attr($prefix); ?>');"><?php echo esc_html__('Remove', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <button class="button mec-add-hourly-schedule-button" type="button" id="<?php echo esc_attr($prefix); ?>mec_add_hourly_schedule_button<?php echo esc_attr($d); ?>" data-day="<?php echo esc_attr($d); ?>" data-prefix="<?php echo esc_attr($prefix); ?>"><?php esc_html_e('Add', 'modern-events-calendar-lite'); ?></button>
                                <span class="description"><?php esc_attr_e('Add new hourly schedule row', 'modern-events-calendar-lite'); ?></span>
                            </div>
                            <div id="<?php echo esc_attr($prefix); ?>mec_hourly_schedules<?php echo esc_attr($d); ?>">
                                <?php $i = 0; foreach($day['schedules'] as $key => $hourly_schedule): if(!is_numeric($key)) continue; $i = max($i, $key); ?>
                                    <div class="mec-form-row mec-box" id="<?php echo esc_attr($prefix); ?>mec_hourly_schedule_row<?php echo esc_attr($d); ?>_<?php echo esc_attr($key); ?>">
                                        <input class="mec-col-1" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][<?php echo esc_attr($key); ?>][from]" placeholder="<?php esc_attr_e('From e.g. 8:15', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($hourly_schedule['from']); ?>"/>
                                        <input class="mec-col-1" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][<?php echo esc_attr($key); ?>][to]" placeholder="<?php esc_attr_e('To e.g. 8:45', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($hourly_schedule['to']); ?>"/>
                                        <input class="mec-col-3" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][<?php echo esc_attr($key); ?>][title]" placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($hourly_schedule['title']); ?>"/>
                                        <?php if(apply_filters('mec_hourly_schedule_custom_field_description_status', false)): ?>
                                            <?php
                                                $field_name = "{$name_prefix}[hourly_schedules][{$d}][schedules][{$key}][description]";
                                                do_action('mec_hourly_schedule_custom_field_description', $hourly_schedule,$field_name, $name_prefix, $d, $key );
                                            ?>
                                        <?php else: ?>
                                            <input class="mec-col-6" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][<?php echo esc_attr($key); ?>][description]" placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($hourly_schedule['description']); ?>"/>
                                        <?php endif; ?>
                                        <button class="button mec-remove-hourly-schedule-button" type="button" onclick="mec_hourly_schedule_remove(<?php echo esc_attr($d); ?>, <?php echo esc_attr($key); ?>, '<?php echo esc_attr($prefix); ?>');"><?php esc_html_e('Remove', 'modern-events-calendar-lite'); ?></button>
                                        <?php if($speakers_status): ?>
                                        <div class="mec-col-12 mec-hourly-schedule-form-speakers" data-d="<?php echo esc_attr($d); ?>" data-key="<?php echo esc_attr($key); ?>" data-name-prefix="<?php echo esc_attr($name_prefix); ?>">
                                            <strong><?php echo esc_html($this->main->m('taxonomy_speakers', esc_html__('Speakers', 'modern-events-calendar-lite'))); ?></strong>
                                            <?php foreach($speakers as $speaker): ?>
                                            <label><input type="checkbox" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][<?php echo esc_attr($key); ?>][speakers][]" value="<?php echo esc_attr($speaker->term_id); ?>" <?php echo (isset($hourly_schedule['speakers']) and in_array($speaker->term_id, $hourly_schedule['speakers'])) ? 'checked="checked"' : ''; ?>><?php echo esc_html($speaker->name); ?></label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" id="<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_key<?php echo esc_attr($d); ?>"
                               value="<?php echo ($i + 1); ?>"/>
                        <div class="mec-util-hidden" id="<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_raw<?php echo esc_attr($d); ?>">
                            <div class="mec-form-row mec-box" id="<?php echo esc_attr($prefix); ?>mec_hourly_schedule_row<?php echo esc_attr($d); ?>_:i:">
                                <input class="mec-col-1" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][:i:][from]" placeholder="<?php esc_attr_e('From e.g. 8:15', 'modern-events-calendar-lite'); ?>"/>
                                <input class="mec-col-1" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][:i:][to]" placeholder="<?php esc_attr_e('To e.g. 8:45', 'modern-events-calendar-lite'); ?>"/>
                                <input class="mec-col-3" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][:i:][title]" placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"/>
                                <?php if(apply_filters('mec_hourly_schedule_custom_field_description_status',false)): ?>
                                    <?php
                                        $field_name = "{$name_prefix}[hourly_schedules][{$d}][schedules][:i:][description]";
                                        do_action('mec_hourly_schedule_custom_field_description', [],$field_name, $name_prefix, $d, ':i:' );
                                    ?>
                                <?php else: ?>
                                    <input class="mec-col-6" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][:i:][description]" placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>" />
                                <?php endif; ?>
                                <button class="button mec-remove-hourly-schedule-button" type="button" onclick="mec_hourly_schedule_remove(<?php echo esc_attr($d); ?>, :i:, '<?php echo esc_attr($prefix); ?>');"><?php esc_html_e('Remove', 'modern-events-calendar-lite'); ?></button>
                                <?php if($speakers_status): ?>
                                <div class="mec-col-12 mec-hourly-schedule-form-speakers" data-d="<?php echo esc_attr($d); ?>" data-key=":i:" data-name-prefix="<?php echo esc_attr($name_prefix); ?>">
                                    <strong><?php echo esc_html($this->main->m('taxonomy_speakers', esc_html__('Speakers', 'modern-events-calendar-lite'))); ?></strong>
                                    <?php foreach($speakers as $speaker): ?>
                                    <label><input type="checkbox" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][<?php echo esc_attr($d); ?>][schedules][:i:][speakers][]" value="<?php echo esc_attr($speaker->term_id); ?>"><?php echo esc_html($speaker->name); ?></label>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $d++;
                endforeach;
                ?>
            </div>
            <input type="hidden" id="<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_day_key" value="<?php echo esc_attr($d); ?>"/>
            <div class="mec-util-hidden" id="<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_day_raw">
                <div id="<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_day_:d:">
                    <h4><?php echo esc_html__('New Day', 'modern-events-calendar-lite'); ?></h4>
                    <div id="<?php echo esc_attr($prefix); ?>mec_meta_box_hourly_schedule_form:d:">
                        <div class="mec-form-row">
                            <div class="mec-col-1"><label for="<?php echo esc_attr($prefix); ?>mec_add_hourly_schedule_day:d:_title"><?php echo esc_html__('Title', 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <div class="mec-col-10"><input type="text" id="<?php echo esc_attr($prefix); ?>mec_add_hourly_schedule_day:d:_title" class="mec_add_hourly_schedule_day_title" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][:d:][title]" value="<?php echo esc_html__('New Day', 'modern-events-calendar-lite'); ?>" class="widefat">
                            </div>
                            <div class="mec-col-1">
                                <button class="button mec-hourly-schedule-remove-day-button" type="button" onclick="mec_hourly_schedule_day_remove(:d:, '<?php echo esc_attr($prefix); ?>');"><?php echo esc_html__('Remove', 'modern-events-calendar-lite'); ?></button>
                            </div>
                        </div>
                        <div class="mec-form-row">
                            <button class="button mec-add-hourly-schedule-button" type="button" id="<?php echo esc_attr($prefix); ?>mec_add_hourly_schedule_button:d:" data-day=":d:" data-prefix="<?php echo esc_attr($prefix); ?>"><?php esc_html_e('Add', 'modern-events-calendar-lite'); ?></button>
                            <span class="description"><?php esc_attr_e('Add new hourly schedule row', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div id="<?php echo esc_attr($prefix); ?>mec_hourly_schedules:d:">
                        </div>
                    </div>
                    <input type="hidden" id="<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_key:d:" value="1"/>
                    <div class="mec-util-hidden mec-hourly-schedule-raw" id="<?php echo esc_attr($prefix); ?>mec_new_hourly_schedule_raw:d:">
                        <div class="mec-form-row mec-box" id="<?php echo esc_attr($prefix); ?>mec_hourly_schedule_row:d:_:i:">
                            <input class="mec-col-1" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][:d:][schedules][:i:][from]" placeholder="<?php esc_attr_e('From e.g. 8:15', 'modern-events-calendar-lite'); ?>"/>
                            <input class="mec-col-1" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][:d:][schedules][:i:][to]" placeholder="<?php esc_attr_e('To e.g. 8:45', 'modern-events-calendar-lite'); ?>"/>
                            <input class="mec-col-3" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][:d:][schedules][:i:][title]" placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"/>
                            <?php if(apply_filters('mec_hourly_schedule_custom_field_description_status',false)): ?>
                                <?php
                                    $field_name = "{$name_prefix}[hourly_schedules][:d:][schedules][:i:][description]";
                                    do_action('mec_hourly_schedule_custom_field_description', [],$field_name, $name_prefix, ':d:', ':i:' );
                                ?>
                            <?php else: ?>
                                <input class="mec-col-6" type="text" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][:d:][schedules][:i:][description]" placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>" />
                            <?php endif; ?>
                            <button class="button mec-remove-hourly-schedule-button" type="button" onclick="mec_hourly_schedule_remove(:d:, :i:, '<?php echo esc_attr($prefix); ?>');"><?php esc_html_e('Remove', 'modern-events-calendar-lite'); ?></button>
                            <?php if($speakers_status): ?>
                            <div class="mec-col-12 mec-hourly-schedule-form-speakers" data-d=":d:" data-key=":i:" data-name-prefix="<?php echo esc_attr($name_prefix); ?>">
                                <strong><?php echo esc_html($this->main->m('taxonomy_speakers', esc_html__('Speakers', 'modern-events-calendar-lite'))); ?></strong>
                                <?php foreach($speakers as $speaker): ?>
                                <label><input type="checkbox" name="<?php echo esc_attr($name_prefix); ?>[hourly_schedules][:d:][schedules][:i:][speakers][]" value="<?php echo esc_attr($speaker->term_id); ?>"><?php echo esc_html($speaker->name); ?></label>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}