<?php

namespace tedo0627\inventoryui;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class InventoryTickTask extends Task {

    private int $tick = 0;

    public function __construct(private Server $server) { }

    public function onRun(): void {
        foreach ($this->server->getOnlinePlayers() as $player) {
            $inventory = $player->getCurrentWindow();
            if ($inventory instanceof CustomInventory) $inventory->tick($this->tick);
        }

        $this->tick++;
    }
}