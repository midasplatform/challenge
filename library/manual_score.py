import sys
import os
import subprocess


def get_cases_to_run(truth_dir, results_dir):
    truths = os.listdir(truth_dir)
    results = os.listdir(results_dir)
    # match up truths and results
    truth_cases = set([truth.replace('_truth','') for truth in truths])
    result_cases = set([result.replace('_result','') for result in results])
    print "truths without results", truth_cases.difference(result_cases)
    print "results without truths", result_cases.difference(truth_cases)
    print "cases to run", truth_cases.intersection(result_cases)
    return truth_cases.intersection(result_cases)

def resolve_metrics(metric_list, metric_exe_dir):
    metrics = {}
    for metric in metric_list.split(','):
        metric_name = "ValidateImage" + metric
        metric_path = os.path.join(metric_exe_dir, metric_name)
        if not os.path.exists(metric_path):
            print "CANNOT locate ", metric_path
        else:
            metrics[metric_name] = metric_path 
            print "Located Metric ", metric_path
    return metrics

def generate_jobs(truth_dir, results_dir, cases_to_run, metrics, output_dir):
    jobs = []
    for case in cases_to_run:
        cols = case.split('.')
        truth = os.path.join(truth_dir, cols[0] + "_truth." + cols[1])
        result = os.path.join(results_dir, cols[0] + "_result." + cols[1])
        for metric in metrics.keys():
            output = cols[0] + "_" + metric + ".out"
            output = os.path.join(output_dir, output)
            job = [metrics[metric], truth, result, output]
            jobs.append(job)
    return jobs

def run_jobs(jobs):
    for ind, job in enumerate(jobs):
        subprocess.check_call(job)


if __name__ == "__main__":
    (script, run_type, truth_dir, results_dir, metric_list, metric_exe_dir, output_dir) = sys.argv
    print "run_type", run_type
    print "truth_dir", truth_dir
    print "results_dir", results_dir
    print "metric_list", metric_list
    print "metric_exe_dir", metric_exe_dir
    print "output_dir", output_dir
    cases_to_run = get_cases_to_run(truth_dir, results_dir)
    metrics = resolve_metrics(metric_list, metric_exe_dir)
    jobs = generate_jobs(truth_dir, results_dir, cases_to_run, metrics, output_dir)
    if run_type != "dry_run":
        run_jobs(jobs)

