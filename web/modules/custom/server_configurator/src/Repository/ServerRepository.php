namespace Drupal\server_configurator\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

final class ServerRepository {

public function __construct(
private readonly EntityTypeManagerInterface $entityTypeManager,
) {}

/**
* @return \Drupal\node\NodeInterface[]
*/
public function findServersByPlatformIds(array $platformIds): array {
if (empty($platformIds)) {
return [];
}

$query = $this->entityTypeManager
->getStorage('node')
->getQuery()
->accessCheck(TRUE)
->condition('type', 'server')
->condition('status', 1)
->condition('field_platform', $platformIds, 'IN');

$serverIds = $query->execute();

if (empty($serverIds)) {
return [];
}

return $this->entityTypeManager
->getStorage('node')
->loadMultiple($serverIds);
}

}
