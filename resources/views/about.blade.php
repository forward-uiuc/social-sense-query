@extends('layouts.app')

@section('content')

<div class="container">
	<h2> About us </h2>
	
	<p> This system was developed by students of <a href="http://www.forwarddatalab.org/kevinchang"> Dr.  Kevin Chen-Chuan Chang</a> with the purpose of accessing data. Every day people make Terabytes of data through use of social media providers like Reddit, Youtube, and Twitter. Our goal is to lower the barrier of entry to accessing this data. The way that we accomplish this is a careful coordination between multiple technologies. Firstly, we build a <a href="https://graphql.org/"> GraphQL</a> server on top of the social media provider's API to represent the structure of the entities in their system. Next, users build queries using our visual, interactive query builder and these queries map to the GraphQL query language. These are sent to the GraphQL server which then takes the query and maps it to corresponding web requests. Eventually this returns data which is then stored and available for processing. </p>

<p> The real power of this system isn't the ability to make one-off queries, however. By working through the social media provider's API standards (Oauth1/2), we can take the authorizations that you provide and perform a query multiple times dependent on a schedule you set. This can be a simple as seeing what twitter hashtags are trending once a day to as interesting as looking for comments from a subreddit once a minute. </p>

<p> This is just the beginning of what we hope to provide. There are plenty of challenges moving forward, some of which are interesting research problems in themselves:
	<ul>
		<li> Query scheduling in the context of rate limiting: At the end of the day, queries boil down to multiple web requests that are subject to rate limiting dependent on the social media provider. This question is, in essence, if somebody presents a query and we want to make sure they get as much data as possible, can we break their query down into its constituent pieces and schedule it as efficiently as possible?</li> 

		<li>Query optimization: Similar to the above, each query consumes a finite resource for the user (namely, the number of web requests they can perform within a certain period of time). Is it possible for us to take their query and minimize the nubmer of web requests that they perform? Can we apply heuristics based off of the data that is returned to find what an optimal time to perform the query would be?  </li>
		
		<li> Mining API documentation for a structural, graph representation: As stated above, to support a different social media provider we develop a GraphQL schhema on top of their api. Presently, this is a manual process and there's not necessarily a good reason for it to be. With most documentation of API by these providers, we have semi-structured text data that describe entities and their relationships. It would be nice if we could mine this structure and synthesize a schema definition from it. </li>
	</ul>
</p>

<p> At the end of the day, we're really excited that you're interested in this system and hope that we can meet your expectations and help you get access to the data that you need. Thank you for your time and, if you are interested in the development of this system, feel free to check us out on Github where the source code for this project is kept open source and free. </p>
</div>
@endsection
