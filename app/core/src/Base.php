<?php

namespace MEC;

use MEC\Libraries\FlushNotices;
use MEC\Attendees\AttendeesTable;

/**
 * Core Class in Plugin
 */
final class Base {

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	public static $version = '1.0.0';

	/**
	 * Session instance
	 *
	 * @var bool
	 */
	protected static $instance;

	/**
	 * MEC Constructor
	 */
	public function __construct() {

		$this->define();
		$this->includes();
		$this->init_hooks();
		$this->admin();
		$this->enqueue_scripts();
	}

	/**
	 * MEC Instance
	 *
	 * @return self()
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set Constants
	 *
	 * @return void
	 */
	public function define() {

		define( 'MEC_CORE_PD', plugin_dir_path( MEC_CORE_FILE ) );
		define( 'MEC_CORE_PDI', plugin_dir_path( MEC_CORE_FILE ) . 'src/' );
		define( 'MEC_CORE_PU_JS', plugins_url( 'assets/js/', MEC_CORE_FILE ) );
		define( 'MEC_CORE_PU_CSS', plugins_url( 'assets/css/', MEC_CORE_FILE ) );
		define( 'MEC_CORE_PU_IMG', plugins_url( 'assets/img/', MEC_CORE_FILE ) );
		define( 'MEC_CORE_PU_FONTS', plugins_url( 'assets/fonts/', MEC_CORE_FILE ) );
		define( 'MEC_CORE_TEMPLATES', plugin_dir_path( MEC_CORE_FILE ) . 'templates/' );
	}

	/**
	 * Include Files
	 *
	 * @return void
	 */
	public function includes() {

	}


	/**
	 * Include Files If is Admin
	 *
	 * @return void
	 */
	public function admin() {

		if ( !is_admin() ) {
			return;
		}

		FlushNotices::getInstance()->init();
	}


	/**
	 * Register actions enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {


	}

	/**
	 * Add Hooks - Actions and Filters
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'admin_notices', array( $this, 'upgrade_notice') );
		add_action('wp_ajax_mec-upgrade-transactions-in-db', array( __CLASS__, 'upgrade_transactions_db_by_ajax' ) );

		add_action( 'init', [ $this, 'init' ] );

		register_activation_hook( MEC_CORE_FILE, __CLASS__ . '::register_activation' );
		$db_version = get_option('mec_core_db','1.0.0');
		if(version_compare($db_version, '6.10.0', '<')){

			static::register_activation();
		}
	}

	/**
	 * Active Plugin
	 *
	 * @return void
	 */
	public static function register_activation() {

		AttendeesTable::create_table();

		update_option('mec_core_db',MEC_VERSION);
	}


	/**
	 * Init MEC after WordPress
	 *
	 * @return void
	 */
	public function init() {

	}

	public static function should_include_assets(){

        $factory = \MEC::getInstance('app.libraries.factory');

        return $factory->should_include_assets('frontend');
    }

	public static function is_include_assets_in_footer(){

        return '1' == \MEC\Settings\Settings::getInstance()->get_settings('assets_in_footer_status') ? true : false;
    }

	public static function get_main(){

		global $MEC_Main;
		if(is_null($MEC_Main)){

			$MEC_Main = new \MEC_main();
		}

		return $MEC_Main;
	}

	/**
	 * Upgrade transactions in db
	 *
	 * @return void
	 */
	public static function upgrade_transactions() {

		$db_version = get_option( 'mec_transaction_version', '1.0.0' );
		if( version_compare( $db_version, MEC_VERSION, '<' ) ) {

			if( current_user_can('activate_plugins') ) {

				\MEC\Transactions\Transaction::upgrade_db();
			}
		}
	}

	/**
	 * Upgrade transactions in db by ajax
	 *
	 * @return void
	 */
	public static function upgrade_transactions_db_by_ajax() {

		if( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'mec-upgrade-transactions-in-db' ) ) {

			return;
		}

		$db_version = get_option( 'mec_transaction_version', '1.0.0' );
		if( version_compare( $db_version, '6.10.0', '<' ) ) {

			static::upgrade_transactions();
			wp_send_json(array(
				'done' => false,
			));
		}else{

			wp_send_json(array(
				'done' => true,
			));
		}
	}

	public function upgrade_notice($type = false) {

		$booking_module_status = (bool)\MEC\Settings\Settings::getInstance()->get_settings('booking_status');
		$db_version = get_option( 'mec_transaction_version', '1.0.0' );
		if( version_compare( $db_version, '6.10.0', '<' ) && $booking_module_status ) {

			if (!current_user_can('activate_plugins')) {
				return;
			}

			$upgrade_url = admin_url( '?mec_upgrade_db=true' );
			$message        = '<p>'
				. __('Your booking database needs updating. To do that, click the button below and wait until the operation is over. Do not refresh the page until the end.', 'modern-events-calendar-lite')
				. '<br><b>' . __('Note: if you have many bookings, the operation might take longer, please be patient.', 'modern-events-calendar-lite') . '</b>'
				. '</p>';
			$message       .= '<p>' . sprintf('<a href="%s" class="button-primary mec-upgrade-db">%s</a>', $upgrade_url, __('Upgrade Database Now', 'modern-events-calendar-lite')) . '</p>';

			?>
			<script>
				jQuery(document).ready(function($){
					$('.mec-upgrade-db').on('click',function(e){
						e.preventDefault();

						var $btn = $(this);
						$btn.html("<?php echo __( 'Updating Database...', 'modern-events-calendar-lite'); ?>");
						$.post(
							"<?php echo admin_url('admin-ajax.php'); ?>",
							{
								action: 'mec-upgrade-transactions-in-db',
								nonce: "<?php echo wp_create_nonce( 'mec-upgrade-transactions-in-db' ) ?>",
							},
							function(r){

								if( false == r.done ) {

									$('.mec-upgrade-db').trigger('click');
								}else{

									$btn.html("<?php echo __( 'Database has been upgraded.', 'modern-events-calendar-lite'); ?>");
								}
							}
						)
					});
				});
			</script>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
			<?php
		}
	}
}