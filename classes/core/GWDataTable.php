<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

/**
 * Class GWDataTable
 */
class GWDataTable
{
    public $logger_table_name;
    public $exam_results_table_name;
    private $charset_collate;

    /**
     * GWDataTable constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->logger_table_name = $wpdb->prefix. 'gw_logger';
        $this->exam_results_table_name = $wpdb->prefix . 'gw_exam_results';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     *  Install Plugin Data Tables
     */
    public function install(){
        $this->loggerInstall();
        $this->examResultInstall();
    }

    /**
     *  Uninstall Plugin Data Tables
     */
    public function uninstall(){
        $this->loggerUninstall();
        $this->examResultUninstall();
    }

    /**
     * Recursively delete plugin wp options
     * @param string $prefix
     */
    public function deleteOptions($prefix=WP_GW_OPTION_PREFIX){
        global $wpdb;

        $plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE ".$prefix."'%'" );

        foreach( $plugin_options as $option ) {
            delete_option( $option->option_name );
        }
    }

    /**
     * loggerInstall
     */
    protected function loggerInstall(){
        $installed_ver = get_option( WP_GW_OPTION_PREFIX . "logger_dt_version" );

        if ( $installed_ver != WP_GW_TABLE_LOG_VERSION ) {

            $this->loggerUpdate();

        }else{

            $sql = "CREATE TABLE $this->logger_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                id_number tinytext NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                type tinytext NULL,
                details text NULL,
                PRIMARY KEY  (id)
                ) $this->charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( WP_GW_OPTION_PREFIX . "gw_logger_dt_version", WP_GW_TABLE_LOG_VERSION );
        }
    }

    /**
     * loggerUpdate
     */
    protected function loggerUpdate(){
        $sql = "CREATE TABLE $this->logger_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                id_number tinytext NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                type tinytext NULL,
                details text NULL,
                PRIMARY KEY  (id)
                ) $this->charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( WP_GW_OPTION_PREFIX . "logger_dt_version", WP_GW_TABLE_LOG_VERSION );
    }

    /**
     *  Drop logger data table
     */
    protected function loggerUninstall(){
        global $wpdb;

        $wpdb->query( "DROP TABLE IF EXISTS  $this->logger_table_name" );
        delete_option( 'gw_logger_dt_version' );
    }

    /**
     * examResultInstall
     */
    protected function examResultInstall(){
        $installed_ver = get_option( WP_GW_OPTION_PREFIX . "exam_results_dt_version" );

        if ( $installed_ver != WP_GW_TABLE_EXAM_RESULT_VERSION ) {
            $this->examResultUpdate();
        }else{

            $sql = "CREATE TABLE $this->exam_results_table_name (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    INSERTION_TIME datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    EXAMINEE_NO varchar(80) NOT NULL,
                    EXAMINATION_DATE varchar(80) NOT NULL,
                    EXAMINATION_TIME varchar(80) NOT NULL,
                    EMAIL_ADDRESS varchar(80) NULL,
                    LAST_NAME varchar(80) NULL,
                    FIRST_NAME varchar(80) NULL,
                    MIDDLE_NAME varchar(80) NULL,
                    NAME_SUFFIX varchar(80) NULL,
                    SEX varchar(80) NULL,
                    BIRTHDATE varchar(80) NULL,
                    CONTACT_NUMBER varchar(80) NULL,
                    ADDRESS varchar(100) NULL,
                    TOTAL varchar(80) NOT NULL,
                    PERCENT varchar(80) NOT NULL,
                    EXAM_STATUS varchar(80) NOT NULL,
                    DEGREE_LEVEL varchar(80) NOT NULL,
                    REQUESTED_COURSE_ID varchar(80) NULL,
                    VALIDATION_REQUIREMENTS varchar(800) NULL,
                    VALIDATION_STATUS varchar(80) NULL,
                    VALIDATION_OFFICER varchar(80) NULL,
                    VALIDATION_FEEDBACK varchar(300) NULL,
                    PRIMARY KEY (id)
                ) $this->charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            add_option( WP_GW_OPTION_PREFIX . "gw_exam_results_dt_version", WP_GW_TABLE_EXAM_RESULT_VERSION );
        }
    }

    /**
     * examResultUpdate
     */
    protected function examResultUpdate(){
        $sql = "CREATE TABLE $this->exam_results_table_name (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    INSERTION_TIME DEFAULT CURRENT_TIMESTAMP,
                    EXAMINEE_NO varchar(80) NOT NULL,
                    EXAMINATION_DATE varchar(80) NOT NULL,
                    EXAMINATION_TIME varchar(80) NOT NULL,
                    EMAIL_ADDRESS varchar(80) NULL,
                    LAST_NAME varchar(80) NULL,
                    FIRST_NAME varchar(80) NULL,
                    MIDDLE_NAME varchar(80) NULL,
                    NAME_SUFFIX varchar(80) NULL,
                    SEX varchar(80) NULL,
                    BIRTHDATE varchar(80) NULL,
                    CONTACT_NUMBER varchar(80) NULL,
                    TOTAL varchar(80) NOT NULL,
                    PERCENT varchar(80) NOT NULL,
                    EXAM_STATUS varchar(80) NOT NULL,
                    DEGREE_LEVEL varchar(80) NOT NULL,
                    PRIMARY KEY (id)
                ) $this->charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( WP_GW_OPTION_PREFIX . "exam_results_dt_version", WP_GW_TABLE_EXAM_RESULT_VERSION );
    }

    /**
     *  Drop exam-result data table
     */
    protected function examResultUninstall(){
        global $wpdb;

        $wpdb->query( "DROP TABLE IF EXISTS  $this->exam_results_table_name" );
        delete_option( 'gw_exam_results_dt_version' );
    }

    /**
     * Returns action method status
     *
     * @param string $method Function name of the method
     * @param $is_status
     * @param string $message
     * @return array
     */
    protected function getActionStatus( $method, $is_status, $message = '' ){
        global $wpdb;

        //$this->legacyLogger($method); // Log all actions into a file
        if( 0 == $is_status ){
            return array(
                'method'   => $method,
                'message'  => $message,
                'id'       => 0,
                'status'   => 'error'
            );
        }
        return array(
            'method'    => $method,
            'status'    => 'success',
            'id'        => $wpdb->insert_id );
    }

    /**
     *  Logs all data modification actions
     *
     * @param $action
     */
    public function legacyLogger($action){
        if (is_user_logged_in()) {
            global $current_user;
            wp_get_current_user();
            $username = $current_user->user_login;
        } else {
            $username = "anonymous";
        }
        $logMsg = "GWDT, user: (" . $username . "), timestamp:" . GWUtility::instance()->get_date() . ', action:'. $action . PHP_EOL;
        file_put_contents('qrdt-legacy.logs', $logMsg, FILE_APPEND | LOCK_EX);
    }

    /**
     * insertLog
     *
     * @param $id
     * @param $type
     * @param $details
     * @return array|string[]
     */
    public function insertLog($id, $type, $details){
        global $wpdb;

        $action = $wpdb->insert(
            $this->logger_table_name,
            array(
                'id_number' => $id,
                'time' => current_time( 'mysql' ),
                'type' => $type,
                'details' => $details,
            )
        );

        return $this->getActionStatus(__FUNCTION__, $action);
    }

    /**
     * Get Logs by type
     *
     * @param $type
     * @return mixed
     */
    public function getLog($type){
        global $wpdb;

        return $wpdb->get_row("SELECT * FROM ".  $this->logger_table_name ." WHERE type LIKE BINARY '".$type."'", ARRAY_A);
    }

    /**
     * Reset Logs by type
     *
     * @param $type
     * @return array|string[]
     */
    public function resetLog($type){
        global $wpdb;

        $action = $wpdb->delete( $this->logger_table_name, array( 'type' => $type ), array( '%d' ) );

        return $this->getActionStatus(__FUNCTION__, $action);
    }

    public function truncateExamResults(){
      global $wpdb;

      $action = $wpdb->query("TRUNCATE TABLE `{$this->exam_results_table_name}`");

      return $this->getActionStatus(__FUNCTION__, $action);
    }

    public function getUser($data_entry){
      global $wpdb;

      $query = "SELECT * FROM {$this->exam_results_table_name} where
    		EXAMINEE_NO='{$data_entry["EXAMINEE_NO"]}' AND
    		EXAMINATION_DATE='{$data_entry["EXAMINATION_DATE"]}' AND
    		EXAMINATION_TIME='{$data_entry["EXAMINATION_TIME"]}' AND
    		BIRTHDATE='{$data_entry["BIRTHDATE"]}'";

    	return $wpdb->get_results($query, OBJECT);
    }

    public function getExamEntries($items_per_page = 20, $current_page = 1, $search=null, $degree_level='college'){
      global $wpdb;

      $offset = ( $current_page * $items_per_page ) - $items_per_page;

      $fields = 'id,
        EXAMINEE_NO as examinee_no,
        LAST_NAME as last_name,
        FIRST_NAME as first_name,
        REQUESTED_COURSE_ID as requested_course,
        PERCENT as percent,
        EXAM_STATUS as exam_status,
        VALIDATION_STATUS as validation_status';

      $query = "SELECT {$fields} FROM {$this->exam_results_table_name}";
      $degree_level_query = "DEGREE_LEVEL like '{$degree_level}'";

      if(!empty($search)){ // Implement search query
        $search_keyword = sanitize_text_field($search);
        $query.= " WHERE
            (
              EXAMINEE_NO LIKE '%{$search_keyword}%'
              OR EMAIL_ADDRESS LIKE '%{$search_keyword}%'
              OR LAST_NAME LIKE '%{$search_keyword}%'
              OR FIRST_NAME LIKE '%{$search_keyword}%'
              OR MIDDLE_NAME LIKE '%{$search_keyword}%'
              OR NAME_SUFFIX LIKE '%{$search_keyword}%'
              OR BIRTHDATE LIKE '%{$search_keyword}%'
              OR CONTACT_NUMBER LIKE '%{$search_keyword}%'
              AND {$degree_level_query}
            )";
      } else {
        $query.= " WHERE {$degree_level_query}";
      }

      $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
      $total = $wpdb->get_var( $total_query );

      $action = $wpdb->get_results("{$query} ORDER BY id DESC LIMIT {$offset}, {$items_per_page}", ARRAY_A );

      return array( "results"=>$action, "total"=>$total);
      //return $this->getActionStatus(__FUNCTION__, $action);
    }

    /**
     * insertExamResult
     *
     * @param $entry
     * @param string $degree_level
     * @return array|string[]
     */
    public function insertExamResult( $entry, $degree_level = 'college' ){
        global $wpdb;

        $action = $wpdb->insert(
            $this->exam_results_table_name,
            array(
                'INSERTION_TIME' => current_time( 'mysql' ),
                'EXAMINEE_NO' => $entry['EXAMINEE_NO'],
                'EXAMINATION_DATE' => $entry['EXAMINATION_DATE'],
                'EXAMINATION_TIME' => $entry['EXAMINATION_TIME'],
                'EMAIL_ADDRESS' => $entry['EMAIL_ADDRESS'],
                'LAST_NAME' => $entry['LAST_NAME'],
                'FIRST_NAME' => $entry['FIRST_NAME'],
                'MIDDLE_NAME' => $entry['MIDDLE_NAME'],
                'NAME_SUFFIX' => $entry['NAME_SUFFIX'],
                'SEX' => $entry['SEX'],
                'BIRTHDATE' => $entry['BIRTHDATE'],
                'CONTACT_NUMBER' => $entry['CONTACT_NUMBER'],
                'TOTAL' => $entry['TOTAL'],
                'PERCENT' => $entry['PERCENT'],
                'EXAM_STATUS' => $entry['EXAM_STATUS'],
                'DEGREE_LEVEL' => $degree_level
            )
        );
        return $this->getActionStatus(__FUNCTION__, $action);
    }

    /**
     * Delete user entry
     * @param $id_number
     * @return array|string[]
     */
    public function deleteExamResult($id_number){
        global $wpdb;

        $action = $wpdb->delete( $this->exam_results_table_name, array( 'id_number' => $id_number ) );

        return $this->getActionStatus(__FUNCTION__, $action);
    }

    /**
     * Update user status
     *
     * @param $id_number
     * @param $status
     * @return array|string[]
     */
    public function updateExamResultStatus($unique_id, $status){
        global $wpdb;

        $action = $wpdb-> update(
            $this->exam_results_table_name,
            array( 'status' => $status ),
            array( 'id' => ucwords($id_number) ),
            array( '%s', '%s' )
        );

        return $this->getActionStatus(__FUNCTION__, $action);
    }

    /**
     * Get user data
     *
     * @param $id_number
     * @return mixed
     */
    public function getExamResultData($unique_id){
        global $wpdb;

        return $wpdb->get_row("SELECT * FROM {$this->exam_results_table_name} WHERE id LIKE BINARY '{$unique_id}'", ARRAY_A);
    }

    /**
     * Check if exam results already exist
     * @param $data_entry
     * @return bool
     */
    public function isExamResultDataExist($data_entry){
        global $wpdb;
        $cntSQL = "SELECT count(*) as count FROM {$this->exam_results_table_name} where
              EXAMINEE_NO='{$data_entry["EXAMINEE_NO"]}' AND
              EXAMINATION_DATE='{$data_entry["EXAMINATION_DATE"]}' AND
              EXAMINATION_TIME='{$data_entry["EXAMINATION_TIME"]}' AND
              BIRTHDATE='{$data_entry["BIRTHDATE"]}' AND
              LAST_NAME='{$data_entry["LAST_NAME"]}' AND
              FIRST_NAME='{$data_entry["FIRST_NAME"]}' AND
              DEGREE_LEVEL='{$data_entry["DEGREE_LEVEL"]}'";

        return $wpdb->get_results($cntSQL, OBJECT);
    }

    /**
     * Check if exam results already exist
     * @param $data_entry
     * @return bool
     */
    public function isCourseApplicationExist($id, $examinee_number){
        global $wpdb;
        $cntSQL = "SELECT count(*) AS is_exist
        FROM buksu_gateway_gw_exam_results
         WHERE ID = '{$id}' AND
         EXAMINEE_NO = '{$examinee_number}' AND
         VALIDATION_STATUS IN ('pending', 'approved') AND
         REQUESTED_COURSE_ID <> ''";

        return $wpdb->get_results($cntSQL, OBJECT)[0]->{'is_exist'};
    }

}
