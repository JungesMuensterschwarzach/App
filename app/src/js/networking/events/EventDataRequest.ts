import { WebserviceUrls } from "../../constants/specific-urls";
import { IUserKeys } from "../account_data/IUser";
import Request from "../Request";
import { IEventEnrollmentKeys } from "./IEventEnrollment";
import { IEventItemKeys } from "./IEventItem";

interface IEventDataRequestParams {
    action: EventDataRequestActions;
    [IEventItemKeys.eventId]?: number;
    [IEventEnrollmentKeys.eventEnrollmentComment]?: string;
    [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]?: number;
    [IUserKeys.firstName]?: string;
    [IUserKeys.lastName]?: string;
}

export enum EventDataRequestActions {
    CHECK_IN = "checkIn",
    CHECK_IN_EXPRESS = "checkInExpress",
    DISENROLL = "disenroll",
    ENROLL = "enroll",
    FETCH_EVENT = "fetchEvent",
    FETCH_EVENT_ENROLLMENT = "fetchEventEnrollment",
    FETCH_EVENT_LIST = "fetchEventList",
    UPDATE_EVENT_ENROLLMENT_COMMENT = "updateEventEnrollmentComment",
    UPDATE_EVENT_ENROLLMENT_PUBLIC_MEDIA_USAGE_CONSENT = "updateEventEnrollmentPublicMediaUsageConsent",
}

export default class EventDataRequest extends Request {
    constructor(
        params: IEventDataRequestParams,
        successCallback: (result: any) => void,
        errorCallback: (error: any) => void
    ) {
        super(WebserviceUrls.EVENTS, params, successCallback, errorCallback);
    }
}
