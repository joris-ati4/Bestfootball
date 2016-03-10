<?php

namespace BF\ImageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BFImageBundle extends Bundle
{
	public function getParent()
    {
        return 'ComurImageBundle';
    }
}
