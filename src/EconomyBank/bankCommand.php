<?php

    namespace EconomyBank;

    use onebone\economyapi\EconomyAPI;
    use pocketmine\command\Command;
    use pocketmine\command\CommandSender;
    use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
    use pocketmine\Player;
    use pocketmine\plugin\Plugin;

    class bankCommand extends Command
    {

        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
            parent::__construct("bank", "Bankを開く", "/bank");
            $this->setPermission("economybank.command.bank");
        }

        public function execute(CommandSender $sender, string $commandLabel, array $args): bool
        {
            $name = $sender->getName();
            if (!$sender instanceof Player) {
                $sender->sendMessage(main::ERROR_TAG . "このコマンドはプレイヤーのみ実行できます。");
                return true;
            }
            $money = EconomyAPI::getInstance()->myMoney($sender);
            $bank_money = $this->plugin->money->get($name);
            $packet = new ModalFormRequestPacket();
            $form = array(
                "type" => "form",
                "title" => "BANK",
                "content" => "あなたの所持金： {$money}\nあなたの預金： {$bank_money}",
                "buttons" => array(
                    array(
                        "text" => "入金する",
                    ),
                    array(
                        "text" => "出金する",
                    ),
                    array(
                        "text" => "預金ランキング",
                    ),
                ),
            );
            $packet->formData = json_encode($form);
            $packet->formId = $this->plugin->formId[0];
            $sender->sendDataPacket($packet);
            return true;
        }

    }