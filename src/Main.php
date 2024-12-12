<?php

declare(strict_types=1);

namespace OnlyJaiden\DiscordLink;

use OnlyJaiden\DiscordLink\commands\LinkDiscordCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use DiscordBot\DiscordBot;
use DiscordBot\DiscordMessage;
use Exception;

class Main extends PluginBase implements Listener {

    public Config $config;
    private DiscordBot $discordBot;
    private bool $testMode;

    public function onEnable(): void {
      
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "data.yml", Config::YAML);
        $this->testMode = $this->getConfig()->get("test-mode", false);
        
        $this->getServer()->getCommandMap()->register("link", new LinkDiscordCommand($this));

        if (!$this->testMode) {
            $this->discordBot = new DiscordBot($this->getConfig()->get("bot-token"));

            $this->discordBot->on('message', function (DiscordMessage $message) {
                $this->handleDiscordMessage($message);
            });
        }
    }

    private function setVerificationCode(string $username, string $code): void {
        $data = $this->config->getAll();
        $data[$username] = $code;
        $this->config->setAll($data);
        $this->config->save();
    }

    private function handleDiscordMessage(DiscordMessage $message): void {
        if ($this->testMode) {
            return;
        }

        try {
            if ($message->isDM()) {
                $userId = $message->getAuthor()->getId();
                $content = trim($message->getContent());
                if (isset($this->config->getAll()[$content])) {
                    $username = $this->config->get($content);

                    $data = $this->config->getAll();
                    $data[$username] = $userId;
                    $this->config->setAll($data);
                    $this->config->save();

                    $message->reply("Your Discord account has been successfully linked to $username!");
                } else {
                    $message->reply("Invalid verification code.");
                }
            }
        } catch (Exception $e) {
            $this->getLogger()->error("Error handling Discord message: " . $e->getMessage());
        }
    }

    public function isTestMode(): bool {
        return $this->testMode;
    }

    public function onDisable(): void {
        if (!$this->testMode) {
            $this->discordBot->close();
        }
    }
}
