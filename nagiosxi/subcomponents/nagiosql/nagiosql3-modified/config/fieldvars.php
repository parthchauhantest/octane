<?php
///////////////////////////////////////////////////////////////////////////////
//
// NagiosQL
//
///////////////////////////////////////////////////////////////////////////////
//
// (c) 2008, 2009 by Martin Willisegger
//
// Project   : NagiosQL
// Component : field language variables (for replace in templates)
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: fieldvars.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Feldvariabeln setzen
// ====================
$arrDescription[] = array ("name" => "LANG_DOMAIN",           "string" => gettext("Domain"));
$arrDescription[] = array ("name" => "LANG_DESCRIPTION",        "string" => gettext("Description"));
$arrDescription[] = array ("name" => "LANG_SERVER_NAMEe",         "string" => gettext("Server name"));
$arrDescription[] = array ("name" => "LANG_METHOD",           "string" => gettext("Method"));
$arrDescription[] = array ("name" => "LANG_USERNAME",           "string" => gettext("Username"));
$arrDescription[] = array ("name" => "LANG_PASSWORD",           "string" => gettext("Password"));
$arrDescription[] = array ("name" => "LANG_SERVER_NAME",        "string" => gettext("Server name"));
$arrDescription[] = array ("name" => "LANG_CONFIGURATION_DIRECTORIES",  "string" => gettext("Configuration directories"));
$arrDescription[] = array ("name" => "LANG_BASE_DIRECTORY",       "string" => gettext("Base directory"));
$arrDescription[] = array ("name" => "LANG_HOST_DIRECTORY",       "string" => gettext("Host directory"));
$arrDescription[] = array ("name" => "LANG_SERVICE_DIRECTORY",      "string" => gettext("Service directory"));
$arrDescription[] = array ("name" => "LANG_BACKUP_DIRECTORY",       "string" => gettext("Backup directory"));
$arrDescription[] = array ("name" => "LANG_HOST_BACKUP_DIRECTORY",    "string" => gettext("Host backup directory"));
$arrDescription[] = array ("name" => "LANG_SERVICE_BACKUP_DIRECTORY",   "string" => gettext("Service backup directory"));
$arrDescription[] = array ("name" => "LANG_NAGIOS_COMMAND_FILE",    "string" => gettext("Nagios command file"));
$arrDescription[] = array ("name" => "LANG_NAGIOS_BINARY_FILE",     "string" => gettext("Nagios binary file"));
$arrDescription[] = array ("name" => "LANG_NAGIOS_PROCESS_FILE",    "string" => gettext("Nagios process file"));
$arrDescription[] = array ("name" => "LANG_NAGIOS_VERSION",       "string" => gettext("Nagios version"));
$arrDescription[] = array ("name" => "LANG_ACCESS_KEY_HOLES",     "string" => gettext("Access key holes"));
$arrDescription[] = array ("name" => "LANG_ACCESS_KEYS",        "string" => gettext("Access keys"));
$arrDescription[] = array ("name" => "LANG_ACTIVE",           "string" => gettext("Active"));
$arrDescription[] = array ("name" => "LANG_REQUIRED",           "string" => gettext("required"));
$arrDescription[] = array ("name" => "LANG_SAVE",             "string" => gettext("Save"));
$arrDescription[] = array ("name" => "LANG_ABORT",            "string" => gettext("Abort"));
$arrDescription[] = array ("name" => "LANG_FUNCTION",           "string" => gettext("Function"));
$arrDescription[] = array ("name" => "LANG_MARKED",           "string" => gettext("Marked"));
$arrDescription[] = array ("name" => "LANG_DO_IT",            "string" => gettext("Do it"));
$arrDescription[] = array ("name" => "LANG_ADD",            "string" => gettext("Add"));
$arrDescription[] = array ("name" => "LANG_FORMCHECK",          "string" => gettext("Formcheck"));
$arrDescription[] = array ("name" => "LANG_SECURE_QUESTION",      "string" => gettext("Secure question"));
$arrDescription[] = array ("name" => "LANG_YES",            "string" => gettext("Yes"));
$arrDescription[] = array ("name" => "LANG_NO",             "string" => gettext("No"));
$arrDescription[] = array ("name" => "LANG_TIME",             "string" => gettext("Time"));
$arrDescription[] = array ("name" => "LANG_USER",             "string" => gettext("User"));
$arrDescription[] = array ("name" => "LANG_IP",             "string" => gettext("IP"));
$arrDescription[] = array ("name" => "LANG_ENTRY",            "string" => gettext("Entry"));
$arrDescription[] = array ("name" => "LANG_FROM",             "string" => gettext("From"));
$arrDescription[] = array ("name" => "LANG_TO",             "string" => gettext("To"));
$arrDescription[] = array ("name" => "LANG_DELETE_LOG_ENTRIES",     "string" => gettext("Delete log entries"));
$arrDescription[] = array ("name" => "LANG_COPY",             "string" => gettext("Copy"));
$arrDescription[] = array ("name" => "LANG_DELETE",           "string" => gettext("Delete"));
$arrDescription[] = array ("name" => "LANG_MODIFY",           "string" => gettext("Modify"));
$arrDescription[] = array ("name" => "LANG_CONFIRM_PASSWORD",       "string" => gettext("Confirm password"));
$arrDescription[] = array ("name" => "LANG_OLD_PASSWORD",         "string" => gettext("Old password"));
$arrDescription[] = array ("name" => "LANG_NEW_PASSWORD",         "string" => gettext("New password"));
$arrDescription[] = array ("name" => "LANG_CHANGE_PASSWORD",      "string" => gettext("Change password"));
$arrDescription[] = array ("name" => "LANG_MENU_PAGE",          "string" => gettext("Menu page"));
$arrDescription[] = array ("name" => "LANG_SEARCH_STRING",        "string" => gettext("Search string"));
$arrDescription[] = array ("name" => "LANG_SEARCH",           "string" => gettext("Search"));
$arrDescription[] = array ("name" => "LANG_WRITE_CONFIG_FILE",      "string" => gettext("Write config file"));
$arrDescription[] = array ("name" => "LANG_DOWNLOAD",           "string" => gettext("Download"));
$arrDescription[] = array ("name" => "LANG_DUPLICATE",          "string" => gettext("Copy"));
$arrDescription[] = array ("name" => "LANG_COMMAND",          "string" => gettext("Command"));
$arrDescription[] = array ("name" => "LANG_COMMAND_LINE",         "string" => gettext("Command line"));
$arrDescription[] = array ("name" => "LANG_COMMAND_TYPE",         "string" => gettext("Command type"));
$arrDescription[] = array ("name" => "LANG_TIME_PERIOD",        "string" => gettext("Time period"));
$arrDescription[] = array ("name" => "LANG_EXCLUDE",          "string" => gettext("Exclude"));
$arrDescription[] = array ("name" => "LANG_TIME_DEFINITIONS",       "string" => gettext("Time definitions"));
$arrDescription[] = array ("name" => "LANG_WEEKDAY",          "string" => gettext("Weekday"));
$arrDescription[] = array ("name" => "LANG_TIME_RANGE",         "string" => gettext("Time range"));
$arrDescription[] = array ("name" => "LANG_TIME_DEFINITION",      "string" => gettext("Time definition"));
$arrDescription[] = array ("name" => "LANG_INSERT",           "string" => gettext("Insert"));
$arrDescription[] = array ("name" => "LANG_MODIFY_SELECTION",       "string" => gettext("Modify selection"));
$arrDescription[] = array ("name" => "LANG_CONTACT_NAME",         "string" => gettext("Contact name"));
$arrDescription[] = array ("name" => "LANG_CONTACT_GROUP",        "string" => gettext("Contact group"));
$arrDescription[] = array ("name" => "LANG_TIME_PERIOD_HOSTS",      "string" => gettext("Time period hosts"));
$arrDescription[] = array ("name" => "LANG_TIME_PERIOD_SERVICES",     "string" => gettext("Time period services"));
$arrDescription[] = array ("name" => "LANG_HOST_OPTIONS",         "string" => gettext("Host options"));
$arrDescription[] = array ("name" => "LANG_SERVICE_OPTIONS",      "string" => gettext("Service options"));
$arrDescription[] = array ("name" => "LANG_HOST_COMMAND",         "string" => gettext("Host command"));
$arrDescription[] = array ("name" => "LANG_SERVICE_COMMAND",      "string" => gettext("Service command"));
$arrDescription[] = array ("name" => "LANG_EMAIL_ADDRESS",        "string" => gettext("EMail address"));
$arrDescription[] = array ("name" => "LANG_PAGER_NUMBER",         "string" => gettext("Pager number"));
$arrDescription[] = array ("name" => "LANG_ADDON_ADDRESS",        "string" => gettext("Addon address"));
$arrDescription[] = array ("name" => "LANG_HOST_NOTIF_ENABLE",     "string" => gettext("Host notif. enable"));
$arrDescription[] = array ("name" => "LANG_SERVICE_NOTIF_ENABLE",    "string" => gettext("Service notif. enable"));
$arrDescription[] = array ("name" => "LANG_CAN_SUBMIT_COMMANDS",    "string" => gettext("Can submit commands"));
$arrDescription[] = array ("name" => "LANG_RETAIN_STATUS_INFO",     "string" => gettext("Retain status info"));
$arrDescription[] = array ("name" => "LANG_RETAIN_NONSTATUS_INFO",    "string" => gettext("Retain nonstatus info"));
$arrDescription[] = array ("name" => "LANG_MEMBERS",          "string" => gettext("Members"));
$arrDescription[] = array ("name" => "LANG_GROUP_MEMBERS",        "string" => gettext("Group members"));
$arrDescription[] = array ("name" => "LANG_COMMON_SETTINGS",      "string" => gettext("Common settings"));
$arrDescription[] = array ("name" => "LANG_TEMPLATE_NAME",        "string" => gettext("Template name"));
$arrDescription[] = array ("name" => "LANG_PARENTS",          "string" => gettext("Parents"));
$arrDescription[] = array ("name" => "LANG_HOST_GROUPS",        "string" => gettext("Host groups"));
$arrDescription[] = array ("name" => "LANG_CHECK_COMMAND",        "string" => gettext("Check command"));
$arrDescription[] = array ("name" => "LANG_COMMAND_VIEW",         "string" => gettext("Command view"));
$arrDescription[] = array ("name" => "LANG_ADDITIONAL_TEMPLATES",     "string" => gettext("Additional templates"));
$arrDescription[] = array ("name" => "LANG_CHECK_SETTINGS",       "string" => gettext("Check settings"));
$arrDescription[] = array ("name" => "LANG_INITIAL_STATE",        "string" => gettext("Initial state"));
$arrDescription[] = array ("name" => "LANG_RETRY_INTERVAL",       "string" => gettext("Retry interval"));
$arrDescription[] = array ("name" => "LANG_MAX_CHECK_ATTEMPTS",     "string" => gettext("Max check attempts"));
$arrDescription[] = array ("name" => "LANG_CHECK_INTERVAL",       "string" => gettext("Check interval"));
$arrDescription[] = array ("name" => "LANG_ACTIVE_CHECKS_ENABLED",    "string" => gettext("Active checks enabled"));
$arrDescription[] = array ("name" => "LANG_PASSIVE_CHECKS_ENABLED",   "string" => gettext("Passive checks enabled"));
$arrDescription[] = array ("name" => "LANG_CHECK_PERIOD",         "string" => gettext("Check period"));
$arrDescription[] = array ("name" => "LANG_FRESHNESS_TRESHOLD",     "string" => gettext("Freshness treshold"));
$arrDescription[] = array ("name" => "LANG_CHECK_FRESHNESS",      "string" => gettext("Check freshness"));
$arrDescription[] = array ("name" => "LANG_OBSESS_OVER_HOST",       "string" => gettext("Obsess over host"));
$arrDescription[] = array ("name" => "LANG_OBSESS_OVER_SERVICE",       "string" => gettext("Obsess over service"));
$arrDescription[] = array ("name" => "LANG_EVENT_HANDLER",        "string" => gettext("Event handler"));
$arrDescription[] = array ("name" => "LANG_EVENT_HANDLER_ENABLED",    "string" => gettext("Event handler enabled"));
$arrDescription[] = array ("name" => "LANG_LOW_FLAP_THRESHOLD",     "string" => gettext("Low flap threshold"));
$arrDescription[] = array ("name" => "LANG_HIGH_FLAP_THRESHOLD",    "string" => gettext("High flap threshold"));
$arrDescription[] = array ("name" => "LANG_FLAP_DETECTION_ENABLED",   "string" => gettext("Flap detection enabled"));
$arrDescription[] = array ("name" => "LANG_FLAP_DETECTION_OPTIONS",   "string" => gettext("Flap detection options"));
$arrDescription[] = array ("name" => "LANG_RETAIN_STATUS_INFORMATION",  "string" => gettext("Retain status information"));
$arrDescription[] = array ("name" => "LANG_RETAIN_NOSTATUS_INFORMATION","string" => gettext("Retain nostatus information"));
$arrDescription[] = array ("name" => "LANG_PROCESS_PERF_DATA",      "string" => gettext("Process perf data"));
$arrDescription[] = array ("name" => "LANG_ALARM_SETTINGS",       "string" => gettext("Alarm settings"));
$arrDescription[] = array ("name" => "LANG_CONTACTS",           "string" => gettext("Contacts"));
$arrDescription[] = array ("name" => "LANG_CONTACT_GROUPS",       "string" => gettext("Contact groups"));
$arrDescription[] = array ("name" => "LANG_NOTIFICATION_PERIOD",    "string" => gettext("Notification period"));
$arrDescription[] = array ("name" => "LANG_NOTIFICATION_OPTIONS",     "string" => gettext("Notification options"));
$arrDescription[] = array ("name" => "LANG_NOTIFICATION_INTERVAL",    "string" => gettext("Notification interval"));
$arrDescription[] = array ("name" => "LANG_FIRST_NOTIFICATION_DELAY",   "string" => gettext("First notification delay"));
$arrDescription[] = array ("name" => "LANG_NOTIFICATION_ENABLED",     "string" => gettext("Notification enabled"));
$arrDescription[] = array ("name" => "LANG_STALKING_OPTIONS",       "string" => gettext("Stalking options"));
$arrDescription[] = array ("name" => "LANG_ADDON_SETTINGS",       "string" => gettext("Addon settings"));
$arrDescription[] = array ("name" => "LANG_NOTES",            "string" => gettext("Notes"));
$arrDescription[] = array ("name" => "LANG_VRML_IMAGE",         "string" => gettext("VRML image"));
$arrDescription[] = array ("name" => "LANG_NOTES_URL",          "string" => gettext("Notes URL"));
$arrDescription[] = array ("name" => "LANG_STATUS_IMAGE",         "string" => gettext("Status image"));
$arrDescription[] = array ("name" => "LANG_ICON_IMAGE",         "string" => gettext("Icon image"));
$arrDescription[] = array ("name" => "LANG_ACTION_URL",         "string" => gettext("Action URL"));
$arrDescription[] = array ("name" => "LANG_2D_COORDS",          "string" => gettext("2D coords"));
$arrDescription[] = array ("name" => "LANG_3D_COORDS",          "string" => gettext("3D coords"));
$arrDescription[] = array ("name" => "LANG_ICON_IMAGE_ALT_TEXT",    "string" => gettext("Icon image ALT text"));
$arrDescription[] = array ("name" => "LANG_STANDARD",           "string" => gettext("standard"));
$arrDescription[] = array ("name" => "LANG_ON",             "string" => gettext("on"));
$arrDescription[] = array ("name" => "LANG_OFF",            "string" => gettext("off"));
$arrDescription[] = array ("name" => "LANG_SKIP",             "string" => gettext("skip"));
$arrDescription[] = array ("name" => "LANG_FREE_VARIABLE_DEFINITIONS",  "string" => gettext("Free variable definitions"));
$arrDescription[] = array ("name" => "LANG_VARIABLE_NAME",        "string" => gettext("Variable name"));
$arrDescription[] = array ("name" => "LANG_VARIABLE_VALUE",       "string" => gettext("Variable value"));
$arrDescription[] = array ("name" => "DELETE",              "string" => gettext("Delete"));
$arrDescription[] = array ("name" => "DUPLICATE",             "string" => gettext("Copy"));
$arrDescription[] = array ("name" => "INFO",              "string" => gettext("Information"));
$arrDescription[] = array ("name" => "WRITE_CONFIG",          "string" => gettext("Write config file"));
$arrDescription[] = array ("name" => "LANG_DELETESINGLE",         "string" => gettext("Do you really want to delete this database entry:"));
$arrDescription[] = array ("name" => "LANG_DELETEOK",           "string" => gettext("Do you really want to delete all marked entries?"));
$arrDescription[] = array ("name" => "LANG_MARKALL",          "string" => gettext("Mark all shown datasets"));
$arrDescription[] = array ("name" => "LANG_FILE",             "string" => gettext("File"));
$arrDescription[] = array ("name" => "LANG_WRITE_CONF_ALL",       "string" => gettext("Write all config files"));
$arrDescription[] = array ("name" => "LANG_ADDRESS",          "string" => gettext("Address"));
$arrDescription[] = array ("name" => "LANG_DISPLAY_NAME",         "string" => gettext("Display name"));
$arrDescription[] = array ("name" => "LANG_USE_THIS_AS_TEMPLATE",     "string" => gettext("Use this configuration as template"));
$arrDescription[] = array ("name" => "LANG_GENERIC_NAME",         "string" => gettext("Generic name"));
$arrDescription[] = array ("name" => "LANG_HOST_NAME",          "string" => gettext("Host name"));
$arrDescription[] = array ("name" => "FILL_ALLFIELDS",          "string" => gettext("Please fill in all fields marked with an *"));
$arrDescription[] = array ("name" => "FILL_ILLEGALCHARS",         "string" => gettext("The following field contains not permitted characters:"));
$arrDescription[] = array ("name" => "FILL_BOXES",            "string" => gettext("Please check at least one option from:"));
$arrDescription[] = array ("name" => "LANG_HOSTGROUP_NAME",       "string" => gettext("Host group name"));
$arrDescription[] = array ("name" => "LANG_HOSTGROUP_MEMBERS",      "string" => gettext("Host group members"));
$arrDescription[] = array ("name" => "LANG_HOSTS",            "string" => gettext("Hosts"));
$arrDescription[] = array ("name" => "LANG_SERVICE_DESCRIPTION",    "string" => gettext("Service description"));
$arrDescription[] = array ("name" => "LANG_SERVICEGROUPS",        "string" => gettext("Service groups"));
$arrDescription[] = array ("name" => "LANG_IS_VOLATILE",        "string" => gettext("Is volatile"));
$arrDescription[] = array ("name" => "LANG_PARALLELIZE_CHECK",      "string" => gettext("Parallelize checks"));
$arrDescription[] = array ("name" => "LANG_CONFIGFILTER",         "string" => gettext("Config name filter"));
$arrDescription[] = array ("name" => "LANG_CONFIG_NAME",        "string" => gettext("Config name"));
$arrDescription[] = array ("name" => "LANG_IMPORT_DIRECTORY",       "string" => gettext("Import directory"));
$arrDescription[] = array ("name" => "LANG_INSERT_ALL_VARIABLE",    "string" => gettext("Please insert a variable name and a variable definition"));
$arrDescription[] = array ("name" => "LANG_MUST_BUT_TEMPLATE",      "string" => "<b>".gettext("Warning:")."<\/b> ".gettext("You have not filled in some required fields!<br><br>If this values are set by a template, you can save anyway - otherwise you will get an invalid configuration!"));
$arrDescription[] = array ("name" => "LANG_TPLNAME",          "string" => gettext("Template name"));
$arrDescription[] = array ("name" => "LANG_NAGIOS_BASEDIR",       "string" => gettext("Nagios base directory"));
$arrDescription[] = array ("name" => "LANG_WRITE_CONFIG",         "string" => gettext("Write config"));
$arrDescription[] = array ("name" => "FILL_ARGUMENTS",          "string" => "<b>".gettext("Warning:")."<\/b> ".gettext("You have not filled in all command arguments (ARGx) for your selected command!<br><br>If this arguments are optional, you can save anyway - otherwise you will get an invalid configuration!"));
$arrDescription[] = array ("name" => "LANG_SERVICEGROUP_MEMBERS",     "string" => gettext("Service group members"));
$arrDescription[] = array ("name" => "LANG_SERVICEGROUP_NAME",      "string" => gettext("Service group name"));
$arrDescription[] = array ("name" => "LANG_DEPENDHOSTS",        "string" => gettext("Dependent hosts"));
$arrDescription[] = array ("name" => "LANG_DEPENDHOSTGRS",        "string" => gettext("Dependent hostgroups"));
$arrDescription[] = array ("name" => "LANG_HOSTGROUPS",         "string" => gettext("Hostgroups"));
$arrDescription[] = array ("name" => "LANG_INHERIT",          "string" => gettext("Inherit parents"));
$arrDescription[] = array ("name" => "LANG_EXECFAILCRIT",         "string" => gettext("Execution failure criteria"));
$arrDescription[] = array ("name" => "LANG_NOTIFFAILCRIT",        "string" => gettext("Nofification failure criteria"));
$arrDescription[] = array ("name" => "LANG_DEPENDENCY_PERIOD",      "string" => gettext("Dependency period"));
$arrDescription[] = array ("name" => "LANG_ESCALATION_PERIOD",      "string" => gettext("Escalation period"));
$arrDescription[] = array ("name" => "LANG_ESCALATION_OPTIONS",     "string" => gettext("Escalation options"));
$arrDescription[] = array ("name" => "LANG_FIRST_NOTIFICATION",     "string" => gettext("First notification"));
$arrDescription[] = array ("name" => "LANG_LAST_NOTIFICATION",      "string" => gettext("Last notification"));
$arrDescription[] = array ("name" => "LANG_DEPENDSERVICES",       "string" => gettext("Dependent services"));
$arrDescription[] = array ("name" => "LANG_SERVICES",           "string" => gettext("Services"));
$arrDescription[] = array ("name" => "LANG_HELP",           "string" => gettext("Help"));
$arrDescription[] = array ("name" => "LANG_CALENDAR",           "string" => gettext("Calendar"));
// weekdays
$arrDescription[] = array ("name" => "LANG_MONDAY",     "string" => gettext("Monday"));
$arrDescription[] = array ("name" => "LANG_TUESDAY",    "string" => gettext("Tuesday"));
$arrDescription[] = array ("name" => "LANG_WEDNESDAY",  "string" => gettext("Wednesday"));
$arrDescription[] = array ("name" => "LANG_THURSDAY",   "string" => gettext("Thursday"));
$arrDescription[] = array ("name" => "LANG_FRIDAY",     "string" => gettext("Friday"));
$arrDescription[] = array ("name" => "LANG_SATURDAY",   "string" => gettext("Saturday"));
$arrDescription[] = array ("name" => "LANG_SUNDAY",     "string" => gettext("Sunday"));
if ($SETS['common']['seldisable'] == 0) {
  $arrDescription[] = array ("name" => "LANG_CTRLINFO",           "string" => gettext("Hold CTRL to select<br>more than one entry"));
} else {
  $arrDescription[] = array ("name" => "LANG_CTRLINFO",           "string" => "&nbsp;");
}
//
// Quick fix for poEdit for dynamically loaded Parameters
// ======================================================
//
// Main menu
gettext('Main page');
gettext('Supervision');
gettext('Alarming');
gettext('Alarming');
gettext('Commands');
gettext('Specialties');
gettext('Tools');
gettext('Administration');
// Submenu
gettext('Hosts');
gettext('Time periods');
gettext('Host templates');
gettext('Contact data');
gettext('Contact groups');
gettext('Services');
gettext('Host groups');
gettext('Service groups');
gettext('Serv. dependency');
gettext('Serv. escalation');
gettext('Host dependency');
gettext('Host escalation');
gettext('Host ext. info');
gettext('Serv. ext. info');
gettext('Data import');
gettext('Delete files');
gettext('User admin');
gettext('Nagios control');
gettext('New password');
gettext('Logbook');
gettext('Nagios config');
gettext('Settings');
gettext('Definitions');
gettext('CGI config');
gettext('Menu access');
gettext('Domains');
gettext('Host templates');
gettext('Service templates');
gettext('Contact templates');
gettext('Help editor');
?>