<?php
/**
 * Plugin Name: Accessible PDF Brochure for WooCommerce
 * Description: Adds a responsive and accessible PDF brochure tab to WooCommerce product pages.
 * Version: 1.0
 * Author: Ridwan Sumantri
 * License: GPLv2 or later
 */

// Cegah akses langsung ke file
if ( ! defined( 'ABSPATH' ) ) exit;

// Tambahkan tab Brosur di halaman produk WooCommerce
add_filter( 'woocommerce_product_tabs', 'add_pdf_brochure_tab' );
function add_pdf_brochure_tab( $tabs ) {
    $tabs['pdf_brochure'] = array(
        'title'    => __( 'Brochure', 'accessible-pdf-brochure' ),
        'priority' => 20,
        'callback' => 'display_pdf_brochure_tab_content'
    );
    return $tabs;
}

// Konten tab Brosur
function display_pdf_brochure_tab_content() {
    global $product;
    $brochure_url = get_post_meta( $product->get_id(), 'product_brochure', true );

    if ( ! empty( $brochure_url ) ) {
        // Mengambil nama file dari URL PDF
        $file_name = basename( $brochure_url );

        echo '<div class="pdf-brochure-container">';
        echo '<p>' . __( 'Lihat atau unduh brosur produk di bawah ini:', 'accessible-pdf-brochure' ) . '</p>';
        echo '<p>' . __( 'Nama file:', 'accessible-pdf-brochure' ) . ' ' . esc_html( $file_name ) . '</p>';
        echo '<a href="' . esc_url( $brochure_url ) . '" class="button" target="_blank" rel="noopener">' . __( 'Lihat Brosur (PDF)', 'accessible-pdf-brochure' ) . '</a>';
        echo '</div>';
    } else {
        echo '<p>' . __( 'Brosur untuk produk ini tidak tersedia.', 'accessible-pdf-brochure' ) . '</p>';
    }
}

// Tambahkan custom field ke editor produk WooCommerce
add_action( 'woocommerce_product_options_general_product_data', 'add_pdf_brochure_custom_field' );
function add_pdf_brochure_custom_field() {
    woocommerce_wp_text_input( array(
        'id'          => 'product_brochure',
        'label'       => __( 'PDF Brochure URL', 'accessible-pdf-brochure' ),
        'desc_tip'    => true,
        'description' => __( 'Enter the URL of the PDF brochure for this product.', 'accessible-pdf-brochure' ),
    ) );
}

// Simpan nilai custom field
add_action( 'woocommerce_process_product_meta', 'save_pdf_brochure_custom_field' );
function save_pdf_brochure_custom_field( $post_id ) {
    // Validasi URL yang dimasukkan
    if ( isset( $_POST['product_brochure'] ) ) {
        $brochure_url = sanitize_text_field( $_POST['product_brochure'] );

        // Pastikan URL valid
        if ( filter_var( $brochure_url, FILTER_VALIDATE_URL ) ) {
            update_post_meta( $post_id, 'product_brochure', $brochure_url );
        } else {
            // Jika URL tidak valid, hapus data meta
            delete_post_meta( $post_id, 'product_brochure' );
        }
    }
}

// Muat file CSS dan JS untuk halaman produk
add_action( 'wp_enqueue_scripts', 'enqueue_pdf_brochure_assets' );
function enqueue_pdf_brochure_assets() {
    if ( is_product() ) {
        wp_enqueue_style( 'accessible-pdf-brochure-styles', plugins_url( '/css/styles.css', __FILE__ ) );
        wp_enqueue_script( 'accessible-pdf-brochure-scripts', plugins_url( '/js/scripts.js', __FILE__ ), array( 'jquery' ), '1.0', true );
    }
}
