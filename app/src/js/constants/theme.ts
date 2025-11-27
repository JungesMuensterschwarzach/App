import { InputBaseComponentProps } from '@material-ui/core/InputBase';
import { createTheme, Theme } from '@material-ui/core/styles';
import React from 'react';
import { IUserKeys, IUserValues } from '../networking/account_data/IUser';

/* general */

export enum ThemeTypes {
    DARK, // default, if not set
    LIGHT
}


/* colors */

export enum CustomTheme {
    COLOR_BLACK = "#000000",
    COLOR_BODY_INNER = "#fdfdfd",
    COLOR_FAILURE = "#ff0000",
    COLOR_LEGAL_NOTICE_EFFECTIVE_DATE = "#333333",
    COLOR_LINK = "#2878ff",
    COLOR_MAP_MARKER = "#ff0000",
    COLOR_PAGINATION_BUTTONS_BACKGROUND = "#e6e6e6",
    COLOR_PAGINATION_BUTTONS_BORDER = "#888888",
    COLOR_PAGINATION_BUTTONS_BORDER_ACTIVE = "#444444",
    COLOR_PRIMARY = "#ff9800",
    COLOR_REQUIRED_MARKER_ACCOUNT = "#ff0000",
    COLOR_REQUIRED_MARKER_EVENT = "#08af08",
    COLOR_SECONDARY = "#ffbc36",
    COLOR_SUCCESS = "#00ff00",
    COLOR_WHITE = "#ffffff",
    COLOR_ACCESS_LEVEL_DEVELOPER = "#007bff",
    COLOR_ACCESS_LEVEL_COURSE_LEADER = "#dc3545",
    COLOR_ACCESS_LEVEL_COURSE_INSTRUCTOR = "#ffc107",
    COLOR_ACCESS_LEVEL_EDITOR = "#28a745",
    COLOR_ACCESS_LEVEL_USER = "#343a40"
}


/* main theme */

export default function getTheme(): Theme {
    return theme;
}

const theme: Theme = createTheme({
    overrides: {
        MuiButton: {
            containedPrimary: {
                '&.Mui-disabled': {
                    color: "rgba(0, 0, 0, 0.75)",
                    backgroundColor: "rgba(255, 152, 0, 0.50)"
                },
            }
        },
        MuiCardContent: {
            root: {
                "&:last-child": {
                    paddingBottom: "16px"
                }
            }
        },
        MuiCardHeader: {
            root: {
                backgroundColor: "rgba(225, 225, 225, 1)"
            }
        },
        MuiDialogContentText: {
            root: {
                whiteSpace: "pre-wrap"
            }
        },
        MuiExpansionPanelSummary: {
            root: {
                backgroundColor: "rgba(225, 225, 225, 1)"
            }
        },
        MuiMenuItem: {
            root: {
                "&:hover:not(.jma-no-background-color)": {
                    backgroundColor: "rgba(255, 255, 255, 0.65)"
                },
                "&:not(.jma-no-background-color)": {
                    backgroundColor: "rgba(255, 255, 255, 0.5)"
                }
            }
        },
        MuiSnackbarContent: {
            root: {
                backgroundColor: "#ff7500",
                color: "#000000"
            }
        },
        MuiTooltip: {
            tooltip: {
                fontSize: "1.2rem"
            }
        },
        MuiTypography: {
            root: {
                whiteSpace: "pre-wrap"
            }
        }
    },
    palette: {
        primary: {
            contrastText: "#000000",
            main: CustomTheme.COLOR_PRIMARY
        },
        secondary: {
            contrastText: "#000000",
            main: CustomTheme.COLOR_SECONDARY
        }
    }
});

/* text field theme */

export function getTextFieldTheme(style: ThemeTypes | undefined): Theme {
    if (style === ThemeTypes.LIGHT) {
        return textFieldLightTheme;
    } else {
        return getTheme();
    }
}

const textFieldLightTheme: Theme = createTheme({
    overrides: {
        MuiFormLabel: {
            root: {
                // adding fake-Ids increases the selectors specificy and overwrites the default of mui
                "&:not(#fake)": {
                    color: "rgba(255, 255, 255, 0.8)"
                }
            }
        },
        MuiOutlinedInput: {
            input: {
                color: "rgba(255, 255, 255, 1)"
            },
            notchedOutline: {
                "&:not(#fake)": {
                    borderColor: "rgba(255, 255, 255, 1)"
                }
            }
        }
    }
});

/* checkbox theme */

export function getCheckboxTheme(style: ThemeTypes | undefined): Theme {
    if (style === ThemeTypes.LIGHT) {
        return checkBoxLightTheme;
    } else {
        return getTheme();
    }
}

const checkBoxLightTheme: Theme = createTheme({
    overrides: {
        MuiCheckbox: {
            root: {
                "&:not(#fake)": {
                    color: "rgba(255, 255, 255, 1)"
                }
            }
        },
        MuiTypography: {
            root: {
                // adding fake-Ids increases the selectors specificy and overwrites the default of mui
                "&:not(#fake)": {
                    color: "rgba(255, 255, 255, 0.8)"
                }
            }
        }
    }
});

export const textFieldInputProps: InputBaseComponentProps = {
    style: {
    }
}

/* typography styles */

export const infoMessageTypographyStyle: React.CSSProperties = {
    color: "#ffffff",
    display: "inline-block",
    textAlign: "center"
};

export const successMsgTypographyStyle: React.CSSProperties = {
    color: "#ffffff",
    display: "inline-block",
    textAlign: "center"
};

export const linkMsgTypographyStyle: React.CSSProperties = {
    color: CustomTheme.COLOR_LINK,
    cursor: "pointer"
}

/* marker styles */

export const markerRequirementsLeftMarginStyle: React.CSSProperties = {
    marginLeft: "1em"
}

export const markerRequirementsSeparatorMarginStyle: React.CSSProperties = {
    marginLeft: "0.2em"
}

export const markerAccountRequirementsStyle: React.CSSProperties = {
    color: CustomTheme.COLOR_REQUIRED_MARKER_ACCOUNT
}

export const markerEventRequirementsStyle: React.CSSProperties = {
    color: CustomTheme.COLOR_REQUIRED_MARKER_EVENT
}

/* logo item styles */

export const logoItemStyle: React.CSSProperties = {
    cursor: "pointer",
    height: "auto",
    width: "100%"
}

/* avatar item styles */

export const avatarListItemStyle: React.CSSProperties = {
    height: theme.spacing(4),
    width: theme.spacing(4)
}

/* access level badges */

export function getAccessLevelBadgeStyle(accessLevel: number): React.CSSProperties {
    let hexColor = CustomTheme.COLOR_WHITE;

    switch (accessLevel) {
        case IUserValues[IUserKeys.accessLevel].developer:
            hexColor = CustomTheme.COLOR_ACCESS_LEVEL_DEVELOPER;
            break;
        case IUserValues[IUserKeys.accessLevel].course_leader:
            hexColor = CustomTheme.COLOR_ACCESS_LEVEL_COURSE_LEADER;
            break;
        case IUserValues[IUserKeys.accessLevel].course_instructor:
            hexColor = CustomTheme.COLOR_ACCESS_LEVEL_COURSE_INSTRUCTOR;
            break;
        case IUserValues[IUserKeys.accessLevel].editor:
            hexColor = CustomTheme.COLOR_ACCESS_LEVEL_EDITOR;
            break;
        case IUserValues[IUserKeys.accessLevel].user:
            hexColor = CustomTheme.COLOR_ACCESS_LEVEL_USER;
            break;
    }

    return {
        color: getContrastTextColor(hexToRgb(hexColor)),
        backgroundColor: hexColor,
        borderRadius: "8px"
    }
}

/* grid styles */

export const gridHorizontalStyle: React.CSSProperties = {
    flexDirection: "row"
}

export const grid1Style: React.CSSProperties = {
    flex: 1
}

export const grid2Style: React.CSSProperties = {
    flex: 2
}

export const grid5Style: React.CSSProperties = {
    flex: 5
}

export const grid6Style: React.CSSProperties = {
    flex: 6
};

export const grid7Style: React.CSSProperties = {
    flex: 7
};

export const grid10Style: React.CSSProperties = {
    flex: 10
};

/* utility functions */

function hexToRgb(hex: string): RGB|null {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    return result
        ? {
              r: parseInt(result[1], 16),
              g: parseInt(result[2], 16),
              b: parseInt(result[3], 16)
          }
        : null
}

function getContrastTextColor(rgb: RGB|null): string {
    return !rgb || (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000 > 125 ?
        CustomTheme.COLOR_BLACK : CustomTheme.COLOR_WHITE;

}

interface RGB {
    r: number;
    g: number;
    b: number;
}