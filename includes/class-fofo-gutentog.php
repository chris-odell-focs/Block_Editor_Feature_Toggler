<?php
namespace FoFoGutenTog;

/**
 * The core class the is repsonsible for turning the feature on and off
 * 
 * This class provides the following features:-
 * 
 *  1. Initialises the hooks for do_action to turn features on and off
 *  2. Adds the admin screen so features can be turned on/off interactively
 *  3. Captures the post back variables from the admin screen
 * 
 * Available block editor features for disabling are :-
 * 
 *  The 'Category Panel' on the settings sidebar [use feature name 'category_panel']
 *  The 'Tag Panel' on the settings sidebar [use feature name 'tag_panel']
 *  The 'Featured Image Panel' on the settings sidebar [use feature name 'featured_image_panel']
 *  The 'Excerpt Panel' on the settings sidebar [use feature name 'excerpt_panel']
 *  The 'Discussion Panel' on the settings sidebar [use feature name 'discussion_panel']
 *  The 'Permalink Panel' on the settings sidebar [use feature name 'permalink_panel']
 * 
 * @since 1.0.0
 */
class FoFo_GutenTog {

    /**
     * The default options when first running the plugin
     * 
     * @var array   $option_defaults {
     *      @type   string    $key    The feature name e.g. category_panel
     *      @type   string    $state  The state (onn/off) of the feature
     * }
     * 
     * @since 1.0.0
     */
    private $option_defaults = [];

    /**
     * The names of the features as they appear on the settings page
     * 
     * @var array   $option_feature_names {
     *      @type   string  $key    The feature name e.g. category_panel
     *      @type   string  $name   The name of the feature as it appears on the settings page
     * }
     * 
     * @since 1.0.0
     */
    private $option_feature_names = [];

    /**
     * A cached version of the current set of options and there state
     * for use when rendering the options page.
     * 
     * @see $option_defaults
     * 
     * @var array   $current_options {
     *      @type   string    $key    The feature name e.g. category_panel
     *      @type   string    $state  The state (onn/off) of the feature
     * }
     * 
     * @since 1.0.0
     */
    private $current_options = null;

    /**
     * The nonce key to use when rendering the form fo the settings page
     * 
     * @var string  $nonce_key
     * 
     * @since 1.0.0
     */
    private $nonce_key = '';

    /**
     * Initialise the plugin
     * 
     * @return  void
     * @since   1.0.0
     */
    public function attach() {

        $this->do_defines();
        $this->do_wp_hooks();
        $this->do_our_hooks();

        $this->option_defaults = [
            FOFO_GUTENTOG_FEATURE_KEY_CATEGORY => 'on',
            FOFO_GUTENTOG_FEATURE_KEY_TAG => 'on',
            FOFO_GUTENTOG_FEATURE_KEY_FEAT_IMG => 'on',
            FOFO_GUTENTOG_FEATURE_KEY_EXCERPT => 'on',
            FOFO_GUTENTOG_FEATURE_KEY_DISCUSSION => 'on',
            FOFO_GUTENTOG_FEATURE_KEY_PERMALINK => 'on'
        ];
        
        $this->option_feature_names = [
            FOFO_GUTENTOG_FEATURE_KEY_CATEGORY => 'Category Panel',
            FOFO_GUTENTOG_FEATURE_KEY_TAG => 'Tags Panel',
            FOFO_GUTENTOG_FEATURE_KEY_FEAT_IMG => 'Featured Image Panel',
            FOFO_GUTENTOG_FEATURE_KEY_EXCERPT => 'Excerpt Panel',
            FOFO_GUTENTOG_FEATURE_KEY_DISCUSSION => 'Discussion Panel',
            FOFO_GUTENTOG_FEATURE_KEY_PERMALINK => 'Permalink Panel'
        ];

        $this->nonce_key = implode( '-', array_keys( $this->option_defaults ) );
        $this->check_options_exist();
    }

    /**
     * Define constants for use throghout the class
     * 
     * @return  void
     * @since   1.0.0
     */
    private function do_defines() {

        if( !defined( 'FOFO_GUTENTOG_OPTIONS_KEY' ) ) {
            define( 'FOFO_GUTENTOG_OPTIONS_KEY', 'FOFO_GUTENTOG_OPTIONS_KEY' );
        }

        //Defines for the option keys
        if( !defined( 'FOFO_GUTENTOG_FEATURE_KEY_CATEGORY' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_KEY_CATEGORY', 'category_panel' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_KEY_TAG' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_KEY_TAG', 'tag_panel' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_KEY_FEAT_IMG' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_KEY_FEAT_IMG', 'featured_image_panel' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_KEY_EXCERPT' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_KEY_EXCERPT', 'excerpt_panel' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_KEY_DISCUSSION' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_KEY_DISCUSSION', 'discussion_panel' );
        }
     
        if( !defined( 'FOFO_GUTENTOG_FEATURE_KEY_PERMALINK' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_KEY_PERMALINK', 'permalink_panel' );
        }

        //Defines for the hooks
        if( !defined( 'FOFO_GUTENTOG_FEATURE_ON' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_ON', 'fofo_gutentog_feature_on' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_OFF' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_OFF', 'fofo_gutentog_feature_off' );
        }

        //Misc Defines
        if( !defined( 'FOFO_GUTENTOG_REQUEST_KEY' ) ) {
            define( 'FOFO_GUTENTOG_REQUEST_KEY', 'fofo_gutentog' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_ON_STATE' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_ON_STATE', 'on' );
        }

        if( !defined( 'FOFO_GUTENTOG_FEATURE_OFF_STATE' ) ) {
            define( 'FOFO_GUTENTOG_FEATURE_OFF_STATE', 'off' );
        }
    }

    /**
     * Hook into the WP actions
     * 
     * This method hooks into the functions for loading the admin scripts
     * and adding a settings page.
     * 
     * @return  void
     * @since   1.0.0
     */
    private function do_wp_hooks() {
        
        add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
        add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );
    }

    /**
     * Localise and load the JS script for turning Block Editor
     * featuures on/off.
     * 
     * @return  void
     * @since   1.0.0
     */
    public function load_scripts() {

        $disabled_gutenberg_features = $this->get_disabled_gutenberg_features();
        wp_register_script( 'fofogtentog-js', plugin_dir_url( __FILE__ ) . '../js/fofogtentog.js', null, '1.0.0', true );
        wp_localize_script(
            'fofogtentog-js', 
            'fofogutentog', 
            $disabled_gutenberg_features
        );

        wp_enqueue_script( 'fofogtentog-js' );
    }

    /**
     * Get the block editor featuures that have been turned off
     * 
     * @return array {
     *      @type   string  $feature    The feature which is off(disabled)
     * }
     * @since 1.0.0
     */
    private function get_disabled_gutenberg_features() {

        $options = get_option( FOFO_GUTENTOG_OPTIONS_KEY, $this->option_defaults );
        $disabled_features = [];

        foreach( $options as $key => $value ) {
            if( 'off' === $value ) {
                $disabled_features[] = $key;
            }
        }

        return $disabled_features;
    }

    /**
     * Add a settings page for interactive use
     * 
     * This adds a settings page so that features can be turned on/off
     * interactively. If the settings page is never to be used and
     * features will be turned on/off using the hooks only then
     * the FOFO_GUTENTOG_SHOW_SETTINGS define can be used to hide the
     * settings page 
     * 
     * @see /fof-guten-toggler.php
     * 
     * @return  void
     * @since   1.0.0
     */
    public function add_plugin_page() {

        if( FOFO_GUTENTOG_SHOW_SETTINGS ) {

            add_menu_page(
                'FoFo Editor Feature Toggler',
                'Editor Feature Toggler',
                'manage_options',
                'fofo_gutentog_plugin_page',
                [ $this, 'show_page' ],
                'dashicons-list-view',
                '63.05'
            );
        }
    }

    /**
     * Callback for add_menu_page
     * 
     * This is the callback for add_menu_page which builds the HTML to 
     * show on the settings page.
     * 
     * @return  void
     * @since   1.0.0
     */
    public function show_page() {

        $this->check_for_ui_update();

        $feature_list = '';
        $row_set = '';
        $append = false;
        foreach( array_keys( $this->option_defaults ) as $feature_key ) {

            $css_id = 'fofo-gutentog-'.$feature_key;

            $row_set .= '
                    <th><label for="'.$css_id.'">'.$this->get_feature_name( $feature_key ).'</label></th>
                    <td>
                        <input type="hidden" name="'.FOFO_GUTENTOG_REQUEST_KEY.'['.$feature_key.']" value="'.FOFO_GUTENTOG_FEATURE_OFF_STATE.'" />
                        <input id="'.$css_id.'" name="'.FOFO_GUTENTOG_REQUEST_KEY.'['.$feature_key.']" type="checkbox" '.$this->get_feature_state( $feature_key ).' value="'.FOFO_GUTENTOG_FEATURE_ON_STATE.'"/>
                    </td>
                ';

            if( $append ) {
                $feature_list.= '<tr>'.$row_set.'</tr>';
                $row_set = '';
                $append = false;
            } else {
                $append = true;
            }
        }

        if( $append ) {
            $feature_list.= '<tr>'.$row_set.'</tr>';
        }

        $page = '
            <div class="wrap">
                <h1>Foxdell Folio Block Editor Feature Toggler</h1>
                <form method="post" action="admin.php?page=fofo_gutentog_plugin_page" novalidate="novalidate">
                    '.wp_nonce_field( $this->nonce_key, '_wpnonce', true, false ).'
                    <table class="form-table" style="width : 75%">
                        <tbody>
                            '.$feature_list.'
                        </tbody>
                    </table>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
                </form>
            </div>
        ';

        $this->current_options = null;
        echo $page;
    }

    /**
     * Get the feature state
     * 
     * Helper function called during the buildding of the settings
     * page to get the feature state
     * 
     * @param   string  $feature    The name of the feature as defined in $option_defaults
     * @return  string  Empty string if the value is off, otherwise 'checked'
     * @since   1.0.0
     */
    private function get_feature_state( $feature ) {

        if( $this->current_options === null ) {
            $this->current_options = get_option( FOFO_GUTENTOG_OPTIONS_KEY, $this->option_defaults );
        }

        return $this->current_options[ $feature ] === 'on' ? 'checked' : '';
    }

    /**
     * Get the featuure display name
     * 
     * Helper function to get the ddisplay name of the 
     * feature. Used during the page buuilding process.
     * 
     * @param   string  $feature    The name of the feature as defined in $option_defaults
     * @return  string  The display name of the feature
     * @since 1.0.0
     */
    private function get_feature_name( $feature ) {
        return $this->option_feature_names[ $feature ];
    }

    /**
     * Hook into external code calling this plugins hooks
     * 
     * Initialise the hooks that external libraries and other
     * themes/plugins can call using do_action 
     * 
     * @return  void
     * @since   1.0.0
     */
    private function do_our_hooks() {

        /**
         * Hook for the action 'fofo_gutentog_feature_on' to turn a feature on
         * 
         * @see Available block editor features
         * 
         * @param   string  $feature    The name of the feature to turn on
         * @since   1.0.0
         */
        add_action( FOFO_GUTENTOG_FEATURE_ON, [ $this, 'turn_feature_on'] , 1, 10 );

        /**
         * Hook for the action 'fofo_gutentog_feature_off' to turn a feature on
         * 
         * @see Available block editor features
         * 
         * @param   string  $feature    The name of the feature to turn on
         * @since   1.0.0
         */
        add_action( FOFO_GUTENTOG_FEATURE_OFF, [ $this, 'turn_feature_off' ], 1, 10 );
    }

    /**
     * Callback for hook to turn feature on
     * 
     * @see     toggle_feature
     * 
     * @access  internal
     * @param   string  $feature    The name of the feature to turn on. Has no effect if feature already on
     * @return  void
     * @since   1.0.0
     */
    public function turn_feature_on( $feature ) {

        $this->toggle_feature( $feature, FOFO_GUTENTOG_FEATURE_ON_STATE );
    } 

    /**
     * Callback for hook to turn feature off
     * 
     * @see     toggle_feature
     * 
     * @access  internal
     * @param   string  $feature    The name of the feature to turn off. Has no effect if feature already off
     * @return  void
     * @since   1.0.0
     */
    public function turn_feature_off( $feature ) {

        $this->toggle_feature( $feature, FOFO_GUTENTOG_FEATURE_OFF_STATE );
    } 

    /**
     * Toggle a feature on/off.
     * 
     * Helper function to update the option to toggle a feature on
     * or off.
     * 
     * @see     turn_feature_on
     * @see     turn_feature_off
     * 
     * @return  void
     * @since   1.0.0
     */
    private function toggle_feature( $key, $value ) {

        $updated_options = get_option( FOFO_GUTENTOG_OPTIONS_KEY, $this->option_defaults );
        $updated_options[ $key ] = $value;
        update_option( FOFO_GUTENTOG_OPTIONS_KEY, $updated_options );
    }

    /**
     * Check if an option exists
     * 
     * Checks to see if one of the default options exists in the database wp_options table
     * and if it doesn't then add it.
     * 
     * @return  void
     * @since   1.0.0
     */
    private function check_options_exist() {

        $options = get_option( FOFO_GUTENTOG_OPTIONS_KEY, $this->option_defaults );
        $diff = array_diff_key( $this->option_defaults, $options );
        $have_diff = count( $diff ) > 0;
        if( $have_diff ) {
            foreach( $diff as $feature => $value ) {
                $options[ $feature ] = $value;
            }

            update_option( FOFO_GUTENTOG_OPTIONS_KEY, $options );
        }
    }

    /**
     * Captre updates from the settings screen
     * 
     * @return  void
     * @since   1.0.0
     */
    private function check_for_ui_update() {
       
        $options = get_option( FOFO_GUTENTOG_OPTIONS_KEY, $this->option_defaults );
        $updates = isset( $_REQUEST[ FOFO_GUTENTOG_REQUEST_KEY ] ) ? $_REQUEST[ FOFO_GUTENTOG_REQUEST_KEY ] : null;

        if( $updates !== null ) {

            if( check_admin_referer( $this->nonce_key ) ) {
                
                $options = array_merge( $options, $updates );
                update_option( FOFO_GUTENTOG_OPTIONS_KEY, $options );
            }
        }
    }
}