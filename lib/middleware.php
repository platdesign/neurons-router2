<?php
namespace router;
use nrns;




	class middleware {
			private $handler, $routeString, $method;


			public function __construct($method, $routeString, $handler) {

				$this->method = strtoupper($method);
				$this->routeString = $routeString;
				$this->handler = $handler;

			}


			public function matches($method, $routeString) {

				if($method === $this->method || $this->method === 'USES' || $this->method === 'ALL') {
					$paramValues = (object) [];

					$regex = preg_replace_callback(
						'#:([\w]+)(:([^/\(\)]*))?#',
						function($matches) use ($paramValues) {
							if(isset($matches[3])) {
								return '(?P<'.$matches[1].'>'.$matches[3].')';
							}

							$paramValues->{$matches[1]} = null;
							return '(?P<'.$matches[1].'>[^/\?]+)';
						},
						str_replace([')', '*'], [')?', '(.*)'], $this->routeString)
					);


					if(preg_match('#^'.$regex.'(?:\?.*)?$#i', $routeString, $matches)) {

						foreach($paramValues as $k => $v) {
							$this->params->{$k} = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
						}
						return true;
					}
				}

			}


			public function prependRouteString($prefix) {
				$this->routeString = '/'.trim(str_replace('//', '/', $prefix . $this->routeString), '/');
			}

			public function execute() {
				$this->_executeHandler($this->handler);
			}

			private function _executeHandler($handler) {
				if( is_array($handler) ) {
					foreach($handler as $h) {
						if( $this->_executeHandler($h) !== NULL) {
							$this->stop();
						}
						if($this->stopped) {
							break;
						}
					}
				}

				if( is_callable($handler) ) {
					return nrns::$injection->invoke($handler, ['route'=>$this]);
				}
			}

			public function stop() {
				$this->stopped=true;
			}

			public function __toString() {
				return $this->routeString;
			}
		}


 ?>