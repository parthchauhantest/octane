--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: xi_auditlog; Type: TABLE; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE TABLE xi_auditlog (
    auditlog_id serial NOT NULL,
    log_time timestamp without time zone,
    source text,
    "user" text,
    "type" integer,
    message text,
    ip_address text
);


ALTER TABLE public.xi_auditlog OWNER TO nagiosxi;

--
-- Name: xi_auditlog_pkey; Type: CONSTRAINT; Schema: public; Owner: nagiosxi; Tablespace: 
--

ALTER TABLE ONLY xi_auditlog
    ADD CONSTRAINT xi_auditlog_pkey PRIMARY KEY (auditlog_id);


--
-- Name: xi_auditlog_ip_address; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_auditlog_ip_address ON xi_auditlog USING btree (ip_address);


--
-- Name: xi_auditlog_log_time; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_auditlog_log_time ON xi_auditlog USING btree (log_time);


--
-- Name: xi_auditlog_source; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_auditlog_source ON xi_auditlog USING btree (source);


--
-- Name: xi_auditlog_type; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_auditlog_type ON xi_auditlog USING btree ("type");


--
-- Name: xi_auditlog_user; Type: INDEX; Schema: public; Owner: nagiosxi; Tablespace: 
--

CREATE INDEX xi_auditlog_user ON xi_auditlog USING btree ("user");


--
-- PostgreSQL database dump complete
--

