<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;

class EmptyArticleRepository implements ArticleRepository
{
    protected ArticleCollection $articleCollection;

    public function __construct()
    {
        $this->articleCollection = new ArticleCollection([
            new Article('first article', 'description of first article', null, null, 1),
            new Article('second article', 'description of second article', null, null, 2),
            new Article('third article', 'description of third article', null, null, 3),
            new Article('fourth article', 'description of fourth article', null, null, 4),
        ]);
    }

    public function getById(string $id): ?Article
    {
        $articles = $this->articleCollection->all();
        foreach ($articles as $article) {
            if ($article->getId() == $id) {
                return $article;
            }
        }

        return null;
    }

    public function save(Article $article): void
    {
        // TODO: Implement save() method.
    }

    public function delete(Article $article): void
    {
        // TODO: Implement delete() method.
    }

    public function getAll(): ArticleCollection
    {
        return $this->articleCollection;
    }
}