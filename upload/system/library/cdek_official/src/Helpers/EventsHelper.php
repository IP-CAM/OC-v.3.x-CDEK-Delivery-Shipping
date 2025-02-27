<?php

namespace CDEK\Helpers;

use CDEK\RegistrySingleton;
use ModelSettingEvent;

class EventsHelper
{
    private const EVENTS
        = [
            'admin/view/sale/order_info/before'                       => [
                'cdek_official_order_info' => 'extension/shipping/cdek_official/orderInfo',
            ],
            'admin/controller/sale/order/info/before'                 => [
                'cdek_official_order_info_scripts' => 'extension/shipping/cdek_official/orderInfoScripts',
            ],
            'catalog/controller/checkout/shipping_method/save/before' => [
                'cdek_official_validate_office_code' => 'extension/shipping/cdek_official/validateOfficeCode',
            ],
            'catalog/controller/checkout/success/before'              => [
                'cdek_official_checkout_success' => 'extension/shipping/cdek_official/saveOfficeCode',
            ],
            'catalog/controller/checkout/confirm/after'               => [
                'cdek_official_checkout_confirm' => 'extension/shipping/cdek_official/saveOfficeCode',
            ],
            'catalog/view/common/header/before'                       => [
                'cdek_official_header_before' => 'extension/shipping/cdek_official/addCheckoutHeaderScript',
            ],
            'catalog/view/plaza/page_section/header_mobile/before'    => [
                'cdek_official_plaza_header_mobile_before' => 'extension/shipping/cdek_official/addCheckoutHeaderScript',
            ],
            'catalog/controller/checkout/uni_checkout/validate/before' => [
                'cdek_official_validate_office_code_uni' => 'extension/shipping/cdek_official/validateOfficeCode',
            ],
            'catalog/controller/checkout/uni_checkout/validate/after'  => [
                'cdek_official_checkout_success_uni' => 'extension/shipping/cdek_official/saveOfficeCode',
            ],
            'catalog/controller/checkout/simplecheckout/after'  => [
                'cdek_official_checkout_success_simple' => 'extension/shipping/cdek_official/saveOfficeCode',
            ],
        ];

    private const OBSOLETE_EVENTS
        = [
            'cdek_official_shipping',
            'cdek_official_checkout_map',
            'cdek_official_checkout',
            'cdek_official_order',
            'cdek_official_controller'
        ];

    public static function registerEvents(): void
    {
        $registry = RegistrySingleton::getInstance();

        LogHelper::write('create events');
        $registry->get('load')->model('setting/event');
        /** @var ModelSettingEvent $eventModel */
        $eventModel = $registry->get('model_setting_event');

        foreach (self::EVENTS as $trigger => $actions) {
            foreach ($actions as $actionName => $action) {
                if (empty($eventModel->getEventByCode($actionName))) {
                    $eventModel->addEvent($actionName, $trigger, $action, 1, 0);
                }
            }
        }

        LogHelper::write('events created');
    }

    public static function deleteEvents(): void
    {
        $registry = RegistrySingleton::getInstance();

        LogHelper::write('delete events');
        $registry->get('load')->model('setting/event');
        /** @var ModelSettingEvent $eventModel */
        $eventModel = $registry->get('model_setting_event');

        foreach (self::EVENTS as $actions) {
            foreach (array_keys($actions) as $event) {
                $eventModel->deleteEventByCode($event);
            }
        }

        foreach (self::OBSOLETE_EVENTS as $event) {
            $eventModel->deleteEventByCode($event);
        }
    }
}
