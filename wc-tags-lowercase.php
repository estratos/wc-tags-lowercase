<?php
/**
 * Plugin Name: Convert Tags to Lowercase for WooCommerce
 * Plugin URI: https://yoursite.com/
 * Description: Automatically converts all WooCommerce product tags to lowercase
 * Version: 1.1.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wc-tags-lowercase
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WC_Tags_Lowercase {
    
    /**
     * Plugin constructor
     */
    public function __construct() {
        // Initialize the plugin
        $this->init();
    }
    
    /**
     * Initialize hooks and actions
     */
    private function init() {
        // Load text domain for translations
        add_action('init', array($this, 'load_textdomain'));
        
        // Hook to convert tags when saving/updating product
        add_action('save_post_product', array($this, 'convert_product_tags'), 10, 3);
        add_action('woocommerce_update_product', array($this, 'convert_product_tags'));
        
        // Hook to convert tags when created/edited directly (FIXED)
        add_action('created_product_tag', array($this, 'convert_single_term_on_save'), 10, 2);
        add_action('edited_product_tag', array($this, 'convert_single_term_on_save'), 10, 2);
        
        // Hook to convert tags before they're saved (PRE SAVE - MOST IMPORTANT FIX)
        add_filter('pre_insert_term', array($this, 'convert_term_before_save'), 10, 2);
        
        // Hook to convert individual tags from quick edit via AJAX
        add_action('wp_ajax_convert_tag_to_lowercase', array($this, 'convert_individual_tag'));
        
        // Hook for admin
        add_action('admin_init', array($this, 'register_admin_actions'));
        
        // Hook for admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Hook for bulk actions in tags list
        add_filter('bulk_actions-edit-product_tag', array($this, 'add_bulk_action'));
        add_filter('handle_bulk_actions-edit-product_tag', array($this, 'handle_bulk_action'), 10, 3);
        
        // Hook for admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hook for inline edit save
        add_action('wp_ajax_inline-save-tax', array($this, 'handle_inline_edit'), 1);
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wc-tags-lowercase',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Convert tags of a specific product
     */
    public function convert_product_tags($post_id, $post = null, $update = true) {
        // Verify it's a product
        if (get_post_type($post_id) !== 'product') {
            return;
        }
        
        // Skip auto-drafts and autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Get current product tags
        $tags = wp_get_post_terms($post_id, 'product_tag', array('fields' => 'names'));
        
        if (!empty($tags) && !is_wp_error($tags)) {
            // Convert each tag to lowercase
            $lowercase_tags = array_map(array($this, 'strtolower_utf8'), $tags);
            
            // Remove old tags
            wp_remove_object_terms($post_id, $tags, 'product_tag');
            
            // Add lowercase tags
            wp_set_post_terms($post_id, $lowercase_tags, 'product_tag', false);
        }
    }
    
    /**
     * UTF-8 safe strtolower function
     */
    private function strtolower_utf8($string) {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($string, 'UTF-8');
        }
        return strtolower($string);
    }
    
    /**
     * Convert term before it's saved to database (MAIN FIX)
     * This hook runs BEFORE the term is inserted/updated
     */
    public function convert_term_before_save($term, $taxonomy) {
        // Only process product tags
        if ($taxonomy !== 'product_tag') {
            return $term;
        }
        
        // If term is an array (from form submission)
        if (is_array($term)) {
            if (isset($term['tag-name'])) {
                $term['tag-name'] = $this->strtolower_utf8($term['tag-name']);
            }
            if (isset($term['name'])) {
                $term['name'] = $this->strtolower_utf8($term['name']);
            }
        }
        // If term is a string (simple tag name)
        elseif (is_string($term)) {
            $term = $this->strtolower_utf8($term);
        }
        
        return $term;
    }
    
    /**
     * Convert single term when saved (backup method)
     */
    public function convert_single_term_on_save($term_id, $tt_id) {
        $tag = get_term($term_id, 'product_tag');
        
        if ($tag && !is_wp_error($tag)) {
            $new_name = $this->strtolower_utf8($tag->name);
            
            // Only update if different
            if ($tag->name !== $new_name) {
                wp_update_term($term_id, 'product_tag', array(
                    'name' => $new_name,
                    'slug' => sanitize_title($new_name)
                ));
            }
        }
    }
    
    /**
     * Handle inline edit saves
     */
    public function handle_inline_edit() {
        // Check if this is for product_tag
        if (isset($_POST['taxonomy']) && $_POST['taxonomy'] === 'product_tag') {
            if (isset($_POST['name'])) {
                $_POST['name'] = $this->strtolower_utf8($_POST['name']);
            }
        }
    }
    
    /**
     * Convert all existing tags
     */
    public function convert_all_tags() {
        // Get all product tags
        $tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
            'fields' => 'all',
            'number' => 0,
        ));
        
        if (is_wp_error($tags) || empty($tags)) {
            return false;
        }
        
        $updated = 0;
        
        foreach ($tags as $tag) {
            // Convert name to lowercase
            $new_name = $this->strtolower_utf8($tag->name);
            
            // Only update if different
            if ($tag->name !== $new_name) {
                $result = wp_update_term($tag->term_id, 'product_tag', array(
                    'name' => $new_name,
                    'slug' => sanitize_title($new_name)
                ));
                
                if (!is_wp_error($result)) {
                    $updated++;
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Convert an individual tag via AJAX
     */
    public function convert_individual_tag() {
        // Verify nonce and permissions
        if (!check_ajax_referer('wc_tags_lowercase_nonce', 'nonce', false) ||
            !current_user_can('manage_product_terms')) {
            wp_die('Unauthorized');
        }
        
        $tag_id = isset($_POST['tag_id']) ? intval($_POST['tag_id']) : 0;
        
        if ($tag_id) {
            $tag = get_term($tag_id, 'product_tag');
            
            if ($tag && !is_wp_error($tag)) {
                $new_name = $this->strtolower_utf8($tag->name);
                
                $result = wp_update_term($tag_id, 'product_tag', array(
                    'name' => $new_name,
                    'slug' => sanitize_title($new_name)
                ));
                
                if (!is_wp_error($result)) {
                    wp_send_json_success(array(
                        'message' => sprintf(
                            __('Tag "%s" converted to lowercase successfully.', 'wc-tags-lowercase'),
                            $new_name
                        )
                    ));
                }
            }
        }
        
        wp_send_json_error(array(
            'message' => __('Error converting the tag.', 'wc-tags-lowercase')
        ));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Tags Lowercase', 'wc-tags-lowercase'),
            __('Tags Lowercase', 'wc-tags-lowercase'),
            'manage_woocommerce',
            'wc-tags-lowercase',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Convert Tags to Lowercase', 'wc-tags-lowercase'); ?></h1>
            
            <?php if (isset($_GET['converted']) && $_GET['converted'] > 0): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php 
                    printf(
                        __('Successfully converted %d tags to lowercase.', 'wc-tags-lowercase'),
                        intval($_GET['converted'])
                    ); 
                    ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2><?php _e('Convert All Tags', 'wc-tags-lowercase'); ?></h2>
                <p><?php _e('This action will convert all existing product tags to lowercase.', 'wc-tags-lowercase'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('wc_tags_lowercase_action', 'wc_tags_lowercase_nonce'); ?>
                    <input type="hidden" name="action" value="convert_all_tags">
                    <button type="submit" class="button button-primary" 
                            onclick="return confirm('<?php _e('Are you sure you want to convert all tags to lowercase?', 'wc-tags-lowercase'); ?>')">
                        <?php _e('Convert All Tags', 'wc-tags-lowercase'); ?>
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2><?php _e('Automatic Functionality', 'wc-tags-lowercase'); ?></h2>
                <p><?php _e('The plugin automatically converts to lowercase:', 'wc-tags-lowercase'); ?></p>
                <ul>
                    <li><?php _e('When creating or editing a product', 'wc-tags-lowercase'); ?></li>
                    <li><?php _e('When updating a product', 'wc-tags-lowercase'); ?></li>
                    <li><?php _e('When adding new tags directly', 'wc-tags-lowercase'); ?></li>
                    <li><?php _e('When editing existing tags', 'wc-tags-lowercase'); ?></li>
                    <li><?php _e('When using inline edit', 'wc-tags-lowercase'); ?></li>
                </ul>
            </div>
            
            <div class="card">
                <h2><?php _e('Statistics', 'wc-tags-lowercase'); ?></h2>
                <?php
                $total_tags = wp_count_terms(array(
                    'taxonomy' => 'product_tag',
                    'hide_empty' => false
                ));
                
                if (is_wp_error($total_tags)) {
                    $total_tags = 0;
                }
                
                $tags = get_terms(array(
                    'taxonomy' => 'product_tag',
                    'hide_empty' => false,
                    'number' => 10,
                    'orderby' => 'name'
                ));
                
                if ($total_tags > 0) {
                    echo '<p>' . sprintf(__('Total tags: %d', 'wc-tags-lowercase'), $total_tags) . '</p>';
                    
                    if (!empty($tags) && !is_wp_error($tags)) {
                        echo '<h3>' . __('Some current tags:', 'wc-tags-lowercase') . '</h3>';
                        echo '<ul>';
                        foreach ($tags as $tag) {
                            $is_lowercase = ($tag->name === $this->strtolower_utf8($tag->name));
                            $style = $is_lowercase ? 'color:green;' : 'color:orange;';
                            echo '<li>';
                            echo '<span style="' . $style . '">' . esc_html($tag->name) . '</span>';
                            if (!$is_lowercase) {
                                echo ' <button class="button button-small convert-tag-single" data-tag-id="' . $tag->term_id . '">' . __('Convert', 'wc-tags-lowercase') . '</button>';
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                }
                ?>
            </div>
            
            <div class="card">
                <h2><?php _e('Test the Plugin', 'wc-tags-lowercase'); ?></h2>
                <p><?php _e('Try adding a new tag with uppercase letters to see the automatic conversion in action.', 'wc-tags-lowercase'); ?></p>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=product_tag&post_type=product'); ?>" class="button">
                    <?php _e('Go to Product Tags', 'wc-tags-lowercase'); ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Register admin actions
     */
    public function register_admin_actions() {
        if (isset($_POST['action']) && $_POST['action'] === 'convert_all_tags') {
            if (!check_admin_referer('wc_tags_lowercase_action', 'wc_tags_lowercase_nonce') ||
                !current_user_can('manage_woocommerce')) {
                return;
            }
            
            $updated = $this->convert_all_tags();
            
            wp_redirect(add_query_arg(
                'converted',
                $updated,
                admin_url('admin.php?page=wc-tags-lowercase')
            ));
            exit;
        }
    }
    
    /**
     * Add bulk action to tags list
     */
    public function add_bulk_action($bulk_actions) {
        $bulk_actions['convert_to_lowercase'] = __('Convert to lowercase', 'wc-tags-lowercase');
        return $bulk_actions;
    }
    
    /**
     * Handle bulk action
     */
    public function handle_bulk_action($redirect_to, $doaction, $tag_ids) {
        if ($doaction !== 'convert_to_lowercase') {
            return $redirect_to;
        }
        
        $updated = 0;
        
        foreach ($tag_ids as $tag_id) {
            $tag = get_term($tag_id, 'product_tag');
            
            if ($tag && !is_wp_error($tag)) {
                $new_name = $this->strtolower_utf8($tag->name);
                
                if ($tag->name !== $new_name) {
                    $result = wp_update_term($tag_id, 'product_tag', array(
                        'name' => $new_name,
                        'slug' => sanitize_title($new_name)
                    ));
                    
                    if (!is_wp_error($result)) {
                        $updated++;
                    }
                }
            }
        }
        
        $redirect_to = add_query_arg(
            'converted_lowercase',
            $updated,
            $redirect_to
        );
        
        return $redirect_to;
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Load on our admin page
        if ($hook === 'woocommerce_page_wc-tags-lowercase') {
            wp_enqueue_style(
                'wc-tags-lowercase-admin',
                plugin_dir_url(__FILE__) . 'assets/admin.css',
                array(),
                '1.1.0'
            );
            
            wp_enqueue_script(
                'wc-tags-lowercase-admin',
                plugin_dir_url(__FILE__) . 'assets/admin.js',
                array('jquery'),
                '1.1.0',
                true
            );
            
            wp_localize_script('wc-tags-lowercase-admin', 'wc_tags_lowercase', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_tags_lowercase_nonce'),
                'texts' => array(
                    'convert' => __('Convert to lowercase', 'wc-tags-lowercase'),
                    'converting' => __('Converting...', 'wc-tags-lowercase'),
                    'success' => __('Successfully converted', 'wc-tags-lowercase'),
                    'error' => __('Error converting', 'wc-tags-lowercase')
                )
            ));
        }
        
        // Load on product tags page
        if ($hook === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_tag') {
            wp_enqueue_script(
                'wc-tags-lowercase-tags-page',
                plugin_dir_url(__FILE__) . 'assets/tags-page.js',
                array('jquery'),
                '1.1.0',
                true
            );
        }
    }
}

// Initialize the plugin
function wc_tags_lowercase_init() {
    if (class_exists('WooCommerce')) {
        new WC_Tags_Lowercase();
    } else {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e('The "Convert Tags to Lowercase" plugin requires WooCommerce to function.', 'wc-tags-lowercase'); ?></p>
            </div>
            <?php
        });
    }
}

add_action('plugins_loaded', 'wc_tags_lowercase_init');

// Plugin activation hook
register_activation_hook(__FILE__, 'wc_tags_lowercase_activate');

function wc_tags_lowercase_activate() {
    // Verify WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce. Please install and activate WooCommerce first.', 'wc-tags-lowercase'));
    }
    
    // Optional: Convert all existing tags on activation
    // Uncomment if you want this behavior
    /*
    $plugin = new WC_Tags_Lowercase();
    $plugin->convert_all_tags();
    */
}

// Add real-time conversion on tag add/update via AJAX
add_action('wp_ajax_add-tag', function() {
    // Check if this is for product_tag
    if (isset($_POST['taxonomy']) && $_POST['taxonomy'] === 'product_tag') {
        // Convert tag name to lowercase before processing
        if (isset($_POST['tag-name'])) {
            $_POST['tag-name'] = strtolower($_POST['tag-name']);
        }
    }
}, 1);
