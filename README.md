# Listen Online

Listen Online is a querying mediator for web services that do not provide a machine-readable description of their capabilties. This is done through GraphQL wrappers around various different data sources like Twitter, Reddit, and Youtube. The user builds a GraphQL query via an interactive tree building front-end component. The canonical example of a user this application is someone who is interested in the vast amount of data available on the Internet, but don't know how to program. For instance, a public health researcher might be interested in extracting data from Tweets to learn about any trends or new serious diseases. Queries can also be ran on a set schedule( i.e. every day, every month, etc). Users can collect data over a long period of time. Listen Online provides an easy way to listen to the social universe for more people, which was not the case before as only programmers know how to work with APIs. 

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites
1. Install php https://computingforgeeks.com/how-to-install-php-on-ubuntu/

2. Install composer https://getcomposer.org/download/

3. Install php laravel https://laravel.com/docs/5.8/installation



### Installing

1. Run ```composer install ``` to install all dependencies

2. Next we need to setup the MySQL DB:

    1. Create a database with name "default" and user with username "default" and password "secret" as per .env file https://www.a2hosting.com/kb/developer-corner/mysql/managing-mysql-databases-and-users-from-the-command-line

    2. Insert a fake user into the DB which will be the first user of lion (currently no functionality for registering a new user through the user interface)
    https://stackoverflow.com/questions/34382043/laravel-how-do-i-insert-the-first-user-in-the-database

3. Install phpmyadmin for managing the DB using a GUI 
https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-on-ubuntu-18-04

    **Note:** If you cannot connect to localhost/phpmyadmin then follow these instructions https://stackoverflow.com/questions/10111455/i-cant-access-http-localhost-phpmyadmin

4. Run ```php artisan migrate``` to run migrations

    **Note:** If you get an error saying cannot connect to the MySQL DB with user default, then the DB wasn't setup correctly (see step 2)

6. Run ```cp .env.example .env``` The .env file is not on remote because it will contain **sensitive information** for the application (i.e. client IDS and client secrets for various web services)

7. Run ```php artisan key:generate``` **If the application key is not set, your user sessions and other encrypted data will not be secure!**

8. Run ```php artisan serve``` to serve the application. It will by default run on localhost:8000

9. Try logging in as the fake user that was created

## Logging into third-party services
1. In order to use third-party web services like Reddit, you need to create a developer account for each web service. Set the redirect URI to localhost:8000/login/{provider}/return where {provider} is the provider that you are creating the account for (currently either "reddit" or "youtube" or "twitter")

2. From step 1, you should now have a client ID, client secret, and redirect URI. Copy and paste each of those fields into the .env file for the respective provider. 

3. To log into Reddit for example, either navigate to localhost:8000/login/reddit or click on the reddit logo under the authorize service providers section. Your clientID, client secret, and redirect URI will automatically be added to the URL. The user needs to authorize access for Listen Online. 
 
## Adding GraphQL servers
GraphQL servers are added dynamically, so there won't be anything registered out of the box. Currently the method of adding new GraphQL servers is slow and can be improved (automatically creating GraphQL schemas from Swagger API docs)

1. Start the GraphQL server in a new terminal, instructions found here: 

1. Go to "manage servers" at the top, and click on "add a new server" button
    1. Set name to anything
    2.  For the server, copy and paste the address of the GraphQL server(instructions for starting the server found here as an example:https://github.com/wangleex/youtube-graphql ). Each GraphQL server has the same instructions.
    3. For the slug, set to either "reddit" or "youtube" or "twitter". 
    4. Add a short description. A description is required.
    5. Set authorization to 1 and authentication to 0
        * authorization means user needs to authorize that Listen Online can use their data from the respective web service
        * authentication means confirming your own identity


## Running the tests

WIP

## Deployment

WIP (Need to figure out Laradock)

## Built With

* [Vue.js](https://vuejs.org/v2/guide/) - The web framework used
* [PHP Laravel](https://laravel.com/docs/5.8/) - Dependency Management