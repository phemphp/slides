<?php

namespace App\Audience;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Phem\Routing\PageName;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function App\Ui\Page\Audience;

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

        return $this->urls->generate(PageName::of(Audience(...)), [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

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
