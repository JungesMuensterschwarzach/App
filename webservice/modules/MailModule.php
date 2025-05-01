<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	use PHPMailer\PHPMailer\SMTP;

	if (ROOT_LOCAL === null) {
		require_once("../assets/global_requirements.php");
	}
	require_once(ROOT_LOCAL."/modules/UserModule.php");
	require_once(ROOT_LOCAL."/modules/EventModule.php");
	require_once(ROOT_LOCAL."/libs/PHPMailer/PHPMailer.php");
	require_once(ROOT_LOCAL."/libs/PHPMailer/Exception.php");
	require_once(ROOT_LOCAL."/libs/PHPMailer/SMTP.php");

	class MailModule {

		// MIGRATED
		public static function sendSignUpRequestMail($eMailAddress, $displayName, $code, $withEnrollment) {
			$url = self::getAccountTokenUrl($code);
			$title = $GLOBALS["dict"]["mail_request_activation_title"];
			$message = $GLOBALS["dict"]["mail_message_salutation_prefix"] 
				. htmlspecialchars($displayName) 
				. $GLOBALS["dict"]["mail_message_salutation_suffix"]
				. $GLOBALS["dict"]["mail_request_activation_message_paragraph_1"]
				. "<a href=\"$url\">$url</a>";
			if ($withEnrollment) {
				$message .= $GLOBALS["dict"]["mail_request_activation_message_paragraph_enrollment"];
			}
			$message .= $GLOBALS["dict"]["mail_request_activation_message_paragraph_2"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		// MIGRATED
		public static function sendNewsletterRequestMail($eMailAddress, $code) {
			$url = self::getNewsletterTokenUrl($code);
			$title = $GLOBALS["dict"]["mail_request_newsletter_title"];
			$message = $GLOBALS["dict"]["mail_request_newsletter_message_paragraph_1"]
				. "<a href=\"$url\">$url</a>"
				. $GLOBALS["dict"]["mail_request_newsletter_message_paragraph_2"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		// MIGRATED
		public static function sendNewsletterMail($eMailAddress, $title, $content, $code, $attachments, $replyToUser) {
			$cancelLink = 
				$code !== null ?
					self::getNewsletterTokenUrl($code) :
					self::getProfileUrl();
			$cancelMsg =
				$code !== null ?
					$GLOBALS["dict"]["account_allowNewsletter_registration_cancel_guest_prefix"]
					. "<a href=\"$cancelLink\">".$GLOBALS["dict"]["account_allowNewsletter_registration_cancel_guest_infix"]."</a>"
					. $GLOBALS["dict"]["account_allowNewsletter_registration_cancel_guest_suffix"]
				:
					$GLOBALS["dict"]["account_allowNewsletter_registration_cancel_account_prefix"]
					. "<a href=\"$cancelLink\">".$GLOBALS["dict"]["navigation_profile"]."</a>"
					. $GLOBALS["dict"]["account_allowNewsletter_registration_cancel_account_suffix"]
				;
			$message = $content
				. "<hr/><small>"
				. $cancelMsg
				. "</small>";

			self::sendMail($eMailAddress, $title, $message, $attachments, $replyToUser);
		}

		// MIGRATED
		public static function sendEventEnrollmentRequestMail(
				$eMailAddress, $displayName, $code, $eventTitle) {
			$url = self::getAccountTokenUrl($code);
			$title = $GLOBALS["dict"]["mail_request_event_enrollment_title"];
			$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
				. htmlspecialchars($displayName)
				. $GLOBALS["dict"]["mail_message_salutation_suffix"]
				. $GLOBALS["dict"]["mail_request_event_enrollment_message_paragraph_1"]
				. "<strong>$eventTitle</strong>"
				. $GLOBALS["dict"]["mail_request_event_enrollment_message_paragraph_2"]
				. "<a href=\"$url\">$url</a>"
				. $GLOBALS["dict"]["mail_request_event_enrollment_message_paragraph_3"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		public static function sendPasswordResetRequestMail($eMailAddress, $displayName, $code) {
			$url = self::getAccountTokenUrl($code);
			$title = $GLOBALS["dict"]["mail_request_password_reset_title"];
			$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
				. htmlspecialchars($displayName)
				. $GLOBALS["dict"]["mail_message_salutation_suffix"]
				. $GLOBALS["dict"]["mail_request_password_reset_message_paragraph_1"]
				. "<a href=\"$url\">$url</a>"
				. $GLOBALS["dict"]["mail_request_password_reset_message_paragraph_2"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		public static function sendOldEMailUpdateRequestMail($eMailAddress, $newEMailAddress, $displayName, $code) {
			$url = self::getAccountTokenUrl($code);
			$title = $GLOBALS["dict"]["mail_request_eMailAddress_update_title"];
			$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
				. htmlspecialchars($displayName)
				. $GLOBALS["dict"]["mail_message_salutation_suffix"]
				. $GLOBALS["dict"]["mail_request_eMailAddress_update_old_message_paragraph_1"]
				. "<strong>$newEMailAddress</strong>"
				. $GLOBALS["dict"]["mail_request_eMailAddress_update_old_message_paragraph_2"]
				. "<a href=\"$url\">$url</a>"
				. $GLOBALS["dict"]["mail_request_eMailAddress_update_old_message_paragraph_3"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		public static function sendNewEMailUpdateRequestMail($eMailAddress, $displayName, $code) {
			$url = self::getAccountTokenUrl($code);
			$title = $GLOBALS["dict"]["mail_request_eMailAddress_update_title"];
			$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
				. htmlspecialchars($displayName)
				. $GLOBALS["dict"]["mail_message_salutation_suffix"]
				. $GLOBALS["dict"]["mail_request_eMailAddress_update_new_message_paragraph_1"]
				. "<a href=\"$url\">$url</a>"
				. $GLOBALS["dict"]["mail_request_eMailAddress_update_new_message_paragraph_2"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		public static function sendAccountDeletionRequestMail($eMailAddress, $displayName, $code) {
			$url = self::getAccountTokenUrl($code);

			$title = $GLOBALS["dict"]["mail_request_account_deletion_title"];
			$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
				. htmlspecialchars($displayName)
				. $GLOBALS["dict"]["mail_message_salutation_suffix"]
				. $GLOBALS["dict"]["mail_request_account_deletion_message_paragraph_1"]
				. "<a href=\"$url\">$url</a>"
				. $GLOBALS["dict"]["mail_request_account_deletion_message_paragraph_2"]
				. self::get_signature();
			self::sendMail($eMailAddress, $title, $message);
		}

		public static function sendEventEnrollmentNotificationFailureMail($error, $trace) {
			try {
				$userList = UserModule::loadUserList(ACCESS_LEVEL_DEVELOPER);
				foreach ($userList as $user) {
					if (!PERMISSIONS[intval($user["accessLevel"])][PERMISSION_DEV_PROCESSING_ERRORS]) {
						continue;
					}

					try {
						$title = $GLOBALS["dict"]["mail_event_enrollment_notification_failure_title"];
						$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
							. htmlspecialchars($user["displayName"])
							. $GLOBALS["dict"]["mail_message_salutation_suffix"]
							. $GLOBALS["dict"]["mail_event_enrollment_notification_failure_message_paragraph"]
							. $GLOBALS["dict"]["label_failure"] . " " . $error . "<br/><br/>"
							. json_encode($trace) . "<br/><br/>"
							. self::get_signature();
						self::sendMail($user["eMailAddress"], $title, $message);
					} catch (Exception $exc) {
						error_log($exc->getMessage());
					}
				}
			} catch (Exception $exc) {
				error_log($exc->getMessage());
			}
		}

		public static function sendEventEnrollmentNotificationMail($eventId, $userId) {
			try {
				$enrolledUser = UserModule::loadUser($userId, ACCESS_LEVEL_DEVELOPER);
				$event = EventModule::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER);
				$eventEnrollment = EventModule::loadEventEnrollment($eventId, $userId, ACCESS_LEVEL_DEVELOPER);

				try {
					$title = $GLOBALS["dict"]["mail_event_enrollment_notification_self_title"];
					$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
						. htmlspecialchars($enrolledUser["displayName"])
						. $GLOBALS["dict"]["mail_message_salutation_suffix"]
						. $GLOBALS["dict"]["mail_event_enrollment_notification_self_message_paragraph_1"]
						. "<strong>" . $event["eventTitle"] . "</strong>"
						. $GLOBALS["dict"]["mail_event_enrollment_notification_self_message_paragraph_2"]
						. self::get_signature();
					self::sendMail($enrolledUser["eMailAddress"], $title, $message);
				} catch (Exception $exc) {
					error_log($exc->getMessage());
				}

				$userList = UserModule::loadUserList(ACCESS_LEVEL_DEVELOPER);
				foreach ($userList as $user) {
					if (!PERMISSIONS[intval($user["accessLevel"])][PERMISSION_MAIL_EVENT_ENROLLMENT_RECEIVE]
						|| $enrolledUser["userId"] === $user["userId"]) {
						continue;
					}

					try {
						$title = $GLOBALS["dict"]["mail_event_enrollment_notification_other_title"];
						$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
							. htmlspecialchars($user["displayName"])
							. $GLOBALS["dict"]["mail_message_salutation_suffix"]
							. "<strong>" . $enrolledUser["firstName"] . " " . $enrolledUser["lastName"] . "</strong>"
							. $GLOBALS["dict"]["mail_event_enrollment_notification_other_message_paragraph_1"]
							. "<strong>" . $event["eventTitle"] . "</strong>"
							. $GLOBALS["dict"]["mail_event_enrollment_notification_other_message_paragraph_2"]
							. $GLOBALS["dict"]["mail_event_enrollment_notification_other_message_paragraph_3"]
							. "<a href=\"" . self::getEventParticipantsUrl($eventId) . "\">" . $GLOBALS["dict"]["event_participants_list"] . "</a>"
							. $GLOBALS["dict"]["mail_event_enrollment_notification_other_message_paragraph_4"];

						if (!empty($eventEnrollment['eventEnrollmentComment'])) {
							$message .= '<strong>' . $GLOBALS["dict"]["event_eventEnrollmentComment"] . '</strong>:<br/>'
								. nl2br(htmlspecialchars($eventEnrollment['eventEnrollmentComment'])) . '<br/><br/>';
						}

						$message .= self::get_signature();
						self::sendMail($user["eMailAddress"], $title, $message);
					} catch (Exception $exc) {
						error_log($exc->getMessage());
					}
				}
			} catch(Exception $exc) {
				error_log($exc->getMessage());
			}
		}

		public static function sendEventDisenrollmentNotificationMail($eventId, $userId) {
			try {
				$disenrolledUser = UserModule::loadUser($userId, ACCESS_LEVEL_DEVELOPER);
				$event = EventModule::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER);

				try {
					$title = $GLOBALS["dict"]["mail_event_disenrollment_notification_self_title"];
					$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
						. htmlspecialchars($disenrolledUser["displayName"])
						. $GLOBALS["dict"]["mail_message_salutation_suffix"]
						. $GLOBALS["dict"]["mail_event_disenrollment_notification_self_message_paragraph_1"]
						. "<strong>" . $event["eventTitle"] . "</strong>"
						. $GLOBALS["dict"]["mail_event_disenrollment_notification_self_message_paragraph_2"]
						. self::get_signature();
					self::sendMail($disenrolledUser["eMailAddress"], $title, $message);
				} catch (Exception $exc) {
					error_log($exc->getMessage());
				}

				$userList = UserModule::loadUserList(ACCESS_LEVEL_DEVELOPER);
				foreach ($userList as $user) {
					if (!PERMISSIONS[intval($user["accessLevel"])][PERMISSION_MAIL_EVENT_ENROLLMENT_RECEIVE]
						|| $disenrolledUser["userId"] === $user["userId"]) {
						continue;
					}

					try {
						$title = $GLOBALS["dict"]["mail_event_disenrollment_notification_other_title"];
						$message = $GLOBALS["dict"]["mail_message_salutation_prefix"]
							. htmlspecialchars($user["displayName"])
							. $GLOBALS["dict"]["mail_message_salutation_suffix"]
							. "<strong>" . htmlspecialchars($disenrolledUser["firstName"]) . " " . htmlspecialchars($disenrolledUser["lastName"]) . "</strong>"
							. $GLOBALS["dict"]["mail_event_disenrollment_notification_other_message_paragraph_1"]
							. "<strong>" . htmlspecialchars($event["eventTitle"]) . "</strong>"
							. $GLOBALS["dict"]["mail_event_disenrollment_notification_other_message_paragraph_2"]
							. self::get_signature();
						self::sendMail($user["eMailAddress"], $title, $message);
					} catch (Exception $exc) {
						error_log($exc->getMessage());
					}
				}
			} catch(Exception $exc) {
				error_log($exc->getMessage());
			}
		}


		// MIGRATED
		public static function sendMail($recipientAddress, $title, $message, $attachments = [], $replyToUser = null) {
			self::validateAttachments($attachments);
			
			if (MAIL_ACTIVE === false) {
				return;
			}
			
			try {
				$mailer = new PHPMailer(true);
				$mailer->IsHTML(true);
				$mailer->CharSet = MAIL_ENCODING;
				$mailer->isSMTP();
				$mailer->Host = MAIL_SERVER;
				$mailer->SMTPAuth = true;
				$mailer->Username = MAIL_ACCOUNT_NAME;
				$mailer->Password = MAIL_PASSWORD;
				$mailer->Port = MAIL_PORT_SMTP;
				if (MAIL_PORT_SMTP === 587) {
					$mailer->SMTPSecure = "tls";
				}

				$mailer->setFrom(MAIL_ADDRESS, MAIL_AUTHOR);
				if ($replyToUser) {
					$mailer->addReplyTo(
						$replyToUser["eMailAddress"],
						isset($replyToUser["firstName"]) && isset($replyToUser["lastName"]) ?
							$replyToUser["firstName"] . " " . $replyToUser["lastName"] :
							$replyToUser["displayName"]
					);
				} else {
					$mailer->addReplyTo(MAIL_ADDRESS, MAIL_AUTHOR);
				}
				$mailer->addAddress($recipientAddress);

				$mailer->Subject = $title;
				$mailer->Body = $message;
				$mailer->AltBody = $message;
				foreach ($attachments as $attachment) {
					$mailer->AddAttachment(
						$attachment["tmp_name"], $attachment["name"]
					);
				}

				$mailer->send();
			} catch (Exception $exc) {
				error_log($exc->getMessage());
				throw new Exception("mail_send_fail");
			} finally {
				unset($mailer);
			}
		}

		// MIGRATED
		private static function getAccountTokenUrl($code) {
			return 
				GlobalFunctions::getRequestProtocol() . "://" 
				. APP_BASEURLS_APP . MAIL_ACCOUNT_TOKEN_URL 
				. rawurlencode($code);
		}

		// MIGRATED
		private static function getNewsletterTokenUrl($code) {
			return
				GlobalFunctions::getRequestProtocol() . "://"
				. APP_BASEURLS_APP . MAIL_NEWSLETTER_TOKEN_URL
				. rawurlencode($code);
		}

		// MIGRATED
		private static function getProfileUrl() {
			return
				GlobalFunctions::getRequestProtocol() . "://"
				. APP_BASEURLS_APP . MAIL_PROFILE_URL;
		}

		// MIGRATED
		private static function getEventParticipantsUrl($eventId) {
			return 
				GlobalFunctions::getRequestProtocol() . "://" 
				. APP_BASEURLS_APP . MAIL_EVENT_PARTICIPANTS_URL
				. $eventId;
		}

		// NOT MIGRATED (SKIPPED)
		private static function validateAttachments($attachments) {
			foreach ($attachments as $attachment) {
				if ($attachment["size"] > MAIL_ATTACHMENT_SIZE_MAX) {
					throw new Exception("mail_attachment_invalid");
				}
			}
		}

		private static function get_signature() {
			return file_get_contents(dirname(__DIR__).'/assets/signature.html');
		}
		
	}
?>