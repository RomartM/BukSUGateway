<?php

class GWMailer {
	
	private $headers;
	private $subject;
	private $message;
	private $attachements;
	
	public function __construct(){
    	$this->headers = array(
        	'Content-Type: text/html; charset=UTF-8',
        	'From: BukSU Gateway; no-reply-gateway@buksu.edu.ph'
        );
    }

	public function setRecipients($recipients){
    	$this->recipients = $recipients;
    }

	public function setSubject($subject){
    	$this->subject = $subject;
    }

	public function setBody($message){
    	$this->message = $message;
    }

	public function setAttachments($attachments){
    	$this->attachements = $attachments;
    }

	public function send(){
    	$response = wp_mail(
        	$this->recipients, 
        	$this->subject, 
        	$this->message, 
        	$this->headers,
        	$this->attachements
        );
    
    	// Log every email sent
    	$data_source = new GWDataTable();
    
    	if(is_array($this->recipients)){
        	$scp = implode(", ", $this->recipients);
        }else{
        	$scp = $this->recipients;
        }
    
    	if(is_user_logged_in()){
        	$user_id = get_current_user_id();
        	$procedure_type = "internal";
        }else{
        	$procedure_type = "external";
        	$user_data = apply_filters('gw_session_login_validate', function ($raw) {
                return $raw;
            });

            $user_id = $user_data["uid"]."|".$user_data["utyp"];
        }
    
    	$data_source->insertLog($user_id, 'send_email', json_encode(
            array(
              "status" 		=>  $response,
              "type"		=>	$procedure_type,
              "recipients"	=>	$scp,
              "subject"		=>	$this->subject
            )
        ));
    
    	return $response;
    }

}

class GWMailerService extends GWMailer {

	function get_template($templateName, $variables) {
    	$template = file_get_contents(WP_GW_ROOT . "/templ/email/" . $templateName . ".tmpl.html");

    	foreach($variables as $key => $value)
    	{
        	$template = str_replace('{{ '.$key.' }}', $value, $template);
    	}
    	return $template;
  	}
	
	public function sendRequestStatus($user_data, $content){
    	$this->setRecipients($user_data['EMAIL_ADDRESS']);
		$this->setSubject("Course application request");
    	$variables = array(
        	"subject"	=> "Course Application",
        	"name"		=> sprintf("%s %s", $user_data['FIRST_NAME'], $user_data['LAST_NAME']),
        	"content"	=> $content
        );
    
		$this->setBody(
        	$this->get_template("request", $variables)
        );
    
		return $this->send();
    }

	public function sendAccountCredential($user_data, $content){
    	$this->setRecipients($user_data['uobj']['EMAIL_ADDRESS']);
		$this->setSubject("Welcome to BukSU - Account Credentials");

    	$variables = array(
        	"subject"	=> "Account Credentials",
        	"name"		=> sprintf("%s %s", $user_data['uobj']['FIRST_NAME'], $user_data['uobj']['LAST_NAME']),
        	"content"	=> $content
        );
    
    	$wp_upload_dir = wp_get_upload_dir()['basedir'];
        $cor_file = "{$wp_upload_dir}/user-requirements/{$user_data['utyp']}/{$user_data['uid']}/cor.pdf";
    
    	$this->setAttachments(
        	array($cor_file)
        );

		$this->setBody(
        	$this->get_template("account", $variables)
        );
    
		$this->send();
    }

	public function sendRequestUpdate($user_data, $action){
    	$this->setRecipients($user_data['EMAIL_ADDRESS']);
    
    	$action_label = "";
    	$action_label_2 = "";
    
    	if($action == "approved"){
        	$action_label = "Congratulations! Your application has been approved";
        	$action_label_2 = "Course Application Approved";
        }else if($action == "denied"){
        	$action_label = "Sorry! Your application has been denied";
        	$action_label_2 = "Course Application Denied";
        }else{
        	$action_label = "Evaluation Remarks has been updated";
        	$action_label_2 = "Evaluation Remarks Updated";
        }
    	
		$this->setSubject($action_label);
		
    	$course = (!empty($user_data['REQUESTED_COURSE_ID']))?apply_filters('gw_get_course_meta_id', $user_data['REQUESTED_COURSE_ID'] , 'get_the_title', null): '';
    	$college = (!empty($user_data['REQUESTED_COURSE_ID']))?apply_filters('gw_get_course_meta_id', $user_data['REQUESTED_COURSE_ID'] , 'get_the_category', null)[0]->cat_name: '';
    	$officer = GWUtility::_gw_get_user_display_name($user_data['VALIDATION_OFFICER']);	
    	$status = strtoupper($user_data['VALIDATION_STATUS']);
    
    	$variables = array(
        	"subject"	=> $action_label_2,
        	"name"		=> sprintf("%s %s", $user_data['FIRST_NAME'], $user_data['LAST_NAME']),
        	"content"	=> "
            <h4>Currently Applied<h4>
            <p style=\"font-size: 16px;margin: 2px;font-weight: 400;\">Course: {$course}</p>
            <p style=\"font-size: 16px;margin: 2px;font-weight: 400;\">College: {$college}</p>
            <p style=\"font-size: 16px;margin: 2px;font-weight: 400;\">Reference Number: {$user_data['REQUESTED_TRANSACTION_ID']}</p>
            <h4>Application Status<h4>
            <p style=\"font-size: 16px;margin: 2px;font-weight: 400;\">Status: {$status}</p>
            <p style=\"font-size: 16px;margin: 2px;font-weight: 400;\">Officer: {$officer}</p>
            <p style=\"font-size: 16px;margin: 2px;font-weight: 400;\">Feedback: {$user_data['VALIDATION_FEEDBACK']}</p>
            "
        );

		$this->setBody(
        	$this->get_template("request", $variables)
        );
    
		$this->send();
    }

}

?>