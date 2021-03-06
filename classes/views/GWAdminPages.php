<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class GWAdminPages
 */
class GWAdminPages
{
    protected $wp_option_prefix;
    private $user_data;

    /**
     * GWAdminPages constructor.
     * @param string $option_prefix
     */
    public function __construct($option_prefix="wp_gw_opt")
    {
        $this->user_data = apply_filters('gw_session_user_validate', function($raw){
          return $raw;
        });
        $this->wp_option_prefix = $option_prefix;
    }

    /**
     * Generate page heading
     * @param string $heading
     * @return string
     */
    protected function page_header($title=null, $heading="h1")
    {
        if (empty($title)) {
            $title = esc_html(get_admin_page_title());
        }
        return '<'. $heading .' class="gw-page-header">'. $title .'</'. $heading .'>';
    }

    /**
     * Generate page content
     * @param string $content
     * @param string $class_name
     */
    protected function page_body($content="", $title=null, $class_name="wrap")
    {
        if (! current_user_can('manage_exam')) {
            return;
        }
        echo '<div class="gw-page-body ' . $class_name . '">';
        echo $this->page_header($title);
        include(WP_GW_ROOT . $content);
        echo $this->page_footer();
        echo '</div>';
    }

    /**
     * Generate page footer
     * @param string $content
     * @return string
     */
    protected function page_footer($content="")
    {
        return '<div class="gw-page-footer">' . $content . '</div>';
    }

    /**
     * Create exam upload view
     */
    public function gw_upload_exam()
    {
        $this->page_body(
            '/templ/admin-upload-exam.php'
        );
    }

    /**
     * Create upload old student view
     */
    public function gw_upload_old_student()
    {
        $this->page_body(
            '/templ/import/old-student.php',
            'Upload Old Student Records'
        );
    }

    /**
     * Create admission upload view
     */
    public function gw_admission_info()
    {
        $this->page_body(
            '/templ/admin-upload-admission-info.php'
        );
    }

    /**
     * Create admission upload view
     */
    public function gw_settings_semester()
    {
        $this->page_body(
            '/templ/admin-semester-config.php'
        );
    }

    /**
     * Create student profile view
     */
    public function gw_student_profile()
    {
        $this->page_body(
            '/templ/admin-student-profile.php',
            'Student Profile'
        );
    }

    /**
     * Create update student profile view
     */
    public function gw_student_update()
    {
        $this->page_body(
            '/templ/admin-student-update.php',
            'Update Contact Information'
        );
    }

    /**
     * Create validate student view
     */
    public function gw_student_validate()
    {
        $this->page_body(
            '/templ/admin-student-validate.php',
            'Validate Student'
        );
    }

    /**
     * Create exam results manager view
     */
    public function gw_exam_result_manager_contents()
    {
        if (isset($_GET['sub'])) {
            $sub_value = sanitize_text_field($_GET['sub']);
            $user_id = sanitize_text_field($_GET['id']);
            $user_typ = $this->user_data["utyp"];

            $data_source = new GWDataTable();
            $result = $data_source->get_requested_course_college($user_id, $user_typ);
            $college_capabilites = GWUtility::_gw_get_user_taxonomies('slug');

            switch ($sub_value) {
              case 'gw-student-profile':
                $this->gw_student_profile();
                break;
              case 'gw-student-update':
                $this->gw_student_update();
                break;
              case 'gw-student-validate':

                if (array_search($result, $college_capabilites) === false) {
                    wp_die("Not allowed");
                }

                $this->gw_student_validate();
                break;
            }
            echo sprintf('<div class="gw-parent-action" style="margin: 30px 0;"><a href="?page=%s" class="button button-secondary">Back to Student Lists</a></div>', $_REQUEST['page']);
            return;
        }

        $colleges = implode(", ", GWUtility::_gw_get_user_taxonomies('name'));

        $this->page_body(
            '/templ/admin-exam-result-manager.php',
            "Pre Listing ({$colleges})"
        );
    }

    public function gw_pre_listing_sub_pages($student_type)
    {
        if (isset($_GET['sub'])) {
            $sub_value = sanitize_text_field($_GET['sub']);
            $user_id = sanitize_text_field($_GET['id']);
            $user_typ = $this->user_data["utyp"];

            $data_source = new GWDataTable();
            $result = $data_source->get_requested_course_college($user_id, $user_typ);
            $college_capabilites = GWUtility::_gw_get_user_taxonomies('slug');

            switch ($sub_value) {
            case 'gw-student-profile':
              $this->gw_student_profile();
              break;
            case 'gw-student-update':
              $this->gw_student_update();
              break;
            case 'gw-student-validate':

              if (array_search($result, $college_capabilites) === false) {
                  wp_die("Not allowed");
              }

              $this->gw_student_validate();
              break;
          }
            echo sprintf('<div class="gw-parent-action" style="margin: 30px 0;"><a href="?page=%s" class="button button-secondary">Back to Student Lists</a></div>', $_REQUEST['page']);
            return;
        }
    }


    public function gw_pre_listing_tabs()
    {
        $tab_values = array(
        array("content_label"=>"Pending", "content_id"=>"pending"),
        array("content_label"=>"Approved", "content_id"=>"approved"),
        array("content_label"=>"Denied", "content_id"=>"denied"),
        array("content_label"=>"Inactive", "content_id"=>"inactive")
      );

        return $tab_values;
    }


    // New Gateway Dashboard

    public function gw_pre_listing()
    {
        $this->page_body(
              '/templ/dashboard/dashboard.php',
              "My Dashboard"
          );
    }

    public function gw_pre_listing_new()
    {
        $this->gw_pre_listing_sub_pages("new");
        $colleges = implode(", ", GWUtility::_gw_get_user_taxonomies('name'));

        if(!isset($_GET['sub'])){
          $this->page_body(
              '/templ/dashboard/new-student.php',
              "Pre Listing - New Students ({$colleges})"
          );
        }
    }

    public function gw_pre_listing_old()
    {
        $this->gw_pre_listing_sub_pages("old");
        $colleges = implode(", ", GWUtility::_gw_get_user_taxonomies('name'));

        if(!isset($_GET['sub'])){
          $this->page_body(
              '/templ/dashboard/old-student.php',
              "Pre Listing - Old Students ({$colleges})"
          );
        }
    }
}
