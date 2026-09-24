<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileViewControllerTest extends WebTestCase
{
    /**
     * @dataProvider viewRouteProvider
     */
    public function testViewPageRendersSuccessfully(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function viewRouteProvider(): iterable
    {
        yield 'main' => ['/file-view/main'];
        yield 'resize' => ['/file-view/resize'];
        yield 'convert' => ['/file-view/convert'];
        yield 'compress' => ['/file-view/compress'];
        yield 'rotate' => ['/file-view/rotate'];
        yield 'sepia' => ['/file-view/sepia'];
    }

    public function testMainPageLinksToRotateAndSepia(): void
    {
        $client = static::createClient();
        $client->request('GET', '/file-view/main');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists('a[href="/file-view/rotate"]');
        $this->assertSelectorExists('a[href="/file-view/sepia"]');
    }

    /**
     * @dataProvider viewRouteProvider
     */
    public function testFooterLinksToPortfolioAndRepository(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        $this->assertSelectorExists('.app-footer a[href="https://kamil-galkowski.pl"]');
        $this->assertSelectorExists('.app-footer a[href="https://github.com/Tolemak/FileActions_BackendDemo"]');
    }

    public function testHomepageRedirectsToMainView(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/file-view/main');
    }

    public function testUnknownRouteRendersNotFoundPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/this-route-does-not-exist');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('404', (string) $client->getResponse()->getContent());
    }

    public function testLocaleSwitcherPersistsAcrossRequests(): void
    {
        $client = static::createClient();
        $client->request('GET', '/file-view/main?_locale=pl');
        $this->assertSelectorTextContains('h1', 'Witaj');

        $client->request('GET', '/file-view/main');
        $this->assertSelectorTextContains('h1', 'Witaj');
    }

    public function testLocalePersistsOnNotFoundPage(): void
    {
        // The locale must be applied before routing runs, otherwise a 404
        // (thrown by the router before a controller is even reached) always
        // falls back to the default locale regardless of the session.
        $client = static::createClient();
        $client->request('GET', '/file-view/main?_locale=pl');
        $client->request('GET', '/this-route-does-not-exist');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Nie znaleziono strony', (string) $client->getResponse()->getContent());
    }
}
