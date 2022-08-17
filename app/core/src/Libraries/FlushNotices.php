<?php

namespace MEC\Libraries;

use MEC\Singleton;

/**
 * Class FlushNotices
 */
class FlushNotices extends Singleton {

    /**
     * Init
     *
     * @return void
     */
    public function init(){

        add_action( 'admin_notices', array( $this, 'display_flash_notices' ), 12 );
    }

    /**
     * Add flash notice
     *
     * @since 1.0.0
     *
     * @return void
     */
    function add_flash_notice( $notice = "", $type = "warning", $dismissible = true ) {

        $notices = get_option( "mec_flash_notices", array() );

        $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";

        array_push( $notices, array(
                "notice" => $notice,
                "type" => $type,
                "dismissible" => $dismissible_text
        ));

        update_option("mec_flash_notices", $notices );
    }

    /**
     * Display flush notices
     *
     * @since 1.0.0
     *
     * @return void
     */
    function display_flash_notices() {
        $notices = get_option( "mec_flash_notices", array() );

        foreach ( $notices as $notice ) {

            printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                $notice['type'],
                $notice['dismissible'],
                $notice['notice']
            );
        }

        if( ! empty( $notices ) ) {

            delete_option( "mec_flash_notices", array() );
        }
    }
}