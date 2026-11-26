<?php

namespace App\Tests\Phone;

use App\Phone\PhoneLink;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class PhoneLinkTest extends TestCase
{
    #[TestWith(['https://deck.example.com'])]
    #[TestWith(['https://deck.example.com/'])]
    public function testTheUrlIsThePublicUrl(string $phoneUrl): void
    {
        $this->assertSame('https://deck.example.com/', new PhoneLink($phoneUrl)->url());
    }

    public function testWithNoPublicUrlThereIsNoUrl(): void
    {
        $this->assertNull(new PhoneLink()->url());
    }

    public function testTheQrCodeIsAnSvgImage(): void
    {
        $qrCode = new PhoneLink('https://deck.example.com')->qrCode('https://deck.example.com/');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qrCode);
    }
}
