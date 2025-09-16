/* PACWP Classifieds JavaScript */

jQuery(document).ready(function($) {
    
    // Search form functionality
    $('.pacwp-search-form').on('submit', function(e) {
        var hasValue = false;
        
        $(this).find('input[type="text"], select').each(function() {
            if ($(this).val() !== '') {
                hasValue = true;
                return false;
            }
        });
        
        if (!hasValue) {
            e.preventDefault();
            alert('Please enter at least one search criteria.');
        }
    });
    
    // Submit form validation
    $('.pacwp-submit-form form').on('submit', function(e) {
        var title = $('#pacwp_title').val().trim();
        var description = $('#pacwp_description').val().trim();
        var email = $('#pacwp_contact_email').val().trim();
        var errors = [];
        
        if (title === '') {
            errors.push('Title is required.');
        }
        
        if (description === '') {
            errors.push('Description is required.');
        }
        
        if (email === '') {
            errors.push('Contact email is required.');
        } else if (!isValidEmail(email)) {
            errors.push('Please enter a valid email address.');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Please fix the following errors:\n' + errors.join('\n'));
        }
    });
    
    // Email validation function
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Auto-hide success/error messages after 5 seconds
    $('.pacwp-message').delay(5000).fadeOut('slow');
    
    // Character counter for description field
    $('#pacwp_description').on('input', function() {
        var maxLength = 2000;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        var counterElement = $('#char-counter');
        if (counterElement.length === 0) {
            $(this).after('<div id="char-counter" style="font-size: 11px; color: #666; margin-top: 5px;"></div>');
            counterElement = $('#char-counter');
        }
        
        counterElement.text(remaining + ' characters remaining');
        
        if (remaining < 50) {
            counterElement.css('color', '#ff0000');
        } else if (remaining < 100) {
            counterElement.css('color', '#ff8800');
        } else {
            counterElement.css('color', '#666');
        }
        
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, maxLength));
            counterElement.text('0 characters remaining').css('color', '#ff0000');
        }
    });
    
    // Price formatting
    $('#pacwp_price').on('input', function() {
        var value = $(this).val().replace(/[^\d.]/g, '');
        var parts = value.split('.');
        
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        $(this).val(value);
    });
    
    // Phone number formatting
    $('#pacwp_contact_phone').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        
        $(this).val(value);
    });
    
    // Toggle additional fields based on listing type
    $('#pacwp_listing_type').on('change', function() {
        var listingType = $(this).val();
        var priceRow = $('#pacwp_price').closest('tr');
        
        if (listingType === 'job' || listingType === 'service') {
            priceRow.find('th label').text('Rate/Salary ($)');
        } else if (listingType === 'housing') {
            priceRow.find('th label').text('Rent/Price ($)');
        } else {
            priceRow.find('th label').text('Price ($)');
        }
    });
    
    // Image upload preview (if file input exists)
    $('input[type="file"]').on('change', function() {
        var file = this.files[0];
        var preview = $('#image-preview');
        
        if (preview.length === 0) {
            $(this).after('<div id="image-preview" style="margin-top: 10px;"></div>');
            preview = $('#image-preview');
        }
        
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px; border: 1px solid #ccc;">');
            };
            reader.readAsDataURL(file);
        } else {
            preview.empty();
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 20
            }, 500);
        }
    });
    
    // Simple modal for contact info (if modal elements exist)
    $('.show-contact').on('click', function(e) {
        e.preventDefault();
        
        var contactInfo = $(this).data('contact');
        var modal = $('#contact-modal');
        
        if (modal.length === 0) {
            $('body').append('<div id="contact-modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 2px solid #000; z-index: 1000;"><div id="modal-content"></div><button id="close-modal" style="margin-top: 10px;">Close</button></div>');
            $('body').append('<div id="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;"></div>');
            modal = $('#contact-modal');
        }
        
        $('#modal-content').html(contactInfo);
        $('#modal-overlay, #contact-modal').fadeIn();
        
        $('#close-modal, #modal-overlay').on('click', function() {
            $('#modal-overlay, #contact-modal').fadeOut();
        });
    });
    
    // Auto-refresh listings every 5 minutes (for live updates)
    if ($('.pacwp-listings').length > 0) {
        setInterval(function() {
            // Only refresh if user hasn't interacted recently
            if (Date.now() - lastUserActivity > 300000) { // 5 minutes
                location.reload();
            }
        }, 300000); // 5 minutes
    }
    
    // Track user activity
    var lastUserActivity = Date.now();
    $(document).on('mousemove keypress scroll', function() {
        lastUserActivity = Date.now();
    });
    
});

// Utility functions available globally
window.PACWP = {
    formatPrice: function(price) {
        return '$' + parseFloat(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    showMessage: function(message, type) {
        var messageClass = type === 'error' ? 'error' : 'success';
        var messageHtml = '<div class="pacwp-message ' + messageClass + '">' + message + '</div>';
        
        $('.pacwp-submit-form, .pacwp-listings').first().prepend(messageHtml);
        
        setTimeout(function() {
            $('.pacwp-message').fadeOut('slow');
        }, 5000);
    }
};