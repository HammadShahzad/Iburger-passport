<?php
/**
 * Plugin Name: iBurger Passport Loyalty
 * Plugin URI: https://github.com/HammadShahzad/Iburger-passport
 * Description: A creative loyalty program where customers collect burger stamps from different countries on their digital passport. Earn rewards after collecting stamps!
 * Version: 1.7.1
 * Author: Hammad Shahzad
 * Author URI: https://github.com/HammadShahzad
 * Text Domain: iburger-passport
 * Requires at least: 5.8
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.4
 * Update URI: https://github.com/HammadShahzad/Iburger-passport
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('IBURGER_PASSPORT_VERSION', '1.7.1');
define('IBURGER_PASSPORT_PATH', plugin_dir_path(__FILE__));
define('IBURGER_PASSPORT_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class IBurger_Passport_Loyalty {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Initialize GitHub Updater
        add_action('init', array($this, 'init_updater'));

        // Check WooCommerce dependency
        add_action('plugins_loaded', array($this, 'check_woocommerce'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Ensure endpoint is installed
        add_action('admin_init', array($this, 'install_endpoint'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Admin Columns for Burger Countries
        add_filter('manage_burger_country_posts_columns', array($this, 'add_country_columns'));
        add_action('manage_burger_country_posts_custom_column', array($this, 'render_country_columns'), 10, 2);
        
        // AJAX handlers
        add_action('wp_ajax_iburger_verify_order', array($this, 'ajax_verify_order'));
        add_action('wp_ajax_iburger_claim_reward', array($this, 'ajax_claim_reward'));
        add_action('wp_ajax_iburger_download_pass', array($this, 'ajax_download_pass'));
        
        // Admin AJAX for manual stamps
        add_action('wp_ajax_iburger_admin_add_stamp', array($this, 'ajax_admin_add_stamp'));
        add_action('wp_ajax_iburger_admin_remove_stamp', array($this, 'ajax_admin_remove_stamp'));
        
        // WooCommerce hooks
        add_action('woocommerce_order_status_completed', array($this, 'process_completed_order'));
        // Handle refunds/cancellations
        add_action('woocommerce_order_status_refunded', array($this, 'remove_stamps_on_refund'));
        add_action('woocommerce_order_status_cancelled', array($this, 'remove_stamps_on_refund'));
        add_action('woocommerce_order_status_failed', array($this, 'remove_stamps_on_refund'));
        
        add_action('woocommerce_account_menu_items', array($this, 'add_passport_menu_item'));
        add_filter('woocommerce_get_query_vars', array($this, 'add_passport_query_vars'));
        add_action('init', array($this, 'add_passport_endpoint'), 0);
        add_action('woocommerce_account_burger-passport_endpoint', array($this, 'passport_endpoint_content'));
        
        // Shortcode
        add_shortcode('iburger_passport', array($this, 'passport_shortcode'));
        
        // Register custom post type for burger countries
        add_action('init', array($this, 'register_burger_countries_cpt'));
        
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_burger_country_meta_boxes'));
        add_action('save_post', array($this, 'save_burger_country_meta'));
        
        // Test Mode - Zero prices for admins
        add_filter('woocommerce_product_get_price', array($this, 'test_mode_price'), 9999, 2);
        add_filter('woocommerce_product_get_regular_price', array($this, 'test_mode_price'), 9999, 2);
        add_filter('woocommerce_product_variation_get_price', array($this, 'test_mode_price'), 9999, 2);
        add_filter('woocommerce_product_variation_get_regular_price', array($this, 'test_mode_price'), 9999, 2);
        
        // Test Mode - Zero addon prices
        add_filter('woocommerce_product_addons_option_price_raw', array($this, 'test_mode_addon_price'), 9999, 4);
        add_filter('woocommerce_product_addons_price_raw', array($this, 'test_mode_addon_price'), 9999, 2);
        add_filter('ppom_option_price', array($this, 'test_mode_simple_zero'), 9999);
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'test_mode_cart_item'), 9999, 3);
        
        // Test Mode - Zero cart totals (catches everything)
        add_action('woocommerce_before_calculate_totals', array($this, 'test_mode_cart_totals'), 9999);
        
        // Test Mode - Zero taxes
        add_filter('woocommerce_calc_tax', array($this, 'test_mode_zero_tax'), 9999);
        add_filter('woocommerce_product_get_tax_class', array($this, 'test_mode_tax_class'), 9999);
        
        add_action('wp_footer', array($this, 'test_mode_banner'));
    }
    
    public function init_updater() {
        if (!class_exists('IBurger_Passport_Updater')) {
            require_once IBURGER_PASSPORT_PATH . 'includes/class-github-updater.php';
        }
        
        new IBurger_Passport_Updater(
            __FILE__,
            'HammadShahzad',
            'Iburger-passport'
        );
    }

    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>' . __('iBurger Passport Loyalty requires WooCommerce to be installed and active.', 'iburger-passport') . '</p></div>';
            });
        }
    }
    
    public function init() {
        load_plugin_textdomain('iburger-passport', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Check if we need to flush rewrite rules
        if (get_option('iburger_passport_flush_rewrite') === 'yes') {
            flush_rewrite_rules();
            delete_option('iburger_passport_flush_rewrite');
        }
    }
    
    public function install_endpoint() {
        // Check if endpoint needs to be installed
        $installed_version = get_option('iburger_passport_endpoint_version', '0');
        
        if (version_compare($installed_version, IBURGER_PASSPORT_VERSION, '<')) {
            // Register endpoint
            add_rewrite_endpoint('burger-passport', EP_ROOT | EP_PAGES);
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Update version
            update_option('iburger_passport_endpoint_version', IBURGER_PASSPORT_VERSION);
        }
    }
    
    public function register_burger_countries_cpt() {
        $labels = array(
            'name'               => __('Burger Countries', 'iburger-passport'),
            'singular_name'      => __('Burger Country', 'iburger-passport'),
            'menu_name'          => __('Burger Countries', 'iburger-passport'),
            'add_new'            => __('Add New Country', 'iburger-passport'),
            'add_new_item'       => __('Add New Burger Country', 'iburger-passport'),
            'edit_item'          => __('Edit Burger Country', 'iburger-passport'),
            'new_item'           => __('New Burger Country', 'iburger-passport'),
            'view_item'          => __('View Burger Country', 'iburger-passport'),
            'search_items'       => __('Search Burger Countries', 'iburger-passport'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'iburger-passport',
            'capability_type'    => 'post',
            'supports'           => array('title', 'thumbnail'),
            'menu_icon'          => 'dashicons-location-alt',
        );
        
        register_post_type('burger_country', $args);
    }
    
    public function add_burger_country_meta_boxes() {
        add_meta_box(
            'burger_country_details',
            __('Country Details', 'iburger-passport'),
            array($this, 'render_burger_country_meta_box'),
            'burger_country',
            'normal',
            'high'
        );
    }
    
    public function render_burger_country_meta_box($post) {
        wp_nonce_field('burger_country_meta', 'burger_country_nonce');
        
        $country_code = get_post_meta($post->ID, '_country_code', true);
        $stamp_image = get_post_meta($post->ID, '_stamp_image', true);
        $linked_products = get_post_meta($post->ID, '_linked_products', true);
        $flag_emoji = get_post_meta($post->ID, '_flag_emoji', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="country_code"><?php _e('Country Code', 'iburger-passport'); ?></label></th>
                <td>
                    <input type="text" id="country_code" name="country_code" value="<?php echo esc_attr($country_code); ?>" class="regular-text" placeholder="e.g., USA, MEX, ITA">
                    <p class="description"><?php _e('3-letter country code for the stamp', 'iburger-passport'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="flag_emoji"><?php _e('Flag Emoji', 'iburger-passport'); ?></label></th>
                <td>
                    <input type="text" id="flag_emoji" name="flag_emoji" value="<?php echo esc_attr($flag_emoji); ?>" class="regular-text" placeholder="e.g., 🇺🇸, 🇲🇽, 🇮🇹">
                    <p class="description"><?php _e('Country flag emoji for display', 'iburger-passport'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="stamp_image"><?php _e('Custom Stamp Image', 'iburger-passport'); ?></label></th>
                <td>
                    <input type="hidden" id="stamp_image" name="stamp_image" value="<?php echo esc_attr($stamp_image); ?>">
                    <button type="button" class="button" id="upload_stamp_image"><?php _e('Upload Stamp', 'iburger-passport'); ?></button>
                    <button type="button" class="button" id="remove_stamp_image" <?php echo empty($stamp_image) ? 'style="display:none;"' : ''; ?>><?php _e('Remove', 'iburger-passport'); ?></button>
                    <div id="stamp_image_preview" style="margin-top: 10px;">
                        <?php if ($stamp_image): ?>
                            <img src="<?php echo esc_url($stamp_image); ?>" style="max-width: 150px; height: auto;">
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label for="linked_products"><?php _e('Linked Products', 'iburger-passport'); ?></label></th>
                <td>
                    <select id="linked_products" name="linked_products[]" multiple class="regular-text" style="height: 150px; width: 100%;">
                        <?php
                        $products = wc_get_products(array('limit' => -1, 'status' => 'publish'));
                        $linked = is_array($linked_products) ? $linked_products : array();
                        foreach ($products as $product) {
                            $selected = in_array($product->get_id(), $linked) ? 'selected' : '';
                            echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>' . esc_html($product->get_name()) . '</option>';
                        }
                        ?>
                    </select>
                    <p class="description"><?php _e('Select products that will earn this country stamp when purchased', 'iburger-passport'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_burger_country_meta($post_id) {
        if (!isset($_POST['burger_country_nonce']) || !wp_verify_nonce($_POST['burger_country_nonce'], 'burger_country_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['country_code'])) {
            update_post_meta($post_id, '_country_code', sanitize_text_field($_POST['country_code']));
        }
        
        if (isset($_POST['flag_emoji'])) {
            update_post_meta($post_id, '_flag_emoji', sanitize_text_field($_POST['flag_emoji']));
        }
        
        if (isset($_POST['stamp_image'])) {
            update_post_meta($post_id, '_stamp_image', esc_url_raw($_POST['stamp_image']));
        }
        
        if (isset($_POST['linked_products'])) {
            update_post_meta($post_id, '_linked_products', array_map('intval', $_POST['linked_products']));
        } else {
            delete_post_meta($post_id, '_linked_products');
        }
    }

    public function add_country_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['flag'] = __('Flag', 'iburger-passport');
        $new_columns['code'] = __('Code', 'iburger-passport');
        $new_columns['products'] = __('Linked Products', 'iburger-passport');
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    public function render_country_columns($column, $post_id) {
        switch ($column) {
            case 'flag':
                echo '<span style="font-size: 24px;">' . get_post_meta($post_id, '_flag_emoji', true) . '</span>';
                break;
            case 'code':
                echo '<strong>' . esc_html(get_post_meta($post_id, '_country_code', true)) . '</strong>';
                break;
            case 'products':
                $product_ids = get_post_meta($post_id, '_linked_products', true);
                if (!empty($product_ids) && is_array($product_ids)) {
                    $names = array();
                    foreach ($product_ids as $pid) {
                        $product = wc_get_product($pid);
                        if ($product) {
                            $names[] = '<a href="' . get_edit_post_link($pid) . '">' . $product->get_name() . '</a>';
                        }
                    }
                    echo implode(', ', $names);
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
                break;
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('iBurger Passport', 'iburger-passport'),
            __('iBurger Passport', 'iburger-passport'),
            'manage_options',
            'iburger-passport',
            array($this, 'render_admin_page'),
            'dashicons-book-alt',
            56
        );
        
        add_submenu_page(
            'iburger-passport',
            __('Settings', 'iburger-passport'),
            __('Settings', 'iburger-passport'),
            'manage_options',
            'iburger-passport-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'iburger-passport',
            __('Customer Passports', 'iburger-passport'),
            __('Customer Passports', 'iburger-passport'),
            'manage_options',
            'iburger-passport-customers',
            array($this, 'render_customers_page')
        );
        
        add_submenu_page(
            'iburger-passport',
            __('Activity Log', 'iburger-passport'),
            __('Activity Log', 'iburger-passport'),
            'manage_options',
            'iburger-passport-logs',
            array($this, 'render_logs_page')
        );
    }
    
    public function render_admin_page() {
        include IBURGER_PASSPORT_PATH . 'includes/admin/dashboard.php';
    }
    
    public function render_settings_page() {
        include IBURGER_PASSPORT_PATH . 'includes/admin/settings.php';
    }
    
    public function render_customers_page() {
        include IBURGER_PASSPORT_PATH . 'includes/admin/customers.php';
    }
    
    public function render_logs_page() {
        include IBURGER_PASSPORT_PATH . 'includes/admin/activity-log.php';
    }
    
    /**
     * Log activity
     */
    public static function log_activity($type, $user_id, $details, $source = 'customer') {
        $logs = get_option('iburger_activity_log', array());
        
        $logs[] = array(
            'type' => $type,
            'user_id' => $user_id,
            'details' => $details,
            'source' => $source,
            'date' => current_time('mysql'),
        );
        
        // Keep only last 1000 entries
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        update_option('iburger_activity_log', $logs);
    }
    
    public function enqueue_frontend_assets() {
        global $post;
        $is_endpoint = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('burger-passport');
        $has_shortcode = is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'iburger_passport');
        
        // Only load assets on passport pages
        if (!$is_endpoint && !$has_shortcode) {
            return;
        }

        wp_enqueue_style(
            'iburger-passport-style',
            IBURGER_PASSPORT_URL . 'assets/css/passport.css',
            array(),
            IBURGER_PASSPORT_VERSION
        );
        
        wp_enqueue_script(
            'iburger-passport-script',
            IBURGER_PASSPORT_URL . 'assets/js/passport.js',
            array('jquery'),
            IBURGER_PASSPORT_VERSION,
            true
        );
        
        wp_localize_script('iburger-passport-script', 'iburgerPassport', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('iburger_passport_nonce'),
            'strings' => array(
                'verifying' => __('Verifying order...', 'iburger-passport'),
                'success' => __('Stamp added to your passport!', 'iburger-passport'),
                'error' => __('Something went wrong. Please try again.', 'iburger-passport'),
                'alreadyClaimed' => __('This order has already been claimed.', 'iburger-passport'),
                'invalidOrder' => __('Invalid order number.', 'iburger-passport'),
            )
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'iburger-passport') !== false || get_post_type() === 'burger_country') {
            wp_enqueue_media();
            wp_enqueue_style(
                'iburger-passport-admin',
                IBURGER_PASSPORT_URL . 'assets/css/admin.css',
                array(),
                IBURGER_PASSPORT_VERSION
            );
            wp_enqueue_script(
                'iburger-passport-admin',
                IBURGER_PASSPORT_URL . 'assets/js/admin.js',
                array('jquery'),
                IBURGER_PASSPORT_VERSION,
                true
            );
        }
    }
    
    public function add_passport_endpoint() {
        add_rewrite_endpoint('burger-passport', EP_ROOT | EP_PAGES);
    }
    
    // Add query var for WooCommerce endpoint
    public function add_passport_query_vars($query_vars) {
        $query_vars['burger-passport'] = 'burger-passport';
        return $query_vars;
    }
    
    public function add_passport_menu_item($items) {
        $new_items = array();
        foreach ($items as $key => $value) {
            $new_items[$key] = $value;
            if ($key === 'orders') {
                $new_items['burger-passport'] = __('🍔 My Passport', 'iburger-passport');
            }
        }
        return $new_items;
    }
    
    public function passport_endpoint_content() {
        echo do_shortcode('[iburger_passport]');
    }
    
    public function passport_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="iburger-login-notice">' . 
                   '<p>' . __('Please log in to view your Burger Passport.', 'iburger-passport') . '</p>' .
                   '<a href="' . esc_url(wc_get_page_permalink('myaccount')) . '" class="button">' . __('Log In', 'iburger-passport') . '</a>' .
                   '</div>';
        }
        
        ob_start();
        include IBURGER_PASSPORT_PATH . 'includes/templates/passport.php';
        return ob_get_clean();
    }
    
    public function process_completed_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $user_id = $order->get_user_id();
        if (!$user_id) return;
        
        // Check if order already processed
        if (get_post_meta($order_id, '_iburger_stamps_processed', true)) {
            return;
        }
        
        $stamps_added = array();
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            // Find burger country for this product
            $burger_countries = get_posts(array(
                'post_type' => 'burger_country',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($burger_countries as $country) {
                $linked_products = get_post_meta($country->ID, '_linked_products', true);
                if (is_array($linked_products) && in_array($product_id, $linked_products)) {
                    $stamps_added[] = $country->ID;
                }
            }
        }
        
        if (!empty($stamps_added)) {
            $this->add_stamps_to_user($user_id, array_unique($stamps_added), $order_id);
            update_post_meta($order_id, '_iburger_stamps_processed', true);
        }
    }
    
    /**
     * Remove stamps if order is refunded/cancelled
     */
    public function remove_stamps_on_refund($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $user_id = $order->get_user_id();
        
        if ($user_id) {
            $this->process_stamp_removal($user_id, $order_id);
        }
    }
    
    private function process_stamp_removal($user_id, $order_id) {
        $stamps = get_user_meta($user_id, '_iburger_stamps', true);
        if (!is_array($stamps)) return;
        
        $initial_count = count($stamps);
        $removed_count = 0;
        
        // Filter out stamps from this order
        $stamps = array_filter($stamps, function($stamp) use ($order_id, &$removed_count) {
            if (isset($stamp['order_id']) && $stamp['order_id'] == $order_id) {
                $removed_count++;
                return false;
            }
            return true;
        });
        
        if (count($stamps) < $initial_count) {
            // Re-index array
            $stamps = array_values($stamps);
            update_user_meta($user_id, '_iburger_stamps', $stamps);
            
            // Log removal
            $details = sprintf(__('Removed %d stamp(s) from Order #%s (Refund/Cancel)', 'iburger-passport'), $removed_count, $order_id);
            self::log_activity('stamp_removed', $user_id, $details, 'system');
            
            // Remove from claimed list
            $claimed_orders = get_user_meta($user_id, '_iburger_claimed_orders', true);
            if (is_array($claimed_orders)) {
                $claimed_orders = array_diff($claimed_orders, array($order_id));
                update_user_meta($user_id, '_iburger_claimed_orders', array_values($claimed_orders));
            }
            
            // Reset processed flag on order so it can be re-added if status changes back
            delete_post_meta($order_id, '_iburger_stamps_processed');
        }
    }
    
    public function add_stamps_to_user($user_id, $country_ids, $order_id, $source = 'customer') {
        $user_stamps = get_user_meta($user_id, '_iburger_stamps', true);
        if (!is_array($user_stamps)) {
            $user_stamps = array();
        }
        
        $country_names = array();
        foreach ($country_ids as $country_id) {
            $stamp_data = array(
                'country_id' => $country_id,
                'order_id' => $order_id,
                'date' => current_time('mysql'),
            );
            $user_stamps[] = $stamp_data;
            
            $country = get_post($country_id);
            if ($country) {
                $country_names[] = $country->post_title;
            }
        }
        
        update_user_meta($user_id, '_iburger_stamps', $user_stamps);
        
        // Log activity
        $details = sprintf(__('Added stamps: %s (Order #%s)', 'iburger-passport'), implode(', ', $country_names), $order_id);
        self::log_activity('stamp_added', $user_id, $details, $source);
        
        // Check if user qualifies for reward
        $this->check_reward_eligibility($user_id);
    }
    
    public function check_reward_eligibility($user_id) {
        $user_stamps = get_user_meta($user_id, '_iburger_stamps', true);
        if (!is_array($user_stamps)) return;
        
        // Get unique countries collected by user
        $unique_countries = array_unique(array_column($user_stamps, 'country_id'));
        
        // Get TOTAL number of burger countries in the system
        $all_countries = get_posts(array(
            'post_type' => 'burger_country',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ));
        $total_countries = count($all_countries);
        
        // Customer must collect ALL countries to earn reward
        if (count($unique_countries) >= $total_countries && $total_countries > 0) {
            // Check if reward already claimed
            $rewards_claimed = get_user_meta($user_id, '_iburger_rewards_claimed', true);
            if (!is_array($rewards_claimed)) {
                $rewards_claimed = array();
            }
            
            // Calculate which reward batch this is (in case they collect all twice with new countries added)
            $batch_number = floor(count($unique_countries) / $total_countries);
            
            if (!in_array($batch_number, $rewards_claimed)) {
                // Mark as eligible for reward
                update_user_meta($user_id, '_iburger_reward_pending', true);
                update_user_meta($user_id, '_iburger_pending_batch', $batch_number);
                
                // Send reward unlocked email
                $this->send_reward_unlocked_email($user_id);
            }
        }
    }
    
    public function ajax_verify_order() {
        check_ajax_referer('iburger_passport_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please log in first.', 'iburger-passport')));
        }
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $user_id = get_current_user_id();
        
        if (!$order_id) {
            wp_send_json_error(array('message' => __('Invalid order number.', 'iburger-passport')));
        }
        
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(array('message' => __('Order not found.', 'iburger-passport')));
        }
        
        // Check if order belongs to user OR allow claiming by email
        $order_user_id = $order->get_user_id();
        $order_email = $order->get_billing_email();
        $current_user = wp_get_current_user();
        
        if ($order_user_id !== $user_id && $order_email !== $current_user->user_email) {
            wp_send_json_error(array('message' => __('This order does not belong to your account.', 'iburger-passport')));
        }
        
        // Check if order is completed
        if ($order->get_status() !== 'completed') {
            wp_send_json_error(array('message' => __('Order must be completed to claim stamps.', 'iburger-passport')));
        }
        
        // Check if already claimed
        $claimed_orders = get_user_meta($user_id, '_iburger_claimed_orders', true);
        if (!is_array($claimed_orders)) {
            $claimed_orders = array();
        }
        
        if (in_array($order_id, $claimed_orders)) {
            wp_send_json_error(array('message' => __('This order has already been claimed.', 'iburger-passport')));
        }
        
        // Process stamps
        $stamps_added = array();
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            $burger_countries = get_posts(array(
                'post_type' => 'burger_country',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($burger_countries as $country) {
                $linked_products = get_post_meta($country->ID, '_linked_products', true);
                if (is_array($linked_products) && in_array($product_id, $linked_products)) {
                    $stamps_added[] = array(
                        'id' => $country->ID,
                        'name' => $country->post_title,
                        'code' => get_post_meta($country->ID, '_country_code', true),
                        'flag' => get_post_meta($country->ID, '_flag_emoji', true),
                    );
                }
            }
        }
        
        if (empty($stamps_added)) {
            wp_send_json_error(array('message' => __('No burger stamps found in this order.', 'iburger-passport')));
        }
        
        // Add stamps
        $this->add_stamps_to_user($user_id, array_column($stamps_added, 'id'), $order_id);
        
        // Mark order as claimed
        $claimed_orders[] = $order_id;
        update_user_meta($user_id, '_iburger_claimed_orders', $claimed_orders);
        
        // Send stamp added email
        $this->send_stamp_added_email($user_id, $stamps_added);
        
        wp_send_json_success(array(
            'message' => __('Stamps added successfully!', 'iburger-passport'),
            'stamps' => $stamps_added
        ));
    }
    
    public function ajax_claim_reward() {
        check_ajax_referer('iburger_passport_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Please log in first.', 'iburger-passport')));
        }
        
        $user_id = get_current_user_id();
        
        // Check if eligible
        if (!get_user_meta($user_id, '_iburger_reward_pending', true)) {
            wp_send_json_error(array('message' => __('No reward available to claim.', 'iburger-passport')));
        }
        
        // Create coupon for free product
        $reward_product_id = get_option('iburger_reward_product', 0);
        
        if (!$reward_product_id) {
            wp_send_json_error(array('message' => __('Reward not configured. Please contact support.', 'iburger-passport')));
        }
        
        $coupon_code = 'IBURGER-' . strtoupper(wp_generate_password(8, false));
        
        $coupon = new WC_Coupon();
        $coupon->set_code($coupon_code);
        $coupon->set_discount_type('percent');
        $coupon->set_amount(100);
        $coupon->set_product_ids(array($reward_product_id));
        $coupon->set_usage_limit(1);
        $coupon->set_usage_limit_per_user(1);
        $coupon->set_individual_use(false);
        $coupon->set_email_restrictions(array(wp_get_current_user()->user_email));
        $coupon->set_date_expires(strtotime('+30 days'));
        $coupon->save();
        
        // Mark reward as claimed
        $batch_number = get_user_meta($user_id, '_iburger_pending_batch', true);
        $rewards_claimed = get_user_meta($user_id, '_iburger_rewards_claimed', true);
        if (!is_array($rewards_claimed)) {
            $rewards_claimed = array();
        }
        $rewards_claimed[] = $batch_number;
        update_user_meta($user_id, '_iburger_rewards_claimed', $rewards_claimed);
        delete_user_meta($user_id, '_iburger_reward_pending');
        delete_user_meta($user_id, '_iburger_pending_batch');
        
        // Store coupon for user
        $user_coupons = get_user_meta($user_id, '_iburger_reward_coupons', true);
        if (!is_array($user_coupons)) {
            $user_coupons = array();
        }
        $user_coupons[] = array(
            'code' => $coupon_code,
            'date' => current_time('mysql'),
            'expires' => date('Y-m-d', strtotime('+30 days')),
        );
        update_user_meta($user_id, '_iburger_reward_coupons', $user_coupons);
        
        $product = wc_get_product($reward_product_id);
        $product_name = $product ? $product->get_name() : __('Free Product', 'iburger-passport');
        $expires_formatted = date('F j, Y', strtotime('+30 days'));
        
        // Log reward claim
        self::log_activity('reward_claimed', $user_id, sprintf(__('Claimed reward: %s (Coupon: %s)', 'iburger-passport'), $product_name, $coupon_code), 'customer');
        
        // Send coupon issued email
        $this->send_coupon_issued_email($user_id, $coupon_code, $product_name, $expires_formatted);
        
        wp_send_json_success(array(
            'message' => __('Congratulations! Your reward has been unlocked!', 'iburger-passport'),
            'coupon' => $coupon_code,
            'product_name' => $product_name,
            'expires' => $expires_formatted,
        ));
    }
    
    public function ajax_download_pass() {
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'iburger_wallet_pass')) {
            wp_die('Invalid security token');
        }
        
        if (!is_user_logged_in()) {
            wp_die('Please log in first');
        }
        
        // This is a placeholder. Real pass generation requires Apple Certificates.
        // For now, we redirect to a "Coming Soon" or instructions page, or output a simple text file.
        
        $user = wp_get_current_user();
        $stamps = self::get_unique_country_count($user->ID);
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="passport-info.txt"');
        
        echo "Burger Passport\n";
        echo "---------------\n";
        echo "Holder: " . $user->display_name . "\n";
        echo "Countries Visited: " . $stamps . "\n";
        echo "\n";
        echo "Apple Wallet integration coming soon!";
        exit;
    }
    
    /**
     * Send Stamp Added Email
     */
    public function send_stamp_added_email($user_id, $stamps_added) {
        try {
            // Check if email is enabled (default to true/1)
            $email_enabled = get_option('iburger_email_stamp_added', 1);
            if (!$email_enabled) {
                error_log('iBurger Passport: Stamp email disabled in settings');
                return;
            }
            
            $user = get_user_by('id', $user_id);
            if (!$user) {
                error_log('iBurger Passport: User not found for email: ' . $user_id);
                return;
            }
            
            $customer_name = $user->display_name ?: $user->user_login;
            $total_collected = self::get_unique_country_count($user_id);
            
            $all_countries = get_posts(array(
                'post_type' => 'burger_country',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'fields' => 'ids'
            ));
            $total_countries = count($all_countries);
            
            $passport_url = wc_get_account_endpoint_url('burger-passport');
            
            // Check if template exists
            $template_path = IBURGER_PASSPORT_PATH . 'includes/emails/stamp-added.php';
            if (!file_exists($template_path)) {
                error_log('iBurger Passport: Email template not found: ' . $template_path);
                return;
            }
            
            ob_start();
            include $template_path;
            $email_content = ob_get_clean();
            
            if (empty($email_content)) {
                error_log('iBurger Passport: Email content is empty');
                return;
            }
            
            $subject = sprintf('[%s] New Stamp Added to Your Passport!', get_bloginfo('name'));
            
            // Use WooCommerce mailer for better deliverability
            if (function_exists('WC') && WC()->mailer()) {
                $mailer = WC()->mailer();
                $wrapped_content = $mailer->wrap_message($subject, $email_content);
            } else {
                $wrapped_content = $email_content;
            }
            
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            $result = wp_mail($user->user_email, $subject, $wrapped_content, $headers);
            
            // Log result
            if (!$result) {
                error_log('iBurger Passport: wp_mail failed for ' . $user->user_email);
            } else {
                self::log_activity('email_sent', $user_id, __('Stamp Added Email sent', 'iburger-passport'), 'system');
            }
        } catch (Exception $e) {
            error_log('iBurger Passport Email Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send Reward Unlocked Email
     */
    public function send_reward_unlocked_email($user_id) {
        $email_enabled = get_option('iburger_email_reward_unlocked', 1);
        if (!$email_enabled) {
            return;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        $customer_name = $user->display_name ?: $user->user_login;
        
        $all_countries = get_posts(array(
            'post_type' => 'burger_country',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ));
        $total_countries = count($all_countries);
        
        $passport_url = wc_get_account_endpoint_url('burger-passport');
        
        ob_start();
        include IBURGER_PASSPORT_PATH . 'includes/emails/reward-unlocked.php';
        $email_content = ob_get_clean();
        
        $subject = sprintf(__('[%s] 🎉 Congratulations! You Unlocked a Reward!', 'iburger-passport'), get_bloginfo('name'));
        
        // Use WooCommerce mailer for better deliverability
        $mailer = WC()->mailer();
        $wrapped_content = $mailer->wrap_message($subject, $email_content);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $result = wp_mail($user->user_email, $subject, $wrapped_content, $headers);
        
        if (!$result) {
            error_log('iBurger Passport: Failed to send reward unlocked email to ' . $user->user_email);
        } else {
            self::log_activity('email_sent', $user_id, __('Reward Unlocked Email sent', 'iburger-passport'), 'system');
        }
    }
    
    /**
     * Send Coupon Issued Email
     */
    public function send_coupon_issued_email($user_id, $coupon_code, $product_name, $expires_date) {
        $email_enabled = get_option('iburger_email_coupon_issued', 1);
        if (!$email_enabled) {
            return;
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        $customer_name = $user->display_name ?: $user->user_login;
        $shop_url = wc_get_page_permalink('shop');
        
        ob_start();
        include IBURGER_PASSPORT_PATH . 'includes/emails/coupon-issued.php';
        $email_content = ob_get_clean();
        
        $subject = sprintf(__('[%s] 🎁 Your FREE Reward Coupon is Ready!', 'iburger-passport'), get_bloginfo('name'));
        
        // Use WooCommerce mailer for better deliverability
        $mailer = WC()->mailer();
        $wrapped_content = $mailer->wrap_message($subject, $email_content);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $result = wp_mail($user->user_email, $subject, $wrapped_content, $headers);
        
        if (!$result) {
            error_log('iBurger Passport: Failed to send coupon issued email to ' . $user->user_email);
        } else {
            self::log_activity('email_sent', $user_id, sprintf(__('Coupon Email sent (Code: %s)', 'iburger-passport'), $coupon_code), 'system');
        }
    }
    
    /**
     * Test Mode: Set prices to 0 for admin users
     */
    public function test_mode_price($price, $product) {
        // Only apply if test mode is enabled
        if (!get_option('iburger_test_mode', 0)) {
            return $price;
        }
        
        // Only apply for admin users
        if (!current_user_can('manage_options')) {
            return $price;
        }
        
        // Don't apply in admin area (so you can still edit products)
        if (is_admin() && !wp_doing_ajax()) {
            return $price;
        }
        
        return 0;
    }
    
    /**
     * Test Mode: Zero addon prices
     */
    public function test_mode_addon_price($price, $option = null, $type = null, $product = null) {
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return $price;
        }
        if (is_admin() && !wp_doing_ajax()) {
            return $price;
        }
        return 0;
    }
    
    /**
     * Test Mode: Simple zero return
     */
    public function test_mode_simple_zero($price) {
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return $price;
        }
        if (is_admin() && !wp_doing_ajax()) {
            return $price;
        }
        return 0;
    }
    
    /**
     * Test Mode: Zero cart item addons
     */
    public function test_mode_cart_item($cart_item, $values, $key) {
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return $cart_item;
        }
        
        // Zero out addon prices in cart item data
        if (isset($cart_item['addons']) && is_array($cart_item['addons'])) {
            foreach ($cart_item['addons'] as &$addon) {
                if (isset($addon['price'])) {
                    $addon['price'] = 0;
                }
            }
        }
        
        return $cart_item;
    }
    
    /**
     * Test Mode: Zero all cart totals
     */
    public function test_mode_cart_totals($cart) {
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return;
        }
        
        if (is_admin() && !wp_doing_ajax()) {
            return;
        }
        
        // Set all cart item prices to 0
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_item['data']->set_price(0);
        }
    }
    
    /**
     * Test Mode: Zero taxes
     */
    public function test_mode_zero_tax($taxes) {
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return $taxes;
        }
        if (is_admin() && !wp_doing_ajax()) {
            return $taxes;
        }
        
        // Return empty taxes array
        return array();
    }
    
    /**
     * Test Mode: Set tax class to zero-rate
     */
    public function test_mode_tax_class($tax_class) {
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return $tax_class;
        }
        if (is_admin() && !wp_doing_ajax()) {
            return $tax_class;
        }
        
        return 'zero-rate';
    }
    
    /**
     * Test Mode: Show banner on frontend
     */
    public function test_mode_banner() {
        // Only show if test mode is enabled and user is admin
        if (!get_option('iburger_test_mode', 0) || !current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div id="iburger-test-mode-banner" style="
            position: fixed;
            top: 32px;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-align: center;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            z-index: 999999;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        ">
            🧪 TEST MODE ACTIVE - All prices are $0 for admin users. 
            <a href="<?php echo admin_url('admin.php?page=iburger-passport-settings'); ?>" style="color: white; text-decoration: underline; margin-left: 10px;">Disable Test Mode</a>
        </div>
        <style>
            body.admin-bar #iburger-test-mode-banner { top: 32px; }
            body:not(.admin-bar) #iburger-test-mode-banner { top: 0; }
            @media screen and (max-width: 782px) {
                body.admin-bar #iburger-test-mode-banner { top: 46px; }
            }
        </style>
        <?php
    }
    
    public static function get_user_stamps($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $stamps = get_user_meta($user_id, '_iburger_stamps', true);
        return is_array($stamps) ? $stamps : array();
    }
    
    /**
     * Admin AJAX: Add stamp to user
     */
    public function ajax_admin_add_stamp() {
        check_ajax_referer('iburger_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'iburger-passport')));
        }
        
        $user_id = intval($_POST['user_id']);
        $country_id = intval($_POST['country_id']);
        
        if (!$user_id || !$country_id) {
            wp_send_json_error(array('message' => __('Invalid data', 'iburger-passport')));
        }
        
        // Add stamp
        $this->add_stamps_to_user($user_id, array($country_id), 0, 'admin');
        
        wp_send_json_success(array('message' => __('Stamp added successfully!', 'iburger-passport')));
    }
    
    /**
     * Admin AJAX: Remove stamp from user
     */
    public function ajax_admin_remove_stamp() {
        check_ajax_referer('iburger_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'iburger-passport')));
        }
        
        $user_id = intval($_POST['user_id']);
        $country_id = intval($_POST['country_id']);
        
        if (!$user_id || !$country_id) {
            wp_send_json_error(array('message' => __('Invalid data', 'iburger-passport')));
        }
        
        $stamps = get_user_meta($user_id, '_iburger_stamps', true);
        if (!is_array($stamps)) {
            wp_send_json_error(array('message' => __('No stamps found', 'iburger-passport')));
        }
        
        // Find and remove the first stamp of this country
        $found = false;
        foreach ($stamps as $key => $stamp) {
            if ($stamp['country_id'] == $country_id) {
                unset($stamps[$key]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            wp_send_json_error(array('message' => __('Stamp not found', 'iburger-passport')));
        }
        
        $stamps = array_values($stamps);
        update_user_meta($user_id, '_iburger_stamps', $stamps);
        
        $country = get_post($country_id);
        $details = sprintf(__('Removed stamp: %s (Admin action)', 'iburger-passport'), $country ? $country->post_title : 'Unknown');
        self::log_activity('stamp_removed', $user_id, $details, 'admin');
        
        wp_send_json_success(array('message' => __('Stamp removed successfully!', 'iburger-passport')));
    }
    
    public static function get_unique_country_count($user_id = null) {
        $stamps = self::get_user_stamps($user_id);
        return count(array_unique(array_column($stamps, 'country_id')));
    }
}

// Activation hook
register_activation_hook(__FILE__, function() {
    // Add default options
    add_option('iburger_stamps_required', 6);
    add_option('iburger_reward_product', 0);
    add_option('iburger_passport_title', 'Burger World Passport');
    
    // Register the endpoint first
    add_rewrite_endpoint('burger-passport', EP_ROOT | EP_PAGES);
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Also set flag to flush on next init (in case it doesn't work immediately)
    update_option('iburger_passport_flush_rewrite', 'yes');
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Initialize plugin
IBurger_Passport_Loyalty::get_instance();

