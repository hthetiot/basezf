-- MyProject - struct.sql
--
-- This file containt the default project structure of table
-- with default indexes and foreigns keys
--

-------------------------------------------------
-- Example table

CREATE TABLE example
(
    example_id      SERIAL NOT NULL UNIQUE, -- use serial in place of mysql auto_increment
    example_type_id INT NOT NULL DEFAULT 1,
    unique_string   TEXT NOT NULL,
    string          TEXT NOT NULL,
    state           INT2 NOT NULL DEFAULT 1,
    date            TIMESTAMP,
    creation        TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Comment all fiels, and with possible value if needed
-- COMMENT ON COLUMN example.state IS 'Current state: 0 pending, 1 validate, -1 refuse';



-- the unique index of curse
CREATE UNIQUE INDEX example_unique_idx ON example USING btree(unique_string);

-------------------------------------------------
-- Example type possible values table

CREATE TABLE example_type
(
    example_type_id SERIAL NOT NULL UNIQUE, -- use serial in place of mysql auto_increment
    label           TEXT NOT NULL,
    short_label     TEXT NOT NULL,
    creation        TIMESTAMP NOT NULL DEFAULT NOW()
)

-- dont miss the foreign keys
--ALTER TABLE ONLY example ADD CONSTRAINT example__example_type_id__fkey FOREIGN KEY (example_type_id) REFERENCES example_type(example_type_id);
