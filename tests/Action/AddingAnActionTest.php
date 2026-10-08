<?php

namespace App\Tests\Action;

use App\Action\ActionRegistry;
use App\Action\FileAction;
use App\Tests\Fixtures\GrayscaleAction;
use Imagick;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddingAnActionTest extends WebTestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    public function testClassImplementingTheInterfaceIsRegisteredWithoutAnyWiring(): void
    {
        $registry = static::getContainer()->get(ActionRegistry::class);

        $this->assertInstanceOf(ActionRegistry::class, $registry);
        $this->assertInstanceOf(GrayscaleAction::class, $registry->find('grayscale'));
        $this->assertSame(
            ['resize', 'convert', 'compress', 'rotate', 'sepia', 'grayscale'],
            array_map(static fn (FileAction $action): string => $action->name(), $registry->all()),
        );
    }

    public function testNewActionGetsAPostRouteWithoutAController(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.png';
        $image = new Imagick();
        $image->newImage(30, 20, 'red');
        $image->setImageFormat('png');
        $image->writeImage($path);
        $this->tempFiles[] = $path;

        $client = static::createClient();
        $client->request('POST', '/file/grayscale', [], ['file' => new UploadedFile($path, 'sample.png', 'image/png', null, true)]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('image/png', $client->getResponse()->headers->get('Content-Type'));

        $result = new Imagick();
        $result->readImageBlob($client->getInternalResponse()->getContent());
        $pixel = $result->getImagePixelColor(0, 0)->getColor();
        $this->assertSame($pixel['r'], $pixel['g']);
        $this->assertSame($pixel['g'], $pixel['b']);
    }

    public function testActionWithoutAnOptionRejectsAnExtraPathSegment(): void
    {
        $client = static::createClient();
        $client->request('POST', '/file/grayscale/5');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testNewActionGetsAPageNavigationEntryAndHomeCard(): void
    {
        $client = static::createClient();
        $client->request('GET', '/file-view/grayscale');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h1', 'Make an image grayscale');
        $this->assertSelectorTextSame('button.button-process', 'Make grayscale');
        $this->assertSelectorCount(1, 'input.filepond[data-action=grayscale]');
        $this->assertSelectorNotExists('.sheet-controls');
        $this->assertSelectorExists('nav.tools a[aria-current=page][href="/file-view/grayscale"]');

        $client->request('GET', '/file-view/main');
        $this->assertSelectorTextContains('.paper:last-child .paper-title', 'Grayscale');
        $this->assertSelectorTextContains('.paper:last-child .paper-spec', 'B&W');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function locales(): iterable
    {
        yield 'en' => ['en'];
        yield 'pl' => ['pl'];
    }

    /**
     * @dataProvider locales
     */
    public function testEveryRegisteredActionHasItsTranslations(string $locale): void
    {
        $container = static::getContainer();
        $registry = $container->get(ActionRegistry::class);
        $translator = $container->get(TranslatorInterface::class);
        $this->assertInstanceOf(ActionRegistry::class, $registry);
        $this->assertInstanceOf(TranslatorBagInterface::class, $translator);
        $catalogue = $translator->getCatalogue($locale);

        foreach ($registry->all() as $action) {
            $name = $action->name();
            $keys = ['tools.' . $name, 'home.card.' . $name . '.text', $name . '.title', $name . '.header', $name . '.description', $name . '.button', 'error.' . $name . '_failed'];
            $option = $action->option();
            if ($option !== null) {
                $keys[] = 'error.' . $name . '_invalid';
                $keys[] = $name . ($option->type->value === 'range' ? '.prompt_title' : '.option_label');
            }

            foreach ($keys as $key) {
                $this->assertTrue($catalogue->defines($key), sprintf('Missing "%s" in locale %s', $key, $locale));
            }
        }
    }
}
