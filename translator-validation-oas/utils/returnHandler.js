//// TODO: Make loggerList global
var loggerList= [];
module.exports.returnHandler=  function(options) {
  var err = {
    'Level':options.level,
    'Rule': options.ruledesc,
    'Message': options.errmsg,
    'JsonPath': options.path,
  };
  if (options.errPath)
  {
    err['ErrPath']=options.errPath;
  }
  loggerList.push(err);
  console.log("Logger error is",err)
}

module.exports.getResults= function(){
  return loggerList;
}

module.exports.initializeLoggerList= function(){
  loggerList= [];
}
