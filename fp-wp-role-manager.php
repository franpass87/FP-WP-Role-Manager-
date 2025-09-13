<?php
/**
 * Plugin Name: FP WordPress Role Manager
 * Description: Plugin semplice per gestire i ruoli WordPress e controllare cosa possono vedere nell'admin.
 * Version: 1.0.0
 * Author: Francesco Passarella
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation - create restaurant manager role
register_activation_hook(__FILE__, 'fp_create_restaurant_manager_role');
function fp_create_restaurant_manager_role() {
    if (!get_role('restaurant_manager')) {
        add_role('restaurant_manager', 'Restaurant Manager', array(
            'read' => true,
            'edit_posts' => true,
            'upload_files' => true,
        ));
    }
    
    // Default settings
    if (!get_option('fp_role_manager_menus')) {
        update_option('fp_role_manager_menus', array('edit.php', 'upload.php'));
    }
}

// Add admin menu
add_action('admin_menu', 'fp_add_role_manager_menu');
function fp_add_role_manager_menu() {
    add_options_page('Role Manager', 'Role Manager', 'manage_options', 'fp-role-manager', 'fp_role_manager_page');
}

// Filter admin menu for restaurant managers
add_action('admin_menu', 'fp_filter_admin_menu', 999);
function fp_filter_admin_menu() {
    $user = wp_get_current_user();
    
    // Skip if user is admin
    if (in_array('administrator', $user->roles)) {
        return;
    }
    
    // Only filter for restaurant managers
    if (!in_array('restaurant_manager', $user->roles)) {
        return;
    }
    
    global $menu;
    $allowed_menus = get_option('fp_role_manager_menus', array());
    
    foreach ($menu as $key => $menu_item) {
        if (isset($menu_item[2])) {
            $menu_slug = $menu_item[2];
            
            // Always keep dashboard and this plugin
            if ($menu_slug === 'index.php' || $menu_slug === 'options-general.php') {
                continue;
            }
            
            // Remove if not in allowed list
            if (!in_array($menu_slug, $allowed_menus)) {
                unset($menu[$key]);
            }
        }
    }
}

// Save settings
add_action('admin_post_fp_save_settings', 'fp_save_role_settings');
function fp_save_role_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('Non autorizzato');
    }
    
    $allowed_menus = isset($_POST['allowed_menus']) ? $_POST['allowed_menus'] : array();
    update_option('fp_role_manager_menus', $allowed_menus);
    
    wp_redirect(add_query_arg('updated', '1', admin_url('options-general.php?page=fp-role-manager')));
    exit;
}

// Admin page
function fp_role_manager_page() {
    $allowed_menus = get_option('fp_role_manager_menus', array());
    ?>
    <div class="wrap">
        <h1>Role Manager</h1>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="notice notice-success"><p>Impostazioni salvate!</p></div>
        <?php endif; ?>
        
        <p>Seleziona i menu che il Restaurant Manager può vedere:</p>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="fp_save_settings">
            <?php wp_nonce_field('fp_save_settings'); ?>
            
            <?php
            $menus = array(
                'edit.php' => 'Posts',
                'upload.php' => 'Media',
                'edit.php?post_type=page' => 'Pagine',
                'edit-comments.php' => 'Commenti',
                'themes.php' => 'Aspetto',
                'plugins.php' => 'Plugin',
                'users.php' => 'Utenti',
                'tools.php' => 'Strumenti',
            );
            
            foreach ($menus as $slug => $name) {
                $checked = in_array($slug, $allowed_menus) ? 'checked' : '';
                echo "<p><label><input type='checkbox' name='allowed_menus[]' value='{$slug}' {$checked}> {$name}</label></p>";
            }
            ?>
            
            <?php submit_button('Salva'); ?>
        </form>
    </div>
    <?php
}