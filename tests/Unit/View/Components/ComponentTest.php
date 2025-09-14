<?php

namespace Tests\Unit\View\Components;

use App\View\Components\AppLayout;
use App\View\Components\GuestLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ComponentTest extends TestCase
{
    use RefreshDatabase;

    // === Тесты для AppLayout ===

    public function test_app_layout_component_can_be_instantiated(): void
    {
        $component = new AppLayout();

        $this->assertInstanceOf(AppLayout::class, $component);
    }

    public function test_app_layout_component_render_returns_view(): void
    {
        $component = new AppLayout();

        $view = $component->render();

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertEquals('layouts.app', $view->getName());
    }

    public function test_app_layout_view_exists(): void
    {
        $this->assertTrue(View::exists('layouts.app'));
    }

    public function test_app_layout_component_extends_component_class(): void
    {
        $component = new AppLayout();

        $this->assertInstanceOf(\Illuminate\View\Component::class, $component);
    }

    // === Тесты для GuestLayout (если существует) ===

    public function test_guest_layout_component_can_be_instantiated(): void
    {
        if (class_exists(GuestLayout::class)) {
            $component = new GuestLayout();

            $this->assertInstanceOf(GuestLayout::class, $component);
        } else {
            $this->markTestSkipped('GuestLayout component does not exist');
        }
    }

    public function test_guest_layout_view_exists(): void
    {
        if (View::exists('layouts.guest')) {
            $this->assertTrue(View::exists('layouts.guest'));
        } else {
            $this->markTestSkipped('Guest layout view does not exist');
        }
    }
}