<?php

namespace Svc\SvcTotpBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SvcTotpBundle extends Bundle
{
  public function getPath(): string
  {
    return \dirname(__DIR__);
  }
}
