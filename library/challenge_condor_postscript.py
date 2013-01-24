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



if __name__ == "__main__":
  (scriptName, workDir, taskId, dashboardId, resultsrunId, resultsFolderId, challengeId, dagjobname, testImage, resultImage, outputParseFile, resultRunItemIds, jobname, jobid, returncode) = sys.argv
  jobidNum = jobname[3:]
  cfgParams = loadConfig('userconfig.cfg')

  postfilename = 'postscript'+jobidNum+'.log'
  log = open(os.path.join(workDir, postfilename),'w')
  log.write('Condor Post Script log\n\nsys.argv:\n\n')
  log.write('\t'.join(sys.argv))

  log.write('\n\nUser Config Params:\n\n')
  log.write('\n'.join(['\t'.join((k,v)) for (k,v) in cfgParams.iteritems()])) 

  interfaceMidas = core.Communicator (cfgParams['url'])
  token = interfaceMidas.login_with_api_key(cfgParams['email'], cfgParams['apikey'])
  log.write("\n\nLogged into midas, got token: "+token+"\n\n")

  jobdefinitionfilename = dagjobname +'.'+jobidNum+'.dagjob' 
  exeOutput = 'bmGrid.' + jobidNum + '.out.txt' 
  exeError = 'bmGrid.' + jobidNum + '.error.txt' 
  exeLog = 'bmGrid.' + jobidNum + '.log.txt' 

  # add the condor job
  response = interfaceMidas.add_condor_job(token, taskId, jobdefinitionfilename, exeOutput, exeError, exeLog, postfilename)
  log.write("\n\nCalled addCondorJob() with response:"+str(response)+"\n\n")
  condorjobid = response['condor_job_id']

  # lots of string parsing to get values
  # strip off any commas, batchmake artifact
  # get the itemid from the path
  testImage = testImage.strip(',')  
  parts = testImage.split('/')
  testItemId = parts[-2]

  resultImage = resultImage.strip(',')  
  parts = resultImage.split('/')
  resultItemId = parts[-2]
  # going to need to coordinate itemName with actual output file
  # TODO FIX
  #itemName = parts[-1] + ".output"
  itemName = exeOutput

  # have to upload the scalar value as an admin

  cfgParams = loadConfig('adminconfig.cfg')
  interfaceMidas = core.Communicator (cfgParams['url'])
  token = interfaceMidas.login_with_api_key(cfgParams['email'], cfgParams['apikey'])

  method = 'midas.challenge.admin.update.results.run.item'


  # parse output and upload value
  lines = open(outputParseFile,'r')
  result_run_item_ids = resultRunItemIds.split(';')
  for ind, line in enumerate(lines):
    line = line.strip()
    cols = line.split('=')
    value = cols[-1]
    value = value.strip()
    key = cols[0]
    key = key.strip()
    parameters = {}
    parameters['token'] = token
    parameters['result_run_item_id'] = result_run_item_ids[ind]
    parameters['result_value'] = value
    log.write("\n\nCalled update.results.run.item with params:"+str(parameters))
    resultsRunItem = interfaceMidas.request(method, parameters)
    log.write("\n\nresponse: "+str(resultsRunItem)+"\n\n")
  lines.close()
  


  
  log.close()
  exit()
