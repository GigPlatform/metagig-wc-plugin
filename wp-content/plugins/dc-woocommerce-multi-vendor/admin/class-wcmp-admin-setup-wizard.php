
<head>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>   
</head>


<?php
/**
* Setup Wizard Class
* 
* @since 2.7.7
* @package WC Marketplace
* @author WC Marketplace
*/
if (!defined('ABSPATH')) {
exit;
}

class WCMp_Admin_Setup_Wizard {

/** @var string Currenct Step */
private $step = '';

/** @var array Steps for the setup wizard */
private $steps = array();

public function __construct() {
    add_action('admin_menu', array($this, 'admin_menus'));
    add_action('admin_init', array($this, 'setup_wizard'));
}

/**
 * Add admin menus/screens.
 */
public function admin_menus() {
    add_dashboard_page('', '', 'manage_options', 'wcmp-setup', '');
}

/**
 * Show the setup wizard.
 */
public function setup_wizard() {
    global $WCMp;
    if (filter_input(INPUT_GET, 'page') != 'wcmp-setup') {
        return;
    }

    if (!WC_Dependencies_Product_Vendor::is_woocommerce_active()) {
        if (isset($_POST['submit'])) {
            $this->install_woocommerce();
        }
        $this->install_woocommerce_view();
        exit();
    }
    $default_steps = array(
        'introduction' => array(
            'name' => __('Introduction', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_introduction'),
            'handler' => '',
        ),
        'creator_info' => array(
            'name' => __('Creator Info', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_creator_info'),
            'handler' => array($this, 'wcmp_setup_creator_info_save')
        ),
        'marketplace_goals' => array(
            'name' => __('Goals of market', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_marketplace_goals'),
            'handler' => array($this, 'wcmp_setup_marketplace_goals_save')
        ),
        'theme' => array(
            'name' => __('Theme', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_theme'),
            'handler' => array($this, 'wcmp_setup_theme_save')
        ),
        'location' => array(
            'name' => __('Location', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_location'),
            'handler' => array($this, 'wcmp_setup_location_save')
        ),
        'commission' => array(
            'name' => __('Commission Setup', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_commission'),
            'handler' => array($this, 'wcmp_setup_commission_save')
        ),
        'security' => array(
            'name' => __('Security and privacy', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_security'),
            'handler' => array($this, 'wcmp_setup_security_save')
        ),
        'next_steps' => array(
            'name' => __('Ready!', 'dc-woocommerce-multi-vendor'),
            'view' => array($this, 'wcmp_setup_ready'),
            'handler' => '',
        ),
    );
    $this->steps = apply_filters('wcmp_setup_wizard_steps', $default_steps);
    $current_step = filter_input(INPUT_GET, 'step');
    $this->step = $current_step ? sanitize_key($current_step) : current(array_keys($this->steps));
    $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), '2.70', true);
    wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
    wp_register_script('wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array('jquery', 'selectWoo'), WC_VERSION);
    wp_localize_script('wc-enhanced-select', 'wc_enhanced_select_params', array(
        'i18n_no_matches' => _x('No matches found', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_input_too_short_1' => _x('Please enter 1 or more characters', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_input_too_short_n' => _x('Please enter %qty% or more characters', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_input_too_long_n' => _x('Please delete %qty% characters', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_selection_too_long_n' => _x('You can only select %qty% items', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_load_more' => _x('Loading more results&hellip;', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'i18n_searching' => _x('Searching&hellip;', 'enhanced select', 'dc-woocommerce-multi-vendor'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'search_products_nonce' => wp_create_nonce('search-products'),
        'search_customers_nonce' => wp_create_nonce('search-customers'),
    ));

    wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
    wp_enqueue_style('wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array('dashicons', 'install'), WC_VERSION);
    wp_register_script('wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array('jquery', 'wc-enhanced-select', 'jquery-blockui'), WC_VERSION);
    wp_register_script('wcmp-setup', $WCMp->plugin_url . '/assets/admin/js/setup-wizard.js', array('wc-setup'), WC_VERSION);
    wp_localize_script('wc-setup', 'wc_setup_params', array(
        'locale_info' => json_encode(include( WC()->plugin_path() . '/i18n/locale-info.php' )),
    ));

    if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
        call_user_func($this->steps[$this->step]['handler'], $this);
    }

    ob_start();
    $this->setup_wizard_header();
    $this->setup_wizard_steps();
    $this->setup_wizard_content();
    $this->setup_wizard_footer();
    exit();
}

/**
 * Content for install woocommerce view
 */
public function install_woocommerce_view() {
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e('WC Marketplace &rsaquo; Setup Wizard', 'dc-woocommerce-multi-vendor'); ?></title>
            <?php do_action('admin_print_styles'); ?>
    <?php do_action('admin_head'); ?>
            <style type="text/css">
                body {
                    margin: 100px auto 24px;
                    box-shadow: none;
                    background: #f1f1f1;
                    padding: 0;
                    max-width: 700px;
                }
                #wcmp-logo {
                    border: 0;
                    margin: 0 0 24px;
                    padding: 0;
                    text-align: center;
                }
                .wcmp-install-woocommerce {
                    box-shadow: 0 1px 3px rgba(0,0,0,.13);
                    padding: 24px 24px 0;
                    margin: 0 0 20px;
                    background: #fff;
                    overflow: hidden;
                    zoom: 1;
                }
                .wcmp-install-woocommerce .button-primary{
                    font-size: 1.25em;
                    padding: .5em 1em;
                    line-height: 1em;
                    margin-right: .5em;
                    margin-bottom: 2px;
                    height: auto;
                }
                .wcmp-install-woocommerce{
                    font-family: sans-serif;
                    text-align: center;    
                }
                .wcmp-install-woocommerce form .button-primary{
                    color: #fff;
                    background-color: #9c5e91;
                    font-size: 16px;
                    border: 1px solid #9a548d;
                    width: 230px;
                    padding: 10px;
                    margin: 25px 0 20px;
                    cursor: pointer;
                }
                .wcmp-install-woocommerce form .button-primary:hover{
                    background-color: #9a548d;
                }
                .wcmp-install-woocommerce p{
                    line-height: 1.6;
                }

            </style>
        </head>
        <body class="wcmp-setup wp-core-ui">
            <h1 id="wcmp-logo"><a href="http://wc-marketplace.com/"><img src="<?php echo trailingslashit(plugins_url('dc-woocommerce-multi-vendor')); ?>assets/images/wc-marketplace.png" alt="WC Marketplace" /></a></h1>
            <div class="wcmp-install-woocommerce">
                <p><?php _e('WC Marketplace requires WooCommerce plugin to be active!', 'dc-woocommerce-multi-vendor'); ?></p>
                <form method="post" action="" name="wcmp_install_woocommerce">
                    <?php submit_button(__('Install WooCommerce', 'primary', 'wcmp_install_woocommerce')); ?>
    <?php wp_nonce_field('wcmp-install-woocommerce'); ?>
                </form>
            </div>
        </body>
    </html>
    <?php
}

/**
 * Install woocommerce if not exist
 * @throws Exception
 */
public function install_woocommerce() {
    check_admin_referer('wcmp-install-woocommerce');
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    WP_Filesystem();
    $skin = new Automatic_Upgrader_Skin;
    $upgrader = new WP_Upgrader($skin);
    $installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
    $plugin_slug = 'woocommerce';
    $plugin = $plugin_slug . '/' . $plugin_slug . '.php';
    $installed = false;
    $activate = false;
    // See if the plugin is installed already
    if (in_array($plugin_slug, $installed_plugins)) {
        $installed = true;
        $activate = !is_plugin_active($plugin);
    }
    // Install this thing!
    if (!$installed) {
        // Suppress feedback
        ob_start();

        try {
            $plugin_information = plugins_api('plugin_information', array(
                'slug' => $plugin_slug,
                'fields' => array(
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'homepage' => false,
                    'donate_link' => false,
                    'author_profile' => false,
                    'author' => false,
                ),
            ));

            if (is_wp_error($plugin_information)) {
                throw new Exception($plugin_information->get_error_message());
            }

            $package = $plugin_information->download_link;
            $download = $upgrader->download_package($package);

            if (is_wp_error($download)) {
                throw new Exception($download->get_error_message());
            }

            $working_dir = $upgrader->unpack_package($download, true);

            if (is_wp_error($working_dir)) {
                throw new Exception($working_dir->get_error_message());
            }

            $result = $upgrader->install_package(array(
                'source' => $working_dir,
                'destination' => WP_PLUGIN_DIR,
                'clear_destination' => false,
                'abort_if_destination_exists' => false,
                'clear_working' => true,
                'hook_extra' => array(
                    'type' => 'plugin',
                    'action' => 'install',
                ),
            ));

            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            $activate = true;
        } catch (Exception $e) {
            printf(
                    __('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'dc-woocommerce-multi-vendor'), 'WooCommerce', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=woocommerce'))
            );
            exit();
        }

        // Discard feedback
        ob_end_clean();
    }

    wp_clean_plugins_cache();
    // Activate this thing
    if ($activate) {
        try {
            $result = activate_plugin($plugin);

            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
        } catch (Exception $e) {
            printf(
                    __('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'dc-woocommerce-multi-vendor'), 'WooCommerce', admin_url('plugins.php')
            );
            exit();
        }
    }
    wp_safe_redirect(admin_url('index.php?page=wcmp-setup'));
}

/**
 * Get slug from path
 * @param  string $key
 * @return string
 */
private static function format_plugin_slug($key) {
    $slug = explode('/', $key);
    $slug = explode('.', end($slug));
    return $slug[0];
}

/**
 * Get the URL for the next step's screen.
 * @param string step   slug (default: current step)
 * @return string       URL for next step if a next step exists.
 *                      Admin URL if it's the last step.
 *                      Empty string on failure.
 * @since 2.7.7
 */
public function get_next_step_link($step = '') {
    if (!$step) {
        $step = $this->step;
    }

    $keys = array_keys($this->steps);
    if (end($keys) === $step) {
        return admin_url();
    }

    $step_index = array_search($step, $keys);
    if (false === $step_index) {
        return '';
    }

    return add_query_arg('step', $keys[$step_index + 1]);
}

/**
 * Setup Wizard Header.
 */
public function setup_wizard_header() {
    global $WCMp;
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e('WC Marketplace &rsaquo; Setup Wizard', 'dc-woocommerce-multi-vendor'); ?></title>
            <?php wp_print_scripts('wc-setup'); ?>
            <?php wp_print_scripts('wcmp-setup'); ?>
    <?php do_action('admin_print_styles'); ?>
    <?php do_action('admin_head'); ?>
            <style type="text/css">
                .wc-setup-steps {
                    justify-content: center;
                }
            </style>
        </head>
        <body class="wc-setup wp-core-ui">
            <h1 id="wc-logo"><a href="http://wc-marketplace.com/"><img src="<?php echo $WCMp->plugin_url; ?>assets/images/wc-marketplace.png" alt="WC Marketplace" /></a></h1>
            <?php
        }

/**
 * Output the steps.
 */
public function setup_wizard_steps() {
    $ouput_steps = $this->steps;
    array_shift($ouput_steps);
    ?>
    <ol class="wc-setup-steps">
        <?php foreach ($ouput_steps as $step_key => $step) : ?>
            <li class="<?php
            if ($step_key === $this->step) {
                echo 'active';
            } elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
                echo 'done';
            }
            ?>"><?php echo esc_html($step['name']); ?></li>
    <?php endforeach; ?>
    </ol>
    <?php
}

/**
 * Output the content for the current step.
 */
public function setup_wizard_content() {
    echo '<div class="wc-setup-content">';
    call_user_func($this->steps[$this->step]['view'], $this);
    echo '</div>';
}

/**
 * Introduction step.
 */
public function wcmp_setup_introduction() {
    ?>
    <h1><?php esc_html_e('Welcome to the META-GIG family!', 'dc-woocommerce-multi-vendor'); ?></h1>
    <p><?php _e('Thank you for choosing META-GIG! This quick setup wizard will help you configure the basic settings and you will have your marketplace ready in no time. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>', 'dc-woocommerce-multi-vendor'); ?></p>
    <p><?php esc_html_e("If you don't want to go through the wizard right now, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!", 'dc-woocommerce-multi-vendor'); ?></p>
    <p class="wc-setup-actions step">
        <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button-primary button button-large button-next"><?php esc_html_e("Let's go!", 'dc-woocommerce-multi-vendor'); ?></a>
        <a href="<?php echo esc_url(admin_url()); ?>" class="button button-large"><?php esc_html_e('Not right now', 'dc-woocommerce-multi-vendor'); ?></a>
    </p>
    <?php
}



/**
 * Store setup Creator info
 */ 

public function wcmp_setup_creator_info() {
    $creator_info = get_option('wcmp_creatorinfo_settings_name');
    ?>
    <h1><?php esc_html_e('Creator Info', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="name"><?php esc_html_e('Name', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <?php
                    $permalinks = get_option('dc_vendors_permalinks');
                    $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'dc-woocommerce-multi-vendor') : $permalinks['vendor_shop_base'];
                    ?>
                    <input type="text" id="name" name="UserName" placeholder="Name" value="<?php echo $vendor_slug; ?>" />
                    <p class="description"><?php _e('What is your name?') ?></p>
                </td>
            
                
            <tr>

                <th scope="row"><label for="mail"><?php esc_html_e('Email', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                <?php
                    $permalinks = get_option('dc_vendors_permalinks');
                    $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'dc-woocommerce-multi-vendor') : $permalinks['vendor_shop_base'];
                    ?>
                    
                    <input type="text" id="email" name="email" placeholder="email" value="<?php echo $vendor_slug; ?>" />
                    <p class="description"><?php _e('What is your e-mail?') ?></p>
                </td>
            </tr>
                <tr><th> <h1>Market Info</h1></th></tr>
               
            <tr>
                <th scope="row"><label for="marketName"><?php esc_html_e('Name', 'dc-woocommerce-multi-vendor'); ?></label></th>
                    <td>
                        <?php
                        $permalinks = get_option('dc_vendors_permalinks');
                        $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'dc-woocommerce-multi-vendor') : $permalinks['vendor_shop_base'];
                        ?>
                        <input type="text" id="marketName" name="vendor_store_url" placeholder="Market Name" value="<?php echo $vendor_slug; ?>" />
                        <p class="description"><?php _e('Define vendor store URL (' . site_url() . '/[this-text]/[seller-name])', 'dc-woocommerce-multi-vendor') ?>
                        </p>
                    </td>
            </tr>
            <tr>
                <th scope="row"><label for="marketName"><?php esc_html_e('Description', 'dc-woocommerce-multi-vendor'); ?></label>
                </th>
                    <td>
                        <?php
                        $permalinks = get_option('dc_vendors_permalinks');
                        $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'dc-woocommerce-multi-vendor') : $permalinks['vendor_shop_base'];
                        ?>
                        <textarea name="about"></textarea> 
                        <p class="description"><?php _e('Tell us about your market') ?></p>
                    
                    </td>
            </tr>
            
        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}



/**
*MarketPlace goals
*/


 public function wcmp_setup_marketplace_goals() {
    $marketgoals_settings = get_option('wcmp_marketgoals_settings_name');
    ?>
<script>
    $(document).ready(function() {
        $("#serviceOption").hide();
        $("#other").hide();
        $("#goodRadio").click(function(){
                $("#goodOption").show();
                $("#serviceOption").hide();
            });
    });
    $(document).ready(function() {
            $("#servicesRadio").click(function(){
                $("#goodOption").hide();
                $("#serviceOption").show();
            });
    });
   
   $(document).ready(function() {
            $("#serviceType").change(function(){
                var value = $('#serviceType').val();
                if(value=='other'){
                    $("#other").show();
                }else{
                    $("#other").hide();
                }
            });
    });

    $(document).ready(function() {
            $('.js-example-basic-single').select2();
    });

</script>

    <h1><?php esc_html_e('Goals of market', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="marketType"><?php esc_html_e('What type of marketplace are you bulding?', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <label><input checked="checked"  id="goodRadio" type="radio" <?php checked($marketType, 'goods'); ?> name="marketType" class="input-radio" value="good" /> <?php esc_html_e('Goods', 'dc-woocommerce-multi-vendor'); ?></label><br/>


                    <label><input id="servicesRadio" type="radio" <?php checked($marketType, 'services'); ?> name="marketType" class="input-radio" value="service" /> <?php esc_html_e('Services', 'dc-woocommerce-multi-vendor'); ?></label>
                </td>
            </tr>



    <form method="post" >
        <tr id="goodOption">
            <th  scope="row"><label for="goods"><?php esc_html_e('What type of good plataform are you planning?', 'dc-woocommerce-multi-vendor'); ?></label>
            </th>
        
            <td >        
                <label><input checked="checked" type="radio" <?php checked($goodType, 'sell'); ?> id="selling" name="goods" class="input-radio" value="sell" /> <?php esc_html_e('Selling', 'dc-woocommerce-multi-vendor'); ?></label><br/>


                <label><input type="radio" <?php checked($goodType, 'rent'); ?> id="renting" name="goods" class="input-radio" value="rent" /> <?php esc_html_e('Renting', 'dc-woocommerce-multi-vendor'); ?></label><br/>

                <label><input type="radio" <?php checked($goodType, 'exchange'); ?> id="exchange" name="goods" class="input-radio" value="exchange" /> <?php esc_html_e('Exchange', 'dc-woocommerce-multi-vendor'); ?></label><br/>

                <label><input type="radio" <?php checked($goodType, 'donate'); ?> id="donation" name="goods" class="input-radio" value="donate" /> <?php esc_html_e('Donation', 'dc-woocommerce-multi-vendor'); ?></label><br/>
            </td>
        </tr>
    </form>



        <tr id="serviceOption">
            <th scope="row"><label for="serviceTypes"><?php esc_html_e('What is your market of?', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>

                 <select style="width: 40%;" id="serviceType"  name="serviceType" class="js-example-basic-single">

                     <option value="food" <?php checked($serviceType, 'food'); ?> data-fields="#food"><?php esc_html_e('Food', 'dc-woocommerce-multi-vendor'); ?></option>

                    <option value="travel" <?php checked($serviceType, 'travel'); ?> data-fields="#travel"><?php esc_html_e('Travel', 'dc-woocommerce-multi-vendor'); ?></option>

                    <option value="tools" <?php checked($serviceType, 'tools'); ?> data-fields="#tools"><?php esc_html_e('Tools', 'dc-woocommerce-multi-vendor'); ?></option>

                     <option value="transport" <?php checked($serviceType, 'transport'); ?> data-fields="#transport"><?php esc_html_e('Transport', 'dc-woocommerce-multi-vendor'); ?></option>

                    <option value="delivery" <?php checked($serviceType, 'delivery'); ?> data-fields="#delivery"><?php esc_html_e('Delivery', 'dc-woocommerce-multi-vendor'); ?></option>

                    <option id="otherDrop" value="other" <?php checked($serviceType, 'other'); ?> data-fields="#others"><?php esc_html_e('Other', 'dc-woocommerce-multi-vendor'); ?></option>
                </select>
            </td>
         </tr>
            

            <tr id="other">
                <th scope="row"><label for="other"><?php esc_html_e('Other', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <input type="text" <?php checked($otherService, 'other'); ?> id="otherService" name="otherS" placeholder="Other type of service" value="<?php echo $other; ?>" />
                </td>
            </tr>

    

           
        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}









/**
*Theme
*/


 public function wcmp_setup_theme() {
    $theme_settings = get_option('wcmp_theme_settings_name');
    ?>
        <style type="text/css">
        
        .templates{
            width: 30%;
            height: 60%;
            float: left;
        }

          .templates2{
            width: 30%;
            height: 60%;
            display: block;
        }


        .templates:hover{  
                cursor: pointer;
                transition-duration: 1s;
                border:solid;
                transform: scale(1.1);
               
                }
        .templates2:hover{  
                cursor: pointer;
                transition-duration: 1s;
                border:solid;
                transform: scale(1.1);
                }
    </style>

    <h1><?php esc_html_e('Select the theme to use', 'dc-woocommerce-multi-vendor'); ?></h1>




    <script type="text/javascript">
        function getID(clicked_id) {
           alert('You have selected '.concat(clicked_id));
        }

 
    </script>


        <div style="width: 100%; height: 50%; margin-left: 22%;">
            <button name="template1" id="template1"  class="templates" style="background-image: url('https://i-cdn.phonearena.com/images/articles/294758-gallery/aesthetic-phone-wallpapers-09.jpg')" onclick="getID(this.id)"></button>

            <button name="template2" id="template2" class="templates2" style="background-image: url('https://grepitout.com/wp-content/uploads/2017/10/Mountain-300x200.jpg')"onclick="getID(this.id)"></button>
            
            <button name="template3" id="template3"  class="templates" style="background-image: url('https://www.walldevil.com/wallpapers/z10/thumb/abstract-art-backlit-wallpaper.jpg')"onclick="getID(this.id)"></button>
            
            <button name="template4" id="template4"  class="templates" style="background-image: url('https://wallup.net/wp-content/uploads/2017/03/27/166315-artwork-300x200.jpg')" onclick="getID(this.id)"></button>


        </div>

    <form method="post">
        <table class="form-table">
            
           
        </table>
        <p class="wc-setup-actions step" style="margin-left: 10%;">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}




/**
*Security
*/


 public function wcmp_setup_security() {
    $security_settings = get_option('wcmp_security_settings_name');
    ?>
    <h1><?php esc_html_e('Security and privacy', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="security"><?php esc_html_e('Do requesters or workers need to pass through an approval process? ', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <label><input checked="checked" type="radio" <?php checked($securityType, 'none'); ?> id="none" name="security" class="input-radio" value="none" /> <?php esc_html_e('None of them', 'dc-woocommerce-multi-vendor'); ?></label><br/>

                    <input type="radio" <?php checked($securityType, 'workers'); ?> id="workers" name="security" class="input-radio" value="workers" /> <?php esc_html_e('Only workers', 'dc-woocommerce-multi-vendor'); ?></label><br/>

                    <input type="radio" <?php checked($securityType, 'requesters'); ?> id="requesters" name="security" class="input-radio" value="requesters" /> <?php esc_html_e('Only requesters', 'dc-woocommerce-multi-vendor'); ?></label><br/>

                    <label><input type="radio" <?php checked($securityType, 'both'); ?> id="both" name="security" class="input-radio" value="both" /> <?php esc_html_e('Both of them', 'dc-woocommerce-multi-vendor'); ?></label>
                </td>
            </tr>

           
        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}




 /**
*Location
*/


 public function wcmp_setup_location() {
    $location_settings = get_option('wcmp_location_settings_name');
    ?>
    <h1><?php esc_html_e('Location', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="location"><?php esc_html_e('Does your model depends on location? ', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <label><input checked="checked"  type="radio" <?php checked($location, 'yes'); ?> id="yes" name="location" class="input-radio" value="yes" /> <?php esc_html_e('Yes', 'dc-woocommerce-multi-vendor'); ?></label><br/>

                    <input type="radio" <?php checked($location, 'no'); ?> id="no" name="location" class="input-radio" value="no" /> <?php esc_html_e('No', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                </td>
            </tr>

           
        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}






/**
 * Store setup content (Disabled)
 */
public function wcmp_setup_store() {
    ?>
    <h1><?php esc_html_e('GIG setup', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="vendor_store_url"><?php esc_html_e('Store URL', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <?php
                    $permalinks = get_option('dc_vendors_permalinks');
                    $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'dc-woocommerce-multi-vendor') : $permalinks['vendor_shop_base'];
                    ?>
                    <input type="text" id="vendor_store_url" name="vendor_store_url" placeholder="vendor" value="<?php echo $vendor_slug; ?>" />
                    <p class="description"><?php _e('Define vendor store URL (' . site_url() . '/[this-text]/[seller-name])', 'dc-woocommerce-multi-vendor') ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="is_single_product_multiple_vendor"><?php esc_html_e('Single Product Multiple Vendors', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
<?php $is_single_product_multiple_vendor = isset(get_option('wcmp_general_settings_name')['is_singleproductmultiseller']) ? get_option('wcmp_general_settings_name')['is_singleproductmultiseller'] : ''; ?>
                    <input type="checkbox" <?php checked($is_single_product_multiple_vendor, 'Enable'); ?> id="is_single_product_multiple_vendor" name="is_single_product_multiple_vendor" class="input-checkbox" value="Enable" />
                </td>
            </tr>
        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}

/**
 * commission setup content
 */
public function wcmp_setup_commission() {
    $payment_settings = get_option('wcmp_payment_settings_name');
    ?>
    <h1><?php esc_html_e('Commission Setup', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="revenue_sharing_mode"><?php esc_html_e('Revenue Sharing Mode', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>

                    <label><input type="radio" <?php checked($revenue_sharing_mode, 'youAndWorker'); ?> checked="checked" id="youAndWorker" name="revenue_sharing_mode" class="input-radio" value="youAndWorker" /> <?php esc_html_e('You and workers earn', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio" <?php checked($revenue_sharing_mode, 'worker'); ?> id="onlyWorkers" name="revenue_sharing_mode" class="input-radio" value="worker" /> <?php esc_html_e('Only the workers earn', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio"  <?php checked($revenue_sharing_mode, 'owner'); ?> id="onlyOwner" name="revenue_sharing_mode" class="input-radio" value="owner" /> <?php esc_html_e('Only the platform owner earn', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio" <?php checked($revenue_sharing_mode, 'anybody'); ?> id="anybody" name="revenue_sharing_mode" class="input-radio" value="anybody" /> <?php esc_html_e('Anybody earns (non-profit model)', 'dc-woocommerce-multi-vendor'); ?></label>
                </td>
            </tr>

        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}

/** 
 * payment setup content (Disabled)
 */
public function wcmp_setup_payments() {
    $payment_settings = get_option('wcmp_payment_settings_name');
    $gateways = $this->get_payment_methods();
    ?>
    <h1><?php esc_html_e('Payments', 'dc-woocommerce-multi-vendor'); ?></h1>
    <form method="post" class="wc-wizard-payment-gateway-form">
        <p><?php esc_html_e('Allowed Payment Methods', 'dc-woocommerce-multi-vendor'); ?></p>

        <ul class="wc-wizard-services wc-wizard-payment-gateways">
                    <?php foreach ($gateways as $gateway_id => $gateway): ?>
                <li class="wc-wizard-service-item wc-wizard-gateway <?php echo esc_attr($gateway['class']); ?>">
                    <div class="wc-wizard-service-name">
                        <label>
<?php echo esc_html($gateway['label']); ?>
                        </label>
                    </div>
                    <div class="wc-wizard-gateway-description">
                <?php echo wp_kses_post(wpautop($gateway['description'])); ?>
                    </div>
                    <div class="wc-wizard-service-enable">
                        <span class="wc-wizard-service-toggle disabled">
                            <?php
                            $is_enable_gateway = isset($payment_settings['payment_method_' . $gateway_id]) ? $payment_settings['payment_method_' . $gateway_id] : '';
                            ?>
                            <input type="checkbox" <?php checked($is_enable_gateway, 'Enable') ?> name="payment_method_<?php echo esc_attr($gateway_id); ?>" class="input-checkbox" value="Enable" />
                        </span>
                    </div>
                </li>
<?php endforeach; ?>
        </ul>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wcmp_disbursal_mode_admin"><?php esc_html_e('Disbursal Schedule', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <?php
                    $wcmp_disbursal_mode_admin = isset($payment_settings['wcmp_disbursal_mode_admin']) ? $payment_settings['wcmp_disbursal_mode_admin'] : '';
                    ?>
                    <input type="checkbox" data-field="#tr_payment_schedule" <?php checked($wcmp_disbursal_mode_admin, 'Enable'); ?> id="wcmp_disbursal_mode_admin" name="wcmp_disbursal_mode_admin" class="input-checkbox" value="Enable" />
                    <p class="description"><?php esc_html_e('If checked, automatically vendors commission will disburse.', 'dc-woocommerce-multi-vendor') ?></p>
                </td>
            </tr>
            <tr id="tr_payment_schedule">
                <th scope="row"><label for="payment_schedule"><?php esc_html_e('Set Schedule', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <?php
                $payment_schedule = isset($payment_settings['payment_schedule']) ? $payment_settings['payment_schedule'] : 'monthly';
                ?>
                <td>
                    <label><input type="radio" <?php checked($payment_schedule, 'weekly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="weekly" /> <?php esc_html_e('Weekly', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio" <?php checked($payment_schedule, 'daily'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="daily" /> <?php esc_html_e('Daily', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio" <?php checked($payment_schedule, 'monthly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="monthly" /> <?php esc_html_e('Monthly', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio" <?php checked($payment_schedule, 'fortnightly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="fortnightly" /> <?php esc_html_e('Fortnightly', 'dc-woocommerce-multi-vendor'); ?></label><br/>
                    <label><input type="radio" <?php checked($payment_schedule, 'hourly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="hourly" /> <?php esc_html_e('Hourly', 'dc-woocommerce-multi-vendor'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wcmp_disbursal_mode_vendor"><?php esc_html_e('Withdrawal Request', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td>
                    <?php
                    $wcmp_disbursal_mode_vendor = isset($payment_settings['wcmp_disbursal_mode_vendor']) ? $payment_settings['wcmp_disbursal_mode_vendor'] : '';
                    ?>
                    <input type="checkbox" <?php checked($wcmp_disbursal_mode_vendor, 'Enable'); ?> id="wcmp_disbursal_mode_vendor" name="wcmp_disbursal_mode_vendor" class="input-checkbox" value="Enable" />
                    <p class="description"><?php esc_html_e('Vendors can request for commission withdrawal.', 'dc-woocommerce-multi-vendor') ?></p>
                </td>
            </tr>
        </table>
        <p class="wc-setup-actions step">
            <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'dc-woocommerce-multi-vendor'); ?>" name="save_step" />
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'dc-woocommerce-multi-vendor'); ?></a>
    <?php wp_nonce_field('wcmp-setup'); ?>
        </p>
    </form>
    <?php
}



/**
 * Ready to go content
 */
public function wcmp_setup_ready() {
    ?>
    <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo site_url(); ?>" data-text="Hey Guys! Our new marketplace is now live and ready to be ransacked! Check it out at" data-via="wc_marketplace" data-size="large">Tweet</a>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    <h1><?php esc_html_e('Yay! All done!', 'dc-woocommerce-multi-vendor'); ?></h1>
    <div class="woocommerce-message woocommerce-tracker">
        <p><?php esc_html_e("Your marketplace is ready. It's time to bring some sellers on your platform and start your journey. We wish you all the success for your business, you will be great!", "dc-woocommerce-multi-vendor") ?></p>
    </div>
    <div class="wc-setup-next-steps">
        <div class="wc-setup-next-steps-first">
            <h2><?php esc_html_e( 'Next steps', 'dc-woocommerce-multi-vendor' ); ?></h2>
            <ul>
                <li class="setup-product"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=wcmp-setting-admin&tab=vendor&tab_section=registration' ) ); ?>"><?php esc_html_e( 'Create your vendor registration form', 'dc-woocommerce-multi-vendor' ); ?></a></li>
            </ul>
        </div>
        <div class="wc-setup-next-steps-last">
            <h2><?php _e( 'Learn more', 'dc-woocommerce-multi-vendor' ); ?></h2>
            <ul>
                <li class="video-walkthrough"><a href="https://www.youtube.com/c/WCMarketplace"><?php esc_html_e( 'Watch the tutorial videos', 'dc-woocommerce-multi-vendor' ); ?></a></li>
                <li class="newsletter"><a href="https://wc-marketplace.com/knowledgebase/wcmp-setup-guide/?utm_source=wcmp_plugin&utm_medium=setup_wizard&utm_campaign=new_installation&utm_content=documentation"><?php esc_html_e( 'Looking for help to get started', 'dc-woocommerce-multi-vendor' ); ?></a></li>
                <li class="learn-more"><a href="https://wc-marketplace.com/best-revenue-model-marketplace-part-one/?utm_source=wcmp_plugin&utm_medium=setup_wizard&utm_campaign=new_installation&utm_content=blog"><?php esc_html_e( 'Learn more about revenue models', 'dc-woocommerce-multi-vendor' ); ?></a></li>
            </ul>
        </div>
    </div>
    <?php
}


/**
 * save creator settings
 */
public function wcmp_setup_creator_info_save() {
    check_admin_referer('wcmp-setup');
    $general_settings = get_option('wcmp_general_settings_name');
    $vendor_permalink = filter_input(INPUT_POST, 'vendor_store_url');
    $is_single_product_multiple_vendor = filter_input(INPUT_POST, 'is_single_product_multiple_vendor');
    if ($is_single_product_multiple_vendor) {
        $general_settings['is_singleproductmultiseller'] = $is_single_product_multiple_vendor;
    } else if (isset($general_settings['is_singleproductmultiseller'])) {
        unset($general_settings['is_singleproductmultiseller']);
    }
    update_option('wcmp_general_settings_name', $general_settings);
    if ($vendor_permalink) {
        $permalinks = get_option('dc_vendors_permalinks', array());
        $permalinks['vendor_shop_base'] = untrailingslashit($vendor_permalink);
        update_option('dc_vendors_permalinks', $permalinks);
        flush_rewrite_rules();
    }
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}


 /**
 * save markeplace goals
 */
public function wcmp_setup_marketplace_goals_save() {
    check_admin_referer('wcmp-setup');
    $marketgoals_settings = get_option('wcmp_marketgoals_settings_name');
    $marketType = filter_input(INPUT_POST, 'marketType');
    $goodType = filter_input(INPUT_POST, 'goods');
    $serviceType = filter_input(INPUT_POST, 'serviceType');
    $otherService = filter_input(INPUT_POST, 'otherService');

    if ($marketType) {
        $marketgoals_settings['marketType'] = $marketType;
    }
    if ($goodType) {
        $marketgoals_settings['goodType'] = $goodType;
    }
    if ($serviceType) {
        $marketgoals_settings['serviceType'] = $serviceType;
    }
    if ($otherService) {
        $marketgoals_settings['otherService'] = $otherService;
    }
    update_option('wcmp_marketgoals_settings_name', $marketgoals_settings);
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}



 /**
 * save theme
 */
public function wcmp_setup_theme_save() {
   check_admin_referer('wcmp-setup');
    $theme_settings = get_option('wcmp_theme_settings_name');
    update_option('wcmp_theme_settings_name', $theme_settings);
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}



/**
 * save security
 */
public function wcmp_setup_security_save() {
   check_admin_referer('wcmp-setup');
    $security_settings = get_option('wcmp_security_settings_name');
    $securityType = filter_input(INPUT_POST, 'security');

    if ($securityType) {
        $security_settings['securityType'] = $securityType;
    }
    update_option('wcmp_security_settings_name', $security_settings);
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}




public function wcmp_setup_location_save() {
   check_admin_referer('wcmp-setup');
    $location_settings = get_option('wcmp_location_settings_name');
    $location = filter_input(INPUT_POST, 'location');

    if ($location) {
        $location_settings['location'] = $location;
    }
    update_option('wcmp_location_settings_name', $location_settings);
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}


/**
 * save store settings
 */
public function wcmp_setup_store_save() {
    check_admin_referer('wcmp-setup');
    $general_settings = get_option('wcmp_general_settings_name');
    $vendor_permalink = filter_input(INPUT_POST, 'vendor_store_url');
    $is_single_product_multiple_vendor = filter_input(INPUT_POST, 'is_single_product_multiple_vendor');
    if ($is_single_product_multiple_vendor) {
        $general_settings['is_singleproductmultiseller'] = $is_single_product_multiple_vendor;
    } else if (isset($general_settings['is_singleproductmultiseller'])) {
        unset($general_settings['is_singleproductmultiseller']);
    }
    update_option('wcmp_general_settings_name', $general_settings);
    if ($vendor_permalink) {
        $permalinks = get_option('dc_vendors_permalinks', array());
        $permalinks['vendor_shop_base'] = untrailingslashit($vendor_permalink);
        update_option('dc_vendors_permalinks', $permalinks);
        flush_rewrite_rules();
    }
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}

/**
 * save commission settings
 */
public function wcmp_setup_commission_save() {
    check_admin_referer('wcmp-setup');
    $payment_settings = get_option('wcmp_payment_settings_name');
    $revenue_sharing_mode = filter_input(INPUT_POST, 'revenue_sharing_mode');
    if ($revenue_sharing_mode) {
        $payment_settings['revenue_sharing_mode'] = $revenue_sharing_mode;
    }
    
    update_option('wcmp_payment_settings_name', $payment_settings);
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}

/**
 * save payment settings
 */
public function wcmp_setup_payments_save() {
    check_admin_referer('wcmp-setup');
    $gateways = $this->get_payment_methods();
    $payment_settings = get_option('wcmp_payment_settings_name');
    $wcmp_disbursal_mode_admin = filter_input(INPUT_POST, 'wcmp_disbursal_mode_admin');
    $wcmp_disbursal_mode_vendor = filter_input(INPUT_POST, 'wcmp_disbursal_mode_vendor');
    if ($wcmp_disbursal_mode_admin) {
        $payment_settings['wcmp_disbursal_mode_admin'] = $wcmp_disbursal_mode_admin;
        $payment_schedule = filter_input(INPUT_POST, 'payment_schedule');
        if ($payment_schedule) {
            $payment_settings['payment_schedule'] = $payment_schedule;
            $schedule = wp_get_schedule('masspay_cron_start');
            if ($schedule != $payment_schedule) {
                if (wp_next_scheduled('masspay_cron_start')) {
                    $timestamp = wp_next_scheduled('masspay_cron_start');
                    wp_unschedule_event($timestamp, 'masspay_cron_start');
                }
                wp_schedule_event(time(), $payment_schedule, 'masspay_cron_start');
            }
        }
    } else if (isset($payment_settings['wcmp_disbursal_mode_admin'])) {
        unset($payment_settings['wcmp_disbursal_mode_admin']);
        if (wp_next_scheduled('masspay_cron_start')) {
            $timestamp = wp_next_scheduled('masspay_cron_start');
            wp_unschedule_event($timestamp, 'masspay_cron_start');
        }
    }

    if ($wcmp_disbursal_mode_vendor) {
        $payment_settings['wcmp_disbursal_mode_vendor'] = $wcmp_disbursal_mode_vendor;
    } else if (isset($payment_settings['wcmp_disbursal_mode_vendor'])) {
        unset($payment_settings['wcmp_disbursal_mode_vendor']);
    }

    foreach ($gateways as $gateway_id => $gateway) {
        $is_enable_gateway = filter_input(INPUT_POST, 'payment_method_' . $gateway_id);
        if ($is_enable_gateway) {
            $payment_settings['payment_method_' . $gateway_id] = $is_enable_gateway;
            if (!empty($gateway['repo-slug'])) {
                wp_schedule_single_event(time() + 10, 'woocommerce_plugin_background_installer', array($gateway_id, $gateway));
            }
        } else if (isset($payment_settings['payment_method_' . $gateway_id])) {
            unset($payment_settings['payment_method_' . $gateway_id]);
        }
    }
    update_option('wcmp_payment_settings_name', $payment_settings);
    wp_redirect(esc_url_raw($this->get_next_step_link()));
    exit;
}



/**
 * Setup Wizard Footer.
 */
public function setup_wizard_footer() {
    if ('next_steps' === $this->step) :
        ?>
        <a class="wc-return-to-dashboard" href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('Return to the WordPress Dashboard', 'dc-woocommerce-multi-vendor'); ?></a>
<?php endif; ?>
</body>
</html>
<?php
}

public function get_payment_methods() {
    $methods = array(
        'paypal_masspay' => array(
            'label' => __('Paypal Masspay', 'dc-woocommerce-multi-vendor'),
            'description' => __('Pay via paypal masspay', 'dc-woocommerce-multi-vendor'),
            'class' => 'featured featured-row-last'
        ),
        'paypal_payout' => array(
            'label' => __('Paypal Payout', 'dc-woocommerce-multi-vendor'),
            'description' => __('Pay via paypal payout', 'dc-woocommerce-multi-vendor'),
            'class' => 'featured featured-row-first'
        ),
        'direct_bank' => array(
            'label' => __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor'),
            'description' => __('', 'dc-woocommerce-multi-vendor'),
            'class' => ''
        ),
        'stripe_masspay' => array(
            'label' => __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
            'description' => __('', 'dc-woocommerce-multi-vendor'),
            'repo-slug' => 'marketplace-stripe-gateway',
            'class' => ''
        ),
        'paypal_adaptive' => array(
            'label' => __('PayPal Adaptive', 'dc-woocommerce-multi-vendor'),
            'description' => __('', 'dc-woocommerce-multi-vendor'),
            'repo-slug' => 'wcmp-paypal-adaptive-gateway',
            'class' => ''
        )
    );
    return $methods;
}

}

new WCMp_Admin_Setup_Wizard();
