<?php

namespace App\Phone;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The URL that opens the slides on a phone: a public URL, for example the URL of a tunnel.
 */
final class PhoneLink
{
    public function __construct(
        // "make up PHONE_URL=..." sets it (see "Phone" in README.md).
        #[Autowire(env: 'PHONE_URL')]
        private readonly string $phoneUrl = '',
    ) {
    }

    public function url(): ?string
    {
        return '' === $this->phoneUrl ? null : rtrim($this->phoneUrl, '/').'/';
    }

    public function qrCode(string $url): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            writerOptions: [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true],
            data: $url,
            size: 512,
            margin: 16,
        );

        return $builder->build()->getDataUri();
    }
}
