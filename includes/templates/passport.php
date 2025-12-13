<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$user = wp_get_current_user();
$stamps = IBurger_Passport_Loyalty::get_user_stamps($user_id);
$unique_count = IBurger_Passport_Loyalty::get_unique_country_count($user_id);
$stamps_required = get_option('iburger_stamps_required', 6);
$passport_title = get_option('iburger_passport_title', 'Burger World Passport');
$passport_subtitle = get_option('iburger_passport_subtitle', 'Collect stamps from around the world!');
$has_pending_reward = get_user_meta($user_id, '_iburger_reward_pending', true);
$user_coupons = get_user_meta($user_id, '_iburger_reward_coupons', true);

// Get all burger countries
$burger_countries = get_posts(array(
    'post_type' => 'burger_country',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

// Organize stamps by country
$stamps_by_country = array();
foreach ($stamps as $stamp) {
    $country_id = $stamp['country_id'];
    if (!isset($stamps_by_country[$country_id])) {
        $stamps_by_country[$country_id] = array();
    }
    $stamps_by_country[$country_id][] = $stamp;
}

// Split countries into pages (4 per page)
$countries_per_page = 4;
$pages = array_chunk($burger_countries, $countries_per_page);

$progress = min(100, round(($unique_count / $stamps_required) * 100));
?>

<div class="iburger-passport-wrapper">
    <!-- Progress Bar -->
    <div class="iburger-progress-section">
        <div class="progress-header">
            <span class="progress-label">Your Burger Journey</span>
            <span class="progress-count"><?php echo $unique_count; ?> / <?php echo $stamps_required; ?> Countries</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
        </div>
        <div class="progress-markers">
            <?php for ($i = 1; $i <= $stamps_required; $i++): ?>
                <div class="marker <?php echo $i <= $unique_count ? 'collected' : ''; ?>">
                    <div class="marker-dot"><?php echo $i <= $unique_count ? '🍔' : $i; ?></div>
                    <span class="marker-label"><?php echo $i <= $unique_count ? 'Done' : 'Next'; ?></span>
                </div>
            <?php endfor; ?>
        </div>
        <?php if ($progress >= 100 && $has_pending_reward): ?>
            <div class="reward-available">
                <span class="reward-icon">🎁</span>
                <div class="reward-text">
                    <h4><?php _e('Reward Unlocked!', 'iburger-passport'); ?></h4>
                    <p><?php _e('You\'ve traveled the burger world. Claim your free reward now!', 'iburger-passport'); ?></p>
                </div>
                <button class="claim-reward-btn" id="claimRewardBtn"><?php _e('Claim Reward', 'iburger-passport'); ?></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Passport Book -->
    <div class="passport-container">
        <div class="passport-book" id="passportBook">
            <!-- Front Cover -->
            <div class="passport-page cover front-cover" data-page="cover">
                <div class="cover-content">
                    <div class="passport-emblem">🍔</div>
                    <h1 class="passport-title"><?php echo esc_html($passport_title); ?></h1>
                    <p class="passport-subtitle"><?php echo esc_html($passport_subtitle); ?></p>
                    <div class="passport-decoration">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="holder-info">
                        <div class="holder-label"><?php _e('PASSPORT HOLDER', 'iburger-passport'); ?></div>
                        <div class="holder-name"><?php echo esc_html($user->display_name); ?></div>
                    </div>
                    <div class="open-passport-hint">
                        <?php _e('Click to open', 'iburger-passport'); ?>
                    </div>
                </div>
            </div>

            <!-- Info Page -->
            <div class="passport-page page-left info-page" data-page="0">
                <div class="page-content">
                    <div class="passport-photo">
                        <?php echo get_avatar($user_id, 100); ?>
                    </div>
                    <div class="holder-details">
                        <div class="detail-row">
                            <span class="detail-label"><?php _e('Name', 'iburger-passport'); ?></span>
                            <span class="detail-value"><?php echo esc_html($user->display_name); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php _e('Member Since', 'iburger-passport'); ?></span>
                            <span class="detail-value"><?php echo date('M Y', strtotime($user->user_registered)); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php _e('Passport No.', 'iburger-passport'); ?></span>
                            <span class="detail-value"><?php echo 'BRG-' . str_pad($user_id, 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><?php _e('Countries Visited', 'iburger-passport'); ?></span>
                            <span class="detail-value"><?php echo $unique_count; ?></span>
                        </div>
                    </div>
                    <div class="passport-stamp-decorative">
                        <div class="stamp-circle">
                            <span>BURGER</span>
                            <span>TRAVELER</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visa/Stamp Pages -->
            <?php 
            $page_num = 1;
            foreach ($pages as $page_index => $page_countries): 
            ?>
                <div class="passport-page stamps-page <?php echo $page_index % 2 === 0 ? 'page-right' : 'page-left'; ?>" data-page="<?php echo $page_num; ?>">
                    <div class="page-header">
                        <span class="page-number"><?php echo $page_num; ?></span>
                        <span class="page-title"><?php _e('VISAS', 'iburger-passport'); ?></span>
                    </div>
                    <div class="stamps-grid">
                        <?php foreach ($page_countries as $country): 
                            $country_id = $country->ID;
                            $flag = get_post_meta($country_id, '_flag_emoji', true);
                            $code = get_post_meta($country_id, '_country_code', true);
                            $stamp_image = get_post_meta($country_id, '_stamp_image', true);
                            $has_stamp = isset($stamps_by_country[$country_id]);
                            $stamp_count = $has_stamp ? count($stamps_by_country[$country_id]) : 0;
                            $last_visit = $has_stamp ? end($stamps_by_country[$country_id])['date'] : null;
                        ?>
                            <div class="stamp-slot <?php echo $has_stamp ? 'has-stamp stamped' : 'empty'; ?>">
                                <?php if ($has_stamp): ?>
                                    <div class="visa-stamp animated">
                                        <?php if ($stamp_image): ?>
                                            <img src="<?php echo esc_url($stamp_image); ?>" alt="<?php echo esc_attr($country->post_title); ?>" class="custom-stamp">
                                        <?php else: ?>
                                            <div class="default-stamp">
                                                <div class="stamp-border">
                                                    <div class="stamp-inner">
                                                        <span class="stamp-flag"><?php echo esc_html($flag); ?></span>
                                                        <span class="stamp-country"><?php echo esc_html($country->post_title); ?></span>
                                                        <span class="stamp-code"><?php echo esc_html($code); ?></span>
                                                        <span class="stamp-date"><?php echo date('d.m.Y', strtotime($last_visit)); ?></span>
                                                        <div class="stamp-approved">✓ <?php _e('APPROVED', 'iburger-passport'); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($stamp_count > 1): ?>
                                            <div class="visit-count">×<?php echo $stamp_count; ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-slot">
                                        <span class="empty-flag"><?php echo esc_html($flag); ?></span>
                                        <span class="empty-name"><?php echo esc_html($country->post_title); ?></span>
                                        <span class="empty-hint"><?php _e('Not visited yet', 'iburger-passport'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="page-watermark">🍔</div>
                </div>
            <?php 
                $page_num++;
            endforeach; 
            ?>

            <!-- Rewards Page -->
            <div class="passport-page rewards-page page-right" data-page="rewards">
                <div class="page-header">
                    <span class="page-title">🎁 <?php _e('REWARDS', 'iburger-passport'); ?></span>
                </div>
                <div class="rewards-content">
                    <?php if (!empty($user_coupons) && is_array($user_coupons)): ?>
                        <h3><?php _e('Your Reward Coupons', 'iburger-passport'); ?></h3>
                        <div class="coupons-list">
                            <?php foreach ($user_coupons as $coupon): ?>
                                <div class="coupon-card">
                                    <div class="coupon-icon">🎟️</div>
                                    <div class="coupon-details">
                                        <div class="coupon-code"><?php echo esc_html($coupon['code']); ?></div>
                                        <div class="coupon-expires"><?php _e('Expires:', 'iburger-passport'); ?> <?php echo date('M j, Y', strtotime($coupon['expires'])); ?></div>
                                    </div>
                                    <button class="copy-coupon" data-code="<?php echo esc_attr($coupon['code']); ?>">
                                        <?php _e('Copy', 'iburger-passport'); ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-rewards-yet">
                            <div class="reward-lock">🔒</div>
                            <p><?php printf(__('Collect %d unique country stamps to unlock your first reward!', 'iburger-passport'), $stamps_required); ?></p>
                            <div class="reward-preview">
                                <?php 
                                $reward_product_id = get_option('iburger_reward_product', 0);
                                if ($reward_product_id):
                                    $reward_product = wc_get_product($reward_product_id);
                                    if ($reward_product):
                                ?>
                                    <div class="preview-label"><?php _e('Your Reward', 'iburger-passport'); ?></div>
                                    <div class="preview-product">
                                        <?php echo $reward_product->get_image('thumbnail'); ?>
                                        <span class="preview-name"><?php echo esc_html($reward_product->get_name()); ?></span>
                                        <span class="preview-free"><?php _e('FREE!', 'iburger-passport'); ?></span>
                                    </div>
                                <?php 
                                    endif;
                                endif; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Back Cover -->
            <div class="passport-page cover back-cover" data-page="back">
                <div class="cover-content">
                    <div class="back-stamp">
                        <div class="circular-text">
                            <?php _e('BURGER WORLD PASSPORT • OFFICIAL DOCUMENT •', 'iburger-passport'); ?>
                        </div>
                    </div>
                    <div class="thank-you">
                        <?php _e('Thank you for traveling the burger world with us!', 'iburger-passport'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Navigation -->
        <div class="passport-nav">
            <button class="nav-btn prev-btn" id="prevPage">
                <span class="nav-arrow">←</span>
                <span class="nav-text"><?php _e('Previous', 'iburger-passport'); ?></span>
            </button>
            <div class="page-indicator">
                <span id="currentPageNum">Cover</span>
            </div>
            <button class="nav-btn next-btn" id="nextPage">
                <span class="nav-text"><?php _e('Next', 'iburger-passport'); ?></span>
                <span class="nav-arrow">→</span>
            </button>
        </div>
    </div>

    <!-- Add Order Form -->
    <div class="add-order-section">
        <h3><?php _e('Add Order to Passport', 'iburger-passport'); ?></h3>
        <p><?php _e('Have a completed order? Enter your order number to add stamps to your passport.', 'iburger-passport'); ?></p>
        <form id="addOrderForm" class="add-order-form">
            <div class="form-group">
                <input type="text" id="orderNumber" name="order_id" placeholder="<?php _e('Enter Order Number (e.g., 12345)', 'iburger-passport'); ?>" required>
                <button type="submit" class="submit-btn">
                    <span class="btn-text"><?php _e('Verify & Add Stamps', 'iburger-passport'); ?></span>
                    <span class="btn-loading">⏳</span>
                </button>
            </div>
            <div id="orderMessage" class="order-message"></div>
        </form>
    </div>

    <!-- Reward Modal -->
    <div class="reward-modal" id="rewardModal">
        <div class="modal-content">
            <div class="modal-close" id="closeModal">×</div>
            <div class="reward-celebration">
                <div class="confetti"></div>
                <div class="reward-icon-large">🎉</div>
                <h2><?php _e('Congratulations!', 'iburger-passport'); ?></h2>
                <p class="reward-message"></p>
                <div class="reward-coupon-display">
                    <span class="coupon-label"><?php _e('Your Coupon Code:', 'iburger-passport'); ?></span>
                    <span class="coupon-code-display"></span>
                    <button class="copy-coupon-btn"><?php _e('Copy Code', 'iburger-passport'); ?></button>
                </div>
                <p class="reward-expires"></p>
                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="shop-now-btn"><?php _e('Shop Now', 'iburger-passport'); ?></a>
            </div>
        </div>
    </div>

    <!-- New Stamp Animation -->
    <div class="stamp-animation-overlay" id="stampOverlay">
        <div class="stamp-animation">
            <div class="stamp-thud">
                <div class="new-stamp-content"></div>
            </div>
        </div>
    </div>
</div>

