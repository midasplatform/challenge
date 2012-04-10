CREATE TABLE challenge_challenge (
    challenge_id serial PRIMARY KEY,
    validation_dashboard_id bigint NOT NULL,
    community_id bigint NOT NULL,
    status text NOT NULL
);


CREATE TABLE challenge_results_run (
  challenge_results_run_id  serial PRIMARY KEY,
  executable_name text NOT NULL,
  params text NOT NULL,
  challenge_id bigint NOT NULL,
  batchmake_task_id bigint NOT NULL,
  results_folder_id bigint NOT NULL,
  output_folder_id bigint NOT NULL
);

CREATE TABLE challenge_results_run_item (
  challenge_results_run_item_id serial PRIMARY KEY,
  challenge_results_run_id bigint NOT NULL,
  test_item_id bigint NOT NULL,
  output_item_id bigint NOT NULL,
  results_item_id bigint NOT NULL,
  condor_dag_job_id bigint NOT NULL,
  validation_scalarresult_id bigint NOT NULL
);

           