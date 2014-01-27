--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
-- SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

-- COMMENT ON SCHEMA public IS 'Standard public schema';


SET search_path = public, pg_catalog;

--
-- Name: if_command_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE if_command_id_seq
    START WITH 20
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.if_command_id_seq OWNER TO nagiosxi;

--
-- Name: if_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE if_meta_id_seq
    START WITH 16
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.if_meta_id_seq OWNER TO nagiosxi;

--
-- Name: if_option_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE if_option_id_seq
    START WITH 25
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.if_option_id_seq OWNER TO nagiosxi;

--
-- Name: if_sysstat_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE if_sysstat_id_seq
    START WITH 15
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.if_sysstat_id_seq OWNER TO nagiosxi;

--
-- Name: if_user_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE if_user_id_seq
    START WITH 14
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.if_user_id_seq OWNER TO nagiosxi;

--
-- Name: if_usermeta_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE if_usermeta_id_seq
    START WITH 142
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.if_usermeta_id_seq OWNER TO nagiosxi;

--
-- Name: xi_commands_command_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_commands_command_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_commands_command_id_seq OWNER TO nagiosxi;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: xi_commands; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_commands (
    command_id integer DEFAULT nextval('xi_commands_command_id_seq'::regclass) NOT NULL,
    group_id integer DEFAULT 0,
    submitter_id integer DEFAULT 0,
    beneficiary_id integer DEFAULT 0,
    command integer NOT NULL,
    submission_time timestamp without time zone NOT NULL,
    event_time timestamp without time zone NOT NULL,
    frequency_type integer DEFAULT 0,
    frequency_units integer DEFAULT 0,
    frequency_interval integer DEFAULT 0,
    processing_time timestamp without time zone,
    status_code integer DEFAULT 0,
    result_code integer DEFAULT 0,
    command_data text,
    result text
);


ALTER TABLE public.xi_commands OWNER TO nagiosxi;

--
-- Name: xi_events; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_events (
    event_id integer NOT NULL,
    event_time timestamp without time zone,
    event_source smallint,
    event_type smallint DEFAULT 0 NOT NULL,
    status_code smallint DEFAULT 0 NOT NULL,
    processing_time timestamp without time zone
);


ALTER TABLE public.xi_events OWNER TO nagiosxi;

--
-- Name: xi_events_event_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_events_event_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_events_event_id_seq OWNER TO nagiosxi;

--
-- Name: xi_events_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: nagiosxi
--

--- ALTER SEQUENCE xi_events_event_id_seq OWNED BY xi_events.event_id;


--
-- Name: xi_meta_meta_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_meta_meta_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_meta_meta_id_seq OWNER TO nagiosxi;

--
-- Name: xi_meta; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_meta (
    meta_id integer DEFAULT nextval('xi_meta_meta_id_seq'::regclass) NOT NULL,
    metatype_id integer DEFAULT 0,
    metaobj_id integer DEFAULT 0,
    keyname character varying(128) NOT NULL,
    keyvalue text
);


ALTER TABLE public.xi_meta OWNER TO nagiosxi;

--
-- Name: xi_options_option_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_options_option_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_options_option_id_seq OWNER TO nagiosxi;

--
-- Name: xi_options; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_options (
    option_id integer DEFAULT nextval('xi_options_option_id_seq'::regclass) NOT NULL,
    name character varying(128) NOT NULL,
    value text
);


ALTER TABLE public.xi_options OWNER TO nagiosxi;

--
-- Name: xi_sysstat_sysstat_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_sysstat_sysstat_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_sysstat_sysstat_id_seq OWNER TO nagiosxi;

--
-- Name: xi_sysstat; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_sysstat (
    sysstat_id integer DEFAULT nextval('xi_sysstat_sysstat_id_seq'::regclass) NOT NULL,
    metric character varying(128) NOT NULL,
    value character varying(4096),
    update_time timestamp without time zone NOT NULL
);


ALTER TABLE public.xi_sysstat OWNER TO nagiosxi;

--
-- Name: xi_usermeta_usermeta_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_usermeta_usermeta_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_usermeta_usermeta_id_seq OWNER TO nagiosxi;

--
-- Name: xi_usermeta; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_usermeta (
    usermeta_id integer DEFAULT nextval('xi_usermeta_usermeta_id_seq'::regclass) NOT NULL,
    user_id integer NOT NULL,
    keyname character varying(255) NOT NULL,
    keyvalue text,
    autoload smallint DEFAULT (0)::smallint
);


ALTER TABLE public.xi_usermeta OWNER TO nagiosxi;

--
-- Name: xi_users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: nagiosxi
--

CREATE SEQUENCE xi_users_user_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.xi_users_user_id_seq OWNER TO nagiosxi;

--
-- Name: xi_users; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_users (
    user_id integer DEFAULT nextval('xi_users_user_id_seq'::regclass) NOT NULL,
    username character varying(64) NOT NULL,
    "password" character varying(64) NOT NULL,
    name character varying(64),
    email character varying(128) NOT NULL,
    backend_ticket character varying(128),
    enabled smallint DEFAULT 1::smallint NOT NULL
);


ALTER TABLE public.xi_users OWNER TO nagiosxi;

--
-- Name: event_id; Type: DEFAULT; Schema: public; Owner: nagiosxi
--

ALTER TABLE xi_events ALTER COLUMN event_id SET DEFAULT nextval('xi_events_event_id_seq'::regclass);


--
-- Name: xi_commands_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_commands
    ADD CONSTRAINT xi_commands_pkey PRIMARY KEY (command_id);


--
-- Name: xi_events_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_events
    ADD CONSTRAINT xi_events_pkey PRIMARY KEY (event_id);


--
-- Name: xi_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_meta
    ADD CONSTRAINT xi_meta_pkey PRIMARY KEY (meta_id);


--
-- Name: xi_options_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_options
    ADD CONSTRAINT xi_options_pkey PRIMARY KEY (option_id);


--
-- Name: xi_sysstat_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_sysstat
    ADD CONSTRAINT xi_sysstat_pkey PRIMARY KEY (sysstat_id);


--
-- Name: xi_usermeta_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_usermeta
    ADD CONSTRAINT xi_usermeta_pkey PRIMARY KEY (usermeta_id);


--
-- Name: xi_usermeta_user_id_key; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_usermeta
    ADD CONSTRAINT xi_usermeta_user_id_key UNIQUE (user_id, keyname);


--
-- Name: xi_users_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_users
    ADD CONSTRAINT xi_users_pkey PRIMARY KEY (user_id);


--
-- Name: xi_users_username_key; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_users
    ADD CONSTRAINT xi_users_username_key UNIQUE (username);


--
-- Name: event_time; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX event_time ON xi_events USING btree (event_source);


--
-- Name: xi_commands_event_time_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_commands_event_time_idx ON xi_commands USING btree (event_time);


--
-- Name: xi_meta_keyname_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_meta_keyname_idx ON xi_meta USING btree (keyname);


--
-- Name: xi_meta_metaobj_id_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_meta_metaobj_id_idx ON xi_meta USING btree (metaobj_id);


--
-- Name: xi_meta_metatype_id_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_meta_metatype_id_idx ON xi_meta USING btree (metatype_id);


--
-- Name: xi_options_name_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_options_name_idx ON xi_options USING btree (name);


--
-- Name: xi_sysstat_metric_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_sysstat_metric_idx ON xi_sysstat USING btree (metric);


--
-- Name: xi_usermeta_autoload_idx; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_usermeta_autoload_idx ON xi_usermeta USING btree (autoload);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

-- REVOKE ALL ON SCHEMA public FROM PUBLIC;
-- REVOKE ALL ON SCHEMA public FROM postgres;
-- GRANT ALL ON SCHEMA public TO postgres;
-- GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

