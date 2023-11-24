<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Collections\ArticleCollection;
use App\Database;
use App\Models\Article;
use App\RedirectResponse;
use App\Response;
use App\ViewResponse;
use Carbon\Carbon;
use Respect\Validation\Validator as v;

class ArticleController
{

    public function index(string $page = null): Response
    {

        $articleEntries = Database::connect()->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->fetchAllAssociative();

        $collection = new ArticleCollection();

        foreach ($articleEntries as $articleEntry) {
            $collection->add($this->buildModel($articleEntry));
        }

        return new ViewResponse('articles/index', ['articles' => $collection]);
    }

    private function buildModel(array $articleEntry): Article
    {
        return new Article(
            $articleEntry['title'],
            $articleEntry['description'],
            $articleEntry['picture'],
            $articleEntry['created_at'],
            (int)$articleEntry['id'],
            $articleEntry['updated_at']
        );
    }

    public function show(string $id): Response
    {
        $articleEntry = Database::connect()->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->fetchAssociative();

        $article = $this->buildModel($articleEntry);

        return new ViewResponse('articles/show', ['article' => $article]);


    }

    public function edit(string $id): Response
    {
        $articleEntry = Database::connect()->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->fetchAssociative();

        $article = $this->buildModel($articleEntry);

        return new ViewResponse('articles/edit', ['article' => $article]);
    }

    public function create(): Response
    {
        return new ViewResponse('articles/create');
    }

    public function store(): Response
    {

        $validTitle = v::notBlank()->validate($_POST['title']);
        $validDescription = v::stringVal()->validate($_POST['description']);

        if (!$validTitle || !$validDescription) {
            $_SESSION['actionStatus'] = 'Failed to save! Invalid input detected!';
            return new RedirectResponse('/articles');
        }

        Database::connect()->createQueryBuilder()
            ->insert('articles')
            ->values(
                [
                    'title' => ':title',
                    'description' => ':description',
                    'picture' => ':picture',
                    'created_at' => ':created_at'
                ]
            )->setParameters([
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'picture' => "http://placekitten.com/500/500",
                'created_at' => Carbon::now()
            ])->executeQuery();

        $_SESSION['actionStatus'] = 'Post stored successfully!';

        return new RedirectResponse('/articles');

    }

    public function update(string $id): Response
    {

        $validTitle = v::notBlank()->validate($_POST['title']);
        $validDescription = v::stringVal()->validate($_POST['description']);

        if (!$validTitle || !$validDescription) {
            $_SESSION['actionStatus'] = 'Failed to update! Invalid input detected!';
            return new RedirectResponse('/articles/' . $id);
        }
        Database::connect()->createQueryBuilder()
            ->update('articles')
            ->set('title', ':title')
            ->set('description', ':description')
            ->set('updated_at', ':updated_at')
            ->where('id = :id')
            ->setParameters([
                'id' => $id,
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'updated_at' => Carbon::now()
            ])->executeQuery();

        $_SESSION['actionStatus'] = 'Post stored successfully!';
        return new RedirectResponse('/articles/' . $id);

    }

    public function delete(): Response
    {
        $_SESSION['actionStatus'] = 'Post nr ' . $_POST['id'] . ' has been deleted';
        Database::connect()->delete('articles', ['id' => $_POST['id']]);

        return new RedirectResponse('/articles');
    }

}