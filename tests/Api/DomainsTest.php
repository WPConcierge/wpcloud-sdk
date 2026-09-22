<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Domains;
use WPConcierge\WPCloud\Api\Sites;
use WPConcierge\WPCloud\Tests\ApiTestCase;

/**
 * REFACTOR GUARDS — every test in this file passes against the code as it
 * stands today, and is expected to keep passing afterwards. None of them is a
 * red-then-green TDD case, and a pass here is NOT evidence that a bug was
 * fixed. They exist because `check-can-host-domain` was reached from
 * {@see Domains::checkCanHost()} with no test at all, and that method is about
 * to be rewritten to delegate to {@see Sites::checkCanHostDomain()} rather than
 * duplicate the call. These tests pin the observable behaviour across that
 * rewrite.
 *
 * The envelope guards below additionally pin what the platform really returns,
 * which is NOT what the docblocks on either method describe. Worked examples
 * are from the WP Cloud Field Guide (quick-start-guide.md:221,235,249):
 *
 *     {"message":"OK","data":{"allowed":true}}
 *     {"message":"Restricted domain [wordpress.com]","data":[]}
 *     {"message":"Domain name already used [...]. TXT record ...","data":[]}
 *
 * A refusal carries its reason in `message` with an EMPTY `data` array. There
 * is no `allowed: false`. Anything written against the prose — a preflight
 * testing for a false verdict — tests a condition that can never match and
 * waves through every domain it exists to refuse.
 */
class DomainsTest extends ApiTestCase
{
    /**
     * Guard 1 (refactor guard): the endpoint, verb and path shape the
     * delegation must not change.
     */
    public function testCheckCanHostIssuesGetToCanHostDomainPath(): void
    {
        (new Domains($this->client()))->checkCanHost('acme', 'x.example');

        $this->assertGet('check-can-host-domain/acme/x.example');
    }

    /**
     * Guard 3 (refactor guard): the point of the refactor. Both methods, given
     * the same arguments, must put the same request on the wire — same verb,
     * same URL, same (absent) body. This is what proves the delegation did not
     * change the call.
     *
     * Sites is the canonical home for this endpoint: Domains.php's own @see
     * points at tags/sites.md, and the SDK organises resources per WP Cloud
     * API tag.
     */
    public function testCheckCanHostSendsTheSameRequestAsSitesCheckCanHostDomain(): void
    {
        (new Domains($this->client()))->checkCanHost('acme', 'x.example');
        $viaDomains = $this->captured;

        (new Sites($this->client()))->checkCanHostDomain('acme', 'x.example');
        $viaSites = $this->captured;

        self::assertNotNull($viaDomains, 'No request was captured for Domains::checkCanHost().');
        self::assertNotNull($viaSites, 'No request was captured for Sites::checkCanHostDomain().');
        self::assertSame($viaDomains, $viaSites);
    }

    /**
     * Guard 4a (refactor guard): an ordinary domain reaches the URL verbatim.
     * Resource::path() rawurlencode()s every segment, and dots and hyphens are
     * unreserved, so real hostnames — subdomains, hyphens, punycode — pass
     * through untouched rather than being mangled into %2E / %2D.
     */
    public function testOrdinaryDomainSegmentsReachTheUrlVerbatim(): void
    {
        (new Domains($this->client()))->checkCanHost('acme', 'shop.faster-transfers.net');

        $this->assertGet('check-can-host-domain/acme/shop.faster-transfers.net');
    }

    /**
     * Guard 4b (refactor guard): a segment that would otherwise forge extra
     * path structure is escaped. This pins that Resource::path() really does
     * encode — a slash becomes %2F rather than an additional path segment.
     */
    public function testPathSegmentsAreUrlEncoded(): void
    {
        (new Domains($this->client()))->checkCanHost('ac/me', 'a b.example');

        $this->assertGet('check-can-host-domain/ac%2Fme/a%20b.example');
    }

    /**
     * Guard 5 (envelope guard): the allowed envelope. `data` is an object and
     * `allowed` is genuinely present and true, so the docblocks are right about
     * this half — it is the refusal half they get wrong.
     */
    public function testAllowedEnvelopeExposesATrueVerdict(): void
    {
        $json     = '{"message":"OK","data":{"allowed":true}}';
        $response = (new Domains($this->client($json)))->checkCanHost('acme', 'x.example');

        self::assertTrue($response->get('allowed'));
    }

    /**
     * Guard 6a (envelope guard): a refusal states its reason in `message`,
     * verbatim. This is the only place the verdict is expressed, so it is the
     * only thing a caller can act on.
     */
    public function testRefusalEnvelopeCarriesTheReasonInMessage(): void
    {
        $json     = '{"message":"Restricted domain [wordpress.com]","data":[]}';
        $response = (new Domains($this->client($json)))->checkCanHost('acme', 'wordpress.com');

        self::assertSame('Restricted domain [wordpress.com]', $response->message);
    }

    /**
     * Guard 6b (envelope guard) — THE IMPORTANT ONE. On a refusal there is no
     * `allowed` key at all: `data` is an empty array. So `get('allowed')` is
     * NOT false, it is the default — null.
     *
     * Each assertion pins something the others do not, and the order matters:
     *
     *  - assertSame([], $response->data) is what actually proves the key is
     *    ABSENT. It is the only one of the three that can tell absence from a
     *    key present holding null, because ApiResponse::get() coalesces with
     *    `??` — a stored null and a missing key both fall through to the
     *    default, so no amount of probing through get() can separate them.
     *  - the sentinel proves get() really is falling through to its default
     *    rather than returning a stored value that happens to be null.
     *  - assertNotFalse is the one that contradicts the docblocks directly.
     *
     * This is the executable form of what both docblocks get wrong. A reader
     * who trusts the prose over the platform is contradicted here rather than
     * by a comment.
     */
    public function testRefusalEnvelopeHasNoAllowedKeyAtAll(): void
    {
        $json     = '{"message":"Restricted domain [wordpress.com]","data":[]}';
        $response = (new Domains($this->client($json)))->checkCanHost('acme', 'wordpress.com');

        self::assertSame([], $response->data);
        self::assertSame('absent', $response->get('allowed', 'absent'));
        self::assertNotFalse($response->get('allowed'));
        self::assertNull($response->get('allowed'));
    }
}
