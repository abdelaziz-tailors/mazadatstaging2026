<?php

namespace Tests\Feature\Dashboard;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Regression guard: every dashboard list table used to carry Bootstrap's
 * ".table-striped" class (39 files), which alternated row backgrounds.
 * Neutralizing the color via theme.css wasn't enough of a guarantee, so the
 * class itself was removed from every Blade view — this scans the whole
 * dashboard views tree to make sure it never comes back on any page.
 */
class NoTableStripedClassTest extends TestCase
{
    public function test_no_dashboard_view_uses_the_table_striped_class()
    {
        $viewsPath = resource_path('views/dashboard');
        $this->assertDirectoryExists($viewsPath);

        $finder = (new Finder())->files()->in($viewsPath)->name('*.blade.php');

        $offenders = [];
        foreach ($finder as $file) {
            if (str_contains($file->getContents(), 'table-striped')) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty($offenders, 'These views still use table-striped: ' . implode(', ', $offenders));
    }
}
