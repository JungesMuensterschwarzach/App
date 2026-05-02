<?php
	use PhpImap\Mailbox;

	if (ROOT_LOCAL === null) {
		require_once("../assets/global_requirements.php");
	}
	require_once(ROOT_LOCAL."/modules/EventModule.php");
	require_once(ROOT_LOCAL."/modules/MailModule.php");
	require_once(ROOT_LOCAL."/modules/UserModule.php");

	require_once(ROOT_LOCAL."/libs/PhpImap/Exceptions/ConnectionException.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/Exceptions/InvalidParameterException.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/DataPartInfo.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/Imap.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/IncomingMailAttachment.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/IncomingMailHeader.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/IncomingMail.php");
	require_once(ROOT_LOCAL."/libs/PhpImap/Mailbox.php");

	class WebsiteEnrollmentModule {

		public static function cron() {
			try {
				$mailbox = self::getMailbox();
				self::createMailboxes($mailbox);

				$enrollmentContents = self::getMailContents($mailbox, MAIL_FOLDER_DEFAULT);

				foreach ($enrollmentContents as $mailId => $enrollmentContent) {
					try {
						$toMailbox = WebsiteEnrollmentModule::processNewEnrollment($enrollmentContent);
						echo("Processed event enrollment.<br/>");
						$mailbox->moveMail($mailId, $toMailbox);
						echo("Moved mail with ID " . $mailId . " into folder " 
							. $toMailbox . ".<br/>");
					} catch (Exception $exc) {
						echo("Error: " . $exc->getMessage() . "<br/>");
						$mailbox->moveMail(
							$mailId, MAIL_FOLDER_ENROLLMENTS_FAILED
						);
						$mailbox->markMailAsUnread($mailId);
						MailModule::sendEventEnrollmentNotificationFailureMail(
							$exc->getMessage(), $exc->getTrace()
						);
						echo("Moved mail with ID " . $mailId . " into folder "
							. MAIL_FOLDER_ENROLLMENTS_FAILED . ".<br/>");
					}
					echo("<br/>");
				}
			} catch (Exception $exc) {
				echo("Error: " . $exc->getMessage());
			}
		}

		public static function processNewEnrollment($enrollmentContent) {
			$enrollment = self::parse($enrollmentContent);
			$toMailbox = MAIL_FOLDER_ENROLLMENTS_PREPROCESSED;

			if (UserModule::isEMailAddressTaken($enrollment["eMailAddress"], false)) {
				$userId = UserModule::getUserIdByEMailAddress($enrollment["eMailAddress"]);
				$user = UserModule::loadUser($userId, ACCESS_LEVEL_DEVELOPER);

				if (intval($user["isActivated"]) === 0) {
					UserModule::resendActivationMail($user["eMailAddress"], true);
				} else if (self::isAlreadyEnrolled($userId, $enrollment["event"]["eventParticipants"])) {
					$toMailbox = MAIL_FOLDER_ENROLLMENTS_ENROLLED;
				} else {
					UserModule::verifyEventEnrollment($user, $enrollment["event"]["eventTitle"]);
				}
			} else {
				$timeParts = explode(" ", microtime());
				$timestamp = $timeParts[1] . $timeParts[0] * 100000000;
				UserModule::signUp(
					$GLOBALS["dict"]["account_accessLevel_user"] . "-" . $timestamp,
					$enrollment["eMailAddress"], null, null, 0, true
				);
			}

			return $toMailbox;
		}

		public static function applyEnrollmentMails($eMailAddress, $triggerPasswordReset) {
			$mailbox = self::getMailbox();
			$contents = self::getMailContents(
				$mailbox,
				MAIL_FOLDER_ENROLLMENTS_PREPROCESSED
			);

			foreach ($contents as $mailId => $content) {
				try {
					$enrollment = self::parse($content);

					if ($enrollment["eMailAddress"] !== $eMailAddress) {
						continue;
					}

					$userId = UserModule::getUserIdByEMailAddress($eMailAddress);
					self::updateAccountInformation($userId, $enrollment);
					
					$savedEnrollment = EventModule::loadEventEnrollment(
						$enrollment["event"]["eventId"], $userId, ACCESS_LEVEL_DEVELOPER
					);
					if ($savedEnrollment === null) {
						EventModule::enroll(
							$userId,
							$enrollment["event"]["eventId"],
							$enrollment["eventEnrollmentComment"],
							ACCESS_LEVEL_DEVELOPER
						);
					} else {
						EventModule::updateEventEnrollmentComment(
							$userId, $enrollment["event"]["eventId"],
							$enrollment["eventEnrollmentComment"], ACCESS_LEVEL_DEVELOPER
						);
					}

					$mailbox->moveMail(
						$mailId, MAIL_FOLDER_ENROLLMENTS_ENROLLED
					);
					
					if ($triggerPasswordReset === true) {
						UserModule::requestPasswordReset($eMailAddress);
						$triggerPasswordReset = false;
					}
				} catch (Exception $exc) {
					error_log($exc->getMessage());
					$mailbox->moveMail(
						$mailId, MAIL_FOLDER_ENROLLMENTS_FAILED
					);
					$mailbox->markMailAsUnread($mailId);
					MailModule::sendEventEnrollmentNotificationFailureMail(
						$exc->getMessage(), $exc->getTrace()
					);
				}
			}
		}

		public static function expireMails($eMailAddress) {
			$mailbox = self::getMailbox();

			$contents = self::getMailContents(
				$mailbox, MAIL_FOLDER_ENROLLMENTS_PREPROCESSED
			);
			foreach ($contents as $mailId => $content) {
				$enrollment = self::parse($content);

				if ($enrollment["eMailAddress"] !== $eMailAddress) {
					continue;
				}

				$mailbox->moveMail(
					$mailId, MAIL_FOLDER_ENROLLMENTS_UNCONFIRMED
				);
				$mailbox->markMailAsUnread($mailId);
			}
		}

		private static function isAlreadyEnrolled($userId, $participants) {
			return in_array(
				$userId,
				array_map(function($participant) { return $participant["userId"]; }, $participants)
			);
		}

		private static function getMailbox() {
			$mailbox = new Mailbox(
				MAIL_FOLDER_PREFIX,
				MAIL_ACCOUNT_NAME,
				MAIL_PASSWORD
			);
			$mailbox->setAttachmentsIgnore(true);
			return $mailbox;
		}

		private static function createMailboxes(Mailbox $mailbox) {
			$mailboxes = $mailbox->getMailboxes();
			$mailboxNames = array_map(function($box) { return $box["shortpath"]; }, $mailboxes);
			$foldersToCreate = array(
				MAIL_FOLDER_ENROLLMENTS_PREPROCESSED => true,
				MAIL_FOLDER_ENROLLMENTS_ENROLLED => true,
				MAIL_FOLDER_ENROLLMENTS_UNCONFIRMED => true,
				MAIL_FOLDER_ENROLLMENTS_FAILED => true
			);
	
			/** @var string $mailboxName */
			foreach ($mailboxNames as $mailboxName) {
				foreach ($foldersToCreate as $folder => $toCreate) {
					if (strpos($mailboxName, $folder) !== false) {
						$foldersToCreate[$folder] = false;
						break;
					}
				}
			}
	
			foreach ($foldersToCreate as $folder => $toCreate) {
				if ($toCreate) {
					$mailbox->createMailbox($folder);
					echo("Created Mailbox " . $folder . ".<br/>");
				} else {
					echo("Mailbox " . $folder . " already exists.<br/>");
				}
			}
		}

		private static function getMailContents(Mailbox $mailbox, $folder) {
			$eventEnrollmentTitle = "Neue Veranstaltungsanmeldung";
			
			$mailbox->switchMailbox(MAIL_FOLDER_PREFIX . $folder);
			$mailIds = $mailbox->searchMailbox();
			sort($mailIds);

			$eventEnrollmentContents = array();
			foreach ($mailIds as $mailId) {
				$mail = $mailbox->getMail($mailId);
	
				if (trim($mail->subject) !== $eventEnrollmentTitle) {
					continue;
				}
	
				$eventEnrollmentContents[$mailId] = 
					$mail->textHtml ?
						strip_tags($mail->textHtml) :
						$mail->textPlain;
			}

			return $eventEnrollmentContents;
		}

		private static function parse($enrollmentContent) {
			$keyDict = array( // mail key to enrollment key
				// on update: update "array_keys" code below 
				"Kursauswahl" => "event",
				"Vorname" => "firstName",
				"Nachname" => "lastName",
				"Straße" => "streetName",
				"Hausnummer" => "houseNumber",
				"Adresszusatz" => "supplementaryAddress",
				"PLZ" => "zipCode",
				"Ort" => "city",
				"Land" => "country",
				"Mail" => "eMailAddress",
				"Telefon" => "phoneNumber",
				"Geburtstag" => "birthdate",
				"Bitte sag uns, ob eine der folgenden Essgewohnheiten auf dich zutreffen" => "eatingHabits", // tmp fix for malformed eating habits line
				"Möchtest du uns noch etwas sagen?" => "eventEnrollmentComment"
			);
			$content = str_replace("\r\n", "\n", trim($enrollmentContent));
			$lines = explode("\n", $content);
			$enrollment = array();

			$savedMailKey = null;
			$savedValue = "";
			foreach ($lines as $line) {
				$line = trim(str_replace("&nbsp;", " ", $line));
				if ($line === "") {
					continue;
				}

				$lineParts = explode(":", $line);
				if (count($lineParts) < 2) {
					$savedValue .= "\n" . $line;
					continue;
				}

				$key = $lineParts[0];
				$value = trim(implode(":", array_slice($lineParts, 1)));

				if (!array_key_exists($key, $keyDict)) {
					$savedValue .= "\n" . $line;
					continue;
				}

				if ($savedMailKey !== null && $savedMailKey !== $key) {
					$enrollment = self::updateParsedEnrollment(
						$enrollment, $savedMailKey, $savedValue, $keyDict
					);
				}

				$savedMailKey = $key;
				switch ($key) {
					case array_keys($keyDict)[0]:
						$title = explode(' ', $value)[0];
						$savedValue = EventModule::getNextEventByTitle($title);
						break;
					case array_keys($keyDict)[9]:
						$savedValue = strtolower($value);
						break;
					case array_keys($keyDict)[11]:
						$birthdate = new DateTime($value, new DateTimeZone(SERVER_TIMEZONE));
						$savedValue = $birthdate->format(DATE_FORMAT_USER_DATE);
						break;
					case array_keys($keyDict)[12]:
						$savedValue = substr($value, 2); // tmp fix for malformed eating habits line
						break;
					default:
						$savedValue = $value;
						break;
				}
			}

			if ($savedMailKey !== null) {
				$enrollment = self::updateParsedEnrollment(
					$enrollment, $savedMailKey, $savedValue, $keyDict
				);
			}

			return $enrollment;
		}

		private static function updateParsedEnrollment($enrollment, $mailKey, $value, $keyDict) {
			if ($value === "") {
				$value = null;
			}

			$enrollment[$keyDict[$mailKey]] = $value;

			return $enrollment;
		}

		private static function updateAccountInformation($userId, $enrollment) {
			UserModule::updateFirstName($userId, $enrollment["firstName"]);
			UserModule::updateLastName($userId, $enrollment["lastName"]);
			UserModule::updateStreetName($userId, $enrollment["streetName"]);
			UserModule::updateHouseNumber($userId, $enrollment["houseNumber"]);
			UserModule::updateSupplementaryAddress($userId, $enrollment["supplementaryAddress"]);
			UserModule::updateZipCode($userId, $enrollment["zipCode"]);
			UserModule::updateCity($userId, $enrollment["city"]);
			UserModule::updateCountry($userId, $enrollment["country"]);
			UserModule::updateBirthdate($userId, $enrollment["birthdate"]);
			UserModule::updateEatingHabits($userId, $enrollment["eatingHabits"]);
			UserModule::updatePhoneNumber($userId, $enrollment["phoneNumber"]);
		}
		
	}
?>