import * as React from "react";
import { useNavigate } from "react-router";

import {
    withTheme,
    WithTheme,
} from "@material-ui/core";

import { Dict } from "../../constants/dict";
import { AppUrls } from "../../constants/specific-urls";
import {
    ThemeTypes,
} from "../../constants/theme";
import { IUserKeys } from "../../networking/account_data/IUser";
import { CheckInExpressEventDataRequest } from "../../networking/events/CheckInExpressEventDataRequest";
import FirstNameInput, {
    FIRST_NAME_INPUT_LOCAL_ERROR_MESSAGES
} from "../form_elements/FirstNameInput";
import LastNameInput, {
    LAST_NAME_INPUT_LOCAL_ERROR_MESSAGES
} from "../form_elements/LastNameInput";
import SubmitButton from "../form_elements/SubmitButton";
import { useRequestQueue } from "../utilities/CustomHooks";
import Grid from "../utilities/Grid";
import { showNotification } from "../utilities/Notifier";
import { IEventEnrollmentKeys } from "../../networking/events/IEventEnrollment";
import { IResponse } from "../../networking/Request";
import PublicMediaUsageConsentCheckbox from "../form_elements/PublicMediaUsageConsentCheckbox";

type IEventCheckInExpressPageProps = WithTheme;

type IFormKeys =
    | IUserKeys.firstName
    | IUserKeys.lastName
    | IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent;

interface IForm {
    [IUserKeys.firstName]: string;
    [IUserKeys.lastName]: string;
    [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]: number;
}

interface IFormError {
    [IUserKeys.firstName]: string | null;
    [IUserKeys.lastName]: string | null;
    [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]: string | null;
}

const EventCheckInExpressForm = (props: IEventCheckInExpressPageProps) => {
    const { theme } = props;
    const [form, setForm] = React.useState<IForm>({
        [IUserKeys.firstName]: "",
        [IUserKeys.lastName]: "",
        [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]: 0,
    });
    const [formError, setFormError] = React.useState<IFormError>({
        [IUserKeys.firstName]: null,
        [IUserKeys.lastName]: null,
        [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]: null,
    });
    const [request, isRequestRunning] = useRequestQueue();
    const suppressErrorMsgs = React.useRef<boolean>(true);
    const navigate = useNavigate();

    const topMarginStyle: React.CSSProperties = React.useMemo(
        () => ({
            marginTop: 2 * theme.spacing(),
        }),
        [theme]
    );

    const updateForm = React.useCallback(
        (key: IFormKeys, value: string | number): void => {
            setForm((form: IForm) => ({
                ...form,
                [key]: value,
            }));
            suppressErrorMsgs.current = false;
        },
        []
    );
    const updateFormError = React.useCallback(
        (key: IFormKeys, value: string | null): void => {
            setFormError((formError: IFormError) => ({
                ...formError,
                [key]: value,
            }));
        },
        []
    );
    const validate = React.useCallback((): boolean => {
        return (
            !FIRST_NAME_INPUT_LOCAL_ERROR_MESSAGES.includes(
                formError[IUserKeys.firstName]
            ) &&
            !LAST_NAME_INPUT_LOCAL_ERROR_MESSAGES.includes(
                formError[IUserKeys.lastName]
            )
        );
    }, [formError]);
    const checkIn = React.useCallback((): void => {
        if (!validate()) {
            return;
        }

        setFormError({
            [IUserKeys.firstName]: null,
            [IUserKeys.lastName]: null,
            [IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent]: null,
        });
        request(
            new CheckInExpressEventDataRequest(
                form[IUserKeys.firstName],
                form[IUserKeys.lastName],
                form[IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent],
                (response: IResponse) => {
                    const errorMsg = response.errorMsg;
                    const successMsg = response.successMsg;

                    if (errorMsg) {
                        if (errorMsg === 'account_id_not_exists') {
                            setFormError((formError) => {
                                return {
                                    ...formError,
                                    [IUserKeys.firstName]: Dict[errorMsg] ?? errorMsg,
                                    [IUserKeys.lastName]: Dict[errorMsg] ?? errorMsg
                                };
                            });
                        } else {
                            showNotification(errorMsg);
                        }
                    } else if (successMsg) {
                        showNotification(successMsg);
                        navigate(AppUrls.HOME);
                    }
                },
                (error: any) => {
                    showNotification(Dict.error_message_timeout);
                }
            )
        );
    }, [form, navigate, request, validate]);

    return (
        <>
            <Grid>
                <FirstNameInput
                    errorMessage={formError[IUserKeys.firstName]}
                    onBlur={() => {}}
                    onError={updateFormError}
                    onUpdateValue={updateForm}
                    suppressErrorMsg={suppressErrorMsgs.current}
                    themeType={ThemeTypes.LIGHT}
                    value={form[IUserKeys.firstName]}
                />

                <LastNameInput
                    errorMessage={formError[IUserKeys.lastName]}
                    onBlur={() => {}}
                    onError={updateFormError}
                    onUpdateValue={updateForm}
                    suppressErrorMsg={suppressErrorMsgs.current}
                    themeType={ThemeTypes.LIGHT}
                    value={form[IUserKeys.lastName]}
                />

                <PublicMediaUsageConsentCheckbox
                    checked={
                        !!form[
                            IEventEnrollmentKeys
                                .eventEnrollmentPublicMediaUsageConsent
                        ]
                    }
                    errorMessage={
                        formError[
                            IEventEnrollmentKeys
                                .eventEnrollmentPublicMediaUsageConsent
                        ]
                    }
                    onBlur={() => {}}
                    onUpdateValue={updateForm}
                    themeType={ThemeTypes.LIGHT}
                />

                <SubmitButton
                    disabled={isRequestRunning}
                    label={Dict.label_submit}
                    onClick={checkIn}
                    style={topMarginStyle}
                />
            </Grid>
        </>
    );
};

export default withTheme(EventCheckInExpressForm);
