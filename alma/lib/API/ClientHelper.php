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

namespace Alma\PrestaShop\API;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma;
use Alma\API\Client;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use Exception;

class ClientHelper
{
    public static function defaultInstance()
    {
        static $_almaClient;

        if (!$_almaClient) {
            $_almaClient = self::createInstance(Settings::getActiveAPIKey());
        }

        return $_almaClient;
    }

    public static function createInstance($apiKey, $mode = null)
    {
        if (!$mode) {
            $mode = Settings::getActiveMode();
        }

        $alma = null;

        try {
            $alma = new Client($apiKey, [
                'mode' => $mode,
                'logger' => Logger::instance(),
            ]);

            $alma->addUserAgentComponent('PrestaShop', _PS_VERSION_);
            $alma->addUserAgentComponent('Alma for PrestaShop', Alma::VERSION);
        } catch (Exception $e) {
            Logger::instance()->error('Error creating Alma API client: ' . $e->getMessage());
        }

        return $alma;
    }
}
