<?php
/** no direct access **/
defined('MECEXEC') or die();

// PRO Version is required
if(!$this->getPRO()) return;

// MEC Settings
$settings = $this->get_settings();

// Google Maps on single page is disabled
if(!isset($settings['google_maps_status']) or (isset($settings['google_maps_status']) and !$settings['google_maps_status'])) return;

$event = $event[0];
$uniqueid = (isset($uniqueid) ? $uniqueid : $event->data->ID);

// Map is disabled for this event
if(isset($event->data->meta['mec_dont_show_map']) and $event->data->meta['mec_dont_show_map']) return;

$location = isset($event->data->meta['mec_location_id']) && isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();

// Event location geo point
$latitude = isset($location['latitude']) ? $location['latitude'] : '';
$longitude = isset($location['longitude']) ? $location['longitude'] : '';
$address = isset($location['address']) ? $location['address'] : '';

// Try to get the latitude and longitude on the fly
if(!trim($latitude) or !trim($longitude))
{
    $geo_point = $this->get_lat_lng($address);

    $latitude = $geo_point[0];
    $longitude = $geo_point[1];

    if(isset($event->data->meta['mec_location_id']))
    {
        update_term_meta($event->data->meta['mec_location_id'], 'latitude', $latitude);
        update_term_meta($event->data->meta['mec_location_id'], 'longitude', $longitude);
    }
}

// Still Latitude and Longitude are wrong!
if(!trim($latitude) or !trim($longitude)) return;

// Include Map Assets such as JS and CSS libraries
if(!$this->is_ajax()) $this->load_map_assets();

// Get Direction Status
$get_direction = (isset($settings['google_maps_get_direction_status']) and in_array($settings['google_maps_get_direction_status'], array(0,1,2))) ? $settings['google_maps_get_direction_status'] : 0;

// Initialize MEC Google Maps jQuery plugin
$javascript = '<script type="text/javascript">
var p'.$uniqueid.';
jQuery(document).ready(function()
{
    p'.$uniqueid.' = jQuery("#mec_googlemap_canvas'.$uniqueid.'").mecGoogleMaps(
    {
        latitude: "'.$latitude.'",
        longitude: "'.$longitude.'",
        autoinit: '.((!isset($auto_init) or (isset($auto_init) and $auto_init)) ? 'true' : 'false').',
        zoom: '.(isset($settings['google_maps_zoomlevel']) ? $settings['google_maps_zoomlevel'] : 14).',
        icon: "'.apply_filters('mec_marker_icon', $this->asset('img/m-04.png')).'",
        styles: '.((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->get_googlemap_style($settings['google_maps_style']) : "''").',
        markers: '.json_encode($render->markers($this->get_rendered_events(array('post__in'=>array($event->ID))))).',
        getDirection: '.$get_direction.',
        directionOptions:
        {
            form: "#mec_get_direction_form'.$uniqueid.'",
            reset: "#mec_map_get_direction_reset'.$uniqueid.'",
            addr: "#mec_get_direction_addr'.$uniqueid.'",
            destination:
            {
                latitude: "'.$latitude.'",
                longitude: "'.$longitude.'",
            },
            startMarker: "'.apply_filters('mec_start_marker_icon', $this->asset('img/m-03.png')).'",
            endMarker: "'.apply_filters('mec_end_marker_icon', $this->asset('img/m-04.png')).'"
        }
    });
});

function mec_init_gmap'.$uniqueid.'()
{
    p'.$uniqueid.'.init();
}
</script>';
if ( !function_exists('is_plugin_active')) include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// Include javascript code into the footer
if($this->is_ajax()) echo $javascript;
elseif (is_plugin_active( 'mec-single-builder/mec-single-builder.php')) echo $javascript;
else $factory->params('footer', $javascript);
?>
<div class="mec-googlemap-details" id="mec_googlemap_canvas<?php echo $uniqueid; ?>" style="height: 500px;">
</div>
<?php if($get_direction): ?>
<div class="mec-get-direction">
    <form method="post" action="#" id="mec_get_direction_form<?php echo $uniqueid; ?>" class="clearfix">
        <div class="mec-map-get-direction-address-cnt">
            <input class="mec-map-get-direction-address" type="text" placeholder="<?php esc_attr_e('Address from ...', 'mec') ?>" id="mec_get_direction_addr<?php echo $uniqueid; ?>" />
            <span class="mec-map-get-direction-reset mec-util-hidden" id="mec_map_get_direction_reset<?php echo $uniqueid; ?>">X</span>
        </div>
        <div class="mec-map-get-direction-btn-cnt btn btn-primary">
            <input type="submit" value="<?php _e('Get Directions', 'mec'); ?>" />
        </div>
    </form>
</div>
<?php endif;