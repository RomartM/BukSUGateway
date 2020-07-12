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
                    case 'login-instant':
                        do_action('gw_validate_login', true, false); // Redirect to dashboard if login
                        apply_filters('gw_template_prepare', array( $this, 'gw_login_instant_ui'), 1);
                        break;
                    case 'login-old-student':
                        do_action('gw_validate_login', true, false); // Redirect to dashboard if login
                        apply_filters('gw_template_prepare', array( $this, 'gw_login_old_ui'), 1);
                        break;
                    case 'logout':
                        do_action( 'gw_session_login_set_reset' );
                        do_action( 'gw_session_user_set_reset' );
                        GWUtility::_gw_redirect('login');
                        break;
                    case 'pass_process':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_pass_process');
                        break;
                    case 'pass_success':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', true, false);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_success'), 1);
                        break;
                    case 'pass_fail':
                        do_action('gw_validate_login', false, true);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_fail'), 1);
                        break;
                    case 'pass_welcome':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', true, false);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_welcome'), 2);
                        break;
                    case 'update_contact_info':
                        do_action('gw_validate_login', false, true);
                        $this->gw_update_contact_info();
                        exit();
                        break;
                    case 'pass_courses':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', true, false);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_courses'), 2);
                        break;
                    case 'pass_course_apply':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', true, false, false);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_course_apply'), 2);
                        break;
                    case 'pass_course_pending':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', false, true);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_course_pending'), 2);
                        break;
                    case 'pass_course_success':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', false, true);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_course_success'), 3);
                        break;
                    case 'pass_enrollment_fill':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', false, true);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_enrollment_fill'), 3);
                        break;
                    case 'pass_enrollment_verify':
                        do_action('gw_validate_login', false, true);
                        do_action('gw_validate_request_status', false, true);
                        apply_filters('gw_template_prepare', array( $this, 'gw_pass_enrollment_verify'), 4);
                        break;
                    case 'pass_enrollment_welcome':
                    do_action('gw_validate_login', false, true);
                    do_action('gw_validate_request_status', false, true);
                        $this->gw_pass_enrollment_welcome();
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

        if(empty($GLOBALS['gw_template_prepared'])){
          return false;
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
        apply_filters('gw_session_login_validate', function($raw){
          switch ($raw["utyp"]) {
            case 'new':
              do_action('gw_validate_new_student_exam_status', true);
              break;
            case 'old':
              do_action( 'gw_validate_old_student_course_preference', true );
              break;
            default:
              do_action('gw_session_reset');
              break;
          }
        });
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
    	echo do_shortcode('[elementor-template id="820"]');
    }

    // Login UI new Student
    public function gw_login_new_ui()
    {
    	echo do_shortcode('[elementor-template id="817"]');
    }

    // Login Intant via Transaction Code
    public function gw_login_instant_ui()
    {
    	echo do_shortcode('[elementor-template id="821"]');
    }

    public function gw_update_contact_info(){
        include WP_GW_ROOT . '/templ/gw-new-student-update-contact.php';
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
        echo do_shortcode('[elementor-template id="668"]');
        //do_action('gw_validate_session');
    }

    // Fill Enrollment Data
    public function gw_pass_enrollment_fill()
    {
        echo do_shortcode('[elementor-template id="673"]');
        echo "<script>
          (function($){
            $(function () {

            $('form.main-info-form').on('submit', function (e) {

              e.preventDefault();

              $.ajax({
                type: 'post',
                url: $('form.main-info-form').attr('action'),
                data: $('form.main-info-form').serialize(),
                success: function (data) {
                  if(data.status == 'success'){
                    $('.uael-trigger[data-modal=\"216b767\"]').click();
                  }else{
                    alert(data.message);
                  }
                }
              });

            });

          });
          })(jQuery);
        </script>";
    }

    // Welcome to University
    public function gw_pass_enrollment_welcome()
    {
        echo "<style>#wpadminbar{display:none;}
              .gw-clipboard{-webkit-user-select: all;-moz-user-select:all;-ms-user-select:all;user-select:all;}
              span.gw-clipboard { background-color: #9e9e9e70; border-radius: 7px; padding: 2px 7px; float: right; }
              .only-print {
                display:none;
              }
              @media print {
                .gw-main-header, .no-print { display: none; }
                .only-print { display: block !important }
              }
              </style>";
        echo do_shortcode('[elementor-template id="680"]');
    }

    // Verify Enrollment
    public function gw_pass_enrollment_verify()
    {
        //do_action('gw_validate_session');
    }

}

new GWFrontEnd();
