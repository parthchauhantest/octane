--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
--SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: if_command_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('if_command_id_seq', 20, false);


--
-- Name: if_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('if_meta_id_seq', 16, false);


--
-- Name: if_option_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('if_option_id_seq', 99, false);


--
-- Name: if_sysstat_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('if_sysstat_id_seq', 15, false);


--
-- Name: if_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('if_user_id_seq', 14, false);


--
-- Name: if_usermeta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('if_usermeta_id_seq', 142, false);


--
-- Name: xi_commands_command_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('xi_commands_command_id_seq', 53, true);


--
-- Name: xi_meta_meta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('xi_meta_meta_id_seq', 21, true);


--
-- Name: xi_options_option_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('xi_options_option_id_seq', 33, true);


--
-- Name: xi_sysstat_sysstat_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('xi_sysstat_sysstat_id_seq', 28, true);


--
-- Name: xi_usermeta_usermeta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('xi_usermeta_usermeta_id_seq', 170, true);


--
-- Name: xi_users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: nagiosxi
--

--SELECT pg_catalog.setval('xi_users_user_id_seq', 25, true);


--
-- Data for Name: xi_commands; Type: TABLE DATA; Schema: public; Owner: nagiosxi
--



--
-- Data for Name: xi_meta; Type: TABLE DATA; Schema: public; Owner: nagiosxi
--




--
-- Data for Name: xi_options; Type: TABLE DATA; Schema: public; Owner: nagiosxi
--

INSERT INTO xi_options VALUES (25, 'subsystem_ticket', '95n2sn26');
INSERT INTO xi_options VALUES (26, 'admin_name', 'Nagios XI Admin');
INSERT INTO xi_options VALUES (27, 'admin_email', 'root@localhost');
INSERT INTO xi_options VALUES (28, 'url', 'http://localhost/nagiosxi/');
INSERT INTO xi_options VALUES (29, 'default_language', 'en');
INSERT INTO xi_options VALUES (30, 'default_theme', 'none');
INSERT INTO xi_options VALUES (31, 'auto_update_check', '1');
INSERT INTO xi_options VALUES (32, 'default_date_format', '1');
INSERT INTO xi_options VALUES (33, 'default_number_format', '1');


--
-- Data for Name: xi_sysstat; Type: TABLE DATA; Schema: public; Owner: nagiosxi
--

INSERT INTO xi_sysstat VALUES (28, 'dbmaint', 'a:1:{s:10:"last_check";i:1255437004;}', '2009-10-13 07:30:04.1862');
INSERT INTO xi_sysstat VALUES (18, 'cleaner', 'a:1:{s:10:"last_check";i:1255437004;}', '2009-10-13 07:30:04.729231');
INSERT INTO xi_sysstat VALUES (25, 'nom', 'a:1:{s:10:"last_check";i:1255437004;}', '2009-10-13 07:30:04.206198');
INSERT INTO xi_sysstat VALUES (16, 'reportengine', 'a:1:{s:10:"last_check";i:1255437033;}', '2009-10-13 07:30:33.584388');
INSERT INTO xi_sysstat VALUES (19, 'dbbackend', 'a:5:{s:12:"last_checkin";s:19:"2009-10-13 07:29:33";s:15:"bytes_processed";s:8:"36359614";s:17:"entries_processed";s:6:"305085";s:12:"connect_time";s:19:"2009-10-12 18:42:59";s:15:"disconnect_time";s:19:"0000-00-00 00:00:00";}', '2009-10-13 07:30:26.779238');
INSERT INTO xi_sysstat VALUES (20, 'daemons', 'a:3:{s:10:"nagioscore";a:4:{s:6:"daemon";s:6:"nagios";s:6:"output";s:32:"nagios (pid 32440) is running...";s:11:"return_code";i:0;s:6:"status";i:0;}s:3:"pnp";a:4:{s:6:"daemon";s:4:"npcd";s:6:"output";s:13:"NPCD running.";s:11:"return_code";i:0;s:6:"status";i:0;}s:8:"ndoutils";a:4:{s:6:"daemon";s:6:"ndo2db";s:6:"output";s:32:"ndo2db (pid 15293) is running...";s:11:"return_code";i:0;s:6:"status";i:0;}}', '2009-10-13 07:30:26.936026');
INSERT INTO xi_sysstat VALUES (21, 'nagioscore', 'a:9:{s:15:"hostcheckevents";a:3:{s:4:"1min";s:1:"0";s:4:"5min";s:2:"13";s:5:"15min";s:2:"13";}s:18:"servicecheckevents";a:3:{s:4:"1min";s:2:"13";s:4:"5min";s:2:"41";s:5:"15min";s:2:"44";}s:11:"timedevents";a:3:{s:4:"1min";s:2:"42";s:4:"5min";s:3:"176";s:5:"15min";s:3:"185";}s:16:"activehostchecks";a:3:{s:4:"1min";s:1:"0";s:4:"5min";s:2:"13";s:5:"15min";s:2:"13";}s:17:"passivehostchecks";a:3:{s:4:"1min";s:1:"0";s:4:"5min";s:1:"0";s:5:"15min";s:1:"0";}s:19:"activeservicechecks";a:3:{s:4:"1min";s:2:"20";s:4:"5min";s:2:"76";s:5:"15min";s:2:"82";}s:20:"passiveservicechecks";a:3:{s:4:"1min";s:1:"0";s:4:"5min";s:1:"0";s:5:"15min";s:1:"0";}s:19:"activehostcheckperf";a:6:{s:11:"min_latency";s:5:"0.108";s:11:"max_latency";s:5:"0.271";s:11:"avg_latency";s:16:"0.19123076923077";s:18:"min_execution_time";s:7:"0.01779";s:18:"max_execution_time";s:7:"3.02042";s:18:"avg_execution_time";s:16:"0.37582384615385";}s:22:"activeservicecheckperf";a:6:{s:11:"min_latency";s:5:"0.009";s:11:"max_latency";s:4:"0.32";s:11:"avg_latency";s:16:"0.14542424242424";s:18:"min_execution_time";s:7:"0.01816";s:18:"max_execution_time";s:7:"4.12315";s:18:"avg_execution_time";s:16:"0.65895454545455";}}', '2009-10-13 07:30:26.978483');
INSERT INTO xi_sysstat VALUES (22, 'load', 'a:3:{s:5:"load1";s:4:"1.06";s:5:"load5";s:4:"0.90";s:6:"load15";s:4:"0.81";}', '2009-10-13 07:30:27.002811');
INSERT INTO xi_sysstat VALUES (23, 'memory', 'a:6:{s:5:"total";s:3:"501";s:4:"used";s:3:"449";s:4:"free";s:2:"51";s:6:"shared";s:1:"0";s:7:"buffers";s:2:"11";s:6:"cached";s:3:"178";}', '2009-10-13 07:30:27.032445');
INSERT INTO xi_sysstat VALUES (24, 'swap', 'a:3:{s:5:"total";s:4:"1043";s:4:"used";s:2:"74";s:4:"free";s:3:"968";}', '2009-10-13 07:30:27.053822');
INSERT INTO xi_sysstat VALUES (26, 'iostat', 'a:6:{s:4:"user";s:5:"12.40";s:4:"nice";s:4:"1.60";s:6:"system";s:4:"3.40";s:6:"iowait";s:4:"0.80";s:5:"steal";s:4:"0.00";s:4:"idle";s:5:"81.80";}', '2009-10-13 07:30:32.095978');
INSERT INTO xi_sysstat VALUES (27, 'sysstat', 'a:1:{s:10:"last_check";i:1255437026;}', '2009-10-13 07:30:32.143179');
INSERT INTO xi_sysstat VALUES (17, 'cmdsubsys', 'a:1:{s:10:"last_check";i:1255437034;}', '2009-10-13 07:30:34.160423');
INSERT INTO xi_sysstat VALUES (15, 'feedprocessor', 'a:1:{s:10:"last_check";i:1255437034;}', '2009-10-13 07:30:34.104182');


--
-- Data for Name: xi_usermeta; Type: TABLE DATA; Schema: public; Owner: nagiosxi
--

INSERT INTO xi_usermeta VALUES (149, 18, 'userlevel', '255', 1);
INSERT INTO xi_usermeta VALUES (144, 18, 'dashboards', 'a:2:{i:0;a:4:{s:2:"id";s:4:"home";s:5:"title";s:9:"Home Page";s:4:"opts";a:1:{s:10:"background";s:6:"ffffff";}s:8:"dashlets";a:3:{i:1;a:5:{s:2:"id";s:8:"t70nntm8";s:4:"name";s:19:"xicore_server_stats";s:5:"title";s:12:"Server Stats";s:4:"opts";a:6:{s:3:"top";i:324;s:4:"left";i:4;s:6:"zindex";s:1:"4";s:6:"pinned";i:1;s:6:"height";i:382;s:5:"width";i:259;}s:4:"args";a:0:{}}i:6;a:5:{s:2:"id";s:8:"euohqtr8";s:4:"name";s:18:"xicore_admin_tasks";s:5:"title";s:20:"Administrative Tasks";s:4:"opts";a:6:{s:3:"top";i:43;s:4:"left";i:2;s:6:"zindex";s:1:"2";s:6:"height";i:244;s:5:"width";i:330;s:6:"pinned";i:1;}s:4:"args";a:0:{}}i:7;a:5:{s:2:"id";s:8:"n745sfa0";s:4:"name";s:22:"xicore_getting_started";s:5:"title";s:21:"Getting Started Guide";s:4:"opts";a:6:{s:3:"top";i:42;s:4:"left";i:351;s:6:"zindex";s:1:"3";s:6:"height";i:456;s:5:"width";i:337;s:6:"pinned";i:1;}s:4:"args";a:0:{}}}}i:1;a:4:{s:2:"id";s:8:"d7mccig7";s:5:"title";s:15:"Empty Dashboard";s:4:"opts";a:1:{s:10:"background";s:6:"ffffff";}s:8:"dashlets";a:0:{}}}', 0);



--
-- Data for Name: xi_users; Type: TABLE DATA; Schema: public; Owner: nagiosxi
--

INSERT INTO xi_users VALUES (18, 'nagiosadmin', '40be4e59b9a2a2b5dffb918c0e86b3d7', 'Nagios Admin', 'root@localhost', '1234', 1);


--
-- PostgreSQL database dump complete
--

