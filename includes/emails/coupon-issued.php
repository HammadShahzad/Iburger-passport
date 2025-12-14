<?php
/**
 * Coupon Issued Email Template
 * 
 * @var string $customer_name
 * @var string $coupon_code
 * @var string $product_name
 * @var string $expires_date
 * @var string $shop_url
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
    <title><?php echo esc_html($site_name); ?> - Your Reward Coupon!</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, <?php echo $primary_color; ?> 0%, #004d00 100%); padding: 40px 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🎁</div>
                            <h1 style="color: <?php echo $accent_color; ?>; font-size: 28px; margin: 0; font-weight: 700; letter-spacing: 2px;">YOUR REWARD IS HERE!</h1>
                            <p style="color: #ffffff; font-size: 14px; margin: 12px 0 0; opacity: 0.9;">Use this coupon for your free item</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #333; font-size: 16px; line-height: 1.6; margin: 0 0 32px; text-align: center;">
                                Hi <strong><?php echo esc_html($customer_name); ?></strong>, here's your reward coupon!
                            </p>
                            
                            <!-- Coupon Box -->
                            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 3px dashed <?php echo $accent_color; ?>; border-radius: 20px; padding: 36px; text-align: center; margin-bottom: 32px; position: relative;">
                                <div style="color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px;">Your Coupon Code</div>
                                
                                <!-- Coupon Code -->
                                <div style="background: #ffffff; border-radius: 12px; padding: 20px 32px; display: inline-block; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                    <span style="font-family: 'Courier New', monospace; font-size: 28px; font-weight: 700; color: <?php echo $primary_color; ?>; letter-spacing: 3px;">
                                        <?php echo esc_html($coupon_code); ?>
                                    </span>
                                </div>
                                
                                <div style="color: <?php echo $primary_color; ?>; font-size: 18px; font-weight: 600; margin-bottom: 8px;">
                                    FREE: <?php echo esc_html($product_name); ?>
                                </div>
                                
                                <div style="color: #666; font-size: 13px;">
                                    100% off • One-time use
                                </div>
                            </div>
                            
                            <!-- Expiry Warning -->
                            <div style="background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px; padding: 16px 20px; margin-bottom: 32px;">
                                <div style="color: #dc2626; font-size: 14px; font-weight: 600;">
                                    ⏰ Expires: <?php echo esc_html($expires_date); ?>
                                </div>
                                <div style="color: #666; font-size: 13px; margin-top: 4px;">
                                    Don't forget to use your coupon before it expires!
                                </div>
                            </div>
                            
                            <!-- How to Use -->
                            <div style="background: #f9f9f9; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
                                <div style="color: <?php echo $primary_color; ?>; font-size: 14px; font-weight: 600; margin-bottom: 16px;">HOW TO USE:</div>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="30" style="color: <?php echo $accent_color; ?>; font-weight: 700; font-size: 16px; vertical-align: top;">1.</td>
                                        <td style="color: #555; font-size: 14px; padding-bottom: 12px;">Add <strong><?php echo esc_html($product_name); ?></strong> to your cart</td>
                                    </tr>
                                    <tr>
                                        <td width="30" style="color: <?php echo $accent_color; ?>; font-weight: 700; font-size: 16px; vertical-align: top;">2.</td>
                                        <td style="color: #555; font-size: 14px; padding-bottom: 12px;">Enter coupon code at checkout</td>
                                    </tr>
                                    <tr>
                                        <td width="30" style="color: <?php echo $accent_color; ?>; font-weight: 700; font-size: 16px; vertical-align: top;">3.</td>
                                        <td style="color: #555; font-size: 14px;">Enjoy your FREE burger! 🍔</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo esc_url($shop_url); ?>" style="display: inline-block; background: linear-gradient(135deg, <?php echo $accent_color; ?> 0%, #B8860B 100%); color: white; text-decoration: none; padding: 18px 48px; border-radius: 50px; font-size: 16px; font-weight: 700; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(201, 162, 39, 0.4);">
                                            SHOP NOW →
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

