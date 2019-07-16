<?php

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create([
            'name' => 'Quan điểm - Tranh luận',
            'slug' => 'quan-diem-tranh-luan',
        ]);
        Category::create([
            'name' => 'Truyền cảm hứng',
            'slug' => 'truyen-cam-hung',
        ]);
        Category::create([
            'name' => 'Khoa học - Công nghệ',
            'slug' => 'khoa-hoc-cong-nghe',
        ]);
        Category::create([
            'name' => 'Game',
            'slug' => 'game',
        ]);
        Category::create([
            'name' => 'Thể thao',
            'slug' => 'the-thao',
        ]);
        Category::create([
            'name' => 'Sáng tác',
            'slug' => 'sang-tac',
        ]);
        Category::create([
            'name' => 'Comics',
            'slug' => 'comics',
        ]);
        Category::create([
            'name' => 'Phim',
            'slug' => 'phim',
        ]);
        Category::create([
            'name' => 'Kỹ năng',
            'slug' => 'ky-nang',
        ]);
        Category::create([
            'name' => 'Âm nhạc',
            'slug' => 'am-nhac',
        ]);
        Category::create([
            'name' => 'English Zone',
            'slug' => 'english-zone',
        ]);
        Category::create([
            'name' => 'Trò chuyện - Tâm sự',
            'slug' => 'tro-chuyen-tam-su',
        ]);
    }
}
