<?php
/**
 * Plugin Name: Accessible PDF Brochure for WooCommerce
 * Plugin URI:  https://github.com/desoem/Accessible-PDF-Brochure
 * Description: Adds a responsive and accessible PDF brochure tab to WooCommerce product pages.
 * Version: 2.0
 * Author: Ridwan Sumantri
 * Author URI: https://github.com/desoem
 * License: GPLv2 or later
 * Text Domain: accessible-pdf-brochure
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

// Cegah akses langsung ke file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'ACCESSIBLE_PDF_BROCHURE_VERSION', '2.0' );
define( 'ACCESSIBLE_PDF_BROCHURE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ACCESSIBLE_PDF_BROCHURE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Main plugin class
 */
class Accessible_PDF_Brochure {
    
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }
    
    public function init() {
        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }
        
        // Check WooCommerce version compatibility
        if ( ! $this->is_woocommerce_compatible() ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_version_notice' ) );
            return;
        }
        
        // Initialize plugin
        $this->init_hooks();
    }
    
    private function is_woocommerce_compatible() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return false;
        }
        
        $wc_version = WC()->version;
        $required_version = '5.0';
        
        return version_compare( $wc_version, $required_version, '>=' );
    }
    
    public function woocommerce_version_notice() {
        echo '<div class="error"><p><strong>' . __( 'Accessible PDF Brochure', 'accessible-pdf-brochure' ) . '</strong> ' . __( 'requires WooCommerce version 5.0 or higher. Please update WooCommerce.', 'accessible-pdf-brochure' ) . '</p></div>';
    }
    
    public function load_textdomain() {
        load_plugin_textdomain( 'accessible-pdf-brochure', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . __( 'Accessible PDF Brochure', 'accessible-pdf-brochure' ) . '</strong> ' . __( 'requires WooCommerce to be installed and active.', 'accessible-pdf-brochure' ) . '</p></div>';
    }
    
    private function init_hooks() {
        // Add product tab
        add_filter( 'woocommerce_product_tabs', array( $this, 'add_pdf_brochure_tab' ) );
        
        // Add custom field
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_pdf_brochure_custom_field' ) );
        
        // Save custom field
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_pdf_brochure_custom_field' ) );
        
        // Enqueue assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        
        // Add WooCommerce compatibility hooks
        add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );
        
        // Add HPOS compatibility
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
    }
    
    public function woocommerce_init() {
        // Initialize WooCommerce-specific features
        if ( class_exists( 'WC_Product' ) ) {
            // Add any WooCommerce-specific initialization here
        }
    }
    
    public function declare_hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
    
    public function add_pdf_brochure_tab( $tabs ) {
        $tabs['pdf_brochure'] = array(
            'title'    => __( 'Brochure', 'accessible-pdf-brochure' ),
            'priority' => 30,
            'callback' => array( $this, 'display_pdf_brochure_tab_content' )
        );
        return $tabs;
    }
    
    public function display_pdf_brochure_tab_content() {
        global $product;
        $brochure_url = get_post_meta( $product->get_id(), 'product_brochure', true );

        if ( ! empty( $brochure_url ) ) {
            // Mengambil nama file dari URL PDF
            $file_name = basename( $brochure_url );

            echo '<div class="pdf-brochure-container" role="region" aria-label="' . esc_attr__( 'Product Brochure', 'accessible-pdf-brochure' ) . '">';
            echo '<p>' . __( 'Lihat atau unduh brosur produk di bawah ini:', 'accessible-pdf-brochure' ) . '</p>';
            echo '<p><strong>' . __( 'Nama file:', 'accessible-pdf-brochure' ) . '</strong> ' . esc_html( $file_name ) . '</p>';
            echo '<a href="' . esc_url( $brochure_url ) . '" class="button" target="_blank" rel="noopener" aria-label="' . esc_attr__( 'View PDF Brochure', 'accessible-pdf-brochure' ) . '">' . __( 'Lihat Brosur (PDF)', 'accessible-pdf-brochure' ) . '</a>';
            echo '</div>';
        } else {
            echo '<p>' . __( 'Brosur untuk produk ini tidak tersedia.', 'accessible-pdf-brochure' ) . '</p>';
        }
    }
    
    public function add_pdf_brochure_custom_field() {
        echo '<div class="options_group">';
        echo '<h3 style="margin-top: 20px; margin-bottom: 10px; color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 5px;">' . __( 'PDF Brochure Settings', 'accessible-pdf-brochure' ) . '</h3>';
        
        woocommerce_wp_text_input( array(
            'id'          => 'product_brochure',
            'label'       => __( 'PDF Brochure URL', 'accessible-pdf-brochure' ),
            'desc_tip'    => true,
            'description' => __( 'Enter the full URL of the PDF brochure for this product. Example: https://yoursite.com/brochures/product-brochure.pdf', 'accessible-pdf-brochure' ),
            'type'        => 'url',
            'custom_attributes' => array(
                'pattern' => 'https?://.+',
                'title'   => __( 'Please enter a valid URL starting with http:// or https://', 'accessible-pdf-brochure' ),
                'placeholder' => 'https://example.com/brochure.pdf'
            )
        ) );
        
        // Add file upload field as alternative
        woocommerce_wp_text_input( array(
            'id'          => 'product_brochure_file',
            'label'       => __( 'Or Upload PDF File', 'accessible-pdf-brochure' ),
            'desc_tip'    => true,
            'description' => __( 'Upload a PDF file directly. This will override the URL field above.', 'accessible-pdf-brochure' ),
            'type'        => 'file',
            'custom_attributes' => array(
                'accept' => '.pdf',
                'title'   => __( 'Select a PDF file to upload', 'accessible-pdf-brochure' )
            )
        ) );
        
        echo '<p class="description" style="margin-top: 10px; padding: 10px; background: #f0f8ff; border-left: 4px solid #0073aa;">';
        echo '<strong>' . __( 'Note:', 'accessible-pdf-brochure' ) . '</strong> ' . __( 'You can either provide a URL to an existing PDF or upload a new PDF file. If both are provided, the uploaded file will take priority.', 'accessible-pdf-brochure' );
        echo '</p>';
        
        echo '</div>';
    }
    
    public function save_pdf_brochure_custom_field( $post_id ) {
        // Handle URL field
        if ( isset( $_POST['product_brochure'] ) ) {
            $brochure_url = sanitize_url( $_POST['product_brochure'] );
            
            if ( filter_var( $brochure_url, FILTER_VALIDATE_URL ) ) {
                update_post_meta( $post_id, 'product_brochure', $brochure_url );
            } else {
                delete_post_meta( $post_id, 'product_brochure' );
            }
        }
        
        // Handle file upload field
        if ( isset( $_FILES['product_brochure_file'] ) && !empty( $_FILES['product_brochure_file']['name'] ) ) {
            $uploaded_file = $this->handle_pdf_upload( $_FILES['product_brochure_file'] );
            
            if ( $uploaded_file && !is_wp_error( $uploaded_file ) ) {
                // Save the uploaded file URL
                update_post_meta( $post_id, 'product_brochure_file', $uploaded_file['url'] );
                update_post_meta( $post_id, 'product_brochure', $uploaded_file['url'] ); // Override URL with uploaded file
            }
        }
    }
    
    private function handle_pdf_upload( $file ) {
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        
        // Check if file is PDF
        $file_type = wp_check_filetype( $file['name'] );
        if ( $file_type['ext'] !== 'pdf' ) {
            return new WP_Error( 'invalid_file_type', __( 'Only PDF files are allowed.', 'accessible-pdf-brochure' ) );
        }
        
        // Check file size (max 10MB)
        if ( $file['size'] > 10 * 1024 * 1024 ) {
            return new WP_Error( 'file_too_large', __( 'File size must be less than 10MB.', 'accessible-pdf-brochure' ) );
        }
        
        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array( 'pdf' => 'application/pdf' )
        );
        
        $movefile = wp_handle_upload( $file, $upload_overrides );
        
        if ( $movefile && !isset( $movefile['error'] ) ) {
            return $movefile;
        } else {
            return new WP_Error( 'upload_error', $movefile['error'] );
        }
    }
    
    public function enqueue_assets() {
        if ( is_product() ) {
            wp_enqueue_style( 
                'accessible-pdf-brochure-styles', 
                ACCESSIBLE_PDF_BROCHURE_PLUGIN_URL . 'css/style.css', 
                array(), 
                ACCESSIBLE_PDF_BROCHURE_VERSION 
            );
            wp_enqueue_script( 
                'accessible-pdf-brochure-scripts', 
                ACCESSIBLE_PDF_BROCHURE_PLUGIN_URL . 'js/script.js', 
                array( 'jquery' ), 
                ACCESSIBLE_PDF_BROCHURE_VERSION, 
                true 
            );
        }
    }
}

// Initialize the plugin
new Accessible_PDF_Brochure();
