<?php

declare(strict_types = 1);

if (!isset($framework) || !is_string($framework)) {
	$framework = 'Symfony';
}

if (!isset($version) || !is_string($version)) {
	$version = 'unknown';
}

if (!isset($message) || !is_string($message)) {
	$message = 'Hello ' . $framework . ' ' . $version;
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="UTF-8" />
	<title>
		<?= htmlspecialchars($framework, ENT_QUOTES, 'UTF-8'); ?>
		<?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?>
	</title>
</head>
<body>
	<h1><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></h1>
	<p>
		Framework: <?= htmlspecialchars($framework, ENT_QUOTES, 'UTF-8'); ?>
		| Version: <?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?>
	</p>
</body>
</html>
