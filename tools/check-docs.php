<?php

declare(strict_types=1);

use App\Tools\Docs\AdrLinter;
use App\Tools\Docs\MarkdownLinkChecker;

require __DIR__.'/../vendor/autoload.php';

$root = \dirname(__DIR__);
$skipped = ['vendor', 'var', 'node_modules', '.git', '.codegraph', '.idea', '.claude', '.phpunit.cache'];

$pages = [];
$files = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    static fn (SplFileInfo $file): bool => !in_array($file->getFilename(), $skipped, true),
));
/** @var SplFileInfo $file */
foreach ($files as $file) {
    if ('md' === $file->getExtension()) {
        $pages[] = $file->getPathname();
    }
}
sort($pages);

$problems = [];
$links = new MarkdownLinkChecker();
foreach ($pages as $page) {
    $problems = [...$problems, ...$links->check($page, (string) file_get_contents($page))];
}

$adrLinter = new AdrLinter();
$adrNames = [];
foreach (glob($root.'/docs/adr/*.md') ?: [] as $adr) {
    $name = basename($adr);
    if ('adr-template.md' === $name) {
        continue;
    }
    $adrNames[] = $name;
    $problems = [...$problems, ...$adrLinter->lint($name, (string) file_get_contents($adr))];
}
$problems = [...$problems, ...$adrLinter->lintNumbering($adrNames)];

foreach ($problems as $problem) {
    fwrite(STDERR, str_replace($root.'/', '', $problem).PHP_EOL);
}

exit([] === $problems ? 0 : 1);
