<?php
/**
 * 2018-2022 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

use Address;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\ShippingData;
use Cart;
use Context;
use Country;
use Customer;
use State;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CartDataHelper.
 *
 * Cart Data
 */
class CartDataHelper
{
    public function __construct(
        Cart $cart,
        Context $context,
        array $feePlans
    )
    {
        $this->cart = $cart;
        $this->context = $context;
        $this->feePlans = $feePlans;
    }

    public function eligibilityData()
    {
        return [
            'purchase_amount' => almaPriceToCents($this->getPurchaseAmount()),
            'queries' => $this->getQueries(),
            'shipping_address' => [
                'country' => $this->getCountryAddressByType('shipping'),
            ],
            'billing_address' => [
                'country' => $this->getCountryAddressByType('billing'),
            ],
            'locale' => $this->getLocale(),
        ];
    }

    public function paymentData()
    {
        return [
            'payment' => [
                'installments_count' => $this->feePlans['installmentsCount'],
                'deferred_days' => $this->feePlans['deferredDays'],
                'deferred_months' => $this->feePlans['deferredMonths'],
                'purchase_amount' => almaPriceToCents($this->getPurchaseAmount()),
                'customer_cancel_url' => $this->context->link->getPageLink('order'),
                'return_url' => $this->context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $this->context->link->getModuleLink('alma', 'ipn'),
                'shipping_address' => [
                    'line1' => $this->getAddressByType('shipping')->address1,
                    'postal_code' => $this->getAddressByType('shipping')->postcode,
                    'city' => $this->getAddressByType('shipping')->city,
                    'country' => $this->getCountryAddressByType('shipping'),
                    'county_sublocality' => null,
                    'state_province' => $this->getStateProvince('shipping'),
                ],
                'shipping_info' => $this->getShippingData(),
                'cart' => $this->getCartInfo(),
                'billing_address' => [
                    'line1' => $this->getAddressByType('billing')->address1,
                    'postal_code' => $this->getAddressByType('billing')->postcode,
                    'city' => $this->getAddressByType('billing')->city,
                    'country' => $this->getCountryAddressByType('billing'),
                    'county_sublocality' => null,
                    'state_province' => $this->getStateProvince('billing'),
                ],
                'custom_data' => [
                    'cart_id' => $this->cart->id,
                    'purchase_amount_new_conversion_func' => almaPriceToCents_str($this->getPurchaseAmount()),
                    'cart_totals' => $this->getPurchaseAmount(),
                    'cart_totals_high_precision' => number_format($this->getPurchaseAmount(), 16),
                ],
                'locale' => $this->getLocale(),
                'customer' => $this->getCustomerData(),
            ],
        ];
    }

    private function getCustomerData()
    {
        $customer = [
            'first_name' => $this->getCustomer()->firstname,
            'last_name' => $this->getCustomer()->lastname,
            'email' => $this->getCustomer()->email,
            'birth_date' => $this->getCustomer()->birthday,
            'addresses' => [],
            'phone' => null,
            'country' => null,
            'county_sublocality' => null,
            'state_province' => null,
        ];

        if ($customer['birth_date'] == '0000-00-00') {
            $customer['birth_date'] = null;
        }

        $shippingAddress = $this->getAddressByType('shipping');

        if ($shippingAddress->phone) {
            $customerData['phone'] = $shippingAddress->phone;
        } elseif ($shippingAddress->phone_mobile) {
            $customerData['phone'] = $shippingAddress->phone_mobile;
        }

        return $customer;
    }

    private function getCustomer()
    {
        if ($this->cart->id_customer) {
            $customer = new Customer($this->cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Logger::instance()->error(
                    "[Alma] Error loading Customer {$this->cart->id_customer} from Cart {$this->cart->id}"
                );

                return null;
            }

            return $customer;
        }

        return $this->context->customer;
    }

    private function getCartInfo()
    {
        return CartData::cartInfo($this->cart);
    }

    private function getShippingData()
    {
        return ShippingData::shippingInfo($this->cart);
    }

    private function getStateProvince($type)
    {
        $idStateShipping = $this->getAddressByType($type)->id_state;

        return $idStateShipping > 0 ? State::getNameById((int) $idStateShipping) : '';
    }
    private function getLocale()
    {
        $locale = $this->context->language->iso_code;

        if (property_exists($this->context->language, 'locale')) {
            $locale = $this->context->language->locale;
        }

        return $locale;
    }

    private function getAddressByType($type)
    {
        if ($type == 'shipping') {
            $address = new Address((int) $this->cart->id_address_delivery);
        } elseif ($type == 'billing') {
            $address = new Address((int) $this->cart->id_address_invoice);
        }

        return $address;
    }

    private function getCountryAddressByType($type)
    {
        $countryAddress = '';

        if ($type == 'shipping') {
            $countryAddress = Country::getIsoById((int) $this->getAddressByType('shipping')->id_country);
        } elseif ($type == 'billing') {
            $countryAddress = Country::getIsoById((int) $this->getAddressByType('billing')->id_country);
        }

        return $countryAddress;
    }

    private function getQueries()
    {
        $queries = [];

        foreach ($this->feePlans as $plan) {
            $queries[] = [
                'purchase_amount' => almaPriceToCents($this->getPurchaseAmount()),
                'installments_count' => $plan['installmentsCount'],
                'deferred_days' => $plan['deferredDays'],
                'deferred_months' => $plan['deferredMonths'],
            ];
        }

        return $queries;
    }

    private function getPurchaseAmount()
    {
        $purchaseAmount = (float) Tools::ps_round((float) $this->cart->getOrderTotal(true, Cart::BOTH), 2);

        return $purchaseAmount;
    } 
}