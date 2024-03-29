import * as React from "react";

import {
    Accordion,
    AccordionDetails,
    AccordionSummary,
    Card,
    CardContent,
    CardHeader,
    Typography,
    withTheme,
    WithTheme,
} from "@material-ui/core";
import { TypographyProps } from "@material-ui/core/Typography";

import { Dict } from "../../constants/dict";
import { CustomTheme } from "../../constants/theme";
import Background from "../utilities/Background";

type ILegalInformationPageProps = WithTheme;

const LegalInformationPage = (props: ILegalInformationPageProps) => {
    const { theme } = props;
    const cardSubHeaderProps: Partial<TypographyProps> = React.useMemo(
        () => ({
            style: {
                color: CustomTheme.COLOR_LEGAL_NOTICE_EFFECTIVE_DATE,
                fontWeight: "bold",
            },
        }),
        []
    );
    const h1Style: React.CSSProperties = React.useMemo(
        () => ({
            fontSize: 1.8 * theme.typography.fontSize.valueOf(),
            marginBottom: 0.8 * theme.spacing(),
        }),
        [theme]
    );
    const h2Style: React.CSSProperties = React.useMemo(
        () => ({
            fontSize: 1.6 * theme.typography.fontSize.valueOf(),
            marginBottom: 0.6 * theme.spacing(),
        }),
        [theme]
    );
    const h3Style: React.CSSProperties = React.useMemo(
        () => ({
            fontSize: 1.4 * theme.typography.fontSize.valueOf(),
            marginBottom: 0.4 * theme.spacing(),
        }),
        [theme]
    );
    const paragraphStyle: React.CSSProperties = React.useMemo(
        () => ({
            marginBottom: theme.spacing(2),
        }),
        [theme]
    );
    const boldParagraphHeadingStyle: React.CSSProperties = React.useMemo(
        () => ({
            fontWeight: "bold",
        }),
        []
    );

    return (
        <Background theme={theme}>
            <Card>
                <CardHeader
                    title={Dict.legal_notice_heading}
                    subheader={Dict.legal_notice_effective}
                    subheaderTypographyProps={cardSubHeaderProps}
                />
                <CardContent>
                    <Typography style={paragraphStyle}>
                        {Dict.legal_notice_paragraph}
                    </Typography>

                    <Typography style={boldParagraphHeadingStyle}>
                        {Dict.legal_notice_authorized_representative_heading}
                    </Typography>
                    <Typography style={paragraphStyle}>
                        {Dict.legal_notice_authorized_representative_paragraph}
                    </Typography>

                    <Typography style={boldParagraphHeadingStyle}>
                        {Dict.legal_notice_responsible_content_heading}
                    </Typography>
                    <Typography style={paragraphStyle}>
                        {Dict.legal_notice_responsible_content_paragraph}
                    </Typography>

                    <Typography style={boldParagraphHeadingStyle}>
                        {Dict.legal_notice_responsible_technics_heading}
                    </Typography>
                    <Typography style={paragraphStyle}>
                        {Dict.legal_notice_responsible_technics_paragraph}
                    </Typography>

                    <Accordion defaultExpanded={true}>
                        <AccordionSummary>
                            <Typography variant="h5">{Dict.tos}</Typography>
                        </AccordionSummary>
                        <AccordionDetails>
                            <div>
                                <Typography style={h1Style}>
                                    {Dict.tos_general}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_general_compliance_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_general_compliance_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_general_validity_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_general_validity_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_general_householders_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_general_householders_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.tos_accounts}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_accounts_membership_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_accounts_membership_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_accounts_costs_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_accounts_costs_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_accounts_security_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_accounts_security_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.tos_contents}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_contents_language_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_contents_language_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_contents_behavior_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_contents_behavior_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_contents_disturbance_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_contents_disturbance_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_contents_copyright_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_contents_copyright_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_contents_advertising_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_contents_advertising_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_contents_fraud_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_contents_fraud_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.tos_infringements}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_infringements_duty_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_infringements_duty_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_infringements_faults_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_infringements_faults_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.tos_infringements_sanctions_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.tos_infringements_sanctions_paragraph}
                                </Typography>
                            </div>
                        </AccordionDetails>
                    </Accordion>

                    <Accordion defaultExpanded={true}>
                        <AccordionSummary>
                            <Typography variant="h5">
                                {Dict.legal_disclaimer_heading}
                            </Typography>
                        </AccordionSummary>
                        <AccordionDetails>
                            <div>
                                <Typography style={paragraphStyle}>
                                    {Dict.legal_disclaimer_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.legal_disclaimer_limitation_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.legal_disclaimer_limitation_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.legal_disclaimer_references_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.legal_disclaimer_references_paragraph}
                                </Typography>
                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.legal_disclaimer_references_paragraph_2
                                    }
                                </Typography>
                            </div>
                        </AccordionDetails>
                    </Accordion>

                    <Accordion defaultExpanded={true}>
                        <AccordionSummary>
                            <Typography variant="h5">
                                {Dict.privacy_policy_heading}
                            </Typography>
                        </AccordionSummary>
                        <AccordionDetails>
                            <div>
                                <Typography style={paragraphStyle}>
                                    {Dict.privacy_policy_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {
                                        Dict.privacy_policy_data_responsibility_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_responsibility_paragraph
                                    }
                                </Typography>

                                <Typography style={h1Style}>
                                    {
                                        Dict.privacy_policy_legal_foundation_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_legal_foundation_paragraph
                                    }
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_data_cause_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.privacy_policy_data_cause_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_data_type_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.privacy_policy_data_type_paragraph}
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.privacy_policy_data_type_technical}
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_technical_cookies_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_technical_cookies_paragraph
                                    }
                                </Typography>
                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_data_type_technical_cookies_session_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_technical_cookies_session_paragraph
                                    }
                                </Typography>
                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_data_type_technical_cookies_persistent_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_technical_cookies_persistent_paragraph
                                    }
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_technical_log_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_technical_log_paragraph
                                    }
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.privacy_policy_data_type_personal}
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_personal_account_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_personal_account_paragraph
                                    }
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_personal_event_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_personal_event_paragraph
                                    }
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_personal_newsletter_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_personal_newsletter_paragraph
                                    }
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.privacy_policy_data_type_consents}
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_consents_mail_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_consents_mail_paragraph
                                    }
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_data_type_consents_pn_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_type_consents_pn_paragraph
                                    }
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_data_duration_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_data_duration_paragraph
                                    }
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_data_rights_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.privacy_policy_data_rights_paragraph}
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_technical}
                                </Typography>

                                <Typography style={h2Style}>
                                    {
                                        Dict.privacy_policy_technical_server_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_technical_server_paragraph
                                    }
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.privacy_policy_technical_security}
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_technical_security_encryption
                                    }
                                </Typography>
                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_technical_security_encryption_https_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_technical_security_encryption_https_paragraph
                                    }
                                </Typography>
                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_technical_security_encryption_mail_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_technical_security_encryption_mail_paragraph
                                    }
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_technical_security_access_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_technical_security_access_paragraph
                                    }
                                </Typography>

                                <Typography style={h2Style}>
                                    {Dict.privacy_policy_technical_transfer}
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_technical_transfer_mapbox_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_technical_transfer_mapbox_paragraph
                                    }
                                </Typography>
                                <Typography style={h3Style}>
                                    {
                                        Dict.privacy_policy_technical_transfer_google_fonts_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_technical_transfer_google_fonts_paragraph
                                    }
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_officials}
                                </Typography>

                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_officials_abbey_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_officials_abbey_paragraph
                                    }
                                </Typography>

                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_officials_congregation_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_officials_congregation_paragraph
                                    }
                                </Typography>

                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_officials_diocese_wuerzburg_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_officials_diocese_wuerzburg_paragraph
                                    }
                                </Typography>

                                <Typography style={boldParagraphHeadingStyle}>
                                    {
                                        Dict.privacy_policy_officials_bavarian_dioceses_heading
                                    }
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {
                                        Dict.privacy_policy_officials_bavarian_dioceses_paragraph
                                    }
                                </Typography>

                                <Typography style={h1Style}>
                                    {Dict.privacy_policy_objection_heading}
                                </Typography>
                                <Typography style={paragraphStyle}>
                                    {Dict.privacy_policy_objection_paragraph}
                                </Typography>
                            </div>
                        </AccordionDetails>
                    </Accordion>
                </CardContent>
            </Card>
        </Background>
    );
};

export default withTheme(LegalInformationPage);
