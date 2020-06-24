<?php


class GWShortCodes
{
    public function __construct()
    {
        // Filters

        add_filter('gw_breadcrumbs_progress', array( $this, 'breadcrumbs_progress'));
        add_filter('gw_selected_course', array( $this, 'selected_course'));
        add_filter('gw_current_user_login', array( $this, 'current_user_login'));

        // Verifications
        add_action('gw_validate_course_availability', array( $this, 'validate_course_availability'));
        add_filter('gw_validate_submitted_information', array( $this, 'validate_submitted_information'));

        // Shortcodes

        add_shortcode('iframe', array($this, 'iframe'));
        add_shortcode('get-course-list', array( $this, 'get_course_list'));
        add_shortcode('gw-breadcrumbs', array( $this, 'breadcrumbs'));
        add_shortcode('gw_current_course', array( $this, 'current_course'));
        add_shortcode('gw_applied_course', array( $this, 'applied_course'));
        add_shortcode('gw_submitted_info', array( $this, 'submitted_info'));
        add_shortcode('gw_current_user', array( $this, 'current_user'));

    }

    public function iframe($atts, $content=null){
        extract(shortcode_atts(array(
            'url'      => '',
            'scrolling'      => 'yes',
            'width'      => '100%',
            'height'      => '85vh',
            'frameborder'      => '0',
            'marginheight'      => '0',
        ), $atts));

        if (empty($url)) return '<!-- Iframe: You did not enter a valid URL -->';

        if($url == 'form_link'){
            $url = do_shortcode('[acf field="form_link"]');
        }

        return '<iframe src="'.$url.'" title="" width="'.$atts['width'].'" height="'.$atts['height'].'" scrolling="'.$atts['scrolling'].'" frameborder="'.$atts['frameborder'].'" marginheight="'.$atts['marginheight'].'"><a href="'.$url.'" target="_blank">'.$url.'</a></iframe>';
    }

    public function _gw_load_course($course_slug){
        $query_args = array(
            'name'        => $course_slug,
            'post_type'   => 'courses',
            'post_status' => 'publish',
            'numberposts' => 1
        );

        $query_course = new WP_Query( $query_args );
        if( $query_course->have_posts() ){
            while( $query_course->have_posts() ) : $query_course->the_post();
                $cat = get_the_category();
                $course_obj = array(
                    'title' => get_the_title(),
                    'slug' => $course_slug,
                    'college' => $cat[0]->cat_name,
                    'slots_available' => get_field('slots_available'),
                    'requirement_percentage' => get_field('requirement_percentage')
                );
                apply_filters( 'gw_selected_course', $course_obj ); // Set course data
            endwhile;
            return true;
        }else{
            return false;
        }
    }

    public function get_course_list($atts){
        $attr_data = shortcode_atts( array(
            'college' => ''
        ), $atts );

        $args = array(
            'post_status' => 'publish',
            'post_type' => 'courses',
            'order' => 'ASC',
            'orderby' => 'title',
            'category_name' => $attr_data['college'],
        );
        // query
        $the_query = new WP_Query( $args );

        ?>
        <?php if( $the_query->have_posts() ): ?>
            <div class="gw-college-sc">
                <div class="gw-college-label"><h4><?php echo $attr_data['college']; ?></h4></div>
                <div class="gw-course-wrapper gw-college-<?php echo $the_query->get('category_name'); ?>">
                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                        <?php
                        $user_data = apply_filters('gw_current_user_login', null);
                        $course_minimum_percent = get_field('requirement_percentage');
                        if( get_field('gw_enable') && $course_minimum_percent <= $user_data->{'PERCENT'}):

                            // Generate apply link
                            $get_slug_name = stripslashes(get_post_field( 'post_name', get_post() ));
                            $remaining_slots = get_field('slots_available') - $this->_gw_course_availed_counts($get_slug_name);
                            $applied_course = do_shortcode("[gw_applied_course field='course_slug']");
                            $href = add_query_arg( array(
                                'page' => 'pass_course_apply',
                                'course' => $get_slug_name,
                            ), _gw_current_page_url(null) );

                            ?>
                            <div class="gw-course-item gw-c-id-<?php the_ID(); ?> <?php  echo ($applied_course == $get_slug_name) ? 'gw-selected-course' : ''  ?>">
                                <div class="gw-course-content">
                                    <div class="gw-c-title">
                                        <h6><?php the_title(); ?></h6>
                                    </div>
                                    <div class="gw-c-slots">
                                        <h6><?php echo $remaining_slots; ?></h6>
                                    </div>
                                    <div class="gw-c-action">
                                        <?php if($remaining_slots>0 || $applied_course == $get_slug_name){ ?>
                                            <a href="<?php echo $href; ?>" class="gw-c-action-link gw-c-action-btn" >
                                                <?php echo (!$applied_course) ? 'Apply Now' : (($applied_course == $get_slug_name) ? 'View Status' : 'View Course') ;  ?>
                                            </a>
                                        <?php }else{ ?>
                                            <a class="gw-c-action-link gw-c-action-btn gw-disabled" disabled>No Slots Available</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        wp_reset_query();
        wp_reset_postdata();
    }

    public function breadcrumbs(){
        $current_progress = apply_filters( 'gw_breadcrumbs_progress', null );

        $menu_list = array(
            array("progress"=> 1, "href"=>"", "label"=>"Check Test Result"),
            array("progress"=> 2, "href"=>"", "label"=>"Pre-enlistment Process"),
            array("progress"=> 3, "href"=>"", "label"=>"Fill-up Enrollment Data"),
            array("progress"=> 4, "href"=>"", "label"=>"Enrollment Verification"),
            array("progress"=> 5, "href"=>"", "label"=>"Finished"),
        );

        echo '<ul class="breadcrumb">';
        forEach($menu_list as $menu_item):
            $get_progress_status = 'gw-bc-inactive';

            if( $current_progress == $menu_item['progress']){
                $get_progress_status = 'gw-bc-active';
            }else if( $current_progress > $menu_item['progress']){
                $get_progress_status = 'gw-bc-passed';
            }else if( $current_progress < $menu_item['progress']){
                $get_progress_status = 'gw-bc-inactive';
            }

            echo '<li class="' . $get_progress_status . '">
        <a href="' . $menu_item['href'] . '">
        	<div class="gw-counter-badge gw-progress-' . $menu_item['progress'] . '">' . $menu_item['progress'] . '</div>
                ' . $menu_item['label'] . '
        </a>
        </li>';
        endforeach;
        echo '</ul>';
    }

    public function current_course($atts){
        shortcode_atts( array(
            'field' => ''
        ), $atts );

        $course_data = apply_filters( 'gw_selected_course', null );
        switch($atts['field']){
            case 'title': return $course_data['title'];
            case 'college': return $course_data['college'];
            case 'slots_available':
                return sprintf('%s / %s', ($course_data['slots_available'] - $this->_gw_course_availed_counts($course_data['slug'])), $course_data['slots_available']);
            case 'slots_number_available': return ($course_data['slots_available'] - $this->_gw_course_availed_counts($course_data['slug']));
            case 'slots_number_capacity':
                return $course_data['slots_available'];
            case 'slots_number_availed':
                return $this->_gw_course_availed_counts($course_data['slug']);
            case 'requirement_percentage': return $course_data['requirement_percentage'];
            default:
                return "No field selected";
        }
    }

    public function applied_course($atts){
        shortcode_atts( array(
            'field' => ''
        ), $atts );

        $user_data = apply_filters( 'gw_current_user_login', null );
        return $this->_gw_current_applied_course($atts['field'], $user_data->{'EXAMINEE_NO'});
    }

    public function submitted_info($atts){
        shortcode_atts( array(
            'field' => ''
        ), $atts );

        $user_data = apply_filters( 'gw_current_user_login', null );
        return $this->_gw_submitted_data_query($atts['field'], $user_data->{'EXAMINEE_NO'});
    }

    public function current_user($atts){
        shortcode_atts( array(
            'field' => ''
        ), $atts );

        $user_data = apply_filters( 'gw_current_user_login', null );
        switch($atts['field']){
            case 'exam_number': return $user_data->{'EXAMINEE_NO'};
            case 'score_in_percent': return $user_data->{'PERCENT'};
            case 'full_name': return $user_data->{'FULL_NAME'};
            case 'is_success': return $user_data->{'EXAM_STATUS'};
            default:
                return "No field selected";
        }
    }

    // Filters
    public function breadcrumbs_progress( $int_progress = null ){
        if($int_progress){
            $GLOBALS['gw_breadcrumbs_progress'] = $int_progress;
            return $GLOBALS['gw_breadcrumbs_progress'];
        }
        return $GLOBALS['gw_breadcrumbs_progress'];
    }

    public function selected_course( $course_obj = null ){
        if($course_obj){
            $GLOBALS['gw_selected_course'] = $course_obj;
            return $GLOBALS['gw_selected_course'];
        }

        return $GLOBALS['gw_selected_course'];
    }

    public function current_user_login( $user_obj = null ){
        if($user_obj){
            $GLOBALS['gw_current_user'] = $user_obj;
            return $GLOBALS['gw_current_user'];
        }
        return $GLOBALS['gw_current_user'];
    }

    // Methods

    public function _gw_course_availed_counts($course_slug = ''){
        global $wpdb;
        $results = $wpdb->get_results( "SELECT COUNT(DISTINCT dt.user_value) as reserve_course_count FROM
		(SELECT
			A.entry_id as course_entry_id,
			A.slug as course_slug,
			A.value as course_value,
			B.entry_id as user_entry_id,
			B.slug as user_slug,
			B.value as user_value,
			C.entry_id as action_entry_id,
			C.slug as action_slug,
			C.value as action_value FROM
				{$wpdb->prefix}cf_form_entry_values A,
				{$wpdb->prefix}cf_form_entry_values B,
				{$wpdb->prefix}cf_form_entry_values C
					WHERE
						A.slug='course' AND
						A.entry_id=B.entry_id AND
						A.entry_id=C.entry_id AND
						B.slug='examinee_number' AND
						C.slug='status' AND
						C.value IN ('pending', 'approved') ORDER BY B.value) dt
							WHERE dt.course_value='{$course_slug}'", OBJECT );
        return $results[0]->{'reserve_course_count'};
    }

    public function _gw_applied_course_query($field_name, $field_examinee_number){
        global $wpdb;

        $field_name = sanitize_text_field( $field_name );
        $field_examinee_number = sanitize_text_field( $field_examinee_number );

        $results = $wpdb->get_results( "SELECT
		A.entry_id as course_entry_id,
		A.slug as course_slug,
		A.value as course_value
			FROM buksu_gateway_cf_form_entry_values A,
					 buksu_gateway_cf_form_entry_values B
					 WHERE
							A.entry_id=B.entry_id AND
							B.slug='examinee_number' AND
							B.value='{$field_examinee_number}' and
							A.slug='{$field_name}'", OBJECT );

        if(!empty($results)){
            return $results[0]->{'course_value'};
        }
        return false;
    }

    public function _gw_submitted_data_query($field_name, $field_examinee_number){
        global $wpdb;

        $field_name = sanitize_text_field( $field_name );
        $field_examinee_number = sanitize_text_field( $field_examinee_number );

        $results = $wpdb->get_results( "SELECT
		B.entry_id as user_entry_id,
		B.slug as user_slug,
		B.value as user_value
			FROM buksu_gateway_cf_form_entry_values A,
					 buksu_gateway_cf_form_entry_values B
					 WHERE
							A.entry_id=B.entry_id AND
							A.slug='examinee_number' AND
							A.value='{$field_examinee_number}' and
							B.slug='{$field_name}'", OBJECT );
        return $results[0]->{'user_value'};
    }

    public function _gw_course_meta_by_slug($course_slug, $function_name, $param){
        $args = array(
            'name'  => $course_slug,
            'post_type'   => 'courses',
            'post_status' => 'publish',
            'numberposts' => 1
        );
        $the_query = new WP_Query( $args );
        if( $the_query->have_posts() ){
            while( $the_query->have_posts() ) : $the_query->the_post();
                return call_user_func($function_name, $param);
            endwhile;
        }
    }

    public function _gw_current_applied_course($field_name, $field_examinee_number){
        $course_slug = $this->_gw_applied_course_query('course', $field_examinee_number);
        switch ($field_name) {
            case 'course_slug':
                return $course_slug;
            case 'course_title':
                return $this->_gw_course_meta_by_slug( $course_slug , 'get_the_title', null);
                break;
            case 'college_title':
                return $this->_gw_course_meta_by_slug( $course_slug , 'get_the_category', null)[0]->cat_name;
                break;
            case 'status':
                return $this->_gw_applied_course_query('status', $field_examinee_number);
            default:
                return "No field selected";
                break;
        }
    }

    public function _gw_render_shortcode($string){
        return do_shortcode(stripslashes($string));
    }

    // Verification

    public function validate_course_availability($course_slug=null){
        if(  'GET' === $_SERVER['REQUEST_METHOD']  ){
            if(isset($_GET['course'])){
                $course_slug = sanitize_text_field( $_GET['course'] );
            } else {
                GWUtility::_gw_redirect( 'pass_courses', 'No Courses Found' );
            }
        }
        if($course_slug!=null){
            if(!$this->_gw_load_course($course_slug)){
                GWUtility::_gw_redirect( 'pass_courses', 'No Courses Found' );
            }
        }
    }

    public function validate_submitted_information($examinee_number = null){ // do_action('gw_validate_session') is required to work
        global $wpdb;
        // Get current user
        $user_data = apply_filters('gw_current_user_login', null);

        if(!$examinee_number){
            $examinee_number = $user_data->{'EXAMINEE_NO'};
        }

        // Query Entries
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}cf_form_entry_values WHERE slug = 'examinee_number' AND value ='{$examinee_number}'", OBJECT );

        return count($results) >= 1;
    }

}

new GWShortCodes();