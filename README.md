#neurons-router2#

A new router-module for [Neurons](https://github.com/platdesign/Neurons).

##install##
`bower install platdesign/neurons-router2 --save`

##provider#

###$routeProvider###

Define route-handlers for different request-methods: `get`, `post`, `put`, `delete` and `uses` (for alle request-methods).

The following example takes effect for all request-methods.

####get($route, [$handler, ...])####

	$routeProvider->get('/home', function($response) {
		$response->setBody('Hello World');
	});

