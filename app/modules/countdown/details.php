<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_main $this */
/** @var MEC_factory $factory */

// MEC Settings
$settings = $this->get_settings();

// Countdown on single page is disabled
if(!isset($settings['countdown_status']) or (isset($settings['countdown_status']) and !$settings['countdown_status'])) return;

$event = $event[0];
$date = $event->date;

$start_date = (isset($date['start']) and isset($date['start']['date'])) ? $date['start']['date'] : current_time('Y-m-d');
$end_date = (isset($date['end']) and isset($date['end']['date'])) ? $date['end']['date'] : current_time('Y-m-d');

$s_time = '';
if(!empty($date))
{
    $s_hour = $date['start']['hour'];
    if(strtoupper($date['start']['ampm']) == 'AM' and $s_hour == '0') $s_hour = 12;

    $s_time .= sprintf("%02d", $s_hour).':';
    $s_time .= sprintf("%02d", $date['start']['minutes']);
    $s_time .= ' '.trim($date['start']['ampm']);
}

$e_time = '';
if(!empty($date))
{
    $e_hour = $date['end']['hour'];
    if(strtoupper($date['end']['ampm']) == 'AM' and $e_hour == '0') $e_hour = 12;

    $e_time .= sprintf("%02d", $e_hour).':';
    $e_time .= sprintf("%02d", $date['end']['minutes']);
    $e_time .= ' '.trim($date['end']['ampm']);
}

$start_time = date('D M j Y G:i:s', strtotime($start_date.' '.$s_time));
$end_time = date('D M j Y G:i:s', strtotime($end_date.' '.$e_time));

// Timezone
$TZO = $this->get_TZO($event);

$starttime = new DateTime($start_time, $TZO);
$nowtime   = new DateTime('now', $TZO);
$endtime   = new DateTime($end_time, $TZO);

if($endtime < $nowtime)
{
    echo '<div class="mec-end-counts"><h3>'.esc_html__('This event has passed', 'modern-events-calendar-lite').'</h3></div>';
    return;
}

$countdown_method = get_post_meta($event->ID, 'mec_countdown_method', true);
if(trim($countdown_method) == '') 
	$countdown_method = 'global';
	
$ongoing = ($starttime < $nowtime);
$disable_for_ongoing = (isset($settings['countdown_disable_for_ongoing_events']) and $settings['countdown_disable_for_ongoing_events']);

if ( ! $ongoing )
	$cd2 = "starts in";
else
{
    if ($disable_for_ongoing or ($countdown_method == 'start'))
    {
        echo '<div class="mec-end-counts"><h3>'
            . esc_html__('going on NOW!', 'modern-events-calendar-lite')
            . '</h3></div>';
        return;
    }
    $cd2 = "ends in";
}

$gmt_offset = $this->get_gmt_offset($event, strtotime($start_date.' '.$s_time));
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') === false) $gmt_offset = ' : '.$gmt_offset;
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') == true) $gmt_offset = substr(trim($gmt_offset), 0 , 3);
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') == true) $gmt_offset = substr(trim($gmt_offset), 2 , 3);

$datetime = $ongoing ? $end_time : $start_time;
$countdown_interval = 30000;  // setting tbd

// Generating javascript code of countdown default module
$defaultjs = '<script>
jQuery(document).ready(function($)
{
    jQuery.each(jQuery(".mec-countdown-details"),function(i,el)
    {
        var datetime = jQuery(el).data("datetime");
        var gmt_offset = jQuery(el).data("gmt_offset");
        var countdown_interval = jQuery(el).data("countdown_interval");
		jQuery(el).mecCountDown(
            {
                date: datetime+""+gmt_offset,
                format: "off",
                interval: countdown_interval 
            },
            function(){}
        );
    });
});
</script>';

// Generating javascript code of countdown flip module
$flipjs = '<script>
var clock;
jQuery(document).ready(function()
{
    var futureDate = new Date("'.($datetime).$gmt_offset.'");
    var currentDate = new Date();
    var diff = parseInt((futureDate.getTime() / 1000 - currentDate.getTime() / 1000));

    function dayDiff(first, second)
    {
        return (second-first)/(1000*3600*24);
    }

    if(dayDiff(currentDate, futureDate) < 100) jQuery(".clock").addClass("twodaydigits");
    else jQuery(".clock").addClass("threedaydigits");

    if(diff < 0)
    {
        diff = 0;
        jQuery(".countdown-message").html();
    }

    clock = jQuery(".clock").FlipClock(diff,
    {
        clockFace: "DailyCounter",
        countdown: true,
        autoStart: true,
            callbacks: {
            stop: function() {
                jQuery(".countdown-message").html()
            }
        }
    });

    jQuery(".mec-wrap .flip-clock-wrapper ul li, a .shadow, a .inn").on("click", function(event)
    {
        event.preventDefault();
    });
});
</script>';
$flipjsDivi = '<script>
var clock;
jQuery(document).ready(function()
{
    var futureDate = new Date("'.($datetime).$gmt_offset.'");
    var currentDate = new Date();
    var diff = parseInt((futureDate.getTime() / 1000 - currentDate.getTime() / 1000));

    function dayDiff(first, second)
    {
        return (second-first)/(1000*3600*24);
    }

    if(dayDiff(currentDate, futureDate) < 100) jQuery(".clock").addClass("twodaydigits");
    else jQuery(".clock").addClass("threedaydigits");

    if(diff < 0)
    {
        diff = 0;
        jQuery(".countdown-message").html();
    }

    clock = $(".clock").FlipClock(diff,
    {
        clockFace: "DailyCounter",
        countdown: true,
        autoStart: true,
            callbacks: {
            stop: function() {
                jQuery(".countdown-message").html()
            }
        }
    });

    jQuery(".mec-wrap .flip-clock-wrapper ul li, a .shadow, a .inn").on("click", function(event)
    {
        event.preventDefault();
    });
});
</script>';
if(!function_exists('is_plugin_active')) include_once( ABSPATH . 'wp-admin/includes/plugin.php');
?>
<?php if(!isset($settings['countdown_list']) or (isset($settings['countdown_list']) and $settings['countdown_list'] === 'default')): ?>
<?php
    if($this->is_ajax()) echo MEC_kses::full($defaultjs);
    elseif (is_plugin_active( 'mec-single-builder/mec-single-builder.php')) echo MEC_kses::full($defaultjs);
    else $factory->params('footer', $defaultjs);
?>
<div class="mec-countdown-details" id="mec_countdown_details" 
			data-datetime="<?php echo esc_attr($datetime); ?>" 
			data-gmt_offset="<?php echo esc_attr($gmt_offset); ?>" 
			data-countdown_interval ="<?php echo $countdown_interval; ?>" >
	<?php  echo $cd2; ?>

    <div class="countdown-w ctd-simple">
        <ul class="clockdiv" id="countdown">
            <li class="days-w block-w">
                <i class="icon-w mec-li_calendar"></i>
                <span class="mec-days">00</span>
                <p class="mec-timeRefDays label-w"><?php esc_html_e('days', 'modern-events-calendar-lite'); ?></p>
            </li>
            <li class="hours-w block-w">
                <i class="icon-w mec-fa-clock-o"></i>
                <span class="mec-hours">00</span>
                <p class="mec-timeRefHours label-w"><?php esc_html_e('hours', 'modern-events-calendar-lite'); ?></p>
            </li>
            <li class="minutes-w block-w">
                <i class="icon-w mec-li_clock"></i>
                <span class="mec-minutes">00</span>
                <p class="mec-timeRefMinutes label-w"><?php esc_html_e('minutes', 'modern-events-calendar-lite'); ?></p>
            </li>
            <li class="seconds-w block-w">
                <i class="icon-w mec-li_heart"></i>
                <span class="mec-seconds">00</span>
                <p class="mec-timeRefSeconds label-w"><?php esc_html_e('seconds', 'modern-events-calendar-lite'); ?></p>
            </li>
        </ul>
    </div>
</div>
<?php elseif(isset($settings['countdown_list']) and $settings['countdown_list'] === 'flip'): ?>
<?php
    if($this->is_ajax()) echo MEC_kses::full($flipjs);
    elseif(is_plugin_active('mec-single-builder/mec-single-builder.php'))
    {
        wp_enqueue_script('mec-flipcount-script', $this->asset('js/flipcount.js'));
        echo MEC_kses::full($flipjs);
    }
    elseif(is_plugin_active( 'divi-single-builder/divi-single-builder.php') || is_plugin_active( 'mec-divi-single-builder/divi-single-builder.php'))
    {
        wp_enqueue_script('mec-flipcount-script', $this->asset('js/flipcount-divi.js'));
        $factory->params('footer', $flipjsDivi);
    }
    else
    {
        // Include FlipCount library
        wp_enqueue_script('mec-flipcount-script', $this->asset('js/flipcount.js'));

        // Include the JS code
        $factory->params('footer', $flipjs);
    }
?>
<div class="clock"></div>
<div class="countdown-message"></div>
<?php endif;