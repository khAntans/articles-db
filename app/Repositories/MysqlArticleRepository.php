<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class MysqlArticleRepository implements ArticleRepository
{
    private Connection $connection;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $connectionParams = [
            'dbname' => 'articles',
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];

        $this->connection = DriverManager::getConnection($connectionParams);

    }

    public function getById(string $id): ?Article
    {
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->fetchAssociative();

        if (empty($result)) {
            // threw new exception that article not found
            return null;
        }
        return $this->buildModel($result);
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

    public function save(Article $article): void
    {


        if ($article->getId()) {
            $this->update($article);
            return;
        }

        $this->insert($article);

    }

    private function update(Article $article): void
    {
        $this->connection->createQueryBuilder()
            ->update('articles')
            ->set('title', ':title')
            ->set('description', ':description')
            ->set('updated_at', ':updated_at')
            ->where('id = :id')
            ->setParameters([
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
                'updated_at' => $article->getUpdatedAt()
            ])->executeQuery();
    }

    private function insert(Article $article): void
    {
        $this->connection->createQueryBuilder()
            ->insert('articles')
            ->values(
                [
                    'title' => ':title',
                    'description' => ':description',
                    'picture' => ':picture',
                    'created_at' => ':created_at'
                ]
            )->setParameters([
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
                'picture' => $article->getPicture(),
                'created_at' => $article->getCreatedAt()
            ])->executeQuery();
    }

    public function getAll(): ArticleCollection
    {
        $articleEntries = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->fetchAllAssociative();

        $collection = new ArticleCollection();

        foreach ($articleEntries as $articleEntry) {
            $collection->add($this->buildModel($articleEntry));
        }

        return $collection;

    }

    public function delete(Article $article): void
    {

        $this->connection->delete('articles', ['id' => $article->getId()]);

    }


}