<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Article;
use App\RedirectResponse;
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
//    protected ArticleRepository $articleRepository;
//
//    public function __construct(ArticleRepository $articleRepository)
//    {
//        $this->articleRepository = $articleRepository;
//    }

    protected IndexArticleService $indexArticleService;
    protected ShowArticleService $showArticleService;
    protected EditArticleService $editArticleService;
    protected UpdateArticleService $updateArticleService;
    protected StoreArticleService $storeArticleService;
    protected DeleteArticleService $deleteArticleService;

    public function __construct(
        IndexArticleService  $indexArticleService,
        ShowArticleService   $showArticleService,
        EditArticleService   $editArticleService,
        UpdateArticleService $updateArticleService,
        StoreArticleService  $storeArticleService,
        DeleteArticleService $deleteArticleService)
    {
        $this->indexArticleService = $indexArticleService;
        $this->showArticleService = $showArticleService;
        $this->editArticleService = $editArticleService;
        $this->updateArticleService = $updateArticleService;
        $this->storeArticleService = $storeArticleService;
        $this->deleteArticleService = $deleteArticleService;
    }


    public function index(string $page = null): Response
    {

        $collection = $this->indexArticleService->execute();

        return new ViewResponse('articles/index', ['articles' => $collection]);

    }

    public function show(string $id = null): Response
    {
        $id = (string)$id ?? $_POST['id'];

        if (!$this->checkIfIsSet($id, "Nothing to show!")) {
            return new RedirectResponse('/articles');
        }

        $article = $this->showArticleService->execute($id);

        return new ViewResponse('articles/show', ['article' => $article]);

    }

    private function checkIfIsSet(string $id, string $additionalMessage = null): ?Article
    {
        $article = $this->showArticleService->execute($id);
        if (empty($article)) {
            $_SESSION['actionStatus'] = "Post Nr. $id doesn't exist. " . $additionalMessage;
            return null;
        }
        return $article;
    }

    public function edit(string $id): Response
    {
        $id = (string)$id ?? $_POST['id'];
        if (!$this->checkIfIsSet($id, "Nothing to edit!")) {
            return new RedirectResponse('/articles');
        }

        $article = $this->editArticleService->execute($id);

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

        $title = $_POST['title'];
        $description = $_POST['description'];


        $this->storeArticleService->execute($title, $description);


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

        $this->updateArticleService->execute($id, $title, $description);

        $_SESSION['actionStatus'] = 'Post stored successfully!';
        return new RedirectResponse('/articles/' . $id);

    }

    public function delete(string $id): Response
    {

        $id = (string)$id ?? $_POST['id'];

        $foundArticle = $this->checkIfIsSet($id, "Nothing changed!");

        if (!$foundArticle) {
            return new RedirectResponse('/articles');
        }


        $_SESSION['actionStatus'] = "Post Nr. $id has been deleted";

        $this->deleteArticleService->execute($foundArticle);

        return new RedirectResponse('/articles');
    }

}