<?php

declare(strict_types=1);

namespace RoNoLo\JsonQuery;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonQueryTest extends TestCase
{
    #[DataProvider('canAccessPropertyFromObjectProvider')]
    public function testCanAccessPropertyFromObject(string $file, string $property, mixed $expected): void
    {
        $q = JsonQuery::fromFile(__DIR__ . '/../fixtures/' . $file);

        $actually = $q->query($property);

        $this->assertEquals($expected, $actually);
    }

    #[DataProvider('canAccessPropertyFromArrayProvider')]
    public function testCanAccessPropertyFromArray(string $file, string $property, mixed $expected): void
    {
        $q = JsonQuery::fromFile(__DIR__ . '/../fixtures/' . $file);

        $actually = $q->query($property);

        $this->assertEquals($expected, $actually);
    }

    public function testCanAccessPropertyWithSpaceInName(): void
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

        $actually = $q->query("bernd flo");
        $this->assertSame(1000, $actually);

        $actually = $q->query("heinz ron.gabriella barbara.b e");
        $this->assertSame([1 => 2], $actually);
    }

    public function testCanAccessPropertiesOfMixedData(): void
    {
        $data = [
            "bernd flo" => 1000,
            "bernd" => "heinz",
            1,
            null,
            0x00,
            10 => 12,
            "!" => "bernd",
            "#bruh" => "foo"
        ];

        $q = JsonQuery::fromData($data);

        $actually = $q->query("10");
        $this->assertSame(12, $actually);

        $actually = $q->query("bernd flo");
        $this->assertSame(1000, $actually);

        $actually = $q->query("bernd");
        $this->assertSame("heinz", $actually);

        $actually = $q->query("0");
        $this->assertSame(1, $actually);

        $actually = $q->query("1");
        $this->assertSame(null, $actually);

        $actually = $q->query("2");
        $this->assertSame(0, $actually);

        $actually = $q->query("!");
        $this->assertSame("bernd", $actually);

        $actually = $q->query("#bruh");
        $this->assertSame("foo", $actually);
    }

    public function testCanAcceptDataWithPhpCustomClass(): void
    {
        $data = [
            "flo" => new ClassWithoutJsonSerialize(),
            "bernd" => "heinz",
        ];

        $q = JsonQuery::fromData($data);

        $actually = $q->query("flo");
        $this->assertEquals(new \stdClass(), $actually);
    }

    public function testWillThrowExceptionOnUnparsebleJson(): void
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('Syntax error');

        JsonQuery::fromFile(__DIR__ . '/../fixtures/invalid/array_1dim.json');
    }

    public function testWillThrowExceptionOnNonExistentFile(): void
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('File "non_existent_file.json" could not be read.');

        JsonQuery::fromFile('non_existent_file.json');
    }

    public function testWillThrowExceptionOnInvalidJsonString(): void
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('Syntax error');

        JsonQuery::fromJson('{invalid json}');
    }

    public function testWillThrowExceptionOnUnencodableData(): void
    {
        $this->expectException(InvalidJsonException::class);

        $resource = fopen('php://memory', 'r');
        try {
            JsonQuery::fromData(['stream' => $resource]);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    public function testFromValidDataAndGetData(): void
    {
        $data = (object) ['foo' => 'bar'];
        $q = JsonQuery::fromValidData($data);

        $this->assertSame($data, $q->getData());
        $this->assertSame('bar', $q->query('foo'));
    }

    public function testQueryWithNullFieldReturnsAllData(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $q = JsonQuery::fromData($data);

        $this->assertEquals((object) $data, $q->query(null));
    }

    public function testQueryWithWhitespaceReturnsAllData(): void
    {
        $data = ['name' => 'John'];
        $q = JsonQuery::fromData($data);

        $this->assertEquals((object) $data, $q->query('   '));
    }

    public function testQueryWithEmptyArrayReturnsAllData(): void
    {
        $data = ['name' => 'John'];
        $q = JsonQuery::fromData($data);

        $this->assertEquals((object) $data, $q->query([]));
    }

    public function testQueryWithArrayOfFieldParts(): void
    {
        $data = [
            'users' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
            ],
        ];
        $q = JsonQuery::fromData($data);

        $this->assertSame('Alice', $q->query(['users', 0, 'name']));
        $this->assertSame('Bob', $q->query(['users', '1', 'name']));
    }

    public function testQueryWithIntegerOrFloat(): void
    {
        $data = ['first', 'second', 'third'];
        $q = JsonQuery::fromData($data);

        $this->assertSame('first', $q->query(0));
        $this->assertSame('second', $q->query(1));
        $this->assertEquals(new ValueNotFound(), $q->query(99));
    }

    public function testQueryArrayContainingNullsDoesNotLoop(): void
    {
        $data = [
            ['name' => 'Alice'],
            null,
            ['name' => 'Charlie'],
        ];
        $q = JsonQuery::fromData($data);

        $this->assertSame([0 => 'Alice', 2 => 'Charlie'], $q->query('name'));
    }

    public function testQueryArrayOfPrimitivesReturnsEmptyArrayWhenQueryingProperties(): void
    {
        $data = [1, 2, 3];
        $q = JsonQuery::fromData($data);

        $this->assertSame([], $q->query('name'));
    }

    public function testQueryReturnsValueNotFoundOnScalarNestedProperty(): void
    {
        $data = ['number' => 42];
        $q = JsonQuery::fromData($data);

        $this->assertEquals(new ValueNotFound(), $q->query('number.sub'));
    }

    public function testQueryReturnsValueNotFoundOnUnsupportedQueryType(): void
    {
        $data = ['name' => 'Alice'];
        $q = JsonQuery::fromData($data);

        $this->assertEquals(new ValueNotFound(), $q->query(true));
        $this->assertEquals(new ValueNotFound(), $q->query(new \stdClass()));
    }

    public function testQueryPrimitiveJsonRoots(): void
    {
        $qInt = JsonQuery::fromJson('123');
        $this->assertSame(123, $qInt->query(''));
        $this->assertEquals(new ValueNotFound(), $qInt->query('foo'));

        $qBool = JsonQuery::fromJson('true');
        $this->assertTrue($qBool->query(''));
        $this->assertEquals(new ValueNotFound(), $qBool->query('foo'));

        $qNull = JsonQuery::fromJson('null');
        $this->assertNull($qNull->query(''));
        $this->assertEquals(new ValueNotFound(), $qNull->query('foo'));
    }

    public static function canAccessPropertyFromObjectProvider(): array
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
                'valid/object_empty.json',
                '',
                new \stdClass(),
            ],
            [
                'valid/object_empty.json',
                'foo',
                new ValueNotFound(),
            ],
        ];
    }

    public static function canAccessPropertyFromArrayProvider(): array
    {
        return [
            [
                'valid/array_2dim_list.json',
                'hobby.type',
                [
                    [
                        "Gaming",
                        "Music"
                    ],
                    [
                        "Dancing",
                        "Sport",
                        "Movies"
                    ],
                    [
                        "Fighting"
                    ]
                ],
            ],
            [
                'valid/array_2dim_list.json',
                'hobby.2.type',
                [
                    1 => "Movies",
                ],
            ],
            [
                'valid/array_2dim_list.json',
                'name',
                [
                    "Peter",
                    "Melanie",
                    "Jim"
                ],
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
            [
                'valid/array_empty.json',
                '',
                [],
            ],
            [
                'valid/array_empty.json',
                '0',
                new ValueNotFound(),
            ],
            [
                'valid/array_empty.json',
                'foo',
                [],
            ],
            [
                'valid/array_1dim_3objects_empty.json',
                'name',
                [],
            ],
        ];
    }
}
