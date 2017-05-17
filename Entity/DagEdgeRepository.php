<?php

namespace Mlb\DagBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Mlb\DagBundle\Entity\EdgeDoesNotExistException;
use Mlb\DagBundle\Entity\CircularRelationException;

/**
 * Utility methods for managing edges between nodes.
 *
 * Basic create, delete, and find operations are supported. Advanced queries
 * are not supported at the moment, maybe in a future release.
 *
 */
abstract class DagEdgeRepository extends EntityRepository
{
    /**
     * Connects two nodes, if not directly connected yet.
     *
     * Creation of a direct connection between nodes ensures that all indirect
     * connection are also cached for later referral.
     *
     * @param \Mlb\DagBundle\Entity\DagNode $start The node this edge begins.
     * @param \Mlb\DagBundle\Entity\DagNode $end The node this edge ends.
     * @throws \Mlb\DagBundle\Entity\CircularRelationException Thrown if a loop will be created.
     */
    public function createEdge(DagNode $start, DagNode $end)
    {
        // Check if the edge already exists
        $direct = $this->findDirectEdge($start, $end);

        if($direct !== null)
            return;

        $em = $this->getEntityManager();

        // Check for a circular reference, step 1
        if($start->getId() === $end->getId())
            throw new CircularRelationException($start, $end);

        // Check for a circular reference, step 2
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.startNode = :end')
            ->andWhere('e.endNode = :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery();

        $circular = $query->getResult();

        if(count($circular) > 0)
            throw new CircularRelationException($start, $end);

        // Create new edge
        $edge = $this->_class->newInstance();
        $edge->setIncomingEdge($edge)
             ->setDirectEdge($edge)
             ->setOutgoingEdge($edge)
             ->setStartNode($start)
             ->setEndNode($end)
             ->setHops(0);

        $em->persist($edge);
        $em->flush();

        // Step 1: A to B incoming edges
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.endNode = :start')
            ->setParameter('start', $start)
            ->getQuery();

        $steps = $query->getResult();
        foreach($steps as $step)
        {
            $outgoing = $this->_class->newInstance();
            $outgoing->setIncomingEdge($step)
                ->setDirectEdge($edge)
                ->setOutgoingEdge($edge)
                ->setStartNode($step->getStartNode())
                ->setEndNode($end)
                ->setHops($step->getHops()+1);

            $em->persist($outgoing);
        }
        $em->flush();

        // Step 2: A to B outgoing edges
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.startNode = :end')
            ->setParameter('end', $end)
            ->getQuery();

        $steps = $query->getResult();
        foreach($steps as $step)
        {
            $outgoing = $this->_class->newInstance();
            $outgoing->setIncomingEdge($edge)
                ->setDirectEdge($edge)
                ->setOutgoingEdge($step)
                ->setStartNode($start)
                ->setEndNode($step->getEndNode())
                ->setHops($step->getHops()+1);

            $em->persist($outgoing);
        }
        $em->flush();

        // Step 3: A's incoming to B's end nodes outgoing edges
        $dql = 'SELECT A.id ida, A.hops ahops, B.id idb, B.hops bhops'.
               '  FROM ' . $this->_entityName . ' A'.
               '  JOIN ' . $this->_entityName . ' B'.
               ' WHERE A.endNode = :start'.
               '   AND B.startNode = :end';
        $query = $em->createQuery($dql);
        $query->setParameter('start', $start)
              ->setParameter('end', $end);

        $steps = $query->getResult();

        foreach($steps as $step)
        {
            $a = $this->findOneById($step['ida']);
            $b = $this->findOneById($step['idb']);
            $outgoing = $this->_class->newInstance();
            $outgoing->setIncomingEdge($a)
                ->setDirectEdge($edge)
                ->setOutgoingEdge($b)
                ->setStartNode($a->getStartNode())
                ->setEndNode($b->getEndNode())
                ->setHops($step['ahops']+$step['bhops']+1);

            $em->persist($outgoing);
        }
        $em->flush();
    }

    /**
     * This method finds a direct edge between two nodes, if any.
     *
     * @param \Mlb\DagBundle\Entity\DagNode $start The node the edge stars with.
     * @param \Mlb\DagBundle\Entity\DagNode $end The node the edge ends with.
     * @return \Mlb\DagBundle\Entity\DagEdge if found, null otherwise.
     */
    public function findDirectEdge(DagNode $start, DagNode $end)
    {
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.startNode = :start')
            ->andWhere('e.endNode = :end')
            ->andWhere('e.hops = :hops')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('hops', 0)
            ->getQuery();

        $edge = $query->getOneOrNullResult();

        return $edge;
    }

    /**
     * This method finds all edges between two nodes, if any.
     *
     * @param \Mlb\DagBundle\Entity\DagNode $start The node the edge stars with.
     * @param \Mlb\DagBundle\Entity\DagNode $end The node the edge ends with.
     * @return array if found, null otherwise.
     */
    public function findEdges(DagNode $start, DagNode $end)
    {
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.startNode = :start')
            ->andWhere('e.endNode = :end')
            ->orderBy('e.hops')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

    /**
     * Find all direct edges connecting nodes.
     *
     * This method queries the data model to discover all direct edges, that is
     * all edges that connect nodes with only one hop.
     *
     * @return Collection All the direct edges in the graph.
     */
    public function findAllDirectEdges()
    {
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.hops = 0')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Given two nodes connected by a direct edge this method disconnects them.
     *
     * Mind that if two nodes are indirectly connected (i.e. if node A is
     * conected to node B and node B is connected to node C then node A is
     * connected to node C, but this is not a direct connection-
     *
     * @param \Mlb\DagBundle\Entity\DagNode $start The node the edge stars with.
     * @param \Mlb\DagBundle\Entity\DagNode $end The node the edge edns with.
     * @throws EdgeDoesNotExistException If the nodes are not directly connected.
     */
    public function deleteEdgeByEnds(DagNode $start, DagNode $end)
    {
        $direct = $this->findDirectEdge($start, $end);
        if($direct === null)
            throw new EdgeDoesNotExistException('No edge connects node '.$start->getId().' to node '.$end->getId());
        $this->deleteEdge($direct);
    }

    /**
     * Given a direct edge, it removes it.
     *
     * @param \Mlb\DagBundle\Entity\DagEdge $edge The edge to delete.
     * @throws EdgeDoesNotExistException It the direct edge does not exist.
     */
    public function deleteEdge(DagEdge $edge)
    {
        $em = $this->getEntityManager();

        $query = $this
            ->createQueryBuilder('e')
            ->where('e.id = :id')
            ->andWhere('e.hops = :hops')
            ->setParameter('id', $edge->getId())
            ->setParameter('hops', 0)
            ->getQuery();

        $direct = $query->getOneOrNullResult();

        if($direct === null)
            throw new EdgeDoesNotExistException('You tried to delete a non existing edge.');

        $em->getConnection()->beginTransaction();

        // Step 1: find derived edges inserted by direct edges
        $this
            ->createQueryBuilder('e')
            ->where('e.directEdge = :direct')
            ->setParameter('direct', $edge)
            ->getQuery();

        $purgeList = $query->getResult();

        do {
            $rowCount = 0;

            $dql = 'SELECT e'.
                   '  FROM ' . $this->_entityName . ' e'.
                   ' WHERE e.hops > 0'.
                   '   AND (e.incomingEdge IN (:purgelist)'.
                   '   AND e.outgoingEdge IN (:purgelist))'.
                   '   AND e NOT IN (:purgelist)';
            $query = $em->createQuery($dql);
            $query->setParameter('purgelist', $purgeList);
            $purgeSubList = $query->getResult();
            $rowCount = count($purgeSubList);
            array_merge($purgeList, $purgeSubList);
        } while($rowCount != 0);

        $dql = 'DELETE'.
               '  FROM ' . $this->_entityName . ' e'.
               ' WHERE e IN (:purgelist)';
        $query = $em->createQuery($dql);
        $query->setParameter('purgelist', $purgeList);
        $query->getResult();

        $em->remove($edge);

        $em->getConnection()->commit();

        $em->flush();
    }
}
