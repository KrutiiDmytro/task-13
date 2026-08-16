<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /** Названия категорий: вынесены, чтобы не дублировать литералы */
    private const CAT_NEWS = 'News';

    private const CAT_TIPS = 'Tips';

    private const CAT_TUTORIALS = 'Tutorials';

    private const CAT_GAMES = 'Games';

    public function run(): void // NOSONAR
    {
        $posts = [
            [
                'title' => 'Steam Deck gets a 90 Hz display in the next hardware revision',
                'content' => "Valve has quietly confirmed that the upcoming refresh of the Steam Deck will ship with a 90 Hz OLED panel, "
                    ."up from the 60 Hz LCD of the original model.\n\n"
                    .'Battery life is claimed to be unchanged despite the higher refresh rate, thanks to a more efficient APU '
                    .'built on a smaller process node. Pre-orders are expected to open later this quarter.',
                'date' => '2026-08-12',
                'category_name' => self::CAT_NEWS,
                'tags' => ['Steam Deck', 'PC', 'Performance'],
            ],
            [
                'title' => 'Five settings to change before you start any open-world game',
                'content' => "Default graphics presets are tuned for screenshots, not for playing. A handful of options make a "
                    ."far bigger difference to how a game feels than the preset label ever will.\n\n"
                    ."Turn off motion blur, cap your frame rate slightly below your monitor's refresh rate, drop volumetric "
                    .'clouds to medium, disable chromatic aberration, and raise the field of view. Together these usually buy '
                    .'you 20-30 percent more frames with almost no visual cost.',
                'date' => '2026-08-09',
                'category_name' => self::CAT_TIPS,
                'tags' => ['Open World', 'Performance', 'PC', 'Beginner'],
            ],
            [
                'title' => 'How to build an unkillable mage in a first playthrough',
                'content' => "Mages are fragile early and unstoppable late. The trick is surviving the gap in between.\n\n"
                    ."Step one: put your first six points into stamina, not intelligence. Step two: pick a shield spell you can "
                    ."cast while moving. Step three: never fight in an open field before level fifteen.\n\n"
                    .'From there, every point goes into spell damage and you simply stop dying.',
                'date' => '2026-08-05',
                'category_name' => self::CAT_TUTORIALS,
                'tags' => ['RPG', 'Beginner', 'Boss Fight'],
            ],
            [
                'title' => 'Hollow echoes: a roguelike that respects your time',
                'content' => "Most roguelikes ask for a hundred hours before they show you their best ideas. Hollow Echoes shows "
                    ."you everything it has in the first twenty minutes, and then asks whether you can do it faster.\n\n"
                    ."Runs last eight minutes. Death costs nothing. The whole design is built around the idea that the "
                    .'interesting decision is which risk you take, not how long you can grind.',
                'date' => '2026-08-02',
                'category_name' => self::CAT_GAMES,
                'tags' => ['Roguelike', 'Indie', 'Review'],
            ],
            [
                'title' => 'The co-op patch everyone asked for finally landed',
                'content' => "Eighteen months after launch, drop-in co-op is live. Progress now carries back to the host's world, "
                    ."which was the single most requested change on the official forum.\n\n"
                    .'The patch also fixes the long-standing inventory desync and rebalances three of the late-game bosses.',
                'date' => '2026-07-28',
                'category_name' => self::CAT_NEWS,
                'tags' => ['Co-op', 'Multiplayer', 'Patch Notes'],
            ],
            [
                'title' => 'Stop aiming with your wrist',
                'content' => "If you play at high sensitivity and struggle with consistency, the problem is almost never your "
                    ."settings. It is that you are aiming from the wrist instead of the arm.\n\n"
                    .'Lower your DPI until a 180 degree turn needs a full sweep of the mousepad. It will feel terrible for two '
                    .'days and noticeably better for the rest of your life.',
                'date' => '2026-07-24',
                'category_name' => self::CAT_TIPS,
                'tags' => ['FPS', 'Esports', 'PC'],
            ],
            [
                'title' => 'Modding for absolute beginners: your first texture swap',
                'content' => "You do not need to write code to make your first mod. You need a file, a folder and about "
                    ."fifteen minutes.\n\n"
                    ."This guide walks through extracting a texture from the game archive, editing it in any image editor, "
                    .'and loading it back in through the community mod loader. By the end you will have changed something '
                    .'you can actually see on screen.',
                'date' => '2026-07-19',
                'category_name' => self::CAT_TUTORIALS,
                'tags' => ['Modding', 'Beginner', 'PC'],
            ],
            [
                'title' => 'A strategy game that finally understands the late game',
                'content' => "Every 4X game has the same problem: by turn two hundred you have already won, and the game "
                    ."makes you prove it for another six hours.\n\n"
                    .'This one solves it by making your own empire the obstacle. The bigger you get, the harder your '
                    .'provinces are to hold, and the endgame turns into a genuine question instead of a formality.',
                'date' => '2026-07-14',
                'category_name' => self::CAT_GAMES,
                'tags' => ['Strategy', 'Review', 'PC'],
            ],
            [
                'title' => 'Handheld VR is closer than the headlines suggest',
                'content' => "Standalone headsets keep getting lighter, but the interesting shift is in the tracking stack "
                    ."rather than the optics.\n\n"
                    .'Inside-out tracking is now good enough that external sensors have effectively disappeared from the '
                    .'consumer market. What is still missing is a comfortable way to play for more than forty minutes.',
                'date' => '2026-07-08',
                'category_name' => self::CAT_NEWS,
                'tags' => ['VR', 'Nintendo Switch', 'Performance'],
            ],
        ];

        // Один автор на все посты сидера
        $user = User::first() ?? User::factory()->create();

        foreach ($posts as $postData) {
            $category = Category::where('name', $postData['category_name'])->first();

            $post = Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'date' => $postData['date'],
                // Без published_at пост не проходит scopePublished и не виден на публичных страницах
                'published_at' => $postData['date'].' 10:00:00',
                'category_id' => $category?->id,
                'user_id' => $user->id,
                'author_name' => $user->name,
                'author_email' => $user->email,
            ]);

            $tags = Tag::whereIn('name', $postData['tags'])->get();
            $post->tags()->attach($tags);
        }
    }
}
