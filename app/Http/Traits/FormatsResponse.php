<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use SimpleXMLElement;

trait FormatsResponse
{
    /**
     * Форматирует ответ в зависимости от параметра format
     */
    protected function formatResponse($data, Request $request, int $statusCode = 200)
    {
        $format = $this->getResponseFormat($request);

        return match($format) {
            'xml' => $this->xmlResponse($data, $statusCode, $request),
            default => $this->jsonResponse($data, $statusCode),
        };
    }

    /**
     * Определяет формат ответа из параметров запроса
     */
    protected function getResponseFormat(Request $request): string
    {
        $format = strtolower(trim((string) $request->get('format', 'json')));

        if (!$request->has('format')) {
            $format = $this->getFormatFromAcceptHeader($request);
        }

        return in_array($format, ['json', 'xml']) ? $format : 'json';
    }

    /**
     * Получает формат из Accept заголовка
     */
    protected function getFormatFromAcceptHeader(Request $request): string
    {
        $acceptHeader = $request->header('Accept', '');
        
        if (str_contains($acceptHeader, 'application/xml') || str_contains($acceptHeader, 'text/xml')) {
            return 'xml';
        }

        return 'json';
    }

    /**
     * Возвращает JSON ответ
     */
    protected function jsonResponse($data, int $statusCode = 200)
    {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->response()->setStatusCode($statusCode);
        }

        return response()->json($data, $statusCode);
    }

    /**
     * Возвращает XML ответ
     */
    protected function xmlResponse($data, int $statusCode = 200, ?Request $request = null)
    {
        $request = $request ?: request();
        
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            $data = $data->toArray($request);
        }
        
        if ($data === null) {
            $data = ['message' => 'No data'];
        }
        
        $xml = $this->arrayToXml($data, 'response', $request);

        return response($xml, $statusCode)->header('Content-Type', 'application/xml');
    }

    /**
     * Конвертирует массив в XML
     */
    protected function arrayToXml($data, string $rootElement = 'response', ?Request $request = null): string
    {
        $request = $request ?: request();
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$rootElement}></{$rootElement}>");

        if (is_array($data) || is_object($data)) {
            $this->arrayToXmlRecursive($data, $xml, $request);
        } else {
            $xml[0] = htmlspecialchars((string) $data);
        }

        return $xml->asXML();
    }

    /**
     * Рекурсивно конвертирует массив в XML
     */
    protected function arrayToXmlRecursive($data, SimpleXMLElement $xml, ?Request $request = null): void
    {
        $request = $request ?: request();

        if ($data === null) {
            return;
        }

        $data = $this->normalizeDataToArray($data, $request);

        if (!is_array($data)) {
            $xml[0] = htmlspecialchars((string) $data);
            return;
        }

        foreach ($data as $key => $value) {
            $this->addXmlChild($xml, $key, $value, $request);
        }
    }

    /**
     * Нормализует данные в массив
     */
    protected function normalizeDataToArray($data, Request $request)
    {
        if (!is_object($data)) {
            return $data;
        }

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->toArray($request);
        }

        if (method_exists($data, 'toArray')) {
            return $data->toArray();
        }

        return (array) $data;
    }

    /**
     * Добавляет дочерний элемент в XML
     */
    protected function addXmlChild(SimpleXMLElement $xml, $key, $value, Request $request): void
    {
        if (is_array($value)) {
            $this->addArrayChild($xml, $key, $value, $request);
            return;
        }

        if ($value === null) {
            $xml->addChild($key)->addAttribute('nil', 'true');
            return;
        }

        if (is_object($value)) {
            $this->addObjectChild($xml, $key, $value, $request);
            return;
        }

        $xml->addChild($key, htmlspecialchars((string) $value));
    }

    /**
     * Добавляет массив как дочерний элемент
     */
    protected function addArrayChild(SimpleXMLElement $xml, $key, array $value, Request $request): void
    {
        if (is_numeric($key)) {
            $key = 'item';
        }

        if ($this->isSequentialArray($value)) {
            $this->addSequentialArrayChild($xml, $key, $value, $request);
        } else {
            $child = $xml->addChild($key);
            $this->arrayToXmlRecursive($value, $child, $request);
        }
    }

    /**
     * Добавляет последовательный массив как коллекцию
     */
    protected function addSequentialArrayChild(SimpleXMLElement $xml, string $key, array $value, Request $request): void
    {
        $collection = $xml->addChild($key.'_collection');
        
        if (empty($value)) {
            $this->addEmptyTextNode($collection);
            return;
        }

        foreach ($value as $item) {
            $this->addCollectionItem($collection, $key, $item, $request);
        }
    }

    /**
     * Добавляет элемент коллекции
     */
    protected function addCollectionItem(SimpleXMLElement $collection, string $key, $item, Request $request): void
    {
        $itemElement = $collection->addChild($key);
        
        if (is_array($item)) {
            $this->arrayToXmlRecursive($item, $itemElement, $request);
        } else {
            $itemElement[0] = htmlspecialchars((string) $item);
        }
    }

    /**
     * Добавляет пустой текстовый узел
     */
    protected function addEmptyTextNode(SimpleXMLElement $element): void
    {
        $dom = dom_import_simplexml($element);
        $dom->appendChild($dom->ownerDocument->createTextNode(''));
    }

    /**
     * Добавляет объект как дочерний элемент
     */
    protected function addObjectChild(SimpleXMLElement $xml, string $key, object $value, Request $request): void
    {
        if (!method_exists($value, 'toArray')) {
            if (method_exists($value, '__toString')) {
                $xml->addChild($key, htmlspecialchars((string) $value));
            } else {
                $xml->addChild($key, htmlspecialchars(json_encode($value)));
            }
            return;
        }

        $child = $xml->addChild($key);
        $arrayValue = ($value instanceof JsonResource || $value instanceof ResourceCollection) 
            ? $value->toArray($request) 
            : $value->toArray();
        
        $this->arrayToXmlRecursive($arrayValue, $child, $request);
    }

    /**
     * Проверяет, является ли массив последовательным (индексированным)
     */
    protected function isSequentialArray(array $array): bool
    {
        if (empty($array)) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Форматирует ошибки валидации
     */
    protected function formatValidationErrors($errors, Request $request, int $statusCode = 422)
    {
        $data = [
            'message' => 'Validation failed',
            'errors' => $errors,
        ];

        return $this->formatResponse($data, $request, $statusCode);
    }

    /**
     * Форматирует ответ об ошибке
     */
    protected function formatErrorResponse(string $message, Request $request, int $statusCode = 400, array $additionalData = [])
    {
        $data = array_merge([
            'message' => $message,
            'status' => $statusCode,
        ], $additionalData);

        return $this->formatResponse($data, $request, $statusCode);
    }

    /**
     * Форматирует успешный ответ без данных
     */
    protected function formatSuccessResponse(string $message, Request $request, int $statusCode = 200, array $additionalData = [])
    {
        $data = array_merge([
            'message' => $message,
            'status' => $statusCode,
        ], $additionalData);

        return $this->formatResponse($data, $request, $statusCode);
    }
}