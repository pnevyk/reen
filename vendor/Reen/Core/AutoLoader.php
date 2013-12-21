<?php

namespace Reen\Core;

use \Reen\Util;

class AutoLoader {
	public static function init($root) {
		spl_autoload_register(function ($className) use ($root) {
			$class = str_replace('\\', '/', $className);

			if (strpos($class, 'Reen') !== false) {
				$filename = $root . '/vendor/' . $class . '.php';

				if (is_readable($filename)) {
					require_once($filename);
					return;
				}

				throw new \Exception("Unable to load $className");
			}
			
			// devise how to load app related classes
		});
	}
}