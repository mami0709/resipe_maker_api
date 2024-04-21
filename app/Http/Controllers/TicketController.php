<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TicketCreateRequest;
use App\Http\Requests\TicketEditRequest;
use App\Models\User;
use App\UseCases\Ticket\GetTicketsAnswersWithMatchingUserIdAction;
use App\UseCases\Ticket\TicketCreateAction;
use App\UseCases\Ticket\TicketDeleteAction;
use App\UseCases\Ticket\TicketEditAction;
use App\UseCases\Ticket\GetAllTicketsAction;
use App\UseCases\Ticket\GetTicketDetailByIdAction;
use App\UseCases\Ticket\GetTicketsByUserIdAndCategoryAction;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

use App\UseCases\Ticket\AddAnswerToTicketAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            throw new ModelNotFoundException("User not found");
        }
    }

    public function getAll(Request $request, GetAllTicketsAction $action, $category = null): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);
        $category = (int) $request->query('category', null);

        $tickets = $action->execute($category, $perPage, $page);

        return response()->json($tickets);
    }

    public function getByTicketId(int $ticket_id, GetTicketDetailByIdAction $action): JsonResponse
    {
        try {
            $ticket = $action($ticket_id);

            $ticketArray = $ticket->toArray();
            $ticketArray['answers'] = $ticket->answers->toArray();

            return response()->json($ticketArray, JsonResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ticket not found'
            ], JsonResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addAnswer(int $ticket_id, Request $request, AddAnswerToTicketAction $action): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        try {
            $answer = $action($ticket_id, $data['user_id'], $data['content']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Server error',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json($answer, JsonResponse::HTTP_CREATED);
    }

    // ユーザーに関連するチケットの全てを取得
    public function getUserTickets(GetTicketsByUserIdAndCategoryAction $action, Request $request, $userId, $category = null): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);

        try {
            $ticketsPaginator = $action->__invoke($userId, $category, $page, $perPage);
            return response()->json($ticketsPaginator->toArray(), JsonResponse::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(TicketCreateRequest $ticketCreateRequest, TicketCreateAction $action): JsonResponse
    {
        $requestData = $ticketCreateRequest->validated();
        $ticket = $action($ticketCreateRequest->user(), $requestData);

        return response()->json($ticket, JsonResponse::HTTP_CREATED);
    }

    public function edit(int $ticket_id, TicketEditRequest $request, TicketEditAction $action): JsonResponse
    {
        return response()->json($action($ticket_id, $request->makeTicket(), $request->user()), JsonResponse::HTTP_OK);
    }

    public function delete(int $ticket_id, TicketDeleteAction $action): JsonResponse
    {
        return response()->json($action($ticket_id, auth()->id()), JsonResponse::HTTP_OK);
    }

    // 自分の回答したチケットの取得
    public function getWithMatchingUser(int $userId, GetTicketsAnswersWithMatchingUserIdAction $action): JsonResponse
    {
        try {
            $user = User::find($userId);
            if (is_null($user)) {
                return response()->json(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
            }
            $tickets = $action->__invoke($userId);
            return response()->json($tickets, JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
