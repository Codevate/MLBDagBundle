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

    public function load(ObjectManager $manager)
    {
        $repoNode = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagNode');
        $repoEdge = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');

        $nodes = $repoNode->findAll();
        foreach ($nodes as $node) {
            $nodeArray[] = $node;
        }

        // First graph
        $repoEdge->createEdge($nodeArray[0], $nodeArray[1]);
        $repoEdge->createEdge($nodeArray[0], $nodeArray[2]);
        $repoEdge->createEdge($nodeArray[0], $nodeArray[3]);

        $repoEdge->createEdge($nodeArray[1], $nodeArray[2]);
        $repoEdge->createEdge($nodeArray[2], $nodeArray[3]);
        $repoEdge->createEdge($nodeArray[3], $nodeArray[4]);

        // Second graph
        $repoEdge->createEdge($nodeArray[5], $nodeArray[6]);
        $repoEdge->createEdge($nodeArray[5], $nodeArray[7]);
        $repoEdge->createEdge($nodeArray[5], $nodeArray[8]);

        $repoEdge->createEdge($nodeArray[6], $nodeArray[7]);
        $repoEdge->createEdge($nodeArray[7], $nodeArray[8]);
        $repoEdge->createEdge($nodeArray[8], $nodeArray[9]);
    }
}
