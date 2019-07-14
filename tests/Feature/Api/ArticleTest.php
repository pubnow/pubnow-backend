<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // ------
    // TODO: Tao article, neu da login, -> ok
    // TODO: Tag article, chua login -> 403
    // TODO: Tao article, da login, nhung truyen thieu data required (name || content || category) => 422
    // TODO: Tao article, da login, nhung truyen sai data (category != uuid, hoac category k ton tai) => 422
    // ----
    // TODO: Xem 1 article, ton tai -> ok
    // TODO: Xem 1 article, khong ton tai -> 404 not found
    // ----
    // TODO: Sua 1 article, ton tai + user la admin -> ok
    // TODO: Sua 1 article, ton tai + user la tac gia -> ok
    // TODO: Sua 1 article, ton tai + user khong phai tac gia -> 403
    // TODO: Sua 1 article, ton tai + user la admin, nhung data sai (category moi khong ton tai, hoac sai) -> 422
    // TODO: Sua 1 article, khong ton tai -> 404 not found
    // ----
    // TODO: Xoa 1 article, ton tai + user la admin -> ok
    // TODO: Xoa 1 article, ton tai + user la tac gia -> ok
    // TODO: Xoa 1 article, ton tai + user khong phai tac gia -> 403
    // TODO: Xoa 1 article, khong ton tai -> 404 not found
    // ----
}
