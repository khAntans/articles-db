<?php
declare(strict_types=1);

namespace App\Services\Article;

use App\Repositories\ArticleRepository;

class UpdateArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function execute(string $id, string $title, string $description): void
    {

        $article = $this->articleRepository->getById($id);

        if ($article == null) {
            return;
        }

        $article->update(
            ['title' => $title,
                'description' => $description]
        );

        $this->articleRepository->save($article);

    }

}