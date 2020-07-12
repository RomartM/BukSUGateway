<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class GWEntriesOldStudentTable extends WP_List_Table
{
    public function __construct($args = array())
    {
        parent::__construct([
            'singular' => __('Pre Listing Entry', 'gw-buksu'),
            'plural'   => __('Pre Listings', 'gw-buksu'),
            'ajax'     => false
        ]);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'last_name':
            case 'first_name':
            case 'validation_status':
                return strtoupper($item[ $column_name ]);
            case 'requested_course':
                $course_id = $item[ $column_name ];
                if (empty($course_id)) {
                    $course_id = null;
                }
                return apply_filters('gw_get_course_meta_id', $course_id, 'get_the_title', null);
            case 'id_number':
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
            'id_number'             => 'ID Number',
            'last_name'             => 'Last Name',
            'first_name'            => 'First Name',
            'requested_course'      => 'Course',
            'validation_status'     => 'Validation Status'

        );
        return $columns;
    }

    public function no_items()
    {
        _e('No student entries found');
    }

    public function get_bulk_actions()
    {
        // $actions = array(
        //     'delete'    => 'Delete'
        // );
        return array();
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'examinee_no'  => array('examinee_no',false),
            'last_name' => array('last_name',false),
            'first_name'   => array('first_name',false),
            'course'  => array('requested_course',false),
            'percent' => array('percent',false),
            'exam_status' => array('exam_status',false),
            'validation_status'   => array('validation_status',false)
        );
        return $sortable_columns;
    }

    protected function get_views()
    {
        $views = array();
        $current_level = (!empty($_REQUEST['level']) ? $_REQUEST['level'] : 'all');

        if (empty($_REQUEST['tab'])) {
            $_REQUEST['tab'] = 'pending';
        }

        $status = strtolower(esc_sql( $_REQUEST['tab'] ));

        // College
        $bar_url = add_query_arg('level', 'college');
        $class = ($current_level == 'college' ? ' class="current"' :'');
        $views['college'] = "<a href='{$bar_url}' {$class} >College</a>";

        // Doctors
        $bar_url = add_query_arg('level', 'doctors');
        $class = ($current_level == 'doctors' ? ' class="current"' :'');
        $views['doctor'] = "<a href='{$bar_url}' {$class} >Doctorate</a>";

        // Masters
        $bar_url = add_query_arg('level', 'masters');
        $class = ($current_level == 'masters' ? ' class="current"' :'');
        $views['masters'] = "<a href='{$bar_url}' {$class} >Masters</a>";

        // Law
        $bar_url = add_query_arg('level', 'law');
        $class = ($current_level == 'law' ? ' class="current"' :'');
        $views['law'] = "<a href='{$bar_url}' {$class} >Law</a>";


        return $views;
    }

    public static function get_exam_entries($per_page = 5, $page_number = 1, $search="", $status="", $degree_level="college")
    {
        global $wpdb;

        $data_table = new GWDataTable();

        // Field to get
        $fields = 'id,
          ID_NUMBER as id_number,
          LAST_NAME as last_name,
          FIRST_NAME as first_name,
          REQUESTED_COURSE_ID as requested_course,
          VALIDATION_STATUS as validation_status';

        if ($status == 'inactive') {
            $validation_query = "VALIDATION_STATUS IS NULL OR VALIDATION_STATUS = ''";
        } else {
            $validation_query = "LOWER(VALIDATION_STATUS) like LOWER('{$status}')";
        }

        if(isset($_REQUEST['level'])){
          $degree_level = esc_sql($_REQUEST['level']);
        }

        $query = "SELECT {$fields} FROM {$data_table->old_student_table_name}";
        $degree_level_query = "LOWER(DEGREE_LEVEL) like LOWER('{$degree_level}')";

        if(!empty($search)){ // Implement search query
          $search_keyword = sanitize_text_field($search);
          $query.= " WHERE
              (
                ID_NUMBER LIKE '%{$search_keyword}%'
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

        if($status !== 'inactive'){
            $college_slug = implode("', '", GWUtility::_gw_get_user_taxonomies('slug'));
            $query .= " AND REQUESTED_COURSE_COLLEGE IN ('{$college_slug}')";
        }

        $query.= " AND {$validation_query}";

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
