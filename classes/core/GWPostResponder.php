<?php


class GWPostResponder
{
    public function __construct()
    {
        // New Student login
        add_action('admin_post_nopriv_gw_new_login', array( $this, 'new_student_login'));
        add_action('admin_post_gw_new_login', array( $this, 'new_student_login'));

        // New Student login
        add_action('admin_post_nopriv_gw_login_instant', array( $this, 'instant_login'));
        add_action('admin_post_gw_login_instant', array( $this, 'instant_login'));


        // Old student login
        add_action('admin_post_nopriv_gw_old_login', array( $this, 'old_student_login'));
        add_action('admin_post_gw_old_login', array( $this, 'old_student_login'));

        // Upload data
        add_action('admin_post_gw_upload_exam_results', array( $this, 'upload_exam_results'));

        // Upload data
        add_action('admin_post_gw_upload_old_student', array( $this, 'upload_old_student'));

        // Upload data
        add_action('admin_post_gw_upload_admission_info', array( $this, 'upload_admission_info'));

        // Update Settings Semester
        add_action('admin_post_gw_settings_semester', array( $this, 'update_settings_semester'));

        // Update Student Contact Self
        add_action('admin_post_nopriv_gw_student_update_self', array( $this, 'gw_update_student_contact'));
        add_action('admin_post_gw_student_update_self', array( $this, 'gw_update_student_contact'));

        // Update Student Info Self
        add_action('admin_post_nopriv_gw_student_update_info_self', array( $this, 'gw_update_student_info'));
        add_action('admin_post_gw_student_update_info_self', array( $this, 'gw_update_student_info'));

        // Update Student Contact Admin
        add_action('admin_post_gw_student_update', array( $this, 'update_student_contact'));

        // Update Student Request
        add_action('admin_post_gw_request_validation', array( $this, 'request_validation'));

        // Filters
        add_filter('gw_form_meta', array($this, 'form_metadata'), 10, 3);
        add_filter('gw_format_date', array($this, 'format_date'));
        add_filter('gw_get_user', array($this, 'get_user'));

        // // Session Filters
        // add_filter('gw_session_validate', array( $this, 'session_validate' ));
        // add_filter('gw_session_set', array( $this, 'session_set' ), 10, 3);
        // add_filter('gw_session_reset', array( $this, 'session_reset' ));
        //
        // // User Validation
        // add_action('gw_validate_login', array($this, 'validate_login',), 10, 2);
        // add_action('gw_validate_exam_status', array($this, 'validate_exam_status'));
    }

    public function sanitizer($success_callback, $error_callback, $type='login')
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            if (isset($_POST['gw_'. $type .'_nonce'])) {
                $get_referer_url = 'gw_' .  wp_get_referer();
                if (wp_verify_nonce($_POST['gw_'. $type .'_nonce'], remove_query_arg( 'q', $get_referer_url))) {
                    return $success_callback();
                }
                return $error_callback('nonce_invalid');
            }
            return $error_callback('nonce_null');
        }
        return $error_callback('post_null');
    }

    public function new_student_login()
    {
        $this->sanitizer(
            function () {
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                if (isset($_POST['gw_examinee_number']) &&
                    isset($_POST['gw_date_of_exam']) &&
                    isset($_POST['gw_date_of_birth']) &&
                    isset($_POST['gw_time_of_exam'])) {
                    $data_entry = array(
                        "EXAMINEE_NO" => sanitize_text_field($_POST['gw_examinee_number']),
                        "EXAMINATION_DATE" => apply_filters('gw_format_date', sanitize_text_field($_POST['gw_date_of_exam'])),
                        "BIRTHDATE" => apply_filters('gw_format_date', sanitize_text_field($_POST['gw_date_of_birth'])),
                        "EXAMINATION_TIME" => str_replace(' ', '', sanitize_text_field($_POST['gw_time_of_exam']))
                    );

                    $initial_user_data = apply_filters('gw_get_user', $data_entry);

                    if (count($initial_user_data)!=0) {
                        $user_data = array(
                            'ID' => $initial_user_data[0]->{'id'},
                            'FIRST_NAME' => $initial_user_data[0]->{'FIRST_NAME'},
                            'MIDDLE_NAME' => $initial_user_data[0]->{'MIDDLE_NAME'},
                            'LAST_NAME' => $initial_user_data[0]->{'LAST_NAME'},
                            'NAME_SUFFIX' => $initial_user_data[0]->{'NAME_SUFFIX'},
                            'FULL_NAME' => sprintf(
                                "%s %s %s %s",
                                $initial_user_data[0]->{'FIRST_NAME'},
                                $initial_user_data[0]->{'MIDDLE_NAME'},
                                $initial_user_data[0]->{'LAST_NAME'},
                                $initial_user_data[0]->{'NAME_SUFFIX'}
                            ),
                            'SEX' => $initial_user_data[0]->{'SEX'},
                            'EXAM_STATUS' => $initial_user_data[0]->{'EXAM_STATUS'},
                            'DEGREE_LEVEL' => preg_replace('/\b\d+\b/', '', $initial_user_data[0]->{'DEGREE_LEVEL'}),
                            'PERCENT' => str_replace("%", "", $initial_user_data[0]->{'PERCENT'}),
                        	'TOTAL' => str_replace("%", "", $initial_user_data[0]->{'TOTAL'}),
                        );

                        $user_id = $initial_user_data[0]->{'id'};

                        // Set Auth Data
                        do_action('gw_session_login_set_data', $user_id, 'new', $data_entry);
                        do_action('gw_session_login_set_cookie');

                        // Set User Data
                        do_action('gw_session_user_set_data', $user_id, 'new', $user_data);
                        do_action('gw_session_user_set_cookie');

                        GWUtility::_gw_redirect('pass_process', null, '/my/');
                    } else {
                        GWUtility::_gw_redirect($referer_page['page'], 404, "", wp_get_referer()); // User does not exists
                    }
                } else {
                    GWUtility::_gw_redirect($referer_page['page'], 417, "", wp_get_referer()); // All fields are reuired
                }
            },
            function () {
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                GWUtility::_gw_redirect($referer_page['page'], 400, "", wp_get_referer()); // Bad Request
            }
        );
    }

    public function instant_login() // TODO: Integrate Old and new Student Transaction ID
    {
        $this->sanitizer(
            function () {
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                if (isset($_POST['gw_tc_number'])) {
                    $tc_id = sanitize_text_field($_POST['gw_tc_number']);
                    $data_table = new GWDataTable();

                    $tc_type = strtoupper(substr($tc_id, 0, 3));
                    if($tc_type == "TCO"){
                      $data_entry = $data_table->getLoginByTC($tc_id, "old");

                      $record = $data_table->isOldStudentDataExist($data_entry);

                      if ($record[0]->count!=0) {

                        $user_id = $record[0]->id;

                        $initial_user_data = $data_table->getOldStudentData($user_id);

                        $user_data = array(
                            'ID' => $initial_user_data->{'id'},
                            'ID_NUMBER' => $initial_user_data->{'ID_NUMBER'},
                            'FIRST_NAME' => $initial_user_data->{'FIRST_NAME'},
                            'MIDDLE_NAME' => $initial_user_data->{'MIDDLE_NAME'},
                            'LAST_NAME' => $initial_user_data->{'LAST_NAME'},
                            'NAME_SUFFIX' => $initial_user_data->{'NAME_SUFFIX'},
                            'FULL_NAME' => sprintf(
                                "%s %s %s %s",
                                $initial_user_data->{'FIRST_NAME'},
                                $initial_user_data->{'MIDDLE_NAME'},
                                $initial_user_data->{'LAST_NAME'},
                                $initial_user_data->{'NAME_SUFFIX'}
                            ),
                            'SEX' => $initial_user_data->{'SEX'},
                            'DEGREE_LEVEL' => preg_replace('/\b\d+\b/', '', $initial_user_data->{'DEGREE_LEVEL'}),
                        );

                        // Set Auth Data
                        do_action('gw_session_login_set_data', $user_id, 'old', $data_entry);
                        do_action('gw_session_login_set_cookie');

                        // Set User Data
                        do_action('gw_session_user_set_data', $user_id, 'old', $user_data);
                        do_action('gw_session_user_set_cookie');

                        GWUtility::_gw_redirect('pass_process', null, '/my/');

                      } else {
                          GWUtility::_gw_redirect($referer_page['page'], 404, "", wp_get_referer()); // User does not exists
                      }
                    }elseif ($tc_type == "TCN") {
                      $data_entry = $data_table->getLoginByTC($tc_id, "new");
                      $initial_user_data = apply_filters('gw_get_user', $data_entry);

                      if (count($initial_user_data)!=0) {
                          $user_data = array(
                              'ID' => $initial_user_data[0]->{'id'},
                          	  'ID_NUMBER' => $initial_user_data[0]->{'ID_NUMBER'},
                              'FIRST_NAME' => $initial_user_data[0]->{'FIRST_NAME'},
                              'MIDDLE_NAME' => $initial_user_data[0]->{'MIDDLE_NAME'},
                              'LAST_NAME' => $initial_user_data[0]->{'LAST_NAME'},
                              'NAME_SUFFIX' => $initial_user_data[0]->{'NAME_SUFFIX'},
                              'FULL_NAME' => sprintf(
                                  "%s %s %s %s",
                                  $initial_user_data[0]->{'FIRST_NAME'},
                                  $initial_user_data[0]->{'MIDDLE_NAME'},
                                  $initial_user_data[0]->{'LAST_NAME'},
                                  $initial_user_data[0]->{'NAME_SUFFIX'}
                              ),
                              'SEX' => $initial_user_data[0]->{'SEX'},
                              'EXAM_STATUS' => $initial_user_data[0]->{'EXAM_STATUS'},
                              'DEGREE_LEVEL' => preg_replace('/\b\d+\b/', '', $initial_user_data[0]->{'DEGREE_LEVEL'}),
                              'PERCENT' => str_replace("%", "", $initial_user_data[0]->{'PERCENT'}),
                          );

                          $user_id = $initial_user_data[0]->{'id'};

                          // Set Auth Data
                          do_action('gw_session_login_set_data', $user_id, 'new', $data_entry);
                          do_action('gw_session_login_set_cookie');

                          // Set User Data
                          do_action('gw_session_user_set_data', $user_id, 'new', $user_data);
                          do_action('gw_session_user_set_cookie');

                          GWUtility::_gw_redirect('pass_process', null, '/my/');
                      } else {
                          GWUtility::_gw_redirect($referer_page['page'], 404, "", wp_get_referer()); // User does not exists
                      }
                    } else {
                      GWUtility::_gw_redirect($referer_page['page'], 404, "", wp_get_referer()); // User does not exists
                    }
                    } else {
                    GWUtility::_gw_redirect($referer_page['page'], 417, "", wp_get_referer()); // All fields are reuired
                }
            },
            function ($e) {
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                GWUtility::_gw_redirect($referer_page['page'], 400, "", wp_get_referer()); // Bad Request
            },
            'login_instant'
        );
    }

    public function old_student_login()
    {
        $this->sanitizer(
            function () { // Success
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                if (isset($_POST['gw_id_number']) && isset($_POST['gw_last_name'])) {
                  $data_table = new GWDataTable();

                  $data_entry = array(
                    "LAST_NAME"=> sanitize_text_field( $_POST['gw_last_name'] ),
                    "ID_NUMBER"=> sanitize_text_field( $_POST['gw_id_number'] )
                  );

                  $record = $data_table->isOldStudentDataExist($data_entry);

                  if ($record[0]->count!=0) {

                    $user_id = $record[0]->id;

                    $initial_user_data = $data_table->getOldStudentData($user_id);

                    $user_data = array(
                        'ID' => $initial_user_data->{'id'},
                        'ID_NUMBER' => $initial_user_data->{'ID_NUMBER'},
                        'FIRST_NAME' => $initial_user_data->{'FIRST_NAME'},
                        'MIDDLE_NAME' => $initial_user_data->{'MIDDLE_NAME'},
                        'LAST_NAME' => $initial_user_data->{'LAST_NAME'},
                        'NAME_SUFFIX' => $initial_user_data->{'NAME_SUFFIX'},
                        'FULL_NAME' => sprintf(
                            "%s %s %s %s",
                            $initial_user_data->{'FIRST_NAME'},
                            $initial_user_data->{'MIDDLE_NAME'},
                            $initial_user_data->{'LAST_NAME'},
                            $initial_user_data->{'NAME_SUFFIX'}
                        ),
                        'SEX' => $initial_user_data->{'SEX'},
                        'DEGREE_LEVEL' => preg_replace('/\b\d+\b/', '', $initial_user_data->{'DEGREE_LEVEL'}),
                    );

                    // Set Auth Data
                    do_action('gw_session_login_set_data', $user_id, 'old', $data_entry);
                    do_action('gw_session_login_set_cookie');

                    // Set User Data
                    do_action('gw_session_user_set_data', $user_id, 'old', $user_data);
                    do_action('gw_session_user_set_cookie');

                    GWUtility::_gw_redirect('pass_process', null, '/my/');

                  } else {
                      GWUtility::_gw_redirect($referer_page['page'], 404, "", wp_get_referer()); // User does not exists
                  }
                } else {
                  GWUtility::_gw_redirect($referer_page['page'], 417, "", wp_get_referer()); // All fields are reuired
                }
            },
            function ($e) { // Error
                $referer_page = GWUtility::_gw_parse_url(wp_get_referer());
                GWUtility::_gw_redirect($referer_page['page'], 400, "", wp_get_referer()); // Bad Request
            },
            'old_login'
        );
    }

    public function form_metadata($page, $is_admin=false, $secret=null)
    {
        global $wp;

        if ($is_admin) {
            $get_url = admin_url("admin.php?page=".$_GET["page"]);
            if (!empty($secret)) {
                $get_url.="&{$secret}";
            }
        } else {
            $get_url =  add_query_arg(array(
                'page' => $page
            ), home_url($wp->request) . '/');

            if (!empty($secret)) {
                $get_url.="&{$secret}";
            }
        }

        $gw_nonce = wp_create_nonce('gw_' . $get_url);
        $action_url = esc_url(admin_url('admin-post.php'));

        return array($get_url, $gw_nonce, $action_url);
    }

    public function get_user($data_entry)
    {
        $data_source = new GWDataTable();
        return $data_source->getUser($data_entry);
    }

    public function format_date($string_date)
    {
        $date = date_create($string_date);
        return date_format($date, "n/j/Y");
    }

    // No Priv Functions

    public function gw_update_student_contact()
    {
        //gw_student_update
        $this->sanitizer(
            function () { // Success
                if (apply_filters('gw_session_login_validate', null)) {
                    if (
                  isset($_POST['gw_student_update_email']) &&
                  isset($_POST['gw_student_update_phone'])
                ) {
                        $field_obj = apply_filters('gw_session_login_validate', function ($raw) {
                            return $raw;
                        });
                        $field_uid = $field_obj["uid"];
                        $field_utyp = $field_obj["utyp"];
                        $field_email = sanitize_text_field($_POST['gw_student_update_email']);
                        $field_phone = sanitize_text_field($_POST['gw_student_update_phone']);
                        $field_address = sanitize_text_field($_POST['gw_student_update_address']);

                        $entry_manager = new GWEntriesManager($field_uid);

                        $updated_count+= $entry_manager->update_entry_email($field_uid, $field_email, $field_utyp);
                        $updated_count+= $entry_manager->update_entry_phone($field_uid, $field_phone, $field_utyp);
                        $updated_count+= $entry_manager->update_entry_address($field_uid, $field_address, $field_utyp);
                    
						if(!empty($field_email)){
                        	// Resend transaction thru email if does exist
                    		$content = GWUtility::_gw_render_shortcode('<div class="caldera-grid"><div class="alert alert-success">
				        		Your information has been successfully submitted. An enrollment officer will contact you through the phone number you provided.
				            	[elementor-template id="627"]
				            	[elementor-template id="617"]
				            	</div></div>');
                        
                        	$field_obj['uobj']['EMAIL_ADDRESS'] = $field_email;
    
    						// Mailer Service
    						$mailer = new GWMailerService();
    						$mailer->sendRequestStatus($field_obj['uobj'], $content);
                        }

                        $url_components = parse_url(wp_get_referer());
                        parse_str($url_components['query'], $params);
                        $student_update_attr = sprintf("?page=%s", $params['page']);
                        $student_update_url = home_url('my/' . $student_update_attr);
                        update_option('gw_user_updated', $updated_count);
                        wp_redirect($student_update_url);
                    } else {
                        print_r('Please fill in required fields');
                    }
                } else {
                    // Not Allowed
                    print_r('Not Allowed');
                }
            },
            function ($e) {
                print_r($e);
            },
            'student_update_self'
        );
    }

    public function gw_update_student_info()
    {
        //gw_student_update
        $this->sanitizer(
            function () { // Success
                if (apply_filters('gw_session_login_validate', null)) {
                    if (isset($_POST['gw_student_update'])) {
                        $data_array = $_POST['gw_student_update'];
                    }

                    if (
                    isset($_POST['gw_student_typ']) &&
                    isset($data_array['last_name']) &&
                    isset($data_array['first_name']) &&
                    isset($data_array['birthdate']) &&
                    isset($data_array['citizenship']) &&
                    isset($data_array['sex']) &&
                    isset($data_array['civil_status']) &&
                    isset($data_array['email_address']) &&
                    isset($data_array['contact_number']) &&
                    isset($data_array['province']) &&
                    isset($data_array['zip_code']) &&
                    isset($data_array['tcm']) &&
                    isset($data_array['brgy']) &&
                    isset($data_array['street'])
                  ) {
                        $formatted_date = date_format(date_create(sanitize_text_field($data_array['birthdate'])), "n/j/Y");

                        // Student Information
                        $field_entry['LAST_NAME'] = sanitize_text_field($data_array['last_name']);
                        $field_entry['FIRST_NAME'] = sanitize_text_field($data_array['first_name']);
                        $field_entry['MIDDLE_NAME'] = sanitize_text_field($data_array['middle_name']);
                        $field_entry['NAME_SUFFIX'] = sanitize_text_field($data_array['name_suffix']);
                        $field_entry['BIRTHDATE'] = $formatted_date;
                        $field_entry['SEX'] = sanitize_text_field($data_array['sex']);
                        $field_entry['CONTACT_NUMBER'] = sanitize_text_field($data_array['contact_number']);
                        $field_entry['EMAIL_ADDRESS'] = sanitize_text_field($data_array['email_address']);

                        // Admission Data Table
                        $field_entry['CITIZENSHIP'] = sanitize_text_field($data_array['citizenship']);
                        $field_entry['BIRTHDATE'] = $formatted_date;
                        $field_entry['CIVIL_STATUS'] = sanitize_text_field($data_array['civil_status']);
                        $field_entry['PROVINCE'] = sanitize_text_field($data_array['province']);
                        $field_entry['ZIP_CODE'] = sanitize_text_field($data_array['zip_code']);
                        $field_entry['TCM'] = sanitize_text_field($data_array['tcm']);
                        $field_entry['BRGY'] = sanitize_text_field($data_array['brgy']);
                        $field_entry['STREET'] = sanitize_text_field($data_array['street']);
                    	$field_entry['ADDRESS'] = sprintf("%s. %s, %s %s %s", 
                                                           $field_entry['STREET'],
                                                           $field_entry['BRGY'],
                                                           $field_entry['TCM'],
                                                           $field_entry['PROVINCE'],
                                                           $field_entry['ZIP_CODE']
                                                          );

                        $data_table = new GWDataTable();

                        $user_data = apply_filters('gw_session_user_validate', function ($raw) {
                            return $raw;
                        });

                        $user_id = $user_data["uid"];
                        $user_typ = $user_data["utyp"];

                        if($user_typ == "new"){
                          $user_dataset = $data_table->getRelatedID($user_id);

                          if (count($user_dataset) < 1) {
                              $response = array(
                                "status"=>"error",
                                "confirmation"=>false,
                                "messsage"=>"No related information could be retrieve"
                              );
                              $this->json_responder($response);
                          } else {
                              $user_exam_id = $user_dataset[0]['exam_id'];
                              $user_admission_id = $user_dataset[0]['admission_id'];
                          }
                          $updated_count = 0;
                          $updated_count += $data_table->updateExamStudentInformation($user_exam_id, $field_entry);
                          $updated_count += $data_table->updateAdmissionStudentInformation($user_admission_id, $field_entry);

                        }else{
                          $updated_count = 0;
                          $updated_count += $data_table->updateOldStudentInformation($user_id, $field_entry);
                          $updated_count += $data_table->generateTempPassword($user_id, $user_typ);
                        }

                        $data_table->setConfirmation($user_id, true, $user_typ);
                        	
                    	// Resend transaction thru email if does exist
                    	$content = GWUtility::_gw_render_shortcode('<div class="gw-account">[elementor-template id="941"]</div>');
                        
                        $user_data['uobj']['EMAIL_ADDRESS'] = array(
                        	$field_entry['EMAIL_ADDRESS'],
                        	sprintf("%s@student.buksu.edu.ph", $user_data['uobj']['ID_NUMBER'])
                        );
    
    					// Mailer Service
    					$mailer = new GWMailerService();
    					$mailer->sendAccountCredential($user_data, $content);

                        // Validation Log Format
                        $data_table->insertLog($user_id, 'info_confirmation', json_encode(
                            array(
                              "updated_count" =>  $updated_count,
                              "type"=>$user_typ
                            )
                        ));

                        $response = array(
                          "status"=>"success",
                          "confirmation"=>true,
                          "updated_count"=>$updated_count
                        );
                        $this->json_responder($response);

                        } else {
                          $response = array(
                            "status"=>"error",
                            "confirmation"=>false,
                            "messsage"=>"Please fill in required fields"
                        );
                        $this->json_responder($response);
                    }
                } else {
                    $response = array(
                "status"=>"error",
                "confirmation"=>false,
                "messsage"=>"Not allowed"
              );
                    $this->json_responder($response);
                }
            },
            function ($e) {
                $response = array(
            "status"=>"error",
            "confirmation"=>false,
            "messsage"=>"Something went wrong",
            "details"=> $e
          );
                $this->json_responder($response);
            },
            'student_update_info_self'
        );
    }

    public function json_responder($response_array)
    {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($response_array);
        die();
    }

    // Admin Core Functions
    public function upload_exam_results()
    {
        $this->sanitizer(
            function () { // Success
                if (current_user_can('edit_users') || current_user_can('manage_exam')) {
                    // File extension
                    $extension = pathinfo($_FILES['gw-import-file']['name'], PATHINFO_EXTENSION);
                    // If file extension is 'csv'
                    if (!empty($_FILES['gw-import-file']['name']) && $extension == 'csv') {
                        $totalInserted = 0;

                        // Open file in read mode
                        $csvFile = fopen($_FILES['gw-import-file']['tmp_name'], 'r');

                        fgetcsv($csvFile); // Skipping header row

                        $data_query = new GWDataTable();

                        // Read file
                        while (($csvData = fgetcsv($csvFile)) !== false) {
                            $csvData = array_map("utf8_encode", $csvData);

                            // Row column length
                            $dataLen = count($csvData);

                            if (!($dataLen == 14)) {
                                continue;
                            }
						
                            $data_entry['EXAMINEE_NO'] = trim($csvData[0]);
                            $data_entry['EXAMINATION_DATE'] = trim($csvData[1]);
                            $data_entry['EXAMINATION_TIME'] = trim($csvData[2]);
                            $data_entry['EMAIL_ADDRESS'] = trim($csvData[3]);
                            $data_entry['LAST_NAME'] = trim($csvData[4]);
                            $data_entry['FIRST_NAME'] = trim($csvData[5]);
                            $data_entry['MIDDLE_NAME'] = trim($csvData[6]);
                            $data_entry['NAME_SUFFIX'] = trim($csvData[7]);
                            $data_entry['SEX'] = trim($csvData[8]);
                            $data_entry['BIRTHDATE'] = trim($csvData[9]);
                            $data_entry['CONTACT_NUMBER'] = trim($csvData[10]);
                            $data_entry['TOTAL'] = trim($csvData[11]);
                            $data_entry['PERCENT'] = trim($csvData[12]);
                            $data_entry['EXAM_STATUS'] = trim($csvData[13]);

                            $data_entry['DEGREE_LEVEL'] = trim($_POST['gw-degree-type']);

                            // Duplicate Checks Action
                            $record = $data_query->isExamResultDataExist($data_entry);
							
                        	//print_r($record);
                            if ($record[0]->count==0) {
                                if (!empty($data_entry['EXAMINEE_NO']) &&
                                  !empty($data_entry['EXAMINATION_DATE']) &&
                                  !empty($data_entry['EXAMINATION_TIME']) &&
                                  !empty($data_entry['LAST_NAME']) &&
                                  !empty($data_entry['FIRST_NAME']) &&
                                  !empty($data_entry['SEX']) &&
                                  !empty($data_entry['BIRTHDATE']) &&
                                  !empty($data_entry['CONTACT_NUMBER']) &&
                                  !empty($data_entry['TOTAL']) &&
                                  !empty($data_entry['PERCENT']) &&
                                  !empty($data_entry['EXAM_STATUS']) &&
                                  !empty($data_entry['DEGREE_LEVEL'])
                              ) {
                                    $result = $data_query->insertExamResult($data_entry, $data_entry['DEGREE_LEVEL']);
                                
                                    if ($result['id'] > 0) {
                                    	$data_table->generateID($result['id']); // Generate ID
                                        $totalInserted++;
                                    }
                                }
                            }
                        }

                        // Validation Log Format
                        $data_query = new GWDataTable();
                        $data_query->insertLog(get_current_user_id(), 'upload', json_encode(
                            array(
                          "type" =>  "exam",
                          "filename" =>  $_FILES['gw-import-file']['name']
                        )
                        ));

                        echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";
                    } else {
                        print_r('Extension Invalid');
                    }
                } else {
                    // Not Allowed
                    print_r('Not Allowed');
                }
            },
            function ($e) { // Post Error
                print_r($e);
            },
            'upload_exam'
        );
    }

    public function upload_old_student()
    {
        $this->sanitizer(
            function () { // Success
                if (current_user_can('edit_users') || current_user_can('manage_exam')) {
                    // File extension
                    $extension = pathinfo($_FILES['gw-import-file']['name'], PATHINFO_EXTENSION);
                    // If file extension is 'csv'
                    if (!empty($_FILES['gw-import-file']['name']) && $extension == 'csv') {
                        $totalInserted = 0;

                        // Open file in read mode
                        $csvFile = fopen($_FILES['gw-import-file']['tmp_name'], 'r');

                        fgetcsv($csvFile); // Skipping header row

                        $data_query = new GWDataTable();

                        // Read file
                        while (($csvData = fgetcsv($csvFile)) !== false) {
                            $csvData = array_map("utf8_encode", $csvData);

                            // Row column length
                            $dataLen = count($csvData);

                            if (!($dataLen == 11)) {
                                continue;
                            }

                            $data_entry['ID_NUMBER'] = trim($csvData[0]);
                            $data_entry['EMAIL_ADDRESS'] = trim($csvData[1]);
                            $data_entry['LAST_NAME'] = GWUtility::_format_N(trim($csvData[2]));
                            $data_entry['FIRST_NAME'] = GWUtility::_format_N(trim($csvData[3]));
                            $data_entry['MIDDLE_NAME'] = GWUtility::_format_N(trim($csvData[4]));
                            $data_entry['NAME_SUFFIX'] = trim($csvData[5]);
                            $data_entry['SEX'] = trim($csvData[6]);
                            $data_entry['BIRTHDATE'] = trim($csvData[7]);
                            $data_entry['CONTACT_NUMBER'] = trim($csvData[8]);
                            $data_entry['DEGREE_LEVEL'] = trim($csvData[9]);
                            $data_entry['COURSE'] = trim($csvData[10]);


                            // Duplicate Checks Action
                            $record = $data_query->isOldStudentDataExist($data_entry);

                            if ($record[0]->count==0) {
                                if (!empty($data_entry['ID_NUMBER']) &&
                                  !empty($data_entry['LAST_NAME']) &&
                                  !empty($data_entry['DEGREE_LEVEL']) &&
                                  !empty($data_entry['COURSE']) &&
                                  !empty($data_entry['FIRST_NAME'])
                              ) {
                                    $result = $data_query->insertOldStudent($data_entry, $data_entry['DEGREE_LEVEL']);
                                    if ($result['id'] > 0) {
                                        $totalInserted++;
                                    }
                                }
                            }
                        }

                        // Validation Log Format
                        $data_query = new GWDataTable();
                        $data_query->insertLog(get_current_user_id(), 'upload', json_encode(
                            array(
                          "type" =>  "old_studen",
                          "filename" =>  $_FILES['gw-import-file']['name']
                        )
                        ));

                        echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";
                    } else {
                        print_r('Extension Invalid');
                    }
                } else {
                    // Not Allowed
                    print_r('Not Allowed');
                }
            },
            function ($e) { // Post Error
                print_r($e);
            },
            'upload_old_student'
        );
    }

    public function upload_admission_info()
    {
        $this->sanitizer(
            function () { // Success
                if (current_user_can('edit_users') || current_user_can('manage_exam')) {
                    // File extension
                    $extension = pathinfo($_FILES['gw-import-file']['name'], PATHINFO_EXTENSION);
                    // If file extension is 'csv'
                    if (!empty($_FILES['gw-import-file']['name']) && $extension == 'csv') {
                        $totalInserted = 0;

                        // Open file in read mode
                        $csvFile = fopen($_FILES['gw-import-file']['tmp_name'], 'r');

                        fgetcsv($csvFile); // Skipping header row

                        $data_query = new GWDataTable();


                        // Read file
                        while (($csvData = fgetcsv($csvFile)) !== false) {
                            $csvData = array_map("utf8_encode", $csvData);


                            // Row column length
                            $dataLen = count($csvData);

                            if (!($dataLen == 19)) {
                                continue;
                            }

                            $data_entry['EXAMINEE_NO'] = trim($csvData[0]);
                            $data_entry['EXAMINATION_DATE'] = trim($csvData[1]);
                            $data_entry['EXAMINATION_TIME'] = trim($csvData[2]);
                            $data_entry['BIRTHDATE'] = trim($csvData[3]);
                            $data_entry['LRN'] = trim($csvData[4]);
                            $data_entry['CITIZENSHIP'] = trim($csvData[5]);
                            $data_entry['CIVIL_STATUS'] = trim($csvData[6]);
                            $data_entry['IS_IG'] = trim($csvData[7]);
                            $data_entry['PROVINCE'] = trim($csvData[8]);
                            $data_entry['ZIP_CODE'] = trim($csvData[9]);
                            $data_entry['TCM'] = trim($csvData[10]);
                            $data_entry['BRGY'] = trim($csvData[11]);
                            $data_entry['STREET'] = trim($csvData[12]);
                            $data_entry['STATUS'] = trim($csvData[13]);
                            $data_entry['COURSE_PREF'] = trim($csvData[14]);
                            $data_entry['SCHOOL_NAME'] = trim($csvData[15]);
                            $data_entry['SCHOOL_ADDR'] = trim($csvData[16]);
                            $data_entry['SHS_STRAND'] = trim($csvData[17]);
                            $data_entry['SCHOOL_TYPE'] = trim($csvData[18]);

                            // Duplicate Checks Action
                            $record = $data_query->isAdmissionInfoDataExist($data_entry);

                            if ($record[0]->count==0) {
                                if (!empty($data_entry['EXAMINEE_NO']) &&
                                  !empty($data_entry['EXAMINATION_DATE']) &&
                                  !empty($data_entry['EXAMINATION_TIME']) &&
                                  !empty($data_entry['BIRTHDATE'])
                              ) {
                                    $result = $data_query->insertAdmissionInfo($data_entry);

                                    if ($result['id'] > 0) {
                                        $totalInserted++;
                                    }
                                }
                            }
                        }

                        // Validation Log Format
                        $data_query = new GWDataTable();
                        $data_query->insertLog(get_current_user_id(), 'upload', json_encode(
                            array(
                          "type" =>  "admission",
                          "filename" =>  $_FILES['gw-import-file']['name']
                        )
                        ));

                        echo "<h3 style='color: green;'>Total record Inserted : ".$totalInserted."</h3>";
                    } else {
                        print_r('Extension Invalid');
                    }
                } else {
                    // Not Allowed
                    print_r('Not Allowed');
                }
            },
            function ($e) { // Post Error
                print_r($e);
            },
            'upload_admission_info'
        );
    }

    public function update_student_contact()
    {
        //gw_student_update
        $this->sanitizer(
            function () { // Success
                if (current_user_can('edit_users') || current_user_can('manage_exam')) {
                    if (
                  isset($_POST['gw_student_uid']) &&
                  isset($_POST['gw_student_utyp']) &&
                  isset($_POST['gw_student_update_email']) &&
                  isset($_POST['gw_student_update_phone'])
                ) {
                        $field_uid = sanitize_text_field($_POST['gw_student_uid']);
                        $field_email = sanitize_text_field($_POST['gw_student_update_email']);
                        $field_phone = sanitize_text_field($_POST['gw_student_update_phone']);
                        $field_address = sanitize_text_field($_POST['gw_student_update_address']);
                        $user_type = sanitize_text_field($_POST['gw_student_utyp']);

                        $user = wp_get_current_user();
                        $entry_manager = new GWEntriesManager($user->ID);

                        $updated_count+= $entry_manager->update_entry_email($field_uid, $field_email, $user_type);
                        $updated_count+= $entry_manager->update_entry_phone($field_uid, $field_phone, $user_type);
                        $updated_count+= $entry_manager->update_entry_address($field_uid, $field_address, $user_type);

                        $url_components = parse_url(wp_get_referer());
                        parse_str($url_components['query'], $params);
                        $student_update_attr = sprintf("?page=%s&sub=%s&id=%s&updated=%s", $params['page'], $params['sub'], $params['id'], $updated_count);
                        $student_update_url = admin_url("admin.php{$student_update_attr}");

                        wp_redirect($student_update_url);
                    } else {
                        print_r('Please fill in required fields');
                    }
                } else {
                    // Not Allowed
                    print_r('Not Allowed');
                }
            },
            function ($e) {
                print_r($e);
            },
            'student_update'
        );
    }

    public function request_validation()
    {
        //gw_student_update
        $this->sanitizer(
            function () { // Success
                if (current_user_can('edit_users') || current_user_can('manage_exam')) {
                    if (
                  isset($_POST['gw_student_uid']) &&
                  isset($_POST['gw_student_typ']) &&
                  isset($_POST['gw_enrollment_officer_feedback'])
                ) {
                        $field_uid = sanitize_text_field($_POST['gw_student_uid']);
                        $user_type = sanitize_text_field($_POST['gw_student_typ']);
                        $gw_enrollment_officer_feedback = wp_kses_post($_POST['gw_enrollment_officer_feedback']);
                        $submit_action = array_keys($_POST['submit']);
                        $action = sanitize_text_field($submit_action[0]);

                        $user = wp_get_current_user();

                        $entry_manager = new GWEntriesManager($user->ID);

                        $wp_upload_dir = wp_get_upload_dir()['basedir'];
                        $file_directory = "{$wp_upload_dir}/user-requirements/{$user_type}/{$field_uid}";
                        $updated_count = 0;

                        if (!file_exists($file_directory)) {
                            mkdir($file_directory, 0755, true);
                        }

                        if (!empty($_FILES['gw-upload-cor'])) {
                            // Save file

                            $info = pathinfo($_FILES['gw-upload-cor']['name']);
                            if(!empty($info['filename'])){
                              $ext = $info['extension']; // get the extension of the file
                              $newname = "cor.".$ext;
                              move_uploaded_file($_FILES['gw-upload-cor']['tmp_name'], $file_directory .'/'.$newname);
                            }
                        }

                        if ($action == 'denied') { // Delete all requirements
                            $files = glob($file_directory.'/*');
                            // Deleting all the files in the list
                            foreach ($files as $file) {
                                if (is_file($file)) {
                                    // Delete the given file
                                    unlink($file);
                                }
                            }

                            $entry_manager->update_requirements($field_uid, null, $user_type);
                        }

                        $updated_count+= $entry_manager->update_entry_feedback($field_uid, $gw_enrollment_officer_feedback, $user_type);


                        if($action == 'approved' || $action == 'denied'){
                          $updated_count+= $entry_manager->validate_entry($field_uid, strtolower($action), $user_type); // Action Type
                        }
                    	
                    	$data_table = new GWDataTable();
                    	
                    	if($user_type == "old"){
                        	$initial_user_data = $data_table->getOldStudentData($field_uid);
							$student_data = GWUtility::gw_object_to_array($initial_user_data);
                        }else if($user_type == "new"){
							$tc_id = $data_table->getTC($field_uid)['REQUESTED_TRANSACTION_ID'];
                        	$data_entry = $data_table->getLoginByTC($tc_id, "new");
                      		$initial_user_data = apply_filters('gw_get_user', $data_entry);
							$student_data = $initial_user_data[0];
                        }
                    
    					// Mailer Service
    					$mailer = new GWMailerService();
    					$mailer->sendRequestUpdate($student_data, $action);

                        $url_components = parse_url(wp_get_referer());
                        parse_str($url_components['query'], $params);
                        $student_update_attr = sprintf("?page=%s&sub=%s&id=%s", $params['page'], $params['sub'], $params['id']);
                        $student_update_url = admin_url("admin.php{$student_update_attr}");

                        wp_redirect($student_update_url);
                    } else {
                        print_r('Please fill in required fields');
                    }
                } else {
                    // Not Allowed
                    print_r('Not Allowed');
                }
            },
            function ($e) {
                print_r($e);
            },
            'request_validation'
        );
    }

    public function update_settings_semester()
    {
        //gw_semester_update
        $this->sanitizer(
            function () { // Success
                if (current_user_can('edit_users') || current_user_can('manage_exam')) {
                    if (
                  isset($_POST['gw-semester']) &&
                  isset($_POST['gw-semester-year'])
                ) {
                        $field_semester = sanitize_text_field($_POST['gw-semester']);
                        $field_semester_year = sanitize_text_field($_POST['gw-semester-year']);

                        update_option('gw_settings_semester', $field_semester);
                        update_option('gw_settings_semester_year', $field_semester_year);

                        $url_components = parse_url(wp_get_referer());
                        parse_str($url_components['query'], $params);
                        $student_update_attr = sprintf("?page=%s", $params['page']);
                        $student_update_url = admin_url("admin.php{$student_update_attr}");

                        wp_redirect($student_update_url);
                    } else {
                        print_r('Please fill in required fields');
                    }
                } else {
                    print_r('Not Allowed');
                }
            },
            function ($e) {
                print_r($e);
            },
            'settings_semester'
        );
    }
}

new GWPostResponder();
