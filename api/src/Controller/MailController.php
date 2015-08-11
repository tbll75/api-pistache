<?php 

namespace App\Controller;

class MailController{

	public function welcome(){

		$server = 'https://mandrillapp.com/api/1.0';

		$fields = array('key' => "FbUINsewlDpp_WAZV-a04w");

		$fields = array(
			"key" => "FbUINsewlDpp_WAZV-a04w",
		    "message" => array(
		        "html" => "<p>Example HTML content</p>",
		        "text" => "Example text content",
		        "subject" => "example subject",
		        "from_email" => "message.from_email@example.com",
		        "from_name" => "Example Name",
		        "to" => array(
		            array(
		                "email" => "recipient.email@example.com",
		                "name" => "Recipient Name",
		                "type" => "to"
		            ),
		            array(
		                "email" => "recipient.email@example.com",
		                "name" => "Recipient Name",
		                "type" => "to"
		            )
		        ),
		        "headers" => array(
		            "Reply-To" => "message.reply@example.com"
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
		        "bcc_address" => "message.bcc_address@example.com",
		        "tracking_domain" => null,
		        "signing_domain" => null,
		        "return_path_domain" => null,
		        "merge" => true,
		        "merge_language" => "mailchimp",
		        "global_merge_vars" => array(
		            array(
		                "name" => "merge1",
		                "content" => "merge1 content"
		            )
		        ),
		        "merge_vars" => array(
		            array(
		                "rcpt" => "recipient.email@example.com",
		                "vars" => array(
		                    array(
		                        "name" => "merge2",
		                        "content" => "merge2 content"
		                    )
		                )
		            )
		        ),
		        "tags" => array(
		            "password-resets"
		        ),
		        "subaccount" => "customer-123",
		        "google_analytics_domains" => array(
		            "example.com"
		        ),
		        "google_analytics_campaign" => "message.from_email@example.com",
		        "metadata" => array(
		            "website" => "www.example.com"
		        ),
		        "recipient_metadata" => array(
		            array(
		                "rcpt" => "recipient.email@example.com",
		                "values" => array(
		                    "user_id" => 123456
		                )
		            )
		        ),
		        "attachments" => array(
		            array(
		                "type" => "text/plain",
		                "name" => "myfile.txt",
		                "content" => "ZXhhbXBsZSBmaWxl"
		            )
		        ),
		        "images" => array(
		            array(
		                "type" => "image/png",
		                "name" => "IMAGECID",
		                "content" => "ZXhhbXBsZSBmaWxl"
		            )
		        )
		    ),
		    "async" => false,
		    "ip_pool" => "Main Pool",
		    "send_at" => "example send_at"
		)

		$mail = curl_init();
		curl_setopt( $mail,CURLOPT_URL, $server.'/messages/send.json' );
		curl_setopt( $mail,CURLOPT_POST, true );
		curl_setopt( $mail,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $mail,CURLOPT_POSTFIELDS, json_encode($fields) );
		$result = curl_exec($mail);
		curl_close($mail);

		echo '<pre>';
		print_r(json_decode($result), true);
		echo '</pre>';


	}

/*	public function welcome(){

		$mgClient = 'key-e71c0195d23e0930cae6e89f7078f360';
		$domain = "sandboxffbbdca0be4c417d8af478152afb81d8.mailgun.org";
		$from = 'postmaster@'.$domain;

		$to = 'Orazio Locchi <orazio.locchi@gmail.com>';
		$subject = 'Hello Orazio Locchi';
		$message = 'Congratulations Orazio Locchi, you just sent an email with Mailgun!  You are truly awesome!  You can see a record of this email in your logs: https://mailgun.com/cp/log .  You can send up to 300 emails/day from this sandbox server.  Next, you should add your own domain so you can send 10,000 emails/month for free.';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:'.$mgClient);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$plain = preg_replace("/\<br\s*\/?\>/i", "\n", $message);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/'.$domain.'/messages');
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => $from,
			'to' => $to,
			'subject' => $subject,
			'html' => $message,
			'text' => $plain));

		$j = json_decode(curl_exec($ch));

		$info = curl_getinfo($ch);

		if($info['http_code'] != 200)
		error("Fel 313: Vänligen meddela detta via E-post till ".$from);

		curl_close($ch);

		print_r($j);
	}
*/
}

 ?>