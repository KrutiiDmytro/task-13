<?php

namespace Tests\Unit\View\Components;

use App\View\Components\AppLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_layout_component_can_be_rendered(): void
    {
        $component = new AppLayout();

        $view = $component->render();

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertEquals('layouts.app', $view->getName());
    }

    public function test_app_layout_renders_correct_view(): void
    {
        $component = new AppLayout();
        
        $view = $component->render();
        
        $this->assertStringContainsString('layouts.app', $view->getName());
    }

    public function test_app_layout_view_exists(): void
    {
        $this->assertTrue(View::exists('layouts.app'));
    }

    public function test_app_layout_component_returns_view_instance(): void
    {
        $component = new AppLayout();
        
        $result = $component->render();
        
        $this->assertInstanceOf(\Illuminate\View\View::class, $result);
    }
}