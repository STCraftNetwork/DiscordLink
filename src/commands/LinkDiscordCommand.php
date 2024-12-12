<?php

declare(strict_types=1);

namespace OnlyJaiden\DiscordLink\commands;

use OnlyJaiden\DiscordLink\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class LinkDiscordCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("link", "Link your Discord account to your Minecraft username.", "/link", []);
        $this->setPermission("discordlink.command.linkdiscord");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        $username = $sender->getName();
        $verificationCode = $this->generateVerificationCode();

        $this->plugin->setVerificationCode($username, $verificationCode);

        $sender->sendMessage(TextFormat::GREEN . "Verification code: $verificationCode. Please check your Discord DM for further instructions.");
        return true;
    }

    private function generateVerificationCode(): string {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = 6;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }
}
