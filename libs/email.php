<?php

/**
 * Class Email
 */
class Email {

	/**
	 * Sends an email.
	 *
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $template template
	 * @param array $vars view vars
	 * @param string $layout layout
	 * @return int number of successful recipients
	 */
	public static function send($to, $subject, $template, $vars = [], $layout = 'default') {

		$view_plain = new View('emails/plain/' . $template, $vars);
		$body_plain = $view_plain->render(null, true);
		$view_html = new View('emails/html/' . $template, $vars);
		$content_html = $view_html->render(null, true);
		$layout_html = new View('layouts/emails/' . $layout, ['subject' => $subject, 'content' => $content_html]);
		$body_html = $layout_html->render(null, true);

		require_once APPDIR . '/vendors/swiftmailer-5.x/lib/swift_required.php';

		$emailConfig = EmailSMTPConfig::get(EmailSMTPConfig::$default);
		$transport = Swift_SmtpTransport::newInstance($emailConfig['smtpHost'], $emailConfig['smtpPort'])
			->setTimeout($emailConfig['smtpTimeout'])
			->setUsername($emailConfig['smtpUsername'])
			->setPassword($emailConfig['smtpPassword']);
		$mailer = Swift_Mailer::newInstance($transport);
		$to = explode(',', $to);
		$message = Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom([$emailConfig['from'] => $emailConfig['fromName']])
			->setTo($to)
			->setBody($body_plain)
			->addPart($body_html, 'text/html');
		$result = $mailer->send($message);

		return $result;

	}

}
