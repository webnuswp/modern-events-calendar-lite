<?php
/** no direct access **/
defined('MECEXEC') or die();

// MEC Settings
$settings = $this->main->get_settings();

// Return the data if called by AJAX
if(isset($this->atts['return_items']) and $this->atts['return_items'])
{
    echo json_encode(array('markers'=>$this->render->markers($this->events)));
    exit;
}

if(count($this->events))
{
    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets();

    $javascript = '<script type="text/javascript">
    jQuery(document).ready(function()
    {
        jQuery("#mec_googlemap_canvas'.$this->id.'").mecGoogleMaps(
        {
            id: "'.$this->id.'",
            atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
            zoom: '.(isset($settings['google_maps_zoomlevel']) ? $settings['google_maps_zoomlevel'] : 14).',
            icon: "'.apply_filters('mec_marker_icon', $this->main->asset('img/m-04.png')).'",
            styles: '.((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->main->get_googlemap_style($settings['google_maps_style']) : "''").',
            markers: '.json_encode($this->render->markers($this->events)).',
            HTML5geolocation: '.$this->geolocation.',
            clustering_images: "'.$this->main->asset('img/cluster1/m').'",
            getDirection: 0,
            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
            sf:
            {
                container: "'.($this->sf_status ? '#mec_search_form_'.$this->id : '').'",
            },
        });
    });
    </script>';

    // Include javascript code into the page
    if($this->main->is_ajax()) echo $javascript;
    else $this->factory->params('footer', $javascript);
}
do_action('mec_start_skin' , $this->id);
do_action('mec_map_skin_head');
?>
<div class="mec-wrap mec-skin-map-container <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
    
    <?php if($this->sf_status) echo $this->sf_search_form(); ?>
    
    <?php if(count($this->events)): ?>
    <div class="mec-googlemap-skin" id="mec_googlemap_canvas<?php echo $this->id; ?>" style="height: 500px;">
    </div>
    <?php else: ?>
    <p class="mec-error"><?php _e('No events found!', 'modern-events-calendar-lite'); ?></p>
    <?php endif; ?>
    
</div>