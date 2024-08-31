<?php

namespace App\Controller;

use App\Entity\Todo;
use App\OptionsResolver\TodoOptionsResolver;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api", "api_")]
class TodoController extends AbstractController
{
    #[Route('/todos', name: 'todos', methods: ["GET"])]
    public function index(TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->findAll();

        return $this->json($todos);
    }

    #[Route('/todos', name: 'create_todo', methods: ["POST"])]
    public function createTodo(
        Request                $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator,
        TodoOptionsResolver    $todoOptionsResolver
    ): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);

            $fields = $todoOptionsResolver->configureTitle(true)->resolve($requestBody);

            $todo = new Todo();
            $todo->setTitle($fields['title']);

            $errors = $validator->validate($todo);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $entityManager->persist($todo);

            $entityManager->flush();

            return $this->json($todo, status: Response::HTTP_CREATED);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route("/todos/{id}", name: 'get_todo', methods: ["GET"])]
    public function getTodo(Todo $todo): JsonResponse
    {
        return $this->json($todo);
    }

    #[Route("/todos/{id}", name: 'update_todo', methods: ["PATCH", "PUT"])]
    public function updateTodo(
        Todo $todo,
        Request $request,
        TodoOptionsResolver $todoOptionsResolver,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    )
    {
        try {
            $isPutMethod = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);

            $fields = $todoOptionsResolver
                ->configureTitle($isPutMethod)
                ->configureCompleted($isPutMethod)
                ->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "title":
                        $todo->setTitle($value);
                        break;
                    case "completed":
                        $todo->setCompleted($value);
                        break;
                }
            }

            $errors = $validator->validate($todo);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $entityManager->flush();

            return $this->json($todo);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route("/todos/{id}", name: 'delete_todo', methods: ["DELETE"])]
    public function deleteTodo(Todo $todo, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($todo);

        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
