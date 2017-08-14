<?php

namespace AudioDidact;

/**
 * Class to handle email utilities such as mailing for new accounts and password recovery
 */
class EMail {
	public static function sendVerificationEmail(User $user){
		$codes = $user->getEmailVerificationCodes();
		$verificationURL = LOCAL_URL . "user/" . $user->getWebID() . "/?verifyEmail="
			. $codes[count($codes) - 1]["code"];
		$subject = 'Verify your account for AudioDidact';
		$message = "<html><head><title>$subject</title></head><body>"
			. "<p>Before using AudioDidact please verify your email by clicking the link below</p>"
			. "<p><a href=\"$verificationURL\">$verificationURL</a></p>"
			. "</body></html>";
		self::mail($subject, $message, $user);
	}

	public static function sendForgotPasswordEmail(User $user){
		$codes = $user->getPasswordRecoveryCodes();
		$verificationURL = LOCAL_URL . "forgot?username=" . $user->getUsername() . "&recoveryCode="
			. $codes[count($codes) - 1]["code"];
		$subject = 'Reset your AudioDidact Password';
		$message = "<html><head><title>$subject</title></head><body>"
			. "<p>Click the link below to reset your AudioDidact password.</p>"
			. " <p><a href = \"$verificationURL\">$verificationURL</a></p>"
			. "</body></html>";
		self::mail($subject, $message, $user);
	}

	public static function sendPasswordWasResetEmail(User $user){
		$subject = 'Your AudioDidact Password Was Just Changed!';
		$message = "<html><head><title>$subject</title></head><body>"
			. "<p>Security Alert:</p>"
			. "<p>Your password on AudioDidact was just changed. If you did not do this, please contact the"
			. " administrator for help. Otherwise, just ignore this email.</p>"
			. "</body></html>";
		self::mail($subject, $message, $user);
	}

	private static function mail($subject, $message, User $user){
		$to = $user->getEmail();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=utf-8';
		$headers[] = "FROM: " . EMAIL_FROM;
		$headers[] = "REPLY-TO: " . EMAIL_FROM;
		mail($to, $subject, $message, implode("\r\n", $headers));
	}
}
