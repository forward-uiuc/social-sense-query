import json

data = '${targetobj}'
data.replace('true','True')
data.replace('false','False')
targetobj= json.loads(data)

data_source = '${sourceobj}'
data_source.replace('true','True')
data_source.replace('false','False')
sourceobj= json.loads(data_source)

${code}
