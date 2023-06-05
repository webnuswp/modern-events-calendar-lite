<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Captcha class.
 * @author Webnus <info@webnus.net>
 */
class MEC_captcha extends MEC_base
{
    const GOOGLE = 'grecaptcha';
    const MTCAPTCHA = 'mtcaptcha';

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var array
     */
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // MEC Main library
        $this->main = $this->getMain();

        // MEC settings
        $this->settings = $this->main->get_settings();
    }

    public function assets()
    {
        // Captcha Client
        $client = $this->client();

        // Captcha is disabled
        if(!$client) return false;

        // Current locale
        $locale = $this->main->get_current_language();

        // Google Recaptcha
        if($client === self::GOOGLE)
        {
            // Include Google Recaptcha Javascript API
            if(apply_filters('mec_grecaptcha_include', true)) wp_enqueue_script('recaptcha', '//www.google.com/recaptcha/api.js?hl='.str_replace('_', '-', $locale), array(), $this->main->get_version(), true);
        }
        elseif($client === self::MTCAPTCHA)
        {
            $ex = explode('_', $locale);
            $lang_code = $ex[0];

            add_action('wp_head', function() use ($lang_code)
            {
                ?>
                <script>
                    var mtcaptchaConfig = {
                        "sitekey": "<?php echo $this->settings['mtcaptcha_sitekey']; ?>",
                        "lang": "<?php echo $lang_code; ?>",
                    };
                    (function(){var mt_service = document.createElement('script');mt_service.async = true;mt_service.src = 'https://service.mtcaptcha.com/mtcv1/client/mtcaptcha.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_service);
                        var mt_service2 = document.createElement('script');mt_service2.async = true;mt_service2.src = 'https://service2.mtcaptcha.com/mtcv1/client/mtcaptcha2.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_service2);}) ();
                </script>
                <?php
            });
        }

        return true;
    }

    public function field()
    {
        // Captcha Client
        $client = $this->client();

        // Captcha is disabled
        if(!$client) return '';

        // Google Recaptcha
        if($client === self::GOOGLE)
        {
            return '<div class="mec-form-row mec-google-recaptcha">
                <div class="g-recaptcha" data-sitekey="'.esc_attr($this->settings['google_recaptcha_sitekey']).'"></div>
            </div>';
        }
        elseif($client === self::MTCAPTCHA)
        {
            return '<div class="mec-form-row mec-mtcaptcha">
                <div class="mtcaptcha"></div>
            </div>';
        }

        return '';
    }

    public function is_valid()
    {
        // Captcha Client
        $client = $this->client();

        // Captcha is disabled
        if(!$client) return false;

        // Google Recaptcha
        if($client === self::GOOGLE)
        {
            $token = isset($_REQUEST['g-recaptcha-response']) ? sanitize_text_field($_REQUEST['g-recaptcha-response']) : NULL;

            $req = "";
            foreach([
                'secret' => (isset($this->settings['google_recaptcha_secretkey']) ? $this->settings['google_recaptcha_secretkey'] : ''),
                'remoteip' => $this->main->get_client_ip(),
                'v' => 'php_1.0',
                'response' => $token
            ] as $key=>$value) $req .= $key.'='.urlencode(stripslashes($value)).'&';

            // Validate the re-captcha
            $response = $this->main->get_web_page("https://www.google.com/recaptcha/api/siteverify?".trim($req, '& '));
            $answers = json_decode($response, true);

            if(isset($answers['success']) and trim($answers['success'])) return true;
            else return false;
        }
        elseif($client === self::MTCAPTCHA)
        {
            $token = isset($_REQUEST['mtcaptcha-verifiedtoken']) ? sanitize_text_field($_REQUEST['mtcaptcha-verifiedtoken']) : NULL;

            // Validate the re-captcha
            $response = $this->main->get_web_page("https://service.mtcaptcha.com/mtcv1/api/checktoken?privatekey=".urlencode(stripslashes($this->settings['mtcaptcha_privatekey']))."&token=".urlencode(stripslashes($token)));
            $answers = json_decode($response, true);

            if(isset($answers['success']) and $answers['success']) return true;
            else return false;
        }

        return false;
    }

    /**
     * @param string $section
     * @return bool
     */
    public function status($section = '')
    {
        // Captcha Client
        $client = $this->client();

        // Captcha is disabled
        if(!$client) return false;

        // Google Recaptcha
        if($client === self::GOOGLE)
        {
            // Check if the feature is enabled for certain section
            if(trim($section) and (!isset($this->settings['google_recaptcha_'.$section]) or (isset($this->settings['google_recaptcha_'.$section]) and !$this->settings['google_recaptcha_'.$section]))) return false;

            // Check if site key and secret key is not empty
            if(!isset($this->settings['google_recaptcha_sitekey']) or (isset($this->settings['google_recaptcha_sitekey']) and trim($this->settings['google_recaptcha_sitekey']) === '')) return false;
            if(!isset($this->settings['google_recaptcha_secretkey']) or (isset($this->settings['google_recaptcha_secretkey']) and trim($this->settings['google_recaptcha_secretkey']) === '')) return false;
        }
        elseif($client === self::MTCAPTCHA)
        {
            // Check if the feature is enabled for certain section
            if(trim($section) and (!isset($this->settings['mtcaptcha_'.$section]) or (isset($this->settings['mtcaptcha_'.$section]) and !$this->settings['mtcaptcha_'.$section]))) return false;

            // Check if site key and secret key is not empty
            if(!isset($this->settings['mtcaptcha_sitekey']) or (isset($this->settings['mtcaptcha_sitekey']) and trim($this->settings['mtcaptcha_sitekey']) === '')) return false;
            if(!isset($this->settings['mtcaptcha_privatekey']) or (isset($this->settings['mtcaptcha_privatekey']) and trim($this->settings['mtcaptcha_privatekey']) === '')) return false;
        }

        return true;
    }

    public function client()
    {
        // Google Recaptcha
        if(isset($this->settings['google_recaptcha_status']) and $this->settings['google_recaptcha_status']) return self::GOOGLE;
        // MTCaptcha
        elseif(isset($this->settings['mtcaptcha_status']) and $this->settings['mtcaptcha_status']) return self::MTCAPTCHA;

        return false;
    }
}