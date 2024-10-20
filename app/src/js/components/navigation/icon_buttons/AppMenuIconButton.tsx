import * as React from "react";

import { IconButton, Tooltip } from "@material-ui/core";
import { MoreVert } from "@material-ui/icons";

import { Dict } from "../../../constants/dict";

interface IAppMenuIconButtonProps {
    toggleAppMenuVisibility: () => void;
}

const AppMenuIconButton = (props: IAppMenuIconButtonProps) => {
    const { toggleAppMenuVisibility } = props;

    return (
        <Tooltip title={Dict.navigation_more}>
            <IconButton
                className="jma-app-menu-anchor"
                onClick={toggleAppMenuVisibility}
            >
                <MoreVert />
            </IconButton>
        </Tooltip>
    );
};

export default AppMenuIconButton;
