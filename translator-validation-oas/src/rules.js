var parser = require('./parser.js');
var returnhandle = require('../utils/returnHandler.js');
let Promise = require('bluebird');
//test
var oasValidate = require('./oasValidation.js');

var custom = require('../utils/customFunction.js');

function custom_array_call (swaggerFile,
  newswaggerFile,custom_array) {
  //call each custom function and wait for promises to resolve

  var Promisearray = [];
  Promisearray.length = custom_array.length;

  console.log("Initial promise array", Promisearray);

  for (i = 0; i < custom_array.length; i++) {

    //wait for promises
    Promisearray[i] = custom.checkCustomFunction(swaggerFile,
      newswaggerFile,
      custom_array[i]);
  }

  console.log("Final Promise array,",Promisearray);

  return new Promise.all(Promisearray)
    .then((values) => {
      console.log(values);
    });

  // return promise when all of them have been successful

}
module.exports.rules = function(swaggerFile, newswaggerFile, jsonItr) {
    custom_array = [];
    non_custom_array = [];
    returnhandle.initializeLoggerList();

    // validate both schemas
    var oasValid = oasValidate.oasValidate(swaggerFile, {});
    var oasValidNew = oasValidate.oasValidate(newswaggerFile, {});

    if (jsonItr instanceof Array) {
      for (i = 0; i < jsonItr.length; i++) {
        if (jsonItr[i].functionName != 'checkCustomFunction') {
          non_custom_array.push(jsonItr[i]);
        }
        else {
          custom_array.push(jsonItr[i]);
        }
      }
    }

    console.log("Custom Array,", custom_array);
    console.log("Non-custom array", non_custom_array);

    //call non_custom_array functions
    for (i = 0; i < non_custom_array.length; i++) {
      var successcode = parser.parser(swaggerFile,
        newswaggerFile,
        non_custom_array[i]);
    }

    console.log("Loggerlist", returnhandle.getResults());

    //call custom array functions asynchronous

    return new Promise((resolve, reject) => {
          custom_array_call(swaggerFile,
            newswaggerFile,custom_array)
            .then(results => console.log("Completed Custom Functions", results))
            .then(results => resolve(returnhandle.getResults()));

          });
        }
