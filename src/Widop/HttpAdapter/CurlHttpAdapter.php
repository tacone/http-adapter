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

/**
 * Curl Http adapter.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class CurlHttpAdapter extends AbstractHttpAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getContent($url, array $headers = array())
    {
        return $this->execute($url, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function postContent($url, array $headers = array(), array $content = array(), array $files = array())
    {
        $fixedContent = $this->fixContent($content);

        return $this->execute($url, $headers, function ($curl) use ($content, $files, $fixedContent) {
            if (!empty($files)) {
                if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                    curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
                    $post = array_merge($content, array_map(function($file) { return new \CURLFile($file); }, $files));
                } else {
                    foreach ($content as &$value) {
                        if (is_string($value) && strpos($value, '@') === 0) {
                            $value = sprintf("\0%s", $value);
                        }
                    }

                    $post = array_merge($content, array_map(function($file) { return '@'.$file; }, $files));
                }
            } else {
                $post = $fixedContent;
            }

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'curl';
    }

    /**
     * Fetches a response from an URL.
     *
     * @param string   $url      The url to fetch.
     * @param array    $headers  The http headers.
     * @param callable $callable A callable function executed before fetching the url.
     *
     * @return \Widop\HttpAdapter\HttpResponse The response.
     */
    protected function execute($url, array $headers = array(), $callable = null)
    {
        $this->configure($curl = curl_init(), $url, $headers, $callable);

        if (($response = curl_exec($curl)) === false) {
            $error = curl_error($curl);
            curl_close($curl);

            throw HttpAdapterException::cannotFetchUrl($url, $this->getName(), $error);
        }

        $headersSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $effectiveUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

        curl_close($curl);

        $headers = substr($response, 0, $headersSize);
        $body = substr($response, $headersSize);

        return $this->createResponse($url, $headers, $body, $effectiveUrl);
    }

    /**
     * Configures the curl resource.
     *
     * @param resource $curl     The curl resource.
     * @param string   $url      The url to fetch.
     * @param array    $headers  The http headers.
     * @param callable $callable A callable function executed before fetching the url.
     */
    protected function configure($curl, $url, array $headers = array(), $callable = null)
    {
        curl_setopt($curl, CURLOPT_URL, $this->fixUrl($url));
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($this->getMaxRedirects() > 0) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_MAXREDIRS, $this->getMaxRedirects());
        }

        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->fixHeaders($headers));
        }

        if ($callable !== null) {
            call_user_func($callable, $curl);
        }
    }
}
