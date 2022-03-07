var parser = require('./parser.js');
var returnhandle = require('../utils/returnHandler.js');
let Promise = require('bluebird');
//test

var custom = require('../utils/customFunction.js');

module.exports.rules= function (swaggerFile, newswaggerFile, jsonItr) {
  // if (jsonItr instanceof Array) {
  //
  //   for (i = 0; i < jsonItr.length; i++) {
  //     //wait for all rules to finish, make iteration async
  //     console.log("Rule here is",jsonItr[i]);
  //     var successcode = parser.parser(swaggerFile,
  //       newswaggerFile,
  //       jsonItr[i]);
  //     console.log(successcode,i);
  //   }
  // }

options= jsonItr[9];

return new Promise((resolve, reject) => {
  custom.checkCustomFunction(swaggerFile, newswaggerFile,options)
  .then(results => console.log("Completed Custom Function",results))
  .then(results => console.log("Waited for custom function to complete",results))
  .then(results => resolve(returnhandle.getResults()));
});
}
