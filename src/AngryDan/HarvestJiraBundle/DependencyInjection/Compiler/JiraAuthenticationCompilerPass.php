<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 30/09/2014
 * Time: 13:57
 */

namespace AngryDan\HarvestJiraBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class JiraAuthenticationCompilerPass
 * @package AngryDan\HarvestJiraBundle\DependencyInjection\Compiler
 */
class JiraAuthenticationCompilerPass implements CompilerPassInterface{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('jira_api.rest_client');
        $definition->setClass('AngryDan\HarvestJiraBundle\Sync\Jira\HttpClient');
        $definition->addMethodCall('setAuthenticationHelper', [new Reference('harvest_jira.jira.authentication_helper')]);
    }
}
