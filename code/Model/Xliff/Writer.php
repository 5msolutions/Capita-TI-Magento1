<?php

class Capita_TI_Model_Xliff_Writer
{

    const XML_NAMESPACE = 'urn:oasis:names:tc:xliff:document:1.2';

    /**
     * Write a collection of objects to $uri as translateable sources
     * 
     * @param string $uri
     * @param traversable $entities
     * @param string $group
     * @param string[] $attributes
     */
    public function output($uri, $entities, $group = null, $attributes = array())
    {
        $xml = new XMLWriter();
        $xml->openUri($uri);
        $xml->startDocument();
        $xml->startElement('xliff');
        $xml->writeAttribute('version', '1.2');
        $xml->writeAttribute('xmlns', self::XML_NAMESPACE);
        $xml->startElement('file');
        $xml->writeAttribute('original', '');
        $xml->writeAttribute('source-language', 'en');
        $xml->writeAttribute('datatype', 'database');
        $xml->startElement('body');

        if ($group) {
            $xml->startElement('group');
            $xml->writeAttribute('id', $group);
        }

        foreach ($entities as $entity) {
            if ($entity instanceof Varien_Object) {
                $data = $entity->getData();
                $entityId = $entity->getId();
            }
            else {
                $data = (array) $entity;
                $entityId = null;
            }
            if ($attributes) {
                $data = array_intersect_key($data, array_fill_keys($attributes, true));
            }
            // do not translate empty values
            $data = array_filter($data, 'strlen');
            if ($data) {
                $xml->startElement('group');
                if ($entityId) {
                    $xml->writeAttribute('id', $entityId);
                }
                foreach ($data as $id => $source) {
                    $xml->startElement('trans-unit');
                    $xml->writeAttribute('id', $id);
                    $xml->startElement('source');
                    $xml->text($source);
                    $xml->endElement(); // source
                    $xml->endElement(); // trans-unit
                }
                $xml->endElement(); // group
            }
        }

        // end all open elements, easier than remembering how many to do
        while ($xml->endElement());
        // only ever one document to end
        $xml->endDocument();
        $xml->flush();
        // force file to close, just in case
        unset($xml);
    }

}
