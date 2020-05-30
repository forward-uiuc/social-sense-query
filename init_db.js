require('dotenv').config();

const fs = require('fs');
const { v4: uuidv4 } = require('uuid');
const bcrypt = require('bcryptjs');
const { query } = require('./sql');
const { client } = require('./mongodb_util');
const { escape } = require('./util');

// const nodes = {
//   '/': [
//     {
//       name: 'reddit',
//       children: [
//         {
//           name: 'reddit_sub',
//           children: [
//             {
//               name: 'reddit_sub_sub',
//               children: [{
//                 name: 'reddit.json',
//               }],
//             },
//             {
//               name: 'reddit_translation.json',
//             },
//           ],
//         },
//         {
//           name: 'reddit_sub_2',
//           children: [{
//             'reddit_translation.json',
//           }],
//         },
//       ],
//     },
//     {
//       value: '/config',
//       label: 'config',
//       children: [
//         {
//           value: '/config/app.js',
//           label: 'app.js',
//         },
//         {
//           value: '/config/database.js',
//           label: 'database.js',
//         },
//       ],
//     },
//     {
//       value: '/public',
//       label: 'public',
//       children: [
//         {
//           value: '/public/assets/',
//           label: 'assets',
//           children: [{
//             value: '/public/assets/style.css',
//             label: 'style.css',
//           }],
//         },
//         {
//           value: '/public/index.html',
//           label: 'index.html',
//         },
//       ],
//     },
//     {
//       value: '/.env',
//       label: '.env',
//     },
//     {
//       value: '/.gitignore',
//       label: '.gitignore',
//     },
//     {
//       value: '/README.md',
//       label: 'README.md',
//     },
//   ],
// };

const init = async () => {
  client.connect(async () => {
    try {
      const db = client.db('listenonline');

      await db.collection('file_system').insertOne({ file_system: JSON.stringify({ '/': [] }) });

      await query('insert into users(name, email, password, uuid, isAdmin) values (?,?,?,?,?)',
        ['test', 'test@gmail.com', await bcrypt.hash('test', bcrypt.genSaltSync(12)), uuidv4(), 1]);
    } catch (error) {
      console.log(error);
    } finally {
      console.log('Inserted into db. Press ctrl+c to exit.');
    }
  });
};

init();
