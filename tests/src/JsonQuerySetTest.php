<?php

namespace RoNoLo\JsonQuery;

use PHPUnit\Framework\TestCase;

class JsonQuerySetTest extends TestCase
{
    /**
     * This tests valid JSON data stored in fixture files.
     *
     * @dataProvider canAccessPropertyProvider
     *
     * @param $file
     * @param $property
     * @param $value
     * @param $expected
     */
    public function testCanSetProperty($file, $property, $value, $expected)
    {
        $q = JsonQuery::fromFile(__DIR__ . '/../fixtures/' . $file);

        $context = null;
        $q->setProperty($property, $value);

        $actually = $q->getProperty($property);

        $this->assertEquals($expected, $actually);
    }

    public function canAccessPropertyProvider()
    {
        return [
//            [
//                'valid/object_empty.json',
//                'price',
//                200,
//                200
//            ],
//            [
//                'valid/array_1dim_3objects_empty.json',
//                'price',
//                200,
//                [
//                    0 => 200,
//                    1 => 200,
//                    2 => 200
//                ]
//            ],
//            [
//                'valid/array_1dim_3objects_empty.json',
//                '2.price',
//                200,
//                200
//            ],
//            [
//                'valid/array_1dim_3objects_empty.json',
//                'hobby',
//                [
//                    "type" => "Swimming",
//                    "rating" => 3
//                ],
//                [
//                    0 => [
//                        "type" => "Swimming",
//                        "rating" => 3
//                    ],
//                    1 => [
//                        "type" => "Swimming",
//                        "rating" => 3
//                    ],
//                    2 => [
//                        "type" => "Swimming",
//                        "rating" => 3
//                    ],
//                ],
//            ],
            [
                'valid/object_empty.json',
                'hobby.kalle.bernd.at',
                [
                    "type" => "Swimming",
                    "rating" => 3
                ],
                4
            ],


//            [
//                'valid/array_2dim_list.json',
//                'hobby.2.type',
//                'Flying',
//                [
//                    0 => "Flying",
//                    1 => "Flying",
//                    2 => "Flying",
//                ]
//            ],
//            [
//                'valid/array_2dim_list.json',
//                'name',
//                [
//                    "Peter",
//                    "Melanie",
//                    "Jim"
//                ],
//            ],
//            [
//                'valid/object_5dim.json',
//                'persons.hobby.type',
//                [
//                    [
//                        "Cars",
//                        "Planes",
//                        "Music"
//                    ],
//                    [
//                        "Comics",
//                        "Dancing"
//                    ]
//                ],
//            ],
//            [
//                'valid/object_5dim.json',
//                'persons.name',
//                [
//                    "Karl",
//                    "Jenni"
//                ],
//            ],
//            [
//                'valid/object_5dim.json',
//                'persons.1.name',
//                "Jenni",
//            ],
//            [
//                'valid/object_5dim.json',
//                'latlng',
//                [
//                    51,
//                    9
//                ],
//            ],
//            [
//                'valid/object_5dim.json',
//                'latlng.0',
//                51,
//            ],
//            [
//                'valid/array_1dim_objects_mix.json',
//                'price.bernd',
//                [],
//            ],
//            [
//                'valid/array_1dim_objects_mix.json',
//                'price',
//                [
//                    3 => 100
//                ]
//            ],
//            [
//                'valid/array_1dim_objects_mix.json',
//                'bernd',
//                []
//            ],
//            [
//                'valid/array_1dim_objects_mix.json',
//                'name',
//                [
//                    0 => "Pickett Burks",
//                    1 => "Cindy Stuart",
//                    3 => null
//                ]
//            ],
//            [
//                'valid/array_1dim_objects_mix.json',
//                'id',
//                [
//                    1 => 1,
//                    2 => 2,
//                    3 => 3
//                ]
//            ],
//            [
//                'valid/object_1dim.json',
//                'fr',
//                'Allemagne'
//            ],
//            [
//                'valid/object_1dim.json',
//                'xx',
//                new ValueNotFound(),
//            ],
//            [
//                'valid/array_1dim.json',
//                '',
//                [
//                    "voluptate",
//                    "dolor",
//                    "qui",
//                    "anim",
//                    "dolor",
//                    "cillum",
//                    "amet"
//                ]
//            ],
//            [
//                'valid/array_1dim_objects.json',
//                'id',
//                [
//                    0, 1, 2, 3
//                ]
//            ],
//            [
//                'valid/array_1dim_objects.json',
//                'name',
//                [
//                    "Pickett Burks",
//                    "Cindy Stuart",
//                    "Lelia Preston",
//                    null
//                ]
//            ],
        ];
    }
}