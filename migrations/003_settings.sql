CREATE TABLE settings (
    key   text PRIMARY KEY,
    value text NOT NULL
);

INSERT INTO settings (key, value) VALUES ('site_name', 'cms');
