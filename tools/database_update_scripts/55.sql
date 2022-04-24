ALTER TABLE `event_enrollments` ADD `checkInDate` TIMESTAMP NULL DEFAULT NULL AFTER `enrollmentDate`, ADD `publicMediaUsageConsent` BOOLEAN NULL DEFAULT NULL AFTER `checkInDate`;
UPDATE `strings` SET `de` = 'Lucas Kinne\r\nElsa-Brandström-Straße 14\r\n97218 Gerbrunn\r\nE-Mail: lucas.kinne@live.de' WHERE `strings`.`identifier` = 'legal_notice_responsible_technics_paragraph';
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('event_user_checked_in', 'You have checked in for the event successfully.', 'Du hast dich erfolgreich zu der Veranstaltung eingecheckt.');
ALTER TABLE `event_enrollments` CHANGE `publicMediaUsageConsent` `eventEnrollmentPublicMediaUsageConsent` TINYINT(1) NULL DEFAULT NULL;
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('event_eventEnrollmentPublicMediaUsageConsent_updated', 'The event enrollment public media usage consent got updated successfully.', 'Die Zustimmung zur öffentlichen Verwendung von Medien wurde erfolgreich aktualisiert.');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('event_eventEnrollmentPublicMediaUsageConsent_invalid', 'The consent of public media usage must be a binary value (yes/no)!', 'Die Einwilligung von öffentlicher Mediennutzung muss ein binärer Wert sein (ja/nein)!');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('event_eventEnrollmentPublicMediaUsageConsent', 'Event enrollment public media usage consent', 'Einwilligung zur öffentlichen Mediennutzung');
UPDATE `strings` SET `de` = 'Uns erlauben, während der Veranstaltung \r\nangefertigte Medien (z.B. Foto- und Videoaufnahmen), die dich beinhalten, für die Öffentlichkeitsarbeit (z.B. durch Teilen auf den Social Media Kanälen) verwenden zu dürfen.' WHERE `strings`.`identifier` = 'event_eventEnrollmentPublicMediaUsageConsent';
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('event_checkIn', 'Check-In', 'Einchecken');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('event_user_enrollment_state_checked_in', 'checked in', 'eingecheckt');
UPDATE `strings` SET `de` = 'Die Genehmigung zur öffentlichen Verwendung von Medien wurde erfolgreich aktualisiert.' WHERE `strings`.`identifier` = 'event_eventEnrollmentPublicMediaUsageConsent_updated';