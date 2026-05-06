namespace Drupal\server_configurator\Service;

use Drupal\server_configurator\Repository\PlatformRepository;
use Drupal\server_configurator\Repository\ServerRepository;

final class ConfiguratorOptionsBuilder {

public function __construct(
private readonly PlatformRepository $platformRepository,
private readonly ServerRepository $serverRepository,
) {}

public function buildServerOptions(array $filters): array {
$platformIds = $this->platformRepository->findCompatiblePlatformIds($filters);

$servers = $this->serverRepository->findServersByPlatformIds($platformIds);

$options = [];

foreach ($servers as $server) {
$options[$server->id()] = $server->label();
}

return $options;
}

}
