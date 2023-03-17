<?php

namespace tedo0627\inventoryui;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;

class EventListener implements Listener {

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $player->getNetworkSession()
            ->getInvManager()
            ?->getContainerOpenCallbacks()
            ->add(function(int $id, Inventory $inventory) use($player): ?array {
                if (!$inventory instanceof CustomInventory) return null;

                return $inventory->getPackets($player, $id);
            });
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        foreach ($transaction->getActions() as $action) {
            if (!$action instanceof SlotChangeAction) continue;

            $inventory = $action->getInventory();
            if (!$inventory instanceof CustomInventory) continue;

            $player = $transaction->getSource();
            $slot = $action->getSlot();
            if (!$inventory->click($player, $slot, $action->getSourceItem(), $action->getTargetItem())) continue;

            $event->cancel();
            return;
        }
    }
}
