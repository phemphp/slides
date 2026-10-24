<?php

namespace App\Audience;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The URL of the audience view, and its QR code.
 */
final class AudienceLink
{
    public function __construct(
        private readonly UrlGeneratorInterface $urls,
        #[Autowire('%env(PUBLIC_URL)%')]
        private readonly string $publicUrl = '',
    ) {
    }

    public function url(): string
    {
        if ('' !== $this->publicUrl) {
            return rtrim($this->publicUrl, '/').'/';
        }

        return $this->urls->generate('audience', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return string an SVG data URI
     */
    public function qrCode(): string
    {
        $builder = new Builder(
            writer: new SvgWriter(),
            writerOptions: [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true],
            data: $this->url(),
            size: 512,
            margin: 16,
        );

        return $builder->build()->getDataUri();
    }
}
