-- MyProject - indexes.sql
--
-- This file containt the extra project table indexes
--

-------------------------------------------------
-- Example table

-- and index to optimize select
DROP INDEX example__string__idx;
CREATE INDEX example__string__idx ON example USING btree(string);

