/**
 * iBurger Passport Loyalty - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Media uploader for stamp image
        var mediaUploader;
        
        $('#upload_stamp_image').on('click', function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'Select Stamp Image',
                button: {
                    text: 'Use This Image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#stamp_image').val(attachment.url);
                $('#stamp_image_preview').html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto; border-radius: 8px;">');
                $('#remove_stamp_image').show();
            });
            
            mediaUploader.open();
        });
        
        $('#remove_stamp_image').on('click', function(e) {
            e.preventDefault();
            $('#stamp_image').val('');
            $('#stamp_image_preview').html('');
            $(this).hide();
        });

        // Enhance select boxes for products
        if (typeof $.fn.select2 !== 'undefined') {
            $('#linked_products, #reward_product').select2({
                placeholder: 'Select products...',
                allowClear: true,
                width: '100%'
            });
        }

        // Confirm before deleting country
        $('.row-actions .delete a').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this burger country? Customers who have this stamp will keep it.')) {
                e.preventDefault();
            }
        });

        // Copy shortcode to clipboard
        $('.iburger-shortcode-info code').on('click', function() {
            var code = $(this).text();
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function() {
                    showCopyNotice('Shortcode copied to clipboard!');
                });
            } else {
                // Fallback
                var $temp = $('<input>');
                $('body').append($temp);
                $temp.val(code).select();
                document.execCommand('copy');
                $temp.remove();
                showCopyNotice('Shortcode copied to clipboard!');
            }
        });

        function showCopyNotice(message) {
            var $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
            $('.iburger-shortcode-info').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 2000);
        }

        // Animate stat numbers on page load
        $('.stat-number').each(function() {
            var $this = $(this);
            var countTo = parseInt($this.text());
            
            if (isNaN(countTo)) return;
            
            $this.text('0');
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(countTo);
                }
            });
        });

        // Country card hover effect
        $('.country-card').on('mouseenter', function() {
            $(this).find('.country-flag').css('transform', 'scale(1.2) rotate(5deg)');
        }).on('mouseleave', function() {
            $(this).find('.country-flag').css('transform', 'scale(1) rotate(0deg)');
        });

        // Add transition to flag
        $('.country-flag').css('transition', 'transform 0.3s ease');

    });

})(jQuery);


