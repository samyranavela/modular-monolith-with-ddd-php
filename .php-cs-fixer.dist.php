<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/tools', __DIR__.'/config'])
    ->notPath(['reference.php', 'bootstrap.php', 'Tools/PHPStan/Fixtures']);

return new PhpCsFixer\Config()
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'php_unit_method_casing' => ['case' => 'snake_case'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache');
