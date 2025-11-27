<?php
	if (ROOT_LOCAL === null) {
		require_once("../assets/global_requirements.php");
	}
	require_once(ROOT_LOCAL."/modules/AlexaNotificationModule.php");
	require_once(ROOT_LOCAL."/modules/DatabaseModule.php");
	require_once(ROOT_LOCAL."/modules/NewsletterModule.php");
	require_once(ROOT_LOCAL."/modules/SessionModule.php");
	require_once(ROOT_LOCAL."/modules/TokenModule.php");
	require_once(ROOT_LOCAL."/modules/TransferModule.php");
	require_once(ROOT_LOCAL."/modules/MailModule.php");
	
	class UserModule {
		
		public static function signUp($displayName, $eMailAddress, $password = null, 
				$passwordRepetition = null, $allowNewsletter = 0, $withEnrollment = false) {
			self::validateDisplayName($displayName);
			self::validateEMailAddress($eMailAddress, false, true);
			self::validatePassword($password, $passwordRepetition);
			self::validateAllowNewsletter($allowNewsletter);

			if (self::storeUser($displayName, $eMailAddress, $password, $allowNewsletter) === false) {
				throw new Exception("account_creation_failed");
			}

			$code = TokenModule::getCode(
				self::getUserIdByCredentials($eMailAddress, $password),
				TokenModule::TOKEN_TYPE_ACTIVATION
			);
			MailModule::sendSignUpRequestMail($eMailAddress, $displayName, $code, $withEnrollment);
		}
		
		private static function storeUser($displayName, $eMailAddress, $password, $allowNewsletter) {
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"INSERT INTO
				 account_data(displayName, eMailAddress, passwordHash, allowNewsletter, registrationDate)
				 VALUES(?, ?, ?, ?, NOW())"
			);
			$stmt->bind_param("sssi", $displayName, $eMailAddress, $passwordHash, $allowNewsletter);
			$result = $stmt->execute();
			$stmt->close();
			return $result;
		}

		public static function resendActivationMail($eMailAddress, $withEnrollment = false) {
			$userId = self::getUserIdByEMailAddress($eMailAddress);
			$user = self::loadUser($userId, ACCESS_LEVEL_DEVELOPER);

			if (intval($user["isActivated"]) !== 0) {
				throw new Exception("account_isActivated_already");
			}

			$code = TokenModule::getCode($user["userId"], TokenModule::TOKEN_TYPE_ACTIVATION);
			MailModule::sendSignUpRequestMail(
				$user["eMailAddress"], $user["displayName"], $code, $withEnrollment
			);
		}

		public static function verifyEventEnrollment($user, $eventTitle) {
			$code = TokenModule::getCode($user["userId"], TokenModule::TOKEN_TYPE_EVENT_ENROLLMENT);
			MailModule::sendEventEnrollmentRequestMail(
				$user["eMailAddress"], $user["displayName"], $code, $eventTitle
			);
		}

		public static function signIn($eMailAddress, $password) {
			self::validateEMailAddress($eMailAddress, true);
			self::validatePassword($password);

			$user = self::loadUser(self::getUserIdByCredentials($eMailAddress, $password), ACCESS_LEVEL_DEVELOPER);
			if (intval($user["isActivated"]) === 0) {
				throw new Exception("account_isActivated_not");
			}
			
			return $user;
		}
		
		private static function getUserIdByCredentials($eMailAddress, $password) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT userId, passwordHash FROM account_data WHERE eMailAddress=?"
			);
			$stmt->bind_param("s", $eMailAddress);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			
			if (password_verify($password, $user["passwordHash"]) === false) {
				throw new Exception("account_password_incorrect");
			}
			
			return intval($user["userId"]);
		}

		public static function getUserIdByEMailAddress($eMailAddress) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT userId FROM account_data WHERE eMailAddress=?"
			);
			$stmt->bind_param("s", $eMailAddress);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			if ($user === null) {
				throw new Exception("account_eMailAddress_not_taken");
			}
			return intval($user["userId"]);
		}

		public static function getUserIdByName($firstName, $lastName) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT userId FROM account_data WHERE firstName=? AND lastName=? ORDER BY userId"
			);
			$stmt->bind_param("ss", $firstName, $lastName);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			if ($user === null) {
				throw new Exception("account_id_not_exists");
			}
			return intval($user["userId"]);
		}
		
		public static function loadUser($userId, $ownAccessLevel) {
			if ($userId === null) {
				throw new Exception("account_id_not_exists");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT ad.userId, ad.displayName, ad.firstName, ad.lastName, ad.eMailAddress, 
				ad.streetName, ad.houseNumber, ad.supplementaryAddress, 
				ad.zipCode, ad.city, ad.country, ad.phoneNumber, ad.birthdate, 
				ad.eatingHabits, ad.allowPost, ad.allowNewsletter, ad.isActivated, 
				ad.registrationDate, ad.modificationDate, 
				al.accessLevel, al.accessIdentifier 
				FROM access_levels al, account_data ad 
				WHERE al.accessLevel=ad.accessLevel AND ad.userId=?"
			);
			$stmt->bind_param("i", $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			if ($user === null) {
				throw new Exception("account_id_not_exists");
			} else if ($user["accessLevel"] > $ownAccessLevel) {
				throw new Exception("error_message_NEP");
			}
			return $user;
		}

		public static function loadUserBySessionHash($sessionHash, $ownAccessLevel) {
			if ($sessionHash === null) {
				throw new Exception("account_id_not_exists");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT ad.userId, ad.displayName, ad.firstName, ad.lastName, ad.eMailAddress, 
				ad.streetName, ad.houseNumber, ad.supplementaryAddress, 
				ad.zipCode, ad.city, ad.country, ad.phoneNumber, ad.birthdate, 
				ad.eatingHabits, ad.allowPost, ad.allowNewsletter, ad.isActivated, 
				ad.registrationDate, ad.modificationDate, 
				al.accessLevel, al.accessIdentifier 
				FROM access_levels al, account_data ad, account_session_hashs ash, account_session_otps aso 
				WHERE al.accessLevel=ad.accessLevel AND ad.userId=aso.userId AND aso.OTP=ash.OTP AND ash.sessionHash=?"
			);
			$stmt->bind_param("s", $sessionHash);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$user = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			if ($user === null) {
				throw new Exception("account_id_not_exists");
			} else if ($user["accessLevel"] > $ownAccessLevel) {
				throw new Exception("error_message_NEP");
			}
			return $user;
		}
		
		public static function loadUserList($ownAccessLevel) {
			if ($ownAccessLevel === null) {
				throw new Exception("error_message_NEP");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT userId FROM account_data WHERE accessLevel <= ? ORDER BY accessLevel DESC, userId"
			);
			$stmt->bind_param("i", $ownAccessLevel);
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			
			$userObjectList = array();
			$res = $stmt->get_result();
			while($user = $res->fetch_assoc()) {
				array_push($userObjectList, self::loadUser($user["userId"], $ownAccessLevel));
			}
			$stmt->close();
			
			return $userObjectList;
		}
		
		public static function loadAccessLevels($maxAccessLevel) {
			if ($maxAccessLevel === null) {
				throw new Exception("account_accessLevel_invalid");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT accessLevel, accessIdentifier FROM access_levels WHERE accessLevel <= ? ORDER BY accessLevel DESC"
			);
			$stmt->bind_param("i", $maxAccessLevel);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			
			$accessLevels = array();
			$res = $stmt->get_result();
			while($accessLevel = $res->fetch_assoc()) {
				array_push($accessLevels, $accessLevel);
			}
			$stmt->close();
			return $accessLevels;
		}

		public static function maskPrivate($user) {
			return array_intersect_key(
				$user, 
				array_flip(array(
					"userId", "displayName", "eMailAddress",
					"accessLevel", "accessIdentifier",
					"firstName", "lastName",
					"streetName", "houseNumber", "supplementaryAddress",
					"zipCode", "city", "country",
					"phoneNumber",
					"birthdate", "eatingHabits",
					"allowPost", "allowNewsletter"
				))
			);
		}

		public static function maskPublic($user) {
			return array_intersect_key(
				$user, 
				array_flip(array(
					"userId", "displayName",
					"accessLevel", "accessIdentifier"
				))
			);
		}
		
		public static function updateAccessLevel($userId, $accessLevel, $ownAccessLevel) {
			self::validateAccessLevel($accessLevel, false, $ownAccessLevel);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET accessLevel=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("ii", $accessLevel, $userId);

			$result = $stmt->execute();
			$stmt->close();
			
			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}
		
		public static function updateFirstName($userId, $firstName) {
			self::validateFirstName($firstName);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET firstName=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $firstName, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}
		
		public static function updateLastName($userId, $lastName) {
			self::validateLastName($lastName);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET lastName=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $lastName, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}
		
		public static function updateDisplayName($userId, $displayName) {
			self::validateDisplayName($displayName);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET displayName=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $displayName, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function initiateAccountTransfer($userId, $newEMailAddress) {
			$user = self::loadUser($userId, ACCESS_LEVEL_DEVELOPER);
			if (intval($user["isActivated"]) === 0) {
				throw new Exception("account_isActivated_not");
			}

			TokenModule::expireToken(TokenModule::getTokenByUserId($userId));
			self::validateEMailAddress($newEMailAddress, false, true);
			TransferModule::initiateTransfer($userId, $newEMailAddress);

			self::requestAccountTransferMail($user["eMailAddress"]);
		}

		public static function requestAccountTransferMail($oldEMailAddress) {
			$user = self::loadUser(self::getUserIdByEMailAddress($oldEMailAddress), ACCESS_LEVEL_DEVELOPER);

			TokenModule::cleanUpTokens();
			$transfer = TransferModule::getTransfer($user["userId"]);
			$code = TokenModule::getCode($user["userId"], TokenModule::TOKEN_TYPE_E_MAIL_UPDATE);

			if (intval($transfer["oldEMailAddressConfirmed"]) === 0) {
				MailModule::sendOldEMailUpdateRequestMail($user["eMailAddress"], $transfer["newEMailAddress"], $user["displayName"], $code);
				CookieModule::set("alert", new Alert("success", $GLOBALS["dict"]["account_transfer_initialized"]));
			} else {
				MailModule::sendNewEMailUpdateRequestMail($transfer["newEMailAddress"], $user["displayName"], $code);
				CookieModule::set("alert", new Alert("success", $GLOBALS["dict"]["account_transfer_progressed"]));
			}
		}
		
		public static function updateEMailAddress($userId, $eMailAddress) {
			self::validateEMailAddress($eMailAddress, false, false);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET eMailAddress=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $eMailAddress, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateStreetName($userId, $streetName) {
			self::validateStreetName($streetName);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET streetName=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $streetName, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateHouseNumber($userId, $houseNumber) {
			self::validateHouseNumber($houseNumber);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET houseNumber=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $houseNumber, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateSupplementaryAddress($userId, $supplementaryAddress) {
			self::validateSupplementaryAddress($supplementaryAddress);
			
			$supplementaryAddress = empty($supplementaryAddress) === true ? null : trim($supplementaryAddress);
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET supplementaryAddress=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $supplementaryAddress, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateZipCode($userId, $zipCode) {
			self::validateZipCode($zipCode);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET zipCode=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $zipCode, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateCity($userId, $city) {
			self::validateCity($city);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET city=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $city, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateCountry($userId, $country) {
			self::validateCountry($country);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET country=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $country, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updatePhoneNumber($userId, $phoneNumber) {			
			self::validatePhoneNumber($phoneNumber);
			
			$phoneNumber = empty($phoneNumber) === true ? null : trim($phoneNumber);
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET phoneNumber=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $phoneNumber, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateBirthdate($userId, $birthdate) {
			$birthdate = DateTime::createFromFormat(DATE_FORMAT_USER_DATE, $birthdate, new DateTimeZone(SERVER_TIMEZONE));

			self::validateBirthdate($birthdate);

			$databaseFormattedBirthdate = $birthdate->format(DATE_FORMAT_DB_FULL);
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET birthdate=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $databaseFormattedBirthdate, $userId);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateEatingHabits($userId, $eatingHabits) {
			self::validateEatingHabits($eatingHabits);
			
			$eatingHabits = empty($eatingHabits) === true ? null : trim($eatingHabits);
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET eatingHabits=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $eatingHabits, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateAllowPost($userId, $allowPost) {
			self::validateAllowPost($allowPost);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET allowPost=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("ii", $allowPost, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateAllowNewsletter($userId, $allowNewsletter) {
			self::validateAllowNewsletter($allowNewsletter);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET allowNewsletter=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("ii", $allowNewsletter, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function requestPasswordReset($eMailAddress) {
			$userId = self::getUserIdByEMailAddress($eMailAddress);
			$user = self::loadUser($userId, ACCESS_LEVEL_DEVELOPER);
			$code = TokenModule::getCode($user["userId"], TokenModule::TOKEN_TYPE_PASSWORD_RESET);
			MailModule::sendPasswordResetRequestMail($user["eMailAddress"], $user["displayName"], $code);
		}
		
		public static function updatePassword($userId, $password, $passwordRepetition) {
			self::validatePassword($password, $passwordRepetition);
			
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET passwordHash=?, modificationDate=NOW() WHERE userId=?"
			);
			$stmt->bind_param("si", $passwordHash, $userId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			$stmt->close();
		}

		public static function updateIsActivated($userId, $isActivated) {
			self::validateIsActivated($isActivated);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE account_data SET isActivated=? WHERE userId=?"
			);
			$stmt->bind_param("ii", $isActivated, $userId);

			if ($stmt->execute() === false || $stmt->affected_rows !== 1) {
				$stmt->close();
				if (intval($isActivated) === 0) {
					throw new Exception("account_isActivated_deactivation_failed");
				} else {
					throw new Exception("account_isActivated_activation_failed");
				}
			}
			$stmt->close();

			NewsletterModule::deleteRegistration(
				self::loadUser($userId, ACCESS_LEVEL_DEVELOPER)["eMailAddress"]
			);

			if (intval($isActivated) === 1) {
				AlexaNotificationModule::sendUserActivationNotification($userId);
			}
		}

		public static function requestAccountDeletion($userId) {
			$user = self::loadUser($userId, ACCESS_LEVEL_DEVELOPER);
			$code = TokenModule::getCode($user["userId"], TokenModule::TOKEN_TYPE_DELETION);
			MailModule::sendAccountDeletionRequestMail($user["eMailAddress"], $user["displayName"], $code);
		}
		
		public static function deleteUser($userId) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"DELETE FROM account_data WHERE userId=?"
			);
			$stmt->bind_param("i", $userId);
			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}
		
		
		/* Validation */

		private static function validateAccessLevel($accessLevel, $includeGuest, $ownAccessLevel) {
			$accessLevels = self::loadAccessLevels($ownAccessLevel);
			$found = false;

			if ($accessLevel !== ACCESS_LEVEL_GUEST || $includeGuest === true) {
				foreach ($accessLevels as $accessLevelEntry) {
					if ($accessLevelEntry["accessLevel"] === $ownAccessLevel) {
						$found = true;
						break;
					}
				}
			}

			if ($found === false) {
				throw new Exception("account_accessLevel_invalid");
			}
		}
		
		private static function validateFirstName($firstName) {
			$firstName = empty($firstName) === true ? null : trim($firstName);
			
			if (empty($firstName) === true) {
				throw new Exception("account_firstName_required");
			} else if (strlen($firstName) > FIRSTNAME_LENGTH_MAX) {
				throw new Exception("account_firstName_invalid");
			}
		}
		
		private static function validateLastName($lastName) {
			$lastName = empty($lastName) === true ? null : trim($lastName);
			
			if (empty($lastName) === true) {
				throw new Exception("account_lastName_required");
			} else if (strlen($lastName) > LASTNAME_LENGTH_MAX) {
				throw new Exception("account_lastName_invalid");
			}
		}
		
		private static function validateDisplayName($displayName) {
			$displayName = trim($displayName);
			if (empty($displayName) === true || strlen($displayName) > DISPLAYNAME_LENGTH_MAX) {
				throw new Exception("account_displayName_invalid");
			}
			if (self::isDisplayNameTaken($displayName) === true) {
				throw new Exception("account_displayName_taken");
			}
		}
		
		public static function validateEMailAddress($eMailAddress, $shouldBeTaken, $includeTransfers = true) {
			$eMailAddress = trim($eMailAddress);
			if (filter_var($eMailAddress, FILTER_VALIDATE_EMAIL) === false 
					|| strlen($eMailAddress) > EMAILADDRESS_LENGTH_MAX) {
				throw new Exception("account_eMailAddress_invalid");
			}
			
			if (self::isEMailAddressTaken($eMailAddress, $includeTransfers) !== $shouldBeTaken) {
				throw new Exception(
					$shouldBeTaken === true ?
					"account_eMailAddress_not_taken" :
					"account_eMailAddress_taken"
				);
			}
		}

		private static function validateStreetName($streetName) {
			$streetName = empty($streetName) === true ? null : trim($streetName);

            if (empty($streetName) === true) {
				throw new Exception("account_streetName_required");
            } else if (strlen($streetName) > STREETNAME_LENGTH_MAX) {
				throw new Exception("account_streetName_invalid");
			}
		}

		private static function validateHouseNumber($houseNumber) {
			$houseNumber = empty($houseNumber) === true ? null : trim($houseNumber);
			
			if (empty($houseNumber) === true) {
				throw new Exception("account_houseNumber_required");
			} else if (strlen($houseNumber) > HOUSENUMBER_LENGTH_MAX) {
				throw new Exception("account_houseNumber_invalid");
			}
		}

		private static function validateSupplementaryAddress($supplementaryAddress) {
			$supplementaryAddress = empty($supplementaryAddress) === true ? null : trim($supplementaryAddress);
			
			if ($supplementaryAddress !== null && strlen($supplementaryAddress) > SUPPLEMENTARY_ADDRESS_LENGTH_MAX) {
				throw new Exception("account_supplementaryAddress_invalid");
			}
		}

		private static function validateZipCode($zipCode) {
			$zipCode = empty($zipCode) === true ? null : trim($zipCode);

            if (empty($zipCode) === true) {
				throw new Exception("account_zipCode_required");
            } else if (strlen($zipCode) > ZIPCODE_LENGTH_MAX) {
				throw new Exception("account_zipCode_invalid");
			}
		}

		private static function validateCity($city) {
			$city = empty($city) === true ? null : trim($city);

			if (empty($city) === true) {
				throw new Exception("account_city_required");
			} else if (strlen($city) > CITY_LENGTH_MAX) {
				throw new Exception("account_city_invalid");
			}
		}

		private static function validateCountry($country) {
			$country = empty($country) === true ? null : trim($country);

			if (empty($country) === true) {
				throw new Exception("account_country_required");
			} else if (strlen($country) > COUNTRY_LENGTH_MAX) {
				throw new Exception("account_country_invalid");
			}
		}

		private static function validatePhoneNumber($phoneNumber) {
			$phoneNumber = empty($phoneNumber) === true ? null : trim($phoneNumber);

			if ($phoneNumber !== null && strlen($phoneNumber) > PHONENUMBER_LENGTH_MAX) {
				throw new Exception("account_phoneNumber_invalid");
			}
		}

		private static function validateBirthdate($birthdate) {
			if ($birthdate === false) {
				throw new Exception("account_birthdate_required");
			}
			if ($birthdate > new DateTime()) {
				throw new Exception("account_birthdate_invalid");
			}
		}

		private static function validateEatingHabits($eatingHabits) {
			$eatingHabits = empty($eatingHabits) === true ? null : trim($eatingHabits);

			if ($eatingHabits !== null && strlen($eatingHabits) > EATINGHABITS_LENGTH_MAX) {
				throw new Exception("account_eatingHabits_invalid");
			}
		}

		private static function validateAllowPost($allowPost) {
			try {
				if (intval($allowPost) !== 1 && intval($allowPost) !== 0) {
					throw new Exception("stub");
				}
			} catch (Exception $exc) {
				throw new Exception("account_allowPost_invalid");
			}
		}

		private static function validateAllowNewsletter($allowNewsletter) {
			try {
				if (intval($allowNewsletter) !== 1 && intval($allowNewsletter) !== 0) {
					throw new Exception("stub");
				}
			} catch (Exception $exc) {
				throw new Exception("account_allowNewsletter_invalid");
			}
		}
		
		private static function validatePassword($password, $passwordRepetition = null) {
			if ($passwordRepetition === null) {
				$passwordRepetition = $password;
			}
			
			if ($password !== null && strlen($password) < PASSWORD_LENGTH_MIN) {
				throw new Exception("account_password_invalid");
			}
			
			if ($passwordRepetition !== $password) {
				throw new Exception("account_passwordRepetition_incorrect");
			}
		}

		private static function validateIsActivated($isActivated) {
			try {
				if (intval($isActivated) !== 1 && intval($isActivated) !== 0) {
					throw new Exception("stub");
				}
			} catch (Exception $exc) {
				throw new Exception("account_isActivated_invalid");
			}
		}
		
		private static function isDisplayNameTaken($displayName) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT 1 FROM account_data WHERE displayName=?"
			);
			$stmt->bind_param("s", $displayName);
			$stmt->execute();
			$stmt->store_result();
			
			$isTaken = $stmt->num_rows > 0;
			$stmt->close();
			return $isTaken;
		}
		
		public static function isEMailAddressTaken($eMailAddress, $includeTransfers = true) {
			$occurenceCounter = 0;

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT 1 FROM account_data WHERE eMailAddress=?"
			);
			$stmt->bind_param("s", $eMailAddress);
			$stmt->execute();
			$stmt->store_result();
			$occurenceCounter += $stmt->num_rows;
			$stmt->close();

			if ($includeTransfers === true) {
				$stmt = DatabaseModule::getInstance()->prepare(
					"SELECT 1 FROM account_transfers WHERE newEMailAddress=?"
				);
				$stmt->bind_param("s", $eMailAddress);
				$stmt->execute();
				$stmt->store_result();
				$occurenceCounter += $stmt->num_rows;
				$stmt->close();
			}

			return $occurenceCounter > 0;
		}
		
	}
?>