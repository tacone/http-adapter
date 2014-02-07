<?php

/*
 * This file is part of the Wid'op package.
 *
 * (c) Wid'op <contact@widop.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Widop\HttpAdapter;

use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface ;

/**
 * Guzzle Http adapter.
 *
 * @author Gunnar Lium <gunnarlium@gmail.com>
 */
class GuzzleHttpAdapter extends AbstractHttpAdapter
{
    /** @var \Guzzle\Http\ClientInterface */
    private $client;

    /**
     * Creates a guzzle adapter.
     *
     * @param \Guzzle\Http\ClientInterface $client       The guzzle client.
     * @param integer                      $maxRedirects The maximum redirects.
     */
    public function __construct(ClientInterface $client = null, $maxRedirects = 5)
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
        $request = $this->client->get($url, $headers);
        $this->configure($request);

        try {
            $response = $request->send();
        } catch (\Exception $e) {
            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $e->getMessage());
        }

        return $this->createResponse(
            $url,
            $response->getHeaders()->toArray(),
            $response->getBody(true),
            $response->getEffectiveUrl()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function postContent($url, array $headers = array(), array $content = array(), array $files = array())
    {
        $request = $this->client->post($url, $headers, $content);
        $this->configure($request);

        foreach ($files as $key => $file) {
            $request->addPostFile($key, $file);
        }

        try {
            $response = $request->send();
        } catch (\Exception $e) {
            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $e->getMessage());
        }

        return $this->createResponse(
            $url,
            $response->getHeaders()->toArray(),
            $response->getBody(true),
            $response->getEffectiveUrl()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'guzzle';
    }

    /**
     * Configures the guzzle request.
     *
     * @param \Guzzle\Http\Message\RequestInterface $request The request.
     */
    private function configure(RequestInterface $request)
    {
        $request->getParams()->set('redirect.max', $this->getMaxRedirects());
    }
}
