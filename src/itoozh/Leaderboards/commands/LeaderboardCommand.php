<?php

declare(strict_types=1);

namespace itoozh\Leaderboards\commands;

use itoozh\Leaderboards\entitys\TopDeathsEntity;
use itoozh\Leaderboards\entitys\TopKillsEntity;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;


class LeaderboardCommand extends Command
{
    public function __construct()
    {
        parent::__construct("top", "edited by - e4fj");
        $this->setPermission('leaderboards.permission');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage('§r§7---------------------------------------------');
            $sender->sendMessage('§r ');
            $sender->sendMessage('§r§6§lTops Scoring');
            $sender->sendMessage('§r§6Version:§r§f 1.0.0');
            $sender->sendMessage('§r§6Editor:§r§f e4fj');
            $sender->sendmessage('§r§6Description:§r§f This plugin is edited and not owned by e4fj.');
            $sender->sendMessage('§r§r ');
            $sender->sendMessage('§r§f/top kills - §eUse this command to spawn the NPC from Kills');
            $sender->sendMessage('§r§f/top deaths - §eUse this command to spawn the NPC from Deaths');
            $sender->sendMessage('§r  ');
            $sender->sendMessage('§r§7---------------------------------------------');
            return;
        }
        if (strtolower($args[0]) === 'kills') {
            if (!isset($args[1])) {
                if (!$sender instanceof Player) {
                    return;
                }
                $entity = TopKillsEntity::create($sender);
                $entity->spawnToAll();
                $sender->sendMessage(TextFormat::colorize('§r§aKills Leaderboards created successfully!'));
            }
        }
        if (strtolower($args[0]) === 'deaths') {
            if (!isset($args[1])) {
                if (!$sender instanceof Player) {
                    return;
                }
                $entity = TopDeathsEntity::create($sender);
                $entity->spawnToAll();
                $sender->sendMessage(TextFormat::colorize('§r§aDeaths Leaderboards created successfully!'));
            }
        }
    }
}
