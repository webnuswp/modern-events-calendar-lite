<?php
/** no direct access **/
defined('MECEXEC') or die();

// Inclue OWL Assets
$this->main->load_owl_assets();

$sed_method = $this->sed_method;
if($sed_method == 'new') $sed_method = '0';

// Generating javascript code tpl
$javascript = '<script>
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.esc_js($this->id).'").mecFullCalendar(
    {
        id: "'.esc_js($this->id).'",
        atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        sed_method: "'.esc_js($sed_method).'",
        image_popup: "'.esc_js($this->image_popup).'",
        sf:
        {
            container: "'.($this->sf_status ? '#mec_search_form_'.esc_js($this->id) : '').'",
            reset: '.($this->sf_reset_button ? 1 : 0).',
            refine: '.($this->sf_refine ? 1 : 0).',
        },
        skin: "'.esc_js($this->default_view).'",
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';

$dark_mode = (isset($styling['dark_mode']) ? $styling['dark_mode'] : '');
if($dark_mode == 1) $set_dark = 'mec-dark-mode';
else $set_dark = '';

do_action('mec_start_skin', $this->id);
do_action('mec_full_skin_head');
?>
<div id="mec_skin_<?php echo esc_attr($this->id); ?>" class="mec-wrap <?php echo esc_attr($event_colorskin . ' ' . $set_dark); ?> mec-full-calendar-wrap">

    <div class="mec-search-form mec-totalcal-box">
        <?php
        if($this->sf_status): ?>
        <?php
            $sf_month_filter = array("type"=> "dropdown");
            $sf_text_search = array( "type"=> "text_input" );

            $sf_month_filter_status = true;
            $sf_text_search_status = true;

            $sf_columns = 7;
        ?>
        <div id="mec_search_form_<?php echo esc_attr($this->id); ?>">
        <?php if($sf_month_filter_status): $sf_columns -= 3; ?>
            <div class="col-md-3">
                <?php echo MEC_kses::form($this->sf_search_field('month_filter', $sf_month_filter , 0)); ?>
            </div>
        <?php endif; ?>
            <div class="col-md-<?php echo esc_attr($sf_columns); ?>">
                <?php if($sf_text_search_status): ?>
                    <?php echo MEC_kses::form($this->sf_search_field('text_search', $sf_text_search , 0)); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
        <div class="col-md-5">
            <div class="mec-totalcal-view">
                <?php if($this->yearly): ?><span class="mec-totalcal-yearlyview<?php if($this->default_view == 'yearly') echo ' mec-totalcalview-selected'; ?>" data-skin="yearly"><?php esc_html_e('Yearly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                <?php if($this->monthly): ?><span class="mec-totalcal-monthlyview<?php if($this->default_view == 'monthly') echo ' mec-totalcalview-selected'; ?>" data-skin="monthly"><?php esc_html_e('Monthly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                <?php if($this->weekly): ?><span class="mec-totalcal-weeklyview<?php if($this->default_view == 'weekly') echo ' mec-totalcalview-selected'; ?>" data-skin="weekly"><?php esc_html_e('Weekly', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                <?php if($this->daily): ?><span class="mec-totalcal-dailyview<?php if($this->default_view == 'daily') echo ' mec-totalcalview-selected'; ?>" data-skin="daily"><?php esc_html_e('Daily', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
                <?php if($this->list): ?><span class="mec-totalcal-listview<?php if($this->default_view == 'list') echo ' mec-totalcalview-selected'; ?>" data-skin="list"><?php esc_html_e('List', 'modern-events-calendar-lite'); ?></span><?php endif; ?>
            </div>
        </div>
    </div>

    <div id="mec_full_calendar_container_<?php echo esc_attr($this->id); ?>" class="mec-full-calendar-skin-container">
        <?php echo MEC_kses::full($this->load_skin($this->default_view)); ?>
    </div>

</div>
<style type="text/css">
#mec_skin_<?php echo esc_attr($this->id); ?> .mec-search-form .mec-date-search{width: 100%;}
</style>