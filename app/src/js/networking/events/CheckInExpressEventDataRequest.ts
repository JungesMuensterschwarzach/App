import { IUserKeys } from '../account_data/IUser';
import { IResponse } from '../Request';
import EventDataRequest, { EventDataRequestActions } from './EventDataRequest';
import { IEventEnrollmentKeys } from './IEventEnrollment';

export class CheckInExpressEventDataRequest extends EventDataRequest {

    constructor(
        firstName: string,
        lastName: string,
        eventEnrollmentPublicMediaUsageConsent: number,
        successCallback: (response: IResponse) => void,
        errorCallback: (error: string) => void) {
        super(
            {
                action: EventDataRequestActions.CHECK_IN_EXPRESS,
                [IUserKeys.firstName]: firstName,
                [IUserKeys.lastName]: lastName,
                [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]: eventEnrollmentPublicMediaUsageConsent
            },
            successCallback,
            errorCallback
        );
    }

}