<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

function connector_delete_plugin() {
    global $wpdb;
    $wpdb->query(sprintf("DROP TABLE IF EXISTS %s", $wpdb->prefix . 'cms2cms_connector_options'));
    removeBridge();
}

function removeBridge()
{
    global $wp_filesystem;
    $bridgeFolder = ABSPATH . 'cms2cms';
    if ($wp_filesystem->is_dir($bridgeFolder)) {
        $wp_filesystem->delete($bridgeFolder, true);
    } else {
        return new WP_Error('writing_error', 'Cannot Remove bridge folder');
    }
}

connector_delete_plugin();
