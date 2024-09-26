# Listen Online

Listen Online is a querying mediator for web services that do not provide a machine-readable description of their capabilties. This is done through GraphQL wrappers around various different data sources like Twitter, Reddit, and Youtube. The user builds a GraphQL query via an interactive tree building front-end component. The canonical example of a user this application is someone who is interested in the vast amount of data available on the Internet, but don't know how to program. For instance, a public health researcher might be interested in extracting data from Tweets to learn about any trends or new serious diseases. Queries can also be ran on a set schedule( i.e. every day, every month, etc). Users can collect data over a long period of time. Listen Online provides an easy way to listen to the social universe for more people, which was not the case before as only programmers know how to work with APIs. 

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites
1. Install nodejs

### Installing

1. Run ```npm install ``` to install all dependencies

2. Next we need to setup the MySQL DB
    - create a new user in the mysql database https://linuxize.com/post/how-to-create-mysql-user-accounts-and-grant-privileges/

3. Run ```cp .env.example .env```. Note: the credentials in .env are secret and should never be posted on github. 

4. Install mongodb

5. Install a mysql gui tool (SequelPro, phpmyadmin, etc.)

6. Run ```npm run init```. This will populate the mysql database with a test user that has admin privaleges. Press ctrl+c to stop once the message saying the database has been initialized has appeared.

7. Run ```npm run dev``` for development and ```npm run prod``` for a production environment.
 
## Adding GraphQL servers


## Running the tests

WIP

## Deployment
Use the sshIntoBaseServer.sh and sshIntoGraphQLserver.sh shell scripts to ssh into the base lion server and graphqlserver respectively. The code is found under /var/www. Simply pull the latest version from git.

PM2 is used to keep the server running: https://pm2.keymetrics.io/

## Built With
- React
- Express
- MongoDB
- MySQL