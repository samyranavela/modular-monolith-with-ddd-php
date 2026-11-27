<?php

declare(strict_types=1);

use App\Tools\Git\CommitMessageChecker;

require __DIR__.'/../vendor/autoload.php';

$options = getopt('', ['first']);

$problems = new CommitMessageChecker()->check(
    (string) stream_get_contents(STDIN),
    firstOfBranch: is_array($options) && array_key_exists('first', $options),
);

foreach ($problems as $problem) {
    fwrite(STDERR, 'Commit message: '.$problem.PHP_EOL);
}

exit([] === $problems ? 0 : 1);
