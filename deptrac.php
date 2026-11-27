<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\ClassLikeConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

/*
 * Module boundaries.
 *
 * One layer per <Module>.<Layer>; vendor code is grouped in Framework / Messenger / Validator / Patchlevel.
 */
return static function (DeptracConfig $config): void {
    $modules = ['Accounts', 'Meetings', 'Payments', 'Notifications'];

    $classLike = static fn (string $name, string $pattern): Layer => Layer::withName($name)
        ->collectors(ClassLikeConfig::create($pattern));

    $sharedKernelDomain = $classLike('SharedKernel.Domain', '^App\\SharedKernel\\Domain\\.*');
    $sharedKernel = $classLike('SharedKernel', '^App\\SharedKernel\\(?!Domain\\).*');

    $messenger = $classLike('Messenger', '^Symfony\\Component\\Messenger\\.*');
    $validator = $classLike('Validator', '^Symfony\\Component\\Validator\\.*');
    $patchlevel = $classLike('Patchlevel', '^Patchlevel\\.*');
    $framework = $classLike(
        'Framework',
        '^(Symfony\\(?!Component\\(Messenger|Validator)\\)|Psr\\|ApiPlatform\\|Doctrine\\).*',
    );

    $layers = [$sharedKernelDomain, $sharedKernel, $messenger, $validator, $patchlevel, $framework];
    $rulesets = [Ruleset::forLayer($sharedKernel), Ruleset::forLayer($sharedKernelDomain)];

    /** @var array<string, array<string, Layer>> $byModule */
    $byModule = [];

    foreach ($modules as $module) {
        foreach (['Domain', 'Application', 'Infrastructure', 'Ui', 'Contract'] as $layer) {
            $byModule[$module][$layer] = $classLike(
                $module.'.'.$layer,
                '^App\\'.$module.'\\'.$layer.'\\.*',
            );
        }

        array_push($layers, ...array_values($byModule[$module]));
    }

    $contracts = array_map(static fn (array $layers): Layer => $layers['Contract'], $byModule);

    foreach ($modules as $module) {
        $domain = $byModule[$module]['Domain'];
        $application = $byModule[$module]['Application'];
        $contract = $byModule[$module]['Contract'];

        $rulesets[] = Ruleset::forLayer($domain)
            ->accesses($sharedKernelDomain, ...('Payments' === $module ? [$patchlevel] : []));
        $rulesets[] = Ruleset::forLayer($application)
            ->accesses($domain, $sharedKernelDomain, $sharedKernel, $messenger, $validator, ...array_values($contracts));
        $rulesets[] = Ruleset::forLayer($byModule[$module]['Infrastructure'])
            ->accesses(
                $domain,
                $application,
                $sharedKernelDomain,
                $sharedKernel,
                $messenger,
                $validator,
                $framework,
                ...('Payments' === $module ? [$patchlevel] : []),
                ...array_values($contracts),
            );
        $rulesets[] = Ruleset::forLayer($byModule[$module]['Ui'])
            ->accesses($application, $contract, $sharedKernel, $messenger, $validator, $framework);
        $rulesets[] = Ruleset::forLayer($contract)
            ->accesses($sharedKernelDomain, $sharedKernel);
    }

    $config
        ->paths('./src')
        ->excludeFiles('#src/Kernel\.php#')
        ->layers(...$layers)
        ->rulesets(...$rulesets);
};
