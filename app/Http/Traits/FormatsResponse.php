<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use SimpleXMLElement;
use DOMDocument;

trait FormatsResponse
{
    /**
     * Форматирует ответ в зависимости от параметра format
     */
    protected function formatResponse($data, Request $request, int $statusCode = 200)
    {
        $format = $this->getResponseFormat($request);
        
        switch ($format) {
            case 'xml':
                return $this->xmlResponse($data, $statusCode, $request);
            case 'json':
            default:
                return $this->jsonResponse($data, $statusCode);
        }
    }

    /**
     * Определяет формат ответа из параметров запроса
     */
    protected function getResponseFormat(Request $request): string
    {
        // Проверяем параметр format
        $format = strtolower(trim((string) $request->get('format', 'json')));
        
        // Проверяем Accept заголовок
        if (!$request->has('format')) {
            $acceptHeader = $request->header('Accept', '');
            if (str_contains($acceptHeader, 'application/xml') || str_contains($acceptHeader, 'text/xml')) {
                $format = 'xml';
            }
        }
        
        return in_array($format, ['json', 'xml']) ? $format : 'json';
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
    protected function xmlResponse($data, int $statusCode = 200, Request $request = null)
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
    protected function arrayToXml($data, string $rootElement = 'response', Request $request = null): string
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
    protected function arrayToXmlRecursive($data, SimpleXMLElement $xml, Request $request = null): void
    {
        $request = $request ?: request();

        if ($data === null) {
            return;
        }
        
        // Если это объект, конвертируем в массив
        if (is_object($data)) {
            if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource
                || $data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
                $data = $data->toArray($request);
            } elseif (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = (array) $data;
            }
        }
        
        if (!is_array($data)) {
            $xml[0] = htmlspecialchars((string) $data);
            return;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                
                if ($this->isSequentialArray($value)) {
                    $collection = $xml->addChild($key . '_collection');
                    if (empty($value)) {
                        // Добавляем пустой текстовый узел, чтобы создать парные теги
                        $dom = dom_import_simplexml($collection);
                        $dom->appendChild($dom->ownerDocument->createTextNode(''));
                        continue;
                    }
                    foreach ($value as $item) {
                        $itemElement = $collection->addChild($key);
                        if (is_array($item)) {
                            $this->arrayToXmlRecursive($item, $itemElement, $request);
                        } else {
                            $itemElement[0] = htmlspecialchars((string) $item);
                        }
                    }
                } else {
                    $child = $xml->addChild($key);
                    $this->arrayToXmlRecursive($value, $child, $request);
                }
            } else {
                if ($value === null) {
                    $xml->addChild($key)->addAttribute('nil', 'true');
                } elseif (is_object($value)) {
                    if (method_exists($value, 'toArray')) {
                        $child = $xml->addChild($key);
                        if ($value instanceof \Illuminate\Http\Resources\Json\JsonResource
                            || $value instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
                            $this->arrayToXmlRecursive($value->toArray($request), $child, $request);
                        } else {
                            $this->arrayToXmlRecursive($value->toArray(), $child, $request);
                        }
                    } elseif (method_exists($value, '__toString')) {
                        $xml->addChild($key, htmlspecialchars((string) $value));
                    } else {
                        $xml->addChild($key, htmlspecialchars(json_encode($value)));
                    }
                } else {
                    $xml->addChild($key, htmlspecialchars((string) $value));
                }
            }
        }
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
            'errors' => $errors
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
            'status' => $statusCode
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
            'status' => $statusCode
        ], $additionalData);
        
        return $this->formatResponse($data, $request, $statusCode);
    }
}