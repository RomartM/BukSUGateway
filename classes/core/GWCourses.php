<?php

/**
 * GW Courses Class
 */
class GWCoursesClass
{

  function __construct()
  {
      add_filter('gw_course_get_by_args', array( $this, 'getCourses'), 10, 2);
      add_filter('gw_course_get_by_shortname', array( $this, 'getCourseByShortName'), 10, 2);
      add_filter('gw_course_get_by_ID', array( $this, 'getCourseByID'), 10, 2);
      add_filter('gw_course_get_by_slug', array( $this, 'getCourseBySlug'), 10, 2);
  }

  public function getCourses($query_args=array(
    'post_type'   => 'courses',
    'post_status' => 'publish'
  ), $success_callback=null)
  {
    $query_course = new WP_Query($query_args);
    if ($query_course->have_posts()) {
        while ($query_course->have_posts()) : $query_course->the_post();
        $cat = get_the_category();
        $course_obj = array(
                'id'  => get_the_id(),
                'title' => get_the_title(),
                'slug' => get_post_field('post_name', get_the_id()),
                'college' => $cat[0]->cat_name,
                'slots_available' => get_field('slots_available'),
                'requirement_percentage' => get_field('requirement_percentage')
            );
        if(!empty($success_callback)){
          return $success_callback($course_obj);
        }
        return true;
        endwhile;
    } else {
        return false;
    }
  }

  public function getCourseByShortName($short_name, $success_callback=null)
  {
    $query_args = array(
        'post_type'   => 'courses',
        'post_status' => 'publish',
        'meta_key'    => 'short_name',
        'meta_value'  => $short_name,
        'numberposts' => 1
    );
    return $this->getCourses($query_args, $success_callback);
  }

  public function getCourseByID($course_id, $success_callback=null)
  {
    $query_args = array(
        'p'            => $course_id,
        'post_type'   => 'courses',
        'post_status' => 'publish',
        'numberposts' => 1
    );
    return $this->getCourses($query_args, $success_callback);
  }

  public function getCourseBySlug($slug, $success_callback=null)
  {
    $query_args = array(
        'name'        => $slug,
        'post_type'   => 'courses',
        'post_status' => 'publish',
        'numberposts' => 1
    );
    return $this->getCourses($query_args, $success_callback);
  }
}

new GWCoursesClass();


 ?>
