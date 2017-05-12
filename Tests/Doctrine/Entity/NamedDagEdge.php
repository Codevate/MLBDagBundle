<?php

namespace Mlb\DagBundle\Tests\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mlb\DagBundle\Entity\DagEdge;

/**
 * This entity represents a connection between nodes in the graph.
 *
 * @ORM\Table(name="named_dag_edge")
 * @ORM\Entity(repositoryClass="Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdgeRepository")
 */
class NamedDagEdge extends DagEdge
{
  /**
   * @var integer The ID of the edge.
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  protected $id;

  /**
   * @ORM\ManyToOne(targetEntity="NamedDagEdge")
   * @ORM\JoinColumn(name="incoming_edge_id", referencedColumnName="id", onDelete="CASCADE")
   **/
  protected $incomingEdge;

  /**
   * @ORM\ManyToOne(targetEntity="NamedDagEdge")
   * @ORM\JoinColumn(name="direct_edge_id", referencedColumnName="id", onDelete="CASCADE")
   **/
  protected $directEdge;

  /**
   * @ORM\ManyToOne(targetEntity="NamedDagEdge")
   * @ORM\JoinColumn(name="outgoing_edge_id", referencedColumnName="id", onDelete="CASCADE")
   **/
  protected $outgoingEdge;

  /**
   * @ORM\ManyToOne(targetEntity="NamedDagNode")
   * @ORM\JoinColumn(name="start_node_id", referencedColumnName="id", onDelete="RESTRICT")
   **/
  protected $startNode;

  /**
   * @ORM\ManyToOne(targetEntity="NamedDagNode")
   * @ORM\JoinColumn(name="end_node_id", referencedColumnName="id", onDelete="CASCADE")
   **/
  protected $endNode;
}
