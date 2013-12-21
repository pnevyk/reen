<?php

function findTests($dir) {
	foreach (scandir($dir) as $file) {
		if ($file === '.' || $file === '..') continue;

		if (is_dir(realpath($dir . '/' . $file))) {
			if ($file === 'test') {
				foreach (scandir(realpath($dir . '/' . $file)) as $testCase) {
					if ($testCase === '.' || $testCase === '..') continue;
					if ($testCase === 'ReenTest.php') return;

					$path = realpath($dir . '/' . $file . '/' . $testCase);

					if (is_file($path)) {
						require_once($path);
					}
				}
			}

			findTests($dir . '/' . $file);
		}
	}
}

findTests(realpath(__DIR__ . '/../'));