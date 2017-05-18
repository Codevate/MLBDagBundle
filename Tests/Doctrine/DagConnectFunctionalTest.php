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

        $directEdges = [
          // Test direct edges between nodes
          array('nodes' => array('start' => $nodes[0], 'end' => $nodes[2]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[2], 'end' => $nodes[4]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[2], 'end' => $nodes[5]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[4], 'end' => $nodes[7]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[5], 'end' => $nodes[8]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[5], 'end' => $nodes[9]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[1], 'end' => $nodes[3]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[3], 'end' => $nodes[5]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[3], 'end' => $nodes[6]), 'hops' => 0),
          array('nodes' => array('start' => $nodes[6], 'end' => $nodes[9]), 'hops' => 0),

          // Test indirect edges between nodes
          array('nodes' => array('start' => $nodes[0], 'end' => $nodes[4]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[0], 'end' => $nodes[5]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[0], 'end' => $nodes[7]), 'hops' => 2),
          array('nodes' => array('start' => $nodes[0], 'end' => $nodes[8]), 'hops' => 2),
          array('nodes' => array('start' => $nodes[0], 'end' => $nodes[9]), 'hops' => 2),
          array('nodes' => array('start' => $nodes[1], 'end' => $nodes[5]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[1], 'end' => $nodes[8]), 'hops' => 2),
          array('nodes' => array('start' => $nodes[1], 'end' => $nodes[6]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[1], 'end' => $nodes[9]), 'hops' => 2),
          array('nodes' => array('start' => $nodes[2], 'end' => $nodes[7]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[2], 'end' => $nodes[8]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[2], 'end' => $nodes[9]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[3], 'end' => $nodes[8]), 'hops' => 1),
          array('nodes' => array('start' => $nodes[3], 'end' => $nodes[9]), 'hops' => 1)
        ];

        foreach ($directEdges as $i => $test) {
            $edges = $edgeRepo->findEdges($test['nodes']['start'], $test['nodes']['end']);

            $this->assertGreaterThanOrEqual(1, count($edges), sprintf('No edges between %s and %s', $test['nodes']['start']->getName(), $test['nodes']['end']->getName()));
            $this->assertEquals($test['hops'], $edges[0]->getHops(), sprintf('Wrong number of hops between %s and %s', $test['nodes']['start']->getName(), $test['nodes']['end']->getName()));
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
