<?php

if (! defined( 'ABSPATH' ) ){
    exit;
}

/**
 * Class GWAdminPages
 */
class GWAdminPages
{

    protected $wp_option_prefix;

    /**
     * GWAdminPages constructor.
     * @param string $option_prefix
     */
    public function __construct($option_prefix="wp_gw_opt") {
        $this->wp_option_prefix = $option_prefix;
    }

    /**
     * Generate page heading
     * @param string $heading
     * @return string
     */
    protected function page_header($heading="h1"){
        $title = esc_html( get_admin_page_title() );
        return '<'. $heading .' class="gw-page-header">'. $title .'</'. $heading .'>';
    }

    /**
     * Generate page content
     * @param string $content
     * @param string $class_name
     */
    protected function page_body($content="", $class_name="wrap"){
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="gw-page-body ' . $class_name . '">';
        echo $this->page_header();
        include (WP_GW_ROOT . $content);
        echo $this->page_footer();
        echo '</div>';
    }

    /**
     * Generate page footer
     * @param string $content
     * @return string
     */
    protected function page_footer($content=""){
        return '<div class="gw-page-footer">' . $content . '</div>';
    }

    /**
     * Create settings view
     */
    public function gw_upload_exam(){
        $this->page_body(
            '/templ/admin-upload-exam.php'
        );
    }

    /**
     * Create exam results manager view
     */
    public function gw_exam_result_manager_contents() {
        $this->page_body(
            '/templ/admin-exam-result-manager.php'
        );
    }

}