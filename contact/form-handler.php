<?php
include('SMTPClass.php');

$use_smtp = '0';
$emailto = 'web.blooom.ua@gmail.com';

	// retrieve from parameters
	$emailfrom = isset($_POST["email"]) ? $_POST["email"] : "";
	$nocomment = isset($_POST["nocomment"]) ? $_POST["nocomment"] : "";
	$subject = 'Почта из вашего сайта';
	$message = 'сообщение:';
	$response = '';
	$response_fail = 'Произошла ошибка при проверке ваших данных.';
	
		// Honeypot captcha
		if($nocomment == '') {
		
			$params = $_POST;
			foreach ( $params as $key=>$value ){
			
				if(!($key == 'ip' || $key == 'emailsubject' || $key == 'url' || $key == 'emailto' || $key == 'nocomment' || $key == 'v_error' || $key == 'v_email')){
				
					$key = ucwords(str_replace("-", " ", $key));
					
					if ( gettype( $value ) == "array" ){
						$message .= "$key: \n";
						foreach ( $value as $two_dim_value )
						$message .= "...$two_dim_value<br>";
					}else {
						$message .= $value != '' ? "$key: $value\n" : '';
					}
				}
			}
			
		$response = sendEmail($subject, $message, $emailto, $emailfrom);
			
		} else {
		
			$response = $response_fail;
		
		}

	echo $response;

// Run server-side validation
function sendEmail($subject, $content, $emailto, $emailfrom) {
	
	$from = $emailfrom;
	$response_sent = 'Спасибо,ваше сообщение отправлено.';
	$response_error = 'Ошибка.Повторите еще раз';
	$subject =  filter($subject);
	$url = "Origin Page: ".$_SERVER['HTTP_REFERER'];
	$ip = "IP Address: ".$_SERVER["REMOTE_ADDR"];
	$message = $content."\n$ip\r\n$url";
	
	// Validate return email & inform admin
	$emailto = filter($emailto);

	// Setup final message
	$body = wordwrap($message);
	
	if($use_smtp == '1'){
	
		$SmtpServer = 'SMTP SERVER';
		$SmtpPort = 'SMTP PORT';
		$SmtpUser = 'SMTP USER';
		$SmtpPass = 'SMTP PASSWORD';
		
		$to = $emailto;
		$SMTPMail = new SMTPClient ($SmtpServer, $SmtpPort, $SmtpUser, $SmtpPass, $from, $to, $subject, $body);
		$SMTPChat = $SMTPMail->SendMail();
		$response = $SMTPChat ? $response_sent : $response_error;
		
	} else {
		
		// Create header
		$headers = "From: $from\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/plain; charset=utf-8\r\n";
		$headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
		
		// Send email
		$mail_sent = @mail($emailto, $subject, $body, $headers);
		$response = $mail_sent ? $response_sent : $response_error;
		
	}
	return $response;
}

// Remove any un-safe values to prevent email injection
function filter($value) {
	$pattern = array("/\n/", "/\r/", "/content-type:/i", "/to:/i", "/from:/i", "/cc:/i");
	$value = preg_replace($pattern, "", $value);
	return $value;
}

exit;

?>