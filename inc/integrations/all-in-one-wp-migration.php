<?php

/**
 * All-in-One WP Migration exclusions.
 *
 * Exclude development files from all themes.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('ai1wm_exclude_themes_from_export', function ($exclude_filters) {

    $themes_directory = wp_normalize_path(
        WP_CONTENT_DIR . '/themes'
    );

    $development_names = array(
        '.git',
        'node_modules',
        '.tailwind',
        '.vscode',
        '.idea',
        '.codex',
        'tests',

        'package.json',
        'package-lock.json',
        'yarn.lock',
        'tailwind.config.js',

        'README.md',
        'AGENTS.md',
        '.gitignore',
        '.editorconfig',
    );

    try {
        $themes = new DirectoryIterator($themes_directory);

        foreach ($themes as $theme) {
            if (
                $theme->isDot() ||
                !$theme->isDir()
            ) {
                continue;
            }

            $theme_name = $theme->getBasename();

            $theme_directory = untrailingslashit(
                wp_normalize_path($theme->getPathname())
            );

            $iterator = new RecursiveIteratorIterator(
                new RecursiveCallbackFilterIterator(
                    new RecursiveDirectoryIterator(
                        $theme_directory,
                        FilesystemIterator::SKIP_DOTS
                    ),
                    function ($file) use (
                        &$exclude_filters,
                        $development_names,
                        $theme_directory,
                        $theme_name
                    ) {
                        $file_path = wp_normalize_path(
                            $file->getPathname()
                        );

                        $relative_inside_theme = ltrim(
                            substr(
                                $file_path,
                                strlen($theme_directory)
                            ),
                            '/'
                        );

                        /**
                         * Exclude assets/src
                         */
                        if (
                            $relative_inside_theme === 'assets/src' ||
                            strpos(
                                $relative_inside_theme,
                                'assets/src/'
                            ) === 0
                        ) {
                            $exclude_filters[] =
                                $theme_name . '/assets/src';

                            return false;
                        }

                        /**
                         * Exclude development files/folders
                         * anywhere inside the theme.
                         */
                        if (
                            in_array(
                                $file->getBasename(),
                                $development_names,
                                true
                            )
                        ) {
                            $exclude_filters[] =
                                $theme_name . '/' .
                                $relative_inside_theme;

                            return false;
                        }

                        return true;
                    }
                ),
                RecursiveIteratorIterator::SELF_FIRST
            );

            iterator_count($iterator);
        }
    } catch (UnexpectedValueException $exception) {
        // Keep export running if a directory cannot be read.
    }

    return array_values(
        array_unique($exclude_filters)
    );
});
