<?php

namespace App\Ui\Code;

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

    private static function tokens(string $code): array
    {
        $code = rtrim(str_replace("\r\n", "\n", $code), "\n");
        $tokens = [];
        $inTag = false;
        $tagName = false;
        // A slot tag has a dot in its name: <Card.footer>. The name goes on after the dot.
        $slot = false;
        // In a Twig expression, a word is not the name of an attribute.
        $twig = false;
        $offset = 0;

        while ($offset < \strlen($code) && preg_match(self::PATTERN, $code, $m, 0, $offset)) {
            $text = $m[0];
            $offset += \strlen($text);
            $next = $code[$offset] ?? '';

            if ('' !== ($m['open'] ?? '')) {
                $tokens[] = ['punct', $text];
                $inTag = $tagName = true;
                $slot = false;
                continue;
            }

            if ($slot && '.' === $text) {
                $tokens[] = ['component', $text];
                $tagName = true;
                continue;
            }

            if ('' !== ($m['close'] ?? '') && $inTag) {
                $tokens[] = ['punct', $text];
                $inTag = $tagName = false;
                continue;
            }

            if ('' !== ($m['word'] ?? '')) {
                $class = match (true) {
                    $tagName => $slot || ctype_upper($text[0]) ? 'component' : 'tag',
                    // An attribute with a value (title="…") or without one (lazy).
                    $inTag && !$twig && ('=' === $next || 1 === preg_match('~[\s/>]~', $next)) => 'attr',
                    \in_array($text, self::KEYWORDS, true) => 'keyword',
                    '(' === $next => ctype_upper($text[0]) ? 'component' : 'function',
                    default => null,
                };
                $slot = $tagName && 'component' === $class && '.' === $next;
                $tagName = false;
                $tokens[] = [$class, $text];
                continue;
            }

            $tagName = false;
            if ('' !== ($m['twig'] ?? '')) {
                $twig = \in_array($text, ['{{', '{%'], true);
            }
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
