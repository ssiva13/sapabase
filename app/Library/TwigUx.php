<?php

/**
 * This file is part of the TwigBridge package.
 *
 * @copyright Robert Crowe <hello@vivalacrowe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acelle\Library;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Access Laravels url class in your Twig templates.
 */
class TwigUx extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'App_Extensions_Twig_Ux';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'ux',
                function ($path) {
                    return 'ux/templates/'.$path;
                }
            ),
            new Twig_SimpleFunction(
                'demo_auth',
                function () {
                    $auth = \Acelle\Model\User::getAuthenticateFromFile();

                    return [
                        'email' => isset($auth['email']) ? $auth['email'] : '',
                        'password' => $auth['password'] ? $auth['password'] : '',
                    ];
                }
            ),
        ];
    }
}
