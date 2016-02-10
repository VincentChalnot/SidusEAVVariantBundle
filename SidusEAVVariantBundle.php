<?php

namespace Sidus\EAVVariantBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SidusEAVVariantBundle extends Bundle
{
    /**
     * Used to override family parsing
     * @return string
     */
    public function getParent()
    {
        return 'SidusEAVBootstrapBundle';
    }
}
