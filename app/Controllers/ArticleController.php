<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Article;
use App\RedirectResponse;
use App\Repositories\ArticleRepository;
use App\Response;
use App\Services\Article\DeleteArticleService;
use App\Services\Article\EditArticleService;
use App\Services\Article\IndexArticleService;
use App\Services\Article\ShowArticleService;
use App\Services\Article\StoreArticleService;
use App\Services\Article\UpdateArticleService;
use App\ViewResponse;
use Respect\Validation\Validator as v;

class ArticleController
{
    protected ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function index(string $page = null): Response
    {

        $service = new IndexArticleService($this->articleRepository);

        $collection = $service->execute();

        return new ViewResponse('articles/index', ['articles' => $collection]);

    }

    public function show(string $id): Response
    {
        $id = (string)$id ?? $_POST['id'];

        if (!$this->checkIfIsSet($id, "Nothing to show!")) {
            return new RedirectResponse('/articles');
        }

        $service = new ShowArticleService($this->articleRepository);

        $article = $service->execute($id);

        return new ViewResponse('articles/show', ['article' => $article]);

    }


    public function edit(string $id): Response
    {
        $id = (string)$id ?? $_POST['id'];
        if (!$this->checkIfIsSet($id, "Nothing to edit!")) {
            return new RedirectResponse('/articles');
        }

        $service = new EditArticleService($this->articleRepository);

        $article = $service->execute($id);

        return new ViewResponse('articles/edit', ['article' => $article]);
    }

    public function create(): Response
    {
        return new ViewResponse('articles/create');
    }

    public function store(): Response
    {

        $validTitle = v::notBlank()->stringVal()->validate($_POST['title']);
        $validDescription = v::stringVal()->validate($_POST['description']);

        if (!$validTitle || !$validDescription) {
            $_SESSION['actionStatus'] = 'Failed to save! Invalid input detected!';
            return new RedirectResponse('/articles');
        }

        $title = $_POST['title'];
        $description = $_POST['description'];

        $service = new StoreArticleService($this->articleRepository);

        $service->execute($title, $description);


        $_SESSION['actionStatus'] = 'Post stored successfully!';

        return new RedirectResponse('/articles');

    }

    public function update(string $id): Response
    {
        $id = (string)$id ?? $_POST['id'];

        if (!$this->checkIfIsSet($id, "Nothing changed!")) {
            return new RedirectResponse('/articles');
        }


        $validTitle = v::notBlank()->stringVal()->validate($_POST['title']);
        $validDescription = v::stringVal()->validate($_POST['description']);

        if (!$validTitle || !$validDescription) {
            $_SESSION['actionStatus'] = 'Failed to update! Invalid input detected!';
            return new RedirectResponse('/articles/' . $id);
        }

        $title = $_POST['title'];
        $description = $_POST['description'];

        $service = new UpdateArticleService($this->articleRepository);

        $service->execute($id, $title, $description);

        $_SESSION['actionStatus'] = 'Post stored successfully!';
        return new RedirectResponse('/articles/' . $id);

    }

    public function delete(string $id): Response
    {

        $id = (string)$id ?? $_POST['id'];

        $foundArticle  = $this->checkIfIsSet($id, "Nothing changed!");

        if (!$foundArticle) {
            return new RedirectResponse('/articles');
        }


        $_SESSION['actionStatus'] = "Post Nr. $id has been deleted";

        $service = new DeleteArticleService($this->articleRepository);

        $service->execute($foundArticle);

        return new RedirectResponse('/articles');
    }

    private function checkIfIsSet(string $id, string $additionalMessage = null): ?Article
    {
        $article = $this->articleRepository->getById($id);
        if (empty($article)) {
            $_SESSION['actionStatus'] = "Post Nr. $id doesn't exist. " . $additionalMessage;
            return null;
        }
        return $article;
    }

}