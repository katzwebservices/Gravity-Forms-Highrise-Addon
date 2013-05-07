<?php

class HighriseAPI{

	var $highrise_url ; // your highrise url, e.g. http://yourcompany.highrisehq.com
	var $api_token ; // your highrise api token; can be found under My Info
	var $http_request_timeout = 15; // give'm this amount of seconds to do the right thing (5 is default)

	var $errorMsg = "";
	var $noticeMsg = "";

	var $person ;

	function HighriseAPI() {
		$settings = get_option("gf_highrise_settings");
		$this->highrise_url = untrailingslashit(str_replace('http://', 'https://', $settings["url"]));
		$this->api_token = untrailingslashit($settings['token']);
		$this->person = '';
	}

	function request($url = '', $body = '', $method = 'POST', $headers = array('Content-Type' => 'application/xml')) {

		if(!defined('DOING_AJAX')) {
			define('DOING_AJAX', true);
			define('DONOTCACHEPAGE', true);
			define('DONOTCACHEDB', true);
			define('DONOTMINIFY', true);
			define('DONOTCDN', true);
			define('DONOTCACHCEOBJECT', true);
		}

		$debug = isset($_GET['debug']) && current_user_can('manage_options');
		$path = $this->highrise_url.$url;

		if(!$headers || !is_array($headers)) { $headers = array(); $debug = false; }

		$headers['Authorization'] = 'Basic ' . base64_encode( $this->api_token . ':x' );

		$args = array(
        	'body' => $body,
        	'headers'=> $headers,
        	'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
        	'sslverify' => false,
        	'timeout' => $this->http_request_timeout,
        );

        $response = wp_remote_request($path, $args);

		if($debug) {
	        echo '<pre style="text-align:left;">';
		        print_r(array(
		        	'gf_highrise_settings' => get_option("gf_highrise_settings"),
		        	'$_POST' => $_POST,
		        	'$path' => $path,
		        	'$args' => $args,
		        	'$response' => $response
		        ));
			echo '</pre>';
		}

		if(is_wp_error($response)) {
			$this->errorMsg = $response->get_error_message();
			return false;
		} else if(isset($response['response']['code']) && $response['response']['code'] != 200 && $response['response']['code'] != 404) {
			$this->errorMsg = strip_tags($response['body']);
			return false;
		} else if(!$response) {
			return false;
		}

		return wp_remote_retrieve_body($response);

	}

	function testAccount($request){
		$xml = $this->request('/people/search.xml?term='.urlencode('kbaquihweruahvlwe iqwughfkaj'), '', 'GET', array());

		if(!$xml) {
			$message = $this->errorMsg;
			$this->errorMsg = '';
			return $message;
		}

		$people = @simplexml_load_string($xml);
		if ( $people ) {
			$message = 'Valid Highrise URL and API Token.';
			foreach ($people as $person ) {
				if($person->a == 'redirected'){
					$message = 'Invalid Highrise URL and/or API Token. Please try another combination.';
				}
			}
		} else {
			$message = 'Invalid Highrise URL and/or API Token. Please try another combination.';
		}
		return $message;
	}

	function checkLocation($label, $key = '') {
		if($key == 'sMobile') { return 'Mobile'; }
		if($key == 'sFax') { return 'Fax'; }

		$label = strtolower($label);
		if(strpos($label, 'work') || strpos($label, 'office')) {
			return 'Work';
		} else if(strpos($label, 'home') || strpos($label, 'personal')) {
			if($key == 'sWebsite') { return 'Personal'; }
			return 'Home';
		} elseif(strpos($label, 'cell') || strpos($label, 'mobile')) {
			return 'Mobile';
		} elseif(strpos($label, 'fax')) {
			return 'Fax';
		} elseif(strpos($label, 'pager')) {
			return 'Pager';
		} elseif(strpos($label, 'skype')) {
			return 'Skype';
		} elseif(strpos($label, 'other')) {
			return 'Other';
		}
		return 'Work';
	}

	function pushContact($request){

			$_REQUEST['highriseErrorMessage'] = '';

			$request = $this->_prepareRequest($request);

			$request = apply_filters('gf_highrise_request', $request); // Tap on in.

			//Check that person doesn't already exist
			$id = $this->getPersonId();

			$update = '';

			$body = '
				<person>
					<first-name>'.htmlspecialchars($request['sFirstName']).'</first-name>
					<last-name>'.htmlspecialchars($request['sLastName']).'</last-name>
					<background>'.htmlspecialchars($request['sBackground']).'</background>
					<company-name>'.htmlspecialchars($request['sCompany']).'</company-name>
					<title>'.htmlspecialchars($request['sTitle']).'</title>
					<contact-data>
						<email-addresses>';
							if(is_array($request['sEmail'])) {
							foreach($request['sEmail'] as $label => $email) {
								$body .= '
								<email-address>
									<address>'.htmlspecialchars($email).'</address>
									<location>'.$this->checkLocation($label).'</location>
								</email-address>';
							}
							}
						$body .= '
						</email-addresses>
						<phone-numbers>';
							$phones = array('sPhone' => $request['sPhone'], 'sMobile' => $request['sMobile'], 'sFax' => $request['sFax']);
							foreach($phones as $phoneKey => $phoneType) {
								if(!is_array($phoneType)) { continue; }
								foreach($phoneType as $label => $phone) {
									$body .= '
									<phone-number>
										<number>'.htmlspecialchars($phone).'</number>
										<location>'.$this->checkLocation($label, $phoneKey).'</location>
									</phone-number>';
								}
							}
							$body .= '
						</phone-numbers>
						<addresses>
							<address>
								<city>'.htmlspecialchars($request['sCity']).'</city>
								<country>'.htmlspecialchars($request['sCountry']).'</country>
								<state>'.htmlspecialchars($request['sState']).'</state>
								<street>'.htmlspecialchars($request['sStreet']).'</street>
								<zip>'.htmlspecialchars($request['sZip']).'</zip>
								<location>Work</location>
							</address>
						</addresses>';
						if(is_array($request['sWebsite'])) {
						$body .= '
						<web-addresses>';
								foreach($request['sWebsite'] as $label => $website) {
									if($website == 'http://') { continue; }
									$body .=
									'<web-address>
										<url>'.$website.'</url>
										<location>'.$this->checkLocation($label, 'sWebsite').'</location>
									</web-address>';
								}
						$body .= '
						</web-addresses>';
						}
						if(!empty($request['sTwitter'])) {
						$body .= '
						<twitter-accounts>
							<twitter-account>
								<location>Personal</location>
								<username>'.esc_html($request['sTwitter']).'</username>
								<url>http://twitter.com/'.esc_html($request['sTwitter']).'</url>
							</twitter-account>
						</twitter-accounts>';
						}
					$body .= '
					</contact-data>';

				$body .= $this->customFields($request);

			$body .= '
			</person>';

			if($id === false) {

				$this->errorMsg = "Name not defined.";

			} elseif($id < 0) {

				$response = $this->request('/people.xml', $body);

				// If a new person is created, we need to update the object
				$person = @simplexml_load_string($response);

				if(isset($person->id)) { $this->person = $person; }

			}else{

				$response = $this->request('/people/'.$id.'.xml', $body, 'PUT');

			}

			$_REQUEST['highriseErrorMessage'] = $this->errorMsg;
			return '';
	}

	function customFields($request) {
		$body = '';

		// We remove the normal fields, which leaves only the custom ones
		$defaults = $this->_prepareRequest();
		foreach($defaults as $k => $v) {
			unset($request[$k]);
		}
		// If there are custom fields remaining...
		if(!empty($request)) {
			$custom_fields = $this->request('/subject_fields.xml', '', 'GET', false);

			if(!empty($custom_fields)) {
				$custom = @simplexml_load_string($custom_fields);
				if(isset($custom->{'subject-field'})) {
					$custom = $custom->{'subject-field'};
					$custom_fields = array();
					foreach($custom as $c) {
						$custom_fields[sanitize_user($c->label)] = (int)$c->id;
					}
				}
			}

			$custom_field_body = '';
			foreach($request as $key => $value) {
				if(!empty($value) && (isset($custom_fields[strtolower(esc_html($key))]) || isset($custom_fields[esc_html($key)]))) {
					$custom_field_body .='
					<subject_data>
						<subject_field_id type="integer">'.(int)$custom_fields[sanitize_user($key)].'</subject_field_id>
						<subject_field_label>'.sanitize_user($key).'</subject_field_label>
						<value>'.$value.'</value>
					</subject_data>';
				}
			}
			if(!empty($custom_field_body)) {
				$body .= '
			<!-- custom fields -->
			<subject_datas type="array">
				'.$custom_field_body.'
			</subject_datas>';
			}
		}

		return $body;
	}


	function pushNote($request){

		if($this->person->id < 0 || empty($request['sNotes'])) { return; }

		$bodyPrefix = apply_filters('gf_highrise_note_prefix', __("Request From Website: ", "gravity-forms-highrise"));

		$body = $bodyPrefix.apply_filters('gf_highrise_note', htmlspecialchars($request['sSubject']).' - '.htmlspecialchars($request['sNotes']));

		$body = '
		<note>
			<subject-id type="integer">'.$this->person->id.'</subject-id>
			<subject-type>Party</subject-type>
			<body>'.$bodyPrefix.' '.htmlspecialchars($request['sSubject']).' - '.htmlspecialchars($request['sNotes']).'</body>
		</note>';

		$response = $this->request('/notes.xml', $body);

		return '';
	}


	function pushTags($request){

		if($this->person->id < 0 || empty($request['sTags'])) { return; }

		$body = '';

		foreach ( $request['sTags'] as $tags ) {
			foreach ( $tags as $tag ) {
				$tag = trim(rtrim($tag));
				if(empty($tag)) { continue; }
				$body = '<name>'.$tag.'</name>';
				$this->request('/people/'.$this->person->id.'/tags.xml', $body);
			}
		}

		return '';
	}

	function _prepareRequest($request = array()) {
		$defaults = array(
			'sFirstName'    => '',
			'sLastName'	    => '',
			'sBothName'	    => '',
			'sCompany'	    => '',
			'sTitle'	    => '',
			'sEmail'	    => '',
			'sPhone'	    => '',
			'sMobile'	    => '',
			'sFax'		    => '',
			'sCity'		    => '',
			'sCountry'	    => '',
			'sState'	    => '',
			'sStreet'	    => '',
			'sZip'		    => '',
			'sWebsite'	    => '',
			'sSubject'	    => '',
			'sNotes'	    => '',
			'sTags'		    => '',
			'highrise'	    => '',
			'sBackground' 	=> '',
			'sTwitter'		=> ''
			);
		return wp_parse_args($request, $defaults);
	}

	public function getPersonId() {
		return isset($this->person->id) ? $this->person->id : -1;
	}

	//Search for a person in Highrise
	function getPerson($person){
		if(empty($person['sFirstName']) || empty($person['sLastName'])) { return false; }

		$xml = $this->request('/people/search.xml?term='.urlencode($person['sFirstName'].' '.$person['sLastName']), '', 'GET', array());

		if(!$xml) { return -1; }

		//Parse XML
		$people = @simplexml_load_string($xml);
		$this->person = null;
		$this->person->id = '-1';
		foreach ($people->person as $person ) {
			if($person != null) {
				$this->person = $person;
			}
		}
		return $this->person;

	}

}

?>