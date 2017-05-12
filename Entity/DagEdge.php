<?php

namespace Mlb\DagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity represents a connection between nodes in the graph.
 *
 * @ORM\MappedSuperclass()
 */
abstract class DagEdge
{
    protected $id;

    /**
     * @var integer The number of hops (i.e. direct edges) this edge skips.
     *
     * @ORM\Column(type="integer")
     */
    protected $hops;

    // Relationship mappings to be defined in concrete class

    /**
     * @var DagEdge The incoming edge responsible for the creation of this edge.
     */
    protected $incomingEdge;

    /**
     * @var DagEdge The direct edge responsible for the creation of this edge.
     */
    protected $directEdge;

    /**
     * @var DagEdge The outgoing edge responsible for the creation of this edge.
     */
    protected $outgoingEdge;

    /**
     * @var DagNode The node this edge starts from.
     */
    protected $startNode;

    /**
     * @var DagNode The node this edge end to.
     */
    protected $endNode;


    /**
     * Gets the ID of the edge
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the number of hops this edge skips.
     *
     * @param integer $hops
     * @return DagEdge
     */
    public function setHops($hops)
    {
        $this->hops = $hops;

        return $this;
    }

    /**
     * Gets the number of hops this edge skips.
     *
     * @return integer 
     */
    public function getHops()
    {
        return $this->hops;
    }

    /**
     * Sets the incoming edge responsible for the creation of this edge.
     *
     * @param DagEdge $incomingEdge
     * @return DagEdge
     */
    public function setIncomingEdge(DagEdge $incomingEdge = null)
    {
        $this->incomingEdge = $incomingEdge;

        return $this;
    }

    /**
     * Gets the incoming edge responsible for the creation of this edge.
     *
     * @return DagEdge
     */
    public function getIncomingEdge()
    {
        return $this->incomingEdge;
    }

    /**
     * Sets the direct edge responsible for the creation of this edge.
     *
     * @param DagEdge $directEdge
     * @return DagEdge
     */
    public function setDirectEdge(DagEdge $directEdge = null)
    {
        $this->directEdge = $directEdge;

        return $this;
    }

    /**
     * Gets the direct edge responsible for the creation of this edge.
     *
     * @return DagEdge
     */
    public function getDirectEdge()
    {
        return $this->directEdge;
    }

    /**
     * Sets the outgoing edge responsible for the creation of this edge.
     *
     * @param DagEdge $outgoingEdge
     * @return DagEdge
     */
    public function setOutgoingEdge(DagEdge $outgoingEdge = null)
    {
        $this->outgoingEdge = $outgoingEdge;

        return $this;
    }

    /**
     * Gets the outgoing edge responsible for the creation of this edge.
     *
     * @return DagEdge
     */
    public function getOutgoingEdge()
    {
        return $this->outgoingEdge;
    }

    /**
     * Sets the start node responsible for the creation of this edge.
     *
     * @param DagNode $startNode
     * @return DagEdge
     */
    public function setStartNode(DagNode $startNode = null)
    {
        $this->startNode = $startNode;

        return $this;
    }

    /**
     * Gets the start node responsible for the creation of this edge.
     *
     * @return DagNode
     */
    public function getStartNode()
    {
        return $this->startNode;
    }

    /**
     * Sets the edn node responsible for the creation of this edge.
     *
     * @param DagNode $endNode
     * @return DagEdge
     */
    public function setEndNode(DagNode $endNode = null)
    {
        $this->endNode = $endNode;

        return $this;
    }

    /**
     * Gets the end node responsible for the creation of this edge.
     *
     * @return DagNode
     */
    public function getEndNode()
    {
        return $this->endNode;
    }
}
