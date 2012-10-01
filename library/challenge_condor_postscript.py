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


def fileContents(filename):
    filec = open(filename, 'r')
    lines = filec.readlines()
    filec.close()
    return ''.join(lines)



if __name__ == "__main__":
  (scriptName, workDir, taskId, dashboardId, resultsrunId, resultsFolderId, challengeId, dagjobname, testImage, resultImage, outputParseFile, resultRunItemId1, resultRunItemId2, jobname, jobid, returncode) = sys.argv
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

  if returncode != 0:
      #print "returncode not 0"
      #print "return_code", returncode
      #print "process_out", exeOutput
      process_out =  fileContents(exeOutput)
      #print process_out 
      #print "process_err", exeError
      process_err =  fileContents(exeError)
      #print process_err 
      #print "process_log", exeLog
      process_log =  fileContents(exeLog)
      #print process_log 
      status = 'error'
      #print "status", "error"
  else:
      status = 'done'
      #print "return_code", returncode
      #print "status", "done"

  result_run_item_ids = [resultRunItemId1, resultRunItemId2]
  result_run_item_ids_vals = {resultRunItemId1: None, resultRunItemId2: None}


  # parse output and upload value
  import os
  if os.path.exists(outputParseFile):
      lines = open(outputParseFile,'r')
      for ind, line in enumerate(lines):
          line = line.strip()
          cols = line.split('=')
          value = cols[-1]
          value = value.strip()
          key = cols[0]
          key = key.strip()
          result_run_item_ids_vals[result_run_item_ids[ind]] = value
      lines.close()

  for rri_id in result_run_item_ids:
      val = result_run_item_ids_vals[rri_id]
      parameters = {}
      parameters['token'] = token
      parameters['result_run_item_id'] = rri_id
      parameters['result_value'] = val
      parameters['status'] = status
      parameters['return_code'] = returncode
      if returncode != 0:
          parameters['process_out'] = process_out
          parameters['process_log'] = process_log
          parameters['process_err'] = process_err
      print parameters
      log.write("\n\nCalled update.results.run.item with params:"+str(parameters))
      resultsRunItem = interfaceMidas.request(method, parameters)
      log.write("\n\nresponse: "+str(resultsRunItem)+"\n\n")


#    parameters = {}
#    parameters['token'] = token
#    parameters['result_run_item_id'] = result_run_item_ids[ind]
#
#
#
#  for ind, line in enumerate(lines):
#    line = line.strip()
#    cols = line.split('=')
#    value = cols[-1]
#    value = value.strip()
#    key = cols[0]
#    key = key.strip()
#    parameters = {}
#    parameters['token'] = token
#    parameters['result_run_item_id'] = result_run_item_ids[ind]
#    parameters['result_value'] = value
#    parameters['status'] = status
#    parameters['return_code'] = returncode
#    if returncode != 0:
#        parameters['process_out'] = process_out
#        parameters['process_log'] = process_log
#        parameters['process_err'] = process_err
#    log.write("\n\nCalled update.results.run.item with params:"+str(parameters))
#    resultsRunItem = interfaceMidas.request(method, parameters)
#    log.write("\n\nresponse: "+str(resultsRunItem)+"\n\n")
#  lines.close()
#  


  
  log.close()
  exit()
