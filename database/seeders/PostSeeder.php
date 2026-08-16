<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PostSeeder extends Seeder
{
    /** Куда складываются обложки на диске public */
    private const COVER_DIR = 'images/posts';

    /** Названия категорий: вынесены, чтобы не дублировать литералы */
    private const CAT_NEWS = 'News';

    private const CAT_TIPS = 'Tips';

    private const CAT_TUTORIALS = 'Tutorials';

    private const CAT_GAMES = 'Games';

    /**
     * Посты пересказывают реальные материалы игровых изданий своими словами
     * и ссылаются на первоисточник. Копировать чужой текст нельзя — он защищён
     * авторским правом, поэтому дословных цитат здесь нет.
     */
    public function run(): void
    {
        $this->publishCoverImages();

        // Один автор на все посты сидера
        $user = User::first() ?? User::factory()->create();

        foreach ($this->postsData() as $postData) {
            $category = Category::where('name', $postData['category_name'])->first();

            $post = Post::create([
                'title' => $postData['title'],
                'content' => $this->withSource($postData),
                'date' => $postData['date'],
                // Без published_at пост не проходит scopePublished и не виден на публичных страницах
                'published_at' => $postData['date'].' 10:00:00',
                // Обложки лежат в storage/app/public, отдаются через симлинк public/storage
                'image' => $postData['image'],
                'category_id' => $category?->id,
                'user_id' => $user->id,
                'author_name' => $user->name,
                'author_email' => $user->email,
            ]);

            $tags = Tag::whereIn('name', $postData['tags'])->get();
            $post->tags()->attach($tags);
        }
    }

    /** Единственный источник правды по содержимому постов */
    public function postsData(): array // NOSONAR
    {
        return [
            [
                'title' => 'Steam Machine ships at $1,049, and the memory shortage is why',
                'content' => "Valve's living-room SteamOS box finally started shipping this summer, after sliding from an "
                    ."early-2026 target to a vaguer \"this year\". It starts at \$1,049 for the 512 GB model.\n\n"
                    .'That is a long way above the $600-800 most people had penciled in. Valve pointed at the same cause '
                    .'each time it moved the date: an AI-driven run on memory and storage chips that pushed component '
                    ."costs up across the whole industry. The company delayed the launch while it reconsidered pricing.\n\n"
                    .'For existing Deck owners the quieter detail matters more. SteamOS 3 now spans the handheld and the '
                    .'living room, and the Verified programme has been extended to cover the Steam Machine, so the '
                    .'compatibility labels you already trust carry over.',
                'date' => '2026-08-14',
                'category_name' => self::CAT_NEWS,
                'tags' => ['Steam Deck', 'PC', 'Performance'],
                'image' => 'images/posts/01-steam-deck.jpg',
                'source_name' => "Tom's Hardware",
                'source_url' => 'https://www.tomshardware.com/video-games/console-gaming/valve-delays-steam-machine-and-says-it-is-reconsidering-pricing-critical-component-shortage-and-costs-behind-the-move',
            ],
            [
                'title' => 'The graphics settings that actually cost you frames',
                'content' => 'A preset bundles a dozen decisions into one word, and a handful of them account for most of '
                    ."the cost. Knowing which ones lets you keep the look and drop the bill.\n\n"
                    .'Shadows and ambient occlusion are the usual culprits. Stepping shadow quality one notch down from '
                    .'Ultra typically buys 10-15 percent, and the difference is close to invisible once the camera is '
                    ."moving. SSAO behaves the same way: a lot of frames for detail you do not register mid-fight.\n\n"
                    .'The real relic is legacy anti-aliasing. MSAA and SSAA can cost 20-40 percent for a job that modern '
                    .'temporal upscalers do better and cheaper. Upscaling is now the single biggest lever on the page, '
                    .'worth 30-60 percent depending on the quality mode you pick.',
                'date' => '2026-08-11',
                'category_name' => self::CAT_TIPS,
                'tags' => ['Performance', 'PC', 'Beginner'],
                'image' => 'images/posts/02-gaming-pc-rgb.jpg',
                'source_name' => 'Digital Foundry testing, via AllKeyShop',
                'source_url' => 'https://www.allkeyshop.com/blog/battlefield-6-pc-settings-boost-fps-news-r/',
            ],
            [
                'title' => 'Keeping a Baldur\'s Gate 3 sorcerer alive long enough to matter',
                'content' => 'Sorcerers hit hard and fold fast. Charisma is the obvious first stat, but the one that '
                    ."decides whether the build works is Constitution.\n\n"
                    .'Constitution does two jobs at once: it raises your hit points and it carries your concentration '
                    .'saves. Losing concentration means losing the spell you built the entire fight around, so it is not '
                    ."a defensive stat you can skip in favour of more damage.\n\n"
                    .'Sorcerers have no armour proficiency, which leaves Dexterity holding up your AC. A common fix is a '
                    .'Fighter dip: it grants Constitution save proficiency and armour, trading a caster level for the '
                    .'ability to stay upright. Draconic Bloodline is the forgiving subclass, since Draconic Resilience '
                    .'adds hit points and a floor under your AC. None of it replaces positioning — stay out of melee and '
                    .'break line of sight.',
                'date' => '2026-08-07',
                'category_name' => self::CAT_TUTORIALS,
                'tags' => ['RPG', 'Beginner', 'Boss Fight'],
                'image' => 'images/posts/03-aurora-magic.jpg',
                'source_name' => 'RPGBot',
                'source_url' => 'https://rpgbot.net/video-games/baldurs-gate-3/classes/sorcerer/',
            ],
            [
                'title' => 'Slay the Spire 2 is in Early Access, and co-op is the surprise',
                'content' => 'Mega Crit put the sequel into Steam Early Access on 5 March 2026, and it did the numbers '
                    ."you would expect from that name: six figures of concurrent players daily in the weeks after.\n\n"
                    .'The headline addition is four-player co-op. Everyone climbs the same Spire, routes get argued over '
                    .'on a shared map, potions can be handed to whoever needs them, and you can watch a teammate fight '
                    ."in real time instead of staring at a loading screen.\n\n"
                    .'Temper the timeline, though. Mega Crit has talked about one to two years before 1.0, and the '
                    .'console versions are not coming until that point.',
                'date' => '2026-08-13',
                'category_name' => self::CAT_GAMES,
                'tags' => ['Roguelike', 'Indie', 'Review', 'PC'],
                'image' => 'images/posts/04-neon-joysticks.jpg',
                'source_name' => 'Steam',
                'source_url' => 'https://store.steampowered.com/app/2868840/Slay_the_Spire_2/',
            ],
            [
                'title' => 'Valorant Champions heads to Shanghai as the current format bows out',
                'content' => 'The 2026 Valorant Champions runs from 24 September to 18 October in Shanghai with 16 teams, '
                    ."closing out the sixth season of the Champions Tour.\n\n"
                    .'It is also the last Champions under the current franchised format — 2027 moves toward something '
                    .'more open. This season already softens the edges with a "Path to Champions" for Challenger teams: '
                    .'each international league sends four of them into its Stage 2 playoffs, with a stipend to help '
                    ."cover travel.\n\n"
                    .'League of Legends is going the other way geographically. Worlds 2026 lands in the United States '
                    .'from 15 October to 14 November, split across Los Angeles, Allen and New York. T1 arrive as '
                    .'three-time defending champions.',
                'date' => '2026-08-15',
                'category_name' => self::CAT_NEWS,
                'tags' => ['Esports', 'Multiplayer', 'PC'],
                'image' => 'images/posts/05-esports-stage.jpg',
                'source_name' => 'VALORANT Esports',
                'source_url' => 'https://valorantesports.com/en-US/news/first-look-at-valorant-champions-tour-2026-',
            ],
            [
                'title' => 'Sensitivity is a commitment, not a setting',
                'content' => 'eDPI — your mouse DPI multiplied by the in-game slider — is the only number that compares '
                    .'across two different setups. Most pros land between roughly 200 and 800, which works out to '
                    ."somewhere around 25-50 cm of mousepad for a full 360.\n\n"
                    .'Where you sit in that band depends on the game. Valorant rewards crosshair placement over fast '
                    .'turns and tends to pull low, around 200-400. Apex asks you to track moving targets and spin '
                    ."quickly, so 400-800 is more typical.\n\n"
                    .'Do the housekeeping first: mouse acceleration off in Windows, polling rate at 1000 Hz. Pick a DPI '
                    .'of 400 or 800 and change only the in-game slider from there. Then the part everyone skips — leave '
                    .'it alone for two weeks before judging. Muscle memory cannot form against a moving target.',
                'date' => '2026-08-10',
                'category_name' => self::CAT_TIPS,
                'tags' => ['FPS', 'Esports', 'PC'],
                'image' => 'images/posts/06-rgb-keyboard.jpg',
                'source_name' => 'Turtle Beach',
                'source_url' => 'https://www.turtlebeach.com/blog/improve-aim-fps-mouse-settings-tips',
            ],
            [
                'title' => 'Your first Skyrim mod, without breaking the install',
                'content' => 'Vortex is the low-friction route into modding, mostly because Nexus builds it and it knows '
                    .'where everything lives. Grab it from Nexus Mods under Mods and Get Vortex, run the one-click '
                    ."installer, and sign in to your account.\n\n"
                    .'Then point it at the game. Choose "Select a game to manage", let it scan, and enable management on '
                    .'the entry it finds under Discovered. Before you touch a single mod, update Skyrim to the current '
                    ."version and back up your saves — that one step prevents most of the horror stories.\n\n"
                    .'After that the loop is short: download a mod, check it is enabled, deal with whatever notifications '
                    .'Vortex raises, play. Vortex flags conflicting files on its own, and LOOT sorts your load order so '
                    .'you do not have to reason about it manually.',
                'date' => '2026-08-05',
                'category_name' => self::CAT_TUTORIALS,
                'tags' => ['Modding', 'Beginner', 'PC'],
                'image' => 'images/posts/07-colorful-code.jpg',
                'source_name' => 'Nexus Mods Wiki',
                'source_url' => 'https://wiki.nexusmods.com/index.php/Modding_Skyrim_Special_Edition_with_Vortex',
            ],
            [
                'title' => 'Civilization VII has recovered, but Civ VI still owns the genre',
                'content' => "Civ VII made the boldest structural call in the series' history: it splits a campaign into "
                    .'distinct Ages — Antiquity, Exploration, Modern — each with its own civilisation choice, unit set '
                    ."and victory conditions. It forces strategic pivots the older games never asked for.\n\n"
                    .'That split the audience at the early-2025 launch and dragged the aggregate scores down to roughly '
                    .'79. Firaxis has patched aggressively since, and reviewers now treat it as a respectable 4X rather '
                    ."than a misfire — and the clearest entry point for anyone new to the genre.\n\n"
                    .'None of which has moved Civ VI off the throne. By player count it is still the centre of gravity '
                    .'here, a decade on. The more interesting alternative is Ara: History Untold, which runs simultaneous '
                    .'turns so everyone moves at once.',
                'date' => '2026-08-03',
                'category_name' => self::CAT_GAMES,
                'tags' => ['Strategy', 'Review', 'PC'],
                'image' => 'images/posts/08-arcade-cabinets.jpg',
                'source_name' => 'Wargamer',
                'source_url' => 'https://www.wargamer.com/4x-games',
            ],
            [
                'title' => 'Valve\'s Steam Frame bets on streaming first',
                'content' => 'Announced in November 2025, the Steam Frame is a standalone SteamOS headset built on an Arm '
                    ."chip: a Snapdragon 8 Gen 3 paired with 16 GB of RAM, double what the Quest 3 carries.\n\n"
                    .'The displays are dual 2160x2160 panels, one per eye, with 72, 80, 90 and 120 Hz modes plus an '
                    .'experimental 144. Eye tracking is built in and drives foveated rendering — and, more unusually, '
                    ."foveated streaming, spending bandwidth only where you are actually looking.\n\n"
                    .'The framing is the genuinely interesting part. Valve is building a PC VR streaming terminal that '
                    .'happens to also run on its own, which inverts how the standalone market has worked since 2019.',
                'date' => '2026-08-01',
                'category_name' => self::CAT_NEWS,
                'tags' => ['VR', 'PC', 'Performance'],
                'image' => 'images/posts/09-vr-headset.jpg',
                'source_name' => "Tom's Guide",
                'source_url' => 'https://www.tomsguide.com/computing/virtual-reality/valve-announces-steam-frame-vr-headset-a-premium-standalone-rival-to-the-meta-quest-3',
            ],
        ];
    }

    /** Дописывает ссылку на первоисточник в конец текста поста */
    private function withSource(array $postData): string
    {
        return $postData['content']."\n\nSource: ".$postData['source_name'].' — '.$postData['source_url'];
    }

    /**
     * Копирует обложки из репозитория на диск public.
     *
     * Содержимое storage/app/public целиком в .gitignore, поэтому оригиналы
     * лежат в database/seeders/assets/posts и публикуются при сидировании.
     * Авторы и лицензии перечислены в IMAGE-CREDITS.md.
     */
    private function publishCoverImages(): void
    {
        $sourceDir = __DIR__.'/assets/posts';

        if (! is_dir($sourceDir)) {
            $this->command?->warn("Обложки не найдены: {$sourceDir}");

            return;
        }

        $disk = Storage::disk('public');

        foreach (glob($sourceDir.'/*.jpg') as $file) {
            $target = self::COVER_DIR.'/'.basename($file);

            if (! $disk->exists($target)) {
                $disk->put($target, file_get_contents($file));
            }
        }
    }
}
