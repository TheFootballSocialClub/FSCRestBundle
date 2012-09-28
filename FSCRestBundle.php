<?php

namespace FSC\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use FSC\RestBundle\DependencyInjection\Compiler\RestResourceCompilerPass;

class FSCRestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RestResourceCompilerPass());
    }

}
