<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event = $this->events[0];
$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$dark_mode = (isset($styling['dark_mode']) ? $styling['dark_mode'] : '');
if($dark_mode == 1) $set_dark = 'mec-dark-mode';
else $set_dark = '';

// Event is not valid!
if(!isset($event->data)) return;

$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';

$location_id = $this->main->get_master_location_id($event);
$event_location = ($location_id ? $this->main->get_location_data($location_id) : array());

$organizer_id = $this->main->get_master_organizer_id($event);
$event_organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

$event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);
$event_link = (isset($event->data->permalink) and trim($event->data->permalink)) ? $this->main->get_event_date_permalink($event, $event_date) : get_permalink($event->data->ID);
$event_color = $this->get_event_color_dot($event);
$event_thumb = $event->data->thumbnails['large'];
$event_thumb_url = $event->data->featured_image['large'];
$event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

do_action('mec_start_skin', $this->id);
do_action('mec_cover_skin_head');
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin . ' ' . $this->html_class . ' ' . $set_dark); ?>">
<?php
    // MEC Schema
    do_action('mec_schema', $event);

    if($this->style == 'modern' and $event_thumb_url): ?>
    <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-cover-modern <?php echo esc_attr($this->get_event_classes($event)); ?>" style="background: url('<?php echo esc_url($event_thumb_url); ?>'); height: 678px;background-size: cover;">
        <a href="<?php echo esc_url($event_link); ?>" class="mec-event-cover-a">
            <div class="mec-event-overlay mec-bg-color"></div>
            <div class="mec-event-detail">
                <?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?>
                <div class="mec-event-date"><?php echo esc_html($this->main->date_i18n($this->date_format_modern1, strtotime($event_date))).((isset($event->data->time) and trim($event->data->time['start'])) ? ' - '.esc_html($event->data->time['start']) : ''); ?></div>
                <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                <h4 class="mec-event-title"><?php echo MEC_kses::element($event->data->title.$this->main->get_flags($event).$event_color); ?></h4>
                <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?>
                <div class="mec-event-place"><?php echo (isset($event_location['name']) ? esc_html($event_location['name']) : ''); ?></div>
            </div>
        </a>
    </article>
    <?php elseif($event_thumb): ?>
    <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-cover-<?php echo esc_attr($this->style); ?> <?php echo esc_attr($this->get_event_classes($event)); ?>">
        <div class="mec-event-image"><?php echo MEC_kses::element($event_thumb); ?></div>
        <div class="mec-event-overlay"></div>
        <?php if($this->style == 'classic'): ?>
        <div class="mec-event-content">
            <i class="mec-event-icon mec-bg-color mec-fa-calendar"></i>
            <div class="mec-event-date">
                <span class="mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_classic1, strtotime($event_date))); ?></span> <?php echo esc_html($this->main->date_i18n($this->date_format_classic2, strtotime($event_date))); ?>
            </div>
            <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
            <h4 class="mec-event-title"><?php echo MEC_kses::element($event->data->title.$this->main->get_flags($event).$event_color); ?><?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?><?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?></h4>
            <div class="mec-btn-wrapper"><?php echo MEC_kses::element($this->display_link($event, $this->main->m('event_detail', esc_html__('EVENT DETAIL', 'modern-events-calendar-lite')), 'mec-event-button')); ?></div>
        </div>
        <?php elseif($this->style == 'clean'): ?>
        <div class="mec-event-content">
            <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event, NULL, '')); ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color); ?><?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?><?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?></h4>
            <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
            <?php if(isset($event_organizer['name'])): ?><div class="mec-event-place"><?php echo (isset($event_organizer['name']) ? esc_html($event_organizer['name']) : ''); ?></div><?php endif; ?>
        </div>
        <div class="mec-event-date mec-bg-color">
            <div class="dday"><?php echo esc_html($this->main->date_i18n($this->date_format_clean1, strtotime($event_date))); ?></div>
            <div class="dmonth"><?php echo esc_html($this->main->date_i18n($this->date_format_clean2, strtotime($event_date))); ?></div>
            <div class="dyear"><?php echo esc_html($this->main->date_i18n($this->date_format_clean3, strtotime($event_date))); ?></div>
        </div>
        <?php endif; ?>
    </article>
    <?php endif; ?>
    <?php echo $this->display_credit_url(); ?>
</div>