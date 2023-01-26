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
        parent::__construct("top", "kitscore.pckt.me | 19132");
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
            $sender->sendMessage('§r§f§lLEADER§6BOARD');
            $sender->sendMessage('§r§6Version:§r§fKITS§aCORE');

            $sender->sendmessage('§r§6Description:§r§f This plugin is edited and not owned by me.');
            $sender->sendMessage('§r§r ');
            $sender->sendMessage('§r§f/top kills - §eDisplay top kills');
            $sender->sendMessage('§r§f/top deaths -§eDisplay top deaths');
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
                $sender->sendMessage(TextFormat::colorize('§r§aCreated successfully!'));
            }
        }
        if (strtolower($args[0]) === 'deaths') {
            if (!isset($args[1])) {
                if (!$sender instanceof Player) {
                    return;
                }
                $entity = TopDeathsEntity::create($sender);
                $entity->spawnToAll();
                $sender->sendMessage(TextFormat::colorize('§r§aCreated successfully!'));
            }
        }
    }
}
