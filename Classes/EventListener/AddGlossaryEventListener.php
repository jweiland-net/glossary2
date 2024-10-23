<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\EventListener;

use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use JWeiland\Glossary2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Glossary2\Service\GlossaryService;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class AddGlossaryEventListener extends AbstractControllerEventListener
{
    protected GlossaryService $glossaryService;

    protected GlossaryRepository $glossaryRepository;

    /**
     * @var array<string, mixed>
     */
    protected array $allowedControllerActions = [
        'Glossary' => [
            'list',
        ],
    ];

    public function __construct(GlossaryService $glossaryService, GlossaryRepository $glossaryRepository)
    {
        $this->glossaryService = $glossaryService;
        $this->glossaryRepository = $glossaryRepository;
    }

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $event->addFluidVariable(
                'glossary',
                $this->glossaryService->buildGlossary(
                    $this->glossaryRepository->getExtbaseQueryForGlossary(),
                    $this->getOptions($event),
                    $event->getRequest(),
                ),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(PostProcessFluidVariablesEvent $event): array
    {
        $options = [
            'extensionName' => 'glossary2',
            'pluginName' => 'glossary',
            'controllerName' => 'Glossary',
            'column' => 'title',
            'settings' => $event->getSettings(),
            'variables' => $event->getFluidVariables(),
        ];

        if (
            isset($event->getSettings()['glossary'])
            && is_array($event->getSettings()['glossary'])
        ) {
            ArrayUtility::mergeRecursiveWithOverrule($options, $event->getSettings()['glossary']);
        }

        return $options;
    }
}
