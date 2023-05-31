<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_main $this */

$occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';
$date = (trim($occurrence) ? $occurrence : $event->date['start']['date']).' '.sprintf("%02d", $event->date['start']['hour']).':'.sprintf("%02d", $event->date['start']['minutes']).' '.$event->date['start']['ampm'];

$weather = $this->get_weather_visualcrossing($visualcrossing, $lat, $lng, $date);
$imperial = (isset($settings['weather_module_imperial_units']) and $settings['weather_module_imperial_units']) ? true : false;

// Weather not found!
if(!is_array($weather) or (is_array($weather) and !count($weather))) return;
?>
<div class="mec-weather-details mec-frontbox" id="mec_weather_details">
    <h3 class="mec-weather mec-frontbox-title"><?php esc_html_e('Weather', 'modern-events-calendar-lite'); ?></h3>

    <!-- mec weather start -->
    <div class="mec-weather-box">

        <div class="mec-weather-head">
            <div class="mec-weather-icon-box">
                <span class="mec-weather-icon <?php echo esc_attr($weather['icon']); ?>"></span>
            </div>
            <div class="mec-weather-summary">

                <?php if(isset($weather['conditions'])): ?>
                <div class="mec-weather-summary-report"><?php echo esc_html($weather['conditions']); ?></div>
                <?php endif; ?>

                <?php if(isset($weather['temp'])): ?>
                    <div class="mec-weather-summary-temp" data-c="<?php esc_html_e( ' °C', 'modern-events-calendar-lite'); ?>" data-f="<?php esc_html_e( ' °F', 'modern-events-calendar-lite'); ?>">
                    <?php if(!$imperial): echo round($weather['temp']); ?>
                    <var><?php esc_html_e(' °C', 'modern-events-calendar-lite'); ?></var>
                    <?php else: echo esc_html($this->weather_unit_convert($weather['temp'], 'C_TO_F')); ?>
                    <var><?php esc_html_e(' °F', 'modern-events-calendar-lite'); ?></var>
                    <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
            
            <?php if(isset($settings['weather_module_change_units_button']) and $settings['weather_module_change_units_button']): ?>
            <span data-imperial="<?php esc_html_e('°Imperial', 'modern-events-calendar-lite'); ?>" data-metric="<?php esc_html_e('°Metric', 'modern-events-calendar-lite'); ?>" class="degrees-mode"><?php if(!$imperial) esc_html_e('°Imperial', 'modern-events-calendar-lite'); else esc_html_e('°Metric', 'modern-events-calendar-lite'); ?></span>
            <?php endif ?>
            
            <div class="mec-weather-extras">

                <?php if(isset($weather['windspeed'])): ?>
                <div class="mec-weather-wind" data-kph="<?php esc_html_e(' KPH', 'modern-events-calendar-lite'); ?>" data-mph="<?php esc_html_e(' MPH', 'modern-events-calendar-lite'); ?>"><span><?php esc_html_e('Wind', 'modern-events-calendar-lite'); ?>: </span><?php if(!$imperial) echo round($weather['windspeed']); else echo esc_html($this->weather_unit_convert($weather['windspeed'], 'KM_TO_M')); ?><var><?php if(!$imperial) esc_html_e(' KPH', 'modern-events-calendar-lite'); else esc_html_e(' MPH', 'modern-events-calendar-lite'); ?></var></div>
                <?php endif; ?>

                <?php if(isset($weather['humidity'])): ?>
                    <div class="mec-weather-humidity"><span><?php esc_html_e('Humidity', 'modern-events-calendar-lite'); ?>:</span> <?php echo round($weather['humidity']); ?><var><?php esc_html_e(' %', 'modern-events-calendar-lite'); ?></var></div>
                <?php endif; ?>

                <?php if(isset($weather['visibility'])): ?>
                    <div class="mec-weather-visibility" data-kph="<?php esc_html_e(' KM', 'modern-events-calendar-lite'); ?>" data-mph="<?php esc_html_e(' Miles', 'modern-events-calendar-lite'); ?>"><span><?php esc_html_e('Visibility', 'modern-events-calendar-lite'); ?>: </span><?php if(!$imperial) echo round($weather['visibility']); else echo esc_html($this->weather_unit_convert($weather['visibility'], 'KM_TO_M')); ?><var><?php if(!$imperial) esc_html_e(' KM', 'modern-events-calendar-lite'); else esc_html_e(' Miles', 'modern-events-calendar-lite'); ?></var></div>
                <?php endif; ?>
        
            </div>
        </div>

    </div><!--  mec weather end -->

</div>