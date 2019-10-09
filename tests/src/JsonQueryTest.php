<?php

namespace RoNoLo\JsonQuery;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Json;

class JsonQueryTest extends TestCase
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

        $actually = $q->getNestedProperty($property);

        $this->assertEquals($expected, $actually);
    }

    /**
     * This tests if properties with spaces in it will work.
     */
    public function testCanAccessPropertyWithSpaceInName()
    {
        $data = [
            "bernd flo" => 1000,
            "bernd" => "heinz",
            "heinz ron" => [
                "gabriella barbara" => [
                    ["f k" => 1],
                    ["b e" => 2],
                ]
            ]
        ];

        $q = JsonQuery::fromData($data);

        $actually = $q->getNestedProperty("bernd flo");
        $this->assertEquals(1000, $actually);

        $actually = $q->getNestedProperty("heinz ron.gabriella barbara.b e");
        $this->assertEquals([1 => 2], $actually);
    }

    /**
     * This tests if aweful data can be accessed.
     *
     * The thing is every PHP array or object given will be
     * converted into json first. That means the array from the
     * example will be converted into an JSON object and can be
     * accessed.
     */
    public function testCanAccessPropertiesOfMixedData()
    {
        $data = [
            "bernd flo" => 1000,
            "bernd" => "heinz",
            1,
            null,
            0x00,
            10 => 12,
        ];

        $q = JsonQuery::fromData($data);

        $acutally = $q->getNestedProperty("10");
        $this->assertEquals(12, $acutally);

        $acutally = $q->getNestedProperty("bernd flo");
        $this->assertEquals(1000, $acutally);

        $acutally = $q->getNestedProperty("bernd");
        $this->assertEquals("heinz", $acutally);

        $acutally = $q->getNestedProperty("0");
        $this->assertEquals(1, $acutally);

        $acutally = $q->getNestedProperty("1");
        $this->assertEquals(null, $acutally);

        $acutally = $q->getNestedProperty("2");
        $this->assertEquals(0, $acutally);
    }

    /**
     * This tests if a PHP data array, which contains a class,
     * which do not implement \JsonSerialize.
     *
     * Custom classes without the \JsonSerialize return an
     * empty object, when json_encode().
     */
    public function testCanAcceptDataWithPhpCustomClass()
    {
        $data = [
            "flo" => new ClassWithoutJsonSerialize(),
            "bernd" => "heinz",
        ];

        $q = JsonQuery::fromData($data);

        $acutally = $q->getNestedProperty("flo");
        $this->assertEquals(new \stdClass(), $acutally);
    }

    public function testWillThrowExceptionOnUnparsebleJson()
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('Syntax error');

        JsonQuery::fromFile(__DIR__ . '/../fixtures/invalid/array_1dim.json');
    }

    public function canAccessPropertyProvider()
    {
        return [
            [
                'valid/object_5dim.json',
                'persons.hobby.type',
                [
                    [
                        "Cars",
                        "Planes",
                        "Music"
                    ],
                    [
                        "Comics",
                        "Dancing"
                    ]
                ],
            ],
            [
                'valid/object_5dim.json',
                'persons.name',
                [
                    "Karl",
                    "Jenni"
                ],
            ],
            [
                'valid/object_5dim.json',
                'persons.1.name',
                "Jenni",
            ],
            [
                'valid/object_5dim.json',
                'latlng',
                [
                    51,
                    9
                ],
            ],
            [
                'valid/object_5dim.json',
                'latlng.0',
                51,
            ],
            [
                'valid/array_1dim_objects_mix.json',
                'price.bernd',
                [],
            ],
            [
                'valid/array_1dim_objects_mix.json',
                'price',
                [
                    3 => 100
                ]
            ],
            [
                'valid/array_1dim_objects_mix.json',
                'bernd',
                []
            ],
            [
                'valid/array_1dim_objects_mix.json',
                'name',
                [
                    0 => "Pickett Burks",
                    1 => "Cindy Stuart",
                    3 => null
                ]
            ],
            [
                'valid/array_1dim_objects_mix.json',
                'id',
                [
                    1 => 1,
                    2 => 2,
                    3 => 3
                ]
            ],
            [
                'valid/object_1dim.json',
                'fr',
                'Allemagne'
            ],
            [
                'valid/object_1dim.json',
                'xx',
                new ValueNotFound(),
            ],
            [
                'valid/array_1dim.json',
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
                'valid/array_1dim_objects.json',
                'id',
                [
                    0, 1, 2, 3
                ]
            ],
            [
                'valid/array_1dim_objects.json',
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