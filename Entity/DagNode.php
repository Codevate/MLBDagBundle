<?php

namespace Mlb\DagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity represents a node in the graph.
 *
 * @ORM\MappedSuperclass()
 */
abstract class DagNode
{
    protected $id;

    /**
     * Get the ID of the node.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
