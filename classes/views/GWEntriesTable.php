<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class GWEntriesTable extends WP_List_Table
{
    public function __construct($args = array())
    {
        parent::__construct([
            'singular' => __('Exam Result Entry', 'gw-buksu'),
            'plural'   => __('Exam Results', 'gw-buksu'),
            'ajax'     => false
        ]);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'last_name':
            case 'first_name':
            case 'percent':
            case 'exam_status':
            case 'validation_status':
                return strtoupper($item[ $column_name ]);
            case 'requested_course':
                $course_id = $item[ $column_name ];
                if (empty($course_id)) {
                    $course_id = null;
                }
                return apply_filters('gw_get_course_meta_id', $course_id, 'get_the_title', null);
            case 'examinee_no':
                return sprintf(
                    '<a href="?page=%s&sub=%s&id=%s" target="_blank">%s</a>',
                    $_REQUEST['page'],
                    'gw-student-profile',
                    $item['id'],
                    $item[ $column_name ]
                );
            default:
                return print_r($item, true) ;
        }
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="gw_entry[]" value="%s" />',
            $item['id']
        );
    }

    public function get_columns()
    {
        $columns = array(
            'cb'                    => '<input type="checkbox" />',
            'examinee_no'           => 'Examinee No.',
            'last_name'             => 'Last Name',
            'first_name'            => 'First Name',
            'requested_course'      => 'Requested Course',
            'percent'               => 'Percentile',
            'exam_status'           => 'Exam Status',
            'validation_status'     => 'Validation Status'

        );
        return $columns;
    }

    public function no_items()
    {
        _e('No exam entries found');
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'examinee_no'  => array('examinee_no',false),
            'last_name' => array('last_name',false),
            'first_name'   => array('first_name',false),
            'requested_course'  => array('requested_course',false),
            'percent' => array('percent',false),
            'exam_status' => array('exam_status',false),
            'validation_status'   => array('validation_status',false)
        );
        return $sortable_columns;
    }

    protected function get_views()
    {
        $views = array();
        $current = (!empty($_REQUEST['exam_status']) ? $_REQUEST['exam_status'] : 'all');

        $exam_entries_instance = new GWDataTable();

        if (empty($_REQUEST['tab'])) {
            return;
        }

        $status = strtolower(esc_sql( $_REQUEST['tab'] ));

        $passed_count = $exam_entries_instance->getExamCount($status, 'PASSED');
        $failed_count = $exam_entries_instance->getExamCount($status, 'FAILED');

        //All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg('exam_status');
        $views['all'] = "<a href='{$all_url }' {$class} >All</a>";

        // Passed the exam
        $foo_url = add_query_arg('exam_status', 'passed');
        $class = ($current == 'passed' ? ' class="current"' :'');
        $views['passed'] = "<a href='{$foo_url}' {$class} >Passed Exam ({$passed_count})</a>";

        // Failed to pass exam
        $bar_url = add_query_arg('exam_status', 'failed');
        $class = ($current == 'failed' ? ' class="current"' :'');
        $views['failed'] = "<a href='{$bar_url}' {$class} >Failed to Pass ({$failed_count})</a>";

        return $views;
    }

    public static function get_exam_entries($per_page = 5, $page_number = 1, $search="", $status="", $degree_level="college")
    {
        global $wpdb;

        $data_table = new GWDataTable();

        // Field to get
        $fields = 'id,
          EXAMINEE_NO as examinee_no,
          LAST_NAME as last_name,
          FIRST_NAME as first_name,
          REQUESTED_COURSE_ID as requested_course,
          PERCENT as percent,
          EXAM_STATUS as exam_status,
          VALIDATION_STATUS as validation_status';

        if ($status == 'inactive') {
            $validation_query = "VALIDATION_STATUS IS NULL OR VALIDATION_STATUS = ''";
        } else {
            $validation_query = "VALIDATION_STATUS like '{$status}'";
        }

        $query = "SELECT {$fields} FROM {$data_table->exam_results_table_name}";
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

        if(! empty($_REQUEST['exam_status']) ){
          $exam_status = strtolower(esc_sql( $_REQUEST['exam_status'] ));
          if( $exam_status == 'passed'){
            $query.= " AND EXAM_STATUS = 'PASSED'";
          }elseif ( $exam_status == 'failed') {
            $query.= " AND EXAM_STATUS = 'FAILED'";
          }
        }

        $query.= "AND {$validation_query}";

        if (! empty($_REQUEST['orderby'])) {
            $query .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $query .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
        $total = $wpdb->get_var( $total_query );

        $query .= " LIMIT $per_page";
        $query .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($query, 'ARRAY_A');

        return array( "results"=> $result, "total"=> $total);
    }

    public function prepare_items($status="", $search=null)
    {
        $user = wp_get_current_user();
        $entry_manager = new GWEntriesManager($user->ID);
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('entries_per_page');
        $current_page = $this->get_pagenum();

        $data_source = self::get_exam_entries($per_page, $current_page, $search, $status);

        $total_items = $data_source['total'];

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ));

        $this->items = $data_source['results'];

        // $custom_caps = array_keys($user->caps);
        // print_r($custom_caps);
        // echo array_search("eo_" ,$custom_caps, true);
    }
}
