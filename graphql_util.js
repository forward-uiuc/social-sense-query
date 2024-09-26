const axios = require('axios').default;
const qs = require('querystring');
const perf = require('execution-time')();
const fetch = require('fetch').fetchUrl;
const Twitter = require('twitter');
const fs = require('fs');
const { query } = require('./sql');
const { calculateQuotaUsed } = require('./mongodb_util');

const toGraphQLQueryString = (node) => {
  const graphqlQuery = { string: node.name };

  if (node.inputs.length) {
    graphqlQuery.string += '(';
    node.inputs.forEach((input, index, inputs) => {
      if (input.inputType === 'String') {
        if (input.value) {
          graphqlQuery.string += `${input.name}:${input.value ? JSON.stringify(input.value) : '""'}`;
          if (index !== inputs.length - 1) {
            graphqlQuery.string += ', ';
          }
        }
      } else {
        graphqlQuery.string += `${input.name}:${input.value}`;
        if (index !== inputs.length - 1) {
          graphqlQuery.string += ', ';
        }
      }
    });
    graphqlQuery.string += ') ';
  }

  if (node.children.length) {
    graphqlQuery.string += '{ ';

    node.children.forEach((child) => {
      if (child.selected) {
        graphqlQuery.string += `${toGraphQLQueryString(child)} `;
      }
    });
    graphqlQuery.string += '} ';
  }

  return graphqlQuery.string;
};

function getURL(url) {
  return new Promise((resolve) => {
    fetch(url, (_error, _meta, body) => {
      resolve(body.toString());
    });
  });
}

function buildRecordsRecusively(data, pattern) {
  const result = {};
  let i;
  let j;
  for (i = 0; i < pattern.length; i += 1) {
    const names = pattern[i].name;
    let newName = '';
    for (j = 0; j < names.length; j += 1) {
      const character = names.charAt(j);
      if (character !== character.toLowerCase()) {
        newName += '_';
      }
      newName += character.toLowerCase();
    }
    if (pattern[i].children.length === 0 || typeof data[newName] === 'undefined') {
      result[newName] = data[newName];
    } else {
      result[newName] = buildRecordsRecusively(data[newName], pattern[i].children);
    }
  }
  return result;
}

function getTweeter(twitterQuery, accessToken, refreshToken) {
  return new Promise((resolve) => {
    new Twitter({
      consumer_key: process.env.TWITTER_KEY,
      consumer_secret: process.env.TWITTER_SECRET,
      access_token_key: accessToken,
      access_token_secret: refreshToken,
    }).get('search/tweets', twitterQuery, (_error, tweets) => {
      resolve(tweets);
    });
  });
}

function getTweeter1(url, options) {
  return new Promise((resolve) => {
    fetch(url, options, (_error, _meta, body) => {
      resolve(body.toString());
    });
  });
}

const submitGraphQLQuery = async (url, accessToken, refreshToken, schema, req) => {
  const { quota } = (await query('SELECT quota from users where id=?', [req.session.user_id]))[0];
  const usedQuota = await calculateQuotaUsed(req.session.user_id, req.db);

  if (usedQuota >= quota) {
    throw new Error(`You have used ${usedQuota} MB of storage out of your quota of ${quota} MB. Remove some stored data in order to continue to query.`);
  }

  perf.start();

  // console.log(schema);

  if (url === 'http://localhost:4005') {
    let i;
    let webpage = 'https://api.stackexchange.com/2.2/questions?';
    for (i = 2; i < schema.children[0].inputs.length; i += 1) {
      webpage += schema.children[0].inputs[i].name;
      webpage += '=';
      if (schema.children[0].inputs[i].value !== null) {
        try {
          let v = schema.children[0].inputs[i].value;
          if (v.endsWith('"') && v.startsWith('"')) {
            v = v.slice(1, -1);
          }
          webpage += v;
        } catch (err) {
          console.log('...');
        }
      }
      webpage += '&';
    }
    webpage = webpage.slice(0, -1);
    console.log(webpage);

    const a = JSON.parse(await getURL(webpage));
    const dataRecords = [];
    console.log(a);
    for (i = 0; i < a.items.length; i += 1) {
      const r = buildRecordsRecusively(a.items[i], schema.children[0].children);
      dataRecords.push(r);
    }
    const result = { data: { children: dataRecords } };
    const duration = perf.stop().time;
    return { result, duration };
  }

  // handle twitter
  if (req.session.slug === 'twitter') {
    const twitterQuery = {};
    let i;
    for (i = 0; i < schema.children[0].inputs.length; i += 1) {
      if (schema.children[0].inputs[i].value) {
        twitterQuery[schema.children[0].inputs[i].name] = schema.children[0].inputs[i].value;
      }
    }
    // const data = await getTweeter(twitterQuery, accessToken, refreshToken);

    const options = {
      headers: {
        authorization: {
          oauth: {
            consumer_key: 'jHN4Bv29FP2762ZCexnoJpc0S',
            consumer_secret: '1Q1GYDnjMGWlW6i54W5lURfJj5L4zOVjqOXIW5cXEitwTgKDfd',
            token: '1219055776981045248-2oQVvzzseDjZ5EAaYfg6WARgubIbXj ',
            token_secret: 'D5TiGuJoRvMGBbXLatlg0jbQick9bO68YnLn83XOZYVXQ',
          },
        },
      },
    };
    const data = await getTweeter1('https://api.twitter.com/1.1/search/tweets.json?q=from%3Atwitterdev&result_type=mixed&count=2', options);
    console.log(data);

    const dataRecords = [];
    for (i = 0; i < data.statuses.length; i += 1) {
      const r = buildRecordsRecusively(data.statuses[i], schema.children[0].children[0].children);
      dataRecords.push(r);
    }
    const result = { data: { children: dataRecords } };
    const duration = perf.stop().time;
    return { result, duration };
  }

  // handle facebook
  if (req.session.slug === 'facebook') {
    const idUrl = `https://graph.facebook.com/me?fields=id&access_token=${accessToken}`;
    const userID = (JSON.parse(await getURL(idUrl))).id;

    const infoUrl = `https://graph.facebook.com/${userID}?fields=birthday,hometown&access_token=${accessToken}`;
    const userInfo = JSON.parse(await getURL(infoUrl));

    const postUrl = `https://graph.facebook.com/${userID}/feed?access_token=${accessToken}`;
    const userPost = JSON.parse(await getURL(postUrl));

    const result = { data: { children: { information: { birthday: userInfo.birthday, hometown: userInfo.hometown.name }, posts: userPost.data } } };
    const duration = perf.stop().time;
    return { result, duration };
  }

  console.log(url);
  console.log(schema);
  console.log(toGraphQLQueryString(schema));
  console.log('-----------');
  const result = (await axios.post(url, {
    accessToken,
    query: toGraphQLQueryString(schema),
  })).data;
  console.log(result);
  const duration = perf.stop().time;
  return { result, duration };
};

module.exports = {
  executeQuery: async (url, slug, schema, serverId, req, queryName) => {
    const timestamp = new Date().toISOString().replace(/T/, ' ').replace(/\..+/, '');

    let accessToken = await query('SELECT access_token FROM authorizations WHERE user_id = ? AND server_id = ?', [req.session.user_id, serverId]);
    let refresh = await query('SELECT refresh_token FROM authorizations WHERE user_id = ? AND server_id = ?', [req.session.user_id, serverId]);
    let result;
    let duration;

    if (accessToken.length) {
      accessToken = accessToken[0].access_token;
    } else if (slug === 'nytimes') {
      accessToken = process.env.NYTIMES_KEY;
    }
    if (refresh.length) {
      refresh = refresh[0].refresh_token;
    }

    ({ result, duration } = await submitGraphQLQuery(url, accessToken, refresh, schema, req));

    if (result.errors && result.errors[0].extensions.statusCode === 401) {
      const refreshToken = (await query('SELECT refresh_token FROM authorizations WHERE user_id = ? AND server_id = ?', [req.session.user_id, serverId]))[0].refresh_token;
      let newAccessToken = null;

      if (slug === 'youtube') {
        const youtubeURL = 'https://oauth2.googleapis.com/token';

        newAccessToken = (await axios.post(youtubeURL, null, {
          params: {
            client_id: process.env.YOUTUBE_KEY,
            client_secret: process.env.YOUTUBE_SECRET,
            refreshToken,
            grant_type: 'refresh_token',
          },
        })).data.access_token;
      } else if (slug === 'reddit') {
        const auth = Buffer.from(`${process.env.REDDIT_KEY}:${process.env.REDDIT_SECRET}`).toString('base64');
        const redditURL = 'https://www.reddit.com/api/v1/access_token';

        const config = {
          headers: {
            Authorization: `Basic ${auth}`,
            'Content-Type': 'application/x-www-form-urlencoded',
            'User-Agent': 'Test Client v/1.0 ',
          },
        };

        newAccessToken = (await axios.post(redditURL, qs.stringify({
          grant_type: 'refresh_token',
          refresh_token: refreshToken,
        }), config)).data.access_token;
      }

      query('update authorizations set access_token = ? WHERE user_id = ? AND server_id = ?',
        [newAccessToken, req.session.user_id, serverId]);

      ({ result, duration } = await submitGraphQLQuery(url, newAccessToken, refreshToken, schema, req));
    }

    req.db.collection('query_histories').insertOne({
      query_name: queryName,
      executionTimestamp: timestamp,
      runtime: duration,
      data: result,
      user_id: req.session.user_id,
    });


    const applications = await query('SELECT callback FROM applications WHERE user_id=?', [req.session.user_id]);

    applications.forEach((app) => {
      axios.post(app.callback, {
        executionTimestamp: timestamp,
        data: result,
        runtime: duration,
        name: queryName,
      });
    });
  },
};
