<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class GWEntriesTable extends WP_List_Table
{

    function __construct($args = array())
    {
        parent::__construct( [
            'singular' => __( 'Exam Result Entry', 'gw-buksu' ),
            'plural'   => __( 'Exam Results', 'gw-buksu' ),
            'ajax'     => true
        ] );
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'examinee_no':
            case 'last_name':
            case 'first_name':
            case 'requested_course':
            case 'percent':
            case 'exam_status':
            case 'validation_status':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="gw_entry[]" value="%s" />', $item['ID']
        );
    }

    function get_columns(){
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

    function column_examinee_no($item) {
        $actions = array(
            'more'      => sprintf('<a href="?page=%s&action=%s&book=%s">More</a>',$_REQUEST['page'],'edit',$item['ID']),
            'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Approve Request</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );

        return sprintf('%1$s %2$s', $item['examinee_no'], $this->row_actions($actions) );
    }

    function no_items() {
        _e( 'No exam entries found' );
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function get_sortable_columns() {
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

    function usort_reorder( $a, $b ) {
        // If no sort, default to title
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'examinee_no';
        // If no order, default to asc
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp( $a[$orderby], $b[$orderby] );
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    function prepare_items($search='') {

        $example_data = array(
            array('ID' => 1,
                  'examinee_no' => 'BUKSU-MAIN-00012',
                  'last_name' => 'Alvarado',
                  'first_name' => 'Aubrey',
                  'requested_course' => 'Bachelor in Science of Information Technology',
                  'percent' => '63.64%',
                  'exam_status' => 'PASSED',
                  'validation_status' => 'VALIDATED'
            ),
            array('ID' => 1,
                'examinee_no' => 'BUKSU-MAIN-00026',
                'last_name' => 'Amposta',
                'first_name' => 'Fitz Loren',
                'requested_course' => 'Bachelor in Science of Mathematics',
                'percent' => '39.09%',
                'exam_status' => 'FAILED',
                'validation_status' => 'PENDING'
            ),
        );

        $this->_column_headers = $this->get_column_info();
        usort( $example_data, array( &$this, 'usort_reorder' ) );

        $per_page = $this->get_items_per_page( 'gw_entries_per_page');

        print_r($per_page);

        $current_page = $this->get_pagenum();
        $total_items = count($example_data);

        $found_data = array_slice($example_data,(($current_page-1)*$per_page),$per_page);

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );
        $this->items = $found_data;

    }

}