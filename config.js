const providers = ['twitter', 'youtube', 'reddit', 'stackexchange', 'facebook'];

const callbacks = providers.map((provider) => (process.env.NODE_ENV === 'production'
  ? `http://listen.online/auth/${provider}/callback`
  : `http://localhost:3000/auth/${provider}/callback`));

const [twitterURL, youtubeURL, redditURL, stackexchangeURL, facebookURL] = callbacks;

exports.TWITTER_CONFIG = {
  consumerKey: process.env.TWITTER_KEY,
  consumerSecret: process.env.TWITTER_SECRET,
  callbackURL: twitterURL,
  passReqToCallback: true,
};

exports.YOUTUBE_CONFIG = {
  clientID: process.env.YOUTUBE_KEY,
  clientSecret: process.env.YOUTUBE_SECRET,
  callbackURL: youtubeURL,
  scope: ['https://www.googleapis.com/auth/youtube.readonly'],
  passReqToCallback: true,
};

exports.REDDIT_CONFIG = {
  clientID: process.env.REDDIT_KEY,
  clientSecret: process.env.REDDIT_SECRET,
  callbackURL: redditURL,
  passReqToCallback: true,
  scope: ['identity', 'edit', 'flair', 'history', 'modconfig', 'modflair', 'modlog', 'modposts', 'modwiki', 'mysubreddits', 'privatemessages', 'read', 'report', 'save', 'submit', 'subscribe', 'vote', 'wikiedit', 'wikiread'],
};

exports.STACKEXCHANGE_CONFIG = {
  clientID: process.env.STACKEXCHANGE_ID,
  clientSecret: process.env.STACKEXCHANGE_SECRET,
  stackAppsKey: process.env.STACKEXCHANGE_KEY,
  callbackURL: stackexchangeURL,
  passReqToCallback: true,
  scope: ['read_inbox', 'no_expiry'],
  site: 'stackoverflow',
};

exports.FACEBOOK_CONFIG = {
  clientID: '277179343436629',
  clientSecret: '532eaaaf716a6a206d8c7586dd137aef',
  callbackURL: facebookURL,
  passReqToCallback: true,
  profileFields: ['id', 'displayName', 'photos', 'email'],
};
