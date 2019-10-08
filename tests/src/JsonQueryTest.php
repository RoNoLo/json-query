<?php

namespace RoNoLo\JsonQuery;

use PHPUnit\Framework\TestCase;

class JsonQueryTest extends TestCase
{
    /**
     * @dataProvider canAccessPropertyProvider
     *
     * @param $property
     * @param $expected
     * @throws \Exception
     */
    public function testCanAccessProperty($file, $property, $expected)
    {
        $q = JsonQuery::fromFile(__DIR__ . '/../fixtures/' . $file);

        $actually = $q->getNestedProperty($property);

        $this->assertEquals($expected, $actually);
    }

    public function canAccessPropertyProvider()
    {
        return [
            [
                'object_5dim.json',
                'persons.hobby.type',
                [
                    "Cars",
                    "Planes",
                    "Music"
                ],
            ],
            [
                'object_5dim.json',
                'persons.name',
                [
                    "Karl",
                    "Jenni"
                ],
            ],
            [
                'array_1dim_objects_mix.json',
                'price.bernd',
                [],
            ],
            [
                'array_1dim_objects_mix.json',
                'price',
                [
                    3 => 100
                ]
            ],
            [
                'array_1dim_objects_mix.json',
                'bernd',
                []
            ],
            [
                'array_1dim_objects_mix.json',
                'name',
                [
                    0 => "Pickett Burks",
                    1 => "Cindy Stuart",
                    3 => null
                ]
            ],
            [
                'array_1dim_objects_mix.json',
                'id',
                [
                    1 => 1,
                    2 => 2,
                    3 => 3
                ]
            ],
            [
                'object_1dim.json',
                'fr',
                'Allemagne'
            ],
            [
                'object_1dim.json',
                'xx',
                new ValueNotFound(),
            ],
            [
                'array_1dim.json',
                '',
                [
                    "voluptate",
                    "dolor",
                    "qui",
                    "anim",
                    "dolor",
                    "cillum",
                    "amet"
                ]
            ],
            [
                'array_1dim_objects.json',
                'id',
                [
                    0, 1, 2, 3
                ]
            ],
            [
                'array_1dim_objects.json',
                'name',
                [
                    "Pickett Burks",
                    "Cindy Stuart",
                    "Lelia Preston",
                    null
                ]
            ],
        ];
    }
}