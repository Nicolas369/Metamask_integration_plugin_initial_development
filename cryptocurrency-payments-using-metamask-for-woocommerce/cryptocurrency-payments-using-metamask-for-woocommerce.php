<?php
/**
 * Plugin Name:Cryptocurrency Payments Using MetaMask For WooCommerce
 * Description:Use MataMask cryptocurrency payment gateway for WooCommerce store and let customers pay with USDT, ETH, BNB or BUSD.
 * Author:Cool Plugins
 * Author URI:https://coolplugins.net/
 * Version: 1.5.1
 * License: GPL2
 * Text Domain: cpmw
 * Domain Path: /languages
 *
 * @package MetaMask
 */

/*
Copyright (C) 2022  CoolPlugins contact@coolplugins.net

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if (!defined('ABSPATH')) {
    exit;
}
define('CPMW_VERSION', '1.5.1');
define('CPMW_FILE', __FILE__);
define('CPMW_PATH', plugin_dir_path(CPMW_FILE));
define('CPMW_URL', plugin_dir_url(CPMW_FILE));

define('CPMW_BUY_PRO', 'https://paywithcryptocurrency.net/wordpress-plugin/pay-with-metamask-for-woocommerce-pro/?utm_source=cpmw_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=settings');

if (!defined('CPMW_DEMO_URL')) {
    define('CPMW_DEMO_URL', 'https://paywithcryptocurrency.net/cart/?add-to-cart=2996&utm_source=cpmw_plugin&utm_medium=inside&utm_campaign=demo&utm_content=check-demo');
}
/*** cpmw_metamask_pay main class by CoolPlugins.net */
if (!class_exists('cpmw_metamask_pay')) {
    final class cpmw_metamask_pay
    {

        /**
         * The unique instance of the plugin.
         */
        private static $instance;

        /**
         * Gets an instance of our plugin.
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor.
         */
        private function __construct()
        {
        }

        // register all hooks
        public function registers()
        {
            /*** Installation and uninstallation hooks */
            register_activation_hook(CPMW_FILE, array(self::$instance, 'activate'));
            register_deactivation_hook(CPMW_FILE, array(self::$instance, 'deactivate'));
            $this->cpmw_installation_date();
            add_action('plugins_loaded', array(self::$instance, 'cpmw_load_files'));
            add_filter('woocommerce_payment_gateways', array(self::$instance, 'cpmw_add_gateway_class'));
            add_action('admin_enqueue_scripts', array(self::$instance, 'cmpw_admin_style'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(self::$instance, 'cpmw_add_widgets_action_links'));
            add_action('admin_menu', array($this, 'cpmw_add_submenu_page'), 1000);
            add_action('init', array($this, 'cpmw_plugin_version_verify'));
            add_action('plugins_loaded', array($this, 'load_text_domain'));
            // add_action('csf_cpmw_settings_save', array($this, 'cpmw_delete_trainsient'));
            add_action('csf_cpmw_settings_save_before', array($this, 'cpmw_delete_trainsient'), 10, 2);
            add_action('woocommerce_blocks_loaded', array($this, 'woocommerce_gateway_block_support'));

        }

        public function cpmw_delete_trainsient($request, $instance)
        {
            // Set option key, which option will control ?
            $opt_key = 'openexchangerates_key';
            $crypto_compare = 'crypto_compare_key';

            // The saved options from framework instance
            $options = $instance->options;

            // Checking the option-key change or not.
            if (isset($options[$opt_key]) && isset($request[$opt_key]) && ($options[$opt_key] !== $request[$opt_key]) || isset($options[$crypto_compare]) && isset($request[$crypto_compare]) && ($options[$crypto_compare] !== $request[$crypto_compare])) {

                delete_transient('cpmw_openexchangerates');
                delete_transient('cpmw_binance_priceETHUSDT');
                delete_transient('cpmw_currencyUSDT');
                delete_transient('cpmw_currencyETH');
                delete_transient('cpmw_currencyBUSD');
                delete_transient('cpmw_currencyBNB');

            }

        }

        public function cpmw_add_submenu_page()
        {
            add_submenu_page('woocommerce', 'MetaMask Settings', '<strong>MetaMask</strong>', 'manage_options', 'admin.php?page=wc-settings&tab=checkout&section=cpmw', false, 100);

            add_submenu_page('woocommerce', 'MetaMask Transaction', '↳ Transaction', 'manage_options', 'cpmw-metamask', array('CPMW_TRANSACTION_TABLE', 'cpmw_transaction_table'), 101);
            add_submenu_page('woocommerce', 'Settings', '↳ Settings', 'manage_options', 'admin.php?page=cpmw-metamask-settings', false, 102);

        }

        // custom links for add widgets in all plugins section
        public function cpmw_add_widgets_action_links($links)
        {
            $cpmw_settings = admin_url() . 'admin.php?page=cpmw-metamask-settings';
            $links[] = '<a  style="font-weight:bold" href="' . esc_url($cpmw_settings) . '" target="_self">' . __('Settings', 'cpmw') . '</a>';
            return $links;

        }

        public function cmpw_admin_style($hook)
        {
            wp_enqueue_script('cpmw-custom', CPMW_URL . 'assets/js/cpmw-admin.js', array('jquery'), CPMW_VERSION, true);
            wp_enqueue_style('cpmw_admin_css', CPMW_URL . 'assets/css/cpmw-admin.css', array(), CPMW_VERSION, null, 'all');

        }

        public function cpmw_add_gateway_class($gateways)
        {
            $gateways[] = 'WC_cpmw_Gateway'; // your class name is here
            return $gateways;
        }
        /*** Load required files */
        public function cpmw_load_files()
        {
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'cpmw_missing_wc_notice'));
                return;
            }
            /*** Include helpers functions*/
            require_once CPMW_PATH . 'includes/api/cpmw-api-data.php';
            require_once CPMW_PATH . 'includes/helper/cpmw-helper-functions.php';
            require_once CPMW_PATH . 'includes/cpmw-woo-payment-gateway.php';
            require_once CPMW_PATH . 'includes/db/cpmw-db.php';
            require_once CPMW_PATH . 'includes/class-rest-api.php';
            if (is_admin()) {
                require_once CPMW_PATH . 'admin/table/cpmw-transaction-table.php';
                require_once CPMW_PATH . 'admin/table/cpmw-list-table.php';
                require_once CPMW_PATH . 'admin/feedback/admin-feedback-form.php';
                require_once CPMW_PATH . 'admin/class.review-notice.php';
                require_once CPMW_PATH . 'admin/codestar-framework/codestar-framework.php';
                require_once CPMW_PATH . 'admin/options-settings.php';
            }

        }
        public function cpmw_installation_date()
        {
            $get_installation_time = strtotime('now');
            add_option('cpmw_activation_time', $get_installation_time);
        }
        public function cpmw_missing_wc_notice()
        {
            $installurl = admin_url() . 'plugin-install.php?tab=plugin-information&plugin=woocommerce';
            if (file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')) {
                echo '<div class="error"><p>' . __('Cryptocurrency Payments Using MetaMask For WooCommerce requires WooCommerce to be active', 'cpmw') . '</div>';
            } else {
                wp_enqueue_script('cpmw-custom-notice', CPMW_URL . 'assets/js/cpmw-admin-notice.js', array('jquery'), CPMW_VERSION, true);
                echo '<div class="error"><p>' . sprintf(__('Cryptocurrency Payments Using MetaMask For WooCommerce requires WooCommerce to be installed and active. Click here to %s WooCommerce plugin.', 'cpmw'), '<button class="cpmw_modal-toggle" >' . __('Install', 'cpmw') . ' </button>') . '</p></div>';
                ?>
				<div class="cpmw_modal">
					<div class="cpmw_modal-overlay cpmw_modal-toggle"></div>
					<div class="cpmw_modal-wrapper cpmw_modal-transition">
					<div class="cpmw_modal-header">
						<button class="cpmw_modal-close cpmw_modal-toggle"><span class="dashicons dashicons-dismiss"></span></button>
						<h2 class="cpmw_modal-heading"><?php _e('Install WooCommerce', 'cpmw');?></h2>
					</div>
					<div class="cpmw_modal-body">
						<div class="cpmw_modal-content">
						<iframe  src="<?php echo esc_url($installurl); ?>" width="600" height="400" id="cpmw_custom_cpmw_modal"> </iframe>
						</div>
					</div>
					</div>
				</div>
				<?php
}
        }

        // set settings on plugin activation
        public static function activate()
        {
            require_once CPMW_PATH . 'includes/db/cpmw-db.php';
            update_option('cpmw-v', CPMW_VERSION);
            update_option('cpmw-type', 'FREE');
            update_option('cpmw-installDate', date('Y-m-d h:i:s'));
            update_option('cpmw-already-rated', 'no');
            $db = new CPMW_database();
            $db->create_table();
        }
        public static function deactivate()
        {
            // $db= new CPMW_database();
            // $db->drop_table();
            delete_option('cpmw-v');
            delete_option('cpmw-type');
            delete_option('cpmw-installDate');
            delete_option('cpmw-already-rated');

        }
        /*
        |--------------------------------------------------------------------------
        |  Check if plugin is just updated from older version to new
        |--------------------------------------------------------------------------
         */
        public function cpmw_plugin_version_verify()
        {
            $CPMW_VERSION = get_option('CPMW_FREE_VERSION');

            if (!isset($CPMW_VERSION) || version_compare($CPMW_VERSION, CPMW_VERSION, '<')) {
                if (!get_option('wp_cpmw_transaction_db_version')) {
                    $this->activate();
                }
                if (isset($CPMW_VERSION) && empty(get_option('cpmw_migarte_settings'))) {
                    $this->cpmw_migrate_settings();
                    update_option('cpmw_migarte_settings', 'migrated');
                }

                update_option('CPMW_FREE_VERSION', CPMW_VERSION);

            }

        }

        // Migrate woocommerce settings to codestar
        protected function cpmw_migrate_settings()
        {
            $woocommerce_settings = get_option('woocommerce_cpmw_settings');
            $codestar_options = get_option('cpmw_settings');
            if (!empty($woocommerce_settings)) {
                $codestar_options['user_wallet'] = $woocommerce_settings['user_wallet'];
                $codestar_options['currency_conversion_api'] = $woocommerce_settings['currency_conversion_api'];
                $codestar_options['crypto_compare_key'] = $woocommerce_settings['crypto_compare_key'];
                $codestar_options['openexchangerates_key'] = $woocommerce_settings['openexchangerates_key'];
                $codestar_options['Chain_network'] = $woocommerce_settings['Chain_network']; // ng_cahin
                $codestar_options['eth_select_currency'] = $woocommerce_settings['eth_select_currency'];
                $codestar_options['user_wallet'] = $woocommerce_settings['user_wallet'];
                $codestar_options['bnb_select_currency'] = $woocommerce_settings['bnb_select_currency']; // ng_ coin
                $codestar_options['payment_status'] = $woocommerce_settings['payment_status'];
                $codestar_options['payment_msg'] = $woocommerce_settings['payment_msg'];
                $codestar_options['confirm_msg'] = $woocommerce_settings['confirm_msg'];
                $codestar_options['payment_process_msg'] = $woocommerce_settings['payment_process_msg'];
                $codestar_options['rejected_message'] = $woocommerce_settings['rejected_message'];
                update_option('cpmw_settings', $codestar_options);
            }

        }
        /*
        |--------------------------------------------------------------------------
        | Load Text domain
        |--------------------------------------------------------------------------
         */
        public function load_text_domain()
        {
            load_plugin_textdomain('cpmw', false, basename(dirname(__FILE__)) . '/languages/');
        }
        public function woocommerce_gateway_block_support()
        {
            if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
                if (!class_exists('CPMWP_metamask_pay')) {
                    require_once 'includes/blocks/class-payment-gateway-blocks.php';
                    add_action(
                        'woocommerce_blocks_payment_method_type_registration',
                        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                            $payment_method_registry->register(new WC_cpmw_Gateway_Blocks_Support());
                        }
                    );
                }
            }
        }

    }

}
/*** cpmw_metamask_pay main class - END */

/*** THANKS - CoolPlugins.net ) */
$cpmw = cpmw_metamask_pay::get_instance();
$cpmw->registers();
