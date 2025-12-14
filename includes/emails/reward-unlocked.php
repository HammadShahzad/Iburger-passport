<?php
/**
 * Reward Unlocked Email Template
 * 
 * @var string $customer_name
 * @var int $total_countries
 * @var string $passport_url
 */

if (!defined('ABSPATH')) exit;

$site_name = get_bloginfo('name');
$primary_color = '#006400';
$accent_color = '#C9A227';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?> - Reward Unlocked!</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <!-- Header - Celebration -->
                    <tr>
                        <td style="background: linear-gradient(135deg, <?php echo $accent_color; ?> 0%, #B8860B 100%); padding: 50px 40px; text-align: center;">
                            <div style="font-size: 64px; margin-bottom: 20px;">🎉</div>
                            <h1 style="color: #ffffff; font-size: 32px; margin: 0; font-weight: 700; letter-spacing: 3px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">CONGRATULATIONS!</h1>
                            <p style="color: #ffffff; font-size: 16px; margin: 16px 0 0; opacity: 0.95;">You've completed your Burger Passport!</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #333; font-size: 18px; line-height: 1.6; margin: 0 0 24px; text-align: center;">
                                Hi <strong><?php echo esc_html($customer_name); ?></strong>,
                            </p>
                            
                            <!-- Achievement Box -->
                            <div style="background: linear-gradient(135deg, <?php echo $primary_color; ?> 0%, #004d00 100%); border-radius: 16px; padding: 32px; text-align: center; margin-bottom: 32px;">
                                <div style="font-size: 48px; margin-bottom: 16px;">🏆</div>
                                <div style="color: <?php echo $accent_color; ?>; font-size: 14px; font-weight: 600; letter-spacing: 2px; margin-bottom: 8px;">ACHIEVEMENT UNLOCKED</div>
                                <div style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 8px;">
                                    All <?php echo intval($total_countries); ?> Countries Collected!
                                </div>
                                <div style="color: rgba(255,255,255,0.8); font-size: 14px;">
                                    You're a true Burger World Traveler
                                </div>
                            </div>
                            
                            <!-- Reward Info -->
                            <div style="background: #fffbeb; border: 2px dashed <?php echo $accent_color; ?>; border-radius: 16px; padding: 28px; text-align: center; margin-bottom: 32px;">
                                <div style="font-size: 36px; margin-bottom: 12px;">🍔</div>
                                <div style="color: <?php echo $primary_color; ?>; font-size: 20px; font-weight: 700; margin-bottom: 8px;">
                                    FREE REWARD WAITING!
                                </div>
                                <p style="color: #666; font-size: 14px; margin: 0;">
                                    Visit your Burger Passport to claim your free product.
                                </p>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo esc_url($passport_url); ?>" style="display: inline-block; background: linear-gradient(135deg, <?php echo $accent_color; ?> 0%, #B8860B 100%); color: white; text-decoration: none; padding: 18px 48px; border-radius: 50px; font-size: 18px; font-weight: 700; letter-spacing: 2px; box-shadow: 0 4px 15px rgba(201, 162, 39, 0.4);">
                                            CLAIM MY REWARD →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f9f9f9; padding: 30px 40px; text-align: center; border-top: 1px solid #eee;">
                            <p style="color: #888; font-size: 13px; margin: 0 0 8px;">
                                Thank you for being a loyal customer! 🙏
                            </p>
                            <p style="color: #aaa; font-size: 12px; margin: 0;">
                                <?php echo esc_html($site_name); ?> • Burger Passport Loyalty Program
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

