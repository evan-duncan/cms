CREATE TABLE links (
    id         bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label      text        NOT NULL,
    url        text        NOT NULL,
    position   integer     NOT NULL DEFAULT 0,
    created_at timestamptz NOT NULL DEFAULT now()
);
