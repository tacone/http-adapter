<?php

/*
 * This file is part of the Widop package.
 *
 * (c) Widop <contact@widop.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Widop\HttpAdapter;

use Widop\HttpAdapter\HttpAdapterException;
use Zend\Http\Client;

/**
 * Zend http adapter.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class ZendHttpAdapter extends AbstractHttpAdapter
{
    /** @var \Zend\Http\Client */
    private $client;

    /**
     * Creates a Zend http adapter.
     *
     * @param \Zend\Http\Client $client       The Zend client.
     * @param integer           $maxRedirects The max redirects.
     */
    public function __construct(Client $client = null, $maxRedirects = 5)
    {
        parent::__construct($maxRedirects);

        if ($client === null) {
            $client = new Client();
        }

        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($url, array $headers = array())
    {
        $this->configure();

        try {
            $response = $this->client->setUri($url)->setHeaders($headers)->send();
        } catch (\Exception $e) {
            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $e->getMessage());
        }

        return $this->createResponse($url, $response->getHeaders()->toArray(), $response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    public function postContent($url, array $headers = array(), array $content = array(), array $files = array())
    {
        $this->configure();

        $request = $this->client
            ->setMethod('POST')
            ->setUri($url)
            ->setHeaders($headers)
            ->setParameterPost($content);

        foreach ($files as $key => $file) {
            $request->setFileUpload($file, $key);
        }

        try {
            $response = $request->send();
        } catch (\Exception $e) {
            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $e->getMessage());
        }

        return $this->createResponse($url, $response->getHeaders()->toArray(), $response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zend';
    }

    /**
     * Configures the Zend Http Client.
     */
    private function configure()
    {
        $this->client->setOptions(array('maxredirects' => $this->getMaxRedirects()));
    }
}
