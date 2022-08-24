<?php

namespace SilverStripe\CMS\Tests\Controllers;

use SilverStripe\Assets\File;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Search\ContentControllerSearchExtension;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\Search\FulltextSearchable;
use SilverStripe\Versioned\Versioned;

class ContentControllerSearchExtensionTest extends SapphireTest
{
    protected static $required_extensions = [
        ContentController::class => [
            ContentControllerSearchExtension::class,
        ],
    ];

    public function testCustomSearchFormClassesToTest()
    {
        $page = new SiteTree();
        $page->URLSegment = 'whatever';
        $page->Content = 'oh really?';
        $page->write();
        $page->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
        /** @var ContentController|ContentControllerSearchExtension $controller */
        $controller = new ContentController($page);
        $form = $controller->SearchForm();
        $this->assertEquals([ File::class ], $form->getClassesToSearch());
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        FulltextSearchable::enable(File::class);
    }

    /**
     * FulltextSearchable::enable() leaves behind remains that don't get cleaned up
     * properly at the end of the test. This becomes apparent when a later test tries to
     * ALTER TABLE File and add fulltext indexes with the InnoDB table type.
     */
    public static function tearDownAfterClass(): void
    {
        File::remove_extension(FulltextSearchable::class);
        parent::tearDownAfterClass();
    }
}
