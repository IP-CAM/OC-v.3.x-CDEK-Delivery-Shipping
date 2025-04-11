<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\Config;
use CDEK\Exceptions\HttpServerException;
use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;
use JsonException;
use Exception;
use Language;
use Loader;
use Session;
use Url;

class GetOrderInfoTabAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $orderId): array
    {
        $registry = RegistrySingleton::getInstance();
        /** @var Loader $loader */
        $loader = $registry->get('load');

        $loader->model('sale/order');
        $loader->language('extension/shipping/cdek_official');

        /** @var Language $language */
        $language = $registry->get('language');
        /** @var Url $url */
        $url = $registry->get('url');
        /** @var Session $session */
        $session = $registry->get('session');

        $orderInfo = $registry->get('model_sale_order')->getOrder($orderId);
        $meta      = OrderMetaRepository::getOrder($orderId);

        $errors = $session->data['errors'] ?? [];

        unset($session->data['errors']);

        if (!empty($meta['cdek_uuid']) && empty($meta['cdek_number']) && empty($meta['deleted_at'])) {
            try {
                $order = CdekApi::getOrderByUuid($meta['cdek_uuid']);
            } catch (JsonException|HttpServerException $e) {
                $order['requests'][0]['errors'] = [
                    [
                        'message' => $e->getMessage(),
                    ],
                ];
            }

            if (!empty($order['requests'][0]['errors'])) {
                foreach ($order['requests'][0]['errors'] as $error) {
                    $errors[] = $error['message'];
                }
            } else {
                OrderMetaRepository::insertCdekTrack($orderId, $order['entity']['cdek_number']);
                $meta = OrderMetaRepository::getOrder($orderId);
            }
        }

        return [
            'code'    => Config::DELIVERY_NAME,
            'title'   => $language->get('heading_title'),
            'content' => explode('.', $orderInfo['shipping_code'])[0] !== 'cdek_official' ?
                $loader->view('extension/shipping/cdek_official/foreign_delivery') :
                $loader->view('extension/shipping/cdek_official/create_order', [
                    'orderInfo' => $orderInfo,
                    'direction' => explode('_', explode('.', $orderInfo['shipping_code'])[1])[0],
                    'meta'      => $meta,
                    'errors'    => $errors,
                    'actions'   => [
                        'create'  => $url->link('extension/shipping/cdek_official/create',
                                                http_build_query([
                                                                     'order_id'   => $orderId,
                                                                     'user_token' => $session->data['user_token'],
                                                                 ]),
                                                true),
                        'waybill' => $url->link('extension/shipping/cdek_official/waybill',
                                                http_build_query([
                                                                     'order_id'   => $orderId,
                                                                     'user_token' => $session->data['user_token'],
                                                                 ]),
                                                true),
                        'delete'  => $url->link('extension/shipping/cdek_official/delete',
                                                http_build_query([
                                                                     'order_id'   => $orderId,
                                                                     'user_token' => $session->data['user_token'],
                                                                 ]),
                                                true),
                    ],
                ]),
        ];
    }
}
