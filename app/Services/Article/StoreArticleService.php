<?php
declare(strict_types=1);

namespace App\Services\Article;

use App\Models\Article;
use App\Repositories\ArticleRepository;

class StoreArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function execute(string $title, string $description): void
    {
        $article = new Article(
            $title,
            $description,
            'http://placekitten.com/' . rand(400, 800) . '/' . rand(400, 800)
        );

        $this->articleRepository->save($article);

    }

}