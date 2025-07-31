<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function homePage() {
        return view('home');
    }

    public function contactPage() {
        return view('contact');
    }

    public function newsDatailsPage($id, $category = 1) {
        $news = [
            1 => [
                'title' => 'First news'
            ],
            2 => [
                'title' => 'Second news'
            ]
        ];

        $categories = [
            1 => [
                'category' => 'News'
            ],
            2 => [
                'category' => 'News IT'
            ]
    ];
        return view('news-details', ['data' => $news[$id], 'category' => $categories[$category]]);
    }
}