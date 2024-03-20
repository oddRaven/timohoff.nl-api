<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Language;
use App\Models\LanguageTranslation;
use App\Models\Translation;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleControllerTest extends TestCase {
    use RefreshDatabase;

    private int $article_id = 0;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $titleTranslation = Translation::factory([
            'title' => 'title translation'
        ])->create();
        $textTranslation = Translation::factory([
            'title' => 'text translation'
        ])->create();

        $englishLanguage = Language::factory([
            'code' => 'en',
            'name' => 'English'
        ])->create();
        $dutchLanguage = Language::factory([
            'code' => 'nl',
            'name' => 'Dutch'
        ])->create();

        LanguageTranslation::factory([
            'translation_id' => $titleTranslation->id,
            'language_code' => 'nl',
            'text' => 'Titel'
        ])->create();
        LanguageTranslation::factory([
            'translation_id' => $titleTranslation->id,
            'language_code' => 'en',
            'text' => 'Title'
        ])->create();
        LanguageTranslation::factory([
            'translation_id' => $textTranslation->id,
            'language_code' => 'nl',
            'text' => 'Tekst'
        ])->create();
        LanguageTranslation::factory([
            'translation_id' => $textTranslation->id,
            'language_code' => 'en',
            'text' => 'Text'
        ])->create();

        $article = Article::factory([
            'title_translation_id' => $titleTranslation->id,
            'text_translation_id' => $textTranslation->id
        ])->create();
        $this->article_id = $article->id;

        $this->user = User::factory()->create();
    }

    public function tearDown(): void
    {
        Article::query()->delete();
        LanguageTranslation::query()->delete();
        Translation::query()->delete();
        Language::query()->delete();
        User::query()->delete();

        parent::tearDown();
    }

    public static function data_test_index ()
    {
        return [
            [
                "en",
                "Title",
                "Text",
            ],
            [
                "nl",
                "Titel",
                "Tekst",
            ],
        ];
    }

    /**
     * @dataProvider data_test_index
     */
    public function test_index ($language_code, $title, $text): void {
        // Arrange
        $path = "api/article";
        $headers = [
            "Content-Language" => $language_code
        ];

        $expected_json = [
            [
                'id' => $this->article_id,
                'title' => $title,
                'text' => $text
            ]
        ];

        // Act
        $response = $this->get($path, $headers);

        // Assert
        $response->assertStatus(200)
            ->assertJson($expected_json);
    }

    public static function data_test_show ()
    {
        return [
            [
                "en",
                "Title",
                "Text",
            ],
            [
                "nl",
                "Titel",
                "Tekst",
            ],
        ];
    }

    /**
     * @dataProvider data_test_show
     */
    public function test_show ($language_code, $title, $text): void {
        // Arrange
        $path = "api/article/{$this->article_id}";
        $headers = [
            "Content-Language" => $language_code
        ];

        $expected_json = [
            'id' => $this->article_id,
            'title' => $title,
            'text' => $text
        ];

        // Act
        $response = $this->get($path, $headers);

        // Assert
        $response->assertStatus(200)
            ->assertJson($expected_json);
    }

    public function test_show_include_language_translations (): void {
        // Arrange
        $path = "api/article/{$this->article_id}?include_language_translations";

        $expected_json = [
            'id' => $this->article_id,
            'title_translations' => [
                [
                    "language_code" => "en",
                    "text" => "Title",
                ],
                [
                    "language_code" => "nl",
                    "text" => "Titel",
                ],
            ],
            'text_translations' => [
                [
                    "language_code" => "en",
                    "text" => "Text",
                ],
                [
                    "language_code" => "nl",
                    "text" => "Tekst",
                ],
            ]
        ];

        // Act
        $response = $this->get($path);

        // Assert
        $response->assertStatus(200)
            ->assertJson($expected_json);
    }

    public function test_store (): void {
        // Arrange
        Sanctum::actingAs($this->user);

        $path = "api/article";

        $request = [
            'title_translations' => [
                [
                    'language_code' => 'nl',
                    'text' => 'Titel'
                ],
                [
                    'language_code' => 'en',
                    'text' => 'Title'
                ]
            ],
            'text_translations' => [
                [
                    'language_code' => 'nl',
                    'text' => 'Tekst'
                ],
                [
                    'language_code' => 'en',
                    'text' => 'Text'
                ]
            ]
        ];

        $expected_json = [
            "message" => "Article created."
        ];
        $expected_count = 2;

        // Act
        $response = $this->post($path, $request);

        // Assert
        $response->assertStatus(201)
            ->assertJson($expected_json);
        $this->assertEquals(Article::count(), $expected_count);
    }

    public function test_update (): void {
        // Arrange
        Sanctum::actingAs($this->user);

        $path = "api/article/{$this->article_id}";

        $request = [
            'title_translations' => [
                [
                    'language_code' => 'nl',
                    'text' => 'Titel foo'
                ],
                [
                    'language_code' => 'en',
                    'text' => 'Title foo'
                ]
            ],
            'text_translations' => [
                [
                    'language_code' => 'nl',
                    'text' => 'Tekst bar'
                ],
                [
                    'language_code' => 'en',
                    'text' => 'Text bar'
                ]
            ]
        ];

        $expected_json = [
            "message" => "Article updated.",
            "article" => [
                "id" => $this->article_id
            ]
        ];

        // Act
        $response = $this->put($path, $request);

        // Assert
        $response->assertStatus(200)
            ->assertJson($expected_json);
    }

    public function test_delete (): void {
        // Arrange
        Sanctum::actingAs($this->user);

        $path = "api/article/{$this->article_id}";

        $expected_json = [
            "message" => "Article deleted."
        ];
        $expected_count = 0;

        // Act
        $response = $this->delete($path);

        // Assert
        $response->assertStatus(200)
            ->assertJson($expected_json);
        $this->assertEquals(Article::count(), $expected_count);
    }
}