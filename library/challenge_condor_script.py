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


def admin_connect():
    cfgParams = loadConfig('adminconfig.cfg')
    interfaceMidas = core.Communicator (cfgParams['url'])
    token = interfaceMidas.login_with_api_key(cfgParams['email'], cfgParams['apikey'])
    return (interfaceMidas, token)

def user_connect():
    cfgParams = loadConfig('userconfig.cfg')
    interfaceMidas = core.Communicator (cfgParams['url'])
    token = interfaceMidas.login_with_api_key(cfgParams['email'], cfgParams['apikey'])
    return (interfaceMidas, token)


def pre_script():
    print "running pre_script"
    (scriptName, script_stage, workDir, taskId, dashboardId, resultsrunId, resultsFolderId, challengeId, dagjobname, testImage, resultImage, outputParseFile, resultRunItemIds, jobname, jobid) = sys.argv
    jobidNum = jobname[3:]
    (midas, token) = user_connect()
    print "jobidNum", jobidNum, "token", token

    # add the condor job
    jobdefinitionfilename = dagjobname +'.'+jobidNum+'.dagjob' 
    exeOutput = 'bmGrid.' + jobidNum + '.out.txt' 
    exeError = 'bmGrid.' + jobidNum + '.error.txt' 
    exeLog = 'bmGrid.' + jobidNum + '.log.txt' 

    response = midas.add_condor_job(token, taskId, jobdefinitionfilename, exeOutput, exeError, exeLog, 'no_postfile')
    print "called add_condor_job, response:", response
    condorjobid = response['condor_job_id']

    # update the resultsrunitem with condorjobid, status=running, and job output file
    # do this once per resultrunitem_id
    # must be done as an admin
    (midas, token) = admin_connect()
    method = 'midas.challenge.admin.update.results.run.item'
    parameters = {}
    parameters['token'] = token
    parameters['status'] = 'running'
    parameters['condor_dag_job_id'] = condorjobid
    parameters['process_out'] = outputParseFile
    result_run_item_ids = resultRunItemIds.split('_')
    for result_run_item_id in result_run_item_ids:
        parameters['result_run_item_id'] = result_run_item_id
        response = midas.request(method, parameters)
        print "called update.results.run.item with response:",response


result_key_map = {
'AveDist(A_1, B_1)' : 'adb 1',
'AveDist(A_2, B_2)' : 'adb 2',
'HausdorffDist(A_1, B_1)' : 'hdb 1',
'HausdorffDist(A_2, B_2)' : 'hdb 2',
'Sensitivity(A_1, B_1)' : 'sens 1',
'Sensitivity(A_2, B_2)' : 'sens 2',
'Specificity(A_1, B_1)' : 'spec 1',
'Specificity(A_2, B_2)' : 'spec 2',
'PositivePredictiveValue(A_1, B_1)' : 'ppv 1',
'PositivePredictiveValue(A_2, B_2)' : 'ppv 2',
'Dice(A_1, B_1)' : 'dice 1',
'Dice(A_2, B_2)' : 'dice 2'}


def add_children(dag, node, children):
    if node not in dag:
        return children
    childrennodes = dag[node][1]
    for child in childrennodes:
        children[child] = child
        add_children(dag, child, children)
    return children

def post_script():
    print "running post_script"
    (scriptName, script_stage, workDir, taskId, dashboardId, resultsrunId, resultsFolderId, challengeId, dagjobname, testImage, resultImage, outputParseFile, resultRunItemIds, jobname, jobid, returnCode) = sys.argv
    jobidNum = jobname[3:]
    (midas, token) = user_connect()
    print "jobidNum", jobidNum, "token", token

    # update the resultsrunitem with result_key, , status=running, and job output file
    # do this once per resultrunitem_id
    # must be done as an admin
    (midas, token) = admin_connect()
    method = 'midas.challenge.admin.update.results.run.item'
    parameters = {}
    parameters['token'] = token
    result_run_item_ids = resultRunItemIds.split('_')
    if not os.path.exists(outputParseFile) or returnCode != '0': 
        for result_run_item_id in result_run_item_ids:
            parameters['result_run_item_id'] = result_run_item_id
            parameters['return_code'] = returnCode
            parameters['status'] = 'error'
            response = midas.request(method, parameters)
            print "called update.results.run.item with response:",response
# this section was written for the case of the dag where further dag jobs are killed
# by parents in the dag, but it is not currently occurring

#        # since all children of this job will not be run, 
#        # notify all children that they are in 'stopped' status
#        # construct the dag
#        lines = open(dagjobname+".dagjob",'r')
#        dag = {}
#        postscripts = {}
#        for line in lines:
#            line = line.strip()
#            cols = line.split()
#            #print cols
#            if len(cols) > 3 and cols[0] == 'SCRIPT' and cols[1] == 'POST':
#                postscripts[cols[2]] = cols
#            if len(cols) == 4 and cols[0] == 'PARENT':
#                parent, child= cols[1], cols[3]            
#                if parent not in dag:
#                    dag[parent] = ('new', [])
#                dag[parent][1].append(child)
#        lines.close()       
#
#        # find all the children of the current job in the dag and update their status
#        children = {}
#        children = add_children(dag, jobname, children)
#        del parameters['return_code']
#        for child in children:
#            if child in postscripts:
#                result_run_item_ids = postscripts[child][-4].split('_')
#                for result_run_item_id in result_run_item_ids:
#                    parameters['result_run_item_id'] = result_run_item_id
#                    parameters['status'] = 'stopped'
#                    response = midas.request(method, parameters)
#                    print "called update.results.run.item with response:",response
    else:
        print ' NO ERROR', outputParseFile
        # parse output and upload value
        lines = open(outputParseFile,'r')
        for ind, line in enumerate(lines):
            # in the case where there are more lines in the outputfile than there
            # are scored labels, ignore the remaining lines
            if ind >= len(result_run_item_ids):
                break
            line = line.strip()
            cols = line.split('=')
            value = cols[-1]
            value = value.strip()
            result_key = cols[0]
            result_key = result_key.strip()
            parameters['result_run_item_id'] = result_run_item_ids[ind]
            parameters['result_key'] = result_key_map[result_key]
            parameters['result_value'] = value
            parameters['return_code'] = returnCode
            parameters['status'] = 'complete'
            response = midas.request(method, parameters)
            print "called update.results.run.item with response:",response
        lines.close()
    


if __name__ == "__main__":
  print sys.argv
  (scriptName, script_stage) = sys.argv[0:2]
  if script_stage == "PRE":
      pre_script()
  elif script_stage == "POST":
      post_script()
  else:
      print "script_stage should be [PRE|POST]"
