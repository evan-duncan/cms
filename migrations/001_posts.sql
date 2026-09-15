CREATE TABLE posts (
    id           bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    slug         text        NOT NULL UNIQUE,
    title        text        NOT NULL,
    body         text        NOT NULL DEFAULT '',
    published_at timestamptz,
    created_at   timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX posts_published_at_idx ON posts (published_at DESC);
