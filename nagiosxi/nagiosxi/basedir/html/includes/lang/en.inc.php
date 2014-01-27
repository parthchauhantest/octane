<?php
// English (U.S.) language file
// $Id: en.inc.php 1300 2012-07-17 19:47:23Z swilkerson $

global $lstr;

include_once("en-perfgraphs.inc.php");

$lstr['language_translation_complete']=true;

///////////////////////////////////////////////////////////////
// PAGE TITLES
///////////////////////////////////////////////////////////////
$lstr['MainPageTitle']="";
$lstr['MissingPageTitle']="Missing Page";
$lstr['MissingFeaturePageTitle']="Unimplemented Feature";
$lstr['LoginPageTitle']="Login";
$lstr['ResetPasswordPageTitle']="Reset Password";
$lstr['PasswordSentPageTitle']="Password Sent";
$lstr['InstallPageTitle']="Install";
$lstr['InstallErrorPageTitle']="Error";


///////////////////////////////////////////////////////////////
// PAGE HEADERS (H1 TAGS)
///////////////////////////////////////////////////////////////
$lstr['MissingPageHeader']="What the...";
$lstr['MissingFeaturePageHeader']="Wouldn't that be nice...";
$lstr['ForcedPasswordChangePageHeader']="Password Change Required";
$lstr['ResetPasswordPageHeader']="Reset Password";
$lstr['MainPageHeader']="Nagios Reports&trade;";
$lstr['LoginPageHeader']="Login";
$lstr['PasswordSentPageHeader']="Password Sent";
$lstr['CreditsPageHeader']="Credits";
$lstr['LegalInfoPageHeader']="Legal Information";


///////////////////////////////////////////////////////////////
// H2 TAGS
///////////////////////////////////////////////////////////////
$lstr['FeedbackSendingHeader']="Sending Feedback...";
$lstr['FeedbackSuccessHeader']="Thank You!";
$lstr['FeedbackErrorHeader']="Error";

///////////////////////////////////////////////////////////////
// MENU ITEMS
///////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////
// SUBMENU ITEMS
///////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////
// FORM LEGENDS
///////////////////////////////////////////////////////////////
$lstr['UpdateUserPrefsFormLegend']="Account Preferences";
$lstr['GeneralOptionsFormLegend']="General Options";
$lstr['UserAccountInfoFormLegend']="User Account Information";
$lstr['LoginPageLegend']="Login";



///////////////////////////////////////////////////////////////
// FORM/PAGE SECTION TITLES
///////////////////////////////////////////////////////////////
$lstr['GeneralProgramSettingsSectionTitle']="General Program Settings";
$lstr['DefaultUserSettingsSectionTitle']="Default User Settings";
$lstr["AdvancedProgramSettingsSectionTitle"]="Advanced Settings";


///////////////////////////////////////////////////////////////
// BUTTONS
///////////////////////////////////////////////////////////////
$lstr['LoginButton']="Login";
$lstr['ResetPasswordButton']="Reset Password";
$lstr['UpgradeButton']="Upgrade";
$lstr['InstallButton']="Install";
$lstr['ChangePasswordButton']="Change Password";
$lstr['UpdateButton']="Update";
$lstr['UpdateSettingsButton']="Update Settings";
$lstr['CancelButton']="Cancel";
$lstr['ContinueButton']="Continue";
$lstr['OkButton']="Ok";
$lstr['AddUserButton']="Add User";
$lstr['UpdateUserButton']="Update User";
$lstr['SubmitButton']="Submit";
$lstr['GoButton']="Go";
$lstr['UpdatePermsButton']="Update Permissions";
$lstr['UpdateDataSourceButton']="Update Settings";
$lstr['UploadFileButton']="Upload File";
$lstr['UploadPluginButton']="Upload Plugin";
$lstr['CheckForUpdatesButton']="Check For Updates Now";


///////////////////////////////////////////////////////////////
// INPUT TEXT TITLE
///////////////////////////////////////////////////////////////
$lstr['UsernameBoxTitle']="Username";
$lstr['Password1BoxTitle']="Password";
$lstr['NewPassword1BoxTitle']="New Password";
$lstr['NewPassword2BoxTitle']="Repeat New Password";
$lstr['Password2BoxTitle']="Repeat Password";
$lstr['AdminEmailBoxText']="Admin Email Address";
$lstr['EmailBoxTitle']="Email Address";
$lstr['DefaultLanguageBoxTitle']="Language";
$lstr['DefaultThemeBoxTitle']="Theme";
$lstr['NameBoxTitle']="Name";
$lstr['AuthorizationLevelBoxTitle']="Authorization Level";
$lstr['ForcePasswordChangeNextLoginBoxTitle']="Force Password Change at Next Login";
$lstr['SendAccountInfoEmailBoxTitle']="Email User Account Information";
$lstr['SendAccountPasswordEmailBoxTitle']="Email User New Password";
$lstr['DefaultDateFormatBoxTitle']="Date Format";
$lstr['DefaultNumberFormatBoxTitle']="Number Format";
$lstr['FeedbackCommentBoxText']="Comments";
$lstr['FeedbackNameBoxTitle']="Your Name (Optional)";
$lstr['FeedbackEmailBoxTitle']="Your Email Address (Optional)";

///////////////////////////////////////////////////////////////
// ERROR MESSAGES
///////////////////////////////////////////////////////////////
$lstr['InvalidUsernamePasswordError']="Invalid username or password.";
$lstr['NoUsernameError']="No username specified.";
$lstr['NoMatchingAccountError']="No account was found by that name.";
$lstr['UnableAccountEmailError']="Unable to get account email address.";
$lstr['UnableAdminEmailError']="Unable to get admin email address.";
$lstr['InvalidEmailAddressError']="Email address is invalid.";
$lstr['BlankUsernameError']="Username is blank.";
$lstr['BlankEmailError']="Email address is blank.";
$lstr['InvalidEmailError']="Email address is invalid.";
$lstr['BlankPasswordError']="Password is blank.";
$lstr['BlankSecurityLevelError']="Security level is blank.";
$lstr['AccountNameCollisionError']="An account with that username already exists.";
$lstr['AddAccountFailedError']="Failed to add account";
$lstr['AddAccountPrivilegesFailedError']="Unable to assign account privileges.";
$lstr['BlankURLError']="URL is blank.";
$lstr['MismatchedPasswordError']="Passwords do not match.";
$lstr['BlankDefaultLanguageError']="Default language not specified.";
$lstr['BlankDefaultThemeError']="Default theme not specified.";
$lstr['BlankNameError']="Name is blank.";
$lstr['InvalidURLError']="Invalid URL.";
$lstr['BadUserAccountError']="User account was not found.";
$lstr['BlankAuthLevelError']="Blank authorization level.";
$lstr['InvalidAuthLevelError']="Invalid authorization level.";
$lstr['BlankUserAccountError']="User account was not specified.";
$lstr['CannotDeleteOwnAccountError']="You cannot delete your own account.";
$lstr['NoUserAccountSelectedError']="No account selected.";
$lstr['InvalidUserAccountError']="Invalid account.";
$lstr["NoAdminNameError"]="No admin name specified.";
$lstr["NoAdminEmailError"]="No admin email address specified.";
$lstr["InvalidAdminEmailError"]="Admin email address is invalid.";

///////////////////////////////////////////////////////////////
// SHORT LINK TEXT
///////////////////////////////////////////////////////////////
$lstr['LegalLinkText']="Legal Info";
$lstr['CreditsLinkText']="Credits";
$lstr['AboutLinkText']="About";
$lstr['PrivacyPolicyLinkText']="Privacy Policy";
$lstr['CheckForUpdatesLinkText']="Check for Updates";

$lstr['FirstPageText']="First Page";
$lstr['LastPageText']="Last Page";
$lstr['NextPageText']="Next Page";
$lstr['PreviousPageText']="Previous Page";
$lstr['PageText']="Page";


///////////////////////////////////////////////////////////////
// TABLE HEADERS
///////////////////////////////////////////////////////////////
$lstr['UsernameTableHeader']="Username";
$lstr['NameTableHeader']="Name";
$lstr['EmailTableHeader']="Email";
$lstr['ActionsTableHeader']="Actions";
$lstr['DateTableHeader']="Date";
$lstr['ResultTableHeader']="Result";
$lstr['FileTableHeader']="File";
$lstr['OutputTableHeader']="Output";
$lstr['SnapshotResultTableHeader']="Snapshot Result";



///////////////////////////////////////////////////////////////
// SHORT TEXT
///////////////////////////////////////////////////////////////
$lstr['MissingPageText']="The page that went missing was: ";
$lstr['MissingFeatureText']="We're currently working on this feature.  Until it's completed, you can't have it!  Seriously though - just sit tight for a while and we'll get it done.";
$lstr['LoginText']="Login";
$lstr['LogoutText']="Logout";
$lstr['ForgotPasswordText']="Forgot your password?";
$lstr['LoggedOutText']="You have logged out.";
$lstr['TryInstallAgainText']="Try Again";
$lstr['UsernameText']="Username";
$lstr['PasswordText']="Password";
$lstr['AdminPasswordText']="Administrator Password";
$lstr['ErrorText']="Error";
$lstr['QueryText']="Query";
$lstr['LanguageText']="Language";
$lstr['ThemeText']="Theme";
$lstr['LoggedInAsText']="Logged in as";
$lstr['MenuText']="Menu";
$lstr['UserPrefsUpdatedText']="Settings Updated.";
$lstr['YesText']="Yes";
$lstr['NoText']="No";
$lstr['GeneralOptionsUpdatedText']="Options Updated.";
$lstr['UserUpdatedText']="User Updated.";
$lstr['UserAddedText']="User Added.";
$lstr['UserDeletedText']="User Deleted.";
$lstr['UsersDeletedText']="Users Deleted.";
$lstr['AddNewUserText']="Add New User";
$lstr['SessionTimedOut']="Your session has timed out.";
$lstr['SearchBoxText']="Search...";
$lstr['WithSelectedText']="With Selected:";
$lstr['CheckForUpdateNowText']="Check Now";
$lstr['YourVersionIsUpToDateText']="Your version is up to date.";
$lstr['AnUpdateIsAvailableText']="An update is available.";
$lstr['NewVersionInformationText']="New version information";
$lstr['CurrentVersionInformationText']="Your current version";
$lstr['NoticesText']="Notices";
$lstr['AdminLevelText']="Admin";
$lstr['UserLevelText']="User";
$lstr['ContinueText']="Continue";
$lstr['CancelText']="Cancel";
$lstr['PerPageText']="Per Page";

$lstr['NeverText']="N/A";
$lstr['NotApplicableText']="N/A";





///////////////////////////////////////////////////////////////
// PARTING/SUBSTRING TEXT
///////////////////////////////////////////////////////////////
$lstr['TotalRecordsSubText']="total records";
$lstr['TotalMatchesForSubText']="total matches for";
$lstr['ShowingSubText']="Showing";
$lstr['OfSubText']="of";
$lstr['YourAreRunningVersionText']="You are currently running";
$lstr['WasReleasedOnText']="was released on";


///////////////////////////////////////////////////////////////
// LONGER TEXT/NOTES
///////////////////////////////////////////////////////////////
$lstr['MissingPageNote']="The page you requested seems to be missing.  It is theoretically possible - though highly unlikely - that we are to blame for this.  It is far more likely that something is wrong with the Universe.  Run for it!";
$lstr['ResetPasswordNote']="Enter your username to have your password reset and emailed to you.";
$lstr['PasswordSentNote']="Your account password has been reset and emailed to you.";
$lstr['AlreadyInstalledNote']="Nagios Reports is already installed and up-to-date.";
$lstr['UpgradeRequiredNote']="Your installation requires an upgrade.  Click the button below to begin.";
$lstr['UpgradeErrorNote']="One or more errors were encountered:";
$lstr['InstallRequiredNote']="Nagios Reports has not yet been setup.  Complete the form below to install it.";
$lstr['InstallErrorNote']="One or more errors were encountered:";
$lstr['InstallFatalErrorNote']="One or more fatal errors were encountered during the installation process:";
$lstr['UpgradeCompleteNote']="Upgrade is complete!";
$lstr['InstallCompleteNote']="Installation is complete!  You can now login with the following credentials:";
$lstr['SQLQueryErrorNote']="An error occurred while executing the following SQL query.";
$lstr['UnableConnectDBErrorNote']="Unable to connect to database";
$lstr['NDOUtilsMissingNote']="The database you specified does not contain tables from the NDOUtils addon.  You must use the same database for both NDOUtils and Reports.  Check your configuration file.";
$lstr['ForceChangePasswordNote']="You are required to change your password before proceeding.";
$lstr['FeedbackSendIntroText']="We love input!  Tell us what you think about this product and you'll directly drive future innovation!";
$lstr['FeedbackSendingMessage']="Please wait...";
$lstr['FeedbackSuccessMessage']="Thanks for helping to make this product better!  We'll review your comments as soon as we get a chance.  Until then, kudos to you for being awesome and helping drive innovation!<br><br>   - The Dedicated Team @ Nagios Enterprises";
$lstr['FeedbackErrorMessage']="An error occurred.  Please try again later.";




///////////////////////////////////////////////////////////////
// EMAIL 
///////////////////////////////////////////////////////////////
//$lstr['AdminEmailFromName']="Nagios XI Admin";

$lstr['PasswordResetEmailSubject']="Nagios XI Password Reset";
$lstr['PasswordChangedEmailSubject']="Nagios XI Password Changed";
$lstr['AccountCreatedEmailSubject']="Nagios XI Account Created";

$lstr['PasswordResetEmailMessage']="Your Nagios XI account password has been reset to:\n\n%s\n\nYou can login to Nagios XI at the following URL:\n\n%s\n\n";

$lstr['PasswordChangedEmailMessage']="Your Nagios XI account password has been changed by an administrator.  You can login using the following information:\n\nUsername: %s\nPassword: %s\nURL: %s\n\n";

$lstr['AccountCreatedEmailMessage']="An account has been created for you to access Nagios XI.  You can login using the following information:\n\nUsername: %s\nPassword: %s\nURL: %s\n\n";


///////////////////////////////////////////////////////////////
// TOOLTIP TEXT
///////////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////
// IMAGE ALT/TITLE TEXT
///////////////////////////////////////////////////////////////
$lstr['EditAlt']="Edit";
$lstr['DeleteAlt']="Delete";
$lstr['ClearSearchAlt']="Clear Search Criteria";
$lstr['CloseAlt']="Close";
$lstr['PermissionsAlt']="Permissions";
$lstr['CustomizePermsAlt']="Customize Permissions";
$lstr['MasqueradeAlt']="Masquerade As";
$lstr['ViewAlt']="View";
$lstr['PopoutAlt']="Popout";
$lstr['AddToMyViewsAlt']="Add to My Views";
$lstr['AddViewAlt']="Add View";
$lstr['EditViewAlt']="Edit View";
$lstr['DeleteViewAlt']="Delete View";
$lstr['SendFeedbackAlt']="Send Us Feedback";
$lstr['GetPermalinkAlt']="Get Permalink";
$lstr['DownloadAlt']="Download";
$lstr['ViewOutputAlt']="View Output";
$lstr['ViewHostNotificationsAlt']="View Host Notifications";
$lstr['ViewHostStatusAlt']="View Current Host Status";
$lstr['ViewHostServiceStatusAlt']="View Current Status of Host Services";
$lstr['ViewServiceNotificationsAlt']="View Service Notifications";
$lstr['ViewServiceStatusAlt']="View Current Service Status";
$lstr['ViewHostServiceStatusAlt']="View Current Status For Host Services";
$lstr['ViewHostHistoryAlt']="View Host History";
$lstr['ViewServiceHistoryAlt']="View Service History";
$lstr['ViewHostTrendsAlt']="View Host Trends";
$lstr['ViewServiceTrendsAlt']="View Service Trends";
$lstr['ViewHostAvailabilityAlt']="View Host Availability";
$lstr['ViewServiceAvailabilityAlt']="View Service Availability";
$lstr['ViewHostHistogramAlt']="View Host Alert Histogram";
$lstr['ViewServiceHistogramAlt']="View Service Alert Histogram";
$lstr['RefreshAlt']="Refresh";
$lstr['ForceRefreshAlt']="Force Refresh";
$lstr['ClearFilterAlt']="Clear Filter";
$lstr['EditSettingsAlt']="Edit Settings";


///////////////////////////////////////////////////////////////
// DATE FORMAT TYPES
///////////////////////////////////////////////////////////////
$lstr['DateFormatISO8601Text']="YYYY-MM-DD HH:MM:SS";
$lstr['DateFormatUSText']="MM/DD/YYYY HH:MM:SS";
$lstr['DateFormatEuroText']="DD/MM/YYYY HH:MM:SS";


///////////////////////////////////////////////////////////////
// NUMBER FORMAT TYPES
///////////////////////////////////////////////////////////////
$lstr['NumberFormat1Text']="1000.00";
$lstr['NumberFormat2Text']="1,000.00";
$lstr['NumberFormat3Text']="1.000,00";
$lstr['NumberFormat4Text']="1 000,00";
$lstr['NumberFormat5Text']="1'000,00";


///////////////////////////////////////////////////////////////
// OBJECT TYPES
///////////////////////////////////////////////////////////////
$lstr['HostObjectText']="Host";
$lstr['HostGroupObjectText']="Host Group";
$lstr['ServiceObjectText']="Service";
$lstr['ServiceGroupObjectText']="Service Group";
$lstr['HostEscalationObjectText']="Host Escalation";
$lstr['ServiceEscalationObjectText']="Service Escalation";
$lstr['HostDependencyObjectText']="Host Dependency";
$lstr['ServiceDependencyObjectText']="Service Dependency";
$lstr['TimeperiodObjectText']="Timeperiod";
$lstr['ContactObjectText']="Contact";
$lstr['ContactGroupObjectText']="Contact Group";
$lstr['CommandObjectText']="Command";

$lstr['HostObjectPluralText']="Hosts";
$lstr['HostGroupObjectPluralText']="Host Groups";
$lstr['ServiceObjectPluralText']="Services";
$lstr['ServiceGroupObjectPluralText']="Service Groups";
$lstr['HostEscalationObjectPluralText']="Host Escalations";
$lstr['ServiceEscalationObjectPluralText']="Service Escalations";
$lstr['HostDependencyObjectPluralText']="Host Dependencies";
$lstr['ServiceDependencyObjectPluralText']="Service Dependencies";
$lstr['TimeperiodObjectPluralText']="Timeperiods";
$lstr['ContactObjectPluralText']="Contacts";
$lstr['ContactGroupObjectPluralText']="Contact Groups";
$lstr['CommandObjectPluralText']="Commands";


///////////////////////////////////////////////////////////////
// STATE AND CHECK TYPES
///////////////////////////////////////////////////////////////
$lstr['HostStatePendingText']="Pending";
$lstr['HostStateUnknownText']="Unknown";
$lstr['HostStateUpText']="Up";
$lstr['HostStateDownText']="Down";
$lstr['HostStateUnreachableText']="Unreachable";

$lstr['ServiceStatePendingText']="Pending";
$lstr['ServiceStateOkText']="Ok";
$lstr['ServiceStateWarningText']="Warning";
$lstr['ServiceStateUnknownText']="Unknown";
$lstr['ServiceStateCriticalText']="Critical";

$lstr['HardStateText']="Hard";
$lstr['SoftStateText']="Soft";

$lstr['PassiveCheckText']="Passive";
$lstr['ActiveCheckText']="Active";



///////////////////////////////////////////////////////////////
// UNSORTED MISC
///////////////////////////////////////////////////////////////
$lstr['FeedbackPopupTitle']="Send Us Feedback";
$lstr['AjaxErrorHeader']="Error";
$lstr['AjaxErrorMessage']="An error occurred processing your request. :-(";
$lstr['AjaxSendingHeader']="Please Wait";
$lstr['AjaxSendingMessage']="Processing...";

$lstr['AddToMyViewsHeader']="Add View";
$lstr['AddToMyViewsMessage']="Use this to add what you see on the screen to your <b>Views</b> page.";
$lstr['AddToMyViewsSuccessHeader']="View Added";
$lstr['AddToMyViewsSuccessMessage']="Success! Your view was added to your <b>Views</b> page.";
$lstr['AddToMyViewsTitleBoxTitle']="View Title";

$lstr['AddViewHeader']="Add View";
$lstr['AddViewMessage']="";
$lstr['AddViewSuccessHeader']="View Added";
$lstr['AddViewSuccessMessage']="Success! Your view was added to your <b>Views</b> page.";
$lstr['AddViewURLBoxTitle']="URL";
$lstr['AddViewTitleBoxTitle']="View Title";

$lstr['EditViewHeader']="Edit View";
$lstr['EditViewMessage']="";
$lstr['EditViewSuccessHeader']="View Changed";
$lstr['EditViewSuccessMessage']="Success! Your view was updated successfully.";
$lstr['EditViewURLBoxTitle']="URL";
$lstr['EditViewTitleBoxTitle']="View Title";

$lstr['PermalinkHeader']="Permalink";
$lstr['PermalinkMessage']="Copy the URL below to retain a direct link to your current view.";
$lstr['PermalinkURLBoxTitle']="URL";

$lstr['MyViewsPageTitle']="My Views";
$lstr['NoViewsDefinedPageHeader']="No Views Defined";
$lstr['NoViewsDefinedText']="You have no views defined.";

$lstr['MyDashboardsPageTitle']="My Dashboards";
$lstr['NoDashboardsDefinedPageHeader']="No Dashboards Defined";
$lstr['NoDashboardsDefinedText']="You have no dashboards defined.";

$lstr['AddDashboardAlt']="Add A New Dashboard";
$lstr['EditDashboardAlt']="Edit Dashboard";
$lstr['DeleteDashboardAlt']="Delete Dashboard";

$lstr['PauseAlt']="Pause";

$lstr['AvailableDashletsPageTitle']="Available Dashlets";
$lstr['AvailableDashletsPageHeader']="Available Dashlets";
$lstr['AvailableDashletsPageText']="The following dashlets can be added to any one or more of your dashboards.  How awesome!";

$lstr['AddDashboardHeader']="Add Dashboard";
$lstr['AddDashboardMessage']="Use this to add a new dashboard to your <b>Dashboards</b> page.";
$lstr['AddDashboardTitleBoxTitle']="Dashboard Title";
$lstr['AddDashboardSuccessHeader']="Dashboard Added";
$lstr['AddDashboardSuccessMessage']="Success! Your new dashboard has been added.";

$lstr['EditDashboardHeader']="Edit Dashboard";
$lstr['EditDashboardMessage']="";
$lstr['EditDashboardSuccessHeader']="Dashboard Changed";
$lstr['EditDashboardSuccessMessage']="Success! Your dashboard was updated successfully.";
$lstr['EditDashboardTitleBoxTitle']="Dashboard Title";

$lstr['DeleteDashboardHeader']="Confirm Dashboard Deletion";
$lstr['DeleteDashboardMessage']="Are you sure you want to delete this dashboard and all dashlets it contains?";
$lstr['DeleteDashboardSuccessHeader']="Dashboard Deleted";
$lstr['DeleteDashboardSuccessMessage']="The requested dashboard has been deleted.  Good riddance!";

$lstr['DeleteButton']="Delete";

$lstr['BadDashboardPageTitle']="Bad Dashboard";
$lstr['BadDashboardPageHeader']="Bad Dashboard";
$lstr['BadDashboardText']="Unfortunately for you, that dashboard is not valid...  Too bad.";

$lstr['ViewDeletedHeader']="View Deleted";
$lstr['ViewDeletedMessage']="Good riddance!";

$lstr['AddToDashboardHeader']="Add To Dashboard";
$lstr['AddToDashboardMessage']="Add this powerful little dashlet to one of your dashboards for visual goodness.";
$lstr['AddToDashboardTitleBoxTitle']="Dashlet Title";

$lstr['AddToDashboardSuccessHeader']="Dashlet Added";
$lstr['AddToDashboardSuccessMessage']="The little dashlet that could will now be busy at work on your dashboard...";
$lstr['AddToDashboardDashboardSelectTitle']="Which Dashboard?";

$lstr['ViewsPageTitle']="Views";
$lstr['AdminPageTitle']="Admin";
$lstr['DashboardsPageTitle']="Dashboards";
$lstr['SubcomponentsPageTitle']="Addons";
$lstr['SubcomponentsPageHeader']="Addons";

$lstr['NoViewsToDeleteHeader']="No View";
$lstr['NoViewsToDeleteMessage']="There is no active view to delete.";
$lstr['NoViewsToEditHeader']="No View";
$lstr['NoViewsToEditMessage']="There is no active view to edit.";

$lstr['NoDashboardsToDeleteHeader']="No Dashboard";
$lstr['NoDashboardsToDeleteMessage']="There is no active dashboard to delete.";
$lstr['NoDashboardsToEditHeader']="No Dashboard";
$lstr['NoDashboardsToEditMessage']="There is no active dashboard to edit.";

$lstr['AddItButton']="Add It";

$lstr["DashletDeletedHeader"]="Dashlet Deleted";
$lstr["DashletDeletedMessage"]="Good riddance!";

$lstr["PinFloatDashletAlt"]="Pin / Float Dashlet";
$lstr["ConfigureDashletAlt"]="Configure Dashlet";
$lstr["DeleteDashletAlt"]="Delete Dashlet";
$lstr["DashboardBackgroundColorTitle"]="Background Color";


$lstr["AccountSettingsPageTitle"]="Account Information";
$lstr["AccountSettingsPageHeader"]="Account Information";
$lstr["MyAccountSettingsSectionTitle"]="General Account Settings";
$lstr["MyAccountPreferencesSectionTitle"]="Account Preferences";

$lstr["NotificationPrefsPageTitle"]="Notification Preferences";
$lstr["NotificationPrefsPageHeader"]="Notification Preferences";


$lstr["IngoreUpdateNotices"]="Ignore Update Notices";
$lstr["DemoModeChangeError"]="Changes are disabled while in demo mode.";

$lstr["GlobalConfigPageTitle"]="System Settings";
$lstr["AutoUpdateCheckBoxTitle"]="Automatically Check for Updates";

$lstr["ManageUsersPageTitle"]="Manage Users";
$lstr["ManageUsersPageHeader"]="Manage Users";

$lstr['NoMatchingRecordsFoundText']="Not Matching Records Found.";

$lstr['CloneAlt']="Clone";

$lstr['MasqueradeAlertHeader']="Masquerade Notice";
$lstr['MasqueradeMessageText']="You are about to masquerade as another user.  If you choose to continue you will be logged out of your current account and logged in as the selected user.  In the process of doing so, you may loose your admin privileges.";

$lstr['AddUserPageTitle']="Add New User";
$lstr['AddUserPageHeader']="Add New User";
$lstr['EditUserPageTitle']="Edit User";
$lstr['EditUserPageHeader']="Edit User";

$lstr['UserAccountGeneralSettingsSectionTitle']="General Settings";
$lstr['UserAccountPreferencesSectionTitle']="Preferences";
$lstr['UserAccountSecuritySettingsSectionTitle']="Security Settings";

$lstr['ProgramURLText']="Program URL";

$lstr['GlobalConfigUpdatedText']="Settings Updated.";

$lstr['AdminNameText']="Administrator Name";
$lstr['AdminEmailText']="Administrator Email Address";

$lstr['ForcePasswordChangePageTitle']="Password Change Required";

$lstr['CloneUserPageTitle']="Clone User";
$lstr['CloneUserPageHeader']="Clone User";
$lstr['CloneUserButton']="Clone User";

$lstr['UserClonedText']="User cloned.";

$lstr['CloneUserDescription']="Use this functionality to create a new user account that is an exact clone of another account on the system.  The cloned account will inherit all preferences, views, and dashboards of the original user.";

$lstr['SystemStatusPageTitle']="System Status";
$lstr['SystemStatusPageHeader']="System Status";
$lstr['MonitoringEngineStatusPageTitle']="Monitoring Engine Status";
$lstr['MonitoringEngineStatusPageHeader']="Monitoring Engine Status";

$lstr['CannotDeleteHomepageDashboardHeader']="Error";
$lstr['CannotDeleteHomepageDashboardMessage']="You cannot delete your home page dashboard.";

$lstr['CloneDashboardAlt']="Clone Dashboard";

$lstr['CloneButton']="Clone";

$lstr['CloneDashboardHeader']="Clone Dashboard";
$lstr['CloneDashboardMessage']="Use this to make an exact clone of the current dashboard and all its wonderful dashlets.";
$lstr['CloneDashboardSuccessHeader']="Dashboard Cloned";
$lstr['CloneDashboardSuccessMessage']="Dashboard successfully cloned.";
$lstr['CloneDashboardTitleBoxTitle']="New Title";

$lstr['CannotDeleteHomepageDashletHeader']="Error";
$lstr['CannotDeleteHomepageDashletMessage']="Deleting dashlets from the home page dashboard is disabled while in demo mode.";

$lstr['PerformanceGraphsPageTitle']="Performance Graphs";
$lstr['PerformanceGraphsPageHeader']="Performance Graphs";

$lstr['NoPerformanceGraphDataSourcesMessage']="There are no datasources to display for this service.";

$lstr['ClearDateAlt']="Clear Date";
$lstr['NotAuthorizedErrorText']="You are not authorized to access this feature.  Contact your Nagios XI administrator for more information, or to obtain access to this feature.";

$lstr['ReportsPageTitle']="Reports";
$lstr['ReportsPageHeader']="Reports";
$lstr['HelpPageTitle']="Help";
$lstr['HelpPageHeader']="Help";

$lstr['NagiosCoreReportsPageTitle']="Reports";
$lstr['NagiosCoreReportsPageHeader']="Reports";
//$lstr['NagiosCoreReportsMessage']="Legacy Nagios&reg; Core&trade; reports are provided for historical purposes.  Please note that legacy report do not offer they same flexibility or options as newer XI reports.";
$lstr['NagiosCoreReportsMessage']="";

$lstr['NagiosXIReportsPageTitle']="Reports";
$lstr['NagiosXIReportsPageHeader']="Reports";
$lstr['NagiosXIReportsMessage']="Reports allow you to see how well your network and system have performed over a period of time.  Available reports are listed below.";

$lstr['LegacyReportAvailabilityTitle']="Availability Report";
$lstr['LegacyReportAvailabilityDescription']="Provides an availability report of uptime for hosts and services.  Useful for determining SLA requirements and compliance.";
$lstr['LegacyReportTrendsTitle']="Trends Report";
$lstr['LegacyReportTrendsDescription']="Provides a graphical, timeline breakdown of the state of a particular host or service.";
$lstr['LegacyReportHistoryTitle']="Alert History Report";
$lstr['LegacyReportHistoryDescription']="Provides a record of historical alerts for hosts and services.";
$lstr['LegacyReportSummaryTitle']="Alert Summary Report";
$lstr['LegacyReportSummaryDescription']="Provides a report of top alert producers.  Useful for determining the biggest trouble-makers in your IT infrastructure.";
$lstr['LegacyReportHistogramTitle']="Alert Histogram Report";
$lstr['LegacyReportHistogramDescription']="Provides a frequency graph of host and service alerts.  Useful for seeing when alerts most often occur for a particular host or service.";
$lstr['LegacyReportNotificationsTitle']="Notifications Report";
$lstr['LegacyReportNotificationsDescription']="Provides a historical record of host and service notifications that have been sent to contacts.";


$lstr['SubcomponentsMessage']="Nagios XI includes several proven, enterprise-grade Open Source addons.  You may access these addons directly using the links below.";

$lstr['SubcomponentNagiosCoreDescription']="Nagios&reg; Core&trade; provides the primary monitoring and alerting engine.";

$lstr['SubcomponentNagiosCoreConfigDescription']="Nagios Core Config Manager provides an advanced graphical interface for configuring the Nagios Core monitoring and alerting engine. Recommended for advanced users only.";

$lstr['ApplyNagiosCoreConfigPageTitle']="Apply Configuration";
$lstr['ApplyNagiosCoreConfigPageHeader']="Apply Configuration";

$lstr['ApplyingNagiosCoreConfigPageTitle']="Applying Configuration";
$lstr['ApplyingNagiosCoreConfigPageHeader']="Applying Configuration";
$lstr['ApplyNagiosCoreConfigMessage']="Use this feature to apply any outstanding configuration changes to Nagios Core.  Changes will be applied and the monitoring engine will be restarted with the updated configuration.";

$lstr['ApplyConfigText']="Apply Configuration";
$lstr['TryAgainText']="Try Again";
$lstr['ApplyConfigSuccessMessage']="Success!  Nagios Core was restarted with an updated configuration.";
$lstr['ApplyConfigErrorMessage']="An error occurred while attempting to apply your configuration to Nagios Core.  Monitoring engine configuration files have been rolled back to their last known good checkpoint.";
$lstr['ViewConfigSuccessSnapshotMessage']="View configuration snapshots";
$lstr['ViewConfigErrorSnapshotMessage']="View a snapshot of this configuration error";

$lstr['AjaxSubmitCommandHeader']="Please Wait";
$lstr['AjaxSubmitCommandMessage']="Submitting command";

$lstr['HelpPageTitle']="Help for Nagios XI";
$lstr['HelpPageHeader']="Help for Nagios XI";
$lstr['HelpPageGeneralSectionTitle']="Get Help Online";
$lstr['HelpPageMoreOptionsSectionTitle']="More Options";

$lstr['AboutPageTitle']="About Nagios XI";
$lstr['AboutPageHeader']="About";

$lstr['LegalPageTitle']="Legal Information";
$lstr['LegalPageHeader']="Legal Information";

$lstr['LicensePageTitle']="License Information";
$lstr['LicensePageHeader']="License Information";


$lstr['AdminPageTitle']="Administration";
$lstr['AdminPageHeader']="Administration";


$lstr['HostStatusDetailPageTitle']="Host Status Detail";
$lstr['HostStatusDetailPageHeader']="Host Status Detail";
$lstr['ServiceStatusDetailPageTitle']="Service Status Detail";
$lstr['ServiceStatusDetailPageHeader']="Service Status Detail";
$lstr['ServiceGroupStatusPageTitle']="Service Group Status";
$lstr['ServiceGroupStatusPageHeader']="Service Group Status";
$lstr['HostGroupStatusPageTitle']="Host Group Status";
$lstr['HostGroupStatusPageHeader']="Host Group Status";
$lstr['HostStatusPageTitle']="Host Status";
$lstr['HostStatusPageHeader']="Host Status";
$lstr['ServiceStatusPageTitle']="Service Status";
$lstr['ServiceStatusPageHeader']="Service Status";
$lstr['TacticalOverviewPageTitle']="Tactical Overview";
$lstr['TacticalOverviewPageHeader']="Tactical Overview";
$lstr['OpenProblemsPageTitle']="Open Problems";
$lstr['OpenProblemsPageHeader']="Open Problems";
$lstr['HostProblemsPageTitle']="Host Problems";
$lstr['HostProblemsPageHeader']="Host Problems";
$lstr['ServiceProblemsPageTitle']="Service Problems";
$lstr['ServiceProblemsPageHeader']="Service Problems";

$lstr['HostNameTableHeader']="Host";
$lstr['ServiceNameTableHeader']="Service";
$lstr['StatusTableHeader']="Status";
$lstr['LastCheckTableHeader']="Last Check";
$lstr['CheckAttemptTableHeader']="Attempt";
$lstr['DurationTableHeader']="Duration";
$lstr['StatusInformationTableHeader']="Status Information";

$lstr['LicensePageTitle']="License Information";
$lstr['LicensePageHeader']="License Information";
$lstr['LicensePageMessage']="";

$lstr['LicenseKeySectionTitle']="License Key";
$lstr['LicenseTypeText']="License Type";
$lstr['LicenseTypeFreeText']="Free";
$lstr['LicenseTypeFreeNotes']="(Limited edition without support)";
$lstr['LicenseTypeLicensedText']="Licensed";
$lstr['LicenseInformationSectionTitle']="License Information";
$lstr['LicenseKeyText']="Your License Key";
$lstr['UpdateLicenseButton']="Update License";
$lstr['InvalidLicenseKeyError']="The license key you entered is not valid.";
$lstr['LicenseInformationUpdatedText']="License key updated successfully.";
$lstr['LicenseExceededPageTitle']="License Exceeded";
$lstr['LicenseExceededPageHeader']="License Exceeded";
$lstr['LicenseExceededMessage']="You have exceeded your license, so this feature is not available.";
$lstr['LicenseOptionsSectionTitle']="License Options";

$lstr['AccountInfoPageTitle']="Account Information";

$lstr['NotificationMethodsSectionTitle']="Notification Methods";
$lstr['NotificationMethodsMessage']="Specify the methods by which you'd like to receive alert messages.  <br><b>Note:</b>These methods are only used if you have <a href='notifyprefs.php'>enabled notifications</a> for your account.";

$lstr['ReceiveNotificationsByEmail']="Email";
$lstr['ReceiveNotificationsByMobileTextMessage']="Mobile Phone Text Message";
$lstr['EnableNotifications']="Enable Notifications";
$lstr['EnableNotificationsMessage']="Choose whether or not you want to receive alert messages.  <br><b>Note:</b> You must specify which notification methods to use in the <a href='notifymethods.php'>notification methods</a> page.";
$lstr['EnableNotificationsSectionTitle']="Notification Status";
$lstr['MobileNumberBoxTitle']="Mobile Phone Number";
$lstr['MobileProviderBoxTitle']="Mobile Phone Carrier";
$lstr['InvalidMobileNumberError']="Invalid mobile phone number.";
$lstr['BlankMobileNumberError']="Missing mobile phone number.";
$lstr['NotificationsPrefsUpdatedText']="Notification preferences updated.";
$lstr['NotificationTypesSectionTitle']="Notification Types";
$lstr['NotificationTypesMessage']="Select the types of alerts you'd like to receive.";
$lstr['NotificationTimesSectionTitle']="Notification Times";
$lstr['NotificationTimesMessage']="Specify the times of day you'd like to receive alerts.";

$lstr['HostRecoveryNotificationsBoxTitle']="Host Recovery";
$lstr['HostDownNotificationsBoxTitle']="Host Down";
$lstr['HostUnreachableNotificationsBoxTitle']="Host Unreachable";
$lstr['HostFlappingNotificationsBoxTitle']="Host Flapping";
$lstr['HostDowntimeNotificationsBoxTitle']="Host Downtime";
$lstr['ServiceWarningNotificationsBoxTitle']="Service Warning";
$lstr['ServiceRecoveryNotificationsBoxTitle']="Service Recovery";
$lstr['ServiceUnknownNotificationsBoxTitle']="Service Unknown";
$lstr['ServiceCriticalNotificationsBoxTitle']="Service Critical";
$lstr['ServiceFlappingNotificationsBoxTitle']="Service Flapping";
$lstr['ServiceDowntimeNotificationsBoxTitle']="Service Downtime";

$lstr['NoNotificationMethodsSelectedError']="No notification methods selected.";
$lstr['InvalidTimeRangesError']="One or more time ranges is invalid.";
$lstr['BlankMobileProviderError']="No mobile carrier selected.";


$lstr['WeekdayBoxTitle']=array(
	0 => "Sunday",
	1 => "Monday",
	2 => "Tuesday",
	3 => "Wednesday",
	4 => "Thursday",
	5 => "Friday",
	6 => "Saturday",
	);

$lstr['FromBoxTitle']="From";
$lstr['ToBoxTitle']="To";

$lstr['AuthorizedForAllObjectsBoxTitle']="Can see all hosts and services";
$lstr['AuthorizedToConfigureObjectsBoxTitle']="Can (re)configure hosts and services";
$lstr['AuthorizedForAllObjectCommandsBoxTitle']="Can control all hosts and services";
$lstr['AuthorizedForMonitoringSystemBoxTitle']="Can see/control monitoring engine";
$lstr['AdvancedUserBoxTitle']="Can access advanced features";
$lstr['ReadonlyUserBoxTitle']="Has read-only access";

$lstr['NotAuthorizedPageTitle']="Not Authorized";
$lstr['NotAuthorizedPageHeader']="Not Authorized";
$lstr['NotAuthorizedForObjectMessage']="You are not authorized to view the requested object, or the object does not exist.";

$lstr['NotificationMessagesPageTitle']="Notification Messages";
$lstr['NotificationMessagesPageHeader']="Notification Messages";
$lstr['NotificationMessagesMessage']="Use this feature to customize the content of the notification messages you receive.";

$lstr['EmailNotificationMessagesSectionTitle']="Email Notifications";
$lstr['EmailNotificationMessagesMessage']="Specify the format of the host and service alert messages you receive via email.";

$lstr['MobileTextNotificationMessagesSectionTitle']="Mobile Text Notifications";
$lstr['MobileTextNotificationMessagesMessage']="Specify the format of the host and service alert messages you receive via mobile text message.";

$lstr['HostNotificationMessageSubjectBoxTitle']="Host Alert Subject";
$lstr['HostNotificationMessageBodyBoxTitle']="Host Alert Message";
$lstr['ServiceNotificationMessageSubjectBoxTitle']="Service Alert Subject";
$lstr['ServiceNotificationMessageBodyBoxTitle']="Service Alert Message";

$lstr['AgreeLicenseError']="You must agree to the license before using this software.";
$lstr['AgreeToLicenseBoxText']="I have read, understood, and agree to be bound by the terms of the license above.";

$lstr['AgreeLicensePageTitle']="License Agreement";
$lstr['AgreeLicensePageHeader']="License Agreement";
$lstr['AgreeLicenseNote']="You must agree to the Nagios Software License Terms and Conditions before continuing using this software.";

$lstr['InstallPageTitle']="Nagios XI Installer";
$lstr['InstallPageHeader']="Nagios XI Installer";
$lstr['InstallPageMessage']="Welcome to the Nagios XI installation.  Just answer a few simple questions and you'll be ready to go.";

$lstr['InstallCompletePageTitle']="Installation Complete";
$lstr['InstallCompletePageHeader']="Installation Complete";
$lstr['InstallCompletePageMessage']="Congratulations! You have successfully installed Nagios XI.";

$lstr['ConfigPageTitle']="Configuration";  // used twice
$lstr['ConfigOverviewPageTitle']="Configuration Options";
$lstr['ConfigOverviewPageHeader']="Configuration Options";
$lstr['ConfigOverviewPageNotes']="What would you like to configure?";

$lstr['MonitoringWizardPageHeader']="Monitoring Wizard";
$lstr['MonitoringWizardPageTitle']="Monitoring Wizard";


$lstr['NextButton']="Next";
$lstr['BackButton']="Back";

$lstr['MonitoringWizardStep1PageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStep1PageHeader']="Monitoring Wizard - Step 1";
$lstr['MonitoringWizardStep1SectionTitle']="";
$lstr['MonitoringWizardStep1Notes']="Monitoring wizards guide you through the process of monitoring devices, servers, applications, services, and more.  Select the appropriate wizard below to get started.";

$lstr['MonitoringWizardStep2PageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStep2PageHeader']="Monitoring Wizard - Step 2";

$lstr['MonitoringWizardStep3PageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStep3PageHeader']="Monitoring Wizard - Step 3";

$lstr['MonitoringWizardStep4PageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStep4PageHeader']="Monitoring Wizard - Step 4";

$lstr['MonitoringWizardStep5PageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStep5PageHeader']="Monitoring Wizard - Step 5";

$lstr['MonitoringWizardStep6PageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStep6PageHeader']="Monitoring Wizard - Step 6";

$lstr['MonitoringWizardStepFinalPageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardStepFinalPageHeader']="Monitoring Wizard - Final Step";

$lstr['MonitoringWizardCommitCompletePageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardCommitCompletePageHeader']="Monitoring Wizard";

$lstr['MonitoringWizardCommitSuccessSectionTitle']="Configuration Request Successful";
$lstr['MonitoringWizardCommitSuccessNotes']="Your configuration changes have been successfully applied to the monitoring engine.";

$lstr['MonitoringWizardCommitErrorSectionTitle']="Configuration Error";
$lstr['MonitoringWizardCommitErrorNotes']="An error occurred while attempting to apply your configuration to the monitoring engine.  Contact your Nagios administrator if this problem persists.";

$lstr['MonitoringWizardPermissionsErrorPageTitle']="Monitoring Wizard";
$lstr['MonitoringWizardPermissionsErrorPageHeader']="Monitoring Wizard - An Error Occurrred";

$lstr['MonitoringWizardPermissionsErrorSectionTitle']="Configuration Request Error";
$lstr['MonitoringWizardPermissionsErrorNotes']="An error occurred while attempting to modify the monitoring engine.  This error occurred because the wizard attempted to modify hosts or services that you do not have permission for.  Contact your Nagios XI administrator for more information.";

$lstr['NoConfigWizardSelectedError']="No wizard selected.";

$lstr['ApplyButton']="Apply";
$lstr['RunThisWizardAgainButton']="Run this monitoring wizard again";
$lstr['RunWizardAgainButton']="Run another monitoring wizard";

$lstr['ApplySettingsButton']="Apply Settings";

$lstr['QuickFind']="Quick Find";

$lstr['AdminPageNotes']="<p>Manage your XI installation with the administrative options available to you in this section.  Make sure you complete any setup tasks that are shown below before using your XI installation.</p>";

$lstr['SecurityCredentialsPageTitle']="Security Credentials";
$lstr['SecurityCredentialsPageHeader']="Security Credentials";

$lstr['SecurityCredentialsPageNotes']="<p>Use this form to reset various internal security credentials used by your XI system.  This is an important step to ensure your XI system does not use default passwords or tokens, which may leave it open to a security breach.</p>";

$lstr['ComponentCredentialsSectionTitle']="Component Credentials";

$lstr['ComponentCredentialsNote']="The credentials listed below are used to manage various aspects of the XI system.  Remember these passwords!";

$lstr['SubsystemCredentialsSectionTitle']="Sub-System Credentials";

$lstr['SubsystemCredentialsNote']="<p>You do not need to remember the credentials below, as they are only used internally by the XI system.</p>";

$lstr['SubsystemTicketText']="XI Subsystem Ticket";
$lstr['UpdateCredentialsButton']="Update Credentials";
$lstr['CurrentText']="Current";
$lstr['ConfigManagerBackendPasswordText']="Config Manager Backend Password";
$lstr['ConfigManagerAdminPasswordText']="New Config Manager Admin Password";
$lstr['ConfigManagerAdminUsernameText']="Admin Username";

$lstr["NoSubsystemTicketError"]="No subsystem ticket.";
$lstr["NoConfigBackendPasswordError"]="No config backend password.";

$lstr['SecurityCredentialsUpdatedText']="Security credentials updated successfully.";

$lstr['NagiosCoreBackendPasswordText']="Nagios Core Backend Password";

$lstr["NoNagiosCoreBackendPasswordError"]="No Nagios Core backend password.";

$lstr['AuditLogPageTitle']="Audit Log";
$lstr['AuditLogPageHeader']="Audit Log";
$lstr['AuditLogPageNotes']="The audit log provides admins with a record of changes that occur on the XI system, which is useful for ensuring your organization meets compliance requirements.";

$lstr['CoreConfigSnapshotsPageTitle']="Monitoring Configuration Snapshots";
$lstr['CoreConfigSnapshotsPageHeader']="Monitoring Configuration Snapshots";
$lstr['CoreConfigSnapshotsPageNotes']="The latest configuration snapshots of the XI monitoring engine are shown below.  Download the most recent snapshots as backups, or get vital information for troubleshooting configuration errors.";

$lstr['MonitoringPluginsPageTitle']="Monitoring Plugins";
$lstr['MonitoringPluginsPageHeader']="Monitoring Plugins";
$lstr['MonitoringPluginsPageNotes']="Manage the monitoring plugins and scripts that are installed on this system.  Use caution when deleting plugins or scripts, as they may cause your monitoring system to generate errors.";

$lstr["SelectFileBoxText"]="Browse File";
$lstr["UploadNewPluginBoxText"]="Upload A New Plugin";

$lstr['PluginUploadedText']="New plugin was installed successfully.";
$lstr['PluginUploadFailedText']="Plugin could not be installed - directory permissions may be incorrect.";

$lstr['PluginDeletedText']="Plugin deleted.";
$lstr['PluginDeleteFailedText']="Plugin delete failed - directory permissions may be incorrect.";
$lstr['NoPluginUploadedText']="No plugin selected for upload.";

$lstr['FilePermsTableHeader']="Permissions";
$lstr['FileOwnerTableHeader']="Owner";
$lstr['FileGroupTableHeader']="Group";

$lstr['ManageConfigWizardsPageTitle']="Manage Configuration Wizards";
$lstr['ManageConfigWizardsPageHeader']="Manage Configuration Wizards";
$lstr['ManageConfigWizardsPageNotes']="Manage the configuration wizards that are installed on this system and available to users under the <a href='../config/'>configuration</a> menu.  Need a custom configuration wizard created for your organization?  <a href='http://www.nagios.com/contact/' target='_blank'>Contact us</a> for pricing information.";

$lstr["UploadNewWizardBoxText"]="Upload A New Wizard";
$lstr['UploadWizardButton']="Upload Wizard";

$lstr['WizardNameTableHeader']="Wizard";
$lstr['WizardTypeTableHeader']="Wizard Type";

$lstr['NoWizardUploadedText']="No wizard selected for upload.";
$lstr['WizardUploadFailedText']="Wizard upload failed.";
$lstr['WizardScheduledForInstallText']="Wizard scheduled for installation.";
$lstr['WizardInstalledText']="Wizard installed.";
$lstr['WizardInstallFailedText']="Wizard installation failed.";
$lstr['WizardPackagingTimedOutText']="Wizard packaging timed out.";
$lstr['WizardScheduledForInstallationText']="Wizard scheduled for installation.";
$lstr['WizardDeletedText']="Wizard deleted.";
$lstr['WizardScheduledForDeletionText']="Wizard scheduled for deletion.";

$lstr['ManageDashletsPageTitle']="Manage Dashlets";
$lstr['ManageDashletsPageHeader']="Manage Dashlets";
$lstr['ManageDashletsPageNotes']="Manage the dashlets that are installed on this system and available to users.  Need a custom dashlet created for your organization?  <a href='http://www.nagios.com/contact/' target='_blank'>Contact us</a> for pricing information.";



$lstr['UploadNewDashletBoxText']="Upload a New Dashlet";
$lstr['UploadDashletButton']="Upload Dashlet";

$lstr['DashletNameTableHeader']="Dashlet";

$lstr['DashletScheduledForInstallationText']="Dashlet scheduled for installation.";
$lstr['DashletUploadFailedText']="Dashlet upload failed.";
$lstr['DashletPackagingTimedOutText']="Dashlet packaging timed out.";
$lstr['DashletDeletedText']="Dashlet deleted.";
$lstr['DashletScheduledForDeletionText']="Dashlet scheduled for deletion.";
$lstr['DashletInstalledText']="Dashlet installed.";
$lstr['DashletInstallFailedText']="Dashlet installation failed.";

$lstr['ManageComponentsPageTitle']="Manage Components";
$lstr['ManageComponentsPageHeader']="Manage Components";
$lstr['ManageComponentsPageNotes']="Manage the components that are installed on this system.  Need a custom component created to extend Nagios XI's capabilities?  <a href='http://www.nagios.com/contact/' target='_blank'>Contact us</a> for pricing information.";

$lstr['ComponentDeletedText']="Component deleted.";
$lstr['ComponentScheduledForDeletionText']="Component scheduled for delettion.";
$lstr['ComponentUploadFailedText']="Component upload failed.";
$lstr['ComponentScheduledForInstallationText']="Component scheduled for installation.";
$lstr['ComponentInstalledText']="Component installed.";
$lstr['ComponentInstallFailedText']="Component installation failed.";
$lstr['ComponentPackagingTimedOutText']="Component packaging timed out.";

$lstr['ConfigSnapshotDeletedText']="Config snapshot deleted.";
$lstr['ConfigSnapshotScheduledForDeletionText']="Config snapshot deleted.";

$lstr["UploadNewComponentBoxText"]="Upload a New Component";
$lstr['UploadComponentButton']="Upload Component";

$lstr['ComponentNameTableHeader']="Component";
$lstr['ComponentTypeTableHeader']="Type";
$lstr['ComponentSettingsTableHeader']="Settings";

$lstr['ConfigureComponentPageTitle']="Component Configuration";
$lstr['ConfigureComponentPageHeader']="Component Configuration";

$lstr['ComponentSettingsUpdatedText']="Component settings updated.";

$lstr['ErrorSubmittingCommandText']="Error submitting command.";

$lstr['NotificationTestPageTitle']="Send Test Notifications";
$lstr['NotificationTestPageHeader']="Send Test Notifications";
$lstr['NotificationTestPageNotes']="Click the button below to send test notifications to your email and/or mobile phone.";

$lstr['SendTestNotificationsButton']="Send Test Notifications";

$lstr['MailSettingsPageTitle']="Mail Settings";
$lstr['MailSettingsPageHeader']="Mail Settings";
$lstr['MailSettingsPageMessage']="Modify the settings used by your Nagios XI system for sending email alerts and informational messages.<br><b>Note:</b> Mail messages may fail to be delivered if your XI server does not have a valid DNS name.";

$lstr['MailSettingsUpdatedText']="Mail settings updated.";

$lstr['GeneralMailSettingsSectionTitle']="General Mail Settings";
$lstr['MailMethodBoxText']="Mail Method";
$lstr['MailFromAddressBoxText']="Send Mail From";

$lstr['SMTPSettingsSectionTitle']="SMTP Settings";

$lstr['SMTPHostBoxText']="Host";
$lstr['SMTPPortBoxText']="Port";
$lstr['SMTPUsernameBoxText']="Username";
$lstr['SMTPPasswordBoxText']="Password";
$lstr['SMTPSecurityBoxText']="Security";

$lstr['NoFromAddressError']="No from address specified.";
$lstr['NoSMTPHostError']="No SMTP host specified.";
$lstr['NoSMTPPortError']="No SMTP port specified.";

$lstr['EmailTestPageTitle']="Test Email Settings";
$lstr['EmailTestPageHeader']="Test Email Settings";
$lstr['EmailTestPageMessage']="Use this to test your mail settings.";
$lstr['SendTestEmailButton']="Send Test Email";

$lstr['NoPerformanceGraphsFoundForServiceText']="No performance graphs were found for this service.";
$lstr['NoPerformanceGraphsFoundForHostText']="No performance graphs were found for this host.";

$lstr['ServiceDetailsOverviewTab']="Overview";
$lstr['ServiceDetailsAdvancedTab']="Advanced";
$lstr['ServiceDetailsConfigureTab']="Configure";
$lstr['ServiceDetailsPerformanceGraphsTab']="Performance Graphs";

$lstr['HostDetailsOverviewTab']="Overview";
$lstr['HostDetailsAdvancedTab']="Advanced";
$lstr['HostDetailsConfigureTab']="Configure";
$lstr['HostDetailsPerformanceGraphsTab']="Performance Graphs";

$lstr['MonitoringProcessPageTitle']="Monitoring Process";
$lstr['MonitoringProcessPageHeader']="Monitoring Process";

$lstr['MonitoringPerformancePageTitle']="Monitoring Performance";
$lstr['MonitoringPerformancePageHeader']="Monitoring Performance";


$lstr['AcknowledgementCommentBoxText']="Your Comment";

$lstr['NetworkOutagesPageTitle']="Network Outages";
$lstr['NetworkOutagesPageHeader']="Network Outages";

$lstr['ViewHostgroupOverviewAlt']="View Hostgroup Overview";
$lstr['ViewHostgroupSummaryAlt']="View Hostgroup Summary";
$lstr['ViewHostgroupGridAlt']="View Hostgroup Grid";
$lstr['ViewHostgroupServiceStatusAlt']="View Hostgroup Service Details";
$lstr['ViewHostgroupCommandsAlt']="View Hostgroup Commands";

$lstr['ViewServicegroupOverviewAlt']="View Servicegroup Overview";
$lstr['ViewServicegroupSummaryAlt']="View Servicegroup Summary";
$lstr['ViewServicegroupGridAlt']="View Servicegroup Grid";
$lstr['ViewServicegroupServiceStatusAlt']="View Servicegroup Service Details";
$lstr['ViewServicegroupCommandsAlt']="View Servicegroup Commands";

$lstr['StatusMapPageTitle']="Network Status Map";
$lstr['StatusMapPageHeader']="Network Status Map";

$lstr['ViewStatusMapTreeAlt']="View Tree Map";
$lstr['ViewStatusMapBalloonAlt']="View Balloon Map";

$lstr['CommentsPageTitle']="Acknowledgements and Comments";
$lstr['CommentsPageHeader']="Acknowledgements and Comments";

$lstr['ConfirmDeleteServicePageTitle']="Confirm Service Deletion";
$lstr['ConfirmDeleteServicePageHeader']="Confirm Service Deletion";
$lstr['ConfirmDeleteServicePageNotes']="Are you sure you want to delete this service and remove it from the monitoring configuration?";

$lstr['DeleteServiceErrorPageTitle']="Service Deletion Error";
$lstr['DeleteServiceErrorPageHeader']="Service Deletion Error";

$lstr['ServiceDeleteScheduledPageTitle']="Service Deletion Scheduled";
$lstr['ServiceDeleteScheduledPageHeader']="Service Deletion Scheduled";

$lstr['ConfirmDeleteHostPageTitle']="Confirm Host Deletion";
$lstr['ConfirmDeleteHostPageHeader']="Confirm Host Deletion";
$lstr['ConfirmDeleteHostPageNotes']="Are you sure you want to delete this host and remove it from the monitoring configuration?";

$lstr['DeleteHostErrorPageTitle']="Host Deletion Error";
$lstr['DeleteHostErrorPageHeader']="Host Deletion Error";

$lstr['HostDeleteScheduledPageTitle']="Host Deletion Scheduled";
$lstr['HostDeleteScheduledPageHeader']="Host Deletion Scheduled";

$lstr['CreateUserAsContactBoxTitle']="Create as Monitoring Contact";

$lstr['UserIsNotContactNotificationPrefsErrorMessage']="Management of notification preferences is not available because your account is not configured to be a monitoring contact.  Contact your Nagios XI administrator for details.";
$lstr['UserIsNotContactNotificationMessagesErrorMessage']="Management of notification preferences is not available for your account.  Contact your Nagios XI administrator for details.";
$lstr['UserIsNotContactNotificationTestErrorMessage']="Testing notification messages is not available for your account.  Contact your Nagios XI administrator for details.";

$lstr['ReconfigureServicePageTitle']="Configure Service";
$lstr['ReconfigureServicePageHeader']="Configure Service";

$lstr['ReconfigureServiceCompletePageTitle']="Configure Service";
$lstr['ReconfigureServiceCompletePageHeader']="Configure Service";

$lstr['ReconfigureHostPageTitle']="Configure Host";
$lstr['ReconfigureHostPageHeader']="Configure Host";

$lstr['ReconfigureHostCompletePageTitle']="Configure Host";
$lstr['ReconfigureHostCompletePageHeader']="Configure Host";

$lstr['ReconfigureServiceSuccessSectionTitle']="Service Re-Configuration Successful";
$lstr['ReconfigureServiceSuccessNotes']="The service has successfully been re-configured with the new settings.";

$lstr['ReconfigureServiceErrorSectionTitle']="Service Re-Configuration Failed";
$lstr['ReconfigureServiceErrorNotes']="A failure occurred while attempting to re-configure the service with the new settings.";


$lstr['ReconfigureHostSuccessSectionTitle']="Host Re-Configuration Successful";
$lstr['ReconfigureHostSuccessNotes']="The host has successfully been re-configured with the new settings.";

$lstr['ReconfigureHostErrorSectionTitle']="Host Re-Configuration Failed";
$lstr['ReconfigureHostErrorNotes']="A failure occurred while attempting to re-configure the host with the new settings.";

$lstr['UpdatesPageTitle']="Updates";
$lstr['UpdatesPageHeader']="Updates";
$lstr['UpdatesPageNotes']="Ensure your IT infrastructure is monitored effectively by keeping up with the latest updates to Nagios XI.  Visit <a href='http://www.nagios.com/products/nagiosxi/' target='_blank'>www.nagios.com</a> to get the latest versions of Nagios XI.";

$lstr['NotificationMethodsPageTitle']="Notification Methods";
$lstr['NotificationMethodsPageHeader']="Notification Methods";
$lstr['NotificationMethodsMessage']="Select the methods by which you'd like to receive host and service alerts.";

$lstr['NotificationsMethodsUpdatedText']="Notification methods updated.";

$lstr['BuiltInNotificationMethodsSectionTitle']="Built-In Notification Methods";
$lstr['AdditionalNotificationMethodsSectionTitle']="Additional Notification Methods";

$lstr['NotificationMethodEmailTitle']="Email";
$lstr['NotificationMethodEmailDescription']="Receive alerts via email.";

$lstr['NotificationMobileTextMessageTitle']="Mobile Phone Text Message";
$lstr['NotificationMobileTextMessageDescription']="Receive text alerts to your cellphone.";

$lstr['NoAdditionalNotificationMethodsInstalledNote']="No additional notification methods have been installed or enabled by the administrator.";


$lstr['UpgradeButton']="Finish Upgrade";

$lstr['UpgradePageTitle']="Upgrade";
$lstr['UpgradePageHeader']="Upgrade";
$lstr['UpgradePageMessage']="Your Nagios XI instance requires some modifications to complete the upgrade process.  Don't worry - its easy.";

$lstr['UpgradeCompletePageTitle']="Upgrade Complete";
$lstr['UpgradeCompletePageHeader']="Upgrade Complete";
$lstr['UpgradeCompletePageMessage']="Congratulations!  Your Nagios XI upgrade has completed successfully.";

$lstr['RecurringDowntimePageTitle']="Recurring Scheduled Downtime";
$lstr['RecurringDowntimePageHeader']="Recurring Scheduled Downtime";
$lstr['RecurringDowntimePageNotes']="Scheduled downtime definitions that are designed to repeat (recur) at set intervals are shown below.  The next schedule for each host/service are added to the monitoring engine when the cron runs at the top of the hour.";

$lstr['DowntimePageTitle']="Scheduled Downtime";
$lstr['DowntimePageHeader']="Scheduled Downtime";

$lstr['ConfigPermsCheckPageTitle']="Config File Permissions Check";
$lstr['ConfigPermsCheckPageHeader']="Config File Permissions Check";

$lstr['TacPageTitle']="Tactical Overview";
$lstr['TacPageHeader']="Tactical Overview";

$lstr['MobileCarriersPageTitle']="Mobile Carriers";
$lstr['MobileCarriersPageHeader']="Mobile Carriers";
$lstr['MobileCarriersPageMessage']="Manage the mobile carrier settings that can be used for email-to-text mobile notifications.  Note: The <i>%number%</i> macro in the address format will be replaced with the user's phone number.";
$lstr['MobileCarriersUpdatedText']="Mobile carriers updated.";

$lstr['DataTransferPageTitle']="Check Data Transfer";
$lstr['DataTransferPageHeader']="Check Data Transfer";
$lstr['DataTransferOverviewPageNotes']="Configure settings for transferring host and service check results to and from this Nagios XI server.";

$lstr['OutboundDataTransferPageTitle']="Outbound Check Transfer Settings";
$lstr['OutboundDataTransferPageHeader']="Outbound Check Transfer Settings";

$lstr['InboundDataTransferPageTitle']="Inbound Check Transfer Settings";
$lstr['InboundDataTransferPageHeader']="Inbound Check Transfer Settings";

$lstr['ToolsPageTitle']="Tools";

$lstr['PerformanceSettingsPageTitle']="Performance Settings";
$lstr['PerformanceSettingsPageHeader']="Performance Settings";
$lstr['PerformanceSettingsUpdatedText']="Performance settings updated";

$lstr['DashletRefreshMultiplierText']="Dashlet Refresh Multiplier";

$lstr['MyReportsPageTitle']="My Reports";
$lstr['MyReportsPageHeader']="My Reports";

$lstr['AddToMyReportsPageTitle']="Add Report";
$lstr['AddToMyReportsPageHeader']="Add Report";

$lstr['SaveReportButton']="Save Report";

$lstr['ActivationPageTitle']="Product Activation";
$lstr['ActivationPageHeader']="Product Activation";
$lstr['ActivationPageMessage']="<p>You must activate your license key in order to access certain features of Nagios XI.  You can obtain an activation code at <a href='http://www.nagios.com/activate/' target='_blank'>http://www.nagios.com/activate</a></p>";

$lstr['ActivationKeySectionTitle']="Activation Information";
$lstr['ActivationKeyText']="Activation Key";
$lstr['InvalidActivationKeyError']="Invalid activation key.";

$lstr['LicenseActivationSectionTitle']="License Activation";
$lstr['ActivationKeyUpdatedText']="Activation key accepted.  Thank you!";

$lstr['ActivateKeyButton']="Activate";

$lstr['MissingObjectsPageTitle']="Unconfigured Objects";
$lstr['MissingObjectsPageHeader']="Unconfigured Objects";

$lstr['AutoLoginPageTitle']="Automatic Login";
$lstr['AutoLoginPageHeader']="Automatic Login";
$lstr['AutoLoginPageNotes']="These options allow you to configure a user account that should be used to automatically login visitors.  Visitors can logout of the default account and into their own if they wish.";
$lstr['AutoLoginButton']="Auto-Login";

$lstr['OptionsUpdatedText']="Options updated.";

$lstr['FinishButton']="Finish";

$lstr['MIBsPageTitle']="SNMP MIBs";
$lstr['MIBsPageHeader']="SNMP MIBs";
$lstr['MIBsPageNotes']="Manage the SNMP MIBs installed on this server.";

$lstr["UploadNewMIBBoxText"]="Upload A New MIB";
$lstr['UploadMIBButton']="Upload MIB";
$lstr['MIBUploadedText']="New MIB was installed successfully.";
$lstr['MIBUploadFailedText']="MIB could not be installed - directory permissions may be incorrect.";
$lstr['MIBDeletedText']="MIB deleted.";
$lstr['MIBDeleteFailedText']="MIB delete failed - directory permissions may be incorrect.";
$lstr['NoMIBUploadedText']="No MIB selected for upload.";
$lstr['MIBTableHeader']="MIB";

$lstr['GraphTemplatesPageTitle']="Graph Templates";
$lstr['GraphTemplatesPageHeader']="Graph Templates";
$lstr['GraphTemplatesPageNotes']="Manage the templates used to generate performance graphs.";

$lstr["UploadNewGraphTemplateBoxText"]="Upload A New Template";
$lstr['UploadGraphTemplateButton']="Upload Template";
$lstr['GraphTemplateUploadedText']="New graph template was installed successfully.";
$lstr['GraphTemplateUploadFailedText']="Graph template could not be installed - directory permissions may be incorrect.";
$lstr['GraphTemplateDeletedText']="Graph template deleted.";
$lstr['GraphTemplateDeleteFailedText']="Graph template delete failed - directory permissions may be incorrect.";
$lstr['NoGraphTemplateUploadedText']="No template selected for upload.";
$lstr['GraphTemplateDirTableHeader']="Directory";

$lstr['EditGraphTemplatePageTitle']="Edit Template";
$lstr['EditGraphTemplatePageHeader']="Edit Template";
$lstr['EditGraphTemplatePageNotes']="";

$lstr['SaveButton']="Save";
$lstr['ApplyButton']="Apply";

$lstr['FileWriteErrorText']="Error writing to file.";
$lstr['FileSavedText']="File saved successfully.";

$lstr['MyToolsPageTitle']="My Tools";
$lstr['MyToolsPageHeader']="My Tools";

$lstr['AddToMyToolsPageTitle']="Add Tool";
$lstr['AddToMyToolsPageHeader']="Add Tool";

$lstr['EditMyToolsPageTitle']="Edit Tool";
$lstr['EditMyToolsPageHeader']="Edit Tool";


$lstr['ToolsPageTitle']="Tools";
$lstr['ToolsPageHeader']="Tools";


$lstr['CommonToolsPageTitle']="Common Tools";
$lstr['CommonToolsPageHeader']="Common Tools";

$lstr['AddToCommonToolsPageTitle']="Add Tool";
$lstr['AddToCommonToolsPageHeader']="Add Tool";

$lstr['EditCommonToolsPageTitle']="Edit Tool";
$lstr['EditCommonToolsPageHeader']="Edit Tool";

$lstr['SchedulePageAlt']="Schedule Page";

$lstr['ConfigSnapshotRestoredText']="Configuration snapshot restored.";
$lstr['ConfigSnapshotScheduledForRestoreText']="Configure snapshot restore has been scheduled.";

$lstr['RestoreAlt']="Restore";
?>
