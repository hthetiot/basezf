-- MyProject - indexes.sql
--
-- This file containt the extra project table indexes
--

-------------------------------------------------
-- Example table

-- and index to optimize select
CREATE INDEX example__string__idx ON example USING btree(string);

