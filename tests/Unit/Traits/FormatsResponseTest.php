<?php

namespace Tests\Unit\Traits;

use App\Http\Traits\FormatsResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormatsResponseTest extends TestCase
{
    use FormatsResponse;

    #[Test]
    public function it_returns_json_by_default(): void
    {
        $request = new Request();
        $data = ['message' => 'test'];

        $response = $this->formatResponse($data, $request);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertJson($response->getContent());
    }

    #[Test]
    public function it_returns_xml_when_format_is_xml(): void
    {
        $request = new Request(['format' => 'xml']);
        $data = ['message' => 'test'];

        $response = $this->formatResponse($data, $request);

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<?xml', $response->getContent());
        $this->assertStringContainsString('<message>test</message>', $response->getContent());
    }

    #[Test]
    public function get_response_format_detects_xml_from_parameter(): void
    {
        $request = new Request(['format' => 'xml']);

        $format = $this->getResponseFormat($request);

        $this->assertEquals('xml', $format);
    }

    #[Test]
    public function get_response_format_detects_xml_from_accept_header(): void
    {
        $request = new Request();
        $request->headers->set('Accept', 'application/xml');

        $format = $this->getResponseFormat($request);

        $this->assertEquals('xml', $format);
    }

    #[Test]
    public function get_response_format_detects_xml_from_text_xml_header(): void
    {
        $request = new Request();
        $request->headers->set('Accept', 'text/xml');

        $format = $this->getResponseFormat($request);

        $this->assertEquals('xml', $format);
    }

    #[Test]
    public function get_response_format_defaults_to_json(): void
    {
        $request = new Request();

        $format = $this->getResponseFormat($request);

        $this->assertEquals('json', $format);
    }

    #[Test]
    public function get_response_format_handles_invalid_format(): void
    {
        $request = new Request(['format' => 'invalid']);

        $format = $this->getResponseFormat($request);

        $this->assertEquals('json', $format);
    }

    #[Test]
    public function get_response_format_prefers_parameter_over_header(): void
    {
        $request = new Request(['format' => 'json']);
        $request->headers->set('Accept', 'application/xml');

        $format = $this->getResponseFormat($request);

        $this->assertEquals('json', $format);
    }

    #[Test]
    public function json_response_handles_plain_data(): void
    {
        $data = ['test' => 'value', 'number' => 123];

        $response = $this->jsonResponse($data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertJson($response->getContent());
        $this->assertStringContainsString('test', $response->getContent());
        $this->assertStringContainsString('value', $response->getContent());
    }

    #[Test]
    public function json_response_handles_custom_status_code(): void
    {
        $data = ['error' => 'Not found'];

        $response = $this->jsonResponse($data, 404);

        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function xml_response_handles_plain_data(): void
    {
        $data = ['message' => 'test', 'status' => 'success'];

        $response = $this->xmlResponse($data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<?xml', $response->getContent());
        $this->assertStringContainsString('<message>test</message>', $response->getContent());
        $this->assertStringContainsString('<status>success</status>', $response->getContent());
    }

    #[Test]
    public function xml_response_handles_custom_status_code(): void
    {
        $data = ['error' => 'Server error'];

        $response = $this->xmlResponse($data, 500);

        $this->assertEquals(500, $response->getStatusCode());
    }

    #[Test]
    public function array_to_xml_converts_simple_array(): void
    {
        $data = ['name' => 'John', 'age' => 30];

        $xml = $this->arrayToXml($data);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<name>John</name>', $xml);
        $this->assertStringContainsString('<age>30</age>', $xml);
    }

    #[Test]
    public function array_to_xml_uses_custom_root_element(): void
    {
        $data = ['name' => 'John'];

        $xml = $this->arrayToXml($data, 'user');

        $this->assertStringContainsString('<user>', $xml);
        $this->assertStringContainsString('</user>', $xml);
    }

    #[Test]
    public function it_handles_null_values_in_xml(): void
    {
        $request = new Request(['format' => 'xml']);
        $data = ['name' => 'John', 'email' => null];

        $response = $this->formatResponse($data, $request);

        $this->assertStringContainsString('<name>John</name>', $response->getContent());
        $this->assertStringContainsString('nil="true"', $response->getContent());
    }

    #[Test]
    public function it_handles_nested_arrays_in_xml(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'New York',
                ],
            ],
        ];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<name>John</name>', $content);
        $this->assertStringContainsString('<street>123 Main St</street>', $content);
        $this->assertStringContainsString('<city>New York</city>', $content);
    }

    #[Test]
    public function it_handles_sequential_arrays_as_collections(): void
    {
        $data = [
            'posts' => [
                ['title' => 'Post 1', 'id' => 1],
                ['title' => 'Post 2', 'id' => 2],
            ],
        ];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('posts_collection', $content);
        $this->assertStringContainsString('<title>Post 1</title>', $content);
        $this->assertStringContainsString('<title>Post 2</title>', $content);
    }

    #[Test]
    public function is_sequential_array_detects_sequential_arrays(): void
    {
        $sequential = ['a', 'b', 'c'];
        $associative = ['key1' => 'a', 'key2' => 'b'];
        $empty = [];

        $this->assertTrue($this->isSequentialArray($sequential));
        $this->assertFalse($this->isSequentialArray($associative));
        $this->assertTrue($this->isSequentialArray($empty));
    }

    #[Test]
    public function format_validation_errors_returns_422_status(): void
    {
        $request = new Request();
        $errors = ['name' => ['Name is required'], 'email' => ['Email is invalid']];

        $response = $this->formatValidationErrors($errors, $request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('Validation failed', $response->getContent());
        $this->assertStringContainsString('Name is required', $response->getContent());
    }

    #[Test]
    public function format_validation_errors_supports_xml(): void
    {
        $request = new Request(['format' => 'xml']);
        $errors = ['name' => ['Name is required']];

        $response = $this->formatValidationErrors($errors, $request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<message>Validation failed</message>', $response->getContent());
    }

    #[Test]
    public function format_validation_errors_uses_custom_status_code(): void
    {
        $request = new Request();
        $errors = ['field' => ['Error message']];

        $response = $this->formatValidationErrors($errors, $request, 400);

        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function format_error_response_returns_error_structure(): void
    {
        $request = new Request();

        $response = $this->formatErrorResponse('Something went wrong', $request, 500);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Something went wrong', $response->getContent());
        $this->assertStringContainsString('500', $response->getContent());
    }

    #[Test]
    public function format_error_response_includes_additional_data(): void
    {
        $request = new Request();
        $additionalData = ['code' => 'ERR001', 'details' => 'Database connection failed'];

        $response = $this->formatErrorResponse('Error occurred', $request, 500, $additionalData);

        $content = $response->getContent();
        $this->assertStringContainsString('ERR001', $content);
        $this->assertStringContainsString('Database connection failed', $content);
    }

    #[Test]
    public function format_error_response_supports_xml(): void
    {
        $request = new Request(['format' => 'xml']);

        $response = $this->formatErrorResponse('XML Error', $request, 400);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<message>XML Error</message>', $response->getContent());
    }

    #[Test]
    public function format_success_response_returns_success_structure(): void
    {
        $request = new Request();

        $response = $this->formatSuccessResponse('Operation successful', $request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Operation successful', $response->getContent());
        $this->assertStringContainsString('200', $response->getContent());
    }

    #[Test]
    public function format_success_response_uses_custom_status_code(): void
    {
        $request = new Request();

        $response = $this->formatSuccessResponse('Created successfully', $request, 201);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('201', $response->getContent());
    }

    #[Test]
    public function format_success_response_includes_additional_data(): void
    {
        $request = new Request();
        $additionalData = ['id' => 123, 'created_at' => '2023-12-01'];

        $response = $this->formatSuccessResponse('Created successfully', $request, 201, $additionalData);

        $content = $response->getContent();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('123', $content);
        $this->assertStringContainsString('2023-12-01', $content);
    }

    #[Test]
    public function format_success_response_supports_xml(): void
    {
        $request = new Request(['format' => 'xml']);

        $response = $this->formatSuccessResponse('XML Success', $request, 201);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<message>XML Success</message>', $response->getContent());
    }

    #[Test]
    public function it_handles_objects_with_to_array_method(): void
    {
        // Создаем мок объекта с методом toArray
        $mockObject = new class
        {
            public function toArray($request = null)
            {
                return ['name' => 'Mock Object', 'type' => 'test'];
            }
        };

        $data = ['object' => $mockObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<name>Mock Object</name>', $content);
        $this->assertStringContainsString('<type>test</type>', $content);
    }

    #[Test]
    public function it_handles_objects_with_to_string_method(): void
    {
        // Создаем мок объекта с методом __toString
        $mockObject = new class
        {
            public function __toString()
            {
                return 'String representation';
            }
        };

        $data = ['object' => $mockObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('String representation', $content);
    }

    #[Test]
    public function it_handles_objects_without_special_methods(): void
    {
        // Создаем простой объект без специальных методов
        $mockObject = new class
        {
            public $property = 'value';
        };

        $data = ['object' => $mockObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        // Должен быть сериализован как JSON
        $this->assertNotEmpty($content);
    }

    #[Test]
    public function it_handles_numeric_keys_in_arrays(): void
    {
        $data = [
            'items' => [
                0 => 'first',
                1 => 'second',
                2 => 'third',
            ],
        ];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('items_collection', $content);
        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
        $this->assertStringContainsString('third', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_empty_arrays(): void
    {
        $data = ['empty_array' => []];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<empty_array_collection>', $content);
    }

    #[Test]
    public function class_can_be_instantiated_in_test_context(): void
    {
        // Проверяем, что trait может использоваться в тестовом классе
        $this->assertInstanceOf(TestCase::class, $this);

        // Проверяем, что методы trait доступны
        $this->assertTrue(method_exists($this, 'formatResponse'));
        $this->assertTrue(method_exists($this, 'getResponseFormat'));
        $this->assertTrue(method_exists($this, 'jsonResponse'));
        $this->assertTrue(method_exists($this, 'xmlResponse'));
    }

    #[Test]
    public function array_to_xml_recursive_handles_json_resource(): void
    {
        // Создаем мок JsonResource с данными
        $mockResource = new class (['resource_data' => 'test_value']) extends JsonResource
        {
            public function toArray($request)
            {
                return ['resource_data' => 'test_value'];
            }
        };

        $data = ['resource' => $mockResource];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<resource_data>test_value</resource_data>', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_resource_collection(): void
    {
        // Создаем мок ResourceCollection с данными
        $mockCollection = new class ([]) extends ResourceCollection
        {
            public function toArray($request)
            {
                return ['collection_data' => ['item1', 'item2']];
            }
        };

        $data = ['collection' => $mockCollection];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('collection_data_collection', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_objects_with_toarray_method(): void
    {
        // Тестируем объекты с методом toArray (не JsonResource)
        $mockObject = new class
        {
            public function toArray()
            {
                return ['object_key' => 'object_value'];
            }
        };

        $data = ['object' => $mockObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<object_key>object_value</object_key>', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_plain_objects(): void
    {
        // Тестируем обычные объекты без специальных методов
        // Они должны быть сериализованы как JSON
        $plainObject = (object) ['property' => 'value'];

        $data = ['plain_object' => $plainObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        // Обычные объекты сериализуются как JSON строка
        $this->assertStringContainsString('{"property":"value"}', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_numeric_keys_as_item(): void
    {
        // Тестируем преобразование числовых ключей в 'item'
        $data = [
            0 => ['title' => 'First'],
            1 => ['title' => 'Second'],
        ];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<item>', $content);
        $this->assertStringContainsString('<title>First</title>', $content);
        $this->assertStringContainsString('<title>Second</title>', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_empty_sequential_array(): void
    {
        // Тестируем пустые последовательные массивы
        $data = ['empty_collection' => []];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<empty_collection_collection></empty_collection_collection>', $content);
    }

    #[Test]
    public function json_response_handles_json_resource(): void
    {
        // Тестируем JsonResource в jsonResponse
        $mockResource = new class (['test' => 'data']) extends JsonResource
        {
            public function toArray($request)
            {
                return ['resource_data' => 'test'];
            }
        };

        $response = $this->jsonResponse($mockResource);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function json_response_handles_resource_collection(): void
    {
        // Тестируем ResourceCollection в jsonResponse
        $mockCollection = new class ([]) extends ResourceCollection
        {
            public function toArray($request)
            {
                return ['data' => []];
            }
        };

        $response = $this->jsonResponse($mockCollection);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function xml_response_handles_json_resource(): void
    {
        // Тестируем JsonResource в xmlResponse
        $mockResource = new class (['test' => 'data']) extends JsonResource
        {
            public function toArray($request)
            {
                return ['resource_field' => 'resource_value'];
            }
        };

        $response = $this->xmlResponse($mockResource);

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<resource_field>resource_value</resource_field>', $response->getContent());
    }

    #[Test]
    public function xml_response_handles_resource_collection(): void
    {
        // Тестируем ResourceCollection в xmlResponse
        $mockCollection = new class ([]) extends ResourceCollection
        {
            public function toArray($request)
            {
                return ['collection_items' => ['item1', 'item2']];
            }
        };

        $response = $this->xmlResponse($mockCollection);

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('collection_items_collection', $response->getContent());
    }

    #[Test]
    public function array_to_xml_recursive_handles_objects_with_tostring(): void
    {
        // Тестируем объекты с методом __toString
        $mockObject = new class
        {
            public function __toString()
            {
                return 'String representation of object';
            }
        };

        $data = ['string_object' => $mockObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('String representation of object', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_objects_without_methods(): void
    {
        // Тестируем объекты без специальных методов (должны быть JSON-сериализованы)
        $plainObject = new \stdClass();
        $plainObject->test = 'value';

        $data = ['json_object' => $plainObject];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        // Объект должен быть сериализован как JSON
        $this->assertStringContainsString('{"test":"value"}', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_non_array_data_types(): void
    {
        // Тестируем различные типы данных, которые не массивы
        $data = [
            'string' => 'test string',
            'number' => 123,
            'boolean' => true,
            'null_value' => null,
        ];

        $response = $this->xmlResponse($data);

        $content = $response->getContent();
        $this->assertStringContainsString('<string>test string</string>', $content);
        $this->assertStringContainsString('<number>123</number>', $content);
        $this->assertStringContainsString('<boolean>1</boolean>', $content);
        $this->assertStringContainsString('nil="true"', $content);
    }

    #[Test]
    public function xml_response_handles_null_data(): void
    {
        $request = new Request();

        // Покрывает строки 70-72 в xmlResponse
        $response = $this->xmlResponse(null, 200, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<message>No data</message>', $response->getContent());
    }

    #[Test]
    public function array_to_xml_handles_scalar_data(): void
    {
        $request = new Request();

        // Покрывает строки 87-89 в arrayToXml
        $xml = $this->arrayToXml('simple string', 'response', $request);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<response>simple string</response>', $xml);
    }

    #[Test]
    public function array_to_xml_handles_numeric_scalar(): void
    {
        $request = new Request();

        // Покрывает строки 87-89 в arrayToXml для числового значения
        $xml = $this->arrayToXml(42, 'response', $request);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<response>42</response>', $xml);
    }

    #[Test]
    public function array_to_xml_recursive_handles_null_data(): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $request = new Request();

        // Покрывает строки 101-103 в arrayToXmlRecursive (early return для null)
        $this->arrayToXmlRecursive(null, $xml, $request);

        // Главное - метод не должен выбросить исключение и должен завершиться корректно
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    }

    #[Test]
    public function array_to_xml_recursive_handles_non_array_non_object_data(): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $request = new Request();

        // Покрывает строки 117-120 в arrayToXmlRecursive
        $this->arrayToXmlRecursive('simple text', $xml, $request);

        $content = $xml->asXML();
        $this->assertStringContainsString('<root>simple text</root>', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_object_without_toarray_or_tostring(): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $request = new Request();

        // Создаем объект без методов toArray или __toString
        $plainObject = new \stdClass();
        $plainObject->property = 'value';

        $data = ['plain_object' => $plainObject];

        // Покрывает строки 162-164 в arrayToXmlRecursive
        $this->arrayToXmlRecursive($data, $xml, $request);

        $content = $xml->asXML();
        $this->assertStringContainsString('<plain_object>', $content);
        $this->assertStringContainsString('{"property":"value"}', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_object_with_tostring(): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $request = new Request();

        // Создаем объект с методом __toString
        $stringableObject = new class
        {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $data = ['stringable' => $stringableObject];

        // Покрывает строки 160-162 в arrayToXmlRecursive
        $this->arrayToXmlRecursive($data, $xml, $request);

        $content = $xml->asXML();
        $this->assertStringContainsString('<stringable>stringable object</stringable>', $content);
    }

    #[Test]
    public function array_to_xml_recursive_handles_object_casting(): void
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $request = new Request();

        // Создаем объект без специальных методов для приведения к массиву
        $plainObject = new \stdClass();
        $plainObject->prop1 = 'value1';
        $plainObject->prop2 = 'value2';

        // Покрывает строки 112-114 в arrayToXmlRecursive (else ветка)
        $this->arrayToXmlRecursive($plainObject, $xml, $request);

        $content = $xml->asXML();
        $this->assertStringContainsString('<prop1>value1</prop1>', $content);
        $this->assertStringContainsString('<prop2>value2</prop2>', $content);
    }
}
