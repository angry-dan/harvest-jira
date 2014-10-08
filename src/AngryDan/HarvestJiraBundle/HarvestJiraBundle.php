<?php

namespace AngryDan\HarvestJiraBundle;

use AngryDan\HarvestJiraBundle\DependencyInjection\Compiler\JiraAuthenticationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HarvestJiraBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new JiraAuthenticationCompilerPass());
    }
}
