<?php

namespace App\Controller;

use App\DTO\CreateTodoDTO;
use App\DTO\UpdateTodoDTO;
use App\Entity\Todo;
use App\OptionsResolver\PaginatorOptionsResolver;
use App\Repository\TodoRepository;
use App\Service\Serializer\DTOSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/api", "api_", format: "json")]
#[IsGranted("IS_AUTHENTICATED")]
class TodoController extends AbstractController
{
    #[Route('/todos', name: 'todos', methods: ["GET"])]
    public function index(
        Request                  $request,
        PaginatorOptionsResolver $paginatorOptionsResolver,
        TodoRepository           $todoRepository,
    ): JsonResponse
    {
        try {
            $queryParams = $paginatorOptionsResolver
                ->configurePage()
                ->resolve($request->query->all());

            $todos = $todoRepository->findAllWithPagination($queryParams['page']);

            return $this->json($todos);

        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/todos', name: 'create_todo', methods: ["POST"])]
    public function createTodo(
        Request                $request,
        DTOSerializer          $serializer,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        /** @var CreateTodoDTO $enquiry */
        $enquiry = $serializer->deserialize(
            $request->getContent(),
            CreateTodoDTO::class,
            'json',
        );

        $todo = new Todo();
        $todo->setTitle($enquiry->getTitle());

        $em->persist($todo);

        $em->flush();

        return $this->json($todo, Response::HTTP_CREATED);
    }

    #[Route("/todos/{id}", name: 'get_todo', methods: ["GET"])]
    public function getTodo(Todo $todo): JsonResponse
    {
        return $this->json($todo);
    }

    #[Route("/todos/{id}", name: 'update_todo', methods: ["PUT"])]
    public function updateTodo(
        Todo                   $todo,
        Request                $request,
        DTOSerializer          $serializer,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var UpdateTodoDTO $enquiry */
        $enquiry = $serializer->deserialize(
            $request->getContent(),
            UpdateTodoDTO::class,
            'json'
        );

        $todo->setTitle($enquiry->getTitle());
        $todo->setCompleted($enquiry->isCompleted());

        $em->flush();

        return $this->json($todo);
    }

    #[Route("/todos/{id}", name: 'delete_todo', methods: ["DELETE"])]
    public function deleteTodo(Todo $todo, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($todo);

        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
