<?php

namespace tedo0627\inventoryui;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class InventoryEntity extends Entity {

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0, 0);
    }

    public static function getNetworkTypeId(): string {
        return "inventoryui:inventoryui";
    }

    protected function initEntity(CompoundTag $nbt): void {
        $this->setCanSaveWithChunk(false);
        $this->networkPropertiesDirty = true;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void {
        parent::syncNetworkData($properties);

        $properties->setByte(EntityMetadataProperties::CONTAINER_TYPE, WindowTypes::INVENTORY);
        $properties->setInt(EntityMetadataProperties::CONTAINER_BASE_SIZE, $this->slot);
    }

    protected int $slot = 9;

    public function getSlot(): int {
        return $this->slot;
    }

    public function setSlot(int $slot): void {
        $this->slot = $slot;
        $this->networkPropertiesDirty = true;

        $changedProperties = $this->getDirtyNetworkData();
        if (count($changedProperties) <= 0) return;

        $this->sendData(null, $changedProperties);
        $this->getNetworkProperties()->clearDirtyProperties();
    }
}