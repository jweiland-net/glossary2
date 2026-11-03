<?php

declare(strict_types=1);

namespace MyVendor\MyExtension\EventListener;

use JWeiland\Glossary2\Event\PostProcessFirstLettersEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener('my-extension/remove-letter-q')]
final readonly class RemoveLetterEventListener
{
    public function __invoke(PostProcessFirstLettersEvent $event): void
    {
        $event->setFirstLetters(
            array_values(array_diff($event->getFirstLetters(), ['q'])),
        );
    }
}
