<?PHP






	$module = nrns::module("router", []);


	$module->config(function(){

		require 'provider/routeProvider.php';

	});

	$module->provider("routeProvider", "router\\routeProvider");






?>
