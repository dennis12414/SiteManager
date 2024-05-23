<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\ChatMessage;
use App\Models\SiteManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function index(GetMessageRequest $request)
    {
        $data = $request->validated();
        $projectId = $data['projectId'];
        $currentPage = $data['page'];
        $pageSize = $data['pageSize'] ?? 15;

        $messages = ChatMessage::where('projectId', $projectId)
            ->latest('created_at')
            ->simplePaginate(
                $pageSize,
                ['*'],
                'page',
                $currentPage
            );

        foreach($messages as $message){
            $managerDetails = SiteManager::where("siteManagerId", $message->siteManagerId)->first();
            $message["name"] = $managerDetails->name;
        }

        return response([
            "messages"=>$messages
        ],200);
    }


    public function store(StoreMessageRequest $request)
    {
        $data = $request->validated();

        $chatMessage = ChatMessage::create($data);

        /// TODO send broadcast event to pusher and send notification to onesignal services
        //$this->sendNotificationToOther($chatMessage);

        return response([
            "messages"=>'Message has been sent successfully.',
            "message"=>$data['message']
        ],200);

    }
//
//    private function sendNotificationToOther(ChatMessage $chatMessage) : void {
//
//        // TODO move this event broadcast to observer
//        broadcast(new NewMessageSent($chatMessage))->toOthers();
//
//        $user = auth()->user();
//        $userId = $user->id;
//
//        $chat = Chat::where('id',$chatMessage->chat_id)
//            ->with(['participants'=>function($query) use ($userId){
//                $query->where('user_id','!=',$userId);
//            }])
//            ->first();
//        if(count($chat->participants) > 0){
//            $otherUserId = $chat->participants[0]->user_id;
//            $otherUser = User::where('id',$otherUserId)->first();
//            $otherUser->sendNewMessageNotification([
//                'messageData'=>[
//                    'senderName'=>$user->username,
//                    'message'=>$chatMessage->message,
//                    'chatId'=>$chatMessage->chat_id
//                ]
//            ]);
//
//        }
//
//    }


}
