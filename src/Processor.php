<?php

namespace AssetsMcs;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Request;

/**
 * Class Processor
 *
 * @package AssetsMcs
 */
class Processor
{
    protected $endpoint;

    public function __construct($endpoint)
    {
        if (null === $endpoint) {
            throw new \Exception(
                "Assets service: endpoint is null"
            );
        }
        $this->endpoint = $endpoint;
    }


    public function Read()
    {
        $client = new GuzzleClient();
        $request = new Request(
            'get',
            $this->getPath('/assets/collections'),
            ['content-type' => 'application/json']
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function ReadOne($id)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'get',
            $this->getPath(sprintf('/assets/collections/%s', $id)),
            ['content-type' => 'application/json']
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function Create($data)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'post',
            $this->getPath('/assets/collections'),
            ['content-type' => 'application/json'],
            json_encode($data)
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function Update($id, $data)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'put',
            $this->getPath(sprintf('/assets/collections/%s', $id)),
            ['content-type' => 'application/json'],
            json_encode($data)
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function Delete($id)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'delete',
            $this->getPath(sprintf('/assets/collections/%s', $id)),
            ['content-type' => 'application/json']
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function ItemsRead($collectionId)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'get',
            $this->getPath(
                sprintf('/assets/collections/%s/items', $collectionId)
            ),
            ['content-type' => 'application/json']
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function ItemsReadOne($collectionId, $itemId)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'get',
            $this->getPath(
                sprintf(
                    '/assets/collections/%s/items/%s', $collectionId, $itemId
                )
            ),
            ['content-type' => 'application/json']
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function ItemsCreate($collectionId, $data)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'post',
            $this->getPath(
                sprintf(
                    '/assets/collections/%s/items', $collectionId
                )
            ),
            ['content-type' => 'application/json'],
            json_encode($data)
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function ItemsUpdate($collectionId, $itemId, $data)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'put',
            $this->getPath(
                sprintf(
                    '/assets/collections/%s/items/%s', $collectionId, $itemId
                )
            ),
            ['content-type' => 'application/json'],
            json_encode($data)
        );
        $response = $this->send($client, $request);
        return $response;
    }

    public function ItemsDelete($collectionId, $itemId)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'delete',
            $this->getPath(
                sprintf(
                    '/assets/collections/%s/items/%s', $collectionId, $itemId
                )
            ),
            ['content-type' => 'application/json']
        );
        $response = $this->send($client, $request);
        return $response;
    }

    protected function getPath($path)
    {
        return $this->endpoint . $path;
    }

    public function send(GuzzleClient $client, Request $request)
    {
        try {
            $response = $client->send($request);
            $data = [
                'body'       => json_decode($response->getBody(), true),
                'headers'    => [],
                'statusCode' => $response->getStatusCode()
            ];

            if (!empty($total = $response->getHeader('X-Total-Count'))) {
                $data['headers']['X-Total-Count'] = $total;
            }
            if (!empty($rate = $response->getHeader('X-Ratelimit-Remaining'))) {
                $data['headers']['X-Ratelimit-Remaining'] = $rate;
            }
            return $data;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return [
                'body'       => [],
                'headers'    => [],
                'statusCode' => 204
            ];
        } catch (GuzzleClientException $e) {
            if ($e->getCode() >= 400 && $e->getCode() <= 499) {
                throw new \Exception($e->getMessage(), $e->getCode());
            } else {
                $message = $this->formatErrorMessage($e);
                throw new \Exception(json_encode($message), 0, $e);
            }
        }
    }

    /**
     * @param GuzzleClientException $httpException
     * @param $code
     *
     * @return array
     */
    public function formatErrorMessage($httpException)
    {
        $message = [
            'message'  => "Something bad happened with Assets service",
            'request'  => [
                'headers' => $httpException->getRequest()->getHeaders(),
                'body'    => $httpException->getRequest()->getBody()
            ],
            'response' => [
                'headers' => $httpException->getResponse()->getHeaders(),
                'body'    => $httpException->getResponse()->getBody()
                    ->getContents(),
                'status'  => $httpException->getResponse()->getStatusCode()
            ]
        ];

        return $message;
    }
}