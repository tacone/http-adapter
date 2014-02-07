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

use Buzz\Browser;

/**
 * Buzz Http adapter.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class BuzzHttpAdapter extends AbstractHttpAdapter
{
    /** @var \Buzz\Browser */
    private $browser;

    /**
     * Constructor.
     *
     * @param \Buzz\Browser $browser      The buzz browser.
     * @param integer       $maxRedirects The maximum redirects.
     */
    public function __construct(Browser $browser = null, $maxRedirects = 5)
    {
        parent::__construct($maxRedirects);

        if ($browser === null) {
            $browser = new Browser();
        }

        $this->browser = $browser;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($url, array $headers = array())
    {
        $this->configure();

        try {
            $response = $this->browser->get($url, $headers);
        } catch (\Exception $e) {
            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $e->getMessage());
        }

        return $this->createResponse($url, $response->getHeaders(), $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function postContent($url, array $headers = array(), array $content = array(), array $files = array())
    {
        $this->configure();
        $post = $content;

        if (!empty($files)) {
            $post = array_merge($post, array_map(function($file) { return '@'.$file; }, $files));
        }

        try {
            $response = $this->browser->post($url, $headers, $post);
        } catch (\Exception $e) {
            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $e->getMessage());
        }

        return $this->createResponse($url, $response->getHeaders(), $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'buzz';
    }

    /**
     * Configures the buzz browser.
     */
    private function configure()
    {
        $this->browser->getClient()->setMaxRedirects($this->getMaxRedirects());
    }
}
