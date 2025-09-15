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
    
    // Role creation form validation
    $('#fp-create-role-form').on('submit', function(e) {
        var roleName = $('#fp_new_role_name').val().trim();
        var displayName = $('#fp_new_role_display_name').val().trim();
        
        if (!roleName) {
            alert('Il nome del ruolo è obbligatorio.');
            e.preventDefault();
            return false;
        }
        
        // Validate role name format
        var roleNamePattern = /^[a-z0-9_]+$/;
        if (!roleNamePattern.test(roleName)) {
            alert('Il nome del ruolo può contenere solo lettere minuscole, numeri e underscore.');
            e.preventDefault();
            return false;
        }
        
        if (roleName.length < 3) {
            alert('Il nome del ruolo deve essere di almeno 3 caratteri.');
            e.preventDefault();
            return false;
        }
        
        // Confirm role creation
        var confirmMsg = 'Creare il nuovo ruolo "' + (displayName || roleName) + '"?';
        if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Role configuration form validation
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
    
    // Auto-format role name input (replace spaces with underscores, convert to lowercase)
    $('#fp_new_role_name').on('input', function() {
        var value = $(this).val();
        var formatted = value.toLowerCase()
                            .replace(/[^a-z0-9_\s]/g, '')
                            .replace(/\s+/g, '_');
        $(this).val(formatted);
    });
});