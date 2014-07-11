<?php

namespace router;
use nrns;



	class Router {

		private $routes = [];

		public function __construct() {
		}

		public function getRoutes() {
			return $this->routes;
		}

		public function uses() {
			$args = func_get_args();

			if( count($args) === 1 ) {
				if( $args[0] instanceof Router ) {
					$this->addRoutesFromRouter($args[0]);
				} else {
					return $this->_addRoute(array_merge(['uses'], $args));
				}
			} else {
				if($args[1] instanceof Router) {
					$this->addRoutesFromRouter($args[1], $args[0]);
				} else {
					return $this->_addRoute(array_merge(['uses'], $args));
				}
			}
		}

		private function addRoutesFromRouter($router, $basePath='/') {
			foreach($router->getRoutes() as $route) {
				$route->prependRouteString($basePath);
				$this->routes[] = $route;
			}
		}

		public function get() {
			return $this->_addRoute(array_merge(['get'], func_get_args()));
		}

		public function post() {
			return $this->_addRoute(array_merge(['post'], func_get_args()));
		}

		public function put() {
			return $this->_addRoute(array_merge(['put'], func_get_args()));
		}

		public function delete() {
			return $this->_addRoute(array_merge(['delete'], func_get_args()));
		}


		private function _addRoute($args) {
			return call_user_func_array([$this, 'addRoute'], $args);
		}


		public function addRoute($method) {
			$route = str_replace("//", "/", $route);

			$args = array_splice(func_get_args(), 1);

			if( is_string($args[0]) ) {
				$routeString = $args[0];
				$handler = array_splice($args, 1);
			} else {
				$routeString = '/*';
				$handler = $args;
			}


			$this->routes[] = nrns::$injection->invoke('router\middleware', [
				'method'		=>	$method,
				'routeString' 	=>	$routeString,
				'handler' 		=>	$handler
			]);

			return $this;
		}

	}




?>