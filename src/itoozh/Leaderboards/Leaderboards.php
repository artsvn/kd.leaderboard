<?php

namespace itoozh\Leaderboards;

use itoozh\Leaderboards\commands\LeaderboardCommand;
use itoozh\Leaderboards\entitys\TopDeathsEntity;
use itoozh\Leaderboards\entitys\TopKillsEntity;
use JetBrains\PhpStorm\Pure;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\world\World;

class Leaderboards extends PluginBase implements Listener
{

    public static array $cache = [];

    protected Config $deathData;

    protected Config $killData;

    protected static self $instance;

    protected function onEnable(): void
    {
        $this->getServer()->getCommandMap()->register("leaderboards", new LeaderboardCommand());

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->killData = new Config($this->getDataFolder() . "killdata.yml", Config::YAML);
        $this->deathData = new Config($this->getDataFolder() . "deathdata.yml", Config::YAML);
        
        $this->getServer()->getNetwork()->setName(TextFormat::colorize($this->getConfig()->get("server-motd")));

        EntityFactory::getInstance()->register(TopDeathsEntity::class, function (World $world, CompoundTag $nbt): TopDeathsEntity {
            return new TopDeathsEntity(EntityDataHelper::parseLocation($nbt, $world), TopDeathsEntity::parseSkinNBT($nbt), $nbt);
        }, ['TopDeathsEntity']);

        EntityFactory::getInstance()->register(TopKillsEntity::class, function (World $world, CompoundTag $nbt): TopKillsEntity {
            return new TopKillsEntity(EntityDataHelper::parseLocation($nbt, $world), TopKillsEntity::parseSkinNBT($nbt), $nbt);
        }, ['TopKillsEntity']);
    }

    protected function onLoad(): void{
        self::$instance = $this;
    }

    /**
     * @return Leaderboards
     */
    public static function getInstance(): Leaderboards{
        return self::$instance;
    }

    #[Pure] public static function getKillsAsRaw(): array{
        $data = [];
        foreach (self::getInstance()->killData->getAll() as $player => $kills){
            $data[$player] = $kills;
        }
        return $data;
    }

    #[Pure] public static function getDeathsAsRaw(): array{
        $data = [];
        foreach (self::getInstance()->deathData->getAll() as $player => $deaths){
            $data[$player] = $deaths;
        }
        return $data;
    }

    public static function getKills(string $player): int{
        return intval(self::getInstance()->killData->get($player, 0));
    }

    public static function addKills(string $player): void{
        self::getInstance()->killData->set($player, self::getKills($player) + 1);
    }

    public static function getDeaths(string $player): int{
        return intval(self::getInstance()->deathData->get($player, 0));
    }

    public static function addDeaths(string $player): void{
        self::getInstance()->deathData->set($player, self::getDeaths($player) + 1);
        self::getInstance()->deathData->save();
    }

    public function onJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        if($this->killData->get($player->getName(), false) === false) {
            $this->killData->set($player->getName(), 0);
            $this->killData->save();
        }

        if($this->deathData->get($player->getName(), false) === false){
            $this->deathData->set($player->getName(), 0);
            $this->deathData->save();
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void{
        $player = $event->getDamager();
        if($player instanceof Player){
            $entity = $event->getEntity();
            if($entity instanceof TopDeathsEntity || $entity instanceof TopKillsEntity )
            {
                $event->cancel();
            }

            if(isset(self::$cache[$player->getName()])){
                if($entity instanceof TopDeathsEntity || $entity instanceof TopKillsEntity)
                {
                    $entity->flagForDespawn();
                }
                unset(self::$cache[$player->getName()]);
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void{
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        self::addDeaths($player->getName());
        if($cause instanceof EntityDamageByEntityEvent){
            $killer = $cause->getDamager();
            if($killer instanceof Player){
                self::addKills($killer->getName());
            }
        }
    }

}
