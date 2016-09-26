<?php

class Salesforce_Logging extends WP_Logging {

	protected $wpdb;
    protected $version;
    protected $text_domain;

    public $enabled;
    public $statuses_to_log;


    /**
    * Functionality for using the WP_Logging class
    *
    * @param object $wpdb
    * @param string $version
    * @param string $text_domain
    * @throws \Exception
    */
    public function __construct( $wpdb, $version, $text_domain ) {
        $this->wpdb = &$wpdb;
        $this->version = $version;
        $this->text_domain = $text_domain;

        $this->enabled = get_option( 'salesforce_api_enable_logging', FALSE );
        $this->statuses_to_log = get_option( 'salesforce_api_statuses_to_log', array() );

        $this->init();

    }

    /**
    * start
    *
    * @throws \Exception
    */
    private function init() {
        if ( $this->enabled === '1' ) {
            add_filter( 'wp_log_types', array( $this, 'set_log_types' ), 10, 1 );
            add_filter( 'wp_logging_should_we_prune', array( $this, 'set_prune_option' ), 10, 1 );
            add_filter( 'wp_logging_prune_when', array( $this, 'set_prune_age' ), 10, 1 );
            add_filter( 'wp_logging_prune_query_args', array( $this, 'set_prune_args' ), 10, 1 );
        }
    }

    /**
    * Set terms for Salesforce logs
    *
    * @param array $terms
    * @return array $terms
    */
    public function set_log_types( $terms ) {
        $terms[] = 'salesforce';
        return $terms;
    }

    /**
    * Should logs be pruned at all?
    *
    * @param string $should_we_prune
    * @return string $should_we_prune
    */
    public function set_prune_option( $should_we_prune ) {
        $should_we_prune = get_option( 'salesforce_api_prune_logs', $should_we_prune );
        if ( $should_we_prune === '1' ) {
            $should_we_prune = true;
        }
        return $should_we_prune;
    }

    /**
    * Set how often to prune the Salesforce logs
    *
    * @param string $how_old
    * @return string $how_old
    */
    public function set_prune_age( $how_old ) {
        $value = get_option( 'salesforce_api_logs_how_old', '' ) . ' ago';
        if ( $value !== '' ) {
            return $value;
        } else {
            return $how_old;
        }
    }

    /**
    * Set arguments for only getting the Salesforce logs
    *
    * @param array $args
    * @return array $args
    */
    public function set_prune_args( $args ) {
        $args['log_type'] = 'salesforce';
        return $args;
    }

    /**
     * Setup new log entry
     *
     * Check and see if we should log anything, and if so, send it to add()
     *
     * @access      public
     * @since       1.0
     *
     * @uses        self::add()
     *
     * @return      none
    */

    public function setup( $title, $message, $trigger = 0, $parent = 0, $status ) {
        if ( $this->enabled === '1' && in_array( $status, $this->statuses_to_log ) ) {
            $triggers_to_log = get_option( 'salesforce_api_triggers_to_log', array() );
            if ( in_array( $trigger, $triggers_to_log ) || $trigger === 0 ) {
                $this->add( $title, $message, $parent );
            }
        }
    }

    /**
     * Create new log entry
     *
     * This is just a simple and fast way to log something. Use self::insert_log()
     * if you need to store custom meta data
     *
     * @access      public
     * @since       1.0
     *
     * @uses        self::insert_log()
     *
     * @return      int The ID of the new log entry
    */

    public static function add( $title = '', $message = '', $parent = 0, $type = 'salesforce' ) {

        $log_data = array(
            'post_title'   => $title,
            'post_content' => $message,
            'post_parent'  => $parent,
            'log_type'     => $type
        );

        return self::insert_log( $log_data );

    }


    /**
     * Easily retrieves log items for a particular object ID
     *
     * @access      private
     * @since       1.0
     *
     * @uses        self::get_connected_logs()
     *
     * @return      array
    */

    public static function get_logs( $object_id = 0, $type = 'salesforce', $paged = null ) {
        return self::get_connected_logs( array( 'post_parent' => $object_id, 'paged' => $paged, 'log_type' => $type ) );

    }


    /**
     * Retrieve all connected logs
     *
     * Used for retrieving logs related to particular items, such as a specific purchase.
     *
     * @access  private
     * @since   1.0
     *
     * @uses    wp_parse_args()
     * @uses    get_posts()
     * @uses    get_query_var()
     * @uses    self::valid_type()
     *
     * @return  array / false
    */

    public static function get_connected_logs( $args = array() ) {

        $defaults = array(
            'post_parent'    => 0,
            'post_type'      => 'wp_log',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
            'paged'          => get_query_var( 'paged' ),
            'log_type'       => 'salesforce'
        );

        $query_args = wp_parse_args( $args, $defaults );

        if( $query_args['log_type'] && self::valid_type( $query_args['log_type'] ) ) {

            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'wp_log_type',
                    'field'    => 'slug',
                    'terms'    => $query_args['log_type']
                )
            );

        }

        $logs = get_posts( $query_args );

        if( $logs )
            return $logs;

        // no logs found
        return false;

    }


    /**
     * Retrieves number of log entries connected to particular object ID
     *
     * @access  private
     * @since   1.0
     *
     * @uses    WP_Query()
     * @uses    self::valid_type()
     *
     * @return  int
    */

    public static function get_log_count( $object_id = 0, $type = 'salesforce', $meta_query = null ) {

        $query_args = array(
            'post_parent'    => $object_id,
            'post_type'      => 'wp_log',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        );

        if( ! empty( $type ) && self::valid_type( $type ) ) {

            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'wp_log_type',
                    'field'    => 'slug',
                    'terms'    => $type
                )
            );

        }

        if( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        $logs = new WP_Query( $query_args );

        return (int) $logs->post_count;

    }


}