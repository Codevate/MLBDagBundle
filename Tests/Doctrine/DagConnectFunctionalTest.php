<?php

namespace Mlb\DagBundle\Tests\Doctrine;

use Mlb\DagBundle\Tests\IntegrationTestCase;
use Mlb\DagBundle\Entity\CircularRelationException;
use Mlb\DagBundle\Entity\EdgeDoesNotExistException;

class DagConnectFunctionalTest extends IntegrationTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    protected function setUp()
    {
		    parent::setUp();

	      $this->em = static::getEntityManager();
    }

    public function testDbInit()
    {
        // Count nodes
        $nodeRepo = $this->em->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagNode');
        $node = $nodeRepo->findAll();
        $this->assertCount(10, $node);

        // Count only direct edges
        $edgeRepo = $this->em->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
        $direct = $edgeRepo->findAllDirectEdges();
        $this->assertCount(10, $direct);
    }

    /*
     * @depends testDbInit
     */
    public function testInitialCreation()
    {
        // Test for test nodes to exist
        $nodeRepo = $this->em->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagNode');
        $nodes = [];

        for ($i = 0; $i < 10; $i++) {
            $name = 'Node ' . $i;
            $nodes[$i] = $nodeRepo->findOneByName($name);

            $this->assertNotNull($nodes[$i]);
            $this->assertEquals($nodes[$i]->getName(), $name);
        }

        // Count all the edges
        $edgeRepo = $this->em->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
        $count = $edgeRepo->findAll();
        $this->assertCount(26, $count);

        // Test direct edges between nodes
        $directEdges = [
          $edgeRepo->findEdges($nodes[0], $nodes[2]),
          $edgeRepo->findEdges($nodes[2], $nodes[4]),
          $edgeRepo->findEdges($nodes[2], $nodes[5]),
          $edgeRepo->findEdges($nodes[4], $nodes[7]),
          $edgeRepo->findEdges($nodes[5], $nodes[8]),
          $edgeRepo->findEdges($nodes[5], $nodes[9]),
          $edgeRepo->findEdges($nodes[1], $nodes[3]),
          $edgeRepo->findEdges($nodes[3], $nodes[5]),
          $edgeRepo->findEdges($nodes[3], $nodes[6]),
          $edgeRepo->findEdges($nodes[6], $nodes[9]),
        ];

        foreach ($directEdges as $i => $edges) {
            // Test edge getters
            if ($i === 0) {
                $edges[0]->getIncomingEdge();
                $edges[0]->getDirectEdge();
                $edges[0]->getOutgoingEdge();
            }

            $this->assertCount(1, $edges);
            $this->assertEquals($edges[0]->getHops(), 0);
        }

        // TODO: Test indirect edges between nodes over multiple hops
        $indirectEdges = [
          1 => [

          ],
          2 => [

          ],
        ];

        foreach ($indirectEdges as $hops => $results) {
            $count = $hops + 1;

            foreach ($results as $i => $edges) {
                $this->assertCount($count, $edges);

                for ($j = 0; $j < $count; $j++) {
                    $this->assertEquals($edges[$j]->getHops(), $j);
                }
            }
        }
    }

    /*
     * @depends testInitialCreation
     * @expectedException Mlb\DagBundle\Entity\CircularRelationException
     * @expectedException Mlb\DagBundle\Entity\EdgeDoesNotExistException
     */
    public function testConnection()
    {
        $this->em = static::getEntityManager();

        $nodeRepo = $this->em->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagNode');
        $node2 = $nodeRepo->findOneByName('Node 2');
        $node6 = $nodeRepo->findOneByName('Node 6');
        $node7 = $nodeRepo->findOneByName('Node 7');

        $edgeRepo = $this->em->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
        $edgeRepo->createEdge($node6, $node7);

        // Double creation to complete test coverage
        $edgeRepo->createEdge($node6, $node7);
        $direct = $edgeRepo->findAllDirectEdges();

        $this->assertCount(11, $direct);

        try {
            $edgeRepo->createEdge($node2, $node2);
        } catch(CircularRelationException $e) {
        }

        try {
            $edgeRepo->createEdge($node7, $node2);
        } catch(CircularRelationException $e) {
        }

        try {
            $deleteEdge =  $edgeRepo->findDirectEdge($node6, $node7);
            $edgeRepo->deleteEdgeByEnds($node6, $node7);

            // Already deleted to complete test coverage
            $edgeRepo->deleteEdge($deleteEdge);
        } catch(EdgeDoesNotExistException $e) {
        }

        $direct = $edgeRepo->findAllDirectEdges();
        $this->assertCount(10, $direct);
    }
}
