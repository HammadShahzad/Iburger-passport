<?php
if (!defined('ABSPATH')) {
    exit;
}

$burger_countries = get_posts(array(
    'post_type' => 'burger_country',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

$total_stamps = 0;
$users_with_stamps = 0;

$users = get_users(array('fields' => 'ID'));
foreach ($users as $user_id) {
    $stamps = get_user_meta($user_id, '_iburger_stamps', true);
    if (is_array($stamps) && !empty($stamps)) {
        $total_stamps += count($stamps);
        $users_with_stamps++;
    }
}

$stamps_required = get_option('iburger_stamps_required', 6);
$reward_product_id = get_option('iburger_reward_product', 0);
$reward_product = $reward_product_id ? wc_get_product($reward_product_id) : null;
?>

<div class="wrap iburger-admin-wrap">
    <h1>
        <span class="dashicons dashicons-book-alt"></span>
        <?php _e('iBurger Passport Dashboard', 'iburger-passport'); ?>
    </h1>
    
    <div class="iburger-dashboard-stats">
        <div class="stat-box">
            <div class="stat-icon">🌍</div>
            <div class="stat-number"><?php echo count($burger_countries); ?></div>
            <div class="stat-label"><?php _e('Burger Countries', 'iburger-passport'); ?></div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon">📬</div>
            <div class="stat-number"><?php echo $total_stamps; ?></div>
            <div class="stat-label"><?php _e('Total Stamps Collected', 'iburger-passport'); ?></div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon">👤</div>
            <div class="stat-number"><?php echo $users_with_stamps; ?></div>
            <div class="stat-label"><?php _e('Active Passport Holders', 'iburger-passport'); ?></div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon">🎁</div>
            <div class="stat-number"><?php echo $stamps_required; ?></div>
            <div class="stat-label"><?php _e('Stamps for Reward', 'iburger-passport'); ?></div>
        </div>
    </div>
    
    <div class="iburger-dashboard-sections">
        <div class="dashboard-section">
            <h2><?php _e('Quick Setup Guide', 'iburger-passport'); ?></h2>
            <ol class="setup-steps">
                <li>
                    <strong><?php _e('Create Burger Countries', 'iburger-passport'); ?></strong>
                    <p><?php _e('Add different country burgers (e.g., American Burger, Mexican Burger, Italian Burger) from the Burger Countries menu.', 'iburger-passport'); ?></p>
                    <a href="<?php echo admin_url('post-new.php?post_type=burger_country'); ?>" class="button"><?php _e('Add Country', 'iburger-passport'); ?></a>
                </li>
                <li>
                    <strong><?php _e('Link Products', 'iburger-passport'); ?></strong>
                    <p><?php _e('Associate each burger country with WooCommerce products. When customers buy these products, they earn stamps.', 'iburger-passport'); ?></p>
                </li>
                <li>
                    <strong><?php _e('Configure Rewards', 'iburger-passport'); ?></strong>
                    <p><?php _e('Set how many unique stamps are needed and which product customers get free.', 'iburger-passport'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=iburger-passport-settings'); ?>" class="button"><?php _e('Settings', 'iburger-passport'); ?></a>
                </li>
                <li>
                    <strong><?php _e('Display Passport', 'iburger-passport'); ?></strong>
                    <p><?php _e('Use the shortcode [iburger_passport] on any page or it will automatically appear in customer accounts.', 'iburger-passport'); ?></p>
                </li>
            </ol>
        </div>
        
        <div class="dashboard-section">
            <h2><?php _e('Your Burger Countries', 'iburger-passport'); ?></h2>
            <?php if (empty($burger_countries)): ?>
                <p class="no-items"><?php _e('No burger countries created yet. Start by adding your first country!', 'iburger-passport'); ?></p>
                <a href="<?php echo admin_url('post-new.php?post_type=burger_country'); ?>" class="button button-primary"><?php _e('Create First Country', 'iburger-passport'); ?></a>
            <?php else: ?>
                <div class="countries-grid">
                    <?php foreach ($burger_countries as $country): 
                        $flag = get_post_meta($country->ID, '_flag_emoji', true);
                        $code = get_post_meta($country->ID, '_country_code', true);
                        $linked = get_post_meta($country->ID, '_linked_products', true);
                        $product_count = is_array($linked) ? count($linked) : 0;
                    ?>
                        <div class="country-card">
                            <div class="country-flag"><?php echo esc_html($flag); ?></div>
                            <div class="country-name"><?php echo esc_html($country->post_title); ?></div>
                            <div class="country-code"><?php echo esc_html($code); ?></div>
                            <div class="country-products"><?php printf(__('%d products linked', 'iburger-passport'), $product_count); ?></div>
                            <a href="<?php echo get_edit_post_link($country->ID); ?>" class="button button-small"><?php _e('Edit', 'iburger-passport'); ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="iburger-shortcode-info">
        <h3><?php _e('Shortcode', 'iburger-passport'); ?></h3>
        <code>[iburger_passport]</code>
        <p><?php _e('Add this shortcode to any page to display the customer passport. It also appears automatically in WooCommerce My Account.', 'iburger-passport'); ?></p>
    </div>
</div>

