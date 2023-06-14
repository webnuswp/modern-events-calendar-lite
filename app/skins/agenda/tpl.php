<?php
/** no direct access **/
defined('MECEXEC') or die();

$current_month_divider = isset($_REQUEST['current_month_divider']) ? sanitize_text_field($_REQUEST['current_month_divider']) : 0;

// Get layout path
$render_path = $this->get_render_path();

ob_start();
include $render_path;
$items_html = ob_get_clean();

if(isset($this->atts['return_items']) and $this->atts['return_items'])
{
    echo json_encode(array('html' => $items_html, 'end_date' => $this->end_date, 'offset' => $this->next_offset, 'count' => $this->found, 'current_month_divider' => $current_month_divider, 'has_more_event' => (int) $this->has_more_events));
    exit;
}

$sed_method = $this->sed_method;
if($sed_method == 'new') $sed_method = '0';

// Generating javascript code tpl
$javascript = '<script>
jQuery(document).ready(function()
{
    var mec_interval = setInterval(function()
    {
        // Not Visible
        if(!jQuery("#mec_skin_'.esc_js($this->id).'").is(":visible")) return;
        
        jQuery("#mec_skin_'.esc_js($this->id).'").mecAgendaView(
        {
            id: "'.esc_js($this->id).'",
            start_date: "'.esc_js($this->start_date).'",
            end_date: "'.esc_js($this->end_date).'",
            offset: "'.esc_js($this->next_offset).'",
            limit: "'.esc_js($this->limit).'",
            pagination: "'.$this->pagination.'",
            current_month_divider: "'.esc_js($current_month_divider).'",
            atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
            ajax_url: "'.esc_js(admin_url('admin-ajax.php', NULL)).'",
            sed_method: "'.esc_js($sed_method).'",
            image_popup: "'.esc_js($this->image_popup).'",
            sf:
            {
                container: "'.esc_js(($this->sf_status ? '#mec_search_form_'.$this->id : '')).'",
                reset: '.($this->sf_reset_button ? 1 : 0).',
                refine: '.($this->sf_refine ? 1 : 0).',
            },
        });
        
        clearInterval(mec_interval);
    }, 500);
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';

$dark_mode = (isset($styling['dark_mode']) ? $styling['dark_mode'] : '');
if($dark_mode == 1) $set_dark = 'mec-dark-mode';
else $set_dark = '';

do_action('mec_start_skin', $this->id);
do_action('mec_agenda_skin_head');
?>
<div class="mec-wrap mec-events-agenda-container <?php echo esc_attr($this->html_class . ' ' . $set_dark); ?>" id="mec_skin_<?php echo esc_attr($this->id); ?>">

    <?php if($this->sf_status) echo MEC_kses::full($this->sf_search_form()); ?>

    <?php if($this->found): ?>
    <div class="mec-skin-agenda-events-container" id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <div class="mec-wrap mec-events-agenda-wrap <?php echo esc_attr($event_colorskin); ?>">
            <div class="mec-events-agenda-container mec-event-agenda-<?php echo esc_attr($this->style); ?>">
                <?php echo MEC_kses::full($items_html); ?>
            </div>
        </div>
    </div>
    <div class="mec-skin-agenda-no-events-container mec-util-hidden" id="mec_skin_no_events_<?php echo esc_attr($this->id); ?>">
        <?php esc_html_e('No event found!', 'modern-events-calendar-lite'); ?>
    </div>
    <?php else: ?>
    <div class="mec-skin-agenda-events-container" id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <?php esc_html_e('No event found!', 'modern-events-calendar-lite'); ?>
    </div>
    <?php endif; ?>

    <?php echo $this->get_pagination_bar(); ?>

</div>
<?php echo $this->display_credit_url();