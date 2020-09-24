<?php

use Bitrix\Main\Config\Option, Bitrix\Main\Loader, Bitrix\Sale\Order, \Bitrix\Main\Type\DateTime, \Sotbit\Cabinet\Element, Sotbit\Cabinet\Personal\Buyer;

class DialGadgets {
	public function getRecomendedOrders() {
		$result = [];
		$rsItems = CIBlockElement::GetList([], ["IBLOCK_ID" => 6, "ACTIVE" => "Y"], false, false, ["ID", "IBLOCK_ID", "NAME"]);
		while ($arItem = $rsItems->Fetch()) {
			$result[] = [
				"ID" => $arItem["ID"],
				"NAME" =>$arItem["NAME"]
			];
		}
		return $result;
	}
	
    private function getOrders($filter = []) {
        Loader::includeModule('sale');
        $result = [];
        $filter["LID"] = SITE_ID;
        $result = Order::getList([
            'select' => [
                "ID",
                'STATUS_ID',
                'PRICE',
                'CURRENCY',
                'DATE_INSERT'
            ],
            'filter' => $filter,
            'order'  => ["ID" => "DESC"]
        ]);
        return $result;
    }

    public function getOrdersLast($interval, $status = "N") {
        Loader::includeModule('sotbit.cabinet');
        $result = [
            "PRICE" => 0,
            "COUNT" => 0
        ];
        $dateInterval = new DateTime();
        $dateInterval->add("-1 " . $interval);
        $orders = $this->getOrders([">=DATE_INSERT" => $dateInterval, "STATUS_ID" => $status]);
        foreach ($orders as $order) {
            $result["PRICE"] += $order["PRICE"];
            $result["COUNT"]++;
        }
        $result["PRICE"] = CCurrencyLang::CurrencyFormat($result["PRICE"], "RUB");
        $result["COUNT"] = $result["COUNT"] . " " . Element::num2word($result["COUNT"], ["заказ", "заказа", "заказов"]);
        return $result;
    }

    public function findBuyersForUser($idUser = 0) {
        Loader::includeModule('sotbit.cabinet');
        $listBuyers = [];
        if (!\SotbitCabinet::getInstance()->isDemo() && $idUser > 0) {
            $filter = ["USER_ID" => $idUser];
            $rsBuyers = \CSaleOrderUserProps::GetList([], $filter);
            while ($buyer = $rsBuyers->fetch()) {
                $listBuyers[$buyer['ID']] = $buyer;
            }
            if ($listBuyers) {
                $db_propVals = \CSaleOrderUserPropsValue::GetList(["ID" => "ASC"], [
                    'CODE' => ['COMPANY', 'DEBET', 'EXPIRED_DEBET', 'STOP']
                ]);
                while ($arPropVals = $db_propVals->Fetch()) {
                    if ($arPropVals['VALUE'] && $listBuyers[$arPropVals['USER_PROPS_ID']]) {
                        $listBuyers[$arPropVals['USER_PROPS_ID']][$arPropVals['PROP_CODE']] = $arPropVals['VALUE'];
                    }
                }
            }
        }
        return $listBuyers;
    }
}