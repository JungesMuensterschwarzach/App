<?php
	if (ROOT_LOCAL === null) {
		require_once("../assets/global_requirements.php");
	}
	require_once(ROOT_LOCAL."/modules/AlexaNotificationModule.php");
	require_once(ROOT_LOCAL."/modules/DatabaseModule.php");
	require_once(ROOT_LOCAL."/modules/UserModule.php");
	require_once(ROOT_LOCAL."/modules/MailModule.php");

	class EventComponent {

		// do not change these values!
		// they are in dynamic usage in javascript and php!
		const ARRIVALS = "Arrival";
		const LOCATIONS = "Location";
		const OFFERS = "Offer";
		const PACKING_LISTS = "PackingList";
		const PRICES = "Price";
		const SCHEDULES = "Schedule";
		const TARGET_GROUPS = "TargetGroup";

		private $eventComponentType;
		private static $reflectionClass;

		public function __construct($eventComponentType) {
			if (self::$reflectionClass === null) {
				self::$reflectionClass = new ReflectionClass($this);
			}

			if ($this->isValid($eventComponentType) === false) {
				throw new Exception("event_component_type_invalid");
			}

			$this->eventComponentType = $eventComponentType;
		}

		private function isValid($eventComponentType) {
			$isValid = false;

			foreach (self::$reflectionClass->getConstants() 
					as $eventComponentTypeKey => $eventComponentTypeValue) {
				if ($eventComponentTypeValue === $eventComponentType) {
					$isValid = true;
					break;
				}
			}

			return $isValid;
		}

		public function getTable() {
			foreach (self::$reflectionClass->getConstants() 
					as $eventComponentTypeKey => $eventComponentTypeValue) {
				if ($eventComponentTypeValue === $this->eventComponentType) {
					return "event_" . strtolower($eventComponentTypeKey);
				}
			}
			
			throw new Exception("event_component_type_invalid");
		}

		public function getIdColumn() {
			return "event{$this->eventComponentType}Id";
		}

		public function getTitleColumn() {
			return "event{$this->eventComponentType}Title";
		}

		public function getContentColumn() {
			return "event{$this->eventComponentType}Content";
		}

	}

	class EventModule {


		/* ####################### EVENT COMPONENTS ############################ */

		/* general event components */

		public static function loadEventComponentList($eventComponentType) {
			$eventComponent = new EventComponent($eventComponentType);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT {$eventComponent->getIdColumn()} 
				 FROM {$eventComponent->getTable()} 
				 ORDER BY {$eventComponent->getIdColumn()}"
			);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$res = $stmt->get_result();
			$eventCompList = array();
			while ($row = $res->fetch_assoc()) {
				array_push($eventCompList, 
					self::loadEventComponent($eventComponentType, $row[$eventComponent->getIdColumn()]));
			}
			$stmt->close();

			return $eventCompList;
		}

		public static function loadEventComponent($eventComponentType, $eventComponentId) {
			$eventComponent = new EventComponent($eventComponentType);

			if ($eventComponentId === null) {
				throw new Exception("event_component_id_invalid");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT {$eventComponent->getIdColumn()}, {$eventComponent->getTitleColumn()}, {$eventComponent->getContentColumn()}
			 	 FROM {$eventComponent->getTable()} 
			 	 WHERE {$eventComponent->getIdColumn()}=?"
			);
			$stmt->bind_param("i", $eventComponentId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$eventComp = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			
			if ($eventComp === null) {
				throw new Exception("error_message_NEP");
			}
			
			return $eventComp;
		}

		public static function createEventComponent($eventComponentType, $eventComponentTitle, $eventComponentContent) {
			$eventComponent = new EventComponent($eventComponentType);
			self::validateEventComponentTitle($eventComponentType, $eventComponentTitle);
			self::validateEventComponentContent($eventComponentContent);

			$stmt = DatabaseModule::getInstance()->prepare(
				"INSERT INTO {$eventComponent->getTable()}({$eventComponent->getTitleColumn()}, {$eventComponent->getContentColumn()}) 
			 	 VALUES (?, ?)"
			);
			$stmt->bind_param("ss", $eventComponentTitle, $eventComponentContent);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventComponentTitle($eventComponentType, $eventComponentId, $eventComponentTitle) {
			$eventComponent = new EventComponent($eventComponentType);
			$currentEventComponent = self::loadEventComponent($eventComponentType, $eventComponentId);

			if ($currentEventComponent[$eventComponent->getTitleColumn()] === $eventComponentTitle) {
				return;
			}
			self::validateEventComponentTitle($eventComponentType, $eventComponentTitle);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE {$eventComponent->getTable()} 
				 SET {$eventComponent->getTitleColumn()}=? 
				 WHERE {$eventComponent->getIdColumn()}=?"
			);
			$stmt->bind_param("si", $eventComponentTitle, $eventComponentId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventComponentContent($eventComponentType, $eventComponentId, $eventComponentContent) {
			$eventComponent = new EventComponent($eventComponentType);
			self::validateEventComponentContent($eventComponentContent);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE {$eventComponent->getTable()} 
				 SET {$eventComponent->getContentColumn()}=? 
				 WHERE {$eventComponent->getIdColumn()}=?"
			);
			$stmt->bind_param("si", $eventComponentContent, $eventComponentId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function deleteEventComponent($eventComponentType, $eventComponentId) {
			$eventComponent = new EventComponent($eventComponentType);

			$stmt = DatabaseModule::getInstance()->prepare(
				"DELETE FROM {$eventComponent->getTable()}
				 WHERE {$eventComponent->getIdColumn()}=?"
			);
			$stmt->bind_param("i", $eventComponentId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		/* adjusted event components */

		public static function loadEventLocationList() {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT eventLocationId 
				 FROM event_locations 
				 ORDER BY eventLocationId"
			);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$res = $stmt->get_result();
			$eventLocList = array();
			while ($row = $res->fetch_assoc()) {
				array_push($eventLocList, 
					self::loadEventLocation($row["eventLocationId"]));
			}
			$stmt->close();

			return $eventLocList;
		}

		public static function loadEventLocation($eventLocationId) {
			if ($eventLocationId === null) {
				throw new Exception("event_component_id_invalid");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT eventLocationId, eventLocationTitle, eventLocationLatitude, eventLocationLongitude 
			 	 FROM event_locations 
			 	 WHERE eventLocationId=?"
			);
			$stmt->bind_param("i", $eventLocationId);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$eventLoc = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			
			if ($eventLoc === null) {
				throw new Exception("error_message_NEP");
			}
			
			return $eventLoc;
		}

		public static function createEventLocation($eventLocationTitle, $eventLocationLatitude, $eventLocationLongitude) {
			self::validateEventComponentTitle(EventComponent::LOCATIONS, $eventLocationTitle);
			self::validateEventLocationLatitude($eventLocationLatitude);
			self::validateEventLocationLongitude($eventLocationLongitude);

			$stmt = DatabaseModule::getInstance()->prepare(
				"INSERT INTO event_locations(eventLocationTitle, eventLocationLatitude, eventLocationLongitude) 
			 	 VALUES (?, ?, ?)"
			);
			$stmt->bind_param("sdd", $eventLocationTitle, $eventLocationLatitude, $eventLocationLongitude);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventLocationTitle($eventLocationId, $eventLocationTitle) {
			$currentEventLocation = self::loadEventLocation($eventLocationId);

			if ($currentEventLocation["eventLocationTitle"] === $eventLocationTitle) {
				return;
			}
			self::validateEventComponentTitle(EventComponent::LOCATIONS, $eventLocationTitle);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE event_locations 
				 SET eventLocationTitle=? 
				 WHERE eventLocationId=?"
			);
			$stmt->bind_param("si", $eventLocationTitle, $eventLocationId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventLocationLatitudeLongitude($eventLocationId, $eventLocationLatitude, $eventLocationLongitude) {
			self::validateEventLocationLatitude($eventLocationLatitude);
			self::validateEventLocationLongitude($eventLocationLongitude);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE event_locations
				 SET eventLocationLatitude=?, eventLocationLongitude=?
				 WHERE eventLocationId=?"
			);
			$stmt->bind_param("ddi", $eventLocationLatitude, $eventLocationLongitude, $eventLocationId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function deleteEventLocation($eventLocationId) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"DELETE FROM event_locations 
				 WHERE eventLocationId=?"
			);
			$stmt->bind_param("i", $eventLocationId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}
		

		/* ####################### EVENTS ############################ */

		public static function loadEventList($ownAccessLevel) {
			if ($ownAccessLevel === null) {
				throw new Exception("error_message_NEP");
			}

			$sql = "SELECT eventId 
					FROM events 
					WHERE requiredAccessLevel<=? 
					ORDER BY eventStart ASC";

			$stmt = DatabaseModule::getInstance()->prepare($sql);
			$stmt->bind_param("i", $ownAccessLevel);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$res = $stmt->get_result();
			$eventList = array();
			while ($row = $res->fetch_assoc()) {
				array_push($eventList, self::loadEvent($row["eventId"], $ownAccessLevel));
			}
			$stmt->close();

			return $eventList;
		}

		public static function loadEvent($eventId, $ownAccessLevel) {
			if ($eventId === null) {
				throw new Exception("event_eventId_invalid");
			}
			if ($ownAccessLevel === null) {
				throw new Exception("error_message_NEP");
			}

			$event = self::loadEventRow($eventId, $ownAccessLevel);
			$event = self::appendImageIds($event);
			$event = self::appendParticipantsList($event);
			return $event;
		}

		private static function loadEventRow($eventId, $ownAccessLevel) {
			$sql = "SELECT e.eventId, e.eventTitle, e.eventTopic, e.eventDetails, 
						e.eventStart, e.eventEnd, e.eventEnrollmentStart, e.eventEnrollmentEnd, 
						el.eventLocationId, el.eventLocationTitle, el.eventLocationLatitude, el.eventLocationLongitude, 
						ea.eventArrivalId, ea.eventArrivalTitle, ea.eventArrivalContent, 
						eo.eventOfferId, eo.eventOfferTitle, eo.eventOfferContent, 
						epl.eventPackingListId, epl.eventPackingListTitle, epl.eventPackingListContent, 
						ep.eventPriceId, ep.eventPriceTitle, ep.eventPriceContent, 
						es.eventScheduleId, es.eventScheduleTitle, es.eventScheduleContent, 
						etg.eventTargetGroupId, etg.eventTargetGroupTitle, etg.eventTargetGroupContent, 
						e.requiredAccessLevel, al.accessIdentifier, e.eventModificationDate 
					FROM events e, event_locations el, event_arrivals ea, event_offers eo, event_packing_lists epl, 
						event_prices ep, event_schedules es, event_target_groups etg, access_levels al  
					WHERE e.eventId=? AND e.requiredAccessLevel<=? AND e.requiredAccessLevel=al.accessLevel
						AND e.eventLocationId=el.eventLocationId 
						AND e.eventArrivalId=ea.eventArrivalId 
						AND e.eventOfferId=eo.eventOfferId 
						AND e.eventPackingListId=epl.eventPackingListId 
						AND e.eventPriceId=ep.eventPriceId 
						AND e.eventScheduleId=es.eventScheduleId 
						AND e.eventTargetGroupId=etg.eventTargetGroupId";
			$stmt = DatabaseModule::getInstance()->prepare($sql);
			$stmt->bind_param("is", $eventId, $ownAccessLevel);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$event = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			
			if ($event === null) {
				throw new Exception("error_message_NEP");
			}
			
			return $event;
		}

		private static function appendImageIds($event) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT ei.imageId, il.path 
				FROM event_images ei, image_library il 
				WHERE ei.eventId=? AND ei.imageId=il.imageId 
				ORDER BY imageId ASC"
			);
			$stmt->bind_param("i", $event["eventId"]);
			
			$event["imageIds"] = array();
			
			if ($stmt->execute() === false) {
				$stmt->close();
				return $event;
			}
			
			$res = $stmt->get_result();
			while ($image = $res->fetch_assoc()) {
				array_push($event["imageIds"], $image);
			}
			
			$stmt->close();
			return $event;
		}

		private static function appendParticipantsList($event) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT ad.userId, ee.eventEnrollmentComment, ee.enrollmentDate, ee.checkInDate, ee.eventEnrollmentPublicMediaUsageConsent 
				 FROM account_data ad, event_enrollments ee 
				 WHERE ee.eventId=? AND ad.userId=ee.userId  
				 ORDER BY ee.checkInDate ASC, ee.enrollmentDate ASC"
			);
			$stmt->bind_param("i", $event["eventId"]);

			$event["eventParticipants"] = array();

			if ($stmt->execute() === false) {
				$stmt->close();
				return $event;
			}

			$res = $stmt->get_result();
			while ($row = $res->fetch_assoc()) {
				$participant = array_merge(
					UserModule::loadUser($row["userId"], ACCESS_LEVEL_DEVELOPER),
					$row
				);

				array_push($event["eventParticipants"], $participant);
			}

			$stmt->close();
			return $event;
		}

		public static function loadEventEnrollment($eventId, $userId, $ownAccessLevel) {
			if ($eventId === null) {
				throw new Exception("event_eventId_invalid");
			}
			if ($userId === null) {
				throw new Exception("account_id_not_exists");
			}
			if ($ownAccessLevel === null) {
				throw new Exception("error_message_NEP");
			}
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT ee.eventEnrollmentComment, ee.eventEnrollmentPublicMediaUsageConsent 
				 FROM events e, event_enrollments ee 
				 WHERE ee.eventId=? AND ee.userId=? AND e.requiredAccessLevel<=? AND e.eventId=ee.eventId"
			);
			$stmt->bind_param("iii", $eventId, $userId, $ownAccessLevel);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$eventEnrollment = $stmt->get_result()->fetch_assoc();
			$stmt->close();
			return $eventEnrollment;
		}

		public static function getNextEventByTitle($eventTitle) {
			$sql = "SELECT eventId, eventTitle, eventEnrollmentStart, eventEnrollmentEnd 
					FROM events
					ORDER BY eventStart";
			$stmt = DatabaseModule::getInstance()->prepare($sql);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			
			$res = $stmt->get_result();
			$now = new DateTime();
			$eventId = null;
			while ($row = $res->fetch_assoc()) {
				if (stripos($row["eventTitle"], $eventTitle) === false) {
					continue;
				}

				$eventEnrollmentStart = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, 
					$row["eventEnrollmentStart"], new DateTimeZone(SERVER_TIMEZONE));
				$eventEnrollmentEnd = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, 
					$row["eventEnrollmentEnd"], new DateTimeZone(SERVER_TIMEZONE));

				if ($now < $eventEnrollmentStart) {
					continue;
				} else if ($eventEnrollmentEnd < $now) {
					continue;
				}

				$eventId = $row["eventId"];
				break;
			}
			$stmt->close();

			return self::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER);
		}

		public static function getCurrentEvent() {
			$sql = "SELECT eventId
					FROM events
					WHERE eventStart<=NOW() AND NOW()<=eventEnd
					ORDER BY eventStart";
			$stmt = DatabaseModule::getInstance()->prepare($sql);
			
			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}
			
			$event = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			if ($event === null) {
				throw new Exception("event_not_exists");
			}

			return self::loadEvent($event["eventId"], ACCESS_LEVEL_DEVELOPER);
		}

		public static function createEvent($eventTitle, $eventTopic, $eventDetails, $eventStart, $eventEnd, $eventEnrollmentStart, $eventEnrollmentEnd, 
				$eventOfferId, $eventScheduleId, $eventTargetGroupId, $eventPriceId, $eventPackingListId, $eventLocationId, $eventArrivalId, $requiredAccessLevel, $ownAccessLevel) {
			self::validateEventTitle($eventTitle);
			self::validateEventTopic($eventTopic);
			self::validateEventDetails($eventDetails);
			self::validateEventStart($eventStart, $eventEnd);
			self::validateEventEnrollmentStart($eventEnrollmentStart, $eventEnrollmentEnd);
			self::validateEventComponentId(EventComponent::OFFERS, $eventOfferId);
			self::validateEventComponentId(EventComponent::SCHEDULES, $eventScheduleId);
			self::validateEventComponentId(EventComponent::TARGET_GROUPS, $eventTargetGroupId);
			self::validateEventComponentId(EventComponent::PRICES, $eventPriceId);
			self::validateEventComponentId(EventComponent::PACKING_LISTS, $eventPackingListId);
			self::validateEventComponentId(EventComponent::LOCATIONS, $eventLocationId);
			self::validateEventComponentId(EventComponent::ARRIVALS, $eventArrivalId);
			self::validateRequiredAccessLevel($requiredAccessLevel, $ownAccessLevel);
			
			$stmt = DatabaseModule::getInstance()->prepare(
				"INSERT INTO events(eventTitle, eventTopic, eventDetails, eventStart, eventEnd, eventEnrollmentStart, eventEnrollmentEnd, 
					eventOfferId, eventScheduleId, eventTargetGroupId, eventPriceId, eventPackingListId, eventLocationId, eventArrivalId, requiredAccessLevel) 
				VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
			);
			$stmt->bind_param("sssssssiiiiiiii", $eventTitle, $eventTopic, $eventDetails, $eventStart, $eventEnd, $eventEnrollmentStart, $eventEnrollmentEnd, 
				$eventOfferId, $eventScheduleId, $eventTargetGroupId, $eventPriceId, $eventPackingListId, $eventLocationId, $eventArrivalId, $requiredAccessLevel);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventComponentId($eventId, $eventComponentType, $eventComponentId, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventComponentId($eventComponentType, $eventComponentId);

			$eventComponent = new EventComponent($eventComponentType);
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET {$eventComponent->getIdColumn()}=? WHERE eventId=?"
			);
			$stmt->bind_param("ii", $eventComponentId, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventTitle($eventId, $eventTitle, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventTitle($eventTitle);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventTitle=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventTitle, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventTopic($eventId, $eventTopic, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventTopic($eventTopic);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventTopic=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventTopic, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventDetails($eventId, $eventDetails, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventDetails($eventDetails);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventDetails=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventDetails, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventStart($eventId, $eventStart, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventStart($eventStart, null, $eventId);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventStart=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventStart, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventEnd($eventId, $eventEnd, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnd($eventEnd, null, $eventId);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventEnd=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventEnd, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventEnrollmentStart($eventId, $eventEnrollmentStart, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnrollmentStart($eventEnrollmentStart, null, $eventId);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventEnrollmentStart=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventEnrollmentStart, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventEnrollmentEnd($eventId, $eventEnrollmentEnd, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnrollmentEnd($eventEnrollmentEnd, null, $eventId);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET eventEnrollmentEnd=? WHERE eventId=?"
			);
			$stmt->bind_param("si", $eventEnrollmentEnd, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateRequiredAccessLevel($eventId, $requiredAccessLevel, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateRequiredAccessLevel($requiredAccessLevel, $ownAccessLevel);

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE events SET requiredAccessLevel=? WHERE eventId=?"
			);
			$stmt->bind_param("ii", $requiredAccessLevel, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function enroll($userId, $eventId, $eventEnrollmentComment, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnrollmentUserRequirements($userId, $ownAccessLevel);
			self::validateEventEnrollmentEventRequirements($eventId, $ownAccessLevel);

			if (self::isUserEnrolled($userId, $eventId) === true) {
				throw new Exception("event_user_enrolled_already");
			}

			$eventEnrollmentComment = $eventEnrollmentComment === "" ? null : $eventEnrollmentComment;
			$stmt = DatabaseModule::getInstance()->prepare(
				"INSERT INTO event_enrollments(userId, eventId, eventEnrollmentComment)
				 VALUES(?, ?, ?)"
			);
			$stmt->bind_param("iis", $userId, $eventId, $eventEnrollmentComment);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}

			MailModule::sendEventEnrollmentNotificationMail($eventId, $userId);
			AlexaNotificationModule::sendEventEnrollmentNotification($eventId, $userId);
		}

		public static function updateEventEnrollmentComment($userId, $eventId, $eventEnrollmentComment, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnrollmentComment($eventEnrollmentComment);

			if (self::isUserEnrolled($userId, $eventId) === false) {
				throw new Exception("event_user_enrolled_not");
			}

			$eventEnrollmentComment = $eventEnrollmentComment === "" ? null : $eventEnrollmentComment;
			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE event_enrollments SET eventEnrollmentComment=? WHERE userId=? AND eventId=?"
			);
			$stmt->bind_param("sii", $eventEnrollmentComment, $userId, $eventId);
			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function disenroll($userId, $eventId, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventDisenrollmentEventRequirements($eventId, $ownAccessLevel);
			
			if (self::isUserEnrolled($userId, $eventId) === false) {
				throw new Exception("event_user_enrolled_not");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"DELETE FROM event_enrollments WHERE userId=? AND eventId=?"
			);
			$stmt->bind_param("ii", $userId, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}

			MailModule::sendEventDisenrollmentNotificationMail($eventId, $userId);
			AlexaNotificationModule::sendEventDisenrollmentNotification($eventId, $userId);
		}

		public static function checkIn($userId, $eventId, $eventEnrollmentPublicMediaUsageConsent, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnrollmentUserRequirements($userId, $ownAccessLevel);
			self::validateEventEnrollmentEventRequirements($eventId, $ownAccessLevel);
			self::validateEventEnrollmentPublicMediaUsageConsent($eventEnrollmentPublicMediaUsageConsent);

			if (self::isUserEnrolled($userId, $eventId) === false) {
				throw new Exception("event_user_enrolled_not");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE event_enrollments
				 SET checkInDate=NOW(), eventEnrollmentPublicMediaUsageConsent=?
				 WHERE userId=? AND eventId=?"
			);
			$stmt->bind_param("iii", $eventEnrollmentPublicMediaUsageConsent, $userId, $eventId);

			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function updateEventEnrollmentPublicMediaUsageConsent($userId, $eventId, $eventEnrollmentPublicMediaUsageConsent, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);
			self::validateEventEnrollmentPublicMediaUsageConsent($eventEnrollmentPublicMediaUsageConsent);

			if (self::isUserEnrolled($userId, $eventId) === false) {
				throw new Exception("event_user_enrolled_not");
			}

			$stmt = DatabaseModule::getInstance()->prepare(
				"UPDATE event_enrollments
				 SET eventEnrollmentPublicMediaUsageConsent=?
				 WHERE userId=? AND eventId=?"
			);
			$stmt->bind_param("iii", $eventEnrollmentPublicMediaUsageConsent, $userId, $eventId);
			$result = $stmt->execute();
			$stmt->close();

			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}

		public static function deleteEvent($eventId, $ownAccessLevel) {
			self::validateEventAccess($eventId, $ownAccessLevel);

			$stmt = DatabaseModule::getInstance()->prepare(
				"DELETE FROM events WHERE eventId=?"
			);
			$stmt->bind_param("i", $eventId);

			$result = $stmt->execute();
			$stmt->close();
			
			if ($result === false) {
				throw new Exception("error_message_try_later");
			}
		}


		/* validation */

		/* event components */ 

		private static function validateEventComponentId($eventComponentType, $eventComponentId) {
			$eventComponent = new EventComponent($eventComponentType);

			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT 1 FROM {$eventComponent->getTable()} WHERE {$eventComponent->getIdColumn()}=?"
			);
			$stmt->bind_param("i", $eventComponentId);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$stmt->store_result();
			$cnt = $stmt->num_rows;
			$stmt->close();

			if ($cnt === 0) {
				throw new Exception("event_{$eventComponent->getIdColumn()}_invalid");
			}
		}

		private static function validateEventComponentTitle($eventComponentType, $eventComponentTitle) {
			if (empty($eventComponentTitle) === true 
					|| strlen($eventComponentTitle) > EVENT_COMPONENT_TITLE_LENGTH_MAX) {
				throw new Exception("event_component_title_invalid");
			}

			$eventComponent = new EventComponent($eventComponentType);
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT 1 FROM {$eventComponent->getTable()} WHERE {$eventComponent->getTitleColumn()}=?"
			);
			$stmt->bind_param("s", $eventComponentTitle);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$stmt->store_result();
			$cnt = $stmt->num_rows;
			$stmt->close();

			if ($cnt > 0) {
				throw new Exception("event_component_title_taken_already");
			}
		}

		private static function validateEventComponentContent($eventComponentContent) {
			if (empty($eventComponentContent) === true
					|| strlen($eventComponentContent) > EVENT_COMPONENT_CONTENT_LENGTH_MAX) {
				throw new Exception("event_component_content_invalid");
			}
		}

		private static function validateEventLocationLatitude($eventLocationLatitude) {
			if (is_numeric($eventLocationLatitude) === false || floatval($eventLocationLatitude) > 90 || floatval($eventLocationLatitude) < -90) {
				throw new Exception("event_eventLocationLatitude_invalid");
			}
		}

		private static function validateEventLocationLongitude($eventLocationLongitude) {
			if (is_numeric($eventLocationLongitude) === false || floatval($eventLocationLongitude) > 180 || floatval($eventLocationLongitude) < -180) {
				throw new Exception("event_eventLocationLongitude_invalid");
			}
		}

		/* events */

		private static function validateEventAccess($eventId, $ownAccessLevel) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT requiredAccessLevel FROM events WHERE eventId=?"
			);
			$stmt->bind_param("i", $eventId);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$requiredAccessLevel = $stmt->get_result()->fetch_assoc()["requiredAccessLevel"];
			$stmt->close();

			if ($requiredAccessLevel > $ownAccessLevel) {
				throw new Exception("error_message_NEP");
			}
		}

		private static function validateEventTitle($eventTitle) {
			if (empty($eventTitle) === true || strlen($eventTitle) > EVENT_TITLE_LENGTH_MAX) {
				throw new Exception("event_eventTitle_invalid");
			}
		}

		private static function validateEventTopic($eventTopic) {
			if (empty($eventTopic) === true || strlen($eventTopic) > EVENT_TOPIC_LENGTH_MAX) {
				throw new Exception("event_eventTopic_invalid");
			}
		}

		private static function validateEventDetails($eventDetails) {
			if (empty($eventDetails) === true || strlen($eventDetails) > EVENT_DETAILS_LENGTH_MAX) {
				throw new Exception("event_eventDetails_invalid");
			}
		}

		private static function validateEventStart($eventStart, $eventEnd = null, $eventId = null) {
			$eventStart = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, $eventStart, new DateTimeZone(SERVER_TIMEZONE));
			$eventEnd = DateTime::createFromFormat(
				DATE_FORMAT_DB_FULL,
				$eventEnd === null ? self::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER)["eventEnd"] : $eventEnd,
				new DateTimeZone(SERVER_TIMEZONE)
			);

			self::validateEventStartEnd($eventStart, $eventEnd);
		}

		private static function validateEventEnd($eventEnd, $eventStart = null, $eventId = null) {
			$eventEnd = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, $eventEnd, new DateTimeZone(SERVER_TIMEZONE));
			$eventStart = DateTime::createFromFormat(
				DATE_FORMAT_DB_FULL,
				$eventStart === null ? self::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER)["eventStart"] : $eventStart,
				new DateTimeZone(SERVER_TIMEZONE)
			);

			self::validateEventStartEnd($eventStart, $eventEnd);
		}

		private static function validateEventStartEnd($eventStart, $eventEnd) {
			if ($eventStart === null || $eventStart === false) {
				throw new Exception("event_eventStart_malformed");
			} else if ($eventEnd === null || $eventEnd === false) {
				throw new Exception("event_eventEnd_malformed");
			} else if ($eventStart >= $eventEnd) {
				throw new Exception("event_eventStartEnd_invalid");
			}
		}

		private static function validateEventEnrollmentStart($eventEnrollmentStart, $eventEnrollmentEnd = null, $eventId = null) {
			$eventEnrollmentStart = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, $eventEnrollmentStart, new DateTimeZone(SERVER_TIMEZONE));
			$eventEnrollmentEnd = DateTime::createFromFormat(
				DATE_FORMAT_DB_FULL,
				$eventEnrollmentEnd === null ? self::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER)["eventEnrollmentEnd"] : $eventEnrollmentEnd,
				new DateTimeZone(SERVER_TIMEZONE)
			);

			self::validateEventEnrollmentStartEnd($eventEnrollmentStart, $eventEnrollmentEnd);
		}

		private static function validateEventEnrollmentEnd($eventEnrollmentEnd, $eventEnrollmentStart = null, $eventId = null) {
			$eventEnrollmentEnd = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, $eventEnrollmentEnd, new DateTimeZone(SERVER_TIMEZONE));
			$eventEnrollmentStart = DateTime::createFromFormat(
				DATE_FORMAT_DB_FULL,
				$eventEnrollmentStart === null ? self::loadEvent($eventId, ACCESS_LEVEL_DEVELOPER)["eventEnrollmentStart"] : $eventEnrollmentStart,
				new DateTimeZone(SERVER_TIMEZONE)
			);

			self::validateEventEnrollmentStartEnd($eventEnrollmentStart, $eventEnrollmentEnd);
		}

		private static function validateEventEnrollmentStartEnd($eventEnrollmentStart, $eventEnrollmentEnd) {
			if ($eventEnrollmentStart === null || $eventEnrollmentStart === false) {
				throw new Exception("event_eventEnrollmentStart_malformed");
			} else if ($eventEnrollmentEnd === null || $eventEnrollmentEnd === false) {
				throw new Exception("event_eventEnrollmentEnd_malformed");
			} else if ($eventEnrollmentStart >= $eventEnrollmentEnd) {
				throw new Exception("event_eventEnrollmentStartEnd_invalid");
			}
		}

		private static function validateRequiredAccessLevel($requiredAccessLevel, $ownAccessLevel) {
			$accessLevels = UserModule::loadAccessLevels($ownAccessLevel);

			$exists = false;
			foreach ($accessLevels as $accessLevel) {
				if ($accessLevel["accessLevel"] == $requiredAccessLevel) {
					$exists = true;
				}
			}
			if ($exists === false) {
				throw new Exception("event_requiredAccessLevel_not_exist");
			}
		}

		private static function validateEventEnrollmentUserRequirements($userId, $ownAccessLevel) {
			$user = UserModule::loadUser($userId, $ownAccessLevel);
			
			if ($user === null 
				|| isset($user["firstName"]) === false 
				|| isset($user["lastName"]) === false
				|| isset($user["eMailAddress"]) === false
				|| isset($user["streetName"]) === false
				|| isset($user["houseNumber"]) === false
				|| isset($user["zipCode"]) === false
				|| isset($user["city"]) === false
				|| isset($user["country"]) === false
				|| isset($user["birthdate"]) === false) {
					throw new Exception("event_user_enrollment_missing_account_data");
			}
		}

		private static function validateEventEnrollmentEventRequirements($eventId, $ownAccessLevel) {
			$event = self::loadEvent($eventId, $ownAccessLevel);
			$now = new DateTime();
			$eventEnrollmentStart = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, 
				$event["eventEnrollmentStart"], new DateTimeZone(SERVER_TIMEZONE));
			$eventEnrollmentEnd = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, 
				$event["eventEnrollmentEnd"], new DateTimeZone(SERVER_TIMEZONE));

			if ($now < $eventEnrollmentStart) {
				throw new Exception("event_user_enrollment_too_early");
			}

			if ($eventEnrollmentEnd < $now) {
				throw new Exception("event_user_enrollment_too_late");
			}
		}

		private static function validateEventDisenrollmentEventRequirements($eventId, $ownAccessLevel) {
			$event = self::loadEvent($eventId, $ownAccessLevel);
			$now = new DateTime();
			$eventEnrollmentStart = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, 
				$event["eventEnrollmentStart"], new DateTimeZone(SERVER_TIMEZONE));
			$eventEnrollmentEnd = DateTime::createFromFormat(DATE_FORMAT_DB_FULL, 
				$event["eventEnrollmentEnd"], new DateTimeZone(SERVER_TIMEZONE));

			if ($now < $eventEnrollmentStart) {
				throw new Exception("event_user_disenrollment_too_early");
			}

			if ($eventEnrollmentEnd < $now) {
				throw new Exception("event_user_disenrollment_too_late");
			}
		}

		private static function validateEventEnrollmentComment($eventEnrollmentComment) {
			if (strlen($eventEnrollmentComment) > EVENT_ENROLLMENT_COMMENT_LENGTH_MAX) {
				throw new Exception("event_eventEnrollmentComment_invalid");
			}
		}

		private static function validateEventEnrollmentPublicMediaUsageConsent($eventEnrollmentPublicMediaUsageConsent) {
			try {
				if (intval($eventEnrollmentPublicMediaUsageConsent) !== 1 && intval($eventEnrollmentPublicMediaUsageConsent) !== 0) {
					throw new Exception("stub");
				}
			} catch (Exception $exc) {
				throw new Exception("event_eventEnrollmentPublicMediaUsageConsent_invalid");
			}
		}

		private static function isUserEnrolled($userId, $eventId) {
			$stmt = DatabaseModule::getInstance()->prepare(
				"SELECT 1 FROM event_enrollments WHERE userId=? AND eventId=?"
			);
			$stmt->bind_param("ii", $userId, $eventId);

			if ($stmt->execute() === false) {
				$stmt->close();
				throw new Exception("error_message_try_later");
			}

			$stmt->store_result();
			$cnt = $stmt->num_rows;
			$stmt->close();

			return $cnt > 0;
		}
		
	}
?>