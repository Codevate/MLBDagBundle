<?php

namespace Mlb\DagBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDagEdges implements FixtureInterface, DependentFixtureInterface
{
    public function getDependencies()
    {
        return array('Mlb\DagBundle\DataFixtures\ORM\LoadDagNodes');
    }

    /**
     * Creates a graph with the following structure:
     *
     *         (0)                    (1)
     *          |                      |
     *         (2)                    (3)
     *         / \                    / \
     *       (4) (5)<————————————————/  (6)
     *        |   |\                     |
     *       (7)  | \——————————————————>(9)
     *           (8)
     *
     * @param ObjectManager $manager
     * @throws \Mlb\DagBundle\Entity\CircularRelationException
     */
    public function load(ObjectManager $manager)
    {
        $repoNode = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagNode');
        $repoEdge = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');

        $nodes = $repoNode->findAll();

        // First graph
        $repoEdge->createEdge($nodes[0], $nodes[2]);
        $repoEdge->createEdge($nodes[2], $nodes[4]);
        $repoEdge->createEdge($nodes[2], $nodes[5]);
        $repoEdge->createEdge($nodes[4], $nodes[7]);
        $repoEdge->createEdge($nodes[5], $nodes[8]);
        $repoEdge->createEdge($nodes[5], $nodes[9]);

        // Second graph
        $repoEdge->createEdge($nodes[1], $nodes[3]);
        $repoEdge->createEdge($nodes[3], $nodes[5]);
        $repoEdge->createEdge($nodes[3], $nodes[6]);
        $repoEdge->createEdge($nodes[6], $nodes[9]);
    }
}
