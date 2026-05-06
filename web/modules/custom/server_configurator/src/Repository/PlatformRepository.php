namespace Drupal\server_configurator\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;

final class PlatformRepository {

public function __construct(
private readonly EntityTypeManagerInterface $entityTypeManager,
) {}

public function findCompatiblePlatformIds(array $filters): array {
$query = $this->entityTypeManager
->getStorage('node')
->getQuery()
->accessCheck(TRUE)
->condition('type', 'platform')
->condition('status', 1);

if (!empty($filters['cpu_generation'])) {
$query->condition('field_cpu_generation', $filters['cpu_generation']);
}

if (!empty($filters['form_factor'])) {
$query->condition('field_form_factor', $filters['form_factor']);
}

if (!empty($filters['storage_form_factor'])) {
$query->condition('field_storage_form_factor', $filters['storage_form_factor']);
}

return array_values($query->execute());
}

}
