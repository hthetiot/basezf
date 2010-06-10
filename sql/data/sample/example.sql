--
-- PostgreSQL database dump
--

SET client_encoding = 'LATIN1';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: example; Type: TABLE; Schema: public; Owner: dev; Tablespace: 
--

CREATE TABLE example (
    example_id integer NOT NULL,
    example_type_id integer DEFAULT 1 NOT NULL,
    unique_string text NOT NULL,
    string text NOT NULL,
    state smallint DEFAULT 1 NOT NULL,
    update timestamp without time zone,
    creation timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.example OWNER TO dev;

--
-- Name: example_example_id_seq; Type: SEQUENCE; Schema: public; Owner: dev
--

CREATE SEQUENCE example_example_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.example_example_id_seq OWNER TO dev;

--
-- Name: example_example_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dev
--

ALTER SEQUENCE example_example_id_seq OWNED BY example.example_id;


--
-- Name: example_example_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dev
--

SELECT pg_catalog.setval('example_example_id_seq', 10, true);


--
-- Name: example_type; Type: TABLE; Schema: public; Owner: dev; Tablespace: 
--

CREATE TABLE example_type (
    example_type_id integer NOT NULL,
    label text NOT NULL,
    short_label text NOT NULL,
    creation timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.example_type OWNER TO dev;

--
-- Name: example_type_example_type_id_seq; Type: SEQUENCE; Schema: public; Owner: dev
--

CREATE SEQUENCE example_type_example_type_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.example_type_example_type_id_seq OWNER TO dev;

--
-- Name: example_type_example_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: dev
--

ALTER SEQUENCE example_type_example_type_id_seq OWNED BY example_type.example_type_id;


--
-- Name: example_type_example_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: dev
--

SELECT pg_catalog.setval('example_type_example_type_id_seq', 1, true);


--
-- Name: example_id; Type: DEFAULT; Schema: public; Owner: dev
--

ALTER TABLE example ALTER COLUMN example_id SET DEFAULT nextval('example_example_id_seq'::regclass);


--
-- Name: example_type_id; Type: DEFAULT; Schema: public; Owner: dev
--

ALTER TABLE example_type ALTER COLUMN example_type_id SET DEFAULT nextval('example_type_example_type_id_seq'::regclass);


--
-- Data for Name: example; Type: TABLE DATA; Schema: public; Owner: dev
--

COPY example (example_id, example_type_id, unique_string, string, state, update, creation) FROM stdin;
1	1	example1	Test	1	\N	2010-04-09 17:05:09.702641
2	1	example2	Test	1	\N	2010-04-09 17:05:09.704306
3	1	example3	Test	1	\N	2010-04-09 17:05:09.704835
4	1	example4	Test	1	\N	2010-04-09 17:05:09.705347
5	1	example5	Test	1	\N	2010-04-09 17:05:09.705772
6	1	example6	Test	1	\N	2010-04-09 17:05:09.706387
7	1	example7	Test	1	\N	2010-04-09 17:05:09.70682
8	1	example8	Test	1	\N	2010-04-09 17:05:09.707255
9	1	example9	Test	1	\N	2010-04-09 17:05:09.707679
10	1	example10	Test	1	\N	2010-04-09 17:05:09.708111
\.


--
-- Data for Name: example_type; Type: TABLE DATA; Schema: public; Owner: dev
--

COPY example_type (example_type_id, label, short_label, creation) FROM stdin;
1	Type 1	T1	2010-04-21 16:36:29.266905
\.


--
-- Name: example_example_id_key; Type: CONSTRAINT; Schema: public; Owner: dev; Tablespace: 
--

ALTER TABLE ONLY example
    ADD CONSTRAINT example_example_id_key UNIQUE (example_id);


--
-- Name: example_pkey; Type: CONSTRAINT; Schema: public; Owner: dev; Tablespace: 
--

ALTER TABLE ONLY example
    ADD CONSTRAINT example_pkey PRIMARY KEY (example_id);


--
-- Name: example_type_example_type_id_key; Type: CONSTRAINT; Schema: public; Owner: dev; Tablespace: 
--

ALTER TABLE ONLY example_type
    ADD CONSTRAINT example_type_example_type_id_key UNIQUE (example_type_id);


--
-- Name: example__string__idx; Type: INDEX; Schema: public; Owner: dev; Tablespace: 
--

CREATE INDEX example__string__idx ON example USING btree (string);


--
-- Name: example_unique_idx; Type: INDEX; Schema: public; Owner: dev; Tablespace: 
--

CREATE UNIQUE INDEX example_unique_idx ON example USING btree (unique_string);


--
-- PostgreSQL database dump complete
--

