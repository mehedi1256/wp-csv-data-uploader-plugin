<?php

/**
 * Plugin Name: CSV Data Uploader
 * Plugin URI: https://example.com
 * Description: A plugin that allows you to upload CSV files and store the data in the database.     
 * Version: 1.0
 * Author: Mehedi Hassan Shovo
 * Author URI: https://example.com
 */

 define('CDU_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));


 add_shortcode('csv-data-uploader', 'cdu_display_uploader_form');

 function cdu_display_uploader_form() {
    // start PHP buffer
    ob_start();
    include_once CDU_PLUGIN_DIR_PATH . '/template/cdu_form.php';// put all content into buffer

    // Read buffer
    $template = ob_get_contents();

    // Clear buffer
    ob_end_clean();

    return $template;
 }

 // DB Table creation dynamically during plugin activation

 register_activation_hook(__FILE__, 'cdu_create_table'); // plugin activation hook

 function cdu_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students_data';
    $table_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) DEFAULT NULL,
        email VARCHAR(50) DEFAULT NULL,
        age INT(11) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        photo VARCHAR(120) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) {$table_collate}";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );
 }

 // add script file

 add_action("wp_enqueue_scripts", "cdu_add_script_file");

 function cdu_add_script_file() {
    wp_enqueue_script('cdu-script-js', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '1.0.0', true);
    wp_localize_script('cdu-script-js', 'cdu_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
 }

 // capture ajax request

 add_action('wp_ajax_cdu_submit_form_data', 'cdu_ajax_handler'); // when user is logged in
 add_action('wp_ajax_nopriv_cdu_submit_form_data', 'cdu_ajax_handler'); // when user is logged out

 function cdu_ajax_handler() {
    if(!empty($_FILES['csv_data_file']['tmp_name'])) {
        $csvFile = $_FILES['csv_data_file']['tmp_name'];
        $handle = fopen($csvFile, "r");
        global $wpdb;
        $table_name = $wpdb->prefix . 'students_data';
        if(($handle)) {
            $row = 0;
            while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($row == 0) {
                    $row++;
                    continue;
                }

                // insert data into table
                $wpdb->insert($table_name, array(
                    'name' => $data[0],
                    'email' => $data[1],
                    'age' => $data[2],
                    'phone' => $data[3],
                    'photo' => $data[4]
                ));
            }

            fclose($handle);

            echo json_encode([
                'status' => 1,
                'message' => 'Data Inserted Successfully'
            ]);
        }
    } else {
        echo json_encode(array(
            'status' => 0,
            'message' => 'No File Found'
        ));
    }

    exit;
 }