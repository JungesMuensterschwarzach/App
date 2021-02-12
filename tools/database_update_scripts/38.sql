INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_enrollment_notification_title', 'Junges Münsterschwarzach App - Event Enrollment Notification', 'Junges Münsterschwarzach App - Veranstaltungsanmeldung');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_enrollment_notification_message_paragraph_1', ' has enrolled themselves to the event ', ' hat sich zur Veranstaltung ');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_enrollment_notification_message_paragraph_2', '.', ' angemeldet.');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_enrollment_notification_message_paragraph_3', '<br/><br/>The complete participant\'s data can be accessed in the ', '<br/><br/>Die kompletten Teilnehmerdaten sind in der ');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_enrollment_notification_message_paragraph_4', '.<br/><br/>', ' einsehbar.<br/><br/>');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_disenrollment_notification_title', 'Junges Münsterschwarzach App - Event Disenrollment Notification', 'Junges Münsterschwarzach App - Veranstaltungsabmeldung');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_disenrollment_notification_message_paragraph_1', ' has disenrolled themselves from the event ', ' hat sich von der Veranstaltung ');
INSERT INTO `strings` (`identifier`, `en`, `de`) VALUES ('mail_event_disenrollment_notification_message_paragraph_2', '.', ' abgemeldet.');
UPDATE `strings` SET `en` = '.<br/><br/>', `de` = ' abgemeldet.<br/><br/>' WHERE `strings`.`identifier` = 'mail_event_disenrollment_notification_message_paragraph_2';