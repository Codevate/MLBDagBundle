# marcobuschini/MLBDagBundle

When managing complex relationship between entities belonging to the real world
we often see that they cannot fit simple data models, such as lists, maps, and
even trees.

This Symony2 bundle implements a Doctrine data model that allows an application
to manage Directed Acyclic Graphs by using both an adjacency list, and a full
transitive closure for indirect edges.

A DAG is a set of nodes connected by a set of oriented edges so that no closed
loop (cycle) can be created in the data structure. This structure is best
handled with an adjacency list, that is a list of edges connecting pairs of
nodes (edge E connects node A to node B, but not the opposite). To prevent
large amounts of queries to find if node B is reachable from node A a full
transitive closure is implemented, that is a list of indirect edges is
managed by the bundle. So that if node A connects to node B that connects to
node C, an indirect edge from node A to node C is managed by the bundle's
logic.

It is important to note that the data structure MUST NOT be modified outside
of this bundle as the logic for preventing cycles, and for creating indirect
edges IS NOT handled by the database, but by the bundle itself.

## Fork

This bundle has been forked to allow custom nodes to be created, by inheriting DagNode, allowing data to be stored
in the nodes.

Currently, the bundle does not support polymorphic graphs - each node type is its own separate graph.

## Initial setup

### 1. Install the bundle

Add the fork repository to composer.json:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Codevate/MLBDagBundle"
        }
    ]

Install the bundle with composer:

    composer require marcobuschini/mlb-dag-bundle dev-master
    
### 2. Enable the bundle

Add to AppKernel.php:

    new Mlb\DagBundle\MlbDagBundle(),
    
### 3. Create your node entity class

All that's required is to define the $id field and mapping. Your own fields for the node can also be defined, such as
the $name field in the example below.

    <?php
    
    namespace Mlb\DagBundle\Tests\Doctrine\Entity;
    
    use Doctrine\ORM\Mapping as ORM;
    use Mlb\DagBundle\Entity\DagNode;
    
    /**
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
    
      // Getters/setters for $name omitted
    }

### 4. Create your edge class

All the fields and mappings below are required. The relationships are required on the concrete class, as the target 
entity must point to the concrete type.

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
    
### 5. Create repositories

It's required to create concrete repository classes which extend the repository classes for the base types for each of 
your own concrete node and edge types, even if they are left empty.

Node repository:

    <?php
    
    namespace Mlb\DagBundle\Tests\Doctrine\Entity;
    
    use Mlb\DagBundle\Entity\DagEdgeRepository;
    
    class NamedDagNodeRepository extends DagEdgeRepository
    {
    
    }
    
Edge repository:

    <?php
    
    namespace Mlb\DagBundle\Tests\Doctrine\Entity;
    
    use Mlb\DagBundle\Entity\DagEdgeRepository;
    
    class NamedDagEdgeRepository extends DagEdgeRepository
    {
    
    }

### 6. Update database schema

    php app/console doctrine:schema:update --force
    
## Example usage

### Connect two nodes

    $edgeRepo = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
    $edgeRepo->createEdge($startNode, $endNode);
    $em->flush();
    
### Disconnect two nodes

    $edgeRepo = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
    $edgeRepo->deleteEdgeByEnds($startNode, $endNode);
    $em->flush();
    
### Find all edges between node nodes (including indirect edges)

    $edgeRepo = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
    $edgeRepo->findEdges($startNode, $endNode);
    
### Find all direct edges between node nodes

    $edgeRepo = $manager->getRepository('Mlb\DagBundle\Tests\Doctrine\Entity\NamedDagEdge');
    $edgeRepo->findAllDirectEdges($startNode, $endNode);