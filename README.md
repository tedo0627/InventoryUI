# InventoryUI
This is the PocketMine virion that implements the dummy inventory.

## Differences from previous APIs
* Chests and other blocks are not placed.
* If a player falls or moves by water or other means when the inventory is open, the inventory will not close.
* You can specify any number of slots in your inventory. (Up to 1000 confirmed)
* Vertical length of inventory slots can be specified. (max. 6)

## How to install
1. Download [InventoryUIResourcePack.mcpack](https://github.com/tedo0627/InventoryUIResourcePack/releases/) and put it in the resource_packs folder
2. Open file ```resource_packs.yml```, set **force_resources** to ```true``` and **resource_stack** to ```InventoryUIResourcePack.mcpack```
```yml
force_resources: true
resource_stack:
  - InventoryUIResourcePack.mcpack
```

## Usage
The following code must be called when the plugin is enabled.
```php
InventoryUI::setup($this);
```
### Open custom inventory
```$slot``` specifies the number of slots in the inventory and must be a number greater than zero.  
```$title``` is the name of the inventory.  
```$length``` is entered as the vertical length of the inventory. If null, it is automatically adjusted.
```php
/** @var Player $player */
$player->setCurrentWindow(new CustomInventory($slot, $title, $length));
```

### Extend custom inventory
```php
class SampleInventory extends CustomInventory {

    public function __construct() {
        parent::__construct(54, "Sample Inventory");
    }

    public function open(Player $player): void {
        // Called when a player opens this inventory.
    }

    public function tick(int $tick): void {
        // Called every tick when someone opens inventory.
    }

    public function click(Player $player, int $slot, Item $sourceItem, Item $targetItem): bool {
        // It is called when a slot in the inventory is operated.
        // If the return value is true, the operation is canceled.
        return false;
    }

    public function close(Player $player): void {
        // Called when the player closes the inventory.
    }
}
```

Sample plugin [here](https://github.com/tedo0627/SampleInventoryUI)

## License
"InventoryUI" is under [MIT License](https://github.com/tedo0627/InventoryUI/blob/master/LICENSE)

## Special Thanks
Help resource pack [@famima65536](https://github.com/famima65536)
