<?php

namespace App\Ui\Code;

/**
 * Highlights PHP and .phem source on the server, with the tok-* classes of the design system
 * (the same classes as the highlighter of the Phem docs). It is made for the short samples of
 * the slides, not for all of PHP.
 */
final class Highlighter
{
    private const KEYWORDS = [
        'abstract', 'as', 'async', 'await', 'bool', 'case', 'catch', 'class', 'const', 'echo', 'else', 'enum',
        'extends', 'false', 'final', 'float', 'fn', 'for', 'foreach', 'function', 'if', 'implements',
        'in', 'int', 'interface', 'match', 'mixed', 'namespace', 'new', 'null', 'private',
        'protected', 'public', 'readonly', 'return', 'self', 'static', 'string', 'throw', 'true',
        'try', 'use', 'void', 'while', 'endfor', 'endif',
    ];

    private const PATTERN = '~\G(?:'
        .'(?<comment>//[^\n]*|/\*.*?\*/|\#(?!\[)[^\n]*)'
        .'|(?<attribute>\#\[(?:[^\[\]]|\[[^\[\]]*\])*\])'
        .'|(?<string>\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*")'
        .'|(?<twig>\{\{|\}\}|\{%|%\})'
        .'|(?<variable>\$\w+)'
        .'|(?<open></?(?=[A-Za-z>]))'
        .'|(?<close>/?>)'
        .'|(?<number>\b\d+(?:\.\d+)?\b)'
        .'|(?<word>[A-Za-z_][\w-]*)'
        .'|(?<other>\s+|.)'
        .')~su';

    /**
     * @return list<string> the HTML of each line
     */
    public static function lines(string $code): array
    {
        $lines = [''];
        foreach (self::tokens($code) as [$class, $text]) {
            foreach (explode("\n", $text) as $i => $part) {
                if ($i > 0) {
                    $lines[] = '';
                }
                if ('' !== $part) {
                    $html = htmlspecialchars($part, \ENT_QUOTES);
                    $lines[array_key_last($lines)] .= null === $class ? $html : '<span class="tok-'.$class.'">'.$html.'</span>';
                }
            }
        }

        return $lines;
    }

    /**
     * @return list<array{?string, string}>
     */
    private static function tokens(string $code): array
    {
        $code = rtrim(str_replace("\r\n", "\n", $code), "\n");
        $tokens = [];
        $inTag = false;
        $tagName = false;
        $offset = 0;

        while ($offset < \strlen($code) && preg_match(self::PATTERN, $code, $m, 0, $offset)) {
            $text = $m[0];
            $offset += \strlen($text);
            $next = $code[$offset] ?? '';

            if ('' !== ($m['open'] ?? '')) {
                $tokens[] = ['punct', $text];
                $inTag = $tagName = true;
                continue;
            }

            if ('' !== ($m['close'] ?? '') && $inTag) {
                $tokens[] = ['punct', $text];
                $inTag = $tagName = false;
                continue;
            }

            if ('' !== ($m['word'] ?? '')) {
                $class = match (true) {
                    $tagName => ctype_upper($text[0]) ? 'component' : 'tag',
                    $inTag && '=' === $next => 'attr',
                    \in_array($text, self::KEYWORDS, true) => 'keyword',
                    '(' === $next => ctype_upper($text[0]) ? 'component' : 'function',
                    default => null,
                };
                $tagName = false;
                $tokens[] = [$class, $text];
                continue;
            }

            $tagName = false;
            $class = null;
            foreach (['comment', 'attribute', 'string', 'twig', 'variable', 'number'] as $name) {
                if ('' !== ($m[$name] ?? '')) {
                    $class = $name;
                    break;
                }
            }
            $tokens[] = [$class, $text];
        }

        return $tokens;
    }
}
