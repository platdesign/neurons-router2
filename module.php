<?PHP






	$module = nrns::module('router', []);


	$module->config(function(){
		require 'lib/Router.php';
		require 'lib/middleware.php';
		require 'provider/routeProvider.php';

	});

	$module->provider('routeProvider', 'router\\routeProvider');






?>
