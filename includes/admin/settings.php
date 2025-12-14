<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle check for updates
if (isset($_GET['check_updates']) && $_GET['check_updates'] === '1' && current_user_can('manage_options')) {
    // Clear update transients to force a fresh check
    delete_site_transient('update_plugins');
    delete_transient('update_plugins');
    
    // Redirect to updates page
    wp_redirect(admin_url('update-core.php?force-check=1'));
    exit;
}

// Handle form submission
if (isset($_POST['iburger_save_settings']) && wp_verify_nonce($_POST['iburger_settings_nonce'], 'iburger_save_settings')) {
    update_option('iburger_reward_product', intval($_POST['reward_product']));
    update_option('iburger_passport_title', sanitize_text_field(wp_unslash($_POST['passport_title'])));
    update_option('iburger_passport_subtitle', sanitize_text_field(wp_unslash($_POST['passport_subtitle'])));
    update_option('iburger_reward_message', sanitize_textarea_field(wp_unslash($_POST['reward_message'])));
    
    // Email settings
    update_option('iburger_email_stamp_added', isset($_POST['email_stamp_added']) ? 1 : 0);
    update_option('iburger_email_reward_unlocked', isset($_POST['email_reward_unlocked']) ? 1 : 0);
    update_option('iburger_email_coupon_issued', isset($_POST['email_coupon_issued']) ? 1 : 0);
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'iburger-passport') . '</p></div>';
}

$reward_product_id = get_option('iburger_reward_product', 0);
$passport_title = get_option('iburger_passport_title', 'Burger World Passport');
$passport_subtitle = get_option('iburger_passport_subtitle', 'Collect stamps from around the world!');
$reward_message = get_option('iburger_reward_message', 'Congratulations! You\'ve traveled the burger world and earned a FREE burger!');

// Email settings (default to enabled)
$email_stamp_added = get_option('iburger_email_stamp_added', 1);
$email_reward_unlocked = get_option('iburger_email_reward_unlocked', 1);
$email_coupon_issued = get_option('iburger_email_coupon_issued', 1);

$products = wc_get_products(array('limit' => -1, 'status' => 'publish'));
?>

<div class="wrap iburger-admin-wrap">
    <h1>
        <span class="dashicons dashicons-admin-settings"></span>
        <?php _e('Passport Settings', 'iburger-passport'); ?>
    </h1>
    
    <form method="post" class="iburger-settings-form">
        <?php wp_nonce_field('iburger_save_settings', 'iburger_settings_nonce'); ?>
        
        <div class="settings-section">
            <h2><?php _e('Passport Appearance', 'iburger-passport'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th><label for="passport_title"><?php _e('Passport Title', 'iburger-passport'); ?></label></th>
                    <td>
                        <input type="text" id="passport_title" name="passport_title" value="<?php echo esc_attr($passport_title); ?>" class="regular-text">
                        <p class="description"><?php _e('The main title displayed on the passport cover', 'iburger-passport'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="passport_subtitle"><?php _e('Passport Subtitle', 'iburger-passport'); ?></label></th>
                    <td>
                        <input type="text" id="passport_subtitle" name="passport_subtitle" value="<?php echo esc_attr($passport_subtitle); ?>" class="regular-text">
                        <p class="description"><?php _e('Subtitle shown below the main title', 'iburger-passport'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="settings-section">
            <h2><?php _e('Reward Settings', 'iburger-passport'); ?></h2>
            
            <?php
            // Count total countries
            $total_countries = wp_count_posts('burger_country')->publish;
            ?>
            
            <table class="form-table">
                <tr>
                    <th><?php _e('Countries to Collect', 'iburger-passport'); ?></th>
                    <td>
                        <strong style="font-size: 1.5em; color: #006400;"><?php echo $total_countries; ?></strong>
                        <span style="color: #666;"> <?php _e('countries in your passport', 'iburger-passport'); ?></span>
                        <p class="description" style="margin-top: 10px; padding: 10px; background: #f0fff0; border-left: 4px solid #006400;">
                            <strong><?php _e('How it works:', 'iburger-passport'); ?></strong><br>
                            <?php _e('Customers must collect stamps from ALL countries to earn the free reward. Add more countries in "Burger Countries" menu.', 'iburger-passport'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="reward_product"><?php _e('Reward Product', 'iburger-passport'); ?></label></th>
                    <td>
                        <select id="reward_product" name="reward_product" class="regular-text">
                            <option value="0"><?php _e('-- Select Product --', 'iburger-passport'); ?></option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected($reward_product_id, $product->get_id()); ?>>
                                    <?php echo esc_html($product->get_name()); ?> (<?php echo $product->get_price_html(); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('The product customers receive for FREE when they collect enough stamps', 'iburger-passport'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="reward_message"><?php _e('Reward Unlock Message', 'iburger-passport'); ?></label></th>
                    <td>
                        <textarea id="reward_message" name="reward_message" rows="3" class="large-text"><?php echo esc_textarea($reward_message); ?></textarea>
                        <p class="description"><?php _e('Message shown when customer unlocks their reward', 'iburger-passport'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="settings-section">
            <h2><?php _e('Email Notifications', 'iburger-passport'); ?></h2>
            <p class="description" style="margin-bottom: 20px;"><?php _e('Control which emails are sent to customers during their passport journey.', 'iburger-passport'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th><?php _e('Stamp Added Email', 'iburger-passport'); ?></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="email_stamp_added" value="1" <?php checked($email_stamp_added, 1); ?>>
                            <span class="slider"></span>
                        </label>
                        <span style="margin-left: 12px; color: #666;">
                            <?php _e('Send email when customer claims stamps from an order', 'iburger-passport'); ?>
                        </span>
                        <p class="description" style="margin-top: 8px;">
                            📬 <?php _e('Shows: new stamps added, current progress, countries remaining', 'iburger-passport'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Reward Unlocked Email', 'iburger-passport'); ?></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="email_reward_unlocked" value="1" <?php checked($email_reward_unlocked, 1); ?>>
                            <span class="slider"></span>
                        </label>
                        <span style="margin-left: 12px; color: #666;">
                            <?php _e('Send email when customer collects all countries', 'iburger-passport'); ?>
                        </span>
                        <p class="description" style="margin-top: 8px;">
                            🎉 <?php _e('Shows: congratulations message, reminder to claim reward', 'iburger-passport'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Coupon Issued Email', 'iburger-passport'); ?></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="email_coupon_issued" value="1" <?php checked($email_coupon_issued, 1); ?>>
                            <span class="slider"></span>
                        </label>
                        <span style="margin-left: 12px; color: #666;">
                            <?php _e('Send email when customer claims their reward coupon', 'iburger-passport'); ?>
                        </span>
                        <p class="description" style="margin-top: 8px;">
                            🎁 <?php _e('Shows: coupon code, product name, expiry date, usage instructions', 'iburger-passport'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Apple Wallet Configuration (Disabled for now) -->
        <!-- 
        <div class="settings-section">
            <h2><?php _e('Apple Wallet Configuration', 'iburger-passport'); ?></h2>
            <div class="notice notice-info inline" style="margin: 0 0 20px 0; padding: 15px;">
                <p><strong><?php _e('Requirements for Apple Wallet Passes:', 'iburger-passport'); ?></strong></p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('An active <strong>Apple Developer Account</strong> ($99/year)', 'iburger-passport'); ?></li>
                    <li><?php _e('A <strong>Pass Type ID</strong> certificate (pass.com.yourdomain.passport)', 'iburger-passport'); ?></li>
                    <li><?php _e('The <strong>WWDR Intermediate Certificate</strong> from Apple', 'iburger-passport'); ?></li>
                    <li><?php _e('A <strong>Team ID</strong> (from your Apple Developer account)', 'iburger-passport'); ?></li>
                </ul>
                <p style="margin-top: 10px;"><?php _e('Once you have these, contact your developer to enable the .pkpass generation.', 'iburger-passport'); ?></p>
            </div>
        </div> 
        -->
        
        <div class="settings-section">
            <h2><?php _e('How It Works', 'iburger-passport'); ?></h2>
            <div class="how-it-works">
                <div class="step">
                    <span class="step-number">1</span>
                    <h4><?php _e('Customer Orders', 'iburger-passport'); ?></h4>
                    <p><?php _e('Customer purchases a burger product linked to a country', 'iburger-passport'); ?></p>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <h4><?php _e('Stamp Added', 'iburger-passport'); ?></h4>
                    <p><?php _e('When order is completed, stamp is automatically added to their passport', 'iburger-passport'); ?></p>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <h4><?php _e('Collect Stamps', 'iburger-passport'); ?></h4>
                    <p><?php _e('Customer collects stamps from different countries', 'iburger-passport'); ?></p>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <h4><?php _e('Earn Reward', 'iburger-passport'); ?></h4>
                    <p><?php printf(__('After collecting ALL %d country stamps, customer gets a coupon for a free product!', 'iburger-passport'), $total_countries); ?></p>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="iburger_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'iburger-passport'); ?>">
        </p>
    </form>
    
    <div class="settings-section" style="margin-top: 30px; background: #f8f9fa; border: 1px solid #e0e0e0;">
        <h2><?php _e('Plugin Information', 'iburger-passport'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php _e('Current Version', 'iburger-passport'); ?></th>
                <td>
                    <strong style="font-size: 1.2em; color: #006400;"><?php echo IBURGER_PASSPORT_VERSION; ?></strong>
                </td>
            </tr>
            <tr>
                <th><?php _e('Check for Updates', 'iburger-passport'); ?></th>
                <td>
                    <a href="<?php echo admin_url('admin.php?page=iburger-passport-settings&check_updates=1'); ?>" class="button button-secondary" style="background: #006400; color: white; border-color: #004d00;">
                        <span class="dashicons dashicons-update" style="margin-top: 4px;"></span>
                        <?php _e('Check for Updates Now', 'iburger-passport'); ?>
                    </a>
                    <p class="description"><?php _e('Click to check if a new version is available on GitHub.', 'iburger-passport'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php _e('GitHub Repository', 'iburger-passport'); ?></th>
                <td>
                    <a href="https://github.com/HammadShahzad/Iburger-passport" target="_blank" class="button button-link">
                        <span class="dashicons dashicons-external" style="margin-top: 4px;"></span>
                        <?php _e('View on GitHub', 'iburger-passport'); ?>
                    </a>
                </td>
            </tr>
        </table>
    </div>
</div>

