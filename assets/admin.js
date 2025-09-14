jQuery(document).ready(function($) {
    // Role Manager Admin JavaScript
    
    // Handle role selector change
    $('#fp_role_selector').on('change', function() {
        var selectedRole = $(this).val();
        
        if (selectedRole) {
            // Redirect to the page with role parameter
            var currentUrl = window.location.href.split('?')[0];
            var newUrl = currentUrl + '?page=fp-role-manager&role=' + encodeURIComponent(selectedRole);
            window.location.href = newUrl;
        }
    });
    
    // Toggle all checkboxes functionality
    $('.fp-role-manager-toggle-all').on('change', function() {
        var target = $(this).data('target');
        var isChecked = $(this).is(':checked');
        
        $('input[name="' + target + '"]').prop('checked', isChecked);
    });
    
    // Show/hide role sections
    $('.fp-role-selector').on('change', function() {
        var selectedRole = $(this).val();
        
        $('.fp-role-section').hide();
        $('.fp-role-section[data-role="' + selectedRole + '"]').show();
    });
    
    // Initialize tooltips if available
    if (typeof $.fn.tooltip !== 'undefined') {
        $('.fp-tooltip').tooltip();
    }
    
    // Form validation
    $('#fp-role-manager-form').on('submit', function(e) {
        var selectedRole = $('#fp_role_selector').val();
        
        if (!selectedRole) {
            alert('Seleziona un ruolo per configurare i permessi.');
            e.preventDefault();
            return false;
        }
        
        // Check if at least one menu is selected
        var hasMenuSelection = false;
        $('input[name*="[allowed_menus]"]:checked').each(function() {
            hasMenuSelection = true;
            return false;
        });
        
        if (!hasMenuSelection) {
            if (!confirm('Nessun menu selezionato. Il ruolo non avrà accesso a nessun menu amministrativo. Continuare?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});