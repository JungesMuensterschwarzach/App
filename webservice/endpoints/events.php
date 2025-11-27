<?php
	require_once("../assets/global_requirements.php");
	require_once(ROOT_LOCAL."/modules/EventModule.php");
	require_once(ROOT_LOCAL."/endpoints/response.php");
	
	
	$response = new Response();
	try {
		if (isset($_POST["action"]) === false) {
			throw new Exception("error_message_NEP");
		}

		$ownUser = isset($_COOKIE[CookieModule::getFullName("sessionHash")]) ?
			UserModule::loadUserBySessionHash(
				$_COOKIE[CookieModule::getFullName("sessionHash")], 
				ACCESS_LEVEL_DEVELOPER
			) :
			null;
		$_POST["action"]($ownUser, $response);
	} catch (Exception $exc) {
		$response->setErrorMsg($exc->getMessage());
	}
	
	echo(json_encode($response));


	function fetchEventList($ownUser, $response) {
		$events = EventModule::loadEventList($ownUser ? $ownUser["accessLevel"] : ACCESS_LEVEL_GUEST);
		$events = array_map(function($event) {
			$event["eventParticipants"] = maskAndSortParticipants($event["eventParticipants"]);
			return $event;
		}, $events);
		$response->setEventList($events);
	}

	function fetchEvent($ownUser, $response) {
		$event = EventModule::loadEvent($_POST["eventId"], $ownUser ? $ownUser["accessLevel"] : ACCESS_LEVEL_GUEST);
		
		$event["eventParticipants"] = maskAndSortParticipants($event["eventParticipants"]);

		$response->setEventList(array($event));
	}

	function fetchEventEnrollment($ownUser, $response) {
		if ($ownUser === null) {
			throw new Exception("error_message_account_required");
		}

		$response->setEventEnrollment(
			EventModule::loadEventEnrollment($_POST["eventId"], $ownUser["userId"], $ownUser["accessLevel"])
		);
	}

	function enroll($ownUser, $response) {
		if ($ownUser === null) {
			throw new Exception("error_message_account_required");
		}

		EventModule::enroll($ownUser["userId"], $_POST["eventId"], $_POST["eventEnrollmentComment"], $ownUser["accessLevel"]);
		$response->setSuccessMsg("event_user_enrolled");
	}

	function updateEventEnrollmentComment($ownUser, $response) {
		if ($ownUser === null) {
			throw new Exception("error_message_account_required");
		}

		EventModule::updateEventEnrollmentComment($ownUser["userId"], $_POST["eventId"], $_POST["eventEnrollmentComment"], $ownUser["accessLevel"]);
		$response->setSuccessMsg("event_eventEnrollmentComment_updated");
	}

	function disenroll($ownUser, $response) {
		if ($ownUser === null) {
			throw new Exception("error_message_account_required");
		}

		EventModule::disenroll($ownUser["userId"], $_POST["eventId"], $ownUser["accessLevel"]);
		$response->setSuccessMsg("event_user_disenrolled");
	}

	function checkIn($ownUser, $response) {
		if ($ownUser === null) {
			throw new Exception("error_message_account_required");
		}

		EventModule::checkIn($ownUser["userId"], $_POST["eventId"], $_POST["eventEnrollmentPublicMediaUsageConsent"], $ownUser["accessLevel"]);
		$response->setSuccessMsg("event_user_checked_in");
	}

	function checkInExpress($ownUser, $response) {
		$userId = UserModule::getUserIdByName($_POST["firstName"], $_POST["lastName"]);
		$user = UserModule::loadUser($userId, ACCESS_LEVEL_DEVELOPER);
		$event = EventModule::getCurrentEvent();

		EventModule::checkIn($user["userId"], $event["eventId"], $_POST["eventEnrollmentPublicMediaUsageConsent"], $user["accessLevel"]);
		$response->setSuccessMsg("event_user_checked_in");
	}

	function updateEventEnrollmentPublicMediaUsageConsent($ownUser, $response) {
		if ($ownUser === null) {
			throw new Exception("error_message_account_required");
		}

		EventModule::updateEventEnrollmentPublicMediaUsageConsent($ownUser["userId"], $_POST["eventId"], $_POST["eventEnrollmentPublicMediaUsageConsent"], $ownUser["accessLevel"]);
		$response->setSuccessMsg("event_eventEnrollmentPublicMediaUsageConsent_updated");
	}

	function maskAndSortParticipants($participants) {
		$participants = array_map(function($user) {
			return UserModule::maskPublic($user);
		}, $participants);

		$accessLevels = array();
		$displayNames = array();
		foreach ($participants as $key => $user) {
			$accessLevels[$key] = $user["accessLevel"];
			$displayNames[$key] = $user["displayName"];
		}
		array_multisort(
			$accessLevels, SORT_DESC,
			$displayNames, SORT_ASC,
			$participants
		);
		
		return $participants;
	}

?>