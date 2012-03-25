CREATE TABLE challenge_challenge (
    challenge_id serial PRIMARY KEY,
    validation_dashboard_id bigint NOT NULL,
    community_id bigint NOT NULL
);
