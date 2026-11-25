<?php

namespace App\Tests\Phone;

use App\Phone\PhoneLink;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PhoneLinkTest extends TestCase
{
    #[TestWith(['http://localhost:8080/', '192.168.1.20', 'http://192.168.1.20:8080/'])]
    #[TestWith(['http://localhost:9000/slide', '192.168.1.20', 'http://192.168.1.20:9000/'])]
    #[TestWith(['http://localhost/', '192.168.1.20', 'http://192.168.1.20/'])]
    #[TestWith(['https://localhost/', '192.168.1.20', 'https://192.168.1.20/'])]
    #[TestWith(['http://192.168.1.50:8080/', '192.168.1.20', 'http://192.168.1.20:8080/'])]
    public function testTheUrlHasTheAddressOfTheHostAndThePortOfTheRequest(string $uri, string $hostIp, string $url): void
    {
        $this->assertSame($url, $this->link($uri, $hostIp)->url());
    }

    public function testWithNoAddressTheUrlIsTheAddressOfTheBrowser(): void
    {
        $this->assertSame('http://192.168.1.50:8080/', $this->link('http://192.168.1.50:8080/', '')->url());
    }

    #[TestWith(['http://localhost:8080/'])]
    #[TestWith(['http://127.0.0.1:8080/'])]
    #[TestWith(['http://[::1]:8080/'])]
    public function testWithNoAddressALoopbackBrowserHasNoUrl(string $uri): void
    {
        $this->assertNull($this->link($uri, '')->url());
    }

    public function testWithNoRequestThereIsNoUrl(): void
    {
        $this->assertNull(new PhoneLink(new RequestStack(), '192.168.1.20')->url());
    }

    #[TestWith(['https://deck.example.com'])]
    #[TestWith(['https://deck.example.com/'])]
    public function testAPublicUrlReplacesTheAddressOfTheHost(string $phoneUrl): void
    {
        $this->assertSame('https://deck.example.com/', $this->link('http://localhost:8080/', '192.168.1.20', $phoneUrl)->url());
    }

    public function testAPublicUrlNeedsNoRequest(): void
    {
        $this->assertSame('https://deck.example.com/', new PhoneLink(new RequestStack(), '', 'https://deck.example.com')->url());
    }

    public function testTheQrCodeIsAnSvgImage(): void
    {
        $qrCode = $this->link('http://localhost:8080/', '192.168.1.20')->qrCode('http://192.168.1.20:8080/');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qrCode);
    }

    private function link(string $uri, string $hostIp, string $phoneUrl = ''): PhoneLink
    {
        $requests = new RequestStack();
        $requests->push(Request::create($uri));

        return new PhoneLink($requests, $hostIp, $phoneUrl);
    }
}
