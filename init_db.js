require('dotenv').config();

const fs = require('fs');
const { v4: uuidv4 } = require('uuid');
const bcrypt = require('bcryptjs');
const { con, query } = require('./sql');
const { client } = require('./mongodb_util');
const { escape } = require('./util');

const swaggerFiles = ['nytimes.json', 'reddit.json', 'stackexchange.json', 'twitter.json', 'youtube.json'];

const init = async () => {
  client.connect(async () => {
    try {
      const db = client.db('listenonline');

      swaggerFiles.forEach((swaggerFile) => {
        fs.readFile(`swagger/${swaggerFile}`, 'utf8', async (err, data) => {
          if (err) throw err;
          const json = JSON.parse(escape(JSON.stringify(data)));
          await db.collection('swagger_files').insertOne({
            swagger: json,
            slug: swaggerFile.substring(0, swaggerFile.indexOf('.')),
          });
        });
      });

      await query('insert into users(name, email, password, uuid, isAdmin) values (?,?,?,?,?)',
        ['test', 'test@gmail.com', await bcrypt.hash('test', bcrypt.genSaltSync(12)), uuidv4(), 1]);
    } catch (error) {
      console.log(error);
    } finally {
      con.end();
      client.close();
    }
  });
};

init();
