let {
  PythonShell
} = require('python-shell');
var fs = require('fs');
const compile = require('compile-template')
let assert = require('assert');
let Promise = require('bluebird');
let pythonBridge = Promise.promisifyAll(require('python-bridge'));
var returnhandle = require('../utils/returnHandler.js');
var jp = require('jsonpath');
module.exports.checkCustomFunction = function (swaggerFile,newswaggerFile, options) {
  //Create Python Script, add template here that accepts arguments

  // extract target object
  options.parentSchema  = swaggerFile;
  options.childSchema =  newswaggerFile;

  var target = JSON.stringify(options.childSchema);
  var source = JSON.stringify(options.parentSchema);


  // compile template from customTemplate.py
  const template = compile(fs.readFileSync(__dirname + '/customTemplate.py', 'utf8'));
  options.errPath = jp.stringify(options.path);

  const script = template({funcname:options.customFunctionName,
                          code:options.code,
                          targetobj: target,
                          sourceobj: source});

  // Save script to file in utils
  var script_path = __dirname+'/scripts/'+'exectuePy'+options.customFunctionName+'.py';
  fs.writeFileSync(script_path,script, function (err) {
  if (err) throw err;
  console.log('New Python Script is created successfully.');
  });

  let py_options = {
    pythonOptions: ['-u'],
  };

  return new Promise((resolve, reject) => {

  PythonShell.run(script_path, py_options, function (err, results) {
    console.log(script_path,err,results);
    if (results[0] == "Failure"){
      returnhandle.returnHandler(options);
    }
  resolve("Success");
  });

  // python.ex`import math`;
  // python`math.sqrt(9)`.then(x => console.log(x));

  // let list = [3, 4, 2, 1];
  // a = 23;
  // b = 23;
  // console.log(options.code);
  // python.ex`
  //   import json
  //   def print(targetobj):
  //       targetobj.replace('true','True')
  //       targetobj.replace('false','False')
  //       targetobj= json.loads(targetobj)
  //       if targetobj['openapi'] != '3.0.0':
  //         return True
  //       else:
  //         return False
  // `;
  // python`print(${target})`
  // .then(x => console.log("Printing x",x))
  // .then(x => returnhandle.returnHandler(options))
  // .then(results=>resolve("Success"));
  // // .then(x => returnhandle.returnHandler(options));
  //
  // python.end();


  });


// delete script after run
  // fs.unlinkSync(script_path);



}
