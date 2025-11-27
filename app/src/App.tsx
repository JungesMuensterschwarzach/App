import log from "loglevel";
import * as React from "react";
import { Routes, useNavigate } from "react-router";
import { Route } from "react-router-dom";

import HeaderNavigation from "./js/components/navigation/HeaderNavigation";
import ChangelogPage from "./js/components/pages/ChangelogPage";
import EventCheckInExpressPage from "./js/components/pages/EventCheckInExpressPage";
import EventItemPage from "./js/components/pages/EventItemPage";
import EventListPage from "./js/components/pages/EventListPage";
import HelpPage from "./js/components/pages/HelpPage";
import HomePage from "./js/components/pages/HomePage";
import InstallationDetailsPage from "./js/components/pages/InstallationDetailsPage";
import InstallationPage from "./js/components/pages/InstallationPage";
import LegalInformationPage from "./js/components/pages/LegalInformationPage";
import LoginPage from "./js/components/pages/LoginPage";
import LogoutPage from "./js/components/pages/LogoutPage";
import NewsItemPage from "./js/components/pages/NewsItemPage";
import NewsletterRedemptionPage from "./js/components/pages/NewsletterRedemptionPage";
import NewsletterSubscriptionPage from "./js/components/pages/NewsletterSubscriptionPage";
import NewsListPage from "./js/components/pages/NewsListPage";
import PageNotFound from "./js/components/pages/PageNotFound";
import ProfilePage from "./js/components/pages/ProfilePage";
import ProfilePasswordChangePage from "./js/components/pages/ProfilePasswordChangePage";
import RegistrationPage from "./js/components/pages/RegistrationPage";
import RequestAccountTransferMailPage from "./js/components/pages/RequestAccountTransferMailPage";
import RequestActivationMailPage from "./js/components/pages/RequestActivationMailPage";
import RequestPasswordResetPage from "./js/components/pages/RequestPasswordResetPage";
import TokenRedemptionPage from "./js/components/pages/TokenRedemptionPage";
import TokenRedemptionPasswordResetPage from "./js/components/pages/TokenRedemptionPasswordResetPage";
import {
    useForceUpdate,
    usePrevious,
} from "./js/components/utilities/CustomHooks";
import Notifier, { showNotification } from "./js/components/utilities/Notifier";
import { registerPushManager } from "./js/constants/global-functions";
import { LogTags } from "./js/constants/log-tags";
import { AppUrls } from "./js/constants/specific-urls";
import { IUserKeys, IUserValues } from "./js/networking/account_data/IUser";
import { AccountSessionSignOnRequest } from "./js/networking/account_session/AccountSessionSignOnRequest";
import { CookieService } from "./js/services/CookieService";

const App = () => {
    const navigate = useNavigate();
    const forceUpdate = useForceUpdate();
    const [isLoggedIn, setIsLoggedIn] = React.useState<boolean>(false);
    const wasLoggedIn = usePrevious(isLoggedIn);
    const sessionObservationSchedule = React.useRef<NodeJS.Timer>();
    const signOnRequest = React.useRef<AccountSessionSignOnRequest>();

    const SESSION_OBSERVATION_INTERVAL: number = React.useMemo(
        () => 1 * 60 * 1000,
        []
    );
    const appRouteStyle: React.CSSProperties = React.useMemo(
        () => ({
            margin: "auto",
            maxWidth: "600px",
        }),
        []
    );

    const isValidAccessLevel = React.useCallback(
        (accessLevel: any): boolean => {
            let isValid: boolean = false;

            if (accessLevel !== null) {
                Object.keys(IUserValues[IUserKeys.accessLevel]).forEach(
                    (accessLevelKey: string) => {
                        if (
                            Number(accessLevel) ===
                            IUserValues[IUserKeys.accessLevel][accessLevelKey]
                        ) {
                            isValid = true;
                        }
                    }
                );
            }

            return isValid;
        },
        []
    );
    const validateAccessLevel = React.useCallback((): void => {
        CookieService.get<number>(IUserKeys.accessLevel)
            .then((accessLevel) => {
                if (!isValidAccessLevel(accessLevel)) {
                    log.warn(
                        LogTags.AUTHENTICATION +
                            "session terminated due to invalid access level!"
                    );
                    setIsLoggedIn(false);
                } else {
                    setIsLoggedIn(true);
                }
            })
            .catch((error) => {
                log.warn(
                    LogTags.AUTHENTICATION +
                        "session terminated due to not loadable access level: " +
                        error
                );
                setIsLoggedIn(false);
            })
            .then(forceUpdate);
    }, [forceUpdate, isValidAccessLevel]);
    const stopSignOnRequest = React.useCallback((): void => {
        if (signOnRequest.current) {
            signOnRequest.current.cancel();
        }
    }, []);
    const stopSessionObservation = React.useCallback((): void => {
        stopSignOnRequest();

        if (sessionObservationSchedule.current) {
            clearInterval(sessionObservationSchedule.current);
            sessionObservationSchedule.current = null;
            log.info(LogTags.AUTHENTICATION + "Session observation stopped.");
        }
    }, [stopSignOnRequest]);
    const newSignOnRequest = React.useMemo((): AccountSessionSignOnRequest => {
        return new AccountSessionSignOnRequest(
            (response) => {
                const errorMsg = response.errorMsg;

                if (errorMsg) {
                    showNotification(errorMsg);
                    log.warn(
                        LogTags.AUTHENTICATION + "Session is not valid anymore."
                    );
                    stopSessionObservation();
                    navigate(AppUrls.LOGOUT);
                } else {
                    log.info(
                        LogTags.AUTHENTICATION +
                            "Session is valid, has been updated and extended. " +
                            "Rechecking in " +
                            SESSION_OBSERVATION_INTERVAL / (60 * 1000) +
                            " minute(s)..."
                    );
                }
            },
            (error) => {
                log.warn(
                    LogTags.AUTHENTICATION +
                        "Session validity could not be checked: " +
                        error
                );
                stopSessionObservation();
                navigate(AppUrls.LOGOUT);
            }
        );
    }, [SESSION_OBSERVATION_INTERVAL, navigate, stopSessionObservation]);
    const runSessionObservation = React.useCallback((): void => {
        if (signOnRequest.current) {
            signOnRequest.current.cancel();
        }

        signOnRequest.current = newSignOnRequest;
        signOnRequest.current.execute();
    }, [newSignOnRequest]);
    const startSessionObservation = React.useCallback((): void => {
        stopSessionObservation();

        if (!isLoggedIn) {
            return;
        }

        CookieService.get<number>(IUserKeys.userId)
            .then((userId) => {
                if (userId === null) {
                    return;
                }

                sessionObservationSchedule.current = setInterval(
                    runSessionObservation,
                    SESSION_OBSERVATION_INTERVAL
                );
                runSessionObservation();
                log.info(
                    LogTags.AUTHENTICATION + "Session observation started."
                );
            })
            .catch(() => {
                log.warn(
                    LogTags.STORAGE_MANAGER + "userId could not be loaded."
                );
            });
    }, [
        SESSION_OBSERVATION_INTERVAL,
        isLoggedIn,
        runSessionObservation,
        stopSessionObservation,
    ]);

    React.useEffect(() => {
        validateAccessLevel();
        startSessionObservation();

        return () => {
            stopSessionObservation();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    React.useEffect(() => {
        if (!wasLoggedIn && isLoggedIn) {
            startSessionObservation();
        } else if (wasLoggedIn && !isLoggedIn) {
            stopSessionObservation();
        }

        if (signOnRequest.current) {
            signOnRequest.current.execute().then(registerPushManager);
        }
    }, [
        isLoggedIn,
        startSessionObservation,
        stopSessionObservation,
        wasLoggedIn,
    ]);

    return (
        <div id="app" style={appRouteStyle}>
            <HeaderNavigation isLoggedIn={isLoggedIn} />
            <div>
                <Routes>
                    <Route
                        path={AppUrls.CHANGELOG}
                        element={<ChangelogPage />}
                    />
                    <Route
                        path={AppUrls.EVENTS_CHECKIN_EXPRESS}
                        element={<EventCheckInExpressPage />}
                    />
                    <Route
                        path={AppUrls.EVENTS_LIST}
                        element={<EventListPage />}
                    />
                    <Route
                        path={AppUrls.EVENTS_ITEM}
                        element={
                            <EventItemPage
                                isLoggedIn={isLoggedIn}
                            />
                        }
                    />
                    <Route
                        path={AppUrls.EVENTS_ITEM_TAB}
                        element={
                            <EventItemPage
                                isLoggedIn={isLoggedIn}
                            />
                        }
                    />
                    <Route
                        path={AppUrls.HELP_NEWSLETTER_REDEEM}
                        element={<NewsletterRedemptionPage />}
                    />
                    <Route
                        path={AppUrls.HELP_NEWSLETTER_SUBSCRIBE}
                        element={<NewsletterSubscriptionPage />}
                    />
                    <Route
                        path={AppUrls.HELP_REDEEM_TOKEN_RESET_PASSWORD}
                        element={<TokenRedemptionPasswordResetPage />}
                    />
                    <Route
                        path={AppUrls.HELP_REDEEM_TOKEN}
                        element={<TokenRedemptionPage />}
                    />
                    <Route
                        path={AppUrls.HELP_REQUEST_ACCOUNT_TRANSFER_MAIL}
                        element={<RequestAccountTransferMailPage />}
                    />
                    <Route
                        path={AppUrls.HELP_REQUEST_ACTIVATION_MAIL}
                        element={<RequestActivationMailPage />}
                    />
                    <Route
                        path={AppUrls.HELP_RESET_PASSWORD}
                        element={<RequestPasswordResetPage />}
                    />
                    <Route path={AppUrls.HELP} element={<HelpPage />} />
                    <Route
                        path={AppUrls.INSTALLATION}
                        element={<InstallationPage />}
                    />
                    <Route
                        path={AppUrls.INSTALLATION_DETAILS}
                        element={<InstallationDetailsPage />}
                    />
                    <Route
                        path={AppUrls.LEGAL_INFORMATION}
                        element={<LegalInformationPage />}
                    />
                    <Route
                        path={AppUrls.LOGIN}
                        element={<LoginPage setIsLoggedIn={setIsLoggedIn} />}
                    />
                    <Route
                        path={AppUrls.LOGOUT}
                        element={<LogoutPage setIsLoggedIn={setIsLoggedIn} />}
                    />
                    <Route
                        path={AppUrls.NEWS_LIST}
                        element={<NewsListPage />}
                    />
                    <Route
                        path={AppUrls.NEWS_ITEM}
                        element={<NewsItemPage />}
                    />
                    <Route
                        path={AppUrls.PROFILE_CHANGE_PASSWORD}
                        element={<ProfilePasswordChangePage />}
                    />
                    <Route
                        path={AppUrls.PROFILE_ENROLLMENT_DATA}
                        element={<ProfilePage />} />
                    <Route path={AppUrls.PROFILE} element={<ProfilePage />} />
                    <Route
                        path={AppUrls.REGISTER}
                        element={<RegistrationPage />}
                    />
                    <Route path={AppUrls.HOME} element={<HomePage />} />
                    <Route path={AppUrls.INDEX} element={<HomePage />} />
                    <Route path="*" element={<PageNotFound />} />
                </Routes>
            </div>

            <Notifier />
        </div>
    );
};

export default App;
