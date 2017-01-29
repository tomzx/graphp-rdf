<?php

namespace tomzx\Graphp\RDF;

use Fhaculty\Graph\Exporter\ExporterInterface;
use Fhaculty\Graph\Graph;

class RDFExporter implements ExporterInterface
{
    /**
     * @param \Fhaculty\Graph\Graph $graph
     * @return string
     */
    public function getOutput(Graph $graph)
    {
        $relations = [];

        foreach ($graph->getVertices() as $vertex) {
            $relations['<' . $vertex->getId() . '> <rdf:ID> "' . $vertex->getId() . '" .'] = true;
            foreach ($vertex->getAttributeBag()->getAttributes() as $key => $value) {
                $relations['<' . $vertex->getId() . '> <' . $key . '> "' . $value . '" .'] = true;
            }
        }

        $edgeId = 0;
        /** @var \Fhaculty\Graph\Edge\Base $edge */
        foreach ($graph->getEdges() as $edge) {
            ++$edgeId;
            $vertices = $edge->getVertices()->getVector();

            $edgeNode = '_:edge' . $edgeId;
            $relations['<' . $vertices[0]->getId() . '> <' . $edgeNode . '> <' . $vertices[1]->getId() . '> .'] = true;

            foreach ($edge->getAttributeBag()->getAttributes() as $key => $value) {
                $relations['<' . $edgeNode . '> <' . $key . '> "' . $value . '" .'] = true;
            }
        }

        return implode(array_keys($relations), PHP_EOL);
    }
}
