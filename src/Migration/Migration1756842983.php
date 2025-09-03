<?php declare(strict_types=1);

namespace AltOffCanvas\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Defaults;

class Migration1756842983 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1756842983;
    }

    public function update(Connection $connection): void
    {
        $setId = Uuid::randomBytes();

        // Insert custom field set
        $connection->insert('custom_field_set', [
            'id' => $setId,
            'name' => 'your_plugin_cross_selling',
            'config' => json_encode([
                'label' => [
                    'en-GB' => 'Cross Selling Settings',
                    'de-DE' => 'Cross-Selling-Einstellungen',
                ],
            ]),
            'active' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // ✅ Link the set to the product entity
        $connection->insert('custom_field_set_relation', [
            'id' => Uuid::randomBytes(),
            'set_id' => $setId,
            'entity_name' => 'product',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);


        // Insert custom field
        $connection->insert('custom_field', [
            'id' => Uuid::randomBytes(),
            'name' => 'cross_selling_index',
            'type' => 'int',
            'config' => json_encode([
                'label' => [
                    'en-GB' => 'Cross-Selling Group Index',
                    'de-DE' => 'Cross-Selling-Gruppenindex',
                ],
                'customFieldType' => 'number',
            ]),
            'set_id' => $setId, // 👈 use set_id here
            'active' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    public function updateDestructive(Connection $connection): void
    {
        // rollback if needed
    }
}
