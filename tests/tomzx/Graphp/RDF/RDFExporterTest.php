<?php

use Fhaculty\Graph\Graph;
use tomzx\Graphp\RDF\RDFExporter;

class RDFExporterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \tomzx\Graphp\RDF\RDFExporter
     */
    protected $rdfExporter;

    public function setUp()
    {
        parent::setUp();

        $this->rdfExporter = new RDFExporter();
    }

    public function testGraphEmpty()
    {
        $graph = new Graph();

        $expected = <<<GRAPH
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphIsolatedVertices()
    {
        $graph = new Graph();
        $graph->createVertex('a');
        $graph->createVertex('b');
        $expected = <<<GRAPH
<a> <rdf:ID> "a" .
<b> <rdf:ID> "b" .
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testEscaping()
    {
        $graph = new Graph();
        $graph->createVertex('a');
        $graph->createVertex('b¹²³ is; ok\\ay, "right"?');
        $graph->createVertex(3);
        $graph->createVertex(4)->setAttribute('graphviz.label', 'normal');
        $expected = <<<GRAPH
<a> <rdf:ID> "a" .
<b¹²³ is; ok\ay, "right"?> <rdf:ID> "b¹²³ is; ok\ay, "right"?" .
<3> <rdf:ID> "3" .
<4> <rdf:ID> "4" .
<4> <graphviz.label> "normal" .
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphDirected()
    {
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $expected = <<<GRAPH
<a> <rdf:ID> "a" .
<b> <rdf:ID> "b" .
<a> <_:edge1> <b> .
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphMixed()
    {
        // a -> b -- c
        $graph = new Graph();
        $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $graph->createVertex('c')->createEdge($graph->getVertex('b'));
        $expected = <<<GRAPH
<a> <rdf:ID> "a" .
<b> <rdf:ID> "b" .
<c> <rdf:ID> "c" .
<a> <_:edge1> <b> .
<c> <_:edge2> <b> .
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphUndirectedWithIsolatedVerticesFirst()
    {
        // a -- b -- c   d
        $graph = new Graph();
        $graph->createVertices(array('a', 'b', 'c', 'd'));
        $graph->getVertex('a')->createEdge($graph->getVertex('b'));
        $graph->getVertex('b')->createEdge($graph->getVertex('c'));
        $expected = <<<GRAPH
<a> <rdf:ID> "a" .
<b> <rdf:ID> "b" .
<c> <rdf:ID> "c" .
<d> <rdf:ID> "d" .
<a> <_:edge1> <b> .
<b> <_:edge2> <c> .
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }

    public function testGraphWithAttributesOnEdges()
    {
        $graph = new Graph();
        $edge = $graph->createVertex('a')->createEdgeTo($graph->createVertex('b'));
        $edge->setAttribute('a1', 'v1');
        $edge->setAttribute('a2', 'v2');
        $edge = $graph->getVertex('a')->createEdgeTo($graph->createVertex('c'));
        $edge->setAttribute('a3', 'v3');
        $edge->setAttribute('a4', 'v4');
        $expected = <<<GRAPH
<a> <rdf:ID> "a" .
<b> <rdf:ID> "b" .
<c> <rdf:ID> "c" .
<a> <_:edge1> <b> .
<_:edge1> <a1> "v1" .
<_:edge1> <a2> "v2" .
<a> <_:edge2> <c> .
<_:edge2> <a3> "v3" .
<_:edge2> <a4> "v4" .
GRAPH;

        $actual = $this->rdfExporter->getOutput($graph);
        $this->assertEquals($expected, $actual);
    }
}
