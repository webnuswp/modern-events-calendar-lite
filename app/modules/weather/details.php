<?php
/** no direct access **/
defined('MECEXEC') or die();

// PRO Version is required
if(!$this->getPRO()) return;

// MEC Settings
$settings = $this->get_settings();

// The module is disabled
if(!isset($settings['weather_module_status']) or (isset($settings['weather_module_status']) and !$settings['weather_module_status'])) return;

// API Key is empty!
if(!isset($settings['weather_module_api_key']) or (isset($settings['weather_module_api_key']) and !trim($settings['weather_module_api_key']))) return;

// Location is not Set
if(!isset($event->data->meta['mec_location_id']) or (isset($event->data->meta['mec_location_id']) and !$event->data->meta['mec_location_id'])) return;
$location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();

$lat = isset($location['latitude']) ? $location['latitude'] : 0;
$lng = isset($location['longitude']) ? $location['longitude'] : 0;

// Cannot find the geo point
if(!$lat or !$lng) return;

$occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';
$date = (trim($occurrence) ? $occurrence : $event->date['start']['date']).' '.sprintf("%02d", $event->date['start']['hour']).':'.sprintf("%02d", $event->date['start']['minutes']).' '.$event->date['start']['ampm'];

$weather = $this->get_weather($lat, $lng, $date);
$imperial = (isset($settings['weather_module_imperial_units']) and $settings['weather_module_imperial_units']) ? true : false;

?>
<div class="mec-weather-details mec-frontbox" id="mec_weather_details">
    <h3 class="mec-weather mec-frontbox-title"><?php _e('Weather', 'mec'); ?></h3>

    <!-- mec weather start -->
    <div class="mec-weather-box">

        <div class="mec-weather-head">
            <div class="mec-weather-icon-box">
                <?php  $url= str_replace('//', '', $weather['condition']['icon']); $icon = 'style="background-size: 100%; background-image: url(http://'.$url.')"'; ?>
                <span class="mec-weather-icon" <?php echo $icon ?>></span>
            </div>
            <div class="mec-weather-summary">

                <?php if(isset($weather['condition']['text'])): ?>
                <div class="mec-weather-summary-report"><?php echo $weather['condition']['text']; ?></div>
                <?php endif; ?>

                <?php if(isset($weather['temp_c'])): ?>
                    <div class="mec-weather-summary-temp" data-c="<?php _e( ' °C', 'mec' ); ?>" data-f="<?php _e( ' °F', 'mec' ); ?>">
                        <?php if(!$imperial): echo round($weather['temp_c']); ?>
                            <var><?php _e(' °C', 'mec'); ?></var>
                        <?php else: echo $this->weather_unit_convert($weather['temp_c'], 'C_TO_F'); ?>
                            <var><?php _e(' °F', 'mec'); ?></var>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                

            </div>
            
            <?php if(isset($settings['weather_module_change_units_button']) and $settings['weather_module_change_units_button']): ?>
            <span data-imperial="<?php _e('°Imperial', 'mec'); ?>" data-metric="<?php _e('°Metric', 'mec'); ?>" class="degrees-mode"><?php if(!$imperial) _e('°Imperial', 'mec'); else _e('°Metric', 'mec'); ?></span>
            <?php endif ?>
            
            <div class="mec-weather-extras">

                <?php if(isset($weather['wind_kph'])): ?>
                <div class="mec-weather-wind" data-kph="<?php _e(' KPH', 'mec'); ?>" data-mph="<?php _e(' MPH', 'mec'); ?>"><span><?php _e('Wind', 'mec'); ?>: </span><?php if(!$imperial) echo round($weather['wind_kph']); else  echo $this->weather_unit_convert($weather['wind_kph'], 'KM_TO_M');?><var><?php if(!$imperial) _e(' KPH', 'mec'); else _e(' MPH', 'mec'); ?></var></div>
                <?php endif; ?>

                <?php if(isset($weather['humidity'])): ?>
                    <div class="mec-weather-humidity"><span><?php _e('Humidity', 'mec'); ?>:</span> <?php echo round($weather['humidity']); ?><var><?php _e(' %','mec'); ?></var></div>
                <?php endif; ?>

                <?php if(isset($weather['vis_km'])): ?>
                    <div class="mec-weather-visibility" data-kph="<?php _e(' KM', 'mec'); ?>" data-mph="<?php _e(' Miles', 'mec'); ?>"><span><?php _e('Visibility', 'mec'); ?>: </span><?php if(!$imperial) echo round($weather['vis_km']); else  echo $this->weather_unit_convert($weather['vis_km'], 'KM_TO_M');?><var><?php if(!$imperial) _e(' KM','mec'); else _e(' Miles','mec'); ?></var></div>
                <?php endif; ?>
        
            </div>
        </div>

    </div><!--  mec weather end -->

</div>