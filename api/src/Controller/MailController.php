<?php 

namespace App\Controller;

class MailController extends SQLController{

	private $server = 'https://mandrillapp.com/api/1.0';
	private $mandrillKey = "FbUINsewlDpp_WAZV-a04w";

	public function welcome($mail, $pass){

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json";
		$templateFields = array(
			"key" => $this->mandrillKey, 
			"name" => "welcome"
			);

		$result = $this->curlMail($api, $templateFields);

		// récupération contenu Template
		if(!empty($result['publish_code'])) { $html = $result['publish_code']; }elseif(!empty($result['code'])) { $html = $result['code']; } 
		if(!empty($result['publish_text'])) { $text = $result['publish_text']; }elseif(!empty($result['text'])) { $text = $result['text']; }

		// params mail
		$title = "Bienvenue chez Pistache!";
		$from = "contact@pistache-app.com";
		$reply = "thibault@pistache-app.com";
		// prepare
		$mergeTo = array(array(
		                "rcpt" => $mail,
		                "vars" => array(
		                    array(
		                        "name" => "PSWD",
		                        "content" => $pass
		                    )
		                )
		            ));

		$result = $this->sendMessage($title, $html, $text, $from, $reply, $mergeTo);

		// retour
		echo 'Send';


	}

	public function sendMessage($title, $html, $text, $from, $reply, $mergeTo){
		// api Mandrill à envoyer
		$api = '/messages/send.json';
		// traitement un pour l'envoi
		$to = '';
		foreach ($mergeTo as $user) {
			if(!empty($user['rcptName']))
				$to[] = array("type" => "to", "email" => $user['rcpt'], "name" => $user['rcptName']);
			else
				$to[] = array("type" => "to", "email" => $user['rcpt']);
		}
		// Content
		$fields = array(
			"key" => $this->mandrillKey, 
		    "message" => array(
		        "html" => $html,
		        "text" => $text,
		        "subject" => $title,
		        "from_email" => $from,
		        "from_name" => "Pistache",
		        "to" => $to,
		        "headers" => array(
		            "Reply-To" => $reply
		        ),
		        "merge_vars" => $mergeTo,
		        "important" => false,
		        "track_opens" => null,
		        "track_clicks" => null,
		        "auto_text" => null,
		        "auto_html" => null,
		        "inline_css" => null,
		        "url_strip_qs" => null,
		        "preserve_recipients" => null,
		        "view_content_link" => null,
		        // "bcc_address" => "message.bcc_address@example.com", // mail pour la copy caché des mails.
		        "tracking_domain" => null,
		        "signing_domain" => null,
		        "return_path_domain" => null/*,
		        "google_analytics_domains" => array(
		            "example.com"
		        ),
		        "google_analytics_campaign" => "message.from_email@example.com",
		        "metadata" => array(
		            "website" => "www.example.com"
		        )*/
		    ),
		    "async" => false
		);

		echo "<pre>";
		print_r($fields);
		echo "</pre>";

		// envoie
		$result = $this->curlMail($api, $fields);
	}

	public function curlMail($api, $fields){

		$curl = curl_init();
		curl_setopt( $curl,CURLOPT_URL, $this->server.$api );
		curl_setopt( $curl,CURLOPT_POST, true );
		curl_setopt( $curl,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl,CURLOPT_POSTFIELDS, json_encode($fields) );
		$result = json_decode(curl_exec($curl), true);
		curl_close($curl);

		return $result;

	}

}

 ?>