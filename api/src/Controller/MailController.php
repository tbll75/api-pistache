<?php 

namespace App\Controller;

class MailController{

	private $server = 'https://mandrillapp.com/api/1.0';
	private $mandrillKey = "FbUINsewlDpp_WAZV-a04w";

	public function welcome(){

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json";
		$templateFields = array(
			"key" => $mandrillKey, 
			"name" => "welcome"
			);

		$resutl = curlMail($api, $templateFields);

		// traitement
		if(!empty($tmpResult['publish_code'])) {
			$html = $tmpResult['publish_code'];
		}elseif(!empty($tmpResult['code'])) {
			$html = $tmpResult['code'];
		}
		if(!empty($tmpResult['publish_text'])) {
			$text = $tmpResult['publish_text'];
		}elseif(!empty($tmpResult['text'])) {
			$text = $tmpResult['text'];
		}

		// prepare
		$api = '/messages/send.json';
		$fields = array(
			"key" => $mandrillKey,
			"template_name" => "welcome",
		    "template_content" => array(
		        array(
		            "name" => "welcome",
		            "content" => "example content"
		        )
		    ),
		    "message" => array(
		        "html" => $html,
		        "text" => $text,
		        "subject" => "Bienvenue chez Pistache!",
		        "from_email" => "contact@pistache-app.com",
		        "from_name" => "Pistache",
		        "to" => array(
		            array(
		                "email" => "tbll75@gmail.com",
		                // "name" => "Orazio Locchi",
		                "type" => "to"
		            ),
		            array(
		                "email" => "orazio.locchi@live.fr",
		                // "name" => "Orazio Locchi",
		                "type" => "to"
		            )
		        ),
		        "headers" => array(
		            "Reply-To" => "tbll75@gmail.com"
		        ),
		         "merge_vars" => array(
		            array(
		                "rcpt" => "tbll75@gmail.com",
		                "vars" => array(
		                    array(
		                        "name" => "PSWD",
		                        "content" => "easy le paswd"
		                    )
		                )
		            ),
		            array(
		                "rcpt" => "orazio.locchi@live.fr",
		                "vars" => array(
		                    array(
		                        "name" => "PSWD",
		                        "content" => "tmtc"
		                    )
		                )
		            )
		        ),
		        "important" => false,
		        "track_opens" => null,
		        "track_clicks" => null,
		        "auto_text" => null,
		        "auto_html" => null,
		        "inline_css" => null,
		        "url_strip_qs" => null,
		        "preserve_recipients" => null,
		        "view_content_link" => null,
		        // "bcc_address" => "message.bcc_address@example.com",
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

		// envoie
		$result = curlMail($api, $fields);

		// retour
		echo 'Send';


	}

	public function curlMail($api, $fields){

		$curl = curl_init();
		curl_setopt( $curl,CURLOPT_URL, $server.$api );
		curl_setopt( $curl,CURLOPT_POST, true );
		curl_setopt( $curl,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl,CURLOPT_POSTFIELDS, json_encode($fields) );
		$result = json_decode(curl_exec($curl), true);
		curl_close($curl);

		return $result;

	}

}

 ?>