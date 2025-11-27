import * as React from "react";

import { MuiThemeProvider, TextField } from "@material-ui/core";

import { Dict } from "../../constants/dict";
import Formats from "../../constants/formats";
import { getTextFieldTheme, grid1Style, textFieldInputProps, ThemeTypes } from "../../constants/theme";
import { IUserKeys } from "../../networking/account_data/IUser";

interface ILastNameInputProps {
    errorMessage: string | null;
    onBlur: (key: IUserKeys.lastName, value: string | null) => void;
    onError: (key: IUserKeys.lastName, value: string | null) => void;
    onUpdateValue: (key: IUserKeys.lastName, value: string) => void;
    required?: boolean;
    suppressErrorMsg?: boolean;
    themeType?: ThemeTypes;
    value: string;
}

export const LAST_NAME_INPUT_LOCAL_ERROR_MESSAGES = [
    Dict.account_lastName_required,
    Dict.account_lastName_invalid,
];

const LastNameInput = (props: ILastNameInputProps) => {
    const { errorMessage, onBlur, onError, onUpdateValue, required, suppressErrorMsg, themeType, value } =
        props;

    const onChange = React.useCallback(
        (event: any): void => {
            if (errorMessage) {
                onError(IUserKeys.lastName, null);
            }
            onUpdateValue(IUserKeys.lastName, event.target.value);
        },
        [errorMessage, onError, onUpdateValue]
    );
    const onLocalBlur = React.useCallback(
        (_: any): void => {
            if (value) {
                onUpdateValue(IUserKeys.lastName, value.trim());
            }

            onBlur(IUserKeys.lastName, value);
        },
        [onBlur, onUpdateValue, value]
    );

    React.useEffect(() => {
        const valueLength = value.trim().length;

        let localErrorMessage = null;
        if (required && (!value || valueLength === 0)) {
            localErrorMessage = Dict.account_lastName_required;
        } else if (value && Formats.LENGTH.MAX.LAST_NAME < valueLength) {
            localErrorMessage = Dict.account_lastName_invalid;
        }

        // do not overwrite server side error messages
        if (
            errorMessage !== localErrorMessage &&
            (!errorMessage || LAST_NAME_INPUT_LOCAL_ERROR_MESSAGES.includes(errorMessage))
        ) {
            onError(IUserKeys.lastName, localErrorMessage);
        }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [value]);

    return (
        <MuiThemeProvider theme={getTextFieldTheme(themeType)}>
            <TextField
                error={errorMessage != null && !suppressErrorMsg}
                helperText={suppressErrorMsg ? null : errorMessage}
                inputProps={{
                    ...textFieldInputProps,
                    maxLength: Formats.LENGTH.MAX.LAST_NAME,
                }}
                label={Dict.account_lastName}
                margin="dense"
                name={IUserKeys.lastName}
                onBlur={onLocalBlur}
                onChange={onChange}
                style={grid1Style}
                type="text"
                value={value}
                variant="outlined"
            />
        </MuiThemeProvider>
    );
};

export default LastNameInput;
