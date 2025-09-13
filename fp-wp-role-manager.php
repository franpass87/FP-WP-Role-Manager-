<?php
/**
 * Plugin Name: FP WordPress Role Manager
 * Plugin URI: https://github.com/franpass87/FP-WP-Role-Manager-
 * Description: Gestione ruoli WordPress per controllare la visibilità dei menu admin. Permette di definire quali plugin/sezioni amministrative possono vedere specifici ruoli utente.
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
        // Create restaurant manager role if it doesn't exist
        if (!get_role('restaurant_manager')) {
            add_role(
                'restaurant_manager',
                __('Restaurant Manager', 'fp-wp-role-manager'),
                array(
                    'read' => true,
                    'edit_posts' => true,
                    'upload_files' => true,
                    'manage_categories' => true,
                )
            );
        }
        
        // Set default options
        if (!get_option('fp_role_manager_settings')) {
            $default_settings = array(
                'restaurant_manager' => array(
                    'allowed_menus' => array(
                        'edit.php', // Posts
                        'upload.php', // Media
                        'users.php', // Users (limited)
                        'fp-role-manager', // This plugin's settings
                    ),
                    'allowed_plugins' => array(
                        'restaurant-reservations', // Example restaurant plugin
                        'woocommerce', // Example for restaurant orders
                    )
                )
            );
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
            if (isset($settings[$role]) && isset($settings[$role]['allowed_menus'])) {
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
                            unset($menu[$key]);
                        }
                    }
                }
                
                // Filter submenus
                foreach ($submenu as $parent_slug => $submenu_items) {
                    if (!in_array($parent_slug, $allowed_menus)) {
                        unset($submenu[$parent_slug]);
                    }
                }
                
                break; // Use first matching role
            }
        }
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
                <h4><?php _e('Informazioni Plugin', 'fp-wp-role-manager'); ?></h4>
                <p><?php _e('Questo plugin permette di controllare quali menu amministrativi possono vedere i diversi ruoli utente. Configurare attentamente i permessi per garantire sicurezza e funzionalità.', 'fp-wp-role-manager'); ?></p>
            </div>
            
            <form method="post" action="options.php" id="fp-role-manager-form">
                <?php
                settings_fields('fp_role_manager_settings');
                do_settings_sections('fp_role_manager_settings');
                
                $settings = get_option('fp_role_manager_settings', array());
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="restaurant_manager_menus"><?php _e('Restaurant Manager - Menu Consentiti', 'fp-wp-role-manager'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php _e('Menu consentiti per Restaurant Manager', 'fp-wp-role-manager'); ?></legend>
                                
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
                                
                                $allowed_menus = isset($settings['restaurant_manager']['allowed_menus']) ? $settings['restaurant_manager']['allowed_menus'] : array();
                                
                                foreach ($available_menus as $menu_slug => $menu_name) {
                                    $checked = in_array($menu_slug, $allowed_menus) ? 'checked="checked"' : '';
                                    echo '<label><input type="checkbox" name="fp_role_manager_settings[restaurant_manager][allowed_menus][]" value="' . esc_attr($menu_slug) . '" ' . $checked . '> ' . esc_html($menu_name) . '</label><br>';
                                }
                                ?>
                            </fieldset>
                            <p class="description"><?php _e('Seleziona i menu che il Restaurant Manager può vedere nell\'admin di WordPress. Il menu Dashboard è sempre visibile.', 'fp-wp-role-manager'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="fp-warning">
                    <h4><?php _e('Attenzione!', 'fp-wp-role-manager'); ?></h4>
                    <p><?php _e('Assicurati di selezionare almeno il menu "Role Manager" per permettere ai Restaurant Manager di accedere a questa configurazione se necessario. Rimuovere tutti i menu potrebbe rendere inutilizzabile l\'area admin per questo ruolo.', 'fp-wp-role-manager'); ?></p>
                </div>
                
                <?php submit_button(); ?>
            </form>
            
            <h2><?php _e('Informazioni Ruoli', 'fp-wp-role-manager'); ?></h2>
            <div class="card">
                <h3><?php _e('Restaurant Manager', 'fp-wp-role-manager'); ?></h3>
                <p><?php _e('Questo ruolo è progettato per gestori di ristoranti che devono accedere solo a funzionalità specifiche dell\'admin WordPress, come la gestione di prenotazioni, menu, e contenuti relativi al ristorante.', 'fp-wp-role-manager'); ?></p>
                <p><strong><?php _e('Capacità del ruolo:', 'fp-wp-role-manager'); ?></strong></p>
                <ul>
                    <li><?php _e('read - Lettura contenuti', 'fp-wp-role-manager'); ?></li>
                    <li><?php _e('edit_posts - Modifica post', 'fp-wp-role-manager'); ?></li>
                    <li><?php _e('upload_files - Caricamento file media', 'fp-wp-role-manager'); ?></li>
                    <li><?php _e('manage_categories - Gestione categorie', 'fp-wp-role-manager'); ?></li>
                </ul>
                
                <p><strong><?php _e('Casi d\'uso tipici:', 'fp-wp-role-manager'); ?></strong></p>
                <ul>
                    <li><?php _e('Gestione contenuti del sito ristorante', 'fp-wp-role-manager'); ?></li>
                    <li><?php _e('Caricamento immagini menu e piatti', 'fp-wp-role-manager'); ?></li>
                    <li><?php _e('Accesso a plugin di prenotazioni', 'fp-wp-role-manager'); ?></li>
                    <li><?php _e('Gestione ordini online (se WooCommerce è abilitato)', 'fp-wp-role-manager'); ?></li>
                </ul>
            </div>
            
            <div class="card">
                <h3><?php _e('Utenti con ruolo Restaurant Manager', 'fp-wp-role-manager'); ?></h3>
                <?php
                $restaurant_managers = get_users(array('role' => 'restaurant_manager'));
                if (!empty($restaurant_managers)) {
                    echo '<ul>';
                    foreach ($restaurant_managers as $user) {
                        echo '<li>' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>' . __('Nessun utente ha attualmente il ruolo Restaurant Manager.', 'fp-wp-role-manager') . '</p>';
                    echo '<p><a href="' . admin_url('users.php') . '" class="button">' . __('Gestisci Utenti', 'fp-wp-role-manager') . '</a></p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
FP_WP_Role_Manager::get_instance();