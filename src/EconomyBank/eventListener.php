<?php

    namespace EconomyBank;


    use onebone\economyapi\EconomyAPI;
    use pocketmine\event\Listener;
    use pocketmine\event\player\PlayerLoginEvent;
    use pocketmine\event\server\DataPacketReceiveEvent;
    use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
    use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
    use pocketmine\plugin\Plugin;

    class eventListener implements Listener
    {
        private $plugin;

        public function __construct(Plugin $plugin)
        {
            $this->plugin = $plugin;
        }

        public function onLogin(PlayerLoginEvent $event)
        {
            $player = $event->getPlayer();
            $name = $player->getName();
            $this->plugin->money->set($name, 0);
        }

        public function onDataPacketReceive(DataPacketReceiveEvent $event)
        {
            $player = $event->getPlayer();
            $name = $player->getName();
            $receive_packet = $event->getPacket();
            if ($receive_packet instanceof ModalFormResponsePacket) {
                $formId = $receive_packet->formId;
                $formData = json_decode($receive_packet->formData, true);
                if ($formData !== null) {
                    if ($formId === $this->plugin->formId[0]) {
                        if ($formData === 0) {
                            $money = EconomyAPI::getInstance()->myMoney($player);
                            $bank_money = $this->plugin->money->get($name);
                            $packet = new ModalFormRequestPacket();
                            $form = array(
                                "type" => "custom_form",
                                "title" => "BANK",
                                "content" => array(
                                    array(
                                        "type" => "label",
                                        "text" => "あなたの所持金： {$money}\nあなたの預金： {$bank_money}",
                                    ),
                                    array(
                                        "type" => "input",
                                        "text" => "入金する金額",
                                    ),
                                ),
                            );
                            $packet->formData = json_encode($form);
                            $packet->formId = $this->plugin->formId[1];
                            $player->sendDataPacket($packet);
                        } else if ($formData === 1) {
                            $money = EconomyAPI::getInstance()->myMoney($player);
                            $bank_money = $this->plugin->money->get($name);
                            $packet = new ModalFormRequestPacket();
                            $form = array(
                                "type" => "custom_form",
                                "title" => "BANK",
                                "content" => array(
                                    array(
                                        "type" => "label",
                                        "text" => "あなたの所持金： {$money}\nあなたの預金： {$bank_money}",
                                    ),
                                    array(
                                        "type" => "input",
                                        "text" => "出金する金額",
                                    ),
                                ),
                            );
                            $packet->formData = json_encode($form);
                            $packet->formId = $this->plugin->formId[2];
                            $player->sendDataPacket($packet);
                        } else if ($formData === 2) {
                            $money = EconomyAPI::getInstance()->myMoney($player);
                            $bank_money = $this->plugin->money->get($name);
                            $packet = new ModalFormRequestPacket();
                            $form = array(
                                "type" => "form",
                                "title" => "BANK",
                                "content" => "あなたの所持金： {$money}\nあなたの預金： {$bank_money}\n--預金ランキング--",
                                "buttons" => array(),
                            );
                            $count = 1;
                            $all_bank = $this->plugin->money->getAll();
                            foreach ($all_bank as $key => $value) {
                                $is_player = $this->plugin->getServer()->getOfflinePlayer($key);
                                if ($is_player !== null && $is_player->isOp()) {
                                    unset($all_bank[$key]);
                                }
                            }
                            arsort($all_bank);
                            foreach ($all_bank as $key => $value) {
                                $color = "§f";
                                if ($count === 1) {
                                    $color = "§l§e";
                                } else if ($count === 2) {
                                    $color = "§l§7";
                                } else if ($count === 3) {
                                    $color = "§l§6";
                                }
                                $form["content"] .= "\n{$color}{$count}§r. §a{$key}§r: §b{$value}";
                                $count++;
                            }
                            $packet->formData = json_encode($form);
                            $packet->formId = $this->plugin->formId[3];
                            $player->sendDataPacket($packet);
                        }
                    } else if ($formId === $this->plugin->formId[1]) {
                        $money = EconomyAPI::getInstance()->myMoney($player);
                        //$bank_money = $this->plugin->money->get($name);
                        $in_money = $formData[1];
                        if (is_numeric($in_money)) {
                          if ($in_money >= 0){
                            if ($in_money <= $money) {
                                EconomyAPI::getInstance()->reduceMoney($player, $in_money);
                                $this->plugin->money->set($name, $this->plugin->money->get($name) + $in_money);
                                $player->sendMessage(main::SUCCESS_TAG . "{$in_money} の預入に成功しました！");
                            } else {
                                $player->sendMessage(main::ERROR_TAG . "所持金が不足しています！");
                            }
                          }else{
                              $player->sendMessage(main::ERROR_TAG . "金額は0以上である必要があります！");//ここをnaraponが追加しました
                          }
                        } else {
                            $player->sendMessage(main::ERROR_TAG . "金額は数値である必要があります！");
                        }
                    } else if ($formId === $this->plugin->formId[2]) {
                        //$money = EconomyAPI::getInstance()->myMoney($player);
                        $bank_money = $this->plugin->money->get($name);
                        $out_money = $formData[1];
                        if (is_numeric($out_money)) {
                          if ($out_money >= 0){
                            if ($out_money <= $bank_money) {
                                EconomyAPI::getInstance()->addMoney($player, $out_money);
                                $this->plugin->money->set($name, $this->plugin->money->get($name) - $out_money);
                                $player->sendMessage(main::SUCCESS_TAG . "{$out_money} の出金に成功しました！");
                            } else {
                                $player->sendMessage(main::ERROR_TAG . "預金が不足しています！");
                            }
                          }else{
                              $player->sendMessage(main::ERROR_TAG . "金額は0以上である必要があります！");//ここをnaraponが追加しました
                          }
                        } else {
                            $player->sendMessage(main::ERROR_TAG . "金額は数値である必要があります！");
                        }
                    }
                }
            }
        }
    }
