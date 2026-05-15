<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function getOrCreateConversation(Request $request)
    {
        $user = $request->user();

        if (!$user->is_premium) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur chat hanya tersedia untuk pengguna Premium.',
                'require_premium' => true,
            ], 403);
        }

        $data = $request->validate([
            'physio_user_id' => 'required|exists:users,id',
            'screening_id'   => 'nullable|exists:screenings,id',
        ]);

        $conversation = Conversation::firstOrCreate([
            'parent_id'    => $user->id,
            'physio_id'    => $data['physio_user_id'],
            'screening_id' => $data['screening_id'] ?? null,
        ]);

        $conversation->load(['parent', 'physio.physiotherapist', 'messages.sender']);

        if ($conversation->physio && $conversation->physio->physiotherapist) {
            $conversation->physio->name        = $conversation->physio->physiotherapist->name;
            $conversation->physio->clinic_name = $conversation->physio->physiotherapist->clinic_name;
            $conversation->physio->specialty   = $conversation->physio->physiotherapist->specialty;
        }

        return response()->json(['success' => true, 'data' => $conversation]);
    }

    public function myConversations(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'physio') {
            $conversations = Conversation::where('physio_id', $user->id)
                ->with(['parent', 'latestMessage', 'physio.physiotherapist'])
                ->orderByDesc('last_message_at')
                ->get();
        } else {
            if (!$user->is_premium) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur chat hanya tersedia untuk pengguna Premium.',
                    'require_premium' => true,
                ], 403);
            }
            $conversations = Conversation::where('parent_id', $user->id)
                ->with(['physio.physiotherapist', 'latestMessage'])
                ->orderByDesc('last_message_at')
                ->get();
        }

        $conversations->each(function ($conv) {
            if ($conv->physio && $conv->physio->physiotherapist) {
                $conv->physio->name        = $conv->physio->physiotherapist->name;
                $conv->physio->clinic_name = $conv->physio->physiotherapist->clinic_name;
                $conv->physio->specialty   = $conv->physio->physiotherapist->specialty;
            }
        });

        return response()->json(['success' => true, 'data' => $conversations]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->parent_id !== $user->id && $conversation->physio_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'body' => 'required_without:file|nullable|string|max:2000',
            'type' => 'in:text,image,video,file',
            'file' => 'required_without:body|nullable|file|max:51200', // max 50MB
        ]);

        $fileUrl  = null;
        $fileName = null;
        $type     = $data['type'] ?? 'text';

        // Upload file ke Cloudinary jika ada
        if ($request->hasFile('file')) {
            $uploaded = $this->uploadToCloudinary($request->file('file'));
            if (!$uploaded) {
                return response()->json(['message' => 'Gagal mengupload file.'], 500);
            }
            $fileUrl  = $uploaded['url'];
            $fileName = $uploaded['original_name'];
            $type     = $uploaded['type']; // image | video | file
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $data['body'] ?? $fileName ?? 'File',
            'type'            => $type,
            'file_url'        => $fileUrl,
            'file_name'       => $fileName,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true, 'data' => $message->load('sender')]);
    }

    public function getMessages(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($conversation->parent_id !== $user->id && $conversation->physio_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        return response()->json(['success' => true, 'data' => $messages]);
    }

    // ─── Upload file ke Cloudinary ────────────────────────────────────────────
    private function uploadToCloudinary($file): ?array
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            // Fallback: simpan lokal
            return $this->uploadLocal($file);
        }

        $mime         = $file->getMimeType();
        $resourceType = 'raw';
        $type         = 'file';

        if (str_starts_with($mime, 'image/')) {
            $resourceType = 'image';
            $type         = 'image';
        } elseif (str_starts_with($mime, 'video/')) {
            $resourceType = 'video';
            $type         = 'video';
        }

        $timestamp  = time();
        $publicId   = 'posturely/chat/' . Str::uuid();
        $signature  = sha1("public_id={$publicId}&timestamp={$timestamp}{$apiSecret}");

        $response = Http::attach(
            'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload", [
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'public_id' => $publicId,
            'signature' => $signature,
        ]);

        if (!$response->successful()) {
            return $this->uploadLocal($file);
        }

        return [
            'url'           => $response->json('secure_url'),
            'original_name' => $file->getClientOriginalName(),
            'type'          => $type,
        ];
    }

    private function uploadLocal($file): array
    {
        $mime = $file->getMimeType();
        $type = 'file';
        if (str_starts_with($mime, 'image/')) $type = 'image';
        elseif (str_starts_with($mime, 'video/')) $type = 'video';

        $path = $file->store('chat_files', 'public');

        return [
            'url'           => asset('storage/' . $path),
            'original_name' => $file->getClientOriginalName(),
            'type'          => $type,
        ];
    }
}