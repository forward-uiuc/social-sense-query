const { getGraphqlSchema } = require('translation-to-graphql');
const { printSchema } = require('graphql');

const addFileToFolder = (location, fileTree, fileName) => {
  if (!location.length) {
    fileTree.push({
      name: fileName,
    });
  } else {
    const next = fileTree.find((dir) => dir.name === location.shift());
    module.exports.addSwaggerToFolder(location, next.children, fileName);
  }
};

const addFolder = (location, fileTree, folderName) => {
  if (!location.length) {
    fileTree.push({
      name: folderName,
      children: [],
    });
  } else {
    const next = fileTree.find((dir) => dir.name === location.shift());
    module.exports.addSwaggerToFolder(location, next.children, folderName);
  }
};

const getLocationArray = (location) => {
  const locationArray = location.split('/');
  locationArray[0] = '/';

  if (locationArray[locationArray.length - 1] === '') {
    locationArray.splice(-1, 1);
  }
  return locationArray;
};

const insertNewFile = async (fileName, file, location, req) => {
  const locationArray = getLocationArray(location);
  const old = await req.db.collection('file_system').findOne({});
  await req.db.collection('file_system').remove({});
  const fileSystem = old.file_system;
  addFileToFolder(locationArray, fileSystem, fileName);
  await req.db.collection('file_system').insertOne({ file_system: fileSystem });
};

module.exports = {
  getCronPattern: (scheduleStr) => {
    const scheduleConversion = {
      'Once a minute': '* * * * *',
      'Once an hour': '0 * * * *',
      'Once a day': '0 0 * * *',
      'Once a week': '0 0 * * 0',
      'Once a month': '0 0 1 * *',
    };

    return scheduleConversion[scheduleStr];
  },

  makeGeneralError: (err) => ({ data: null, error: { generalError: err } }),

  makeFieldError: (err) => ({ data: null, error: { fieldError: err } }),

  makeSuccess: (data) => ({ data }),

  insertNewSwaggerFile: async (swaggerName, swagger, location, req) => {
    this.insertNewFile(swaggerName, swagger, location, req);
    await req.db.collection('swagger_files').insertOne({ name: swaggerName, swagger });
  },

  insertNewTranslationFile: async (translationName, translation, location, swaggerName, req) => {
    this.insertNewFile(translationName, translation, location, req);
    const { swagger } = await req.db.collection('swagger_files').findOne({ name: swaggerName });
    const schema = printSchema(await getGraphqlSchema(swagger, translation));
    await req.db.collection('swagger_files').insertOne({
      name: translationName, translation, schema, swaggerName,
    });
  },

  insertNewFolder: async (location, folderName, req) => {
    const locationArray = getLocationArray(location);
    const old = await req.db.collection('file_system').findOne({});
    await req.db.collection('file_system').remove({});
    const fileSystem = old.file_system;
    addFolder(locationArray, fileSystem, folderName);
    await req.db.collection('file_system').insertOne({ file_system: fileSystem });
  },

  checkUser: (req, res, next) => {
    if (req.path === '/app/login' || req.path === '/app/register'
    || req.path === '/' || req.path === '/app'
    || req.path.startsWith('/app/api/auth' || req.path.startsWith('/auth'))
    || req.path.startsWith('/app/api/social-media-socket')) {
      return next();
    }

    if (req.session.user_id) {
      if ((req.path.startsWith('/app/api/user') || req.path.startsWith('/app/api/server')) && !req.session.isAdmin) {
        return res.sendStatus(403);
      }
      return next();
    }
    return res.sendStatus(401);
  },
};
