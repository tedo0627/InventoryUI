<?php

namespace tedo0627\inventoryui;

use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;

class CustomInventory extends SimpleInventory {

    private int $tick = -1;

    private string $title;
    private int $length;
    private array $entities = [];

    public function __construct(int $size, string $title = "inventory", ?int $verticalLength = null) {
        if ($size < 0) {
            throw new IllegalInventorySizeException("The size of the inventory must be greater than 0");
        }
        parent::__construct($size);

        $this->title = $title;
        if ($verticalLength != null && 0 <= $verticalLength && $verticalLength <= 6) {
            $this->length = $verticalLength;
        } else {
            $length = $size / 9 + ($size % 9 == 0 ? 0 : 1);
            if ($length > 6) $length = 6;

            $this->length = $length;
        }
    }

    public function getPackets(Player $player, int $id): array {
        $name = $player->getName();
        if (!array_key_exists($name, $this->entities)) {
            $entity = new InventoryEntity($player->getLocation());
            $entity->setSlot($this->getSize());
            $entity->setNameTag("§" . $this->length . "§r§r§r§r§r§r§r§r§r§r" . $this->title);
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

    public final function onTick(int $tick): void {
        if ($this->tick === $tick) return;

        $this->tick = $tick;
        $this->onTick($tick);
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getVerticalLength(): int {
        return $this->length;
    }

    public function open(Player $Player): void {

    }

    public function close(Player $player): void {

    }

    public function click(Player $player, int $slot, Item $sourceItem, Item $targetItem): bool {
        return false;
    }

    public function tick(int $tick): void {

    }
}