/**
 * iBurger Passport Loyalty - Frontend JavaScript
 * Handles passport animations, page navigation, and AJAX interactions
 */

(function($) {
    'use strict';

    // Passport Controller
    const IBurgerPassport = {
        currentPage: 0,
        totalPages: 0,
        pages: [],
        isAnimating: false,

        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.initializePassport();
        },

        cacheElements: function() {
            this.$wrapper = $('.iburger-passport-wrapper');
            this.$passportBook = $('#passportBook');
            this.$pages = this.$passportBook.find('.passport-page');
            this.$prevBtn = $('#prevPage');
            this.$nextBtn = $('#nextPage');
            this.$pageIndicator = $('#currentPageNum');
            this.$orderForm = $('#addOrderForm');
            this.$orderInput = $('#orderNumber');
            this.$orderMessage = $('#orderMessage');
            this.$claimRewardBtn = $('#claimRewardBtn');
            this.$rewardModal = $('#rewardModal');
            this.$closeModal = $('#closeModal');
            this.$stampOverlay = $('#stampOverlay');
        },

        bindEvents: function() {
            const self = this;

            // Page navigation
            this.$prevBtn.on('click', function() {
                self.navigatePage('prev');
            });

            this.$nextBtn.on('click', function() {
                self.navigatePage('next');
            });

            // Front cover click to open
            this.$passportBook.find('.front-cover').on('click', function() {
                self.navigatePage('next');
            });

            // Keyboard navigation
            $(document).on('keydown', function(e) {
                if (!self.$wrapper.is(':visible')) return;
                
                if (e.key === 'ArrowLeft') {
                    self.navigatePage('prev');
                } else if (e.key === 'ArrowRight') {
                    self.navigatePage('next');
                }
            });

            $(window).on('resize', () => {
                this.updateBookHeight();
            });

            // Touch/swipe support
            let touchStartX = 0;
            let touchEndX = 0;

            this.$passportBook.on('touchstart', function(e) {
                touchStartX = e.originalEvent.changedTouches[0].screenX;
            });

            this.$passportBook.on('touchend', function(e) {
                touchEndX = e.originalEvent.changedTouches[0].screenX;
                self.handleSwipe(touchStartX, touchEndX);
            });

            // Order form submission
            this.$orderForm.on('submit', function(e) {
                e.preventDefault();
                self.verifyOrder();
            });

            // Claim reward button
            this.$claimRewardBtn.on('click', function() {
                self.claimReward();
            });

            // Close modal
            this.$closeModal.on('click', function() {
                self.closeModal();
            });

            this.$rewardModal.on('click', function(e) {
                if ($(e.target).is('.reward-modal')) {
                    self.closeModal();
                }
            });

            // Copy coupon buttons
            $(document).on('click', '.copy-coupon, .copy-coupon-btn', function() {
                const code = $(this).data('code') || $('.coupon-code-display').text();
                self.copyToClipboard(code, $(this));
            });
        },

        initializePassport: function() {
            // Build pages array
            this.pages = [];
            this.$pages.each((index, page) => {
                this.pages.push($(page));
            });
            this.totalPages = this.pages.length;

            // Set initial state - show cover
            this.$pages.removeClass('active').addClass('hidden');
            this.pages[0].removeClass('hidden').addClass('active');
            this.currentPage = 0;

            this.updateNavigation();
            this.updateBookHeight();

            // Animate stamps on page load
            this.animateStampsOnLoad();
        },

        updateBookHeight: function() {
            const $activePage = this.$pages.filter('.active');
            if ($activePage.length) {
                // Get height of content to ensure book expands
                const $content = $activePage.find('.page-content, .cover-content, .rewards-content');
                const height = $content.length ? $content.outerHeight() : $activePage.outerHeight();
                
                // Add buffer for shadows/transform
                this.$passportBook.css('min-height', (height + 20) + 'px');
            }
        },

        navigatePage: function(direction) {
            if (this.isAnimating) return;

            let newPage = this.currentPage;

            if (direction === 'prev' && this.currentPage > 0) {
                newPage = this.currentPage - 1;
            } else if (direction === 'next' && this.currentPage < this.totalPages - 1) {
                newPage = this.currentPage + 1;
            } else {
                return;
            }

            this.goToPage(newPage, direction);
        },

        goToPage: function(pageIndex, direction) {
            if (pageIndex === this.currentPage || this.isAnimating) return;
            
            this.isAnimating = true;

            const currentPageEl = this.pages[this.currentPage];
            const newPageEl = this.pages[pageIndex];
            const isForward = pageIndex > this.currentPage;

            // Animate out current page
            currentPageEl.removeClass('active');
            currentPageEl.addClass(isForward ? 'flip-out-left' : 'flip-out-right');

            // Prepare and animate in new page
            newPageEl.removeClass('hidden flip-out-left flip-out-right');
            
            setTimeout(() => {
                currentPageEl.addClass('hidden').removeClass('flip-out-left flip-out-right');
                newPageEl.addClass('active');
                
                this.currentPage = pageIndex;
                this.updateNavigation();
                this.updateBookHeight();
                this.isAnimating = false;

                // Animate stamps if it's a stamps page
                if (newPageEl.hasClass('stamps-page')) {
                    this.animateStampsOnPage(newPageEl);
                }
            }, 300);

            // Play page flip sound effect (optional)
            this.playPageSound();
        },

        handleSwipe: function(startX, endX) {
            const threshold = 50;
            const diff = startX - endX;

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.navigatePage('next');
                } else {
                    this.navigatePage('prev');
                }
            }
        },

        updateNavigation: function() {
            // Update buttons
            this.$prevBtn.prop('disabled', this.currentPage === 0);
            this.$nextBtn.prop('disabled', this.currentPage === this.totalPages - 1);

            // Update page indicator
            const pageData = this.pages[this.currentPage].data('page');
            let pageText = 'Cover';

            if (pageData === 'cover') {
                pageText = 'Cover';
            } else if (pageData === 'back') {
                pageText = 'Back Cover';
            } else if (pageData === 'rewards') {
                pageText = 'Rewards';
            } else if (pageData === '0') {
                pageText = 'Personal Info';
            } else {
                pageText = 'Page ' + pageData;
            }

            this.$pageIndicator.text(pageText);
        },

        animateStampsOnLoad: function() {
            // Delay stamp animations for visual effect
            $('.visa-stamp.animated').each(function(index) {
                const $stamp = $(this);
                $stamp.css('animation-delay', (index * 0.15) + 's');
            });
        },

        animateStampsOnPage: function($page) {
            $page.find('.visa-stamp').each(function(index) {
                const $stamp = $(this);
                $stamp.removeClass('animated');
                
                setTimeout(() => {
                    $stamp.addClass('animated');
                }, index * 100);
            });
        },

        playPageSound: function() {
            // Optional: Add page flip sound
            // const audio = new Audio(iburgerPassport.pluginUrl + 'assets/sounds/page-flip.mp3');
            // audio.volume = 0.3;
            // audio.play().catch(() => {});
        },

        verifyOrder: function() {
            const orderId = this.$orderInput.val().trim();
            
            if (!orderId) {
                this.showMessage('error', 'Please enter an order number.');
                return;
            }

            const $submitBtn = this.$orderForm.find('.submit-btn');
            $submitBtn.addClass('loading').prop('disabled', true);
            this.$orderMessage.removeClass('show success error');

            $.ajax({
                url: iburgerPassport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'iburger_verify_order',
                    nonce: iburgerPassport.nonce,
                    order_id: orderId
                },
                success: (response) => {
                    $submitBtn.removeClass('loading').prop('disabled', false);

                    if (response.success) {
                        this.showMessage('success', response.data.message);
                        this.$orderInput.val('');
                        
                        // Show stamp animation for each new stamp
                        if (response.data.stamps && response.data.stamps.length > 0) {
                            this.showNewStampAnimation(response.data.stamps);
                        }

                        // Reload page after animation to show updated passport
                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    } else {
                        this.showMessage('error', response.data.message);
                    }
                },
                error: () => {
                    $submitBtn.removeClass('loading').prop('disabled', false);
                    this.showMessage('error', iburgerPassport.strings.error);
                }
            });
        },

        showMessage: function(type, message) {
            this.$orderMessage
                .removeClass('success error show')
                .addClass(type + ' show')
                .text(message);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.$orderMessage.removeClass('show');
            }, 5000);
        },

        showNewStampAnimation: function(stamps) {
            if (!stamps || stamps.length === 0) return;

            let delay = 0;
            stamps.forEach((stamp, index) => {
                setTimeout(() => {
                    this.animateSingleStamp(stamp);
                }, delay);
                delay += 1500;
            });
        },

        animateSingleStamp: function(stamp) {
            const $overlay = this.$stampOverlay;
            const $content = $overlay.find('.new-stamp-content');

            $content.html(`
                <span class="stamp-flag">${stamp.flag}</span>
                <span class="stamp-name">${stamp.name}</span>
                <span class="stamp-message">Stamp Added! 🎉</span>
            `);

            $overlay.addClass('show');

            // Play stamp sound (optional)
            // const audio = new Audio(iburgerPassport.pluginUrl + 'assets/sounds/stamp.mp3');
            // audio.play().catch(() => {});

            setTimeout(() => {
                $overlay.removeClass('show');
            }, 1200);
        },

        claimReward: function() {
            const $btn = this.$claimRewardBtn;
            $btn.text('Claiming...').prop('disabled', true);

            $.ajax({
                url: iburgerPassport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'iburger_claim_reward',
                    nonce: iburgerPassport.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showRewardModal(response.data);
                    } else {
                        alert(response.data.message);
                        $btn.text('Claim Your Free Burger!').prop('disabled', false);
                    }
                },
                error: () => {
                    alert(iburgerPassport.strings.error);
                    $btn.text('Claim Your Free Burger!').prop('disabled', false);
                }
            });
        },

        showRewardModal: function(data) {
            const $modal = this.$rewardModal;
            
            $modal.find('.reward-message').text(data.message);
            $modal.find('.coupon-code-display').text(data.coupon);
            $modal.find('.reward-expires').html(`<strong>Expires:</strong> ${data.expires}`);
            
            $modal.addClass('show');

            // Confetti effect
            this.createConfetti();
        },

        closeModal: function() {
            this.$rewardModal.removeClass('show');
            // Reload to update the page
            setTimeout(() => {
                location.reload();
            }, 300);
        },

        copyToClipboard: function(text, $button) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopySuccess($button);
                }).catch(() => {
                    this.fallbackCopy(text, $button);
                });
            } else {
                this.fallbackCopy(text, $button);
            }
        },

        fallbackCopy: function(text, $button) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                this.showCopySuccess($button);
            } catch (err) {
                alert('Copy failed. Please copy manually: ' + text);
            }
            
            document.body.removeChild(textarea);
        },

        showCopySuccess: function($button) {
            const originalText = $button.text();
            $button.text('Copied! ✓').addClass('copied');
            
            setTimeout(() => {
                $button.text(originalText).removeClass('copied');
            }, 2000);
        },

        createConfetti: function() {
            const colors = ['#C9A227', '#6B1D2B', '#C41E3A', '#1E3A5F', '#1E5F3A'];
            const confettiCount = 50;
            const $modal = this.$rewardModal.find('.modal-content');

            for (let i = 0; i < confettiCount; i++) {
                const $confetti = $('<div class="confetti-piece"></div>');
                $confetti.css({
                    position: 'absolute',
                    width: Math.random() * 10 + 5 + 'px',
                    height: Math.random() * 10 + 5 + 'px',
                    background: colors[Math.floor(Math.random() * colors.length)],
                    left: Math.random() * 100 + '%',
                    top: '-20px',
                    borderRadius: Math.random() > 0.5 ? '50%' : '0',
                    transform: 'rotate(' + Math.random() * 360 + 'deg)',
                    animation: `confetti-fall-${i % 3} ${Math.random() * 2 + 1}s ease-out forwards`,
                    animationDelay: Math.random() * 0.5 + 's'
                });
                
                $modal.append($confetti);

                setTimeout(() => {
                    $confetti.remove();
                }, 3000);
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.iburger-passport-wrapper').length) {
            IBurgerPassport.init();
        }
    });

})(jQuery);

