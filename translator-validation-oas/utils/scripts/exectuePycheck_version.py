import json

data = '{"openapi":"3.0.0","info":{"version":"0.0.0","title":"Links example"},"paths":{"/username/{username}":{"post":{"summary":"Creates a user and returns the user ID","parameters":[{"name":"username","in":"path","required":true,"content":{"application/json":{"schema":{"type":"string"}}}},{"name":"userquery","in":"query","required":true,"content":{"application/json":{"schema":{"type":"integer"}}}}],"responses":{"200":{"description":"Created"},"201":{"description":"Created","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}},"operationId":"username-post:undefined"}}},"components":{"schemas":{"User":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}'
data.replace('true','True')
data.replace('false','False')
targetobj= json.loads(data)

data_source = '{"openapi":"3.0.0","info":{"version":"0.0.0","title":"Links example"},"paths":{"/users/{username}":{"post":{"summary":"Creates a user and returns the user ID","operationId":"createUser","parameters":[{"in":"path","name":"username","required":true,"content":{"application/json":{"schema":{"type":"string"}}}},{"in":"query","name":"userquery","required":true,"content":{"application/json":{"schema":{"type":"integer"}}}}],"responses":{"200":{"description":"Created"},"201":{"description":"Created","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}}}},"/usersdesc":{"patch":{"summary":"Patch User desc","operationId":"PatchUserDesc","requestBody":{"required":true,"description":"A JSON object that contains the user name and desc.","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}},"responses":{"200":{"description":"Created","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}}}},"/users/userdesc/":{"post":{"summary":"Post User desc","operationId":"usersUserdesc","requestBody":{"required":true,"description":"A JSON object that contains the user name and desc","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}},"responses":{"200":{"description":"Created","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}}}},"/users/{userId}":{"get":{"summary":"Gets a user by ID","operationId":"getUser","parameters":[{"in":"path","name":"userId","required":true,"content":{"application/json":{"schema":{"type":"integer"}}}}],"responses":{"default":{"description":"A User object","content":{"application/json":{"schema":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}}}}},"components":{"schemas":{"User":{"type":"object","properties":{"id":{"type":"integer","format":"int64","readOnly":true},"name":{"type":"integer","format":"int64"}}}}}}'
data_source.replace('true','True')
data_source.replace('false','False')
sourceobj= json.loads(data_source)

from jsonpath_ng import jsonpath, parse
def check_required_params(s_value,e_value):
    jsonpath_expression = parse('$..parameters')
    match_s=jsonpath_expression.find(s_value)
    match_e= jsonpath_expression.find(e_value)
    required_param_list=[]
    for record in match_e[0].value:
        required_param_list.append(record["name"])
    for record in match_s[0].value:
        if record['required'] == True:
            # check if it is in list of endpoint param values
            if record['name'] not in required_param_list:
                return False
            
    return True
    

def check_param_exists_source(sourceobj,endpoint_name,endpoint_name_value):
    jsonpath_expression = parse('$..paths')
    match=jsonpath_expression.find(sourceobj)
    
    for key,value in match[0].value.items():
        s_endpoint_name = key.replace('}','').replace('{','').split('/')[-1].lower()
        if s_endpoint_name == endpoint_name:
            # Return check required params
            s_endpoint_name_value = value
            if check_required_params(s_endpoint_name_value,endpoint_name_value) == False:
                return False
    
    
jsonpath_expression = parse('$..paths')
match=jsonpath_expression.find(targetobj)
for idx in range(0,len(match)):
    for key,value in match[idx].value.items():
        endpoint_name = key.split('/')[-1].replace('}','').replace('{','').lower()
        endpoint_name_value= value
        if check_param_exists_source(sourceobj,endpoint_name,endpoint_name_value) == False:
            print("Failure")

print("Success")
