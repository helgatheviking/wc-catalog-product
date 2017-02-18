<?php
/**
 * WC Catalog Product Uninstall
 *
 * Uninstalling plugin deletes scheduled hook
 *
 * @author      Kathy Darling
 * @category    Core
 * @package     WC_Catalog_Product/Uninstaller
 * @version     0.1.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

wp_clear_scheduled_hook( 'wc_catalog_product_delete_temporary_file' );

// Get the path to the catalogs folder
$upload_dir      = wp_upload_dir();

$dir = $upload_dir['basedir'] . '/' . WC_Catalog_Product::DIR;

// Remove all files from folder.		
foreach (scandir($dir) as $item) {
	if ($item == '.' || $item == '..') continue;
	unlink($dir.DIRECTORY_SEPARATOR.$item);
}
// Delete folder.
rmdir($dir);

