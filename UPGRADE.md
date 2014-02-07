# UPGRADE

## 1.0 to 1.1

 * The `HttpAdapterInterface::getContent` and the `HttpAdapterInterface::postContent` returns a
   `Widop\HttpAdapter\HttpResponse` instead of the response body string.
 * The `BuzzHttpAdapter::execute` returns a `Widop\HttpAdapter\HttpResponse` instead of the response body string.
 * The `StreamHttpAdapter::execute` returns a `Widop\HttpAdapter\HttpResponse` instead of the response body string.
 * The third argument of the `HttpAdapterInterface::postContent` (ie. `$content`) is now typehinted as `array`
   in order to be consistent with other parameters.
 * The third argument of the `CurlHttpAdapter::execute` have been removed.
