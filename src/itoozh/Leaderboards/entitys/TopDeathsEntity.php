<?php

declare(strict_types=1);

namespace itoozh\Leaderboards\entitys;

use itoozh\Leaderboards\Leaderboards;
use JetBrains\PhpStorm\Pure;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TopDeathsEntity extends Human
{
    public $canCollide = false;
    protected $gravity = 0.0;
    protected $immobile = true;
    protected $scale = 0.001;
    protected $drag = 0.0;



    /** @var int|null */

    /**
     * @param Player $player
     *
     * @return TopDeathsEntity
     */
    public static function create(Player $player): self
    {
        $nbt = CompoundTag::create()
            ->setTag("Pos", new ListTag([
                new DoubleTag($player->getLocation()->x),
                new DoubleTag($player->getLocation()->y),
                new DoubleTag($player->getLocation()->z)
            ]))
            ->setTag("Motion", new ListTag([
                new DoubleTag($player->getMotion()->x),
                new DoubleTag($player->getMotion()->y),
                new DoubleTag($player->getMotion()->z)
            ]))
            ->setTag("Rotation", new ListTag([
                new FloatTag($player->getLocation()->yaw),
                new FloatTag($player->getLocation()->pitch)
            ]));
        return new self($player->getLocation(), $player->getSkin(), $nbt);
    }

    #[Pure] protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.001, 0.001, 0.001);
    }

    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    /**
     * @param int $currentTick
     *
     * @return bool
     */
    public function onUpdate(int $currentTick): bool{
        $line = "–––––x–––––x–––––";
        $msg = $line . TextFormat::EOL;
        $msg .= TextFormat::BOLD . TextFormat::WHITE . "TOP DEATHS" . TextFormat::EOL;
        $msg .= TextFormat::EOL;
        $place = 1;
        $kills = Leaderboards::getDeathsAsRaw();
        arsort($kills);
        foreach($kills as $player => $kill){
            if($place > 5) break;
            $msg .= TextFormat::GRAY . $place . ". " . TextFormat::YELLOW . $player . " " . TextFormat::GRAY . "»" . TextFormat::RED . $kill . TextFormat::EOL;
            $place++;
        }
        $msg .= $line;
        $this->setNameTag($msg);
        $this->setNameTagAlwaysVisible(true);
        return parent::onUpdate($currentTick);
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();

        if (!$source instanceof EntityDamageByEntityEvent) {
            return;
        }

        $damager = $source->getDamager();

        if (!$damager instanceof Player) {
            return;
        }

        if ($damager->getInventory()->getItemInHand()->getId() === 276) {
            if ($damager->hasPermission('leaderboards.permission')) {
                $this->kill();
            }
            return;
        }

    }
}
