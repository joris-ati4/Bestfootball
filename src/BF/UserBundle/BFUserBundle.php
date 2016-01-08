<?php

namespace BF\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BFUserBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
