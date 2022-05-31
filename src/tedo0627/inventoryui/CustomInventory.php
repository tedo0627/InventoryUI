<?php

namespace tedo0627\inventoryui;

use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;

class CustomInventory extends SimpleInventory {

    private array $entities = [];

    public function getPackets(Player $player, int $id): array {
        $name = $player->getName();
        if (!array_key_exists($name, $this->entities)) {
            $entity = new InventoryEntity($player->getLocation());
            $entity->setSlot($this->getSize());
            $this->entities[$name] = $entity;
        } else {
            $entity = $this->entities[$name];
        }

        $entity->spawnTo($player);

        $link = new EntityLink($player->getId(), $entity->getId(), EntityLink::TYPE_RIDER, true, true);
        $pk1 = SetActorLinkPacket::create($link);

        $pk2 = ContainerOpenPacket::entityInv($id, WindowTypes::CONTAINER, $entity->getId());
        $pk2->blockPosition = BlockPosition::fromVector3($entity->getLocation());

        return [$pk1, $pk2];
    }

    public final function onOpen(Player $who): void {
        parent::onOpen($who);

        $this->open($who);
    }

    public final function onClose(Player $who): void {
        parent::onClose($who);

        $name = $who->getName();
        if (array_key_exists($name, $this->entities)) {
            $this->entities[$name]->close();
            unset($this->entities[$name]);
        }

        $this->close($who);
    }

    public function open(Player $Player): void {

    }

    public function close(Player $player): void {

    }

    public function click(Player $player, int $slot): bool {
        return false;
    }
}