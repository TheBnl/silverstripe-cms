<?php

namespace SilverStripe\CMS\Tests\Tasks;

use SilverStripe\CMS\Tasks\RemoveOrphanedPagesTask;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Dev\FunctionalTest;

/**
 * <h2>Fixture tree</h2>
 * <code>
 * parent1_published
 *   child1_1_published
 *     grandchild1_1_1
 *     grandchild1_1_2_published
 *     grandchild1_1_3_orphaned
 *     grandchild1_1_4_orphaned_published
 *   child1_2_published
 *   child1_3_orphaned
 *   child1_4_orphaned_published
 * parent2
 *   child2_1_published_orphaned // is orphaned because parent is not published
 * </code>
 *
 * <h2>Cleaned up tree</h2>
 * <code>
 * parent1_published
 *   child1_1_published
 *     grandchild1_1_1
 *     grandchild1_1_2_published
 *   child2_1_published_orphaned
 * parent2
 * </code>
 *
 * @author Ingo Schommer (<firstname>@silverstripe.com), SilverStripe Ltd.
 */
class RemoveOrphanedPagesTaskTest extends FunctionalTest
{
    protected static $fixture_file = 'RemoveOrphanedPagesTaskTest.yml';

    protected function setUp(): void
    {
        parent::setUp();

        $parent1_published = $this->objFromFixture('Page', 'parent1_published');
        $parent1_published->publishSingle();

        $child1_1_published = $this->objFromFixture('Page', 'child1_1_published');
        $child1_1_published->publishSingle();

        $child1_2_published = $this->objFromFixture('Page', 'child1_2_published');
        $child1_2_published->publishSingle();

        $child1_3_orphaned = $this->objFromFixture('Page', 'child1_3_orphaned');
        $child1_3_orphaned->ParentID = 9999;
        $child1_3_orphaned->write();

        $child1_4_orphaned_published = $this->objFromFixture('Page', 'child1_4_orphaned_published');
        $child1_4_orphaned_published->ParentID = 9999;
        $child1_4_orphaned_published->write();
        $child1_4_orphaned_published->publishSingle();

        $grandchild1_1_2_published = $this->objFromFixture('Page', 'grandchild1_1_2_published');
        $grandchild1_1_2_published->publishSingle();

        $grandchild1_1_3_orphaned = $this->objFromFixture('Page', 'grandchild1_1_3_orphaned');
        $grandchild1_1_3_orphaned->ParentID = 9999;
        $grandchild1_1_3_orphaned->write();

        $grandchild1_1_4_orphaned_published = $this->objFromFixture(
            'Page',
            'grandchild1_1_4_orphaned_published'
        );
        $grandchild1_1_4_orphaned_published->ParentID = 9999;
        $grandchild1_1_4_orphaned_published->write();
        $grandchild1_1_4_orphaned_published->publishSingle();

        $child2_1_published_orphaned = $this->objFromFixture('Page', 'child2_1_published_orphaned');
        $child2_1_published_orphaned->publishSingle();
    }

    public function testGetOrphansByStage()
    {
        // all orphans
        $child1_3_orphaned = $this->objFromFixture('Page', 'child1_3_orphaned');
        $child1_4_orphaned_published = $this->objFromFixture('Page', 'child1_4_orphaned_published');
        $grandchild1_1_3_orphaned = $this->objFromFixture('Page', 'grandchild1_1_3_orphaned');
        $grandchild1_1_4_orphaned_published = $this->objFromFixture(
            'Page',
            'grandchild1_1_4_orphaned_published'
        );
        $child2_1_published_orphaned = $this->objFromFixture('Page', 'child2_1_published_orphaned');

        $task = singleton(RemoveOrphanedPagesTask::class);
        $orphans = $task->getOrphanedPages();
        $orphanIDs = $orphans->column('ID');
        sort($orphanIDs);
        $compareIDs = [
            $child1_3_orphaned->ID,
            $child1_4_orphaned_published->ID,
            $grandchild1_1_3_orphaned->ID,
            $grandchild1_1_4_orphaned_published->ID,
            $child2_1_published_orphaned->ID
        ];
        sort($compareIDs);

        $this->assertEquals($orphanIDs, $compareIDs);
    }
}
