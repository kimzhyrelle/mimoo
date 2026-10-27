<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListFilterRequest;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(ListFilterRequest $request)
    {
        $filters = $request->validated();
        $active = null;

        if ($request->filled('conversation')) {
            $active = Conversation::query()->find($request->integer('conversation'));
        }

        $active = $active ?? Conversation::query()
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->first();

        if ($active) {
            $active->messages()
                ->where('sender', 'them')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $conversationModels = Conversation::query()
            ->listFilters($filters)
            ->with(['parcel', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query->unread()])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        $conversations = $conversationModels->map(fn (Conversation $conversation) => [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'initials' => $conversation->initials,
            'name' => $conversation->name,
            'preview' => $conversation->latestMessage?->body ?? 'No messages yet',
            'ref' => $conversation->reference(),
            'time' => $conversation->latestMessage?->created_at?->diffForHumans() ?? '',
            'online' => $conversation->online,
            'unread' => $conversation->unread_count,
        ]);

        $activeConvo = $active
            ? $conversations->firstWhere('id', $active->id)
            : [
                'id' => 0,
                'type' => 'admin',
                'initials' => '--',
                'name' => 'No conversation selected',
                'preview' => '',
                'ref' => 'GENERAL',
                'time' => '',
                'online' => false,
                'unread' => 0,
            ];

        $messages = $active
            ? $active->messages()->orderBy('id')->get()->map(fn (Message $message) => [
                'id' => $message->id,
                'from' => $message->sender,
                'text' => $message->body,
                'time' => $message->created_at?->format('g:i A') ?? '',
                'parcel_id' => $message->parcel_id,
            ])->values()->all()
            : [];

        $tabCounts = [
            'all' => Message::query()->unread()->count(),
            'rider' => Message::query()->unread()->whereHas('conversation', fn ($query) => $query->where('type', 'rider'))->count(),
            'seller' => Message::query()->unread()->whereHas('conversation', fn ($query) => $query->where('type', 'seller'))->count(),
            'admin' => Message::query()->unread()->whereHas('conversation', fn ($query) => $query->where('type', 'admin'))->count(),
        ];

        $activeTab = $request->get('tab', 'all');

        return view('sorting.chat', compact('conversations', 'activeConvo', 'messages', 'activeTab', 'tabCounts'));
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = DB::transaction(function () use ($data, $conversation) {
            $message = $conversation->messages()->create([
                'sender' => 'me',
                'body' => trim($data['body']),
                'parcel_id' => $conversation->parcel_id,
                'read_at' => now(),
            ]);

            $conversation->update(['last_message_at' => $message->created_at]);

            return $message;
        });

        return response()->json([
            'ok' => true,
            'message' => 'Message sent.',
            'data' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender' => $message->sender,
                'parcel_id' => $message->parcel_id,
                'created_at' => $message->created_at?->toIso8601String(),
                'time' => $message->created_at?->format('g:i A'),
            ],
            'unread_count' => Message::query()->unread()->count(),
        ]);
    }

    public function markRead(Conversation $conversation): JsonResponse
    {
        $conversation->messages()
            ->where('sender', 'them')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'Conversation marked as read.',
            'unread_count' => Message::query()->unread()->count(),
        ]);
    }
}
