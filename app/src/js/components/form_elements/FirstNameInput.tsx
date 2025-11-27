import * as React from "react";

import { MuiThemeProvider, TextField } from "@material-ui/core";

import { Dict } from "../../constants/dict";
import Formats from "../../constants/formats";
import { getTextFieldTheme, grid1Style, textFieldInputProps, ThemeTypes } from "../../constants/theme";
import { IUserKeys } from "../../networking/account_data/IUser";

interface IFirstNameInputProps {
    errorMessage: string | null;
    onBlur: (key: IUserKeys.firstName, value: string | null) => void;
    onError: (key: IUserKeys.firstName, value: string | null) => void;
    onUpdateValue: (key: IUserKeys.firstName, value: string) => void;
    required?: boolean;
    suppressErrorMsg?: boolean;
    themeType?: ThemeTypes;
    value: string;
}

export const FIRST_NAME_INPUT_LOCAL_ERROR_MESSAGES = [
    Dict.account_firstName_required,
    Dict.account_firstName_invalid,
];

const FirstNameInput = (props: IFirstNameInputProps) => {
    const { errorMessage, onBlur, onError, onUpdateValue, required, suppressErrorMsg, themeType, value } =
        props;

    const onChange = React.useCallback(
        (event: any): void => {
            if (errorMessage) {
                onError(IUserKeys.firstName, null);
            }
            onUpdateValue(IUserKeys.firstName, event.target.value);
        },
        [errorMessage, onError, onUpdateValue]
    );
    const onLocalBlur = React.useCallback(
        (_: any): void => {
            if (value) {
                onUpdateValue(IUserKeys.firstName, value.trim());
            }

            onBlur(IUserKeys.firstName, value);
        },
        [onBlur, onUpdateValue, value]
    );

    React.useEffect(() => {
        const valueLength = value.trim().length;

        let localErrorMessage = null;
        if (required && (!value || valueLength === 0)) {
            localErrorMessage = Dict.account_firstName_required;
        } else if (value && Formats.LENGTH.MAX.FIRST_NAME < valueLength) {
            localErrorMessage = Dict.account_firstName_invalid;
        }

        // do not overwrite server side error messages
        if (
            errorMessage !== localErrorMessage &&
            (!errorMessage || FIRST_NAME_INPUT_LOCAL_ERROR_MESSAGES.includes(errorMessage))
        ) {
            onError(IUserKeys.firstName, localErrorMessage);
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
                    maxLength: Formats.LENGTH.MAX.FIRST_NAME,
                }}
                label={Dict.account_firstName}
                margin="dense"
                name={IUserKeys.firstName}
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

export default FirstNameInput;
