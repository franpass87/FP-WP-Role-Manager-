jQuery(document).ready(function($) {
    // Role Manager Admin JavaScript
    
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
        var hasSelection = false;
        
        $('input[type="checkbox"]:checked').each(function() {
            if ($(this).attr('name').indexOf('allowed_menus') !== -1) {
                hasSelection = true;
                return false;
            }
        });
        
        if (!hasSelection) {
            alert('Seleziona almeno un menu per il ruolo.');
            e.preventDefault();
            return false;
        }
    });
});