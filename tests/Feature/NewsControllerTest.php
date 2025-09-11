<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_news()
    {
        News::factory()->count(3)->create();

        $response = $this->getJson('/api/main/news');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data berita berhasil diambil'
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_news()
    {
        $data = [
            'title' => 'Berita Sekolah',
            'content' => 'Konten berita sekolah yang informatif',
            'is_published' => 'publish'
        ];

        $response = $this->postJson('/api/main/news', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Berita berhasil dibuat'
            ]);

        $this->assertDatabaseHas('news', [
            'title' => 'Berita Sekolah',
            'content' => 'Konten berita sekolah yang informatif',
            'is_published' => 'publish'
        ]);
    }

    /** @test */
    public function it_can_show_news()
    {
        $news = News::factory()->create();

        $response = $this->getJson("/api/main/news/{$news->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data berita berhasil diambil',
                'data' => [
                    'id' => $news->id,
                    'title' => $news->title
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_news()
    {
        $response = $this->getJson('/api/main/news/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Berita tidak ditemukan'
            ]);
    }

    /** @test */
    public function it_can_update_news()
    {
        $news = News::factory()->create(['title' => 'Berita Lama']);

        $updatedData = [
            'title' => 'Berita Baru',
            'content' => 'Konten berita yang diperbarui'
        ];

        $response = $this->putJson("/api/main/news/{$news->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Berita berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('news', array_merge(['id' => $news->id], $updatedData));
    }

    /** @test */
    public function it_can_delete_news()
    {
        $news = News::factory()->create();

        $response = $this->deleteJson("/api/main/news/{$news->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Berita berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }
}
