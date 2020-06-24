<?php


class GWFrontEnd
{

    public function __construct(){

        // Frontend template rendering
        add_action('gw_get_request', array( $this, 'get_request_navigator'), 15);
        add_action('gw_get_request', array( $this, 'get_request'), 18);
        add_filter('gw_template_prepare', array( $this, 'template_prepare'), 10, 2);
        add_action('gw_template_build', array( $this, 'template_head_tag'), 35);
        add_action('gw_template_build', array( $this, 'template_content'), 36);
        add_action('gw_template_build', array( $this, 'template_end_tag'), 40);
        add_action('gw_template_build_header', array( $this, 'template_header'));
        add_action('gw_template_render', array( $this, 'template_render'), 45);
        add_action('gw_frontend_render', array( $this, 'render_ui'), 50);

        // Page template for `my` slug
        add_filter('page_template', array( $this, 'frontend_template'));

        add_action('gw_pass_process', array($this, 'pass_process'));
        add_filter('gw_user_set', array($this, 'user_set'));
    }

    public function get_request_navigator(){
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            if (isset($_GET['page'])) {
                $page_name =  sanitize_text_field($_GET['page']); // Sanitize page value
                switch ($page_name) {
                    case 'login':
                        do_action('gw_validate_login', true, false); // Redirect to dashboard if login
                        apply_filters('gw_template_prepare', array( $this, 'gw_login'));
                        break;
                    case 'login-new-student':
                        do_action('gw_validate_login', true, false); // Redirect to dashboard if login
                        apply_filters('gw_template_prepare', array( $this, 'gw_login_new_ui'), 1);
                        break;
                    case 'login-old-student':
                        do_action('gw_validate_login', true, false); // Redirect to dashboard if login
                        apply_filters('gw_template_prepare', array( $this, 'gw_login_old_ui'), 1);
                        break;
                    case 'logout':
                        apply_filters('gw_session_reset', '');
                        GWUtility::_gw_redirect('login');
                        break;
                    case 'pass_process':
                        do_action('gw_pass_process');
                        break;
                    case 'pass_success':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_success'), 1);
                        break;
                    case 'pass_fail':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_fail'), 1);
                        break;
                    case 'pass_welcome':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_welcome'), 2);
                        break;
                    case 'pass_courses':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_courses'), 2);
                        break;
                    case 'pass_course_apply':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_course_apply'), 2);
                        break;
                    case 'pass_course_pending':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_course_pending'), 2);
                        break;
                    case 'pass_course_success':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_course_success'), 3);
                        break;
                    case 'pass_enrollment_fill':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_enrollment_fill'), 3);
                        break;
                    case 'pass_enrollment_verify':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_enrollment_verify'), 4);
                        break;
                    case 'pass_enrollment_welcome':
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_enrollment_welcome'), 5);
                        break;
                    default:
                        GWUtility::_gw_redirect('login');
                        break;
                }
                return;
            }
        }
        GWUtility::_gw_redirect('login');
    }

    public function get_request(){
        $get_template = apply_filters('gw_template_prepare', null, null);
        list($ui_name, $page_progress) = $get_template;
        do_action('gw_load_validation', $ui_name, $page_progress);
    }

    public function template_prepare($ui_name=null, $page_progress=null){
        $current_template = array($ui_name, $page_progress);
        if (!empty($ui_name)) {
            $GLOBALS['gw_template_prepared'] = $current_template;
            return $GLOBALS['gw_template_prepared'];
        }
        return $GLOBALS['gw_template_prepared'];
    }

    public function template_head_tag(){
        echo '<div class="gw-main-container">';
    }

    public function template_end_tag(){
        echo '</div>';
    }

    public  function template_header($page_progress){
        apply_filters('gw_breadcrumbs_progress', $page_progress); // Set page progress
        echo do_shortcode('[elementor-template id="542"]'); // Header
    }

    public function template_content(){
        $get_template = apply_filters('gw_template_prepare', null, null);
        list($ui_name, $page_progress) = $get_template;
        if ($page_progress!=0) {
            do_action('gw_template_build_header', $page_progress);
        }
        if (!empty($ui_name)) {
            do_action('gw_template_message');
            call_user_func_array($ui_name, array());
        }
    }

    public function template_render(){
        get_header();
        echo '<div id="primary" class="site-content">
				<div id="content" role="main">';
        echo do_action('gw_template_build');
        echo '</div><!-- #content -->
				</div><!-- #primary -->';
        get_footer();
    }



    public function render_ui(){
        do_action('gw_get_request');
        do_action('gw_template_render');
    }



    public function frontend_template( $page_template )
    {
        if ( is_page( 'my' ) ) {
            $page_template = WP_GW_ROOT . '/templ/gw-frontend-template.php';
        }
        return $page_template;
    }




    public function pass_process()
    {
        print_r("Processing...");
        if (apply_filters('gw_session_validate', '')) {
            $user_meta = apply_filters('gw_user_set', null);
            switch ($user_meta->{'STUDENT_TYPE'}) {
                case 'new':
                    do_action('gw_validate_exam_status', true);
                    break;
                case 'old':
                    // code...
                    break;
                default:
                    // Unknown user type; Force reset Auth cookies
                    apply_filters('gw_session_reset', null);
                    apply_filters('gw_session_validate', null);
                    break;
            }
        }
    }

    public function user_set($object_data=null)
    {
        if ($object_data) {
            $GLOBALS['gw_current_user'] = $object_data;
            return $GLOBALS['gw_current_user'];
        }
        return $GLOBALS['gw_current_user'];
    }


    // gw_* template functions

    public function gw_login()
    {
        echo do_shortcode('[elementor-template id="595"]');
    }

    // Login UI old Student
    public function gw_login_old_ui()
    {
        include WP_GW_ROOT . '/templ/gw-old-student-login.php';
    }

    // Login UI new Student
    public function gw_login_new_ui()
    {
        include WP_GW_ROOT . '/templ/gw-new-student-login.php';
    }

    // Did Passed UI
    public function gw_pass_success()
    {
        //do_action('gw_validate_session');
        echo do_shortcode('[elementor-template id="532"]');
    }

    // Did not Pass UI
    public function gw_pass_fail()
    {
        echo do_shortcode('[elementor-template id="536"]');
    }

    // Congratulation UI
    public function gw_pass_welcome()
    {
    }

    // Select Course UI
    public function gw_pass_courses()
    {
        //do_action('gw_validate_session');
        echo do_shortcode('[elementor-template id="539"]');
    }

    // Apply Course UI
    public function gw_pass_course_apply()
    {
        //do_action('gw_validate_session');
        //do_action('gw_validate_course_availability');
        //do_action('gw_validate_request');
        echo do_shortcode('[elementor-template id="568"]');
    }

    // Pending Course Application
    public function gw_pass_course_pending()
    {
        //do_action('gw_validate_session');
        //do_action('gw_validate_course_availability');
        //do_action('gw_validate_request');
        echo do_shortcode('[elementor-template id="568"]');
    }

    // Approve Course Application
    public function gw_pass_course_success()
    {
        //do_action('gw_validate_session');
    }

    // Fill Enrollment Data
    public function gw_pass_enrollment_fill()
    {
        //do_action('gw_validate_session');
    }

    // Welcome to University
    public function gw_pass_enrollment_welcome()
    {
        //do_action('gw_validate_session');
    }

    // Verify Enrollment
    public function gw_pass_enrollment_verify()
    {
        //do_action('gw_validate_session');
    }

}

new GWFrontEnd();