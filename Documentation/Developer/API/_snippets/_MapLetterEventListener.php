<?php

declare(strict_types=1);

namespace MyVendor\MyExtension\EventListener;

use JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener('my-extension/map-letters')]
final readonly class MapLetterEventListener
{
    private const LETTER_MAPPING = [
        'ł' => 'l',
        'đ' => 'd',
    ];

    public function __invoke(SanitizeValueForCharsetHelperEvent $event): void
    {
        $event->setValue(strtr($event->getValue(), self::LETTER_MAPPING));
    }
}
