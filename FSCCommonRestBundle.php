<?php

namespace FSC\Common\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use FSC\Common\RestBundle\DependencyInjection\Compiler\RestResourceCompilerPass;

class FSCCommonRestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RestResourceCompilerPass());
    }

}
