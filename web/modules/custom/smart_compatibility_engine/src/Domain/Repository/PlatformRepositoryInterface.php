<?php

namespace Drupal\smart_compatibility_engine\Domain\Repository;

use Drupal\smart_compatibility_engine\Domain\Model\Platform;

interface PlatformRepository
{

  public function get(int $id): Platform;

}
