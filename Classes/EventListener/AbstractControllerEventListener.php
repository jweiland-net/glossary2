<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\EventListener;

use JWeiland\Glossary2\Event\ControllerActionEventInterface;

/**
 * Abstract EventListener just for action controllers.
 */
readonly class AbstractControllerEventListener
{
    /**
     * Only execute this EventListener if controller and action matches
     *
     * @var array<string, mixed>
     */
    public const ALLOWED_CONTROLLER_ACTIONS = [];

    protected function isValidRequest(ControllerActionEventInterface $event): bool
    {
        return
            array_key_exists(
                $event->getControllerName(),
                static::ALLOWED_CONTROLLER_ACTIONS,
            )
            && in_array(
                $event->getActionName(),
                static::ALLOWED_CONTROLLER_ACTIONS[$event->getControllerName()],
                true,
            );
    }
}
