<?php

$baseDir = dirname(__DIR__);

$appIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir.'/app')
);

foreach ($appIterator as $file) {
    if ($file->isDir()) {
        continue;
    }

    $path = $file->getPathname();
    if (substr($path, -4) !== '.php') {
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    if (strpos($content, 'private readonly ') !== false) {
        $content = str_replace('private readonly ', '', $content);
    }

    $content = preg_replace_callback(
        '/public function __construct\s*\((.*?)\)\s*\{\s*\}/s',
        function ($matches) {
            $params = $matches[1];
            preg_match_all('/\$([A-Za-z_][A-Za-z0-9_]*)/', $params, $vars);

            if (empty($vars[1])) {
                return $matches[0];
            }

            $assignments = [];
            foreach ($vars[1] as $var) {
                $assignments[] = '        $this->'.$var.' = $'.$var.';';
            }

            return "public function __construct(".$params.")\n    {\n"
                .implode("\n", $assignments)
                ."\n    }";
        },
        $content
    );

    if (strpos($content, '?->') !== false) {
        $content = preg_replace_callback(
            '/\$([A-Za-z_][A-Za-z0-9_]*(?:->\w+(?:\([^()]*\))?)*)\?->([A-Za-z_][A-Za-z0-9_]*(?:\([^()]*\))?)/',
            function ($matches) {
                return 'optional($'.$matches[1].')->'.$matches[2];
            },
            $content
        );
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated app file: ".$path.PHP_EOL;
    }
}

$viewsIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir.'/resources/views')
);

foreach ($viewsIterator as $file) {
    if ($file->isDir()) {
        continue;
    }

    $path = $file->getPathname();
    if (substr($path, -10) !== '.blade.php') {
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    if (strpos($content, '?->') !== false) {
        $content = preg_replace_callback(
            '/\$([A-Za-z_][A-Za-z0-9_]*(?:->\w+)*)\?->([A-Za-z_][A-Za-z0-9_]*)/',
            function ($matches) {
                return 'optional($'.$matches[1].')->'.$matches[2];
            },
            $content
        );
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated view file: ".$path.PHP_EOL;
    }
}
