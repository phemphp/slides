<?php

namespace App\Phone;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The URL that opens the slides on a phone in the same network.
 */
final class PhoneLink
{
    private const array LOOPBACK = ['localhost', '127.0.0.1', '[::1]'];

    public function __construct(
        private readonly RequestStack $requests,
        // The address of the host in the current network. The Makefile sets it (see bin/host-ip).
        #[Autowire(env: 'HOST_IP')]
        private readonly string $hostIp = '',
    ) {
    }

    public function url(): ?string
    {
        $request = $this->requests->getMainRequest();
        if (null === $request) {
            return null;
        }

        if ('' !== $this->hostIp) {
            return \sprintf('%s://%s%s/', $request->getScheme(), $this->hostIp, $this->port($request));
        }

        // The browser already uses an address of the network.
        if (!\in_array($request->getHost(), self::LOOPBACK, true)) {
            return $request->getSchemeAndHttpHost().'/';
        }

        return null;
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

    /**
     * The port of the request is the port that the host publishes: the browser of the speaker
     * and the phone use the same port.
     */
    private function port(Request $request): string
    {
        $port = (int) $request->getPort();
        $default = $request->isSecure() ? 443 : 80;

        return $default === $port ? '' : ':'.$port;
    }
}
