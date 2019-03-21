<?php

namespace Oyst\Services;

use Configuration;
use Language;
use OrderState;
use Validate;

class OystStatusService
{
    private $status = [
        'oyst_canceled' => [
            'prestashop_name' => 'PS_OS_CANCELED',
        ],
        'oyst_payment_waiting_validation' => [
            'prestashop_name' => 'OYST_OS_PAY_WAITING_VALIDATION',
            'data' => [
                'name' => [
                    'fr' => 'En attente de validation chez Oyst',
                ],
                'color' => '#360088',
                'unremovable' => true,
                'deleted' => false,
                'delivery' => false,
                'invoice' => false,
                'logable' => false,
                'paid' => false,
                'hidden' => false,
                'shipped' => false,
                'send_email' => false,
            ]
        ],
        'oyst_payment_captured' => [
            'prestashop_name' => 'OYST_OS_PAYMENT_CAPTURED',
            'data' => [
                'name' => [
                    'fr' => 'Paiement accepté (Oyst)',
                ],
                'color' => '#32CD32',
                'unremovable' => true,
                'deleted' => false,
                'delivery' => false,
                'invoice' => true,
                'logable' => true,
                'paid' => true,
                'hidden' => false,
                'shipped' => false,
                'send_email' => false,
            ]
        ],
        'oyst_payment_waiting_to_capture' => [
            'prestashop_name' => 'OYST_OS_PAY_WAITING_TO_CAPTURE',
            'data' => [
                'name' => [
                    'fr' => 'En attente de capture chez Oyst',
                ],
                'color' => '#FFA500',
                'unremovable' => true,
                'deleted' => false,
                'delivery' => false,
                'invoice' => false,
                'logable' => false,
                'paid' => false,
                'hidden' => false,
                'shipped' => false,
                'send_email' => false,
            ]
        ],
        'oyst_payment_to_capture' => [
            'prestashop_name' => 'OYST_OS_PAYMENT_TO_CAPTURE',
            'data' => [
                'name' => [
                    'fr' => 'A capturer',
                ],
                'color' => '#FFA500',
                'unremovable' => true,
                'deleted' => false,
                'delivery' => false,
                'invoice' => false,
                'logable' => false,
                'paid' => false,
                'hidden' => false,
                'shipped' => false,
                'send_email' => false,
            ]
        ],
		'oyst_partial_refund' => [
			'prestashop_name' => 'OYST_ORDER_STATUS_PARTIAL_REFUND',
			'data' => [
				'name' => [
					'fr' => 'Remboursement partiel',
				],
				'color' => '#E96756',
				'unremovable' => true,
				'deleted' => false,
				'delivery' => true,
				'invoice' => true,
				'logable' => true,
				'paid' => true,
				'hidden' => false,
				'shipped' => false,
				'send_email' => false,
			]
		],
    ];

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new OystStatusService();
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}

    public function createAllStatus()
    {
        $res = true;
        foreach ($this->status as $status) {
            $res &= $this->createStatus($status);
        }
        return $res;
    }

    public function createStatus($status)
    {
        $res = true;
        $order_state = new OrderState(Configuration::get($status['prestashop_name']));
        if (!Validate::isLoadedObject($order_state)) {
            foreach (Language::getLanguages() as $language) {
                if (isset($status['data']['name'][$language['iso_code']])) {
                    $name = $status['data']['name'][$language['iso_code']];
                } else {
                    $name = $status['data']['name']['fr'];
                }
                $order_state->name[$language['id_lang']] = $name;
            }
            $order_state->color = $status['data']['color'];
            $order_state->unremovable = $status['data']['unremovable'];
            $order_state->deleted = $status['data']['deleted'];
            $order_state->delivery = $status['data']['delivery'];
            $order_state->invoice = $status['data']['invoice'];
            $order_state->logable = $status['data']['logable'];
            $order_state->module_name = 'oyst';
            $order_state->paid = $status['data']['paid'];
            $order_state->hidden = $status['data']['hidden'];
            $order_state->shipped = $status['data']['shipped'];
            $order_state->send_email = $status['data']['send_email'];
            $res &= $order_state->add();
            Configuration::updateValue($status['prestashop_name'], $order_state->id);
        }
        return $res;
    }

    public function getPrestashopStatusIdFromOystStatus($oyst_status)
    {
        if ($oyst_status == 'oyst_payment_captured') {
            $status_name = 'OYST_ORDER_CREATION_STATUS';
        } elseif (isset($this->status[$oyst_status])) {
            $status_name = $this->status[$oyst_status]['prestashop_name'];
        } else {
            $status_name = '';
        }

        if (!empty($status_name) && Configuration::hasKey($status_name)) {
            return Configuration::get($status_name);
        } else {
            return 0;
        }
    }

    public function getOystStatusFromPrestashopStatus($prestashop_status)
    {
        foreach ($this->status as $oyst_name => $status) {
            if ($status['prestashop_name'] == $prestashop_status) {
                return $oyst_name;
            }
        }
        return '';
    }
}
