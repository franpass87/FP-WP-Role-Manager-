<?php
/**
 * Plugin Name: FP WordPress Role Manager
 * Plugin URI: https://github.com/franpass87/FP-WP-Role-Manager-
 * Description: Gestione avanzata dei ruoli WordPress per controllare la visibilità dei menu amministrativi e l'accesso ai plugin. Configura quali sezioni possono vedere specifici ruoli utente.
 * Version: 1.0
 * Author: Francesco Passeri
 * License: GPL v2 or later
 * Text Domain: fp-wp-role-manager
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FP_ROLE_MANAGER_VERSION', '1.0');
define('FP_ROLE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FP_ROLE_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FP_ROLE_MANAGER_PLUGIN_FILE', __FILE__);

// Main plugin class
class FP_WP_Role_Manager {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_menu', array($this, 'filter_admin_menu'), 999);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Initialize empty settings if they don't exist
        if (!get_option('fp_role_manager_settings')) {
            $default_settings = array();
            update_option('fp_role_manager_settings', $default_settings);
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain('fp-wp-role-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Register settings
        register_setting('fp_role_manager_settings', 'fp_role_manager_settings');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on our settings page
        if ($hook !== 'tools_page_fp-role-manager') {
            return;
        }
        
        wp_enqueue_style(
            'fp-role-manager-admin',
            FP_ROLE_MANAGER_PLUGIN_URL . 'assets/admin.css',
            array(),
            FP_ROLE_MANAGER_VERSION
        );
        
        wp_enqueue_script(
            'fp-role-manager-admin',
            FP_ROLE_MANAGER_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            FP_ROLE_MANAGER_VERSION,
            true
        );
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_management_page(
            __('Role Manager', 'fp-wp-role-manager'),
            __('Role Manager', 'fp-wp-role-manager'),
            'manage_options',
            'fp-role-manager',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Filter admin menu based on user role
     */
    public function filter_admin_menu() {
        global $menu, $submenu;
        
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        
        // Don't filter for administrators
        if (in_array('administrator', $user_roles)) {
            return;
        }
        
        $settings = get_option('fp_role_manager_settings', array());
        
        foreach ($user_roles as $role) {
            if (isset($settings[$role])) {
                // Filter by allowed menus
                if (isset($settings[$role]['allowed_menus'])) {
                    $allowed_menus = $settings[$role]['allowed_menus'];
                    
                    // Filter main menu
                    foreach ($menu as $key => $menu_item) {
                        if (isset($menu_item[2])) {
                            $menu_slug = $menu_item[2];
                            
                            // Always allow dashboard
                            if ($menu_slug === 'index.php') {
                                continue;
                            }
                            
                            // Check if menu is allowed
                            if (!in_array($menu_slug, $allowed_menus)) {
                                // Additional check for plugin-specific menus
                                if ($this->is_plugin_menu_allowed($menu_slug, $role, $settings)) {
                                    continue;
                                }
                                unset($menu[$key]);
                            }
                        }
                    }
                    
                    // Filter submenus
                    foreach ($submenu as $parent_slug => $submenu_items) {
                        if (!in_array($parent_slug, $allowed_menus)) {
                            // Check if it's a plugin menu that should be allowed
                            if (!$this->is_plugin_menu_allowed($parent_slug, $role, $settings)) {
                                unset($submenu[$parent_slug]);
                            }
                        }
                    }
                }
                
                break; // Use first matching role
            }
        }
    }
    
    /**
     * Check if a plugin menu should be allowed for a role
     */
    private function is_plugin_menu_allowed($menu_slug, $role, $settings) {
        if (!isset($settings[$role]['allowed_plugins'])) {
            return false;
        }
        
        $allowed_plugins = $settings[$role]['allowed_plugins'];
        
        // Get all active plugins and their slugs
        $active_plugins = get_option('active_plugins', array());
        
        foreach ($active_plugins as $plugin_path) {
            $plugin_slug = dirname($plugin_path);
            
            // If this plugin is in the allowed list, check if the menu belongs to it
            if (in_array($plugin_slug, $allowed_plugins)) {
                // Check if menu slug contains plugin identifier
                // This is a simple check - could be improved with more sophisticated matching
                if (strpos($menu_slug, $plugin_slug) !== false || 
                    strpos($menu_slug, str_replace('-', '_', $plugin_slug)) !== false ||
                    strpos($menu_slug, str_replace('_', '-', $plugin_slug)) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        ?>
        <div class="wrap fp-role-manager">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Impostazioni salvate con successo!', 'fp-wp-role-manager'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="fp-role-info">
                <h4><?php _e('Gestione Ruoli WordPress', 'fp-wp-role-manager'); ?></h4>
                <p><?php _e('Configura quali menu amministrativi e plugin possono vedere i diversi ruoli utente. Seleziona un ruolo per iniziare la configurazione.', 'fp-wp-role-manager'); ?></p>
            </div>
            
            <form method="post" action="options.php" id="fp-role-manager-form">
                <?php
                settings_fields('fp_role_manager_settings');
                do_settings_sections('fp_role_manager_settings');
                
                $settings = get_option('fp_role_manager_settings', array());
                $selected_role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : '';
                
                // Get all WordPress roles
                $wp_roles = wp_roles();
                $available_roles = array();
                foreach ($wp_roles->get_names() as $role => $name) {
                    // Skip administrator role
                    if ($role !== 'administrator') {
                        $available_roles[$role] = $name;
                    }
                }
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="fp_role_selector"><?php _e('Seleziona Ruolo da Configurare', 'fp-wp-role-manager'); ?></label>
                        </th>
                        <td>
                            <select name="fp_role_selector" id="fp_role_selector" class="fp-role-selector">
                                <option value=""><?php _e('-- Seleziona un ruolo --', 'fp-wp-role-manager'); ?></option>
                                <?php foreach ($available_roles as $role => $name): ?>
                                    <option value="<?php echo esc_attr($role); ?>" <?php selected($selected_role, $role); ?>>
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Seleziona il ruolo per il quale vuoi configurare i permessi di accesso.', 'fp-wp-role-manager'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php if ($selected_role && isset($available_roles[$selected_role])): ?>
                    <div class="fp-role-section active" data-role="<?php echo esc_attr($selected_role); ?>">
                        <h3><?php echo sprintf(__('Configurazione per %s', 'fp-wp-role-manager'), esc_html($available_roles[$selected_role])); ?></h3>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label><?php _e('Menu Amministrativi Consentiti', 'fp-wp-role-manager'); ?></label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><?php _e('Menu consentiti', 'fp-wp-role-manager'); ?></legend>
                                        
                                        <?php
                                        $available_menus = array(
                                            'edit.php' => __('Posts (Articoli)', 'fp-wp-role-manager'),
                                            'upload.php' => __('Media (File multimediali)', 'fp-wp-role-manager'),
                                            'edit.php?post_type=page' => __('Pages (Pagine)', 'fp-wp-role-manager'),
                                            'edit-comments.php' => __('Comments (Commenti)', 'fp-wp-role-manager'),
                                            'themes.php' => __('Appearance (Aspetto)', 'fp-wp-role-manager'),
                                            'plugins.php' => __('Plugins', 'fp-wp-role-manager'),
                                            'users.php' => __('Users (Utenti)', 'fp-wp-role-manager'),
                                            'tools.php' => __('Tools (Strumenti)', 'fp-wp-role-manager'),
                                            'options-general.php' => __('Settings (Impostazioni)', 'fp-wp-role-manager'),
                                            'fp-role-manager' => __('Role Manager', 'fp-wp-role-manager'),
                                        );
                                        
                                        $allowed_menus = isset($settings[$selected_role]['allowed_menus']) ? $settings[$selected_role]['allowed_menus'] : array();
                                        
                                        foreach ($available_menus as $menu_slug => $menu_name) {
                                            $checked = in_array($menu_slug, $allowed_menus) ? 'checked="checked"' : '';
                                            echo '<label><input type="checkbox" name="fp_role_manager_settings[' . esc_attr($selected_role) . '][allowed_menus][]" value="' . esc_attr($menu_slug) . '" ' . $checked . '> ' . esc_html($menu_name) . '</label><br>';
                                        }
                                        ?>
                                    </fieldset>
                                    <p class="description"><?php _e('Seleziona i menu che questo ruolo può vedere. Il menu Dashboard è sempre visibile.', 'fp-wp-role-manager'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label><?php _e('Plugin Consentiti', 'fp-wp-role-manager'); ?></label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><?php _e('Plugin consentiti', 'fp-wp-role-manager'); ?></legend>
                                        
                                        <?php
                                        // Get all active plugins
                                        $active_plugins = get_option('active_plugins', array());
                                        $allowed_plugins = isset($settings[$selected_role]['allowed_plugins']) ? $settings[$selected_role]['allowed_plugins'] : array();
                                        
                                        if (!empty($active_plugins)) {
                                            foreach ($active_plugins as $plugin_path) {
                                                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
                                                $plugin_name = $plugin_data['Name'];
                                                $plugin_slug = dirname($plugin_path);
                                                
                                                // Skip this plugin
                                                if (strpos($plugin_path, 'fp-wp-role-manager') !== false) {
                                                    continue;
                                                }
                                                
                                                $checked = in_array($plugin_slug, $allowed_plugins) ? 'checked="checked"' : '';
                                                echo '<label><input type="checkbox" name="fp_role_manager_settings[' . esc_attr($selected_role) . '][allowed_plugins][]" value="' . esc_attr($plugin_slug) . '" ' . $checked . '> ' . esc_html($plugin_name) . '</label><br>';
                                            }
                                        } else {
                                            echo '<p>' . __('Nessun plugin attivo trovato.', 'fp-wp-role-manager') . '</p>';
                                        }
                                        ?>
                                    </fieldset>
                                    <p class="description"><?php _e('Seleziona i plugin che questo ruolo può utilizzare. I menu dei plugin non selezionati saranno nascosti.', 'fp-wp-role-manager'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="fp-warning">
                            <h4><?php _e('Attenzione!', 'fp-wp-role-manager'); ?></h4>
                            <p><?php _e('Configurare attentamente i permessi. Rimuovere tutti i menu potrebbe rendere inutilizzabile l\'area admin per questo ruolo.', 'fp-wp-role-manager'); ?></p>
                        </div>
                        
                        <?php submit_button(); ?>
                    </div>
                <?php endif; ?>
            </form>
            
            <?php if (!empty($settings)): ?>
                <h2><?php _e('Ruoli Configurati', 'fp-wp-role-manager'); ?></h2>
                <?php foreach ($settings as $role => $config): ?>
                    <div class="card">
                        <h3><?php echo esc_html(isset($available_roles[$role]) ? $available_roles[$role] : $role); ?></h3>
                        <p><strong><?php _e('Menu consentiti:', 'fp-wp-role-manager'); ?></strong></p>
                        <ul>
                            <?php 
                            if (isset($config['allowed_menus']) && !empty($config['allowed_menus'])) {
                                foreach ($config['allowed_menus'] as $menu) {
                                    echo '<li>' . esc_html($menu) . '</li>';
                                }
                            } else {
                                echo '<li>' . __('Nessun menu configurato', 'fp-wp-role-manager') . '</li>';
                            }
                            ?>
                        </ul>
                        
                        <p><strong><?php _e('Plugin consentiti:', 'fp-wp-role-manager'); ?></strong></p>
                        <ul>
                            <?php 
                            if (isset($config['allowed_plugins']) && !empty($config['allowed_plugins'])) {
                                foreach ($config['allowed_plugins'] as $plugin) {
                                    echo '<li>' . esc_html($plugin) . '</li>';
                                }
                            } else {
                                echo '<li>' . __('Nessun plugin configurato', 'fp-wp-role-manager') . '</li>';
                            }
                            ?>
                        </ul>
                        
                        <p><a href="<?php echo admin_url('tools.php?page=fp-role-manager&role=' . urlencode($role)); ?>" class="button"><?php _e('Modifica Configurazione', 'fp-wp-role-manager'); ?></a></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <h3><?php _e('Nessuna Configurazione', 'fp-wp-role-manager'); ?></h3>
                    <p><?php _e('Non ci sono ancora configurazioni salvate. Seleziona un ruolo per iniziare.', 'fp-wp-role-manager'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Initialize the plugin
FP_WP_Role_Manager::get_instance();