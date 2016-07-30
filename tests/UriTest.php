<?php
namespace Test;

// \Http\Message
use Kambo\Http\Message\Uri;

/**
 * Unit test for the UriTest object.
 *
 * @package Test
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test create URI object with all parameters
     *
     * @return void
     */
    public function testCreateUri()
    {
        $uri = new Uri(
            'http',
            'www.example.com',
            1111,
            '/path/123',
            'q=abc',
            'fragment',
            'user',
            'password'
        );

        $this->assertEquals('fragment', $uri->getFragment());
        $this->assertEquals('www.example.com', $uri->getHost());
        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals(1111, $uri->getPort());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
    }

    /**
     * Test create URI object with missing port information.
     *
     * @return void
     */
    public function testCreateUriNoPort()
    {
        $uri = new Uri(
            'http',
            'www.example.com'
        );

        $this->assertEquals(null, $uri->getPort());
    }

    /**
     * Test creation of URI object with missing user info (password and username).
     *
     * @return void
     */
    public function testCreateUriMissingUserInfo()
    {
        $uri = new Uri(
            'http',
            'test.com'
        );

        $this->assertEquals(null, $uri->getUserInfo());
    }

    /**
     * Test create URI object with already encoded query string. Uri MUST NOT
     * double-encode characters that are already percent-encoded.
     *
     * @return void
     */
    public function testCreateUriQueryAlreadyEncoded()
    {
        $uri = new Uri(
            'http',
            'test.com',
            null,
            '/',
            'foo%20foo=bar'
        );

        $this->assertEquals('foo%20foo=bar', $uri->getQuery());
    }

    /**
     * Test create URI object with not encoded query string - query contains space.
     *
     * @return void
     */
    public function testCreateUriQueryNotEncoded()
    {
        $uri = new Uri(
            'http',
            'test.com',
            null,
            '/path/123',
            'foo foo=bar'
        );

        $this->assertEquals('/path/123', $uri->getPath());
        $this->assertEquals('foo%20foo=bar', $uri->getQuery());
    }

    /**
     * Test create URI object with empty path - it must be preserved.
     *
     * @return void
     */
    public function testCreateUriPathEmpty()
    {
        $uri = new Uri(
            'http',
            'test.com',
            null,
            ''
        );

        $this->assertEquals('', $uri->getPath());
    }

    /**
     * Test create URI object with invalid path.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testCreateUriPathInvalid()
    {
        $uri = new Uri(
            'http',
            'test.com',
            null,
            []
        );
    }

    /**
     * Test create URI object with empty path - it must be preserved.
     *
     * @return void
     */
    public function testCreateUriPathWithMultipleSlashes()
    {
        $uri = new Uri(
            'http',
            'test.com',
            null,
            '///'
        );

        $this->assertEquals('/', $uri->getPath());
    }

    /**
     * Test creation of string representation of URI object.
     *
     * @return void
     */
    public function testToString()
    {
        $uri = new Uri(
            'http',
            'domain.tld',
            1111,
            '/path/123',
            'q=abc',
            'test',
            'user',
            'password'
        );

        $this->assertEquals('http://user:password@domain.tld:1111/path/123?q=abc#test', (string)$uri);
    }

    /**
     * Test creation of string representation of URI object with
     * omnited username and password.
     *
     * @return void
     */
    public function testToStringMissingUsernamePassword()
    {
        $uri = new Uri(
            'http',
            'domain.tld',
            1111,
            '/path/123',
            'q=abc',
            'test'
        );

        $this->assertEquals('http://domain.tld:1111/path/123?q=abc#test', (string)$uri);
    }

    /**
     * Test creation of string representation of URI object with 
     * omnited protocol.
     *
     * @return void
     */
    public function testToStringMissingProtocol()
    {
        $uri = new Uri(
            '',
            'domain.tld',
            1111,
            '/data/test',
            'action=view',
            'fragment'
        );

        $this->assertEquals('//domain.tld:1111/data/test?action=view#fragment', (string)$uri);
    }

    /**
     * Test creation of string representation of URI object with 
     * omnited username, password, path, query and fragment.
     *
     * @return void
     */
    public function testToStringProtocolHostName()
    {
        $uri = new Uri(
            'http',
            'domain.tld'
        );
        $this->assertEquals('http://domain.tld/', (string)$uri);
    }

    /**
     * Test creation of string representation of URI object with 
     * omnited protocol, username, password, path, query and fragment.
     *
     * @return void
     */
    public function testToStringOnlyHostName()
    {
        $uri = new Uri(
            '',
            'domain.tld'
        );

        $this->assertEquals('//domain.tld/', (string)$uri);
    }

    /**
     * Test creation of string representation of URI object with 
     * omnited protocol, username, password, path, query and fragment.
     *
     * @return void
     */
    public function testToStringOnlyPath()
    {
        $uri = new Uri(
            'http',
            'domain.tld',
            80,
            '//foo/bar'
        );

        $this->assertEquals('http://domain.tld/foo/bar', (string)$uri);
    }

    /**
     * Test create URI object, schema and defualt port should be ignored. 
     *
     * @return void
     */
    public function testGetPortWithSchemeAndDefaultPort()
    {
        $uriHttp  = new Uri('http', 'www.example.com', 80);
        $uriHttps = new Uri('https', 'www.example.com', 443);

        $this->assertNull($uriHttp->getPort());
        $this->assertNull($uriHttps->getPort());
    }

    /**
     * Test create URI object, schema should be ignored. 
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithScheme()
    {
        $uri          = $this->getUriForTest();
        $uriWithHttps = $uri->withScheme('https');

        $this->assertEquals('https', $uriWithHttps->getScheme());
        $this->assertEquals('/data/test', $uriWithHttps->getPath());

        $this->assertNotEquals($uri, $uriWithHttps);
    }

    /**
     * Test change URI schema, with invalid scheme in form of array. 
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithSchemeInvalidType()
    {
        $uri          = $this->getUriForTest();
        $uriWithHttps = $uri->withScheme(['https']);
    }

    /**
     * Test change URI schema, with invalid scheme. 
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithSchemeInvalidScheme()
    {
        $uri          = $this->getUriForTest();
        $uriWithHttps = $uri->withScheme('foo');
    }

    /**
     * Test change port of the uri.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithHost()
    {
        $uri         = $this->getUriForTest();
        $uriWithHost = $uri->withHost('www.foo.bar');

        $this->assertEquals('www.example.com', $uri->getHost());
        $this->assertEquals('www.foo.bar', $uriWithHost->getHost());
    }

    /**
     * Schema with defualt port should ignore port. 
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithPort()
    {
        $uri     = $this->getUriForTest();
        $uriPort = $uri->withPort(4040);

        $this->assertEquals(null, $uri->getPort());
        $this->assertEquals(4040, $uriPort->getPort());
    }

    /**
     * Test create url with invalid port.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithPortInvalid()
    {
        $uri = $this->getUriForTest();
        $uri->withPort(-1);
    }

    /**
     * Test change path of the URI.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithPath()
    {
        $uri     = $this->getUriForTest();
        $uriPath = $uri->withPath('/foo/bar');

        $this->assertEquals('/data/test', $uri->getPath());
        $this->assertEquals('/foo/bar', $uriPath->getPath());
    }

    /**
     * Test change path of the URI with invalid value in form of array.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithPathInvalidArray()
    {
        $uri = $this->getUriForTest();
        $uri->withPath(['/foo/bar']);
    }

    /**
     * Test change path of the URI with invalid value in form of query.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithPathInvalidQueryString()
    {
        $uri = $this->getUriForTest();
        $uri->withPath('/foo/bar?foo=bar');
    }

    /**
     * Test change path of the URI with invalid value in form of URI fragment.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithPathInvalidQueryStringFragment()
    {
        $uri = $this->getUriForTest();
        $uri->withPath('/foo/bar#fragment');
    }

    /**
     * Test change query part of the URI.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithQuery()
    {
        $uri      = $this->getUriForTest();
        $uriQuery = $uri->withQuery('foo=bar');

        $this->assertEquals('action=view', $uri->getQuery());
        $this->assertEquals('foo=bar', $uriQuery->getQuery());
    }

    /**
     * Test change query part of the URI with invalid value - null.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithQueryNull()
    {
        $uri = $this->getUriForTest();
        $uri->withQuery(null);
    }

    /**
     * Test change query part of the URI with already encoded value
     * value should not be encoded again.
     *
     * @return void
     */
    public function testWithQueryAlreadyEncode()
    {
        $uri      = $this->getUriForTest();
        $uriQuery = $uri->withQuery('foo%20foo=bar');

        $this->assertEquals('action=view', $uri->getQuery());
        $this->assertEquals('foo%20foo=bar', $uriQuery->getQuery());
    }

    /**
     * Test change query part of the URI with invalid value - array.
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithQueryInvalidFormat()
    {
        $uri = $this->getUriForTest();
        $uri->withQuery(['foo=bar']);
    }

    /**
     * Test change query part of the URI with invalid value - URI fragment. 
     *
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testWithQueryInvalidFormatFragment()
    {
        $uri = $this->getUriForTest();
        $uri->withQuery('foo=bar#foo');
    }

    /**
     * Test change fragment of the URI.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithFragment()
    {
        $uri         = $this->getUriForTest();
        $uriFragment = $uri->withFragment('foo-bar');

        $this->assertEquals('fragment', $uri->getFragment());
        $this->assertEquals('foo-bar', $uriFragment->getFragment());
    }

    /**
     * Test change user info - just user.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithUserInfoOnlyUser()
    {
        $uri     = $this->getUriForTest();
        $uriInfo = $uri->withUserInfo('foo');

        $this->assertEquals('', $uri->getUserInfo());
        $this->assertEquals('foo', $uriInfo->getUserInfo());
    }

    /**
     * Test change user info - user and password.
     * Operation must be immutable - a new instance of object must be created and previous
     * instance must retain its value.
     *
     * @return void
     */
    public function testWithUserInfo()
    {
        $uri     = $this->getUriForTest();
        $uriInfo = $uri->withUserInfo('foo', 'bar');

        $this->assertEquals('', $uri->getUserInfo());
        $this->assertEquals('foo:bar', $uriInfo->getUserInfo());
    }

    // ------------ PRIVATE METHODS

    /**
     * Get instance of uri for the testing purpose.
     *
     * @return Uri Instance of uri for the testing purpose.
     */
    private function getUriForTest()
    {
        return new Uri(
            'http',
            'www.example.com',
            80,
            '/data/test',
            'action=view',
            'fragment'
        );
    }
}
