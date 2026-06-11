<?php
/**
 * HomeController
 *
 * Handles the public home/landing page which contains a brief
 * description of the service along with a placeholder image.
 */

declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    /**
     * Render the home page.
     */
    public function index(array $params = []): void
    {
        $pageTitle = 'Willkommen';
        require __DIR__ . '/../Views/layout.php';
    }
}
