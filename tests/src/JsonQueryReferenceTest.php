<?php

namespace RoNoLo\JsonQuery;

use PHPUnit\Framework\TestCase;

class JsonQueryReferenceTest extends TestCase
{
    /**
     * This tests valid JSON data stored in fixture files.
     *
     * @dataProvider canAccessPropertyProvider
     *
     * @param $file
     * @param $property
     * @param $expected
     */
    public function testCanAccessProperty($file, $property, $expected)
    {
        $q = JsonQuery::fromFile(__DIR__ . '/../fixtures/' . $file);

        $actually =& $q->getPropertyReference($property);

        $actually[0] = ['$ref' => 'dfsdfgsd'];
        $actually[1] = ['$ref' => 'fcsdfgdsfgds'];

        $this->assertEquals($expected, $actually);
    }

    public function canAccessPropertyProvider()
    {
        return [
            [
                'valid/object_5dim.json',
                'persons',
                'bernd'
            ],
        ];
    }
}