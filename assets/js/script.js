jQuery(document).ready(function($) {
    
    // Handle listing submission form
    $('#pacwp-submit-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('input[type="submit"]');
        var messageDiv = $('#pacwp-form-message');
        
        // Disable submit button
        submitBtn.prop('disabled', true).val('Submitting...');
        
        // Clear previous messages
        messageDiv.removeClass('success error').empty();
        
        // Collect form data
        var formData = {
            action: 'submit_listing',
            pacwp_nonce: form.find('#pacwp_nonce').val(),
            listing_title: form.find('#listing_title').val(),
            listing_description: form.find('#listing_description').val(),
            listing_category: form.find('#listing_category').val(),
            listing_location: form.find('#listing_location').val(),
            listing_price: form.find('#listing_price').val(),
            listing_contact_email: form.find('#listing_contact_email').val(),
            listing_contact_phone: form.find('#listing_contact_phone').val(),
            listing_address: form.find('#listing_address').val()
        };
        
        // Submit via AJAX
        $.ajax({
            type: 'POST',
            url: pacwp_ajax.ajax_url,
            data: formData,
            success: function(response) {
                if (response.success) {
                    messageDiv.addClass('success').text(response.data);
                    form[0].reset(); // Clear form
                } else {
                    messageDiv.addClass('error').text(response.data);
                }
            },
            error: function() {
                messageDiv.addClass('error').text('An error occurred. Please try again.');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).val('Submit Listing');
            }
        });
    });
    
    // Live search functionality
    if ($('#pacwp-search-form').length) {
        var searchTimer;
        
        $('#pacwp-search-input').on('keyup', function() {
            clearTimeout(searchTimer);
            var searchTerm = $(this).val();
            
            if (searchTerm.length >= 3) {
                searchTimer = setTimeout(function() {
                    performSearch(searchTerm);
                }, 500);
            } else if (searchTerm.length === 0) {
                // Reset to show all listings
                performSearch('');
            }
        });
        
        // Category and location filter change
        $('#pacwp-category-filter, #pacwp-location-filter').on('change', function() {
            var searchTerm = $('#pacwp-search-input').val();
            performSearch(searchTerm);
        });
    }
    
    function performSearch(searchTerm) {
        var category = $('#pacwp-category-filter').val();
        var location = $('#pacwp-location-filter').val();
        
        var searchData = {
            action: 'search_listings',
            search: searchTerm,
            category: category,
            location: location,
            nonce: pacwp_ajax.nonce
        };
        
        $.ajax({
            type: 'POST',
            url: pacwp_ajax.ajax_url,
            data: searchData,
            beforeSend: function() {
                $('.pacwp-listings').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    $('.pacwp-listings').html(response.data);
                }
            },
            complete: function() {
                $('.pacwp-listings').removeClass('loading');
            }
        });
    }
    
    // Form validation
    function validateForm(form) {
        var isValid = true;
        var requiredFields = form.find('[required]');
        
        requiredFields.each(function() {
            var field = $(this);
            if (field.val().trim() === '') {
                field.addClass('error');
                isValid = false;
            } else {
                field.removeClass('error');
            }
        });
        
        // Email validation
        var emailField = form.find('input[type="email"]');
        if (emailField.length && emailField.val()) {
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailField.val())) {
                emailField.addClass('error');
                isValid = false;
            } else {
                emailField.removeClass('error');
            }
        }
        
        return isValid;
    }
    
    // Add error styling for validation
    $('<style>')
        .prop('type', 'text/css')
        .html('.error { border-color: #dc3545 !important; background-color: #f8d7da; }')
        .appendTo('head');
    
    // Image upload handling (if images are supported)
    if ($('#listing-images').length) {
        $('#listing-images').on('change', function() {
            var files = this.files;
            var preview = $('#image-preview');
            
            preview.empty();
            
            for (var i = 0; i < files.length && i < 5; i++) {
                var file = files[i];
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = $('<img>').attr('src', e.target.result).css({
                            'max-width': '150px',
                            'max-height': '150px',
                            'margin': '5px',
                            'border': '1px solid #ddd'
                        });
                        preview.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    }
    
    // Auto-complete for location field (if Google Places API is available)
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
        var addressInput = document.getElementById('listing_address');
        if (addressInput) {
            var autocomplete = new google.maps.places.Autocomplete(addressInput);
            autocomplete.setFields(['formatted_address', 'geometry']);
        }
    }
});

// Add loading animation CSS
jQuery(document).ready(function($) {
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .pacwp-listings.loading {
                opacity: 0.6;
                position: relative;
            }
            .pacwp-listings.loading::after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 32px;
                height: 32px;
                margin-top: -16px;
                margin-left: -16px;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #0645AD;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `)
        .appendTo('head');
});