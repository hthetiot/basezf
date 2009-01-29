create table example (
   example_id           SERIAL NOT NULL,
   country_id           INT4 NOT NULL,
   language_id          INT4 NOT NULL,
   login                VARCHAR(64) NOT NULL,
   email                VARCHAR(255) NOT NULL,
   display_name         VARCHAR(64) NOT NULL,
   creation             TIMESTAMP NOT NULL DEFAULT NOW()
);                                                                                      
