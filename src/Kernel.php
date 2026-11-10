<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Compiler\ResettableServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        // Symfony 8.1 runs ResettableServicePass before LoggerPass (both at -32), so the services
        // resetter misses the "kernel.reset" tag of the default logger. In debug, the logger then
        // keeps the logs of all the requests of a FrankenPHP worker. Run the pass again after it.
        $container->addCompilerPass(new ResettableServicePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -33);
    }

    private function getAllowedEnvs(): array
    {
        return ['prod', 'dev', 'test'];
    }
}
