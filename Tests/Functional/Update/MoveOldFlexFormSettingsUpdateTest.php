<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Update;

use JWeiland\Glossary2\Update\MoveOldFlexFormSettingsUpdate;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class MoveOldFlexFormSettingsUpdateTest extends FunctionalTestCase
{
    private const FLEXFORM_WITH_SCA = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'
        . '<T3FlexForms><data><sheet index="sDEFAULT"><language index="lDEF">'
        . '<field index="switchableControllerActions"><value index="vDEF">Glossary->listWithoutGlossar</value></field>'
        . '<field index="settings.pidOfDetailPage"><value index="vDEF">12</value></field>'
        . '</language></sheet></data></T3FlexForms>';

    private const FLEXFORM_UP_TO_DATE = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'
        . '<T3FlexForms><data><sheet index="sDEF"><language index="lDEF">'
        . '<field index="settings.showGlossar"><value index="vDEF">1</value></field>'
        . '<field index="settings.showAllLink"><value index="vDEF">1</value></field>'
        . '</language></sheet></data></T3FlexForms>';

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    #[Test]
    public function updateNecessaryWithoutPluginsReturnsFalse(): void
    {
        self::assertFalse(
            $this->getSubject()->updateNecessary(),
        );
    }

    #[Test]
    public function updateNecessaryWithUpToDatePluginsReturnsFalse(): void
    {
        $this->insertContentElement(1, 'glossary2_glossary', '', self::FLEXFORM_UP_TO_DATE);
        $this->insertContentElement(2, 'glossary2_glossary', '', '');

        self::assertFalse(
            $this->getSubject()->updateNecessary(),
        );
    }

    #[Test]
    public function updateNecessaryWithOutdatedPluginAfterUpToDatePluginReturnsTrue(): void
    {
        $this->insertContentElement(1, 'glossary2_glossary', '', self::FLEXFORM_UP_TO_DATE);
        $this->insertContentElement(2, 'glossary2_glossary', '', self::FLEXFORM_WITH_SCA);

        self::assertTrue(
            $this->getSubject()->updateNecessary(),
        );
    }

    #[Test]
    public function updateNecessaryWithOutdatedListTypePluginReturnsTrue(): void
    {
        $this->insertContentElement(1, 'list', 'glossary2_glossary', self::FLEXFORM_WITH_SCA);

        self::assertTrue(
            $this->getSubject()->updateNecessary(),
        );
    }

    #[Test]
    public function executeUpdateMigratesOutdatedFlexForm(): void
    {
        $this->insertContentElement(1, 'glossary2_glossary', '', self::FLEXFORM_WITH_SCA);

        $subject = $this->getSubject();
        self::assertTrue($subject->executeUpdate());

        $flexForm = GeneralUtility::xml2array($this->getFlexFormOfContentElement(1));
        self::assertIsArray($flexForm);
        self::assertArrayNotHasKey('sDEFAULT', $flexForm['data']);
        self::assertSame(
            [
                'settings.pidOfDetailPage' => ['vDEF' => '12'],
                'settings.showGlossar' => ['vDEF' => '0'],
                'settings.showAllLink' => ['vDEF' => '0'],
            ],
            $flexForm['data']['sDEF']['lDEF'],
        );
        self::assertFalse($subject->updateNecessary());
    }

    #[Test]
    public function executeUpdateKeepsUpToDateFlexForm(): void
    {
        $this->insertContentElement(1, 'glossary2_glossary', '', self::FLEXFORM_UP_TO_DATE);

        $this->getSubject()->executeUpdate();

        self::assertSame(
            self::FLEXFORM_UP_TO_DATE,
            $this->getFlexFormOfContentElement(1),
        );
    }

    private function getSubject(): MoveOldFlexFormSettingsUpdate
    {
        return new MoveOldFlexFormSettingsUpdate(
            GeneralUtility::makeInstance(ConnectionPool::class),
        );
    }

    private function insertContentElement(int $uid, string $cType, string $listType, string $flexForm): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->insert('tt_content', [
                'uid' => $uid,
                'pid' => 1,
                'CType' => $cType,
                'list_type' => $listType,
                'pi_flexform' => $flexForm,
            ]);
    }

    private function getFlexFormOfContentElement(int $uid): string
    {
        return (string)GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->select(['pi_flexform'], 'tt_content', ['uid' => $uid])
            ->fetchOne();
    }
}
