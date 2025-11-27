import * as React from "react";

import { MuiThemeProvider, Checkbox, Typography } from "@material-ui/core";

import { Dict } from "../../constants/dict";
import { getCheckboxTheme, grid1Style, ThemeTypes } from "../../constants/theme";
import ErrorMessageTypography from "./ErrorMessageTypography";
import { IEventEnrollmentKeys } from "../../networking/events/IEventEnrollment";

interface IPublicMediaUsageConsentCheckboxProps {
    checked: boolean;
    errorMessage: string | null;
    onBlur?: (
        key: IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent,
        value: number
    ) => void;
    onUpdateValue: (
        key: IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent,
        value: number
    ) => void;
    style?: React.CSSProperties;
    themeType?: ThemeTypes;
}

const PublicMediaUsageConsentCheckbox = (
    props: IPublicMediaUsageConsentCheckboxProps
) => {
    const { checked, errorMessage, onBlur, onUpdateValue, style, themeType } = props;
    const [isBlurScheduled, setIsBlurScheduled] =
        React.useState<boolean>(false);

    const contentDivStyle: React.CSSProperties = React.useMemo(
        () => ({
            ...style,
            alignItems: "center",
            display: "flex",
        }),
        [style]
    );

    const onChange = React.useCallback(
        (event: React.ChangeEvent<HTMLInputElement>): void => {
            onUpdateValue(
                IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent,
                (event.currentTarget as HTMLInputElement).checked ? 1 : 0
            );
            if (onBlur) {
                setIsBlurScheduled(true);
            }
        },
        [onBlur, onUpdateValue]
    );

    React.useEffect(() => {
        if (isBlurScheduled) {
            if (onBlur) {
                onBlur(IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent, checked ? 1 : 0);
            }
            setIsBlurScheduled(false);
        }
    }, [checked, isBlurScheduled, onBlur]);

    return (
        <MuiThemeProvider theme={getCheckboxTheme(themeType)}>
            <div style={contentDivStyle}>
                <Checkbox
                    checked={checked}
                    color="primary"
                    name={IEventEnrollmentKeys.eventEnrollmentPublicMediaUsageConsent}
                    onChange={onChange}
                />
                <div style={grid1Style} />
                <Typography>
                    {Dict.event_eventEnrollmentPublicMediaUsageConsent}
                </Typography>
            </div>
            <ErrorMessageTypography
                value={errorMessage}
            />
        </MuiThemeProvider>
    );
};

export default PublicMediaUsageConsentCheckbox;
