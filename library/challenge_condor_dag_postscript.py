#! /usr/bin/python
import os
import sys
import pydas.drivers
import pydas.exceptions
import pydas.core as core

# Load configuration file
def loadConfig(filename):
   try:
     configfile = open(filename, "r")
     ret = dict()
     for x in configfile:
       x = x.strip()
       if not x: continue
       cols = x.split()
       ret[cols[0]] = cols[1]
     return ret
   except Exception, e: raise



def openLog(logpath):
  log = open(os.path.join(outputDir,'postscript'+jobidNum+'.log'),'w')
  log.write('Condor Post Script log\n\nsys.argv:\n\n')
  log.write('\t'.join(sys.argv))
  return log

def logConfig(log, cfgParams):
  log.write('\n\nConfig Params:\n\n')
  log.write('\n'.join(['\t'.join((k,v)) for (k,v) in cfgParams.iteritems()])) 


def midas_connect():
  # get config params
  cfgParams = loadConfig('userconfig.cfg')
  # open connection to midas
  interfaceMidas = core.Communicator (cfgParams['url'])
  token = interfaceMidas.login_with_api_key(cfgParams['email'], cfgParams['apikey'])
  print "Logged into midas, got token: "+token
  return (interfaceMidas, token)




def update_results_run(midas, token, results_run_id, status):
    method = 'midas.challenge.competitor.update.results.run'
    parameters = {}
    parameters['token'] = token
    parameters['results_run_id'] = results_run_id 
    parameters['status'] = status
    print "calling ", method, parameters
    response = midas.request(method, parameters)
    print response


 

def dag_start(jobName, dagName, taskId, results_run_id):
  jobidNum = jobName[3:]

  (midas, token) = midas_connect()

  dagfilename = dagName + ".dagjob"
  dagmanoutfilename = dagfilename + ".dagman.out"
  print "Calling add condor dag with params:"+token+" "+taskId+" "+dagfilename+" "+ dagmanoutfilename
  # add the condor_dag
  dagResponse = midas.add_condor_dag(token, taskId, dagfilename, dagmanoutfilename)
  print "Added a Condor Dag with response:"+str(dagResponse)

  update_results_run(midas, token, results_run_id, 'running')



def dag_end(results_run_id):
  (midas, token) = midas_connect()
  update_results_run(midas, token, results_run_id, 'complete')


if __name__ == "__main__":
  (scriptName, dagStage, results_run_id) = sys.argv[0:3]
  print scriptName, dagStage, results_run_id

  if dagStage == 'START':
      (outputDir, taskId, dagName, jobId, jobName, returnCode) = sys.argv[3:]
      dag_start(jobName, dagName, taskId, results_run_id)
  elif dagStage == 'END':
      dag_end(results_run_id)
  else:
      print "UNKNOWN STAGE", dagStage
      exit(1)
















