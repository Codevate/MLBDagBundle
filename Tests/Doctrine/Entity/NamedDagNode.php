<?php

namespace Mlb\DagBundle\Tests\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mlb\DagBundle\Entity\DagNode;

/**
 * This entity represents a node in the graph.
 *
 * @ORM\Table(name="named_dag_node")
 * @ORM\Entity(repositoryClass="Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagNodeRepository")
 */
class NamedDagNode extends DagNode
{
  /**
   * @var integer The ID of the node.
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  protected $id;

  /**
   * @var string The name of the node.
   *
   * @ORM\Column(name="name", type="string", length=80, nullable=false)
   */
  private $name;

  /**
   * Get the ID of the node.
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Sets the name of the node.
   *
   * @param string $name The name of the node.
   * @return NamedDagNode Returns the node itself for method chaining.
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Gets the name of the node.
   *
   * @return string The name of the node.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Creates a node with the given name.
   *
   * @param string $name The name of the node.
   */
  public function __construct($name)
  {
    $this->setName($name);
  }
}
