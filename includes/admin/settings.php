<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['iburger_save_settings']) && wp_verify_nonce($_POST['iburger_settings_nonce'], 'iburger_save_settings')) {
    update_option('iburger_stamps_required', intval($_POST['stamps_required']));
    update_option('iburger_reward_product', intval($_POST['reward_product']));
    update_option('iburger_passport_title', sanitize_text_field($_POST['passport_title']));
    update_option('iburger_passport_subtitle', sanitize_text_field($_POST['passport_subtitle']));
    update_option('iburger_reward_message', sanitize_textarea_field($_POST['reward_message']));
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'iburger-passport') . '</p></div>';
}

$stamps_required = get_option('iburger_stamps_required', 6);
$reward_product_id = get_option('iburger_reward_product', 0);
$passport_title = get_option('iburger_passport_title', 'Burger World Passport');
$passport_subtitle = get_option('iburger_passport_subtitle', 'Collect stamps from around the world!');
$reward_message = get_option('iburger_reward_message', 'Congratulations! You\'ve traveled the burger world and earned a FREE burger!');

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
            
            <table class="form-table">
                <tr>
                    <th><label for="stamps_required"><?php _e('Stamps Required for Reward', 'iburger-passport'); ?></label></th>
                    <td>
                        <input type="number" id="stamps_required" name="stamps_required" value="<?php echo esc_attr($stamps_required); ?>" min="1" max="20" class="small-text">
                        <p class="description"><?php _e('Number of unique country stamps needed to unlock the free product reward', 'iburger-passport'); ?></p>
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
                    <p><?php printf(__('After %d unique stamps, customer gets a coupon for a free product!', 'iburger-passport'), $stamps_required); ?></p>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="iburger_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'iburger-passport'); ?>">
        </p>
    </form>
</div>

